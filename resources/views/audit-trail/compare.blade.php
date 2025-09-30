@extends('layouts.layoutMaster')

@section('title', 'Compare Versions')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">Version Control /</span>
            Compare Versions
        </h4>
        <p class="text-muted">Side-by-side comparison of two versions</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>Back to Audit Trail
        </a>
    </div>
</div>

<!-- Comparison Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                        <div class="avatar-initial bg-primary rounded">
                            <i class="ti ti-clock"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">Version A</h6>
                        <small class="text-muted">
                            {{ $activity1->created_at->format('M d, Y H:i:s') }} by
                            {{ $activity1->causer ? $activity1->causer->name : 'System' }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3">
                        <div class="avatar-initial bg-success rounded">
                            <i class="ti ti-clock"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">Version B</h6>
                        <small class="text-muted">
                            {{ $activity2->created_at->format('M d, Y H:i:s') }} by
                            {{ $activity2->causer ? $activity2->causer->name : 'System' }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comparison Results -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Field Comparison</h5>
    </div>
    <div class="card-body">
        @if(count($differences) > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="25%">Field</th>
                            <th width="35%">Version A Value</th>
                            <th width="35%">Version B Value</th>
                            <th width="5%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($differences as $field => $values)
                            @php
                                $isDifferent = $values['activity1'] !== $values['activity2'];
                            @endphp
                            <tr class="{{ $isDifferent ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong>
                                </td>
                                <td>
                                    <div class="comparison-value {{ $isDifferent ? 'bg-light border rounded p-2' : '' }}"
                                         title="{{ is_array($values['activity1']) || is_object($values['activity1']) ? json_encode($values['activity1']) : ($values['activity1'] ?? '') }}">
                                        @if(is_array($values['activity1']) || is_object($values['activity1']))
                                            <code>{{ json_encode($values['activity1'], JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_null($values['activity1']))
                                            <span class="text-muted fst-italic">null</span>
                                        @elseif($values['activity1'] === '')
                                            <span class="text-muted fst-italic">(empty)</span>
                                        @else
                                            {{ $values['activity1'] }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="comparison-value {{ $isDifferent ? 'bg-light border rounded p-2' : '' }}"
                                         title="{{ is_array($values['activity2']) || is_object($values['activity2']) ? json_encode($values['activity2']) : ($values['activity2'] ?? '') }}">
                                        @if(is_array($values['activity2']) || is_object($values['activity2']))
                                            <code>{{ json_encode($values['activity2'], JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_null($values['activity2']))
                                            <span class="text-muted fst-italic">null</span>
                                        @elseif($values['activity2'] === '')
                                            <span class="text-muted fst-italic">(empty)</span>
                                        @else
                                            {{ $values['activity2'] }}
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($isDifferent)
                                        <i class="ti ti-x text-warning" title="Different"></i>
                                    @else
                                        <i class="ti ti-check text-success" title="Same"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="mt-4">
                <div class="row">
                    @php
                        $changedFields = collect($differences)->filter(fn($values) => $values['activity1'] !== $values['activity2']);
                        $unchangedFields = collect($differences)->filter(fn($values) => $values['activity1'] === $values['activity2']);
                    @endphp

                    <div class="col-md-4">
                        <div class="card bg-warning-subtle">
                            <div class="card-body text-center">
                                <h4 class="text-warning">{{ $changedFields->count() }}</h4>
                                <p class="mb-0">Fields Changed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success-subtle">
                            <div class="card-body text-center">
                                <h4 class="text-success">{{ $unchangedFields->count() }}</h4>
                                <p class="mb-0">Fields Unchanged</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-primary-subtle">
                            <div class="card-body text-center">
                                <h4 class="text-primary">{{ count($differences) }}</h4>
                                <p class="mb-0">Total Fields</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($changedFields->count() > 0)
                <div class="mt-4">
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-info-circle me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Found {{ $changedFields->count() }} difference(s) between the versions.</strong>
                                <br>
                                <small>
                                    Changed fields:
                                    {{ $changedFields->keys()->map(fn($field) => ucwords(str_replace('_', ' ', $field)))->join(', ') }}
                                </small>
                            </div>
                            {{-- Restore buttons removed as requested --}}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="ti ti-equal display-4 text-success"></i>
                <h5 class="mt-3 text-success">Versions are Identical</h5>
                <p class="text-muted">No differences found between the selected versions.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
// Add some visual enhancements for better comparison viewing
$(document).ready(function() {
    // Highlight different values on hover
    $('.comparison-value').hover(
        function() {
            const row = $(this).closest('tr');
            if (row.hasClass('table-warning')) {
                row.addClass('table-hover');
            }
        },
        function() {
            $(this).closest('tr').removeClass('table-hover');
        }
    );

    // Add click to expand large values
    $('.comparison-value code').each(function() {
        const $this = $(this);
        const text = $this.text();
        if (text.length > 100) {
            $this.addClass('expandable-code');
            $this.attr('title', 'Click to expand/collapse');
            $this.css({
                'max-height': '100px',
                'overflow': 'hidden',
                'cursor': 'pointer'
            });

            $this.click(function() {
                if ($this.css('max-height') === '100px') {
                    $this.css('max-height', 'none');
                } else {
                    $this.css('max-height', '100px');
                }
            });
        }
    });
});
</script>

<style>
.comparison-value {
    min-height: 40px;
    word-break: break-word;
}

.expandable-code {
    transition: max-height 0.3s ease;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
}
</style>
@endsection
