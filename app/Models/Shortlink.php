<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Shortlink extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination',      // Obligatoire : à valider côté contrôleur
        'titre',            // Facultatif
        'chemin_personnalise', // Facultatif, mais unique
        'utm_term',
        'utm_content',
        'utm_campaign',
        'utm_source',
        'utm_medium',
        'domaine_id',
    ];

    // Valeurs par défaut pour certains attributs
    // protected $attributes = [
    //     'domaine' => 'tnbresa',
    // ];

    // Tu peux également utiliser $guarded pour spécifier les champs protégés
    //protected $guarded = ['domaine'];  // 'domaine' est protégé et ne peut pas être modifié
    // Définir la relation avec Domaine
    public function domaine()
    {
        return $this->belongsTo(Domaine::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($shortlink) {
            // Vérifier l'unicité de la destination
            if (self::where('destination', $shortlink->destination)->exists()) {
                throw new \Exception('La destination doit être unique.');
            }

            // Vérifier l'unicité du chemin personnalisé
            if ($shortlink->chemin_personnalise && self::where('chemin_personnalise', $shortlink->chemin_personnalise)->exists()) {
                throw new \Exception('Le chemin personnalisé doit être unique.');
            }

            // Si chemin_personnalise n'est pas fourni, générer un code unique
            if (empty($shortlink->chemin_personnalise)) {
                do {
                    $code = Str::random(8); // Générer un code de 8 caractères aléatoires
                } while (self::where('chemin_personnalise', $code)->exists());

                $shortlink->chemin_personnalise = $code;
            }

            // Si le titre est vide, essayer d'extraire un titre de la page de destination
            if (empty($shortlink->titre)) {
                $shortlink->titre = self::extractTitleFromPage($shortlink->destination);
            }
        });
    }


    public function clicks()
    {
        return $this->hasMany(Click::class);
    }


    // Fonction pour extraire le titre à partir du contenu HTML de la page
    protected static function extractTitleFromPage($destination)
    {
        $client = new Client();
        try {
            // Faire une requête GET vers l'URL de destination
            $response = $client->get($destination);

            // Vérifier si la réponse est valide
            if ($response->getStatusCode() == 200) {
                $html = (string) $response->getBody();

                // Utiliser une expression régulière pour extraire le contenu de la balise <title>
                if (preg_match('/<title>(.*?)<\/title>/', $html, $matches)) {
                    // Retourner le titre trouvé
                    return $matches[1];
                }
            }
        } catch (RequestException $e) {
            // \Log::error("Erreur lors de la récupération du titre de la page: " . $e->getMessage());
        }

        // Si le titre ne peut pas être extrait, retourner un titre par défaut
        return 'Titre non disponible';
    }
}
