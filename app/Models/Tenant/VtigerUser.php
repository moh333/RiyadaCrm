<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class VtigerUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'vtiger_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Disable timestamps if the table doesn't have created_at/updated_at
     */
    public $timestamps = false;

    /**
     * Custom password field name
     */
    public function getAuthPassword()
    {
        return $this->user_password;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }
}
