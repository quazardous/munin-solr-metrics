# Munin SolR Metrics Plugin

Munin plugin using (new) SolR Metrics API.

https://lucene.apache.org/solr/guide/7_5/metrics-reporting.html

## Installation

`git clone` (prefered):

```bash
cd /opt
git clone https://github.com/quazardous/munin-solr-metrics.git
cd munin-solr-metrics/
composer install
```

Or `composer` local:

```bash
composer require quazardous/munin-solr-metrics
```

Or `composer global`:

```bash
composer global require quazardous/munin-solr-metrics
```

NB: Using `composer` as `root` is discouraged !

## Usage

Soft link into munin plugin:

```bash
(cd /etc/munin/plugins/; ln -s /opt/munin-solr-metrics/bin/munin_solr_metrics solr_metrics)
```
This will use the default profile.

You can use a specific profile:

```bash
(cd /etc/munin/plugins/; ln -s /opt/munin-solr-metrics/bin/munin_solr_metrics solr_metrics_my_profile)
```
This will use the profile `my_profile` in the config file (see below).

## Config file

### Location

The plugin will search for a config file in this order:
  - The file specified with the environment variable `MUNIN_SOLR_METRICS_CONFIG`
  - `./.munin-solr-metrics.php`
  - `$HOME/.munin-solr-metrics.php`
  - `/etc/munin-solr-metrics.php`
  
NB: You can inject environment variables by adding a plugin conf file `/etc/munin/plugin-conf.d/solr_metrics`:

```
[solr_metrics*]
#user solr
env.MUNIN_SOLR_METRICS_CONFIG /path/to/munin-solr-metrics.php
timeout 30

```
  
### Structure
  
The config file is a `PHP` file returning a config array.
 
 ```php
 <?php

return [
    // First level elements are profiles.
    // Each profile will produce a multigraph plugin.
    'default' => [
    	 // You have to define the SolR Metrics API query
        'solr_metrics_query' => 'http://localhost:8983/solr/admin/metrics',
        // You can use any query parameters. Be aware that some parameters can change the response structure and affect the metric definitions below.
        // 'solr_metrics_query' => 'http://localhost:8983/solr/admin/metrics?group=core',
        //'solr_auth' => 'user:password', // the plugin can handle basic auth
        'graphs' => [
            // each graph element will produce a munin graph entry.
            'graph1' => [
                'fields' => [
                    '2xx' => [
                        // You can target any existing metric
                        // Use `|` (pipe) to create a metric path. 
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.2xx-responses|count',
                        // Any othe attribute will be added as field attribute to the munin graph config.
                        'draw' => 'LINE', // will produce `_2xx.draw LINE`
                    ],
                    '2xx.1minRate' => [
                        'metric' => 'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.2xx-responses|1minRate',
                    ],
                    'error' => [
                        'metric' => [
                            // if metric is an array, metrics will be aggregated
                            'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.4xx-responses|count',
                            'solr.jetty|org.eclipse.jetty.server.handler.DefaultHandler.5xx-responses|count',
                        ],
                        // by default metrics are added together but you can specify your own function, metrics are passed by arg in order
                        // 'metric_aggregate' => function ($metric1, $metric2) { return $metric1 + $metric2; }, 
                    ],
                ],
                // Any other attributes will be added to the munin graph config.
                'graph_category' => 'solr',
                'graph_info' => 'Something meaningful',
            ],
        ]
    ],
];
```

Look into `contrib\examples` for more examples.