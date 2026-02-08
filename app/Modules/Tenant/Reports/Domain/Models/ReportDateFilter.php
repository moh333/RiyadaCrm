<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportDateFilter extends Model
{
    protected $table = 'vtiger_reportdatefilter';
    protected $primaryKey = 'datefilterid';
    public $timestamps = false;
    protected $guarded = [];
}
