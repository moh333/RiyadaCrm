<?php

namespace App\Modules\Tenant\Users\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharingRulesController extends Controller
{
    public function index()
    {
        // Get Organization Wide Defaults
        $defaults = DB::connection('tenant')->table('vtiger_def_org_share')
            ->join('vtiger_tab', 'vtiger_def_org_share.tabid', '=', 'vtiger_tab.tabid')
            ->whereIn('vtiger_tab.presence', [0, 2])
            ->where('vtiger_tab.isentitytype', 1)
            ->whereNotIn('vtiger_tab.name', ['Events', 'Webmails'])
            ->select('vtiger_def_org_share.*', 'vtiger_tab.name as module_name', 'vtiger_tab.tablabel')
            ->get();

        // Get Advanced Sharing Rules
        $customRules = $this->getAdvancedSharingRules();

        // Data for Modal
        $users = DB::connection('tenant')->table('vtiger_users')->where('status', 'Active')->get();
        $groups = DB::connection('tenant')->table('vtiger_groups')->get();
        $roles = DB::connection('tenant')->table('vtiger_role')->get();

        return view('tenant::sharing_rules.index', compact('defaults', 'customRules', 'users', 'groups', 'roles'));
    }

    private function getAdvancedSharingRules()
    {
        $tables = [
            'role2role' => ['share_roleid', 'to_roleid', 'Roles', 'Roles'],
            'role2rs' => ['share_roleid', 'to_roleandsubid', 'Roles', 'RoleAndSubordinates'],
            'role2group' => ['share_roleid', 'to_groupid', 'Roles', 'Groups'],
            'rs2role' => ['share_roleandsubid', 'to_roleid', 'RoleAndSubordinates', 'Roles'],
            'rs2rs' => ['share_roleandsubid', 'to_roleandsubid', 'RoleAndSubordinates', 'RoleAndSubordinates'],
            'rs2grp' => ['share_roleandsubid', 'to_groupid', 'RoleAndSubordinates', 'Groups'],
            'grp2role' => ['share_groupid', 'to_roleid', 'Groups', 'Roles'],
            'grp2rs' => ['share_groupid', 'to_roleandsubid', 'Groups', 'RoleAndSubordinates'],
            'grp2grp' => ['share_groupid', 'to_groupid', 'Groups', 'Groups'],
        ];

        $rules = [];

        foreach ($tables as $suffix => $config) {
            $tableName = "vtiger_datashare_" . $suffix;
            $rawRules = DB::connection('tenant')->table($tableName)
                ->join('vtiger_datashare_module_rel', "$tableName.shareid", '=', 'vtiger_datashare_module_rel.shareid')
                ->join('vtiger_tab', 'vtiger_datashare_module_rel.tabid', '=', 'vtiger_tab.tabid')
                ->select("$tableName.*", 'vtiger_tab.tabid', 'vtiger_tab.tablabel', 'vtiger_tab.name as module_name')
                ->get();

            foreach ($rawRules as $rule) {
                // We need to resolve names for from/to IDs
                $fromName = $this->resolveEntityName($rule->{$config[0]}, $config[2]);
                $toName = $this->resolveEntityName($rule->{$config[1]}, $config[3]);

                $rules[] = (object) [
                    'shareid' => $rule->shareid,
                    'tabid' => $rule->tabid,
                    'relation_type' => $suffix,
                    'module' => $rule->tablabel,
                    'from' => $fromName,
                    'from_id' => $rule->{$config[0]},
                    'from_type' => $config[2],
                    'to' => $toName,
                    'to_id' => $rule->{$config[1]},
                    'to_type' => $config[3],
                    'permission' => $rule->permission,
                ];
            }
        }

        return collect($rules)->groupBy('tabid');
    }

    private function resolveEntityName($id, $type)
    {
        switch ($type) {
            case 'Roles':
            case 'RoleAndSubordinates':
                return DB::connection('tenant')->table('vtiger_role')->where('roleid', $id)->value('rolename') ?? $id;
            case 'Groups':
                return DB::connection('tenant')->table('vtiger_groups')->where('groupid', $id)->value('groupname') ?? $id;
            default:
                return $id;
        }
    }

    public function updateDefaults(Request $request)
    {
        $rules = $request->input('rules', []);

        foreach ($rules as $ruleId => $permission) {
            DB::connection('tenant')->table('vtiger_def_org_share')
                ->where('ruleid', $ruleId)
                ->update(['permission' => $permission]);
        }

        return redirect()->route('tenant.settings.users.sharing-rules.index')
            ->with('success', __('tenant::users.updated_successfully'));
    }

    public function storeCustom(Request $request)
    {
        $validated = $request->validate([
            'tabid' => 'required|integer',
            'from_type' => 'required|in:Groups,Roles,RoleAndSubordinates',
            'from_id' => 'required',
            'to_type' => 'required|in:Groups,Roles,RoleAndSubordinates',
            'to_id' => 'required',
            'permission' => 'required|in:0,1', // 0: Read Only, 1: Read and Write
        ]);

        $this->saveCustomRule($validated);

        return redirect()->route('tenant.settings.users.sharing-rules.index')
            ->with('success', __('tenant::users.created_successfully'));
    }

    public function updateCustom(Request $request, $id)
    {
        $validated = $request->validate([
            'tabid' => 'required|integer',
            'from_type' => 'required|in:Groups,Roles,RoleAndSubordinates',
            'from_id' => 'required',
            'to_type' => 'required|in:Groups,Roles,RoleAndSubordinates',
            'to_id' => 'required',
            'permission' => 'required|in:0,1',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated, $id) {
            $this->cleanupCustomRule($id);
            $this->saveCustomRule($validated, $id);
        });

        return redirect()->route('tenant.settings.users.sharing-rules.index')
            ->with('success', __('tenant::users.updated_successfully'));
    }

    public function destroyCustom($id)
    {
        DB::connection('tenant')->transaction(function () use ($id) {
            $this->cleanupCustomRule($id);
        });

        return redirect()->route('tenant.settings.users.sharing-rules.index')
            ->with('success', __('tenant::users.deleted_successfully'));
    }

    private function saveCustomRule($data, $explicitShareId = null)
    {
        $tabId = $data['tabid'];

        DB::connection('tenant')->transaction(function () use ($data, $tabId, $explicitShareId) {
            $shareId = $explicitShareId ?? (DB::connection('tenant')->table('vtiger_datashare_module_rel')->max('shareid') + 1);

            $typeMap = [
                'Groups' => 'grp',
                'Roles' => 'role',
                'RoleAndSubordinates' => 'rs',
            ];

            $fromSuffix = $typeMap[$data['from_type']];
            $toSuffix = $typeMap[$data['to_type']];
            $tableName = "vtiger_datashare_{$fromSuffix}2{$toSuffix}";

            $colMap = [
                'grp' => 'share_groupid',
                'role' => 'share_roleid',
                'rs' => 'share_roleandsubid',
                'to_grp' => 'to_groupid',
                'to_role' => 'to_roleid',
                'to_rs' => 'to_roleandsubid',
            ];

            // 1. Module Rel
            DB::connection('tenant')->table('vtiger_datashare_module_rel')->insert([
                'shareid' => $shareId,
                'tabid' => $tabId,
                'relationtype' => 'Custom',
            ]);

            // 2. Relation Table
            DB::connection('tenant')->table($tableName)->insert([
                'shareid' => $shareId,
                $colMap[$fromSuffix] => $data['from_id'],
                $colMap["to_{$toSuffix}"] => $data['to_id'],
                'permission' => $data['permission'] == 1 ? 2 : 1,
            ]);
        });
    }

    private function cleanupCustomRule($id)
    {
        DB::connection('tenant')->table('vtiger_datashare_module_rel')->where('shareid', $id)->delete();

        $suffixes = ['role2role', 'role2rs', 'role2group', 'rs2role', 'rs2rs', 'rs2grp', 'grp2role', 'grp2rs', 'grp2grp'];
        foreach ($suffixes as $suffix) {
            DB::connection('tenant')->table("vtiger_datashare_" . $suffix)->where('shareid', $id)->delete();
        }
    }
}
