@extends('layouts.layoutMaster')

@section('title', 'User Activity - ' . $userModel->name)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">Version Control / User Activity /</span>
            {{ $userModel->name }}
        </h4>
        <p class="text-muted">All activities performed by this user</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>Back to Audit Trail
        </a>
    </div>
</div>

<!-- User Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">User Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-2">
                <div class="avatar avatar-xl">
                    @if($userModel->profile_photo_path)
                        <img src="{{ $userModel->profile_photo_url }}" alt="{{ $userModel->name }}" class="rounded">
                    @else
                        <div class="avatar-initial bg-primary rounded">
                            {{ strtoupper(substr($userModel->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-5">
                <strong>Name:</strong> {{ $userModel->name }}<br>
                <strong>Email:</strong> {{ $userModel->email }}<br>
                @if($userModel->currentTeam)
                    <strong>Team:</strong> {{ $userModel->currentTeam->name }}<br>
                @endif
                <strong>Member since:</strong> {{ $userModel->created_at->format('M d, Y') }}
            </div>
            <div class="col-md-5">
                <strong>Total Activities:</strong> {{ $activities->total() }}<br>
                <strong>Roles:</strong>
                @if($userModel->roles->count() > 0)
                    @foreach($userModel->roles as $role)
                        <span class="badge bg-primary">{{ $role->name }}</span>
                    @endforeach
                @else
                    <span class="text-muted">No roles assigned</span>
                @endif
                <br>
                @if($activities->count() > 0)
                    <strong>Last Activity:</strong> {{ $activities->first()->created_at->diffForHumans() }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Activity Timeline -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Activity Timeline</h5>
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
                                        {{ class_basename($activity->subject_type) }}
                                        @if($activity->subject)
                                            @if(isset($activity->subject->name))
                                                - {{ $activity->subject->name }}
                                            @elseif(isset($activity->subject->title))
                                                - {{ $activity->subject->title }}
                                            @else
                                                #{{ $activity->subject_id }}
                                            @endif
                                        @else
                                            #{{ $activity->subject_id }} <span class="text-danger">(deleted)</span>
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        {{ $activity->created_at->format('M d, Y H:i:s') }}
                                        ({{ $activity->created_at->diffForHumans() }})
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($activity->subject)
                                        <a href="{{ route('version-control.audit.show', [
                                            'model' => strtolower(class_basename($activity->subject_type)),
                                            'id' => $activity->subject_id
                                        ]) }}" class="btn btn-sm btn-outline-primary" title="View record details">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    @endif

                                    <button class="btn btn-sm btn-outline-secondary"
                                            onclick="toggleActivityDetails({{ $activity->id }})"
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
                                            <strong>Changes made:</strong>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Field</th>
                                                            @if(count($old) > 0)
                                                                <th>Previous Value</th>
                                                            @endif
                                                            <th>New Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                                        @foreach($attributes as $field => $newValue)
                                                                            <tr>
                                                                                <td><strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong></td>
                                                                                @if(count($old) > 0)
                                                                                    <td class="text-muted">
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
                                    <p class="text-muted mt-2">No detailed changes recorded for this activity.</p>
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
                <i class="ti ti-user-off display-4 text-muted"></i>
                <p class="text-muted mt-2">No activities found for this user</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
function toggleActivityDetails(activityId) {
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
