@extends('layouts.backlayout')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="d-flex justify-content-between flex-wrap">
                <div class="d-flex align-items-end flex-wrap">
                    <div class="mr-md-3 mr-xl-5">
                        <h2>Admin Dashboard Overview</h2>
                        <p class="mb-md-0">Welcome to AI Prompt Management System</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="row">
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-primary text-white border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="font-weight-normal">Total Prompts</h5>
                    <h2 class="mb-0 font-weight-bold">{{ $stats['total_prompts'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-success text-white border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="font-weight-normal">Categories</h5>
                    <h2 class="mb-0 font-weight-bold">{{ $stats['total_categories'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-warning text-white border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="font-weight-normal">Total Users</h5>
                    <h2 class="mb-0 font-weight-bold">{{ $stats['total_users'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 grid-margin stretch-card">
            <div class="card bg-purple text-white border-0 shadow-sm" style="background-color: #6f42c1;">
                <div class="card-body">
                    <h5 class="font-weight-normal">Total Copies</h5>
                    <h2 class="mb-0 font-weight-bold">{{ $stats['total_copies'] ?? 0 }}</h2>
                    <a href="{{ route('admin.analytics.copies') }}" class="text-white text-decoration-none small mt-2 d-inline-block">View Analytics &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Prompts Table --}}
    <div class="row mt-4">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="card-title">Recent Prompts</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" style="table-layout: fixed; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 6%;">#</th>
                                    <th style="width: 44%;">Title</th>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 15%;">Image</th>
                                    <th style="width: 20%;">Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPrompts as $key => $prompt)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td class="text-truncate" style="max-width: 250px;" title="{{ $prompt->title }}">
                                            {{ $prompt->title }}
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $prompt->category->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($prompt->image))
                                                <a href="{{ asset('storage/' . $prompt->image) }}" target="_blank" title="View Image">
                                                    <img src="{{ asset('storage/' . $prompt->image) }}" alt="Preview" style="width: 35px; height: 35px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                                </a>
                                            @else
                                                <span class="text-muted small">No Image</span>
                                            @endif
                                        </td>
                                        <td>{{ $prompt->created_at->format('d M, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No prompts found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection