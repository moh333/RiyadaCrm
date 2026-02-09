<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VtigerScheduledReport extends Model
{
    use HasFactory;

    // Schedule Type Constants
    const SCHEDULED_DAILY = 1;
    const SCHEDULED_WEEKLY = 2;
    const SCHEDULED_MONTHLY_BY_DATE = 3;
    const SCHEDULED_ANNUALLY = 4;
    const SCHEDULED_ON_SPECIFIC_DATE = 5;

    protected $table = 'vtiger_schedulereports';
    protected $primaryKey = 'reportid';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'reportid' => 'integer',
            'scheduleid' => 'integer',
            'next_trigger_time' => 'datetime',
        ];
    }

    /**
     * Get the report that owns the schedule.
     */
    public function report()
    {
        return $this->belongsTo(\App\Modules\Tenant\Reports\Domain\Models\Report::class, 'reportid', 'reportid');
    }

    /**
     * Get decoded recipients array.
     */
    public function getRecipientsArrayAttribute(): array
    {
        if (empty($this->recipients)) {
            return [];
        }
        $decoded = json_decode($this->recipients, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get decoded specific emails array.
     */
    public function getSpecificEmailsArrayAttribute(): array
    {
        if (empty($this->specificemails)) {
            return [];
        }
        $decoded = json_decode($this->specificemails, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get decoded day of week array.
     */
    public function getDayOfWeekArrayAttribute(): array
    {
        if (empty($this->schdayoftheweek)) {
            return [];
        }
        $decoded = json_decode($this->schdayoftheweek, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get decoded day of month array.
     */
    public function getDayOfMonthArrayAttribute(): array
    {
        if (empty($this->schdayofthemonth)) {
            return [];
        }
        $decoded = json_decode($this->schdayofthemonth, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Get decoded annual dates array.
     */
    public function getAnnualDatesArrayAttribute(): array
    {
        if (empty($this->schannualdates)) {
            return [];
        }
        $decoded = json_decode($this->schannualdates, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Calculate the next trigger time based on schedule type.
     */
    public function calculateNextTriggerTime(): Carbon
    {
        $time = $this->schtime ? Carbon::parse($this->schtime) : Carbon::now()->setTime(9, 0, 0);
        $now = Carbon::now();

        switch ($this->scheduleid) {
            case self::SCHEDULED_DAILY:
                return $this->getNextDailyTrigger($time);

            case self::SCHEDULED_WEEKLY:
                return $this->getNextWeeklyTrigger($time);

            case self::SCHEDULED_MONTHLY_BY_DATE:
                return $this->getNextMonthlyTrigger($time);

            case self::SCHEDULED_ANNUALLY:
                return $this->getNextAnnualTrigger($time);

            case self::SCHEDULED_ON_SPECIFIC_DATE:
                $specificDate = json_decode($this->schdate, true);
                if (is_array($specificDate) && !empty($specificDate[0])) {
                    return Carbon::parse($specificDate[0])->setTime($time->hour, $time->minute, 0);
                }
                return Carbon::parse($this->schdate)->setTime($time->hour, $time->minute, 0);

            default:
                return $now->addDay()->setTime($time->hour, $time->minute, 0);
        }
    }

    /**
     * Get the next daily trigger time.
     */
    protected function getNextDailyTrigger(Carbon $time): Carbon
    {
        $next = Carbon::today()->setTime($time->hour, $time->minute, 0);
        if ($next->isPast()) {
            $next->addDay();
        }
        return $next;
    }

    /**
     * Get the next weekly trigger time.
     */
    protected function getNextWeeklyTrigger(Carbon $time): Carbon
    {
        $daysOfWeek = $this->day_of_week_array;
        if (empty($daysOfWeek)) {
            return $this->getNextDailyTrigger($time);
        }

        $now = Carbon::now();
        $nextTrigger = null;

        // Check for next occurrence within the next 7 days
        for ($i = 0; $i <= 7; $i++) {
            $checkDate = $now->copy()->addDays($i);
            $dayOfWeek = $checkDate->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)

            if (in_array($dayOfWeek, $daysOfWeek) || in_array((string) $dayOfWeek, $daysOfWeek)) {
                $candidateTime = $checkDate->copy()->setTime($time->hour, $time->minute, 0);
                if ($candidateTime->isFuture()) {
                    $nextTrigger = $candidateTime;
                    break;
                }
            }
        }

        return $nextTrigger ?? $now->addWeek()->setTime($time->hour, $time->minute, 0);
    }

    /**
     * Get the next monthly trigger time.
     */
    protected function getNextMonthlyTrigger(Carbon $time): Carbon
    {
        $daysOfMonth = $this->day_of_month_array;
        if (empty($daysOfMonth)) {
            return $this->getNextDailyTrigger($time);
        }

        $now = Carbon::now();
        sort($daysOfMonth);

        // Check current month
        foreach ($daysOfMonth as $day) {
            $day = (int) $day;
            if ($day > $now->daysInMonth) {
                continue;
            }
            $candidateDate = Carbon::createFromDate($now->year, $now->month, $day)
                ->setTime($time->hour, $time->minute, 0);
            if ($candidateDate->isFuture()) {
                return $candidateDate;
            }
        }

        // Check next month
        $nextMonth = $now->copy()->addMonth();
        foreach ($daysOfMonth as $day) {
            $day = (int) $day;
            if ($day > $nextMonth->daysInMonth) {
                continue;
            }
            return Carbon::createFromDate($nextMonth->year, $nextMonth->month, $day)
                ->setTime($time->hour, $time->minute, 0);
        }

        return $now->addMonth()->setTime($time->hour, $time->minute, 0);
    }

    /**
     * Get the next annual trigger time.
     */
    protected function getNextAnnualTrigger(Carbon $time): Carbon
    {
        $annualDates = $this->annual_dates_array;
        if (empty($annualDates)) {
            return $this->getNextDailyTrigger($time);
        }

        $now = Carbon::now();
        $nextTrigger = null;

        foreach ($annualDates as $dateString) {
            $date = Carbon::parse($dateString)->setTime($time->hour, $time->minute, 0);

            // If date is in the past this year, try next year
            if ($date->isPast()) {
                $date->addYear();
            }

            if ($nextTrigger === null || $date->lt($nextTrigger)) {
                $nextTrigger = $date;
            }
        }

        return $nextTrigger ?? $now->addYear()->setTime($time->hour, $time->minute, 0);
    }

    /**
     * Get human-readable schedule type label.
     */
    public function getScheduleTypeLabelAttribute(): string
    {
        return match ($this->scheduleid) {
            self::SCHEDULED_DAILY => __('reports::reports.daily'),
            self::SCHEDULED_WEEKLY => __('reports::reports.weekly'),
            self::SCHEDULED_MONTHLY_BY_DATE => __('reports::reports.monthly_by_date'),
            self::SCHEDULED_ANNUALLY => __('reports::reports.yearly'),
            self::SCHEDULED_ON_SPECIFIC_DATE => __('reports::reports.specific_date'),
            default => __('reports::reports.unknown'),
        };
    }

    /**
     * Get formatted next trigger time for display.
     */
    public function getNextTriggerTimeFormattedAttribute(): string
    {
        if (!$this->next_trigger_time) {
            return '-';
        }
        return $this->next_trigger_time->format('Y-m-d H:i');
    }
}
