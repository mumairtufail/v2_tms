@extends('layouts.app')
@section('content')

<div class="container-fluid py-4 min-vh-100">

    <!-- Header Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body card-body-form">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fa fa-users text-primary me-2"></i>
                        Activity logs
                    </h4>
                    <p class="text-muted mb-0">Users Activity</p>
                </div>
            </div>
        </div>
    </div>

    @include('partials.message')
    <!-- Table Section -->
    <div class="card shadow-sm">
        <div class="card-body card-body-form">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px">ID</th>
                            <th>User</th>
                            <th>Method</th>
                            <th>Browser</th>
                            <th>Action</th>
                            <th class="text-center">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activity_logs as $key => $logs)
                        <tr>
                            <td class="text-center">{{ $key +1 }}</td>
                            <td>{{ $logs->user->f_name }}</td>
                            <td>{{ $logs->method }}</td>
                            <td>{{ $logs->user_agent }}</td>
                            <td>{{ $logs->action }}</td>
                            <td>
                                @canPermission('activity_logs', 'view')
                                <a class="btn btn-sm btn-outline-primary edit-user"
                                    href="{{ route('logs.view', $logs->id) }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @endcanPermission
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fa fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No logs Found</h5>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection