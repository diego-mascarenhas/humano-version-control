@extends('layouts.layoutMaster')

@section('title', __('Restore Preview'))

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
    <div class="d-flex flex-column justify-content-center">
        <h4 class="mb-1 mt-3">
            <span class="text-muted fw-light">{{ __('Version Control') }}/</span>
            {{ __('Restore') }} {{ class_basename($activity->subject_type) }}
        </h4>
        <p class="text-muted">{{ __('Preview and restore from version :date', ['date' => $activity->created_at->format('M d, Y H:i:s')]) }}</p>
    </div>
    <div class="d-flex align-content-center flex-wrap gap-3">
        <a href="{{ route('version-control.audit.show', ['model' => $model, 'id' => $subject->id]) }}" class="btn btn-label-secondary waves-effect" style="cursor: pointer !important;">
            <i class="ti ti-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- Version Info -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Version Information') }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>{{ __('Action') }}:</strong> {{ __(ucfirst($activity->description)) }}<br>
                <strong>{{ __('Date') }}:</strong> {{ $activity->created_at->format('M d, Y H:i:s') }}<br>
                <strong>{{ __('User') }}:</strong> {{ $activity->causer ? $activity->causer->name : __('System') }}
            </div>
            <div class="col-md-6">
                <strong>{{ __('Record') }}:</strong> {{ class_basename($activity->subject_type) }} #{{ $subject->id }}<br>
                <strong>{{ __('Current Time') }}:</strong> {{ now()->format('M d, Y H:i:s') }}<br>
                <strong>{{ __('Age') }}:</strong> {{ $activity->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
</div>

<!-- Restore Form -->
<form id="restore-form" action="{{ route('version-control.restore.execute', ['model' => $model, 'id' => $subject->id, 'version' => $activity->id]) }}" method="POST">
    @csrf

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Field Comparison') }}</h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="select-all">{{ __('Select All') }}</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="select-none">{{ __('Select None') }}</button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="select-changed">{{ __('Only Changed') }}</button>
            </div>
        </div>
        <div class="card-body">
            @if(count($differences) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                    </div>
                                </th>
                                <th>{{ __('Field') }}</th>
                                <th>{{ __('Current Value') }}</th>
                                <th>{{ __('Version Value') }}</th>
                                <th class="text-center">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($differences as $field => $data)
                                <tr class="{{ $data['changed'] ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input field-checkbox"
                                                   type="checkbox"
                                                   name="fields[]"
                                                   value="{{ $field }}"
                                                   id="field-{{ $field }}"
                                                   {{ $data['changed'] ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <label for="field-{{ $field }}" class="form-label mb-0">
                                            <strong>{{ ucwords(str_replace('_', ' ', $field)) }}</strong>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;"
                                             title="{{ is_array($data['current']) || is_object($data['current']) ? json_encode($data['current']) : $data['current'] }}">
                                            @if(is_array($data['current']) || is_object($data['current']))
                                                <code>{{ json_encode($data['current']) }}</code>
                                            @elseif(is_null($data['current']))
                                                <span class="text-muted">null</span>
                                            @elseif($data['current'] === '')
                                                <span class="text-muted">(empty)</span>
                                            @else
                                                {{ $data['current'] }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;"
                                             title="{{ is_array($data['version']) || is_object($data['version']) ? json_encode($data['version']) : ($data['version'] ?? '') }}">
                                            @if(is_array($data['version']) || is_object($data['version']))
                                                <code>{{ json_encode($data['version']) }}</code>
                                            @elseif(is_null($data['version']))
                                                <span class="text-muted">null</span>
                                            @elseif($data['version'] === '')
                                                <span class="text-muted">(empty)</span>
                                            @else
                                                {{ $data['version'] }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($data['changed'])
                                            <span class="badge bg-warning">{{ __('Changed') }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Same') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="ti ti-info-circle display-4 text-muted"></i>
                    <p class="text-muted mt-2">{{ __('No field differences found') }}</p>
                </div>
            @endif
        </div>

        @if(count($differences) > 0)
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirm-restore" required>
                            <label class="form-check-label" for="confirm-restore">
                                {{ __('I understand that this action will modify the current record') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back()">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success" id="restore-button" disabled>
                            <i class="ti ti-restore me-1"></i>{{ __('Restore Selected Fields') }}
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</form>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Test SweetAlert2
    console.log('SweetAlert2 loaded:', typeof Swal !== 'undefined');
    
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado');
    }
    
    // Handle select all/none buttons
    $('#select-all').click(function() {
        $('.field-checkbox').prop('checked', true);
        $('#select-all-checkbox').prop('checked', true);
    });

    $('#select-none').click(function() {
        $('.field-checkbox').prop('checked', false);
        $('#select-all-checkbox').prop('checked', false);
    });

    $('#select-changed').click(function() {
        $('.field-checkbox').prop('checked', false);
        $('tr.table-warning .field-checkbox').prop('checked', true);
        updateSelectAllCheckbox();
    });

    // Handle select all checkbox
    $('#select-all-checkbox').change(function() {
        $('.field-checkbox').prop('checked', this.checked);
    });

    // Handle individual checkboxes
    $('.field-checkbox').change(function() {
        updateSelectAllCheckbox();
    });

    // Handle confirmation checkbox
    $('#confirm-restore').change(function() {
        $('#restore-button').prop('disabled', !this.checked);
    });

    function updateSelectAllCheckbox() {
        const total = $('.field-checkbox').length;
        const checked = $('.field-checkbox:checked').length;
        $('#select-all-checkbox').prop('checked', total === checked);
    }

    // Handle form submission
    $('#restore-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        // Verificar SweetAlert2
        if (typeof Swal === 'undefined') {
            alert('SweetAlert2 no está disponible. Usando confirm nativo.');
            const selectedFields = $('.field-checkbox:checked').length;
            if (selectedFields === 0) {
                alert('Por favor selecciona al menos un campo para restaurar');
                return false;
            }
            if (!confirm(`¿Estás seguro de que quieres restaurar ${selectedFields} campo(s)? Esta acción no se puede deshacer.`)) {
                return false;
            }
            form.submit();
            return;
        }
        
        const selectedFields = $('.field-checkbox:checked').length;
        if (selectedFields === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin campos seleccionados',
                text: 'Por favor selecciona al menos un campo para restaurar',
                customClass: {
                    confirmButton: 'btn btn-warning waves-effect waves-light'
                },
                buttonsStyling: false
            });
            return false;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas restaurar ${selectedFields} campo(s)? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'btn btn-primary me-3 waves-effect waves-light',
                cancelButton: 'btn btn-label-secondary waves-effect waves-light'
            },
            buttonsStyling: false
        }).then(function (result) {
            if (result.value) {
                // Submit form
                $.post(form.action, $(form).serialize())
                    .done(function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Restaurado!',
                                text: response.message || 'Los campos han sido restaurados exitosamente',
                                customClass: {
                                    confirmButton: 'btn btn-success waves-effect waves-light'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                // Redirect to audit trail
                                window.location.href = '{{ route("version-control.audit.show", ["model" => $model, "id" => $subject->id]) }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Ocurrió un error durante la restauración',
                                customClass: {
                                    confirmButton: 'btn btn-danger waves-effect waves-light'
                                },
                                buttonsStyling: false
                            });
                        }
                    })
                    .fail(function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al restaurar campos. Por favor intenta de nuevo.',
                            customClass: {
                                confirmButton: 'btn btn-danger waves-effect waves-light'
                            },
                            buttonsStyling: false
                        });
                    });
            }
        });
    });
});
</script>
@endsection
