<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSummary extends Model
{
    protected $table = 'vtiger_reportsummary';
    protected $primaryKey = 'reportsummaryid';
    public $timestamps = false;
    protected $guarded = [];
}
