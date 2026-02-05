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
            'endHours' => $this->getEndHours()
        ]);
    }

    /**
     * Get start hour options
     */
    private function getStartHours(): array
    {
        return [
            '00:00' => '12:00 AM',
            '01:00' => '01:00 AM',
            '02:00' => '02:00 AM',
            '03:00' => '03:00 AM',
            '04:00' => '04:00 AM',
            '05:00' => '05:00 AM',
            '06:00' => '06:00 AM',
            '07:00' => '07:00 AM',
            '08:00' => '08:00 AM',
            '09:00' => '09:00 AM',
            '10:00' => '10:00 AM',
            '11:00' => '11:00 AM',
            '12:00' => '12:00 PM',
            '13:00' => '01:00 PM',
            '14:00' => '02:00 PM',
            '15:00' => '03:00 PM',
            '16:00' => '04:00 PM',
            '17:00' => '05:00 PM',
            '18:00' => '06:00 PM',
            '19:00' => '07:00 PM',
            '20:00' => '08:00 PM',
            '21:00' => '09:00 PM',
            '22:00' => '10:00 PM',
            '23:00' => '11:00 PM'
        ];
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
            'language' => 'required|string|max:50',
            'currency_id' => 'required|integer',
            'date_format' => 'required|string|max:50',
            'hour_format' => 'required|string|max:10',
            'time_zone' => 'required|string|max:100',
            'start_hour' => 'nullable|string|max:10',
            'end_hour' => 'nullable|string|max:10',
            'defaultlandingpage' => 'nullable|string|max:100',
            'no_of_currency_decimals' => 'nullable|integer|min:0|max:5',
        ]);

        \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->update($validated);

        return redirect()->route('tenant.settings.preferences.index', ['user_id' => $userId])
            ->with('success', __('tenant::settings.preferences_updated_successfully'));
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
