<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Modules\Tenant\Core\Domain\Services\ModuleRegistry;

class TenantSidebarComposer
{
    public function __construct(
        protected ModuleRegistry $registry
    ) {
    }

    public function compose(View $view)
    {
        $modules = collect($this->registry->all())->unique('name');
        $view->with('groupedModules', $modules->groupBy('appName'));
    }
}
