<?php

namespace App\Http\Controllers;

use App\Models\Shortlink;
use App\Models\Click;
use App\Models\User;
use App\Models\Domaine;
use Jenssegers\Agent\Agent;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;

class ShortlinkController extends Controller
{
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $user = auth()->user();
        $shortlinks = $user->shortlinks; // Utilisation de la relation
        return response()->json($shortlinks);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

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

        $domaineParDefaut = Domaine::where('is_default', true)->first();

        if (!$domaineParDefaut) {
            return response()->json(['error' => 'Aucun domaine par défaut trouvé.'], 400);
        }

        $validated['domaine_id'] = $domaineParDefaut->id;
        $validated['user_id'] = $user->id; // Associer le shortlink à l'utilisateur connecté

        $shortlink = $user->shortlinks()->create($validated); // Utilisation de la relation

        return response()->json([
            'message' => 'Lien créé avec succès!',
            'data' => $shortlink,
        ], 201);
    }

    public function show($id)
    {
        $user = auth()->user();
        $shortlink = $user->shortlinks()->with('clicks')->find($id); // Utilisation de la relation

        if (!$shortlink) {
            return response()->json(['error' => 'Shortlink not found or not owned by you'], 404);
        }

        return response()->json([
            'shortlink' => $shortlink,
            'clicks' => $shortlink->clicks,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $shortlink = $user->shortlinks()->find($id); // Utilisation de la relation

        if (!$shortlink) {
            return response()->json(['error' => 'Shortlink not found or not owned by you'], 404);
        }

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

        $shortlink->update($validated);

        return response()->json($shortlink);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $shortlink = $user->shortlinks()->find($id); // Utilisation de la relation

        if (!$shortlink) {
            return response()->json(['error' => 'Shortlink not found or not owned by you'], 404);
        }

        $shortlink->delete();

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

    public function redirect($chemin_personnalise, Request $request)
    {
        $shortlink = Shortlink::where('chemin_personnalise', $chemin_personnalise)->firstOrFail();

        $agent = new Agent();
        $agent->setUserAgent($request->header('User-Agent'));

        // Récupérer le pays par géolocalisation si besoin (exemple simplifié)
        $country = null;

        Click::create([
            'shortlink_id' => $shortlink->id,
            'ip'           => $request->ip(),
            'user_agent'   => $request->header('User-Agent'),
            'referrer'     => $request->header('referer'),
            'country'      => $country,
            'device'       => $agent->device(),
        ]);

        return redirect($shortlink->destination);
    }


    public function getShortlinksInfo(Request $request)
    {
        $user = $request->user();
        $shortlinks = Shortlink::where('user_id', $user->id)->get();

        $analytics = [];

        foreach ($shortlinks as $shortlink) {
            $clicks = Click::where('shortlink_id', $shortlink->id)->get();

            $countryAnalytics = [];
            $deviceAnalytics = [];
            $ipAnalytics = [];
            $referrerAnalytics = [];
            $totalClicks = 0;

            foreach ($clicks as $click) {
                $country = $click->country ?? 'Inconnu';
                $countryAnalytics[$country] = ($countryAnalytics[$country] ?? 0) + 1;

                $device = $click->device ?? 'Inconnu';
                $deviceAnalytics[$device] = ($deviceAnalytics[$device] ?? 0) + 1;

                $ip = $click->ip;
                $ipAnalytics[$ip] = ($ipAnalytics[$ip] ?? 0) + 1;

                $referrer = $click->referrer ?? 'Inconnu';
                $referrerAnalytics[$referrer] = ($referrerAnalytics[$referrer] ?? 0) + 1;

                $totalClicks++;
            }

            $analytics[] = [
                'shortlink' => $shortlink->chemin_personnalise,
                'country' => $countryAnalytics,
                'device' => $deviceAnalytics,
                'total_clicks' => $totalClicks,
                'ip' => $ipAnalytics,
                'referrer' => $referrerAnalytics,
            ];
        }

        return response()->json($analytics);
    }
}
