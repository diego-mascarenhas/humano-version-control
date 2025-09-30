<div class="d-flex justify-content-center align-items-center">
    @if($activity->subject)
        <a href="{{ route('version-control.audit.show', [
            'model' => strtolower(class_basename($activity->subject_type)),
            'id' => $activity->subject_id
        ]) }}" class="text-body me-2" title="View Details">
            <i class="ti ti-eye ti-sm"></i>
        </a>

        @can('restore-versions')
            <a href="{{ route('version-control.restore.preview', [
                'model' => strtolower(class_basename($activity->subject_type)),
                'id' => $activity->subject_id,
                'version' => $activity->id
            ]) }}" class="text-success me-2" title="Preview Restore">
                <i class="ti ti-restore ti-sm"></i>
            </a>
        @endcan
    @endif

    <a href="#" class="text-info" onclick="showActivityDetails({{ $activity->id }})" title="Show Changes">
        <i class="ti ti-list-details ti-sm"></i>
    </a>
</div>
