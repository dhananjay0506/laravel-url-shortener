<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('urls.index') }}">LinkShrink</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            @auth
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('urls.index') }}">URLs</a>
                    </li>
                    @if (!Auth::user()->isMember())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('invitations.create') }}">Send Invitation</a>
                        </li>
                    @endif
                </ul>
                <div class="d-flex align-items-center text-white gap-3">
                    <span class="badge bg-secondary">
                        {{ Auth::user()->role }} 
                        @if(Auth::user()->company)
                            ({{ Auth::user()->company->name }})
                        @endif
                    </span>
                    <span>{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>