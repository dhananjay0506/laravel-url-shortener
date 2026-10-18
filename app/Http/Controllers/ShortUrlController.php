<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShortUrlController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $urlsQuery = ShortUrl::with(['company', 'user'])->latest();

        if ($user->isSuperAdmin()) {
            $urls = $urlsQuery->paginate(15);
        } elseif ($user->isAdmin()) {
            $urls = $urlsQuery->where('company_id', $user->company_id)->paginate(15);
        } else {
            $urls = $urlsQuery->where('user_id', $user->id)->paginate(15);
        }

        return view('urls.index', compact('urls'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('urls.index')->with('error', 'SuperAdmin is not permitted to generate short URLs.');
        }

        $validated = $request->validate([
            'original_url' => ['required', 'url', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            do {
                $code = Str::random(6);
            } while (ShortUrl::where('short_code', $code)->exists());

            $shortUrl = ShortUrl::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'original_url' => $validated['original_url'],
                'short_code' => $code,
                'clicks' => 0,
            ]);

            DB::commit();
            Log::info('Short URL generated', ['id' => $shortUrl->id, 'code' => $code, 'user_id' => $user->id]);

            return redirect()->route('urls.index')->with('success', 'Short URL generated successfully: ' . url($code));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create short URL', ['error' => $e->getMessage(), 'user_id' => $user->id]);
            return redirect()->route('urls.index')->with('error', 'Could not generate short URL. Please try again.');
        }
    }

    public function resolve(string $code): RedirectResponse
    {
        try {
            $shortUrl = ShortUrl::where('short_code', $code)->firstOrFail();
            $shortUrl->increment('clicks');

            Log::info('Redirecting short code', ['code' => $code, 'clicks' => $shortUrl->clicks]);
            return redirect()->away($shortUrl->original_url);
        } catch (\Throwable $e) {
            Log::warning('Failed redirect for short code', ['code' => $code, 'error' => $e->getMessage()]);
            abort(404, 'URL not found or expired.');
        }
    }
}