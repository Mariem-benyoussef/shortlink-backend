<?php

namespace App\Services;

use Google\Analytics\Data\V1alpha\Filter;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
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
     *
     * @param array $metrics
     * @param array $dimensions
     * @param string $startDate
     * @param string $endDate
     * @param string $filter
     * @return array
     * @throws \Exception
     */
    // public function getReport(array $metrics, array $dimensions, string $startDate = '30daysAgo', string $endDate = 'today', string $filter = ''): array
    // {
    //     try {
    //         $propertyId = 'properties/' . env('GOOGLE_ANALYTICS_PROPERTY_ID');

    //         // Plage de dates
    //         $dateRange = new DateRange([
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //         ]);

    //         // Convertir les métriques en objets Metric
    //         $metricObjects = array_map(function ($metric) {
    //             return new Metric(['name' => $metric]);
    //         }, $metrics);

    //         // Convertir les dimensions en objets Dimension
    //         $dimensionObjects = array_map(function ($dimension) {
    //             return new Dimension(['name' => $dimension]);
    //         }, $dimensions);

    //         // Créer la requête de rapport
    //         $request = new RunReportRequest([
    //             'property' => $propertyId,
    //             'date_ranges' => [$dateRange],
    //             'metrics' => $metricObjects,
    //             'dimensions' => $dimensionObjects,
    //         ]);

    //         // Ajouter un filtre si fourni
    //         if ($filter) {
    //             $stringFilter = new StringFilter([
    //                 'value' => $filter,
    //             ]);

    //             $filterExpression = new FilterExpression([
    //                 'filter' => $stringFilter,
    //             ]);

    //             $request->setDimensionFilter($filterExpression);
    //         }

    //         // Récupérer le rapport
    //         $response = $this->client->runReport($request);

    //         // Traiter et retourner les résultats
    //         $result = [];
    //         foreach ($response->getRows() as $row) {
    //             $rowData = [];
    //             foreach ($row->getDimensionValues() as $dimensionValue) {
    //                 $rowData[] = $dimensionValue->getValue();
    //             }
    //             foreach ($row->getMetricValues() as $metricValue) {
    //                 $rowData[] = $metricValue->getValue();
    //             }
    //             $result[] = $rowData;
    //         }

    //         return $result;
    //     } catch (ApiException $e) {
    //         // Gérer les erreurs d'API
    //         throw new \Exception('Erreur Google Analytics API : ' . $e->getMessage());
    //     }
    // }

    public function getReport(
        array $metrics,
        array $dimensions,
        string $startDate = '30daysAgo',
        string $endDate = 'today',
        string $filterDimension = '',
        string $filterValue = ''
    ): array {
        try {
            $propertyId = 'properties/' . env('GOOGLE_ANALYTICS_PROPERTY_ID');

            // Plage de dates
            $dateRange = new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Convertir les métriques en objets Metric
            $metricObjects = array_map(function ($metric) {
                return new Metric(['name' => $metric]);
            }, $metrics);

            // Convertir les dimensions en objets Dimension
            $dimensionObjects = array_map(function ($dimension) {
                return new Dimension(['name' => $dimension]);
            }, $dimensions);

            // Créer la requête de rapport
            $request = new RunReportRequest([
                'property' => $propertyId,
                'date_ranges' => [$dateRange],
                'metrics' => $metricObjects,
                'dimensions' => $dimensionObjects,
            ]);

            // Ajouter un filtre de dimension si fourni
            if ($filterDimension && $filterValue) {
                $stringFilter = new StringFilter([
                    'value' => $filterValue,
                ]);

                $dimensionFilter = new FilterExpression([
                    'filter' => new Filter([
                        'field_name' => $filterDimension, // La dimension à filtrer (ex: 'pagePath')
                        'string_filter' => $stringFilter,
                    ]),
                ]);

                $request->setDimensionFilter($dimensionFilter);
            }

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
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception
     */
    public function getClickStats(string $startDate = '30daysAgo', string $endDate = 'today'): array
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
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception
     */
    public function getDailyStats(string $startDate = '30daysAgo', string $endDate = 'today'): array
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
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception
     */
    public function getSourceStats(string $startDate = '30daysAgo', string $endDate = 'today'): array
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
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception
     */
    public function getDeviceStats(string $startDate = '30daysAgo', string $endDate = 'today'): array
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
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     * @throws \Exception
     */
    public function getTopLinks(string $startDate = '30daysAgo', string $endDate = 'today'): array
    {
        return $this->getReport(
            ['eventCount'], // Métriques
            ['pagePath'],  // Dimensions
            $startDate,
            $endDate
        );
    }


    /**
     * Récupère les statistiques pour un shortlink spécifique.
     */
    public function getShortlinkStats(string $destination, string $startDate = '30daysAgo', string $endDate = 'today'): array
    {
        return $this->getReport(
            ['eventCount'], // Métriques
            ['pagePath'],  // Dimensions
            $startDate,
            $endDate,
            'pagePath',    // Dimension à filtrer
            $destination   // Valeur du filtre (ex: '/mon-shortlink')
        );
    }
}
