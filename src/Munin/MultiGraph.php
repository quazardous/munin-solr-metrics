<?php

namespace Quazardous\Munin;

class MultiGraph
{
    use DumperTrait;
    public function __construct(array $graphs = [])
    {
        foreach ($graphs as $name => $graph) $this->addGraph($name, $graph);
    }
    
    /**
     * @var Graph[]
     */
    protected $graphs = [];
    
    /**
     * @param string $name
     * @param Graph $graph
     * @return MultiGraph
     */
    public function addGraph(string $name, Graph $graph)
    {
        $this->graphs[$name] = $graph;
        return $this;
    }
    
    /**
     * @param string $name
     * @return NULL|Graph
     */
    public function getGraph(string $name)
    {
        return $this->graphs[$name] ?? null;
    }
    
    /**
     * @return Graph[]
     */
    public function getGraphs()
    {
        return $this->graphs;
    }
    
    public function dumpConfig()
    {
        foreach ($this->graphs as $name => $graph) {
            $this->dumpAttribute('multigraph', $name);
            $graph->dumpConfig();
            $this->dumpLine();
        }
    }
    
    public function dumpValues()
    {
        foreach ($this->graphs as $name => $graph) {
            $this->dumpAttribute('multigraph', $name);
            $graph->dumpValues();
            $this->dumpLine();
        }
    }
}