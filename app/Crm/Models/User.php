<?php

namespace App\Crm\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\CrmUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<CrmUserFactory> */
    use HasFactory, Notifiable;

    protected static function newFactory(): CrmUserFactory
    {
        return CrmUserFactory::new();
    }

    /**
     * The CRM owns its users table independently from the public website.
     *
     * @var string
     */
    protected $table = 'crm_users';

    public function getConnectionName(): ?string
    {
        return app()->environment('testing') || ! config('database.crm_enabled')
            ? parent::getConnectionName()
            : 'crm';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        $role = $this->role;

        return $role !== null && ($role->slug === 'super_admin'
            || $role->permissions->contains('permission', $permission));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
