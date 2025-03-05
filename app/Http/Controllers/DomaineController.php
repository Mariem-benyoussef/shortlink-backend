<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use Illuminate\Http\Request;

class DomaineController extends Controller
{
    /**
     * Display a listing of the domains.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Retourner tous les domaines
        return response()->json(Domaine::all(), 200);
    }

    /**
     * Store a newly created domain in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Valider la requête pour s'assurer que le domaine est unique
        $request->validate([
            'nom' => 'required|unique:domaines'
        ]);

        // Créer un nouveau domaine
        $domaine = Domaine::create([
            'nom' => $request->nom
        ]);

        // Retourner la réponse après la création
        return response()->json($domaine, 201);
    }

    /**
     * Display the specified domain.
     *
     * @param  \App\Models\Domaine  $domaine
     * @return \Illuminate\Http\Response
     */
    public function show(Domaine $domaine)
    {
        // Retourner un domaine spécifique
        return response()->json($domaine, 200);
    }

    /**
     * Update the specified domain in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Domaine  $domaine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Domaine $domaine)
    {
        // Valider la requête
        $request->validate([
            'nom' => 'required|unique:domaines,nom,' . $domaine->id
        ]);

        // Mettre à jour le domaine
        $domaine->update([
            'nom' => $request->nom
        ]);

        // Retourner la réponse après la mise à jour
        return response()->json($domaine, 200);
    }

    /**
     * Remove the specified domain from storage.
     *
     * @param  \App\Models\Domaine  $domaine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Domaine $domaine)
    {
        // Supprimer le domaine
        $domaine->delete();

        // Retourner une réponse indiquant la suppression
        return response()->json(['message' => 'Domaine supprimé avec succès'], 200);
    }


    public function setDefaultDomain(Request $request)
    {
        // Valider que le domaineId est bien fourni
        $request->validate([
            'domainId' => 'required|exists:domaines,id'
        ]);

        // Réinitialiser tous les domaines existants comme non par défaut
        Domaine::query()->update(['is_default' => false]);

        // Trouver et mettre à jour le domaine spécifié pour le marquer comme par défaut
        $domaine = Domaine::findOrFail($request->domainId);
        $domaine->is_default = true;
        $domaine->save();

        // Retourner la réponse avec le domaine mis à jour
        return response()->json($domaine, 200);
    }
}
