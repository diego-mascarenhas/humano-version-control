@extends('layouts.layoutMaster')

@section('title', __('Audit Details') . ' - ' . ($subject ? class_basename($subject) : __('Deleted Record')))

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">{{ __('Version Control') }}/{{ __('Audit') }}/</span>
            @if($subject)
                {{ class_basename($subject) }} #{{ $subject->id }}
            @else
                <span class="text-danger">{{ __('Deleted Record') }}</span>
            @endif
        </h4>
        <p class="text-muted">{{ __('Complete activity history for this record') }}</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        @if($subject && $activities->total() > 1)
            <a href="{{ route('version-control.audit.versions', ['model' => $modelSlug, 'id' => $subject->id]) }}" class="btn btn-primary">
                <i class="ti ti-versions me-1"></i>{{ __('View Versions') }}
            </a>
        @endif
        
        <a href="{{ route('version-control.audit.index') }}" class="btn btn-label-secondary waves-effect" style="cursor: pointer !important;">
            <i class="ti ti-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

{{-- ✅ VISTA ESTÁNDAR PARA TODAS LAS ACTIVIDADES --}}

<!-- Record Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Record Information') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>{{ __('Type') }}:</strong> {{ $model }}<br>
                @if($subject)
                    <strong>ID:</strong> {{ $subject->id }}<br>
                    @if(isset($subject->name))
                        <strong>{{ __('Name') }}:</strong> {{ $subject->name }}<br>
                    @endif
                    @if(isset($subject->title))
                        <strong>{{ __('Title') }}:</strong> {{ $subject->title }}<br>
                    @endif
                    @if(isset($subject->email))
                        <strong>{{ __('Email') }}:</strong> {{ $subject->email }}<br>
                    @endif
                @else
                    <strong>ID:</strong> <span class="text-danger">{{ __('Record deleted') }}</span><br>
                    <strong>{{ __('Status') }}:</strong> <span class="text-danger">{{ __('This record no longer exists') }}</span><br>
                @endif
            </div>
            <div class="col-md-6">
                @if($subject)
                    <strong>{{ __('Created') }}:</strong> {{ $subject->created_at->format('M d, Y H:i:s') }}<br>
                    <strong>{{ __('Updated') }}:</strong> {{ $subject->updated_at->format('M d, Y H:i:s') }}<br>
                    <strong>{{ __('Total Activities') }}:</strong> {{ $activities->total() }}<br>
                    @if($activities->count() > 0)
                        <strong>{{ __('Last Activity') }}:</strong> {{ $activities->first()->created_at->format('M d, Y H:i:s') }}
                    @endif
                @else
                    <strong>{{ __('Available Activities') }}:</strong> {{ $activities->count() }}<br>
                    <strong>{{ __('Note') }}:</strong> <span class="text-muted">{{ __('Only activity history is available for deleted records') }}</span><br>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Activity History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ __('Activity History') }}</h5>
        <span class="badge bg-primary">{{ $activities->total() }} {{ $activities->total() == 1 ? __('Activity') : __('Activities') }}</span>
    </div>
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="timeline">
                @foreach($activities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            @php
                                // Mapeo usando principalmente el campo event
                                if ($activity->event === 'created') {
                                    $actionConfig = ['icon' => 'plus', 'color' => 'success'];
                                } elseif ($activity->event === 'updated') {
                                    $actionConfig = ['icon' => 'edit', 'color' => 'primary'];
                                } elseif ($activity->event === 'deleted') {
                                    $actionConfig = ['icon' => 'trash', 'color' => 'danger'];
                                } elseif (str_contains(strtolower($activity->description), 'restored') || str_contains(strtolower($activity->description), 'record restored')) {
                                    $actionConfig = ['icon' => 'rotate-clockwise', 'color' => 'info'];
                                } elseif (str_contains(strtolower($activity->description), 'logged in') || str_contains(strtolower($activity->description), 'login')) {
                                    $actionConfig = ['icon' => 'login', 'color' => 'warning'];
                                } elseif (str_contains(strtolower($activity->description), 'logged out') || str_contains(strtolower($activity->description), 'logout')) {
                                    $actionConfig = ['icon' => 'logout', 'color' => 'secondary'];
                                } else {
                                    $actionConfig = ['icon' => 'activity', 'color' => 'info'];
                                }
                            @endphp
                            <div class="timeline-marker-indicator bg-{{ $actionConfig['color'] }}">
                                <i class="ti ti-{{ $actionConfig['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1">
                                        {{ ucfirst($activity->description) }}
                                        @if($activity->description === 'restored')
                                            <span class="badge bg-success ms-2">Restoration</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ $activity->created_at->format('M d, Y H:i:s') }}
                                        ({{ $activity->created_at->diffForHumans() }})
                                        {{ __('by') }} {{ $activity->causer ? $activity->causer->name : __('System') }}
                                    </small>
                                </div>
                        <div class="d-flex gap-2">
                            @php
                                // Check if this is a "created" event using only the event field
                                $isCreatedEvent = ($activity->event === 'created');
                                $isAdmin = auth()->user()->hasRole('admin');
                                $recordExists = $activity->subject !== null;
                            @endphp
                            
                            @if($isCreatedEvent && $isAdmin && $recordExists)
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteActivity({{ $activity->id }}, this)"
                                        title="Eliminar registro del sistema">
                                    <i class="ti ti-trash"></i>
                                </button>
                            @endif
                                    <button class="btn btn-sm btn-outline-primary"
                                            onclick="toggleDetails({{ $activity->id }})"
                                            title="Show/hide details">
                                        <i class="ti ti-chevron-down"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Activity Details (Initially Hidden) -->
                            <div id="activity-details-{{ $activity->id }}" class="activity-details" style="display: none;">
                                @if($activity->properties->isNotEmpty())
                                    @php
                                        $attributes = $activity->properties->get('attributes', []);
                                        $old = $activity->properties->get('old', []);
                                    @endphp

                                    @if(count($attributes) > 0)
                                        <div class="mt-3">
                                            <strong>{{ __('Changes') }}:</strong>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ __('Field') }}</th>
                                                            @if(count($old) > 0)
                                                                <th>{{ __('Old Value') }}</th>
                                                            @endif
                                                            <th>{{ __('New Value') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($attributes as $field => $newValue)
                                                            <tr>
                                                                <td><strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong></td>
                                                                @if(count($old) > 0)
                                                                    <td>
                                                                        <span class="text-muted">
                                                                            @if(isset($old[$field]))
                                                                                @if(is_array($old[$field]) || is_object($old[$field]))
                                                                                    <code>{{ json_encode($old[$field], JSON_PRETTY_PRINT) }}</code>
                                                                                @elseif(is_null($old[$field]))
                                                                                    <em>null</em>
                                                                                @elseif($old[$field] === '')
                                                                                    <em>(empty)</em>
                                                                                @else
                                                                                    {{ $old[$field] }}
                                                                                @endif
                                                                            @else
                                                                                <em>{{ __('not set') }}</em>
                                                                            @endif
                                                                        </span>
                                                                    </td>
                                                                @endif
                                                                <td>
                                                                    @if(is_array($newValue) || is_object($newValue))
                                                                        <code>{{ json_encode($newValue, JSON_PRETTY_PRINT) }}</code>
                                                                    @elseif(is_null($newValue))
                                                                        <em>null</em>
                                                                    @elseif($newValue === '')
                                                                        <em>(empty)</em>
                                                                    @else
                                                                        {{ $newValue }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <p class="text-muted mt-2">No detailed information available for this activity.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                {{ $activities->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="ti ti-history display-4 text-muted"></i>
                <p class="text-muted mt-2">No activities found for this record</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
function toggleDetails(activityId) {
    const details = document.getElementById('activity-details-' + activityId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');

    if (details.style.display === 'none') {
        details.style.display = 'block';
        icon.classList.remove('ti-chevron-down');
        icon.classList.add('ti-chevron-up');
    } else {
        details.style.display = 'none';
        icon.classList.remove('ti-chevron-up');
        icon.classList.add('ti-chevron-down');
    }
}

// Delete activity function
function deleteActivity(activityId, element) {
    // Verificar que SweetAlert2 esté disponible
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado');
        if (confirm('¿Estás seguro de que quieres eliminar este registro? Esto eliminará el registro real del sistema (no solo la actividad de auditoría). Esta acción no se puede deshacer.')) {
            // Fallback usando fetch directo
            element.disabled = true;
            element.innerHTML = '<i class="ti ti-loader ti-sm"></i>';
            
            fetch(`/version-control/api/activity/${activityId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registro eliminado correctamente: ' + data.message);
                    window.location.href = '/version-control/audit';
                } else {
                    alert('Error: ' + data.message);
                    element.disabled = false;
                    element.innerHTML = '<i class="ti ti-trash"></i>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el registro');
                element.disabled = false;
                element.innerHTML = '<i class="ti ti-trash"></i>';
            });
        }
        return;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Estás seguro de que quieres eliminar este registro? Esto eliminará el registro real del sistema (no solo la actividad de auditoría). Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3 waves-effect waves-light',
            cancelButton: 'btn btn-label-secondary waves-effect waves-light'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.isConfirmed) {
            // Show loading state
            element.disabled = true;
            element.innerHTML = '<i class="ti ti-loader ti-sm"></i>';
            
            fetch(`/version-control/api/activity/${activityId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message,
                        customClass: {
                            confirmButton: 'btn btn-success waves-effect waves-light'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        // Remove the activity from DOM and redirect
                        element.closest('.timeline-item').remove();
                        // Redirect to main audit page
                        window.location.href = '/version-control/audit';
                    });
                } else {
                    console.error('Delete error:', data);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Error al eliminar el registro',
                        footer: data.debug ? `Debug: ${JSON.stringify(data.debug)}` : '',
                        customClass: {
                            confirmButton: 'btn btn-danger waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                    element.disabled = false;
                    element.innerHTML = '<i class="ti ti-trash"></i>';
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'Error de conexión al eliminar el registro',
                    footer: `Detalles: ${error.message}`,
                    customClass: {
                        confirmButton: 'btn btn-danger waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
                element.disabled = false;
                element.innerHTML = '<i class="ti ti-trash"></i>';
            });
        }
    });
}
</script>

<style>
/* Timeline con línea conectora simple */
.timeline {
    position: relative;
    padding: 0;
    margin: 0;
    border: none !important;
    background: none !important;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 1rem;
    bottom: 2rem;
    width: 1px;
    background-color: #d9dee3;
    z-index: 1;
}

.timeline-item {
    position: relative;
    padding-left: 3rem;
    margin-bottom: 2rem;
    border: none !important;
    background: none !important;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item::before,
.timeline-item::after {
    display: none !important;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    border: none !important;
    background: none !important;
}

.timeline-marker::before,
.timeline-marker::after {
    display: none !important;
}

.timeline-marker-indicator {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    position: relative;
    z-index: 10;
    border: none !important;
}

.timeline-content {
    background: #ffffff;
    border-radius: 0.375rem;
    padding: 1rem;
    border: none !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.timeline-content::before,
.timeline-content::after {
    display: none !important;
}

.activity-details {
    background: #f8f9fa;
    border-radius: 0.25rem;
    padding: 1rem;
    margin-top: 1rem;
    border: none !important;
}
</style>
@endsection
