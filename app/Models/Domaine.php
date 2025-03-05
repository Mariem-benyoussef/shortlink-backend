<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domaine extends Model
{
    use HasFactory;
    protected $fillable = ['nom'];

    // Relation un-à-plusieurs avec Shortlink
    public function shortlinks()
    {
        return $this->hasMany(Shortlink::class);
    }
}
