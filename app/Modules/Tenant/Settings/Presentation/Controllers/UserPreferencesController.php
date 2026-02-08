<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserPreferencesController
{
    /**
     * Display user preferences
     */
    public function index(Request $request): View
    {
        $userId = $request->get('user_id', auth()->id());

        $user = \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->first();

        return view('tenant::settings.preferences.index', [
            'user' => $user
        ]);
    }

    /**
     * Show edit preferences form
     */
    public function edit(Request $request): View
    {
        $userId = $request->get('user_id', auth()->id());

        $user = \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->first();

        return view('tenant::settings.preferences.edit', [
            'user' => $user,
            'languages' => $this->getLanguages(),
            'currencies' => $this->getCurrencies(),
            'timezones' => $this->getTimezones(),
            'dateFormats' => $this->getDateFormats(),
            'hourFormats' => $this->getHourFormats(),
            'startHours' => $this->getStartHours(),
            'endHours' => $this->getEndHours(),
            'users' => $this->getUsers($userId)
        ]);
    }

    /**
     * Get start hour options
     */
    private function getStartHours(): array
    {
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $key = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $label = date("h:i A", strtotime($key));
            $hours[$key] = $label;
        }
        return $hours;
    }

    /**
     * Get end hour options
     */
    private function getEndHours(): array
    {
        return $this->getStartHours();
    }

    /**
     * Update user preferences
     */
    public function update(Request $request): RedirectResponse
    {
        $userId = $request->get('user_id', auth()->id());

        $validated = $request->validate([
            // Basic Info
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email1' => 'required|email|max:255',
            'title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'phone_work' => 'nullable|string|max:50',
            'phone_mobile' => 'nullable|string|max:50',
            'phone_home' => 'nullable|string|max:50',
            'phone_fax' => 'nullable|string|max:50',
            'reports_to_id' => 'nullable|string',
            'signature' => 'nullable|string',

            // Address Info
            'address_street' => 'nullable|string',
            'address_city' => 'nullable|string|max:255',
            'address_state' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:255',
            'address_postalcode' => 'nullable|string|max:20',

            // Advanced Options
            'language' => 'required|string|max:50',
            'currency_id' => 'required|integer',
            'date_format' => 'required|string|max:50',
            'hour_format' => 'required|string|max:10',
            'time_zone' => 'required|string|max:100',
            'start_hour' => 'nullable|string|max:50',
            'end_hour' => 'nullable|string|max:50',
            'defaultlandingpage' => 'nullable|string|max:100',
            'no_of_currency_decimals' => 'nullable|integer|min:0|max:5',
            'truncate_trailing_zeros' => 'nullable|boolean',
        ]);

        // Fix for reports_to_id and checkbox
        $validated['reports_to_id'] = $validated['reports_to_id'] ?: null;
        $validated['truncate_trailing_zeros'] = $request->has('truncate_trailing_zeros') ? 1 : 0;

        // Filter out columns that don't exist in the table to avoid SQL errors
        $columns = \DB::connection('tenant')->getSchemaBuilder()->getColumnListing('vtiger_users');
        $updateData = array_intersect_key($validated, array_flip($columns));

        \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->update($updateData);

        return redirect()->route('tenant.settings.preferences.index', ['user_id' => $userId])
            ->with('success', __('tenant::settings.preferences_updated_successfully'));
    }

    /**
     * Get all users for reports_to field
     */
    private function getUsers(int $excludeId): array
    {
        return \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', '!=', $excludeId)
            ->where('status', 'Active')
            ->select('id', 'first_name', 'last_name')
            ->get()
            ->mapWithKeys(fn($u) => [$u->id => $u->first_name . ' ' . $u->last_name])
            ->toArray();
    }

    /**
     * Get available languages
     */
    private function getLanguages(): array
    {
        return [
            'en' => 'English',
            'ar' => 'العربية (Arabic)'
        ];
    }

    /**
     * Get available currencies
     */
    private function getCurrencies(): array
    {
        return \DB::connection('tenant')
            ->table('vtiger_currency_info')
            ->where('deleted', 0)
            ->where('currency_status', 'Active')
            ->pluck('currency_name', 'id')
            ->toArray();
    }

    /**
     * Get available timezones
     */
    private function getTimezones(): array
    {
        return [
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Asia/Dubai' => 'Dubai',
            'Asia/Riyadh' => 'Riyadh',
            'Asia/Kolkata' => 'Mumbai, Kolkata',
            'Asia/Tokyo' => 'Tokyo',
            'Australia/Sydney' => 'Sydney'
        ];
    }

    /**
     * Get date format options
     */
    private function getDateFormats(): array
    {
        return [
            'yyyy-mm-dd' => '2026-02-04',
            'mm-dd-yyyy' => '02-04-2026',
            'dd-mm-yyyy' => '04-02-2026',
            'yyyy.mm.dd' => '2026.02.04',
            'mm.dd.yyyy' => '02.04.2026',
            'dd.mm.yyyy' => '04.02.2026',
            'yyyy/mm/dd' => '2026/02/04',
            'mm/dd/yyyy' => '02/04/2026',
            'dd/mm/yyyy' => '04/02/2026'
        ];
    }

    /**
     * Get hour format options
     */
    private function getHourFormats(): array
    {
        return [
            '12' => '12 Hour (AM/PM)',
            '24' => '24 Hour'
        ];
    }
}
