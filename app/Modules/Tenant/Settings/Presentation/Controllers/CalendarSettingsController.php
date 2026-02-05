<?php

namespace App\Modules\Tenant\Settings\Presentation\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CalendarSettingsController
{
    /**
     * Display calendar settings
     */
    public function index(Request $request): View
    {
        $userId = $request->get('user_id', auth()->id());

        $user = \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->first();

        return view('tenant::settings.calendar.index', [
            'user' => $user
        ]);
    }

    /**
     * Show edit calendar settings form
     */
    public function edit(Request $request): View
    {
        $userId = $request->get('user_id', auth()->id());

        $user = \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->first();

        return view('tenant::settings.calendar.edit', [
            'user' => $user,
            'hourFormats' => $this->getHourFormats(),
            'startHours' => $this->getStartHours(),
            'endHours' => $this->getEndHours(),
            'activityTypes' => $this->getActivityTypes(),
            'eventStatuses' => $this->getEventStatuses(),
            'reminderIntervals' => $this->getReminderIntervals(),
            'calendarViews' => $this->getCalendarViews()
        ]);
    }

    /**
     * Update calendar settings
     */
    public function update(Request $request): RedirectResponse
    {
        $userId = $request->get('user_id', auth()->id());

        $validated = $request->validate([
            'hour_format' => 'required|string|max:10',
            'start_hour' => 'required|string|max:10',
            'end_hour' => 'required|string|max:10',
            'defaultactivitytype' => 'nullable|string|max:100',
            'defaulteventstatus' => 'nullable|string|max:100',
            'callduration' => 'nullable|integer',
            'othereventduration' => 'nullable|integer',
            'activity_view' => 'nullable|string|max:100',
            'defaultcalendarview' => 'nullable|string|max:100',
            'reminder_interval' => 'nullable|string|max:100',
        ]);

        \DB::connection('tenant')
            ->table('vtiger_users')
            ->where('id', $userId)
            ->update($validated);

        return redirect()->route('tenant.settings.calendar.index', ['user_id' => $userId])
            ->with('success', __('tenant::settings.calendar_settings_updated'));
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
     * Get activity type options
     */
    private function getActivityTypes(): array
    {
        return [
            'Call' => 'Call',
            'Meeting' => 'Meeting',
            'Task' => 'Task'
        ];
    }

    /**
     * Get event status options
     */
    private function getEventStatuses(): array
    {
        return [
            'Planned' => 'Planned',
            'Held' => 'Held',
            'Not Held' => 'Not Held'
        ];
    }

    /**
     * Get reminder interval options
     */
    private function getReminderIntervals(): array
    {
        return [
            'None' => 'None',
            '1 Minute' => '1 Minute',
            '5 Minutes' => '5 Minutes',
            '15 Minutes' => '15 Minutes',
            '30 Minutes' => '30 Minutes',
            '45 Minutes' => '45 Minutes',
            '1 Hour' => '1 Hour',
            '1 Day' => '1 Day'
        ];
    }

    /**
     * Get calendar view options
     */
    private function getCalendarViews(): array
    {
        return [
            'ListView' => 'List View',
            'Calendar' => 'Calendar View'
        ];
    }
}
