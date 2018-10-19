<?php

return [
    'default' => [
        'solr_metrics_query' => 'http://localhost:8983/solr/admin/metrics',
        //'solr_auth' => 'solr:password',
        'graphs' => [
            'http' => [
                'graph_category' => 'solr',
                'graph_title' => 'HTTP responses',
                'fields' => [
                    '1xx' => [
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.1xx-responses|count',
                        'draw' => 'AREASTACK',
                        'type' => 'DERIVE',
                    ],
                    '2xx' => [
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.2xx-responses|count',
                        'draw' => 'AREASTACK',
                        'type' => 'DERIVE',
                    ],
                    '3xx' => [
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.3xx-responses|count',
                        'draw' => 'AREASTACK',
                        'type' => 'DERIVE',
                    ],
                    '4xx' => [
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.4xx-responses|count',
                        'draw' => 'AREASTACK',
                        'type' => 'DERIVE',
                    ],
                    '5xx' => [
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.5xx-responses|count',
                        'draw' => 'AREASTACK',
                        'type' => 'DERIVE',
                    ],
                ],
            ],
        ],
    ],
];