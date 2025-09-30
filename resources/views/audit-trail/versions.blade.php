@extends('layouts.layoutMaster')

@section('title', __('Version History') . ' - ' . class_basename($subject))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">{{ __('Version Control') }} / {{ __('Versions') }} /</span>
            {{ class_basename($subject) }} #{{ $subject->id }}
        </h4>
        <p class="text-muted">{{ __('Version history and restoration options') }}</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.show', ['model' => $model, 'id' => $subject->id]) }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Back to Details') }}
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
                    {{ __('Created') }} {{ $subject->created_at->format('M d, Y H:i:s') }} •
                    {{ __('Last modified') }} {{ $subject->updated_at->format('M d, Y H:i:s') }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="badge bg-primary fs-6">{{ $activities->count() }} {{ $activities->count() == 1 ? __('Version') : __('Versions') }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Version List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Available Versions') }}</h5>
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
                                            {{ __('Version') }} #{{ $activities->count() - $index }}
                                            @if($index === 0)
                                                <span class="badge bg-primary ms-2">{{ __('Current') }}</span>
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
                                    <strong>{{ __('Action') }}:</strong> {{ __(ucfirst($activity->description)) }}<br>
                                    <strong>{{ __('By') }}:</strong> {{ $activity->causer ? $activity->causer->name : __('System') }}<br>
                                    <strong>{{ __('Time') }}:</strong> {{ $activity->created_at->diffForHumans() }}
                                </div>

                                @if($activity->properties->isNotEmpty())
                                    @php
                                        $attributes = $activity->properties->get('attributes', []);
                                    @endphp
                                    @if(count($attributes) > 0)
                                        <div class="mb-3">
                                            <strong>{{ __('Changes') }}:</strong> {{ count($attributes) }} {{ count($attributes) == 1 ? __('field') : __('fields') }}
                                            <small class="d-block text-muted">
                                                {{ implode(', ', array_keys($attributes)) }}
                                            </small>
                                        </div>
                                    @endif
                                @endif

                                {{-- Restore button removed as requested --}}
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
                                                    {{ __('Version') }} #{{ $activities->count() - $index }} - {{ $activity->created_at->format('M d, H:i') }}
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
                                                    {{ __('Version') }} #{{ $activities->count() - $index }} - {{ $activity->created_at->format('M d, H:i') }}
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

{{-- Modal removed since View Details buttons were removed --}}
@endsection

{{-- No JavaScript needed anymore since View Details buttons were removed --}}
