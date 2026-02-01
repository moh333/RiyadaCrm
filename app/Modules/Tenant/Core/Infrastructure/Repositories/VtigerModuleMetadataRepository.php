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
        $userId = auth('tenant')->id();
        $profileId = DB::connection($this->connection)->table('vtiger_user2role')
            ->join('vtiger_role2profile', 'vtiger_user2role.roleid', '=', 'vtiger_role2profile.roleid')
            ->where('vtiger_user2role.userid', $userId)
            ->value('profileid');

        $query = DB::connection($this->connection)
            ->table('vtiger_field')
            ->leftJoin('vtiger_blocks', 'vtiger_field.block', '=', 'vtiger_blocks.blockid')
            ->where('vtiger_field.tabid', $tabId)
            ->whereIn('vtiger_field.presence', [0, 2])
            ->whereIn('vtiger_field.displaytype', [1, 2, 3, 4, 5]); // Include 2 (read-only) and 4 (auto-increment)

        $select = ['vtiger_field.*', 'vtiger_blocks.blocklabel', 'vtiger_blocks.label_en as block_label_en', 'vtiger_blocks.label_ar as block_label_ar'];

        // If not admin, filter by profile permissions
        if ($userId != 1 && $profileId) {
            $query->join('vtiger_profile2field', function ($join) use ($profileId, $tabId) {
                $join->on('vtiger_field.fieldid', '=', 'vtiger_profile2field.fieldid')
                    ->where('vtiger_profile2field.profileid', $profileId)
                    ->where('vtiger_profile2field.visible', 0); // 0 = visible in Vtiger
            });
            $select[] = 'vtiger_profile2field.readonly as profile_readonly';
        }

        $fields = $query->select($select)
            ->orderBy('vtiger_field.sequence')
            ->get();

        if ($fields->isEmpty()) {
            $moduleName = DB::connection($this->connection)->table('vtiger_tab')->where('tabid', $tabId)->value('name');
            if ($moduleName === 'EmailTemplates') {
                return [
                    new FieldDescriptor(0, 'templatename', 'templatename', 'Template Name', 'vtiger_emailtemplates', 1, 'V~M', true, false, 0, 0, 'LBL_EMAIL_TEMPLATE_INFORMATION', null, null, [], null, false, null, false),
                    new FieldDescriptor(0, 'subject', 'subject', 'Subject', 'vtiger_emailtemplates', 1, 'V~O', false, false, 0, 0, 'LBL_EMAIL_TEMPLATE_INFORMATION', null, null, [], null, false, null, false),
                    new FieldDescriptor(0, 'description', 'description', 'Description', 'vtiger_emailtemplates', 1, 'V~O', false, false, 0, 0, 'LBL_EMAIL_TEMPLATE_INFORMATION', null, null, [], null, false, null, false),
                ];
            }
        }

        return $fields->map(function ($f) use ($userId) {
            $picklistValues = [];
            // Common picklist uitypes: 15 (standard), 16 (system), 33 (multi-select), 55 (salutation)
            if (in_array($f->uitype, [15, 16, 33, 55])) {
                $tableName = 'vtiger_' . $f->fieldname;
                if (!\Illuminate\Support\Facades\Schema::connection($this->connection)->hasTable($tableName)) {
                    // Try without vtiger_ prefix for some custom fields or quirks
                    $tableName = $f->fieldname;
                }

                if (\Illuminate\Support\Facades\Schema::connection($this->connection)->hasTable($tableName)) {
                    $query = DB::connection($this->connection)->table($tableName);

                    if (\Illuminate\Support\Facades\Schema::connection($this->connection)->hasColumn($tableName, 'sortorderid')) {
                        $query->orderBy('sortorderid');
                    } else {
                        $query->orderBy($f->fieldname);
                    }

                    $picklistValues = $query->pluck($f->fieldname)->toArray();
                }
            }

            // Determine if the field is readonly
            $isReadonly = false;

            if ($userId == 1) {
                // Admin bypasses profile/explicit readonly, but respects system-level readonly
                if (in_array($f->displaytype, [2, 3]) || $f->uitype == 4) {
                    $isReadonly = true;
                }
            } else {
                // 1. Mandatory readonly by display type
                if (in_array($f->displaytype, [2, 3])) {
                    $isReadonly = true;
                }
                // 2. Auto-increment fields (uitype 4) are always readonly
                elseif ($f->uitype == 4) {
                    $isReadonly = true;
                }
                // 3. Profile-based readonly
                elseif (isset($f->profile_readonly) && $f->profile_readonly == 1) {
                    $isReadonly = true;
                }
                // 4. Explicitly set on field record
                elseif (isset($f->readonly) && $f->readonly == 1) {
                    $isReadonly = true;
                }
            }

            // Determine the label to use
            $label = $f->fieldlabel;
            $locale = app()->getLocale();
            if ($locale === 'ar' && !empty($f->fieldlabel_ar)) {
                $label = $f->fieldlabel_ar;
            } elseif ($locale === 'en' && !empty($f->fieldlabel_en)) {
                $label = $f->fieldlabel_en;
            }

            return new FieldDescriptor(
                id: $f->fieldid,
                name: $f->fieldname,
                column: $f->columnname,
                label: $label,
                table: $f->tablename,
                uiType: (int) $f->uitype,
                typeofData: $f->typeofdata,
                isMandatory: str_contains($f->typeofdata, '~M'),
                isCustomField: str_starts_with($f->tablename, 'vtiger_') && str_ends_with($f->tablename, 'cf'),
                blockId: (int) $f->block,
                presence: (int) $f->presence,
                blockLabel: $f->blocklabel,
                blockLabelEn: $f->block_label_en ?? null,
                blockLabelAr: $f->block_label_ar ?? null,
                picklistValues: $picklistValues,
                helpInfo: $f->helpinfo ?? null,
                allowMultipleFiles: (bool) ($f->allow_multiple_files ?? false),
                acceptableFileTypes: $f->acceptable_file_types ?? null,
                readonly: $isReadonly
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

        // Determine the label to use
        $label = $tab->tablabel;
        $locale = app()->getLocale();
        if ($locale === 'ar' && !empty($tab->tablabel_ar)) {
            $label = $tab->tablabel_ar;
        } elseif ($locale === 'en' && !empty($tab->tablabel_en)) {
            $label = $tab->tablabel_en;
        }

        return new ModuleDescriptor(
            id: $tab->tabid,
            name: $name,
            label: $label,
            baseTable: $baseTable,
            baseTableIndex: $baseTableIndex,
            isEntity: (bool) $tab->isentitytype,
            presence: $presence,
            appName: $tab->appname ?? 'OTHERS',
            customFieldTable: $baseTable . 'cf'
        );
    }
}
