<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'surname',
        'other_names',
        'email',
        'phone',
        'password',
        'is_phone_verified'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            BankDetail::create([
                'user_id' => $model->id,
            ]);

            Identification::create([
                'user_id' => $model->id,
            ]);
        });
    }

    // Relationships

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class);
    }

    public function identification()
    {
        return $this->hasOne(Identification::class);
    }


    /// Helper Methods

    public function hasVerifiedPhone()
    {
        return $this->is_phone_verified;
    }

    public function hasVerifiedBank()
    {
        return $this->bankDetail->is_verified;
    }

    public function hasVerifiedId()
    {
        return $this->identification->is_verified;
    }

    public function hasVerifiedProfile()
    {
        return $this->hasVerifiedEmail() && $this->hasVerifiedPhone() && $this->hasVerifiedBank() && $this->hasVerifiedId();
    }

    // Attribute

    public function getFullNameAttribute()
    {
        return ucfirst($this->surname) . ' ' . ucfirst($this->other_names);
    }

    public function getInitialsAttribute()
    {
        return ucfirst($this->surname[0]) . ucfirst($this->other_names[0]);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }


    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
















    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
