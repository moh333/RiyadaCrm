<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportModule extends Model
{
    protected $table = 'vtiger_reportmodules';
    protected $primaryKey = 'reportmodulesid';
    public $timestamps = false;
    protected $guarded = [];
}
