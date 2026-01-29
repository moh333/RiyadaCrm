<?php

namespace App\Modules\Tenant\Core\Infrastructure\Repositories;

use App\Modules\Tenant\Core\Domain\Repositories\ModuleMetadataRepositoryInterface;
use App\Modules\Tenant\Core\Domain\Entities\ModuleDescriptor;
use App\Modules\Tenant\Core\Domain\Entities\FieldDescriptor;
use Illuminate\Support\Facades\DB;

class VtigerModuleMetadataRepository implements ModuleMetadataRepositoryInterface
{
    protected string $connection = 'tenant';

    public function getAllModules(): array
    {
        $tabs = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->leftJoin('vtiger_entityname', 'vtiger_tab.tabid', '=', 'vtiger_entityname.tabid')
            ->leftJoin('vtiger_app2tab', 'vtiger_tab.tabid', '=', 'vtiger_app2tab.tabid')
            ->select('vtiger_tab.*', 'vtiger_entityname.tablename as base_table', 'vtiger_entityname.entityidfield', 'vtiger_app2tab.appname')
            ->where('vtiger_tab.presence', 0)
            ->where('vtiger_tab.isentitytype', 1)
            ->orderBy('vtiger_tab.tabsequence')
            ->get();

        return $tabs->map(fn($t) => $this->mapTabToDescriptor($t))->toArray();
    }

    public function getModuleByName(string $name): ?ModuleDescriptor
    {
        $tab = DB::connection($this->connection)
            ->table('vtiger_tab')
            ->leftJoin('vtiger_entityname', 'vtiger_tab.tabid', '=', 'vtiger_entityname.tabid')
            ->leftJoin('vtiger_app2tab', 'vtiger_tab.tabid', '=', 'vtiger_app2tab.tabid')
            ->select('vtiger_tab.*', 'vtiger_entityname.tablename as base_table', 'vtiger_entityname.entityidfield', 'vtiger_app2tab.appname')
            ->where('vtiger_tab.name', $name)
            ->first();

        if (!$tab)
            return null;

        return $this->mapTabToDescriptor($tab);
    }

    public function getFieldsByModule(int $tabId): array
    {
        $fields = DB::connection($this->connection)
            ->table('vtiger_field')
            ->leftJoin('vtiger_blocks', 'vtiger_field.block', '=', 'vtiger_blocks.blockid')
            ->where('vtiger_field.tabid', $tabId)
            ->where('vtiger_field.presence', 0)
            ->select('vtiger_field.*', 'vtiger_blocks.blocklabel')
            ->orderBy('vtiger_field.sequence')
            ->get();

        if ($fields->isEmpty()) {
            $moduleName = DB::connection($this->connection)->table('vtiger_tab')->where('tabid', $tabId)->value('name');
            if ($moduleName === 'EmailTemplates') {
                return [
                    new FieldDescriptor(0, 'templatename', 'templatename', 'Template Name', 'vtiger_emailtemplates', 1, 'V~M', true, false, 0, 0, 'LBL_EMAIL_TEMPLATE_INFORMATION'),
                    new FieldDescriptor(0, 'subject', 'subject', 'Subject', 'vtiger_emailtemplates', 1, 'V~O', false, false, 0, 0, 'LBL_EMAIL_TEMPLATE_INFORMATION'),
                    new FieldDescriptor(0, 'description', 'description', 'Description', 'vtiger_emailtemplates', 1, 'V~O', false, false, 0, 0, 'LBL_EMAIL_TEMPLATE_INFORMATION'),
                ];
            }
        }

        return $fields->map(function ($f) {
            $picklistValues = [];
            if (in_array($f->uitype, [15, 16, 33])) {
                // Try to fetch picklist values from vtiger_{fieldname} table
                if (\Illuminate\Support\Facades\Schema::connection($this->connection)->hasTable('vtiger_' . $f->fieldname)) {
                    $picklistValues = DB::connection($this->connection)
                        ->table('vtiger_' . $f->fieldname)
                        ->pluck($f->fieldname)
                        ->toArray();
                }
            }

            return new FieldDescriptor(
                id: $f->fieldid,
                name: $f->fieldname,
                column: $f->columnname,
                label: $f->fieldlabel,
                table: $f->tablename,
                uiType: (int) $f->uitype,
                typeofData: $f->typeofdata,
                isMandatory: str_contains($f->typeofdata, '~M'),
                isCustomField: str_starts_with($f->tablename, 'vtiger_') && str_ends_with($f->tablename, 'cf'),
                blockId: (int) $f->block,
                presence: (int) $f->presence,
                blockLabel: $f->blocklabel,
                picklistValues: $picklistValues
            );
        })->toArray();
    }

    public function getRelationshipsByModule(string $name): array
    {
        return DB::connection($this->connection)
            ->table('vtiger_relatedlists')
            ->join('vtiger_tab as target', 'vtiger_relatedlists.related_tabid', '=', 'target.tabid')
            ->whereExists(function ($query) use ($name) {
                $query->select(DB::raw(1))
                    ->from('vtiger_tab')
                    ->whereRaw('vtiger_tab.tabid = vtiger_relatedlists.tabid')
                    ->where('vtiger_tab.name', $name);
            })
            ->select('vtiger_relatedlists.*', 'target.name as target_module')
            ->get()
            ->toArray();
    }

    protected function mapTabToDescriptor(object $tab): ModuleDescriptor
    {
        $name = $tab->name;
        $baseTable = $tab->base_table;
        $baseTableIndex = $tab->entityidfield;

        // Manual mapping for core vtiger modules that share tables or have special primary keys
        $quirks = [
            'EmailTemplates' => ['table' => 'vtiger_emailtemplates', 'index' => 'templateid'],
            'Documents' => ['table' => 'vtiger_notes', 'index' => 'notesid'],
            'HelpDesk' => ['table' => 'vtiger_troubletickets', 'index' => 'ticketid'],
            'Potentials' => ['table' => 'vtiger_potential', 'index' => 'potentialid'],
            'Accounts' => ['table' => 'vtiger_account', 'index' => 'accountid'],
            'Calendar' => ['table' => 'vtiger_activity', 'index' => 'activityid'],
            'Events' => ['table' => 'vtiger_activity', 'index' => 'activityid'],
            'ModComments' => ['table' => 'vtiger_modcomments', 'index' => 'modcommentsid'],
        ];

        if (isset($quirks[$name])) {
            if (empty($baseTable)) {
                $baseTable = $quirks[$name]['table'];
            }
            if (empty($baseTableIndex)) {
                $baseTableIndex = $quirks[$name]['index'];
            }
        }

        // If baseTable is still null/empty, use the default convention
        if (empty($baseTable)) {
            $baseTable = 'vtiger_' . strtolower($name);
        }

        // If baseTableIndex is still null/empty, use the default convention
        if (empty($baseTableIndex)) {
            $baseTableIndex = strtolower($name) . 'id';
        }

        // Verify if base table exists
        $presence = (int) $tab->presence;
        if (!\Illuminate\Support\Facades\Schema::connection($this->connection)->hasTable($baseTable)) {
            $presence = 1; // Mark as hidden if table is missing
        }

        return new ModuleDescriptor(
            id: $tab->tabid,
            name: $name,
            label: $tab->tablabel,
            baseTable: $baseTable,
            baseTableIndex: $baseTableIndex,
            isEntity: (bool) $tab->isentitytype,
            presence: $presence,
            appName: $tab->appname ?? 'OTHERS',
            customFieldTable: $baseTable . 'cf'
        );
    }
}
