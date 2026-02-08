<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CTPowerBlocksFieldsController extends Controller
{
    /**
     * Display a listing of the rules.
     */
    public function index(): View
    {
        $rules = DB::connection('tenant')
            ->table('vtiger_ctpowerblocks')
            ->select('vtiger_ctpowerblocks.*', 'vtiger_tab.name as module_name')
            ->leftJoin('vtiger_tab', 'vtiger_ctpowerblocks.moduleid', '=', 'vtiger_tab.tabid')
            ->get();

        return view('tenant::settings.ctpowerblocks.index', compact('rules'));
    }

    /**
     * Show the form for creating a new rule.
     */
    public function create(): View
    {
        $modules = DB::connection('tenant')
            ->table('vtiger_tab')
            ->where('presence', 0)
            ->where('isentitytype', 1)
            ->whereNotIn('name', ['Home', 'Events', 'Calendar'])
            ->orderBy('name')
            ->get();

        return view('tenant::settings.ctpowerblocks.create', compact('modules'));
    }

    /**
     * Store a newly created rule in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'moduleid' => 'required|integer',
            'conditions' => 'nullable|string',
            'hidefieldid' => 'nullable|array',
            'showfieldid' => 'nullable|array',
            'readonlyfieldid' => 'nullable|array',
            'mandatoryfieldid' => 'nullable|array',
        ]);

        $data = [
            'moduleid' => $validated['moduleid'],
            'conditions' => $validated['conditions'] ?? '[]',
            'hidefieldid' => isset($validated['hidefieldid']) ? implode(',', $validated['hidefieldid']) : null,
            'showfieldid' => isset($validated['showfieldid']) ? implode(',', $validated['showfieldid']) : null,
            'readonlyfieldid' => isset($validated['readonlyfieldid']) ? implode(',', $validated['readonlyfieldid']) : null,
            'mandatoryfieldid' => isset($validated['mandatoryfieldid']) ? implode(',', $validated['mandatoryfieldid']) : null,
        ];

        DB::connection('tenant')
            ->table('vtiger_ctpowerblocks')
            ->insert($data);

        return redirect()->route('tenant.settings.crm.ctpower-blocks-fields.index')
            ->with('success', 'Rule created successfully');
    }

    /**
     * Show the form for editing the specified rule.
     */
    public function edit($id): View
    {
        $rule = DB::connection('tenant')
            ->table('vtiger_ctpowerblocks')
            ->where('ctpowerblockfieldsid', $id)
            ->first();

        $modules = DB::connection('tenant')
            ->table('vtiger_tab')
            ->where('presence', 0)
            ->where('isentitytype', 1)
            ->orderBy('name')
            ->get();

        return view('tenant::settings.ctpowerblocks.edit', compact('rule', 'modules'));
    }

    /**
     * Update the specified rule in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'moduleid' => 'required|integer',
            'conditions' => 'nullable|string',
            'hidefieldid' => 'nullable|array',
            'showfieldid' => 'nullable|array',
            'readonlyfieldid' => 'nullable|array',
            'mandatoryfieldid' => 'nullable|array',
        ]);

        $data = [
            'moduleid' => $validated['moduleid'],
            'conditions' => $validated['conditions'] ?? '[]',
            'hidefieldid' => isset($validated['hidefieldid']) ? implode(',', $validated['hidefieldid']) : null,
            'showfieldid' => isset($validated['showfieldid']) ? implode(',', $validated['showfieldid']) : null,
            'readonlyfieldid' => isset($validated['readonlyfieldid']) ? implode(',', $validated['readonlyfieldid']) : null,
            'mandatoryfieldid' => isset($validated['mandatoryfieldid']) ? implode(',', $validated['mandatoryfieldid']) : null,
        ];

        DB::connection('tenant')
            ->table('vtiger_ctpowerblocks')
            ->where('ctpowerblockfieldsid', $id)
            ->update($data);

        return redirect()->route('tenant.settings.crm.ctpower-blocks-fields.index')
            ->with('success', 'Rule updated successfully');
    }

    /**
     * Remove the specified rule from storage.
     */
    public function destroy($id): RedirectResponse
    {
        DB::connection('tenant')
            ->table('vtiger_ctpowerblocks')
            ->where('ctpowerblockfieldsid', $id)
            ->delete();

        return redirect()->route('tenant.settings.crm.ctpower-blocks-fields.index')
            ->with('success', 'Rule deleted successfully');
    }
}
