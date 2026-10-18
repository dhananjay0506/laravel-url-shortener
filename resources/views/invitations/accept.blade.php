@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">
                <h4 class="card-title text-center mb-2">Accept Invitation</h4>
                <p class="text-muted text-center mb-4">Joining <strong>{{ $invitation->company->name }}</strong> as <strong>{{ $invitation->role }}</strong></p>

                <form action="{{ route('invitations.process', $invitation->token) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="text" class="form-control" value="{{ $invitation->email }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Complete Registration</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection