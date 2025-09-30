{{-- ✅ VISTA ESPECÍFICA PARA ACTIVIDADES DE RESTAURACIÓN --}}

@php
    $properties = $activity->properties ?? collect();
    $restoredFromVersion = $properties->get('restored_from_version');
    $restoredFields = $properties->get('restored_fields', []);
    $previousState = $properties->get('previous_state', []);
    $restoredState = $properties->get('restored_state', []);
@endphp

<!-- Restoration Alert -->
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="ti ti-circle-check me-2"></i>
    <div class="flex-grow-1">
        <strong>Record Restored Successfully</strong><br>
        <small>This record was restored from a previous version on {{ $activity->created_at->format('M d, Y \a\t H:i:s') }}</small>
    </div>
    <div class="text-end">
        <span class="badge bg-success fs-6">
            <i class="ti ti-restore me-1"></i>Restoration
        </span>
    </div>
</div>

<!-- Restoration Details -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="ti ti-info-circle me-2"></i>Restoration Details
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <strong class="text-primary">Restored by:</strong>
                    <div>{{ $activity->causer ? $activity->causer->name : 'System' }}</div>
                </div>
                
                <div class="mb-3">
                    <strong class="text-primary">Restoration date:</strong>
                    <div>{{ $activity->created_at->format('M d, Y \a\t H:i:s') }}</div>
                    <small class="text-muted">({{ $activity->created_at->diffForHumans() }})</small>
                </div>

                @if($restoredFromVersion)
                    <div class="mb-3">
                        <strong class="text-primary">Restored from version:</strong>
                        <div>{{ $restoredFromVersion }}</div>
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <strong class="text-primary">Record type:</strong>
                    <div>{{ class_basename($subject) }}</div>
                </div>

                <div class="mb-3">
                    <strong class="text-primary">Record ID:</strong>
                    <div>#{{ $subject->id }}</div>
                </div>

                @if(isset($subject->name))
                    <div class="mb-3">
                        <strong class="text-primary">Record name:</strong>
                        <div>{{ $subject->name }}</div>
                    </div>
                @elseif(isset($subject->title))
                    <div class="mb-3">
                        <strong class="text-primary">Record title:</strong>
                        <div>{{ $subject->title }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Restored Fields -->
@if(!empty($restoredFields) && !empty($restoredState))
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="ti ti-list-details me-2"></i>Restored Fields ({{ count($restoredFields) }})
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="25%">Field</th>
                            <th width="35%">Previous Value</th>
                            <th width="35%">Restored Value</th>
                            <th width="5%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($restoredFields as $field)
                            <tr>
                                <td>
                                    <strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong>
                                </td>
                                <td>
                                    <div class="restoration-value previous-value">
                                        @php
                                            $previousValue = $previousState[$field] ?? null;
                                        @endphp
                                        @if(is_array($previousValue) || is_object($previousValue))
                                            <code class="text-muted">{{ json_encode($previousValue, JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_null($previousValue))
                                            <em class="text-muted">null</em>
                                        @elseif($previousValue === '')
                                            <em class="text-muted">(empty)</em>
                                        @else
                                            {{ $previousValue }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="restoration-value restored-value">
                                        @php
                                            $restoredValue = $restoredState[$field] ?? null;
                                        @endphp
                                        @if(is_array($restoredValue) || is_object($restoredValue))
                                            <code class="text-success">{{ json_encode($restoredValue, JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_null($restoredValue))
                                            <em class="text-success">null</em>
                                        @elseif($restoredValue === '')
                                            <em class="text-success">(empty)</em>
                                        @else
                                            <span class="text-success">{{ $restoredValue }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="ti ti-check"></i>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Restoration Timeline -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="ti ti-timeline me-2"></i>Complete History
        </h5>
        <p class="card-subtitle mt-1 mb-0">All activities for this record including the restoration</p>
    </div>
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="timeline">
                @foreach($activities as $historyActivity)
                    <div class="timeline-item {{ $historyActivity->id == $activity->id ? 'timeline-item-highlighted' : '' }}">
                        <div class="timeline-marker 
                            @if($historyActivity->description === 'restored') bg-success
                            @elseif($historyActivity->description === 'created') bg-primary
                            @elseif($historyActivity->description === 'updated') bg-warning
                            @else bg-secondary
                            @endif">
                            <i class="ti ti-{{ 
                                $historyActivity->description === 'restored' ? 'restore' : 
                                ($historyActivity->description === 'created' ? 'plus' : 
                                ($historyActivity->description === 'updated' ? 'edit' : 'activity'))
                            }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <h6 class="mb-1">
                                    {{ ucfirst($historyActivity->description) }}
                                    @if($historyActivity->id == $activity->id)
                                        <span class="badge bg-success ms-2">Current</span>
                                    @endif
                                </h6>
                                <small class="text-muted">
                                    {{ $historyActivity->created_at->format('M d, Y H:i:s') }} 
                                    ({{ $historyActivity->created_at->diffForHumans() }})
                                    by {{ $historyActivity->causer ? $historyActivity->causer->name : 'System' }}
                                </small>
                            </div>
                            
                            @if($historyActivity->description === 'restored')
                                <div class="alert alert-success alert-sm mt-2 mb-0">
                                    <strong>Restoration completed:</strong> 
                                    {{ count($restoredFields) }} field(s) restored
                                    @if($restoredFromVersion)
                                        from version {{ $restoredFromVersion }}
                                    @endif
                                </div>
                            @else
                                @php
                                    $historyProperties = $historyActivity->properties ?? collect();
                                    $attributes = $historyProperties->get('attributes', []);
                                @endphp
                                @if(!empty($attributes))
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Changed: {{ implode(', ', array_keys($attributes)) }}
                                        </small>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="ti ti-history display-4 text-muted"></i>
                <p class="text-muted mt-2">No activity history found</p>
            </div>
        @endif
    </div>
</div>

<style>
.restoration-value {
    padding: 0.5rem;
    border-radius: 0.375rem;
    font-family: monospace;
    font-size: 0.875rem;
}

.previous-value {
    background-color: #fff2f2;
    border-left: 3px solid #dc3545;
}

.restored-value {
    background-color: #f0f9ff;
    border-left: 3px solid #198754;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item-highlighted {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-left: -1rem;
    margin-right: -1rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
}

.timeline-content {
    padding-left: 1rem;
}

.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}
</style>
