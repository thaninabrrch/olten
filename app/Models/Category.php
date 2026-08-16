<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'description', 'image', 'service_id', 'slug', 'icon'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }


    public function objets()
    {
        return $this->hasMany(Objet::class, 'categorie_id');
    }

    public function ads()
    {
        return $this->hasMany(Ad::class, 'category_id');
    }
}
