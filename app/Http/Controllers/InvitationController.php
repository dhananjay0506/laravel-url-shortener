<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function create(): View
    {
        $user = Auth::user();
        if ($user->isMember()) {
            abort(403, 'Unauthorized action.');
        }

        $companies = $user->isSuperAdmin() ? Company::orderBy('name')->get() : collect();
        return view('invitations.create', compact('companies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isMember()) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->isSuperAdmin()) {
            $validated =  $request->validate([
                'email'            => ['required', 'email', 'unique:users,email'],
                'new_company_name' => ['required_without:company_id', 'nullable', 'string', 'max:255', 'unique:companies,name'],
                'company_id'       => ['required_without:new_company_name', 'nullable', 'exists:companies,id'],
            ]);

            $targetRole = 'Admin';
        } else {
            $validated = $request->validate([
                'email' => ['required', 'email', 'unique:users,email'],
                'role'  => ['required', Rule::in(['Admin', 'Member'])],
            ]);

            $targetRole = $validated['role'];
        }

        DB::beginTransaction();
        try {
            if ($user->isSuperAdmin()) {
                if (!empty($validated['new_company_name'])) {
                    $company = Company::create(['name' => $validated['new_company_name']]);
                    $targetCompanyId = $company->id;
                } else {
                    $targetCompanyId = $validated['company_id'];
                }
            } else {
                $targetCompanyId = $user->company_id;
            }

            $token = Str::random(40);

            $invitation = Invitation::create([
                'email' => $validated['email'],
                'token' => $token,
                'company_id' => $targetCompanyId,
                'role' => $targetRole,
                'invited_by' => $user->id,
            ]);

            DB::commit();
            Log::info('Invitation generated', ['invitation_id' => $invitation->id, 'email' => $validated['email']]);

            $inviteLink = route('invitations.accept', ['token' => $token]);
            return redirect()->route('invitations.create')->with('invite_link', $inviteLink)->with('success', 'Invitation link created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Invitation dispatch failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to generate invitation.');
        }
    }

    public function accept(string $token): View
    {
        $invitation = Invitation::with('company')->where('token', $token)->whereNull('accepted_at')->firstOrFail();
        return view('invitations.accept', compact('invitation'));
    }

    public function processAccept(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->whereNull('accepted_at')->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::beginTransaction();
        try {
            $newUser = User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'company_id' => $invitation->company_id,
                'role' => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);

            DB::commit();
            Log::info('Invitation claimed and user registered', ['user_id' => $newUser->id]);

            Auth::login($newUser);
            return redirect()->route('urls.index')->with('success', 'Registration complete. Welcome!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Accept invitation processing failure', ['error' => $e->getMessage()]);
            return back()->with('error', 'Unable to complete registration.');
        }
    }
}