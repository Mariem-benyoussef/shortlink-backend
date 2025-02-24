<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Filter\StringFilter;
use Google\Analytics\Data\V1beta\Filter;
use Google\ApiCore\ApiException;

class GoogleAnalyticsService
{
    protected $client;
    protected $propertyId;
    public function __construct()
    {
        // Initialiser le client GA4
        $this->client = new BetaAnalyticsDataClient([
            'credentials' => storage_path(env('GOOGLE_APPLICATION_CREDENTIALS')),
        ]);

        $this->propertyId = 'properties/' . env('GOOGLE_ANALYTICS_PROPERTY_ID');
    }

    /**
     * Récupère un rapport personnalisé en fonction des métriques et dimensions spécifiées.
     */
    // public function getReport(
    //     array $metrics,
    //     array $dimensions,
    //     string $startDate = '30daysAgo',
    //     string $endDate = 'today',
    //     string $filterDimension = '',
    //     string $filterValue = ''
    // ): array {
    //     try {
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
    //             'property' => $this->propertyId,
    //             'date_ranges' => [$dateRange],
    //             'metrics' => $metricObjects,
    //             'dimensions' => $dimensionObjects,
    //         ]);

    //         // Ajouter un filtre de dimension si fourni
    //         if ($filterDimension && $filterValue) {
    //             $stringFilter = new StringFilter([
    //                 'value' => $filterValue,
    //             ]);

    //             $dimensionFilter = new FilterExpression([
    //                 'filter' => new Filter([
    //                     'field_name' => $filterDimension, // 'pageLocation'
    //                     'string_filter' => $stringFilter,
    //                 ]),
    //             ]);

    //             $request->setDimensionFilter($dimensionFilter);
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
                'property' => $this->propertyId,
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
                        'field_name' => $filterDimension, // 'pageLocation'
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
                $rowData = [
                    'dimensions' => [],
                    'metrics' => [],
                ];

                // Récupérer les valeurs des dimensions
                foreach ($row->getDimensionValues() as $dimensionValue) {
                    $rowData['dimensions'][] = $dimensionValue->getValue();
                }

                // Récupérer les valeurs des métriques
                foreach ($row->getMetricValues() as $metricValue) {
                    $rowData['metrics'][] = $metricValue->getValue();
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
     * Récupère les informations du shortlink (comme la destination) à partir de Google Analytics.
     */
    // public function getShortlinkInfoFromGA(string $destination, string $startDate = '30daysAgo', string $endDate = 'today'): array
    // {
    //     // Définir les métriques et dimensions à récupérer
    //     $metrics = ['eventCount']; // Nombre de clics
    //     $dimensions = ['pagePath', 'pageLocation']; // Chemin de la page et URL complète

    //     // Appeler getReport avec la destination comme filtre
    //     $results = $this->getReport(
    //         $metrics,
    //         $dimensions,
    //         $startDate,
    //         $endDate,
    //         'pageLocation', // Filtrer par l'URL complète (destination)
    //         $destination // Utiliser la destination comme valeur de filtre
    //     );

    //     // Log des résultats pour déboguer
    //     // \Log::info('Résultats de Google Analytics : ', $results);

    //     // Traiter les résultats
    //     $shortlinkInfo = [];
    //     foreach ($results as $row) {
    //         $shortlinkInfo[] = [
    //             'pagePath' => $row[0], // Chemin de la page (shortlink)
    //             'pageLocation' => $row[1], // URL complète (destination)
    //             'eventCount' => $row[2], // Nombre d'événements (clics)
    //         ];
    //     }

    //     return $shortlinkInfo;
    // }


    // public function getShortlinkInfoFromGA(string $destination, string $startDate = '30daysAgo', string $endDate = 'today'): array
    // {
    //     // Définir les métriques et dimensions à récupérer
    //     $metrics = ['screenPageViews']; // Métrique compatible
    //     $dimensions = ['date']; // Dimension simple

    //     // Appeler getReport sans filtre
    //     $results = $this->getReport(
    //         $metrics,
    //         $dimensions,
    //         $startDate,
    //         $endDate,
    //         'pageLocation',
    //         $destination

    //     );

    //     // Traiter les résultats
    //     $shortlinkInfo = [];
    //     foreach ($results as $row) {
    //         $shortlinkInfo[] = [
    //             'date' => $row[0],           // Date
    //             'screenPageViews' => $row[1], // Nombre de vues de page
    //         ];
    //     }

    //     return $shortlinkInfo;
    // }



    public function getShortlinkInfoFromGA(string $destination, string $startDate = '30daysAgo', string $endDate = 'today'): array
    {
        // Définir les métriques et dimensions à récupérer
        $metrics = ['screenPageViews']; // Métrique compatible
        $dimensions = [
            'city',            // Ville
            'country',         // Pays
            'date',            // Date
            'deviceCategory',   // Type d'appareil
            'sessionSource'    // Source de la session
        ];

        // Appeler getReport avec la destination comme filtre
        $results = $this->getReport(
            $metrics,
            $dimensions,
            $startDate,
            $endDate,
            'pageLocation', // Filtrer par l'URL complète (destination)
            $destination    // Utiliser la destination comme valeur de filtre
        );

        // Traiter les résultats
        $shortlinkInfo = [];
        foreach ($results as $row) {
            $shortlinkInfo[] = [
                'city' => $row['dimensions'][0],           // Ville
                'country' => $row['dimensions'][1],       // Pays
                'date' => $row['dimensions'][2],          // Date
                'deviceCategory' => $row['dimensions'][3], // Type d'appareil
                'sessionSource' => $row['dimensions'][4], // Source de la session
                'screenPageViews' => $row['metrics'][0],     // Nombre de vues de page
            ];
        }

        return $shortlinkInfo;
    }











    public function getShortlinkStats(string $destination, string $startDate = '30daysAgo', string $endDate = 'today'): array
    {
        try {
            $propertyId = 'properties/' . env('GOOGLE_ANALYTICS_PROPERTY_ID');

            // Define the date range
            $dateRange = new DateRange([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Define metrics (e.g., eventCount for clicks)
            $metrics = [new Metric(['name' => 'eventCount'])];

            // Define dimensions (e.g., pagePath for the shortlink)
            $dimensions = [new Dimension(['name' => 'pagePath'])];

            // Create a filter for the destination (e.g., pagePath or pageLocation)
            $stringFilter = new StringFilter([
                'match_type' => StringFilter\MatchType::EXACT, // Match the exact destination
                'value' => $destination,
            ]);

            $dimensionFilter = new FilterExpression([
                'filter' => new Filter([
                    'field_name' => 'pagePath', // Filter by pagePath
                    'string_filter' => $stringFilter,
                ]),
            ]);

            // Create the report request
            $request = new RunReportRequest([
                'property' => $propertyId,
                'date_ranges' => [$dateRange],
                'metrics' => $metrics,
                'dimensions' => $dimensions,
                'dimension_filter' => $dimensionFilter,
            ]);

            // Fetch the report
            $response = $this->client->runReport($request);

            // Process the response
            $result = [];
            foreach ($response->getRows() as $row) {
                $result[] = [
                    'pagePath' => $row->getDimensionValues()[0]->getValue(), // Page path (shortlink)
                    'eventCount' => $row->getMetricValues()[0]->getValue(),  // Number of events (clicks)
                ];
            }

            return $result;
        } catch (ApiException $e) {
            // Handle API errors
            throw new \Exception('Google Analytics API Error: ' . $e->getMessage());
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
