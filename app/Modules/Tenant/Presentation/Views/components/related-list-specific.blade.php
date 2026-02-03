@props(['module', 'recordId', 'relation', 'activities' => []])

@php
    $targetModuleName = $relation->target_module_name;
    $relationId = $relation->relation_id;
@endphp

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-link-45deg text-primary me-2"></i>
                    {{ vtranslate($relation->label, $module->name) }}
                </h6>
                <small class="text-muted">
                    {{ $targetModuleName }}
                </small>
            </div>
            <div class="d-flex gap-2">
                @if(str_contains($relation->actions ?? '', 'ADD'))
                    <button class="btn btn-sm btn-primary rounded-3">
                        <i class="bi bi-plus-circle me-1"></i>Add {{ vtranslate($targetModuleName, $targetModuleName) }}
                    </button>
                @endif
                @if(str_contains($relation->actions ?? '', 'SELECT'))
                    <button class="btn btn-sm btn-outline-primary rounded-3">
                        <i class="bi bi-search me-1"></i>Select
                    </button>
                @endif
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($targetModuleName === 'ModComments')
            @include('tenant::partials.comment-section', ['recordId' => $recordId])
        @elseif($targetModuleName === 'ModTracker')
            <div class="p-4">
                @if(empty($activities) || count($activities) == 0)
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0">No recent activities found</p>
                    </div>
                @else
                    <div class="timeline">
                        @foreach($activities as $activity)
                            <div class="timeline-item mb-4 pb-4 border-start ps-4 position-relative">
                                <div class="timeline-marker"></div>
                                <div class="d-flex justify-content-between">
                                    <h6 class="fw-bold mb-1">
                                        <span class="text-primary">{{ $activity->user_name ?: 'System' }}</span>
                                        @php
                                            $action = match ((int) $activity->status) {
                                                0 => 'updated',
                                                1 => 'deleted',
                                                2 => 'created',
                                                3 => 'restored',
                                                4 => 'linked',
                                                5 => 'unlinked',
                                                default => 'modified'
                                            };
                                        @endphp
                                        <span class="text-muted fw-normal ms-1">{{ $action }}</span>
                                    </h6>
                                    <small
                                        class="text-muted">{{ \Carbon\Carbon::parse($activity->changedon)->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="p-4">
                <table class="table table-hover w-100" id="rel-table-{{ $relationId }}">
                    <thead class="bg-light text-uppercase small fw-bold">
                        <tr>
                            <th>#ID</th>
                            <th>Record</th>
                            <th>Created</th>
                            <th>Modified</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($targetModuleName !== 'ModComments' && $targetModuleName !== 'ModTracker')
    @push('scripts')
        <script>
            $(document).ready(function () {
                if (!$.fn.DataTable.isDataTable('#rel-table-{{ $relationId }}')) {
                    $('#rel-table-{{ $relationId }}').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: "{{ route('tenant.modules.related-data', [$module->name, $recordId, $relationId]) }}",
                        },
                        columns: [
                            { data: 'crmid', name: 'ce.crmid' },
                            {
                                data: null,
                                name: 'base.crmid', // Just a placeholder for searching if needed
                                render: function (data, type, row) {
                                    // Try to find a display name similar to standard implementation
                                    let displayName = row.crmid;
                                    const nameFields = ['ticket_title', 'subject', 'name', 'title', 'firstname', 'lastname', 'accountname', 'potentialname'];

                                    for (let f of nameFields) {
                                        if (row[f]) {
                                            displayName = row[f];
                                            break;
                                        }
                                    }

                                    if (row.firstname && row.lastname) {
                                        displayName = (row.firstname + ' ' + row.lastname).trim();
                                    }

                                    return `
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box-sm bg-soft-primary text-primary me-3">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">${displayName}</div>
                                                </div>
                                            </div>
                                        `;
                                }
                            },
                            { data: 'createdtime', name: 'ce.createdtime' },
                            { data: 'modifiedtime', name: 'ce.modifiedtime' },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                        ],
                        order: [[2, 'desc']], // Order by Created Time
                        language: {
                            url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/{{ app()->getLocale() == 'ar' ? 'ar.json' : 'en-GB.json' }}"
                        }
                    });
                }
            });

            function unlinkRecord(id) {
                if (confirm('Are you sure you want to unlink this record?')) {
                    // Unlink logic would go here
                    console.log('Unlinking:', id);
                }
            }
        </script>
    @endpush
@endif

<style>
    .icon-box-sm {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .bg-soft-primary {
        background-color: #eef2ff;
        color: #4361ee;
    }

    .timeline {
        position: relative;
    }

    .timeline-item {
        border-left: 2px solid #e9ecef !important;
    }

    .timeline-marker {
        position: absolute;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #4361ee;
        left: -7px;
        top: 5px;
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }
</style>