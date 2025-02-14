<?php

namespace App\Http\Controllers;

use App\Services\GoogleAnalyticsService;

class GoogleAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(GoogleAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $data = $this->analyticsService->getReport();
        return view('analytics.index', compact('data'));
    }
}
