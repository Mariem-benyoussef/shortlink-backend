<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\ApiCore\ApiException;

class GoogleAnalyticsService
{
    protected $client;

    public function __construct()
    {
        // Initialiser le client GA4
        $this->client = new BetaAnalyticsDataClient([
            'credentials' => storage_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);
    }

    /**
     * Récupère un rapport personnalisé en fonction des métriques et dimensions spécifiées.
     */
    public function getReport(array $metrics, array $dimensions, string $startDate = '30daysAgo', string $endDate = 'today')
    {
        try {
            $propertyId = 'properties/' . env('GOOGLE_ANALYTICS_PROPERTY_ID');

            // Plage de dates
            $dateRange = new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Convertir les métriques en objets Metric
            $metricObjects = [];
            foreach ($metrics as $metric) {
                $metricObjects[] = new Metric(['name' => $metric]);
            }

            // Convertir les dimensions en objets Dimension
            $dimensionObjects = [];
            foreach ($dimensions as $dimension) {
                $dimensionObjects[] = new Dimension(['name' => $dimension]);
            }

            // Créer la requête de rapport
            $request = new RunReportRequest([
                'property' => $propertyId,
                'date_ranges' => [$dateRange],
                'metrics' => $metricObjects,
                'dimensions' => $dimensionObjects,
            ]);

            // Récupérer le rapport
            $response = $this->client->runReport($request);

            // Traiter et retourner les résultats
            $result = [];
            foreach ($response->getRows() as $row) {
                $rowData = [];
                foreach ($row->getDimensionValues() as $dimensionValue) {
                    $rowData[] = $dimensionValue->getValue();
                }
                foreach ($row->getMetricValues() as $metricValue) {
                    $rowData[] = $metricValue->getValue();
                }
                $result[] = $rowData;
            }

            return $result;
        } catch (ApiException $e) {
            // Gérer les erreurs d'API
            throw new \Exception('Erreur Google Analytics API : ' . $e->getMessage());
        }
    }

    /**
     * Stats par clic.
     */
    public function getClickStats(string $startDate = '30daysAgo', string $endDate = 'today')
    {
        return $this->getReport(
            ['eventCount'], // Métriques
            ['eventName'],   // Dimensions
            $startDate,
            $endDate
        );
    }

    /**
     * Stats par jour.
     */
    public function getDailyStats(string $startDate = '30daysAgo', string $endDate = 'today')
    {
        return $this->getReport(
            ['activeUsers', 'sessions'], // Métriques
            ['date'],                     // Dimensions
            $startDate,
            $endDate
        );
    }

    /**
     * Stats par source.
     */
    public function getSourceStats(string $startDate = '30daysAgo', string $endDate = 'today')
    {
        return $this->getReport(
            ['sessions'], // Métriques
            ['source'],   // Dimensions
            $startDate,
            $endDate
        );
    }

    /**
     * Stats par type d'appareil.
     */
    public function getDeviceStats(string $startDate = '30daysAgo', string $endDate = 'today')
    {
        return $this->getReport(
            ['sessions'],      // Métriques
            ['deviceCategory'], // Dimensions
            $startDate,
            $endDate
        );
    }

    /**
     * Liens les plus performants.
     */
    public function getTopLinks(string $startDate = '30daysAgo', string $endDate = 'today')
    {
        return $this->getReport(
            ['eventCount'], // Métriques
            ['pagePath'],  // Dimensions
            $startDate,
            $endDate
        );
    }
}
