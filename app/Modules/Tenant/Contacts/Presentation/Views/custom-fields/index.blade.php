@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">{{ __('contacts::contacts.custom_fields') }} - {{ $module }}</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('tenant.dashboard') }}">{{ __('contacts::contacts.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ $module }}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('contacts::contacts.custom_fields') }}
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('tenant.custom-fields.create', ['module' => $module]) }}"
                    class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-plus-lg"></i>
                    <span>{{ __('contacts::contacts.add_custom_field') }}</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold">{{ __('contacts::contacts.manage_custom_fields') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-4 py-3">{{ __('contacts::contacts.field_name') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.field_label') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.field_type') }}</th>
                            <th class="py-3">{{ __('contacts::contacts.column_name') }}</th>
                            <th class="py-3 text-center">{{ __('contacts::contacts.mandatory') }}</th>
                            <th class="pe-4 py-3 text-end">{{ __('contacts::contacts.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customFields as $field)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold">{{ $field->getFieldName() }}</span>
                                </td>
                                <td>
                                    {{-- Localized Label --}}
                                    {{ __($field->getFieldLabel()) }}
                                    @if(__($field->getFieldLabel()) !== $field->getFieldLabel())
                                        <small class="text-muted d-block">({{ $field->getFieldLabel() }})</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-soft-primary text-primary rounded-pill px-3">
                                        {{ $field->getUitype()->label() }}
                                    </span>
                                </td>
                                <td>
                                    <code class="text-muted">{{ $field->getColumnName() }}</code>
                                </td>
                                <td class="text-center">
                                    @if($field->isMandatory())
                                        <span class="badge bg-soft-danger text-danger rounded-pill px-3">
                                            {{ __('contacts::contacts.required') }}
                                        </span>
                                    @else
                                        <span class="badge bg-soft-secondary text-secondary rounded-pill px-3">
                                            {{ __('contacts::contacts.optional') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('tenant.custom-fields.edit', ['module' => $module, 'id' => $field->getFieldId()]) }}"
                                            class="btn btn-sm btn-soft-primary rounded-2"
                                            title="{{ __('contacts::contacts.edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="{{ route('tenant.custom-fields.destroy', ['module' => $module, 'id' => $field->getFieldId()]) }}"
                                            method="POST"
                                            onsubmit="return confirm('{{ __('contacts::contacts.confirm_delete_field') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger rounded-2"
                                                title="{{ __('contacts::contacts.delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-ui-checks text-muted" style="font-size: 3rem;"></i>
                                        <p class="mt-3 text-muted">{{ __('contacts::contacts.no_custom_fields') }}</p>
                                        <a href="{{ route('tenant.custom-fields.create', ['module' => $module]) }}"
                                            class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> {{ __('contacts::contacts.add_custom_field') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .bg-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
        }

        .bg-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
        }

        .bg-soft-secondary {
            background-color: #f1f5f9;
            color: #64748b;
        }

        .btn-soft-danger {
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
        }

        .btn-soft-danger:hover {
            background-color: #ef4444;
            color: white;
        }

        .btn-soft-primary {
            background-color: #eef2ff;
            color: #6366f1;
            border: none;
        }

        .btn-soft-primary:hover {
            background-color: #6366f1;
            color: white;
        }
    </style>
@endsection