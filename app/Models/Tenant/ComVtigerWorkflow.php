<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComVtigerWorkflow extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'workflow_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module_name',
        'summary',
        'test',
        'execution_condition',
        'defaultworkflow',
        'type',
        'filtersavedinnew',
        'schtypeid',
        'schtime',
        'schdayofmonth',
        'schdayofweek',
        'schannualdates',
        'nexttrigger_time',
        'status',
        'workflowname',
        'schdayofweekexclude',
        'timefrom',
        'timeto',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'workflow_id' => 'integer',
            'execution_condition' => 'integer',
            'status' => 'integer',
            'defaultworkflow' => 'integer',
            'filtersavedinnew' => 'integer',
            'schtypeid' => 'integer',
        ];
    }
}
