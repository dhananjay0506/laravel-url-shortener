@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>URL Dashboard</h2>
</div>

@if (!Auth::user()->isSuperAdmin())
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white"><strong>Shorten a New URL</strong></div>
        <div class="card-body">
            <form action="{{ route('urls.store') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="url" name="original_url" class="form-control" placeholder="https://example.com/very-long-url" required>
                    <button class="btn btn-primary" type="submit">Shorten</button>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>Generated URLs</strong></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Short URL</th>
                        <th>Original Destination</th>
                        @if(Auth::user()->isSuperAdmin())
                            <th>Company</th>
                        @endif
                        <th>Created By</th>
                        <th>Clicks</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($urls as $url)
                        <tr>
                            <td>
                                <a href="{{ url($url->short_code) }}" target="_blank" class="fw-bold">{{ url($url->short_code) }}</a>
                                <button type="button" class="btn btn-sm btn-outline-secondary copy-btn ms-1" data-clipboard-text="{{ url($url->short_code) }}">Copy</button>
                            </td>
                            <td class="text-truncate" style="max-width: 300px;">
                                <a href="{{ $url->original_url }}" target="_blank" class="text-muted">{{ $url->original_url }}</a>
                            </td>
                            @if(Auth::user()->isSuperAdmin())
                                <td>{{ isset($url->company->name) ? $url->company->name : 'N/A' }}</td>
                            @endif
                            <td>{{ isset($url->user->name) ? $url->user->name : 'System' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $url->clicks }}</span></td>
                            <td>{{ $url->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ Auth::user()->isSuperAdmin() ? 6 : 5 }}" class="text-center py-4 text-muted">
                                No shortened URLs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($urls->hasPages())
        <div class="card-footer bg-white">
            {{ $urls->links() }}
        </div>
    @endif
</div>
@endsection