@extends('layouts.layoutMaster')

@section('title', __('Version Control') . ' Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">{{ __('Version Control') }}</h4>
        <p class="text-muted">{{ __('Advanced audit trails and data restoration system') }}</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.index') }}" class="btn btn-primary">
            <i class="ti ti-history me-1"></i>{{ __('View Audit Trail') }}
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
                <span class="fw-semibold d-block mb-1">{{ __('Total Activities') }}</span>
                <h3 class="card-title mb-2">{{ number_format($stats['total_activities']) }}</h3>
                <small class="text-success fw-semibold">
                    <i class="ti ti-chevron-up"></i>
                    +{{ number_format($stats['today_activities']) }} {{ __('Today') }}
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
                <span class="fw-semibold d-block mb-1">{{ __('Tracked Models') }}</span>
                <h3 class="card-title mb-2">{{ number_format($stats['tracked_models']) }}</h3>
                <small class="text-muted">{{ __('Different model types') }}</small>
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
                <span class="fw-semibold d-block mb-1">{{ __('Active Users') }}</span>
                <h3 class="card-title mb-2">{{ number_format($stats['active_users']) }}</h3>
                <small class="text-muted">{{ __('Making changes') }}</small>
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
                <span class="fw-semibold d-block mb-1">{{ __('Today') }}</span>
                <h3 class="card-title mb-2">{{ number_format($stats['today_activities']) }}</h3>
                <small class="text-muted">{{ __('Activities recorded') }}</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Activity -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">{{ __('Recent Activity') }}</h5>
                <a href="{{ route('version-control.audit.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('View All') }}
                </a>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentActivity as $activity)
                            @php
                                // Mapeo mejorado que detecta palabras clave en la descripción
                                $description = strtolower($activity->description);
                                
                                if (str_contains($description, 'creado') || str_contains($description, 'created')) {
                                    $actionConfig = ['icon' => 'plus', 'color' => 'success', 'bg' => 'bg-label-success'];
                                } elseif (str_contains($description, 'actualizado') || str_contains($description, 'updated')) {
                                    $actionConfig = ['icon' => 'edit', 'color' => 'primary', 'bg' => 'bg-label-primary'];
                                } elseif (str_contains($description, 'eliminado') || str_contains($description, 'deleted')) {
                                    $actionConfig = ['icon' => 'trash', 'color' => 'danger', 'bg' => 'bg-label-danger'];
                                } elseif (str_contains($description, 'restaurado') || str_contains($description, 'restored')) {
                                    $actionConfig = ['icon' => 'restore', 'color' => 'info', 'bg' => 'bg-label-info'];
                                } elseif (str_contains($description, 'logged in') || str_contains($description, 'login')) {
                                    $actionConfig = ['icon' => 'login', 'color' => 'warning', 'bg' => 'bg-label-warning'];
                                } elseif (str_contains($description, 'logged out') || str_contains($description, 'logout')) {
                                    $actionConfig = ['icon' => 'logout', 'color' => 'secondary', 'bg' => 'bg-label-secondary'];
                                } else {
                                    $actionConfig = ['icon' => 'activity', 'color' => 'secondary', 'bg' => 'bg-label-secondary'];
                                }
                            @endphp
                            <div class="list-group-item d-flex align-items-center px-0">
                                <div class="avatar flex-shrink-0 me-3">
                                    <div class="avatar-initial {{ $actionConfig['bg'] }} rounded-circle">
                                        <i class="ti ti-{{ $actionConfig['icon'] }} text-{{ $actionConfig['color'] }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        {{ class_basename($activity->subject_type) }}
                                        {{ __($activity->description) }}
                                    </h6>
                                    <small class="text-muted">
                                        {{ __('by') }} {{ $activity->causer ? $activity->causer->name : __('System') }}
                                        - {{ $activity->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                {{-- ✅ NUEVO SISTEMA DINÁMICO - Acceso directo por Activity ID --}}
                                <a href="{{ route('version-control.activity.show', $activity->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye ti-sm me-1"></i>{{ __('View Activity') }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-history display-4 text-muted"></i>
                        <p class="text-muted mt-2">{{ __('No recent activity found') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Model Statistics -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title m-0">{{ __('Model Activity') }}</h5>
            </div>
            <div class="card-body">
                @if(count($modelStats) > 0)
                    @foreach($modelStats as $model => $count)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ class_basename($model) }}</h6>
                                <small class="text-muted">{{ number_format($count) }} {{ __('activities') }}</small>
                            </div>
                            <div class="badge bg-primary">{{ $count }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center">
                        <i class="ti ti-database-off display-6 text-muted"></i>
                        <p class="text-muted mt-2">{{ __('No tracked models') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
