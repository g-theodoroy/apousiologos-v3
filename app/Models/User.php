<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
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
        ];
    }

    /**
     * με κενό  $description επιστρέφει τo $role_id του ενεργού χρήστη
     */
    public static function getRoleId($description = null)
    {
        if (!$description) return Auth::user()->role_id;
        $roles = config('gth.roles');
        return array_search($description, $roles);
    }

    /**
     * με κενό $id επιστρέφει την $description του ενεργού χρήστη
     */
    public static function getRoleDescription($id = null)
    {
        $roles = config('gth.roles');
        if (!$id) return $roles[Auth::user()->role_id] ?? false;
        return $roles[$id] ?? false;
    }

    public function getNumOfAdmins()
    {
        return User::whereRoleId(1)->count();
    }

    public static function getNumOfKathigites()
    {
        return User::count();
    }

    public function anatheseis()
    {
        return $this->belongsToMany(Anathesi::class);
    }

    protected $appends = [
        'permissions'
    ];

    public function getPermissionsAttribute()
    {
        return [
            'admin' => $this->role_id == 1,
            'teacher' => $this->role_id == 2,
            'student' => $this->role_id == 3,
            'teacherOrAdmin' => $this->role_id < 3,
        ];
    }


    public static function getNames()
    {
        return User::pluck('name', 'id');
    }
}
