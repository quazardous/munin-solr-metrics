<?php

namespace Quazardous\Munin;

trait HasAttributesTrait
{
    protected $attributes = [];
    
    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function addAttribute(string $name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
    
    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}