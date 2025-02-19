<?php

namespace App\Http\Controllers;

use App\Models\Shortlink;
use Illuminate\Http\Request;

class ShortlinkController extends Controller
{
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
}
