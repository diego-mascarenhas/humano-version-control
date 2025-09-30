@extends('layouts.layoutMaster')

@section('title', 'Version History - ' . class_basename($subject))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">Version Control / Versions /</span>
            {{ class_basename($subject) }} #{{ $subject->id }}
        </h4>
        <p class="text-muted">Version history and restoration options</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.show', ['model' => $model, 'id' => $subject->id]) }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>Back to Details
        </a>
    </div>
</div>

<!-- Record Summary -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5>{{ class_basename($subject) }}
                    @if(isset($subject->name))
                        - {{ $subject->name }}
                    @elseif(isset($subject->title))
                        - {{ $subject->title }}
                    @endif
                </h5>
                <p class="text-muted mb-0">
                    Created {{ $subject->created_at->format('M d, Y H:i:s') }} •
                    Last modified {{ $subject->updated_at->format('M d, Y H:i:s') }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="badge bg-primary fs-6">{{ $activities->count() }} Versions</div>
            </div>
        </div>
    </div>
</div>

<!-- Version List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Available Versions</h5>
    </div>
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="row">
                @foreach($activities as $index => $activity)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100
                            @if($index === 0) border-primary @endif">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="card-title mb-1">
                                            Version #{{ $activities->count() - $index }}
                                            @if($index === 0)
                                                <span class="badge bg-primary ms-2">Current</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            {{ $activity->created_at->format('M d, Y H:i:s') }}
                                        </small>
                                    </div>
                                    <div class="avatar avatar-sm">
                                        <div class="avatar-initial bg-{{
                                            $activity->description === 'created' ? 'success' :
                                            ($activity->description === 'updated' ? 'primary' : 'secondary')
                                        }} rounded">
                                            <i class="ti ti-{{
                                                $activity->description === 'created' ? 'plus' :
                                                ($activity->description === 'updated' ? 'edit' : 'activity')
                                            }}"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong>Action:</strong> {{ ucfirst($activity->description) }}<br>
                                    <strong>By:</strong> {{ $activity->causer ? $activity->causer->name : 'System' }}<br>
                                    <strong>Time:</strong> {{ $activity->created_at->diffForHumans() }}
                                </div>

                                @if($activity->properties->isNotEmpty())
                                    @php
                                        $attributes = $activity->properties->get('attributes', []);
                                    @endphp
                                    @if(count($attributes) > 0)
                                        <div class="mb-3">
                                            <strong>Changes:</strong> {{ count($attributes) }} field(s)
                                            <small class="d-block text-muted">
                                                {{ implode(', ', array_keys($attributes)) }}
                                            </small>
                                        </div>
                                    @endif
                                @endif

                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary flex-fill"
                                            onclick="showVersionDetails({{ $activity->id }})">
                                        <i class="ti ti-eye me-1"></i>View Details
                                    </button>

                                    @can('restore-versions')
                                        @if($index !== 0)
                                            <a href="{{ route('version-control.restore.preview', [
                                                'model' => $model,
                                                'id' => $subject->id,
                                                'version' => $activity->id
                                            ]) }}" class="btn btn-sm btn-success">
                                                <i class="ti ti-restore"></i>
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Compare Versions -->
            @if($activities->count() > 1)
                <div class="mt-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6>Compare Versions</h6>
                            <form action="{{ route('version-control.audit.compare') }}" method="GET">
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label">First Version</label>
                                        <select name="activity1" class="form-select" required>
                                            <option value="">Select version...</option>
                                            @foreach($activities as $index => $activity)
                                                <option value="{{ $activity->id }}">
                                                    Version #{{ $activities->count() - $index }} - {{ $activity->created_at->format('M d, H:i') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Second Version</label>
                                        <select name="activity2" class="form-select" required>
                                            <option value="">Select version...</option>
                                            @foreach($activities as $index => $activity)
                                                <option value="{{ $activity->id }}">
                                                    Version #{{ $activities->count() - $index }} - {{ $activity->created_at->format('M d, H:i') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ti ti-compare me-1"></i>Compare Versions
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="ti ti-versions display-4 text-muted"></i>
                <p class="text-muted mt-2">No versions available for this record</p>
            </div>
        @endif
    </div>
</div>

<!-- Version Details Modal -->
<div class="modal fade" id="versionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Version Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="versionDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
function showVersionDetails(activityId) {
    // ✅ NUEVA IMPLEMENTACIÓN - Usar nuevo sistema dinámico
    // Redirigir al nuevo sistema de acceso directo por Activity ID
    window.location.href = "{{ route('version-control.activity.show', ':activityId') }}".replace(':activityId', activityId);
}

// ✅ FUNCIÓN ALTERNATIVA - Modal con datos existentes (si se prefiere mantener modal)
function showVersionDetailsModal(activityId) {
    // Obtener datos del servidor via AJAX
    fetch(`/version-control/api/activity/${activityId}/versions`)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                alert('Activity not found');
                return;
            }

            const activity = data[0]; // Primera actividad es la que queremos mostrar
            
            let content = `
                <div class="mb-3">
                    <strong>Action:</strong> ${activity.description.charAt(0).toUpperCase() + activity.description.slice(1)}<br>
                    <strong>Date:</strong> ${new Date(activity.created_at).toLocaleString()}<br>
                    <strong>User:</strong> ${activity.causer || 'System'}
                </div>
            `;

            if (activity.properties && activity.properties.attributes) {
                content += '<h6>Changed Fields:</h6>';
                content += '<div class="table-responsive">';
                content += '<table class="table table-sm">';
                content += '<thead><tr><th>Field</th><th>Old Value</th><th>New Value</th></tr></thead>';
                content += '<tbody>';

                const attributes = activity.properties.attributes;
                const oldValues = activity.properties.old || {};

                for (const [field, newValue] of Object.entries(attributes)) {
                    const oldValue = oldValues[field];
                    
                    let displayOldValue = oldValue;
                    let displayNewValue = newValue;
                    
                    // Format values for display
                    [displayOldValue, displayNewValue].forEach((value, index) => {
                        if (typeof value === 'object' && value !== null) {
                            if (index === 0) displayOldValue = JSON.stringify(value, null, 2);
                            else displayNewValue = JSON.stringify(value, null, 2);
                        } else if (value === null) {
                            if (index === 0) displayOldValue = '<em class="text-muted">null</em>';
                            else displayNewValue = '<em class="text-muted">null</em>';
                        } else if (value === '') {
                            if (index === 0) displayOldValue = '<em class="text-muted">(empty)</em>';
                            else displayNewValue = '<em class="text-muted">(empty)</em>';
                        }
                    });

                    const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    content += `<tr>
                        <td><strong>${fieldName}</strong></td>
                        <td>${displayOldValue || '<em class="text-muted">N/A</em>'}</td>
                        <td>${displayNewValue}</td>
                    </tr>`;
                }

                content += '</tbody></table></div>';

                // ✅ BOTÓN PARA VER DETALLES COMPLETOS
                content += `<div class="mt-3 text-center">
                    <a href="/version-control/activity/${activityId}" class="btn btn-primary">
                        <i class="ti ti-external-link me-1"></i>View Full Details
                    </a>
                </div>`;
            } else {
                content += '<p class="text-muted">No detailed information available for this version.</p>';
                content += `<div class="mt-3 text-center">
                    <a href="/version-control/activity/${activityId}" class="btn btn-primary">
                        <i class="ti ti-external-link me-1"></i>View Activity Details
                    </a>
                </div>`;
            }

            document.getElementById('versionDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('versionDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error fetching activity details:', error);
            alert('Error loading activity details');
        });
}
</script>
@endsection
