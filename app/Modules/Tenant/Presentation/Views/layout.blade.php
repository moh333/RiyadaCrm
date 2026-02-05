<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('tenant::tenant.dashboard') }} - Riyada CRM</title>
    <link rel="icon" type="image/svg+xml" href="{{ global_asset('favicon.svg') }}">

    <!-- Bootstrap 5 CSS -->
    @if(app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    @endif

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    @if(app()->getLocale() == 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
    @endif

    <!-- intl-tel-input CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/css/intlTelInput.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    @stack('styles')

    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #6366f1;
            --primary-light: #eef2ff;
            --bg-light: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: '{{ app()->getLocale() == 'ar' ? 'Cairo' : 'Plus Jakarta Sans' }}', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}
            : 0;
            top: 0;
            background: white;
            border-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 1px solid #e2e8f0;
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .logo-box {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.75rem;
        }

        .nav-list {
            list-style: none;
            padding: 1.5rem 1rem;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }


        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .nav-link i {
            font-size: 1.25rem;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.75rem;
        }

        .nav-link:hover,
        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }

        /* Top Bar */
        .topbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .search-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            width: 300px;
        }

        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0.5rem;
            width: 100%;
            font-size: 0.875rem;
        }

        /* Cards & Components */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
        }

        .stats-card {
            padding: 1.5rem;
            display: flex;
            align-items: center;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 1rem;
        }

        .bg-soft-primary {
            background: #eef2ff;
            color: #6366f1;
        }

        .bg-soft-success {
            background: #f0fdf4;
            color: #22c55e;
        }

        .bg-soft-warning {
            background: #fffbeb;
            color: #f59e0b;
        }

        .bg-soft-danger {
            background: #fef2f2;
            color: #ef4444;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 0.75rem;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX({{ app()->getLocale() == 'ar' ? '100%' : '-100%' }});
            }

            .main-content {
                margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0;
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }

        /* intl-tel-input tweaks */
        .iti {
            width: 100%;
            display: block;
        }

        .iti__country-list {
            z-index: 1050;
        }

        .iti input {
            padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 50px !important;
        }

        /* Select2 Tweaks */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem;
            /* rounded-3 */
            border-color: #dee2e6;
            min-height: 38px;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: #212529;
            padding-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0.75rem;
        }

        .select2-search__field {
            border-radius: 0.375rem;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <div class="logo-box">
                <i class="bi bi-rocket-takeoff-fill"></i>
            </div>
            <span class="fs-5 fw-bold">Tenan<span class="text-primary">tHub</span></span>
        </div>

        <ul class="nav-list">
            <li class="nav-item">
                <a href="{{ route('tenant.dashboard') }}"
                    class="nav-link {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    {{ __('tenant::tenant.dashboard') }}
                </a>
            </li>

            <li class="nav-item mt-3">
                <small
                    class="text-muted fw-bold text-uppercase px-3">{{ __('tenant::tenant.modules') ?? 'Modules' }}</small>
            </li>

            @foreach($groupedModules as $appName => $modules)
                <li class="nav-item">
                    @php
                        $groupId = 'submenu_' . strtolower(str_replace(' ', '_', $appName));
                        $isActiveGroup = false;
                        foreach ($modules as $m)
                            if (request()->is('modules/' . $m->name . '*'))
                                $isActiveGroup = true;
                    @endphp

                    <a class="nav-link {{ $isActiveGroup ? '' : 'collapsed' }} d-flex justify-content-between align-items-center"
                        data-bs-toggle="collapse" href="#{{ $groupId }}" role="button"
                        aria-expanded="{{ $isActiveGroup ? 'true' : 'false' }}" aria-controls="{{ $groupId }}">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-folder-fill"></i>
                            {{ vtranslate($appName, 'Vtiger') }}
                        </div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    <div class="collapse {{ $isActiveGroup ? 'show' : '' }}" id="{{ $groupId }}">
                        <ul class="list-unstyled fw-normal pb-1 small bg-light rounded-bottom px-2 pt-1 mt-1">
                            @foreach($modules as $module)
                                <li>
                                    <a href="{{ route('tenant.modules.index', $module->name) }}"
                                        class="nav-link {{ request()->is('modules/' . $module->name . '*') ? 'active' : '' }} ps-4 py-2">
                                        <i class="bi bi-layers me-2" style="font-size: 0.9rem;"></i>
                                        {{ $module->getLabel() }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endforeach

            <li class="nav-item mt-4">
                <small class="text-muted fw-bold text-uppercase px-3">{{ __('tenant::tenant.administration') }}</small>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#moduleMgmtSubmenu" role="button" aria-expanded="false"
                    aria-controls="moduleMgmtSubmenu">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        {{ __('tenant::tenant.module_management') }}
                    </div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse {{ request()->routeIs('tenant.settings.modules.*') ? 'show' : '' }}"
                    id="moduleMgmtSubmenu">
                    <ul class="list-unstyled fw-normal pb-1 small bg-light rounded-bottom px-2 pt-1">
                        <li>
                            <a href="{{ route('tenant.settings.modules.menu') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.modules.menu') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-list-task me-2"></i>
                                {{ __('tenant::tenant.menu_management') ?? 'Menu Management' }}
                            </a>
                        </li>

                        <li><a href="{{ route('tenant.settings.modules.layouts') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.modules.layouts') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-layout-text-window-reverse me-2"></i>
                                {{ __('tenant::tenant.module_layouts_fields') ?? 'Layouts & Fields' }}</a>
                        </li>

                        <li><a href="{{ route('tenant.settings.modules.numbering.selection') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.modules.numbering.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-123 me-2"></i>
                                {{ __('tenant::tenant.module_numbering') ?? 'Numbering' }}</a>
                        </li>

                        <li><a href="{{ route('tenant.settings.modules.relations.selection') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.modules.relations.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-diagram-3 me-2"></i>
                                {{ __('tenant::tenant.module_relations') ?? 'Relations' }}</a>
                        </li>

                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#userMgmtSubmenu" role="button" aria-expanded="false"
                    aria-controls="userMgmtSubmenu">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people-fill"></i>
                        {{ __('tenant::users.user_management') }}
                    </div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse {{ request()->routeIs('tenant.settings.users.*') ? 'show' : '' }}"
                    id="userMgmtSubmenu">
                    <ul class="list-unstyled fw-normal pb-1 small bg-light rounded-bottom px-2 pt-1">
                        <li><a href="{{ route('tenant.settings.users.index') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.users.index') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-person me-2"></i> {{ __('tenant::users.users') }}</a></li>
                        <li><a href="{{ route('tenant.settings.users.roles.index') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.users.roles.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-diagram-3 me-2"></i> {{ __('tenant::users.roles') }}</a></li>
                        <li><a href="{{ route('tenant.settings.users.profiles.index') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.users.profiles.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-person-badge me-2"></i> {{ __('tenant::users.profiles') }}</a></li>
                        <li><a href="{{ route('tenant.settings.users.sharing-rules.index') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.users.sharing-rules.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-share me-2"></i> {{ __('tenant::users.sharing_rules') }}</a></li>
                        <li><a href="{{ route('tenant.settings.users.groups.index') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.users.groups.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-people me-2"></i> {{ __('tenant::users.groups') }}</a></li>
                        <li><a href="{{ route('tenant.settings.users.login-history.index') }}"
                                class="nav-link {{ request()->routeIs('tenant.settings.users.login-history.*') ? 'active' : '' }} ps-4"><i
                                    class="bi bi-clock-history me-2"></i> {{ __('tenant::users.login_history') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link collapsed d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#crmSettingsSubmenu" role="button"
                    aria-expanded="{{ request()->is('settings/crm*') || request()->is('settings/preferences*') || request()->is('settings/calendar*') || request()->is('settings/tags*') ? 'true' : 'false' }}"
                    aria-controls="crmSettingsSubmenu">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-sliders"></i>
                        {{ __('tenant::settings.crm_settings') ?? 'CRM Settings' }}
                    </div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse {{ request()->is('settings/crm*') || request()->is('settings/preferences*') || request()->is('settings/calendar*') || request()->is('settings/tags*') ? 'show' : '' }}"
                    id="crmSettingsSubmenu">
                    <ul class="list-unstyled fw-normal pb-1 small bg-light rounded-bottom px-2 pt-1">

                        {{-- Configuration Submenu --}}
                        <li>
                            <a class="nav-link collapsed d-flex justify-content-between align-items-center ps-4"
                                data-bs-toggle="collapse" href="#configurationSubmenu" role="button"
                                aria-expanded="{{ request()->routeIs('tenant.settings.crm.company.*') || request()->routeIs('tenant.settings.crm.portal.*') || request()->routeIs('tenant.settings.crm.currency.*') || request()->routeIs('tenant.settings.crm.mail.*') || request()->routeIs('tenant.settings.crm.config.*') || request()->routeIs('tenant.settings.crm.picklist.*') || request()->routeIs('tenant.settings.crm.picklist-dependency.*') ? 'true' : 'false' }}"
                                aria-controls="configurationSubmenu">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-gear-wide-connected me-2"></i>
                                    {{ __('tenant::settings.configuration') ?? 'Configuration' }}
                                </div>
                                <i class="bi bi-chevron-down small"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('tenant.settings.crm.company.*') || request()->routeIs('tenant.settings.crm.portal.*') || request()->routeIs('tenant.settings.crm.currency.*') || request()->routeIs('tenant.settings.crm.mail.*') || request()->routeIs('tenant.settings.crm.config.*') || request()->routeIs('tenant.settings.crm.picklist.*') || request()->routeIs('tenant.settings.crm.picklist-dependency.*') ? 'show' : '' }}"
                                id="configurationSubmenu">
                                <ul class="list-unstyled fw-normal pb-1 small bg-white rounded-bottom px-2 pt-1 ms-3">
                                    <li><a href="{{ route('tenant.settings.crm.company.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.company.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-building me-2"></i>
                                            {{ __('tenant::settings.company_details') ?? 'Company Details' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.crm.portal.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.portal.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-person-circle me-2"></i>
                                            {{ __('tenant::settings.customer_portal') ?? 'Customer Portal' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.crm.currency.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.currency.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-currency-exchange me-2"></i>
                                            {{ __('tenant::settings.currencies') ?? 'Currencies' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.crm.mail.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.mail.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-envelope-at me-2"></i>
                                            {{ __('tenant::settings.outgoing_server') ?? 'Outgoing Server' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.crm.config.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.config.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-code-square me-2"></i>
                                            {{ __('tenant::settings.config_editor') ?? 'Config Editor' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.crm.picklist.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.picklist.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-list-ul me-2"></i>
                                            {{ __('tenant::settings.picklist') ?? 'Picklist' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.crm.picklist-dependency.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.picklist-dependency.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-diagram-3 me-2"></i>
                                            {{ __('tenant::settings.picklist_dependency') ?? 'Picklist Dependency' }}</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Automation Submenu --}}
                        <li>
                            <a class="nav-link collapsed d-flex justify-content-between align-items-center ps-4"
                                data-bs-toggle="collapse" href="#automationSubmenu" role="button"
                                aria-expanded="{{ request()->routeIs('tenant.settings.crm.automation.*') ? 'true' : 'false' }}"
                                aria-controls="automationSubmenu">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightning-charge me-2"></i>
                                    {{ __('tenant::settings.automation') ?? 'Automation' }}
                                </div>
                                <i class="bi bi-chevron-down small"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('tenant.settings.crm.automation.*') ? 'show' : '' }}"
                                id="automationSubmenu">
                                <ul class="list-unstyled fw-normal pb-1 small bg-white rounded-bottom px-2 pt-1 ms-3">
                                    <li>
                                        <a href="{{ route('tenant.settings.crm.automation.scheduler.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.automation.scheduler.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-clock-history me-2"></i>
                                            {{ __('tenant::settings.scheduler') ?? 'Scheduler' }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('tenant.settings.crm.automation.workflows.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.automation.workflows.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-diagram-2 me-2"></i>
                                            {{ __('tenant::settings.workflows') ?? 'Workflows' }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Inventory Settings --}}
                        <li>
                            <a class="nav-link collapsed d-flex justify-content-between align-items-center ps-4"
                                data-bs-toggle="collapse" href="#inventorySubmenu" role="button"
                                aria-expanded="{{ request()->routeIs('tenant.settings.crm.tax.*') || request()->routeIs('tenant.settings.crm.terms.*') ? 'true' : 'false' }}"
                                aria-controls="inventorySubmenu">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-box-seam me-2"></i>
                                    {{ __('tenant::settings.inventory_settings') ?? 'Inventory Settings' }}
                                </div>
                                <i class="bi bi-chevron-down small"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('tenant.settings.crm.tax.*') || request()->routeIs('tenant.settings.crm.terms.*') ? 'show' : '' }}"
                                id="inventorySubmenu">
                                <ul class="list-unstyled fw-normal pb-1 small bg-white rounded-bottom px-2 pt-1 ms-3">
                                    <li>
                                        <a href="{{ route('tenant.settings.crm.tax.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.tax.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-percent me-2"></i>
                                            {{ __('tenant::settings.tax_management') ?? 'Tax Management' }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('tenant.settings.crm.terms.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.crm.terms.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-file-text me-2"></i>
                                            {{ __('tenant::settings.terms_conditions') ?? 'Terms & Conditions' }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- My Preferences Submenu --}}
                        <li>
                            <a class="nav-link collapsed d-flex justify-content-between align-items-center ps-4"
                                data-bs-toggle="collapse" href="#preferencesMenu" role="button"
                                aria-expanded="{{ request()->routeIs('tenant.settings.preferences.*') || request()->routeIs('tenant.settings.calendar.*') || request()->routeIs('tenant.settings.tags.*') ? 'true' : 'false' }}"
                                aria-controls="preferencesMenu">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-gear me-2"></i>
                                    {{ __('tenant::settings.my_preferences') ?? 'My Preferences' }}
                                </div>
                                <i class="bi bi-chevron-down small"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('tenant.settings.preferences.*') || request()->routeIs('tenant.settings.calendar.*') || request()->routeIs('tenant.settings.tags.*') ? 'show' : '' }}"
                                id="preferencesMenu">
                                <ul class="list-unstyled fw-normal pb-1 small bg-white rounded-bottom px-2 pt-1 ms-3">
                                    <li><a href="{{ route('tenant.settings.preferences.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.preferences.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-sliders me-2"></i>
                                            {{ __('tenant::settings.user_preferences') ?? 'User Preferences' }}</a></li>
                                    <li><a href="{{ route('tenant.settings.calendar.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.calendar.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-calendar3 me-2"></i>
                                            {{ __('tenant::settings.calendar_settings') ?? 'Calendar Settings' }}</a>
                                    </li>
                                    <li><a href="{{ route('tenant.settings.tags.index') }}"
                                            class="nav-link {{ request()->routeIs('tenant.settings.tags.*') ? 'active' : '' }} ps-4"><i
                                                class="bi bi-tags me-2"></i>
                                            {{ __('tenant::settings.my_tags') ?? 'My Tags' }}</a></li>
                                </ul>
                            </div>
                        </li>

                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a href="{{ route('tenant.settings') }}"
                    class="nav-link {{ request()->routeIs('tenant.settings') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    {{ __('tenant::tenant.settings') }}
                </a>
                </a>
            </li>

            <!-- Language Switcher in Sidebar for mobile or easy access -->
            <li class="nav-item mt-auto border-top pt-3">
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('lang.switch', 'en') }}"
                        class="btn btn-sm {{ app()->getLocale() == 'en' ? 'btn-primary' : 'btn-outline-secondary' }}">EN</a>
                    <a href="{{ route('lang.switch', 'ar') }}"
                        class="btn btn-sm {{ app()->getLocale() == 'ar' ? 'btn-primary' : 'btn-outline-secondary' }}">AR</a>
                </div>
            </li>
        </ul>

        <div class="p-3 border-top mt-2">
            <form action="{{ route('tenant.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                    <i
                        class="bi bi-box-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-2"></i>{{ __('tenant::tenant.sign_out') }}
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar justify-content-between mb-4 rounded-4 shadow-sm px-4">
            <div class="search-box">
                <i class="bi bi-search text-muted"></i>
                <input type="text" placeholder="{{ __('tenant::tenant.search_placeholder') }}">
            </div>

            <div class="d-flex align-items-center gap-4">
                <div class="position-relative">
                    <i class="bi bi-bell fs-5 text-muted"></i>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>

                <div class="user-profile dropdown">
                    <div class="d-flex align-items-center" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name={{ auth('tenant')->user()->user_name }}&background=6366f1&color=fff"
                            class="user-avatar" alt="User">
                        <div class="d-none d-md-block">
                            <h6 class="mb-0 fw-bold">{{ auth('tenant')->user()->user_name }}</h6>
                            <small class="text-muted">{{ __('tenant::tenant.administrator') }}</small>
                        </div>
                    </div>
                    <ul
                        class="dropdown-menu dropdown-menu-{{ app()->getLocale() == 'ar' ? 'start' : 'end' }} shadow border-0 mt-3 rounded-4">
                        <li><a class="dropdown-item py-2" href="{{ route('tenant.profile') }}"><i
                                    class="bi bi-person me-2"></i>
                                {{ __('tenant::tenant.my_profile') }}</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('tenant.settings') }}"><i
                                    class="bi bi-gear me-2"></i>
                                {{ __('tenant::tenant.settings') }}</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('tenant.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i
                                        class="bi bi-box-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-2"></i>
                                    {{ __('tenant::tenant.sign_out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        @yield('content')
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (Required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- intl-tel-input JS -->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        function deleteFile(recordId, field, filePath, elementId) {
            if (!confirm('{{ __("contacts::contacts.are_you_sure") }}')) return;

            const url = `{{ route("tenant.contacts.index") }}/${recordId}/delete-file`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field: field,
                    file_path: filePath
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const element = document.getElementById(elementId);
                        if (element) {
                            element.classList.add('fade');
                            setTimeout(() => element.remove(), 300);
                        }
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the file.');
                });
        }

        // Initialize things
        document.addEventListener('DOMContentLoaded', function () {

            // Select2 Initialization for all .select2 inputs
            // We use jQuery because Select2 is jQuery dependent
            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dir: '{{ app()->getLocale() == "ar" ? "rtl" : "ltr" }}',
                    placeholder: $(this).data('placeholder'),
                    allowClear: true
                });
            } else {
                // Should load jQuery if simple select2 requires it, but CDN might not bundle it.
                // It seems I missed to include jQuery which is required for Select2 (standard version).
                // I will add jQuery as well.
                console.warn('jQuery not found for Select2');
            }

            // Phone Input
            if (typeof window.intlTelInput !== 'undefined') {
                document.querySelectorAll('.phone-input:not(.iti-initialized)').forEach(input => {
                    input.classList.add('iti-initialized');
                    const iti = window.intlTelInput(input, {
                        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js",
                        initialCountry: "auto",
                        geoIpLookup: callback => {
                            fetch("https://ipapi.co/json")
                                .then(res => res.json())
                                .then(data => callback(data.country_code))
                                .catch(() => callback("SA"));
                        },
                        separateDialCode: true,
                        autoPlaceholder: "aggressive",
                        nationalMode: false,
                        formatOnDisplay: true
                    });

                    // Update input with full international number before form submission & VALIDATE
                    const form = input.closest('form');
                    if (form) {
                        form.addEventListener('submit', function (e) {
                            if (input.value.trim()) {
                                if (iti.isValidNumber()) {
                                    // Replace value with full formatted international number
                                    input.value = iti.getNumber();
                                } else {
                                    e.preventDefault();
                                    input.classList.add('is-invalid');
                                    alert('{{ __("contacts::contacts.invalid_phone") }} (' + input.placeholder + ')');
                                    input.focus();
                                }
                            }
                        });

                        input.addEventListener('input', function () {
                            input.classList.remove('is-invalid');
                        });
                    }
                });
            }
        });
    </script>
    @stack('scripts')
    @yield('scripts')
</body>

</html>