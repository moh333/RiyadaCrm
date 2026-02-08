<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportGroupByColumn extends Model
{
    protected $table = 'vtiger_reportgroupbycolumn';
    public $timestamps = false;
    protected $guarded = [];
}
