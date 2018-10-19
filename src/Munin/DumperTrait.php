<?php

namespace Quazardous\Munin;

trait DumperTrait
{
    protected function dumpAttribute($attribute, $value)
    {
        echo sprintf("%s %s",
            $this->sanitizeElementBeforeDump($attribute),
            (string)$value
        );
        $this->dumpLine();
    }
    
    protected function dumpFieldAttribute($field, $attribute, $value)
    {
        echo sprintf("%s.%s %s",
            $this->sanitizeElementBeforeDump($field),
            $this->sanitizeElementBeforeDump($attribute),
            (string)$value
        );
        $this->dumpLine();
    }
    
    protected function dumpLine()
    {
        echo "\n";
    }
    
    protected function sanitizeElementBeforeDump($element)
    {
        // http://guide.munin-monitoring.org/en/latest/reference/plugin.html#notes-on-fieldnames
        $element = preg_replace('/[^a-z0-9_]+/i', '', $element);
        if (preg_match('/^[0-9]+/i', $element)) $element = '_' . $element;
        return $element;
    }
}
