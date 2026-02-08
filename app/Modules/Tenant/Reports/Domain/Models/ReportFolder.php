<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportFolder extends Model
{
    protected $table = 'vtiger_reportfolder';
    protected $primaryKey = 'folderid';
    public $timestamps = false;
    protected $guarded = [];
}
