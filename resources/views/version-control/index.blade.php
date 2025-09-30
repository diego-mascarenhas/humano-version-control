@extends('layouts.layoutMaster')

@section('title', 'Version Control Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">Version Control</h4>
        <p class="text-muted">Advanced audit trails and data restoration system</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.index') }}" class="btn btn-primary">
            <i class="ti ti-history me-1"></i>View Audit Trail
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <div class="avatar-initial bg-primary rounded">
                            <i class="ti ti-activity"></i>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Activities</span>
                <h3 class="card-title mb-2">{{ number_format($stats['total_activities']) }}</h3>
                <small class="text-success fw-semibold">
                    <i class="ti ti-chevron-up"></i>
                    +{{ number_format($stats['today_activities']) }} today
                </small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <div class="avatar-initial bg-success rounded">
                            <i class="ti ti-database"></i>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Tracked Models</span>
                <h3 class="card-title mb-2">{{ number_format($stats['tracked_models']) }}</h3>
                <small class="text-muted">Different model types</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <div class="avatar-initial bg-info rounded">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Active Users</span>
                <h3 class="card-title mb-2">{{ number_format($stats['active_users']) }}</h3>
                <small class="text-muted">Making changes</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <div class="avatar-initial bg-warning rounded">
                            <i class="ti ti-clock"></i>
                        </div>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Today</span>
                <h3 class="card-title mb-2">{{ number_format($stats['today_activities']) }}</h3>
                <small class="text-muted">Activities recorded</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Activity -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">Recent Activity</h5>
                <a href="{{ route('version-control.audit.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $activity)
                            <div class="list-group-item d-flex align-items-center px-0">
                                <div class="avatar flex-shrink-0 me-3">
                                    <div class="avatar-initial bg-label-primary rounded-circle">
                                        <i class="ti ti-{{ $activity->description === 'created' ? 'plus' : ($activity->description === 'updated' ? 'edit' : 'trash') }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        {{ class_basename($activity->subject_type) }}
                                        {{ $activity->description }}
                                    </h6>
                                    <small class="text-muted">
                                        by {{ $activity->causer ? $activity->causer->name : 'System' }}
                                        - {{ $activity->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                @if($activity->subject)
                                    <a href="{{ route('version-control.audit.show', [
                                        'model' => strtolower(class_basename($activity->subject_type)),
                                        'id' => $activity->subject_id
                                    ]) }}" class="btn btn-sm btn-outline-secondary">
                                        View
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-history display-4 text-muted"></i>
                        <p class="text-muted mt-2">No recent activity found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Model Statistics -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">Model Activity</h5>
            </div>
            <div class="card-body">
                @if(count($modelStats) > 0)
                    @foreach($modelStats as $model => $count)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ class_basename($model) }}</h6>
                                <small class="text-muted">{{ number_format($count) }} activities</small>
                            </div>
                            <div class="badge bg-primary">{{ $count }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center">
                        <i class="ti ti-database-off display-6 text-muted"></i>
                        <p class="text-muted mt-2">No tracked models</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
