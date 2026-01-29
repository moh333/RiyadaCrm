@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">
                <i class="bi bi-collection me-2 text-primary"></i>{{ vtranslate($metadata->name, $metadata->name) }}
            </h3>
            @canModule($metadata->name, 'create')
            <a href="{{ route('tenant.modules.create', $metadata->name) }}"
                class="btn btn-primary rounded-3 shadow-sm px-4">
                <i class="bi bi-plus-lg me-2"></i>{{ __('tenant::tenant.add_new_record') }}
                {{ vtranslate('SINGLE_' . $metadata->name, $metadata->name) }}
            </a>
            @endcanModule
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="dynamicModuleTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                @foreach($fields as $field)
                                    @if($field->presence == 0)
                                        <th>{{ $field->getLabel($metadata->name) }}</th>
                                    @endif
                                @endforeach
                                <th>{{ __('tenant::tenant.actions') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#dynamicModuleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('tenant.modules.index', $metadata->name) }}",
                columns: [
                    @foreach($fields as $field)
                        @if($field->presence == 0)
                            { data: '{{ $field->column }}', name: '{{ $field->table }}.{{ $field->column }}' },
                        @endif
                    @endforeach
                    {
                        data: '{{ $metadata->baseTableIndex }}',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return `
                                                                    <div class="btn-group">
                                                                        <a href="/modules/{{ $metadata->name }}/${data}" class="btn btn-sm btn-outline-info rounded-3 me-1" title="{{ __('tenant::tenant.view') }}">
                                                                            <i class="bi bi-eye"></i>
                                                                        </a>
                                                                        <a href="/modules/{{ $metadata->name }}/${data}/edit" class="btn btn-sm btn-outline-primary rounded-3 me-1" title="{{ __('tenant::tenant.edit') }}">
                                                                            <i class="bi bi-pencil"></i>
                                                                        </a>
                                                                        <form action="/modules/{{ $metadata->name }}/${data}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('tenant::tenant.delete_block_confirm') }}')">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3">
                                                                                <i class="bi bi-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                `;
                        }
                    }
                ],
                order: [[0, 'desc']],
                language: {
                    url: "{{ app()->getLocale() == 'ar' ? 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' : '' }}"
                },
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
            });
        });
    </script>
@endpush