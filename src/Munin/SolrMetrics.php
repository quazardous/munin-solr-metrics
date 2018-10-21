<?php

namespace Quazardous\Munin;

use Symfony\Component\PropertyAccess\PropertyAccess;

class SolrMetrics
{
    protected $mode;
    protected $profile;
    public function __construct($mode, $profile = null)
    {
        $this->mode = strtolower($mode);
        $this->profile = $profile ?? 'default';
    }
    
    protected $config;
    protected function loadConfig()
    {
        if (empty($this->config)) {
            $configFile = null;
            if (! ($configFile = getenv('MUNIN_SOLR_METRICS_CONFIG'))) {
                $candidateConfigFiles = ['./.munin-solr-metrics.php'];
                if ($home = getenv('HOME')) {
                    $candidateConfigFiles[] = $home . '/.munin-solr-metrics.php';
                }
                $candidateConfigFiles[] = '/etc/munin-solr-metrics.php';
                foreach ($candidateConfigFiles as $candidateConfigFile) {
                    if (is_readable($candidateConfigFile)) {
                        $configFile = $candidateConfigFile;
                        break;
                    }
                }
            }
            if ($configFile) {
                if (!is_readable($configFile)) throw new \RuntimeException(sprintf('Cannot read config file %s', $configFile));
                $this->config = include $configFile;
            }
            if (empty($this->config)) throw new \RuntimeException(sprintf('Bad config file (%s)', $configFile));
            if (empty($this->config[$this->profile])) throw new \RuntimeException(sprintf('Cannot find config entry for profile %s', $this->profile));
            $profileConfig = (array)$this->config[$this->profile];
            $profileConfig += [
                'graphs' => [],
            ];
            foreach ($profileConfig['graphs'] as $graphName => &$graphConfig) {
                if (!is_array($graphConfig)) throw new \RuntimeException(sprintf('Something is wrong in profile %s graph %s ', $this->profile, $graphName));
                $graphConfig += [
                    'fields' => [],
                    'graph_title' => $graphName,
                ];
                foreach ($graphConfig['fields'] as $fieldName => &$fieldConfig) {
                    if (is_string($fieldConfig)) {
                        $fieldConfig = ['metric' => $fieldConfig];
                    }
                    $fieldConfig += [
                        'label' => $fieldName,
                        'metric_aggregate' => null,
                    ];
                    if (!is_array($fieldConfig)) throw new \RuntimeException(sprintf('Something is wrong in profile %s graph %s field %s', $this->profile, $graphName, $fieldName));
                    $fieldConfig['attributes'] = array_udiff_assoc($fieldConfig, ['metric' => true, 'metric_aggregate' => true], function ($a, $b) { return 0; });
                    $fieldConfig = array_uintersect_assoc($fieldConfig, ['attributes' => true, 'metric' => true, 'metric_aggregate' => true], function ($a, $b) { return 0; });
                }
                $graphConfig['attributes'] = array_udiff_assoc($graphConfig, ['fields' => true], function ($a, $b) { return 0; });
                $graphConfig = array_uintersect_assoc($graphConfig, ['attributes' => true, 'fields' => true], function ($a, $b) { return 0; });
            }
            $this->config[$this->profile] = $profileConfig;
        }
    }
    
    protected function assertConfigElement($element)
    {
        if (empty($this->config[$this->profile][$element])) throw new \RuntimeException(sprintf('Cannot find guess config element %s in profile %s', $element, $this->profile));
        return $this->config[$this->profile][$element];
    }
    
    protected function getConfigElement($element)
    {
        return $this->config[$this->profile][$element] ?? null;
    }
    
    public function run()
    {
        $this->init();
        
        switch ($this->mode)
        {
            case 'config':
                $this->runConfig();
                break;
            case 'values':
                $this->runValues();
                break;
            default:
                throw new \RuntimeException(sprintf('Bad mode %s', $this->mode));
        }
    }
    
    /**
     * @return MultiGraph
     */
    protected function createMultiGraph()
    {
        $mg = new MultiGraph();
        foreach ($this->config[$this->profile]['graphs'] as $graphName => $graphConfig) {
            $fields = [];
            foreach ($graphConfig['fields'] as $fieldName => $fieldConfig) {
                $metric = (array)$fieldConfig['metric'];
                $aggregate = $fieldConfig['metric_aggregate'];
                $fields[$fieldName] = new GraphField(function() use ($metric, $aggregate) {
                    return $this->getSolrMetric($metric, $aggregate);
                }, $fieldConfig['attributes']);
            }
            $mg->addGraph($graphName, new Graph($graphConfig['attributes'], $fields));
        }
        return $mg;
    }
    
    protected function runConfig()
    {
        $mg = $this->createMultiGraph();
        $mg->dumpConfig();
    }
    
    protected function runValues()
    {
        $mg = $this->createMultiGraph();
        $mg->dumpValues();
    }
    
    protected function init()
    {
        $this->loadConfig();
    }
    
    /**
     * Get a metric from the list of given metrics
     * @param array $metrics
     * @param callable $aggregate
     * @return number
     */
    protected function getSolrMetric(array $metrics, callable $aggregate = null)
    {
        $this->pollSolrMetrics();
        // general case is to aggregate a list of metrics
        
        $values = [];
        foreach ($metrics as $metric) {
            $tokens = explode('|', $metric);
            $propertyPath = '';
            foreach ($tokens as &$token) $token = '[' . $token . ']';
            $propertyPath = implode('', $tokens);
            $values[] = $this->getPropertyAccessor()->getValue($this->solrMetrics, '[metrics]' . $propertyPath);
            unset($tokens);
            unset($token);
        }
        
        // by default metrics are added together
        if (empty($aggregate)) $aggregate = function () { return array_sum(func_get_args()); };
        
        return call_user_func_array($aggregate, $values);
    }
    
    /**
     * @return \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        return PropertyAccess::createPropertyAccessor();
    }
    
    protected $solrMetrics = null;
    protected function pollSolrMetrics()
    {
        if (is_null($this->solrMetrics)) {
            $query = $this->assertConfigElement('solr_metrics_query');
            $context = null;
            if ($auth = $this->getConfigElement('solr_auth')) {
                $auth = base64_encode($auth);
                $context = stream_context_create(['http' => ['header' => "Authorization: Basic $auth"]]);
            }
            $content = file_get_contents($query, false, $context);
            $this->solrMetrics = json_decode($content, true);
        }
    }
    
    public static function error($string, $code = 1)
    {
        static::write_ln('ERROR: ' . $string . ' (' . $code . ')');
        die($code > 0 ? $code : 99);
    }
    
    public static function write($string)
    {
        echo $string;
    }
    
    public static function write_ln($string = '')
    {
        static::write($string . "\n");
    }
    
}