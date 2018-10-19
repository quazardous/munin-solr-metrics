<?php

namespace Quazardous\Munin;

class Graph
{
    use HasAttributesTrait;
    use DumperTrait;
    
    public function __construct(array $attributes = [], array $fields = [])
    {
        foreach ($attributes as $name => $value) $this->addAttribute($name, $value);
        foreach ($fields as $name => $field) $this->addField($name, $field);
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @var GraphField[]
     */
    protected $fields = [];
    
    /**
     * @param string $name
     * @param GraphField $field
     * @return Graph
     */
    public function addField(string $name, GraphField $field)
    {
        $this->fields[$name] = $field;
        return $this;
    }
    
    /**
     * @param string $name
     * @return NULL|GraphField
     */
    public function getField(string $name)
    {
        return $this->fields[$name] ?? null;
    }
    
    /**
     * @return GraphField[]
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    public function dumpConfig()
    {
        foreach ($this->attributes as $name => $attribute) {
            $this->dumpAttribute($name, $attribute);
        }
        foreach ($this->fields as $fieldName => $field) {
            foreach ($field->getAttributes() as $attributeName => $value) {
                $this->dumpFieldAttribute($fieldName, $attributeName, $value);
            }
        }
    }
    
    public function dumpValues()
    {
        foreach ($this->fields as $fieldName => $field) {
            $this->dumpFieldAttribute($fieldName, 'value', $field->getValue());
        }
    }
    
}