<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shortlink extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination',      // Obligatoire : à valider côté contrôleur
        'titre',            // Facultatif
        'chemin_personnalise', // Facultatif, mais unique (contrainte à gérer dans la migration/validation)
        'utm_term',         // Facultatif
        'utm_content',      // Facultatif
        'utm_campaign',     // Facultatif
        'utm_source',       // Facultatif
        'utm_medium',       // Facultatif
    ];

    // Valeurs par défaut pour certains attributs
    // protected $attributes = [
    //     'domaine' => 'tnbresa',
    // ];


    // Tu peux également utiliser $guarded pour spécifier les champs protégés
    protected $guarded = ['domaine'];  // 'domaine' est protégé et ne peut pas être modifié
}
