@props(['module', 'recordId'])

@php
    // Get all relations for this module using vtiger metadata
    $relations = \DB::connection('tenant')
        ->table('vtiger_relatedlists as vrl')
        ->join('vtiger_tab as vt', 'vrl.related_tabid', '=', 'vt.tabid')
        ->leftJoin('vtiger_field as vf', 'vrl.relationfieldid', '=', 'vf.fieldid')
        ->where('vrl.tabid', $module->getId())
        ->where('vrl.presence', 0)
        ->select([
            'vrl.relation_id',
            'vrl.label',
            'vrl.actions',
            'vrl.relationtype',
            'vrl.name as relation_name',
            'vt.name as target_module_name',
            'vt.tabid as target_tabid',
            'vf.columnname as linking_column',
            'vf.tablename as linking_table'
        ])
        ->orderBy('vrl.sequence')
        ->get();
@endphp

@if($relations->isEmpty())
    <div class="text-center py-5">
        <i class="bi bi-diagram-3 text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3">{{ __('tenant::tenant.module_relations') }}
            <br>
            {{ __('tenant::tenant.relations_not_configured') }}
        </p>
        <a href="{{ route('tenant.settings.modules.relations', $module->getName()) }}" class="btn btn-primary rounded-3">
            <i class="bi bi-gear me-2"></i> {{ __('tenant::tenant.configure_relations') }}
        </a>
    </div>
@else
    @foreach($relations as $relation)
        @php
            $relatedRecords = collect();

            try {
                $targetModule = app(\App\Modules\Core\VtigerModules\Contracts\ModuleRegistryInterface::class)
                    ->get($relation->target_module_name);

                if ($targetModule && $targetModule->getBaseTable()) {
                    $query = \DB::connection('tenant')
                        ->table($targetModule->getBaseTable() . ' as base')
                        ->join('vtiger_crmentity as ce', 'base.' . $targetModule->getBaseIndex(), '=', 'ce.crmid')
                        ->where('ce.deleted', 0);

                    if ($relation->linking_column) {
                        // 1:N Relationship via field
                        $query->where('base.' . $relation->linking_column, $recordId);
                    } else {
                        // Likely N:N Relationship or legacy relation
                        // Check vtiger_crmentityrel junction table
                        $query->join('vtiger_crmentityrel as rel', function ($join) use ($recordId, $targetModule) {
                            $join->on('base.' . $targetModule->getBaseIndex(), '=', 'rel.relcrmid')
                                ->where('rel.crmid', $recordId);
                        })->orWhere(function ($q) use ($recordId, $targetModule) {
                            $q->where('rel.crmid', 'base.' . $targetModule->getBaseIndex())
                                ->where('rel.relcrmid', $recordId);
                        });

                        // Also check specific N:N tables if needed, but crmentityrel is standard
                    }

                    $relatedRecords = $query->select(['ce.crmid', 'ce.createdtime', 'ce.modifiedtime', 'base.*'])
                        ->limit(10)
                        ->get();
                }
            } catch (\Exception $e) {
                // Ignore errors for individual relations
            }
        @endphp

        {{-- Only show cards for relations that actually have records or are standard --}}
        @if($relatedRecords->isNotEmpty() || in_array($relation->target_module_name, ['HelpDesk', 'ModComments']))
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-link-45deg text-primary me-2"></i>
                                {{ $relation->label }}
                            </h6>
                            <small class="text-muted">
                                {{ $relation->target_module_name }}
                                @if($relatedRecords->isNotEmpty())
                                    <span class="badge bg-primary ms-2">{{ $relatedRecords->count() }} records</span>
                                @endif
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            @if(str_contains($relation->actions ?? '', 'ADD') && $relation->target_module_name !== 'ModComments')
                                <button class="btn btn-sm btn-primary rounded-3">
                                    <i class="bi bi-plus-circle me-1"></i>Add
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($relation->target_module_name === 'ModComments')
                        @include('tenant::partials.comment-section', ['recordId' => $recordId])
                    @elseif($relatedRecords->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No {{ $relation->label }} found</p>
                            <small>Add or link {{ $relation->label }} to this {{ strtolower($module->getName()) }}</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Record</th>
                                        <th>Created</th>
                                        <th>Modified</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($relatedRecords as $record)

                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-box-sm bg-soft-primary text-primary me-3">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold">
                                                            @php
                                                                // Try to get a display name and number
                                                                $displayName = $record->crmid ?? 'Record';
                                                                $displayNo = $record->crmid;

                                                                // Common number fields
                                                                $noFields = ['ticket_no', 'contact_no', 'account_no', 'potential_no', 'quote_no', 'purchaseorder_no', 'salesorder_no', 'invoice_no', 'project_no', 'asset_no'];
                                                                foreach ($noFields as $field) {
                                                                    if (isset($record->$field) && !empty($record->$field)) {
                                                                        $displayNo = $record->$field;
                                                                        break;
                                                                    }
                                                                }

                                                                // Common name fields
                                                                $nameFields = ['ticket_title', 'subject', 'name', 'title', 'firstname', 'lastname', 'accountname', 'potentialname'];
                                                                foreach ($nameFields as $field) {
                                                                    if (isset($record->$field) && !empty($record->$field)) {
                                                                        $displayName = $record->$field;
                                                                        break;
                                                                    }
                                                                }

                                                                // Combine first and last name if available
                                                                if (isset($record->firstname) && isset($record->lastname)) {
                                                                    $displayName = trim($record->firstname . ' ' . $record->lastname);
                                                                }
                                                            @endphp
                                                            {{ $displayName }}
                                                        </div>
                                                        <small class="text-muted">#{{ $displayNo }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($record->createdtime)->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($record->modifiedtime)->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary rounded-start" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-outline-secondary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger rounded-end" title="Unlink">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($relatedRecords->count() >= 10)
                            <div class="card-footer bg-white border-top text-center py-3">
                                <a href="#" class="text-primary fw-bold text-decoration-none">
                                    View All {{ $relation->label }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    @endforeach
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
        color: #6366f1;
    }
</style>