<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    protected $table = 'vtiger_report';
    protected $primaryKey = 'reportid';
    public $timestamps = false; // VTiger tables usually don't have Laravel timestamps

    protected $guarded = [];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ReportFolder::class, 'folderid', 'folderid');
    }

    public function selectQuery(): BelongsTo
    {
        return $this->belongsTo(SelectQuery::class, 'queryid', 'queryid');
    }

    public function modules(): HasOne
    {
        return $this->hasOne(ReportModule::class, 'reportmodulesid', 'reportid');
    }

    public function sortColumns(): HasMany
    {
        return $this->hasMany(ReportSortColumn::class, 'reportid', 'reportid');
    }

    public function groupByColumns(): HasMany
    {
        return $this->hasMany(ReportGroupByColumn::class, 'reportid', 'reportid');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(ReportSummary::class, 'reportsummaryid', 'reportid');
    }
}
