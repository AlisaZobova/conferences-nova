<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Billable;

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
        'birthdate' => 'date'
    ];

    protected $appends = ['credits', 'has_card', 'active_subscription'];

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
        return $this->belongsToMany(Conference::class)->withTimestamps();
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

    public function getCreditsAttribute()
    {
        $subscription = self::getActiveSubscriptionAttribute();

        if ($subscription) {

            $joins = self::joinedConferences()
                ->whereDate(
                    'conference_user.created_at',
                    '>=',
                    $this->getMonthsAgo($subscription->ends_at?:now(), 1)
                )
                ->whereDate(
                    'conference_user.created_at',
                    '<=',
                    $subscription->ends_at ?: now()
                )
                ->count();

            $plan = Plan::where('name', $this->subscriptions[0]->name)->first();

            if ($plan->joins_per_month) {
                $credits = $plan->joins_per_month - $joins;
                return max($credits, 0);
            }
            else {
                return 'unlimited';
            }
        }
        else {
            return false;
        }
    }

    public function getHasCardAttribute()
    {
        return $this->hasStripeId() && $this->hasPaymentMethod('card');
    }

    public function getActiveSubscriptionAttribute()
    {
        return $this->subscriptions()->where('stripe_status', 'active')->first();
    }

    public function loadRelationships()
    {
        return $this->load(
            'roles',
            'conferences:id,user_id',
            'joinedConferences:id,user_id',
            'favorites'
        );
    }

    public static function getMonthsAgo($date, int $n): string
    {
        $date = new \DateTime($date);
        $day  = $date->format('j');
        $date->modify('first day of this month')->modify('-' . $n . ' months');
        if ($day > $date->format('t')) {
            $day = $date->format('t');
        }
        $date->setDate($date->format('Y'), $date->format('m'), $day);
        return $date->format('Y-m-d');
    }
}
