<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::orderBy('id','desc')->get();
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'db_name' => 'required|string|max:64|alpha_dash',
            'db_host' => 'nullable|string',
            'db_username' => 'nullable|string',
            'db_password' => 'nullable|string',
        ]);

        $host = $data['db_host'] ?? env('DB_HOST','127.0.0.1');
        $username = $data['db_username'] ?? env('DB_USERNAME','root');
        $password = $data['db_password'] ?? env('DB_PASSWORD',null);
        $dbName = $data['db_name'];

        // Create the database for the tenant
        try {
            $charset = config('database.connections.' . config('database.default') . '.charset', 'utf8mb4');
            $collation = config('database.connections.' . config('database.default') . '.collation', 'utf8mb4_unicode_ci');
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$collation}");
        } catch (\Exception $e) {
            return back()->withErrors(['db' => 'Failed to create database: '.$e->getMessage()])->withInput();
        }

        // Save tenant record
        $tenant = Tenant::create([
            'name' => $data['name'],
            'db_name' => $dbName,
            'db_host' => $host,
            'db_username' => $username,
            'db_password' => $password,
        ]);

        // Configure runtime connection for tenant
        Config::set('database.connections.tenant', [
            'driver' => config('database.default'),
            'host' => $host,
            'database' => $dbName,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Create a sample table in the tenant database so the DB is usable
        try {
            Schema::connection('tenant')->create('tenant_sample', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        } catch (\Exception $e) {
            // ignore if table exists or creation fails; tenant DB still created
        }

        return redirect()->route('tenants.index')->with('success', 'Tenant created');
    }
}
