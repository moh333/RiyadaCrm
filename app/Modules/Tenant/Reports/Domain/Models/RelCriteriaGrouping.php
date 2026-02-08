<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class RelCriteriaGrouping extends Model
{
    protected $table = 'vtiger_relcriteria_grouping';
    public $timestamps = false;
    protected $guarded = [];
}
