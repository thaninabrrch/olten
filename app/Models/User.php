<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use App\Models\Role;

class User extends Authenticatable implements LaratrustUser
{
    use HasFactory, Notifiable;
    use HasRolesAndPermissions;

    public $timestamps = false;

    protected $fillable = [
        'name','firstname','lastname','email','password','about_me',
        'phone','gender','disable_email_notifications','x_com','facebook',
        'linkedin','instagram','youtube','tiktok','whatsapp',
        'identity_verification','profile_photo','is_admin','verifie','role'
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function reservations() { return $this->hasMany(Reservation::class,'locataire_id'); }
    public function objets() { return $this->hasMany(Objet::class,'proprietaire_id'); }
    public function covoiturages() { return $this->hasMany(Covoiturage::class,'conducteur_id'); }
    public function livraisonsRepas() { return $this->hasMany(LivraisonRepas::class,'livreur_id'); }
    public function livraisonsColis() { return $this->hasMany(LivraisonColis::class,'livreur_id'); }
    public function livraisonsVtc() { return $this->hasMany(LivraisonVtc::class,'chauffeur_id'); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function pointsFidelite() { return $this->hasMany(PointsFidelite::class); }
}
