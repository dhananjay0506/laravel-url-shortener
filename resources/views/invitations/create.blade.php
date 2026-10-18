@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        @if (session('invite_link'))
            <div class="alert alert-success d-flex flex-column gap-2">
                <strong>Invitation Link Created:</strong>
                <div class="input-group">
                    <input type="text" id="link-field" class="form-control" value="{{ session('invite_link') }}" readonly>
                    <button class="btn btn-outline-dark copy-btn" data-clipboard-text="{{ session('invite_link') }}">Copy Link</button>
                </div>
                <small class="text-muted">Distribute this link directly to the invitee to finalize onboarding.</small>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-white"><strong>Send New Invitation</strong></div>
            <div class="card-body">
                <form action="{{ route('invitations.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Invitee Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" required value="{{ old('email') }}">
                    </div>

                    @if (Auth::user()->isSuperAdmin())
                        <div class="mb-3">
                            <label for="new_company_name" class="form-label">Create New Company</label>
                            <input type="text" name="new_company_name" id="new_company_name" class="form-control" placeholder="Company Name" value="{{ old('new_company_name') }}">
                            <small class="text-muted">Or pick an existing one below:</small>
                        </div>
                        <div class="mb-3">
                            <label for="company_id" class="form-label">Existing Company</label>
                            <select name="company_id" id="company_id" class="form-select">
                                <option value="">-- Choose Existing Company --</option>
                                @foreach ($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="Member">Member</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary">Generate Invitation</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection