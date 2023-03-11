<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'password',
        'birthdate',
        'phone',
        'email',
        'country_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public static function givePermissions()
    {
        $user = Auth::user();
        $user->syncPermissions(['update conference', 'delete conference']);
    }

    public function conferences()
    {
        return $this->hasMany(Conference::class, 'user_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id', 'id');
    }

    public function joinedConferences()
    {
        return $this->belongsToMany(Conference::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(Report::class);
    }

    public function isJoined(Conference $conference)
    {
        return $this->joinedConferences->contains($conference);
    }

    public static function associateUser($model_object)
    {
        $model_object->user()->associate(Auth::user());
        $model_object->save();
    }
}
