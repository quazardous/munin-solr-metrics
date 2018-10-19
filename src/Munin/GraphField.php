<?php

namespace Quazardous\Munin;

class GraphField
{
    use HasAttributesTrait;
    
    protected $value;
    public function __construct($value, array $attributes = [])
    {
        $this->value = $value;
        foreach ($attributes as $name => $value) $this->addAttribute($name, $value);
    }
    
    public function getValue()
    {
        $value = $this->value;
        if (is_callable($value)) $value = call_user_func($value);
        return $value;
    }
}