<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleAnalyticsService;

class GoogleAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    // public function index()
    // {
    //     try {
    //         // Exemple : Récupérer les stats par clic
    //         $clickStats = $this->analyticsService->getClickStats();

    //         // Exemple : Récupérer les stats par jour
    //         $dailyStats = $this->analyticsService->getDailyStats();

    //         // Exemple : Récupérer les stats par source
    //         $sourceStats = $this->analyticsService->getSourceStats();

    //         // Exemple : Récupérer les stats par type d'appareil
    //         $deviceStats = $this->analyticsService->getDeviceStats();

    //         // Exemple : Récupérer les liens les plus performants
    //         $topLinks = $this->analyticsService->getTopLinks();

    //         return view('analytics.index', compact(
    //             'clickStats',
    //             'dailyStats',
    //             'sourceStats',
    //             'deviceStats',
    //             'topLinks'
    //         ));
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', $e->getMessage());
    //     }
    // }

    public function index(Request $request)
    {
        try {
            $destination = $request->input('destination', '/mon-shortlink');

            return response()->json([
                'clickStats' => $this->analyticsService->getClickStats(),
                'dailyStats' => $this->analyticsService->getDailyStats(),
                'sourceStats' => $this->analyticsService->getSourceStats(),
                'deviceStats' => $this->analyticsService->getDeviceStats(),
                'topLinks' => $this->analyticsService->getTopLinks(),
                'shortlinkStats' => $this->analyticsService->getShortlinkStats($destination),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
