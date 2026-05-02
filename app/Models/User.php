<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'employee_id',
        'department',
        'job_title',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'last_login_at' => 'datetime',
        ];
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function loginOtpCodes(): HasMany
    {
        return $this->hasMany(LoginOtpCode::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(BookingChangeRequest::class);
    }

    public function assignedContactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class, 'assigned_to');
    }

    public function isAdmin(): bool
    {
        return UserRole::normalize($this->role) === UserRole::ADMIN;
    }

    public function isStaff(): bool
    {
        return UserRole::normalize($this->role) === UserRole::STAFF;
    }

    public function isManager(): bool
    {
        return UserRole::normalize($this->role) === UserRole::MANAGER;
    }

    public function isCustomer(): bool
    {
        return in_array($this->role, UserRole::customerValues(), true);
    }

    public function isBackoffice(): bool
    {
        return in_array(UserRole::normalize($this->role), UserRole::backofficeValues(), true);
    }

    public function roleLabel(): string
    {
        return UserRole::label($this->role);
    }

    public function canViewReports(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canViewUsers(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageMasterData(): bool
    {
        return $this->isAdmin();
    }

    public function canViewOperations(): bool
    {
        return $this->isBackoffice();
    }

    public function canManageSupportInbox(): bool
    {
        return $this->isBackoffice();
    }
}
