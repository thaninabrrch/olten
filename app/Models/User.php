<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use App\Models\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailCustom;

class User extends Authenticatable implements LaratrustUser, MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use HasRolesAndPermissions;

    public $timestamps = false;

    protected $fillable = [
        'name','firstname','lastname','email','password','about_me',
        'phone','gender','disable_email_notifications','x_com','facebook',
        'linkedin','instagram','youtube','tiktok','whatsapp',
        'identity_verification','profile_photo','is_admin','verifie','role','is_vtc_driver', 'is_approved', 'subscription_id', 'subscription_expired_at'
    ];

    protected $hidden = ['password','remember_token'];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_vtc_driver' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'locataire_id');
    }
    public function objets()
    {
        return $this->hasMany(Objet::class, 'proprietaire_id');
    }
    public function covoiturages()
    {
        return $this->hasMany(Covoiturage::class, 'conducteur_id');
    }
    public function livraisonsRepas()
    {
        return $this->hasMany(LivraisonRepas::class, 'livreur_id');
    }
    // public function livraisonsColis()
    // {
    //     return $this->hasMany(LivraisonColis::class, 'livreur_id');
    // }
    public function livraisonsVtc()
    {
        return $this->hasMany(LivraisonVtc::class, 'chauffeur_id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function pointsFidelite()
    {
        return $this->hasMany(PointsFidelite::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function favorites()
    {
        return $this->belongsToMany(Ad::class, 'favorites', 'user_id', 'ad_id')->withTimestamps();
    }

    public function hasFavorited(Ad $ad)
    {
        return $this->favorites()->where('ad_id', $ad->id)->exists();
    }
    
    public function demandesLivreur()
    {
        return $this->hasMany(DemandeLivreur::class, 'id_livreur');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'delivery_person_id');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailCustom());
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
