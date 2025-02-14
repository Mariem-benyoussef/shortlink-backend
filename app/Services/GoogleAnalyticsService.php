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

    public function getReport()
    {
        try {
            // Remplacez par votre propertyId GA4 (ex: 'properties/123456789')
            $propertyId = 'properties/' . env('GOOGLE_ANALYTICS_PROPERTY_ID');

            // Plage de dates
            $dateRange = new DateRange([
                'start_date' => '30daysAgo', // Période : 30 derniers jours
                'end_date' => 'today',
            ]);

            // Exemple de métriques
            $metricUsers = new Metric([
                'name' => 'activeUsers', // Nombre d'utilisateurs actifs
            ]);

            $metricSessions = new Metric([
                'name' => 'sessions', // Nombre de sessions
            ]);

            // Exemple de dimension
            $dimensionDate = new Dimension([
                'name' => 'date', // Dimension de la date
            ]);

            // Créer la requête de rapport
            $request = new RunReportRequest([
                'property' => $propertyId,
                'date_ranges' => [$dateRange],
                'metrics' => [$metricUsers, $metricSessions],
                'dimensions' => [$dimensionDate],
            ]);

            // Récupérer le rapport
            $response = $this->client->runReport($request);

            // Traiter et retourner les résultats
            $result = [];
            foreach ($response->getRows() as $row) {
                $date = $row->getDimensionValues()[0]->getValue();
                $users = $row->getMetricValues()[0]->getValue();
                $sessions = $row->getMetricValues()[1]->getValue();

                $result[] = [
                    'date' => $date,
                    'users' => $users,
                    'sessions' => $sessions,
                ];
            }

            return $result;
        } catch (ApiException $e) {
            // Gérer les erreurs d'API
            throw new \Exception('Erreur Google Analytics API : ' . $e->getMessage());
        }
    }
}
