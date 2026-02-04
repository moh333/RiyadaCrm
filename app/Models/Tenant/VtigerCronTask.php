<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VtigerCronTask extends Model
{
    use HasFactory;

    protected $table = 'vtiger_cron_task';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'handler_file',
        'frequency',
        'laststart',
        'lastend',
        'status',
        'module',
        'sequence',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'frequency' => 'integer',
            'laststart' => 'integer',
            'lastend' => 'integer',
            'status' => 'integer',
            'sequence' => 'integer',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status == 1 ? 'Active' : 'Disabled';
    }

    /**
     * Get last run time
     */
    public function getLastRunAttribute(): ?string
    {
        if (!$this->laststart) {
            return null;
        }
        return date('Y-m-d H:i:s', $this->laststart);
    }

    /**
     * Get last end time
     */
    public function getLastEndTimeAttribute(): ?string
    {
        if (!$this->lastend) {
            return null;
        }
        return date('Y-m-d H:i:s', $this->lastend);
    }

    /**
     * Get frequency in human readable format
     */
    public function getFrequencyLabelAttribute(): string
    {
        $seconds = $this->frequency;

        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            return ($seconds / 60) . ' minutes';
        } elseif ($seconds < 86400) {
            return ($seconds / 3600) . ' hours';
        } else {
            return ($seconds / 86400) . ' days';
        }
    }

    /**
     * Check if task is running
     */
    public function isRunning(): bool
    {
        return $this->laststart && !$this->lastend;
    }

    /**
     * Check if task is enabled
     */
    public function isEnabled(): bool
    {
        return $this->status == 1;
    }

    /**
     * Scope to get only active tasks
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope to get tasks by module
     */
    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}

