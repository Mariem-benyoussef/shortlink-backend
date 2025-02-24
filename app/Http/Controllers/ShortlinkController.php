<?php

namespace App\Http\Controllers;

use App\Models\Shortlink;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;

class ShortlinkController extends Controller
{
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    // Afficher la liste des shortlinks (en tant qu'API)
    public function index()
    {
        $shortlinks = Shortlink::all();
        return response()->json($shortlinks);
    }

    // Créer un nouveau shortlink (en tant qu'API)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|url',
            'titre' => 'nullable|string',
            'chemin_personnalise' => 'nullable|unique:shortlinks',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_term' => 'nullable|string',
            'utm_content' => 'nullable|string',
        ]);

        $shortlink = Shortlink::create($validated);

        return response()->json(['message' => 'Lien créé avec succès!', 'data' => $shortlink]);
    }

    // Afficher un shortlink spécifique (en tant qu'API)
    public function show($id)
    {
        $shortlink = Shortlink::findOrFail($id);
        return response()->json($shortlink);
    }

    // Mettre à jour un shortlink existant
    public function update(Request $request, $id)
    {
        // Validation des données
        $validated = $request->validate([
            'destination' => 'required|url',
            'titre' => 'nullable|string',
            'chemin_personnalise' => 'nullable|unique:shortlinks,chemin_personnalise,' . $id,
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'utm_term' => 'nullable|string',
            'utm_content' => 'nullable|string',
        ]);

        // Trouver le shortlink à mettre à jour
        $shortlink = Shortlink::findOrFail($id);

        // Mettre à jour les données du shortlink
        $shortlink->update($validated);

        // Retourner la réponse JSON avec le shortlink mis à jour
        return response()->json($shortlink);
    }

    // Supprimer un shortlink
    public function destroy($id)
    {
        // Trouver le shortlink à supprimer
        $shortlink = Shortlink::findOrFail($id);

        // Supprimer le shortlink
        $shortlink->delete();

        // Retourner une réponse de succès
        return response()->json(['message' => 'Shortlink deleted successfully']);
    }


    // Vérifier si le chemin personnalisé est unique
    public function checkCheminUnique(Request $request)
    {
        $chemin = $request->input('chemin_personnalise');

        // Vérifier si le chemin est déjà utilisé
        $exists = Shortlink::where('chemin_personnalise', $chemin)->exists();

        return response()->json(['isUnique' => !$exists]);
    }

    public function checkDestinationUnique(Request $request)
    {
        $destination = $request->input('destination');

        // Vérifier si le chemin est déjà utilisé
        $exists = Shortlink::where('destination', $destination)->exists();

        return response()->json(['isUnique' => !$exists]);
    }



    public function showShortlinkDetails(Request $request, string $destination)
    {
        // Check if the destination exists in the database (shortlinks table)
        // $shortlink = Shortlink::where('destination', $destination)->first();

        // if (!$shortlink) {
        //     return response()->json(['error' => 'Shortlink does not exist in the database'], 404);
        // }

        try {
            $shortlinkInfo = $this->analyticsService->getShortlinkInfoFromGA($destination);

            return response()->json([
                'destination' => $destination,
                'info' => $shortlinkInfo,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Analytics data unavailable.', 'message' => $e->getMessage()], 500);
        }
    }
}
