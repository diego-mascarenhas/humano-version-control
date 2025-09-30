@extends('layouts.layoutMaster')

@section('title', __('Audit Details') . ' - ' . class_basename($subject))

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">{{ __('Version Control') }} / {{ __('Audit') }} /</span>
            {{ class_basename($subject) }} #{{ $subject->id }}
        </h4>
        <p class="text-muted">{{ __('Complete activity history for this record') }}</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Back to Audit Trail') }}
        </a>

        <a href="{{ route('version-control.audit.versions', ['model' => $modelSlug, 'id' => $subject->id]) }}" class="btn btn-primary">
            <i class="ti ti-versions me-1"></i>{{ __('View Versions') }}
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
                <strong>{{ __('Type') }}:</strong> {{ class_basename($subject) }}<br>
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
            </div>
            <div class="col-md-6">
                <strong>{{ __('Created') }}:</strong> {{ $subject->created_at->format('M d, Y H:i:s') }}<br>
                <strong>{{ __('Updated') }}:</strong> {{ $subject->updated_at->format('M d, Y H:i:s') }}<br>
                <strong>{{ __('Total Activities') }}:</strong> {{ $activities->total() }}<br>
                @if($activities->count() > 0)
                    <strong>{{ __('Last Activity') }}:</strong> {{ $activities->first()->created_at->format('M d, Y H:i:s') }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Activity History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ __('Activity History') }}</h5>
        <span class="badge bg-primary">{{ $activities->total() }} Activities</span>
    </div>
    <div class="card-body">
        @if($activities->count() > 0)
            <div class="timeline">
                @foreach($activities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="timeline-marker-indicator
                                @if($activity->description === 'created') bg-success
                                @elseif($activity->description === 'updated') bg-primary
                                @elseif($activity->description === 'deleted') bg-danger
                                @else bg-info
                                @endif">
                                <i class="ti ti-{{
                                    $activity->description === 'created' ? 'plus' :
                                    ($activity->description === 'updated' ? 'edit' :
                                    ($activity->description === 'deleted' ? 'trash' : 'activity'))
                                }}"></i>
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
                                        by {{ $activity->causer ? $activity->causer->name : 'System' }}
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    {{-- Restore button removed as requested --}}
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
                                            <strong>Changes:</strong>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Field</th>
                                                            @if(count($old) > 0)
                                                                <th>Old Value</th>
                                                            @endif
                                                            <th>New Value</th>
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
                                                                                <em>not set</em>
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
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
}

.timeline-marker-indicator {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 3px solid #e9ecef;
}

.activity-details {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
    margin-top: 1rem;
}
</style>
@endsection
