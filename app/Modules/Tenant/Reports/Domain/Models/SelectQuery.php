<?php

namespace App\Modules\Tenant\Reports\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SelectQuery extends Model
{
    protected $table = 'vtiger_selectquery';
    protected $primaryKey = 'queryid';
    public $timestamps = false;
    protected $guarded = [];

    public function columns(): HasMany
    {
        return $this->hasMany(SelectColumn::class, 'queryid', 'queryid');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(RelCriteria::class, 'queryid', 'queryid');
    }

    public function criteriaGroupings(): HasMany
    {
        return $this->hasMany(RelCriteriaGrouping::class, 'queryid', 'queryid');
    }
}
