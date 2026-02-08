<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class RelCriteria extends Model
{
    protected $table = 'vtiger_relcriteria';
    public $timestamps = false;
    protected $guarded = [];
}
