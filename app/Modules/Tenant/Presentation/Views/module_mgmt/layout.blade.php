@extends('tenant::layout')

@section('content')
    <div class="container-fluid p-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">
                    <i class="bi bi-layout-text-sidebar me-2"></i>{{ $moduleDefinition->getName() }} - {{ __('tenant::tenant.module_layouts_fields') }}
                </h3>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBlockModal">
                    <i class="bi bi-folder-plus me-2"></i>{{ __('tenant::tenant.add_block') }}
                </button>
                <a href="{{ route('tenant.settings.modules.layouts') }}" class="btn btn-outline-secondary rounded-3">
                    <i class="bi bi-arrow-left me-2"></i>{{ __('tenant::tenant.cancel') }}
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

        <form action="{{ route('tenant.settings.modules.layout.update', $moduleDefinition->getName()) }}" method="POST" id="layout-settings-form">
            @csrf
            
            @php
                $blocks = $moduleDefinition->blocks()->sortBy('sequence');
            @endphp

            @foreach($blocks as $block)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-folder2-open me-2 text-primary"></i>
                                {{ app()->getLocale() == 'ar' ? ($block->getLabelAr() ?? $block->getLabel()) : ($block->getLabelEn() ?? $block->getLabel()) }}
                            </h5>
                        </div>
                        <div class="d-flex gap-2">
                             <button type="button" 
                                     class="btn btn-sm btn-success rounded-3 shadow-sm add-field-btn" 
                                     data-block-id="{{ $block->getId() }}"
                                     data-block-label="{{ app()->getLocale() == 'ar' ? ($block->getLabelAr() ?? $block->getLabel()) : ($block->getLabelEn() ?? $block->getLabel()) }}"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#addFieldModal">
                                <i class="bi bi-plus-circle me-1"></i>{{ __('tenant::tenant.add_custom_field') }}
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary rounded-3 edit-block-btn"
                                    data-id="{{ $block->getId() }}"
                                    data-label-en="{{ $block->getLabelEn() }}"
                                    data-label-ar="{{ $block->getLabelAr() }}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editBlockModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Delete button triggers hidden form out of main form -->
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger rounded-3" 
                                    onclick="window.confirmDelete('{{ route('tenant.settings.modules.block.delete', [$moduleDefinition->getName(), $block->getId()]) }}', '{{ addslashes(__('tenant::tenant.delete_block_confirm')) }}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4" width="30%">Field Label</th>
                                        <th width="15%">Internal Name</th>
                                        <th width="10%">Type</th>
                                        <th width="10%" class="text-center">{{ __('tenant::tenant.status') }}</th>
                                        <th width="10%" class="text-center">Editable</th>
                                        <th width="10%" class="text-center">Mandatory</th>
                                        <th width="15%" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $blockFields = $moduleDefinition->fields()
                                            ->filter(fn($f) => $f->getBlockId() === $block->getId())
                                            ->sortBy('sequence');
                                    @endphp
                                    
                                    @forelse($blockFields as $field)
                                        <tr class="{{ $field->isCustomField() ? 'table-info' : '' }}">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-grip-vertical text-muted me-2"></i>
                                                    <span class="fw-bold">{{ $field->getLabel() }}</span>
                                                    @if($field->isCustomField())
                                                        <span class="badge bg-primary rounded-pill ms-2">Custom</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted font-monospace">{{ $field->getFieldName() }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $field->getFieldType() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="fields[{{ $field->getId() }}][visible]"
                                                           value="1"
                                                           {{ $field->isVisible() ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="fields[{{ $field->getId() }}][editable]"
                                                           value="1"
                                                           {{ $field->isEditable() ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="fields[{{ $field->getId() }}][mandatory]"
                                                           value="1"
                                                           {{ $field->isMandatory() ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($field->isCustomField())
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary rounded-pill edit-field-btn"
                                                                data-id="{{ $field->getId() }}"
                                                                data-label="{{ $field->getLabel() }}"
                                                                data-name="{{ $field->getFieldName() }}"
                                                                data-uitype="{{ $field->getUitype() }}"
                                                                data-block-id="{{ $field->getBlockId() }}"
                                                                data-block-label="{{ app()->getLocale() == 'ar' ? ($block->getLabelAr() ?? $block->getLabel()) : ($block->getLabelEn() ?? $block->getLabel()) }}"
                                                                data-typeofdata="{{ $field->getTypeofdata() }}"
                                                                data-quickcreate="{{ $field->isQuickCreate() ? 1 : 0 }}"
                                                                data-helpinfo="{{ $field->getHelpInfo() }}"
                                                                data-defaultvalue="{{ $field->getDefaultValue() }}"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editFieldModal">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger rounded-pill" 
                                                                onclick="window.confirmDelete('{{ route('tenant.custom-fields.destroy', [$moduleDefinition->getName(), $field->getId()]) }}', '{{ addslashes(__('contacts::contacts.confirm_delete_field')) }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Standard</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                No fields in this block
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-body p-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('tenant.settings.modules.layouts') }}" class="btn btn-light rounded-3 px-4">
                        {{ __('tenant::tenant.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>{{ __('tenant::tenant.save_settings') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Hidden Master Delete Form -->
    <form id="master-delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Modals Section -->
    <div id="modals-container">
        <!-- Add Block Modal -->
        <div class="modal fade" id="addBlockModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold">{{ __('tenant::tenant.add_block') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('tenant.settings.modules.block.add', $moduleDefinition->getName()) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('tenant::tenant.label_en') }}</label>
                                <input type="text" name="label_en" class="form-control rounded-3" placeholder="English Label" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('tenant::tenant.label_ar') }}</label>
                                <input type="text" name="label_ar" class="form-control rounded-3" placeholder="Arabic Label" dir="rtl" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('tenant::tenant.add_after') }}</label>
                                <select name="after_block" class="form-select rounded-3">
                                    <option value="">-- At the start --</option>
                                    @foreach($blocks as $b)
                                        <option value="{{ $b->getSequence() }}">
                                            {{ app()->getLocale() == 'ar' ? ($b->getLabelAr() ?? $b->getLabel()) : ($b->getLabelEn() ?? $b->getLabel()) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">{{ __('tenant::tenant.add_block') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Block Modal -->
        <div class="modal fade" id="editBlockModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold">{{ __('tenant::tenant.edit_block') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editBlockForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('tenant::tenant.label_en') }}</label>
                                <input type="text" name="label_en" id="edit_label_en" class="form-control rounded-3" placeholder="English Label" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('tenant::tenant.label_ar') }}</label>
                                <input type="text" name="label_ar" id="edit_label_ar" class="form-control rounded-3" placeholder="Arabic Label" dir="rtl" required>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">{{ __('tenant::tenant.save_settings') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Field Modal -->
        <div class="modal fade" id="addFieldModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold">{{ __('contacts::contacts.add_custom_field') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('tenant.custom-fields.store', $moduleDefinition->getName()) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-12 mb-2">
                                    <div class="p-2 bg-light rounded-3 border">
                                        <strong>Adding to block:</strong> <span id="add_field_block_label_display" class="text-primary text-bold"></span>
                                    </div>
                                    <input type="hidden" name="block" id="add_field_block_id_hidden">
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Internal Name</label>
                                    <input type="text" name="fieldname" class="form-control rounded-3" placeholder="e.g., linkedin_url" required pattern="^[a-zA-Z0-9_]+$">
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Field Label</label>
                                    <input type="text" name="fieldlabel" class="form-control rounded-3" placeholder="e.g., LinkedIn Profile" required>
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Field Type</label>
                                    <select name="uitype" class="form-select rounded-3 uitype-selector" required>
                                        @foreach($fieldTypes as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 picklist-container d-none text-start">
                                    <label class="form-label fw-bold">Picklist Values (One per line)</label>
                                    <textarea name="picklist_values" class="form-control rounded-3" rows="3" placeholder="Option 1\nOption 2"></textarea>
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Default Value</label>
                                    <input type="text" name="defaultvalue" class="form-control rounded-3" placeholder="Optional">
                                </div>
                                <div class="col-md-6 text-start d-flex align-items-center mt-4 pt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="quickcreate" value="1" id="add_quickcreate">
                                        <label class="form-check-label fw-bold" for="add_quickcreate">Show in Quick Create</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">{{ __('tenant::tenant.add_custom_field') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Field Modal -->
        <div class="modal fade" id="editFieldModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold">{{ __('contacts::contacts.edit_custom_field') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editFieldForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-12 mb-2">
                                    <div class="p-2 bg-light rounded-3 border">
                                        <strong>Block:</strong> <span id="edit_field_block_label_display" class="text-primary text-bold"></span>
                                    </div>
                                    <input type="hidden" name="block" id="edit_f_block_hidden">
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Internal Name</label>
                                    <input type="text" id="edit_f_name" class="form-control rounded-3 bg-light" readonly>
                                    <small class="text-muted">Cannot be changed</small>
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Field Label</label>
                                    <input type="text" name="fieldlabel" id="edit_f_label" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 text-start">
                                    <label class="form-label fw-bold">Default Value</label>
                                    <input type="text" name="defaultvalue" id="edit_f_default" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6 text-start d-flex align-items-center mt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="quickcreate" value="1" id="edit_f_quickcreate">
                                        <label class="form-check-label fw-bold" for="edit_f_quickcreate">Show in Quick Create</label>
                                    </div>
                                </div>
                                <div class="col-md-12 text-start">
                                    <label class="form-label fw-bold">Help Text</label>
                                    <textarea name="helpinfo" id="edit_f_help" class="form-control rounded-3" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pb-4 px-4">
                            <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">{{ __('tenant::tenant.cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">{{ __('tenant::tenant.save_settings') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .table-info {
            background-color: rgba(13, 202, 240, 0.05) !important;
        }
        .font-monospace {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
        .bg-primary {
            background-color: #6366f1 !important;
        }
    </style>

    <script>
        // Global deletion handler attached to window
        window.confirmDelete = function(url, message) {
            if (confirm(message)) {
                const form = document.getElementById('master-delete-form');
                form.action = url;
                form.submit();
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Block Editing
            const editBlockModal = document.getElementById('editBlockModal');
            if (editBlockModal) {
                editBlockModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const labelEn = button.getAttribute('data-label-en');
                    const labelAr = button.getAttribute('data-label-ar');

                    const form = document.getElementById('editBlockForm');
                    form.action = `{{ url('settings/modules') }}/{{ $moduleDefinition->getName() }}/block/${id}`;
                    
                    document.getElementById('edit_label_en').value = labelEn || '';
                    document.getElementById('edit_label_ar').value = labelAr || '';
                });
            }

            // Field Creation - Set Block
            const addFieldModal = document.getElementById('addFieldModal');
            if (addFieldModal) {
                addFieldModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const blockId = button.getAttribute('data-block-id');
                    const blockLabel = button.getAttribute('data-block-label');
                    
                    if (blockId) {
                        document.getElementById('add_field_block_id_hidden').value = blockId;
                        document.getElementById('add_field_block_label_display').innerText = blockLabel;
                    }
                });
            }

            // Picklist visibility in creation/edit
            document.querySelectorAll('.uitype-selector').forEach(select => {
                select.addEventListener('change', function() {
                    const container = this.closest('form').querySelector('.picklist-container');
                    if (container) {
                        if (this.value === '15' || this.value === '33') {
                            container.classList.remove('d-none');
                        } else {
                            container.classList.add('d-none');
                        }
                    }
                });
            });

            // Field Editing
            const editFieldModal = document.getElementById('editFieldModal');
            if (editFieldModal) {
                editFieldModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const label = button.getAttribute('data-label');
                    const name = button.getAttribute('data-name');
                    const blockId = button.getAttribute('data-block-id');
                    const blockLabel = button.getAttribute('data-block-label');
                    const quick = button.getAttribute('data-quickcreate') === '1';
                    const help = button.getAttribute('data-helpinfo');
                    const def = button.getAttribute('data-defaultvalue');

                    const form = document.getElementById('editFieldForm');
                    form.action = `{{ url('settings/custom-fields') }}/{{ $moduleDefinition->getName() }}/${id}`;
                    
                    document.getElementById('edit_f_name').value = name;
                    document.getElementById('edit_f_label').value = label;
                    document.getElementById('edit_f_block_hidden').value = blockId;
                    document.getElementById('edit_field_block_label_display').innerText = blockLabel;
                    document.getElementById('edit_f_quickcreate').checked = quick;
                    document.getElementById('edit_f_help').value = help || '';
                    document.getElementById('edit_f_default').value = def || '';
                });
            }
        });
    </script>
@endsection
