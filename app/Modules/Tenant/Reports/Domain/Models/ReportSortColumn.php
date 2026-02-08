<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSortColumn extends Model
{
    protected $table = 'vtiger_reportsortcol';
    public $timestamps = false;
    protected $guarded = [];
}
