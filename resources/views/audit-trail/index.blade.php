@extends('layouts.layoutMaster')

@section('title', __('Audit Trail'))

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
<script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">{{ __('Audit Trail') }}</h4>
        <p class="text-muted">{{ __('Complete history of all system changes') }}</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>{{ __('Back to Dashboard') }}
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">{{ __('Filters') }}</h5>
    </div>
    <div class="card-body">
        <form id="filters-form" class="row g-3">
            <div class="col-md-3">
                <label for="model-filter" class="form-label">{{ __('Model Type') }}</label>
                <select id="model-filter" class="form-select">
                    <option value="">{{ __('All Models') }}</option>
                    @foreach($modelTypes as $type => $name)
                        <option value="{{ $type }}" {{ $model === $type ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="user-filter" class="form-label">{{ __('User') }}</label>
                <select id="user-filter" class="form-select">
                    <option value="">{{ __('All Users') }}</option>
                    @foreach($users as $userId => $userName)
                        <option value="{{ $userId }}">{{ $userName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="date-from" class="form-label">{{ __('Date From') }}</label>
                <input type="date" id="date-from" class="form-control">
            </div>

            <div class="col-md-2">
                <label for="date-to" class="form-label">{{ __('Date To') }}</label>
                <input type="date" id="date-to" class="form-control">
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="ti ti-filter me-1"></i>{{ __('Filter') }}
                </button>
                <button type="button" id="clear-filters" class="btn btn-outline-secondary">
                    <i class="ti ti-x me-1"></i>{{ __('Clear') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Activities Table -->
<div class="card">
    <div class="card-body">
        <table id="activities-table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>{{ __('Model') }}</th>
                    <th>{{ __('Record') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#activities-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('version-control.api.activities') }}",
            data: function(d) {
                d.model = $('#model-filter').val();
                d.user_id = $('#user-filter').val();
                d.date_from = $('#date-from').val();
                d.date_to = $('#date-to').val();
            }
        },
        columns: [
            { data: 'model_name', name: 'subject_type' },
            { data: 'subject_name', name: 'subject_id' },
            { data: 'description', name: 'description' },
            { data: 'causer_name', name: 'causer.name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[4, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Handle filters
    $('#filters-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('#clear-filters').on('click', function() {
        $('#filters-form')[0].reset();
        $('#model-filter, #user-filter').trigger('change');
        table.draw();
    });

    // Initialize Select2
    $('#model-filter, #user-filter').select2({
        width: '100%'
    });

    // Initialize date pickers
    $('#date-from, #date-to').flatpickr({
        dateFormat: 'Y-m-d'
    });
});
</script>
@endsection