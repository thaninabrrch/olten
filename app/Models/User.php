<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use App\Models\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerifyEmailCustom;
use App\Models\Category;
use App\Models\Subscription;

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
            // $timestamps = false empeche Eloquent d'ecrire created_at/updated_at,
            // mais il lui retire aussi la conversion en Carbon a la lecture : les
            // lignes qui ont malgre tout une date la renvoyaient en chaine, et tout
            // appel type ->translatedFormat() plantait. Le cast la retablit.
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
        return $this->belongsToMany(
            Ad::class,
            'favorites',
            'user_id',
            'ad_id'
        )->withTimestamps();
    }

    public function productFavorites()
    {
        return $this->belongsToMany(
            Product::class,
            'favorites',
            'user_id',
            'product_id'
        )->withTimestamps();
    }

    public function hasFavorited(Ad $ad)
    {
        return $this->favorites()
            ->where('ad_id', $ad->id)
            ->exists();
    }

    public function hasFavoritedProduct(Product $product)
    {
        return $this->productFavorites()
            ->where('product_id', $product->id)
            ->exists();
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

    public function hasPremiumSubscription(): bool
    {
        return $this->subscription?->slug === 'premium';
    }

    public function notificationCategories()
    {
        return $this->belongsToMany(
            Category::class,
            'notification_preferences'
        );
    }
    public function trips()        { return $this->hasMany(Covoiturage::class, 'conducteur_id'); }
    public function tripBookings() { return $this->hasMany(TripBooking::class); }
    public function tripAlerts()   { return $this->hasMany(TripAlert::class); }

    /** Demandes de réservation reçues sur ses trajets, en attente de sa réponse. */
    public function pendingTripRequests()
    {
        return TripBooking::query()->pending()
            ->whereHas('trip', fn ($q) => $q->where('conducteur_id', $this->id));
    }

    /*
    |--------------------------------------------------------------------------
    | Identite montree aux autres membres
    |--------------------------------------------------------------------------
    | Par exemple dans la liste des passagers d'un trajet : le prenom et le
    | nom (« Sarah Amrani »), la photo ou les initiales, jamais les
    | coordonnees.
    */

    public function publicName(): Attribute
    {
        return Attribute::get(function () {
            [$first, $last] = $this->nameParts();

            return trim(Str::ucfirst($first) . ' ' . Str::ucfirst($last)) ?: 'Membre';
        });
    }

    public function initials(): Attribute
    {
        return Attribute::get(function () {
            [$first, $last] = $this->nameParts();

            return mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1)) ?: '?';
        });
    }

    /** Photo de profil : chemin sur le disque public, ou adresse deja complete. */
    public function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            $photo = $this->profile_photo;

            if (! $photo) {
                return null;
            }

            return Str::startsWith($photo, ['http://', 'https://', '/']) ? $photo : asset('storage/' . ltrim($photo, '/'));
        });
    }

    /** Prenom et nom ; les comptes sans ces champs sont lus dans `name`. */
    private function nameParts(): array
    {
        $first = trim((string) $this->firstname);
        $last  = trim((string) $this->lastname);

        if ($first === '') {
            $words = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY);
            $first = (string) array_shift($words);
            $last  = $last !== '' ? $last : (string) array_pop($words);
        }

        return [$first, $last];
    }
}
