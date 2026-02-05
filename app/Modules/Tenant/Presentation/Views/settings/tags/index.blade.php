@extends('tenant::layout')

@section('title', __('tenant::settings.my_tags'))

@section('content')
    <div class="container-fluid py-4">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-2 fw-bold">
                    <i class="bi bi-tags text-primary me-2"></i>
                    {{ __('tenant::settings.my_tags') }}
                </h1>
                <p class="text-muted mb-0">{{ __('tenant::settings.my_tags_description') }}</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal"
                    data-bs-target="#addTagModal">
                    <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::settings.add_tag') }}
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Tags Table --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">{{ __('tenant::settings.my_tags') }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <table id="tagsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('tenant::settings.tag_name') }}</th>
                                    <th class="text-end">{{ __('tenant::settings.actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tag Cloud Settings --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-light">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-cloud text-primary me-2"></i>{{ __('tenant::settings.tag_cloud') }}
                        </h6>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enableTagCloud" {{ $showTagCloud ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="enableTagCloud">
                                {{ __('tenant::settings.enable_tag_cloud') }}
                            </label>
                        </div>
                        <p class="small text-muted mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Tag cloud displays your frequently used tags in the home dashboard
                        </p>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="card border-0 shadow-sm rounded-4 mt-3">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-lightbulb text-warning me-2"></i>{{ __('tenant::settings.tips') }}
                        </h6>
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Use tags to organize and categorize records
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Tags are personal and only visible to you
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Apply multiple tags to a single record
                            </li>
                            <li class="mb-0">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Filter records by tags for quick access
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Tag Modal --}}
    <div class="modal fade" id="addTagModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">{{ __('tenant::settings.add_tag') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTagForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tagName" class="form-label fw-semibold">
                                {{ __('tenant::settings.tag_name') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="tagName" name="tag" required
                                placeholder="Enter tag name...">
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill"
                            data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Tag Modal --}}
    <div class="modal fade" id="editTagModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">{{ __('tenant::settings.edit_tag') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editTagForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editTagId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTagName" class="form-label fw-semibold">
                                {{ __('tenant::settings.tag_name') }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="editTagName" name="tag" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary rounded-pill"
                            data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="bi bi-check-lg me-2"></i>{{ __('tenant::settings.save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteTagModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">{{ __('tenant::settings.confirm_delete') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('tenant::settings.confirm_delete_message') }}</p>
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will remove the tag from all associated records.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill"
                        data-bs-dismiss="modal">{{ __('tenant::settings.cancel') }}</button>
                    <button type="button" class="btn btn-danger rounded-pill"
                        id="confirmDeleteTag">{{ __('tenant::settings.delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            let deleteTagId = null;

            // Tags DataTable
            const tagsTable = $('#tagsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('tenant.settings.tags.data') }}',
                columns: [{
                    data: 'tag',
                    name: 'tag',
                    render: function (data) {
                        return `<span class="badge bg-primary fs-6 px-3 py-2">${data}</span>`;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                                        <button class="btn btn-sm btn-outline-primary rounded-pill me-1 edit-tag" 
                                                data-id="${row.id}" data-name="${row.tag}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill delete-tag" 
                                                data-id="${row.id}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    `;
                    }
                }
                ],
                order: [
                    [0, 'asc']
                ],
                language: {
                    emptyTable: '{{ __('tenant::settings.no_tags') }}',
                    info: 'Showing _START_ to _END_ of _TOTAL_ tags',
                    infoEmpty: '{{ __('tenant::settings.create_first_tag') }}'
                }
            });

            // Add tag
            $('#addTagForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('tenant.settings.tags.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#addTagModal').modal('hide');
                        $('#addTagForm')[0].reset();
                        tagsTable.ajax.reload();
                        showAlert('success', response.message);
                    },
                    error: function (xhr) {
                        showAlert('danger', 'Error creating tag');
                    }
                });
            });

            // Edit tag
            $(document).on('click', '.edit-tag', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                $('#editTagId').val(id);
                $('#editTagName').val(name);
                $('#editTagModal').modal('show');
            });

            $('#editTagForm').on('submit', function (e) {
                e.preventDefault();
                const id = $('#editTagId').val();
                $.ajax({
                    url: `/settings/tags/${id}`,
                    type: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tag: $('#editTagName').val()
                    },
                    success: function (response) {
                        $('#editTagModal').modal('hide');
                        tagsTable.ajax.reload();
                        showAlert('success', response.message);
                    },
                    error: function (xhr) {
                        showAlert('danger', 'Error updating tag');
                    }
                });
            });

            // Delete tag
            $(document).on('click', '.delete-tag', function () {
                deleteTagId = $(this).data('id');
                $('#deleteTagModal').modal('show');
            });

            $('#confirmDeleteTag').click(function () {
                $.ajax({
                    url: `/settings/tags/${deleteTagId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        $('#deleteTagModal').modal('hide');
                        tagsTable.ajax.reload();
                        showAlert('success', response.message);
                    },
                    error: function (xhr) {
                        $('#deleteTagModal').modal('hide');
                        showAlert('danger', 'Error deleting tag');
                    }
                });
            });

            // Tag cloud toggle
            $('#enableTagCloud').change(function () {
                $.ajax({
                    url: '{{ route('tenant.settings.tags.tag-cloud') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        enabled: $(this).is(':checked')
                    },
                    success: function (response) {
                        showAlert('success', response.message);
                    }
                });
            });

            function showAlert(type, message) {
                const alert = `
                            <div class="alert alert-${type} alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                $('.container-fluid').prepend(alert);
                setTimeout(() => {
                    $('.alert').fadeOut();
                }, 3000);
            }
        });
    </script>
@endpush