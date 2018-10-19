<?php
if (empty($_SERVER['argv'])) throw new \RuntimeException('Cli only !'); 

foreach ([
    __DIR__ . '/../../../../vendor/autoload.php', // general case
    __DIR__ . '/../vendor/autoload.php', // for this project
    ] as $file)
{
    if (is_file($file)) {
        $autoload = $file;
        break;
    }
}
if (!$autoload) {
    throw new \RuntimeException('Something is wrong...');
}
require_once $autoload;

use Quazardous\Munin\SolrMetrics;

// trying to get a group in the munin way
$profile = null;
$matches = null;
if (preg_match('/(munin_)?solr_metrics_(\w+)$/i', basename($_SERVER['argv'][0]), $matches)) {
    $profile = $matches[2];
}

$sm = new SolrMetrics($_SERVER['argv'][1] ?? 'values', $profile);

if (!getenv('MUNIN_SOLR_METRICS_DEBUG')) {
    set_exception_handler(function ($e) use ($sm) {
        $sm::error($e->getMessage(), $e->getCode());
    });
}

$sm->run();

