<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class SelectColumn extends Model
{
    protected $table = 'vtiger_selectcolumn';
    public $timestamps = false;
    protected $guarded = [];
}
