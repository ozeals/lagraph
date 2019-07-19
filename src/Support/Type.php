<?php

namespace Rebing\GraphQL\Support;

use Illuminate\Support\Str;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type as GraphqlType;
use Rebing\GraphQL\Support\Contracts\TypeConvertible;

/**
 * @property string $name
 */
abstract class Type implements TypeConvertible
{
    protected $attributes = [];

    /**
     * Set to `true` in your type when it should reflect an InputObject.
     * @var bool
     * @deprecated Use InputType instead
     * @see InputType
     */
    protected $inputObject = false;

    /**
     * Set to `true` in your type when it should reflect an Enum.
     * @var bool
     * @deprecated Use EnumType instead
     * @see EnumType
     */
    protected $enumObject = false;

    public function attributes()
    {
        return [];
    }

    /**
     * @return array<string,array|string|FieldDefinition>
     */
    public function fields()
    {
        return [];
    }

    public function interfaces()
    {
        return [];
    }

	
    /**
	 * Get field resovle
	 *
     * @return callable|null
     */
    protected function getFieldResolver(string $name, array $field)
    {
        if (isset($field['resolve'])) {
            return $field['resolve'];
        }

        $resolveMethod = 'resolve'.Str::studly($name).'Field';

        if (method_exists($this, $resolveMethod)) {
            $resolver = [$this, $resolveMethod];

            return function () use ($resolver) {
                $args = func_get_args();

                return call_user_func_array($resolver, $args);
            };
        }

        if (isset($field['alias']) && is_string($field['alias'])) {
            $alias = $field['alias'];

            return function ($type) use ($alias) {
                return $type->{$alias};
            };
        }

        return null;
    }

    /**
	 * Get fields
	 *
     * @return array
     */
    public function getFields()
    {
        $fields = $this->fields();
        $allFields = [];
        foreach ($fields as $name => $field) {
            if (is_string($field)) {
                $field = app($field);
                $field->name = $name;
                $allFields[$name] = $field->toArray();
            } elseif ($field instanceof FieldDefinition) {
                $allFields[$field->name] = $field;
            } else {
                $resolver = $this->getFieldResolver($name, $field);
                if ($resolver) {
                    $field['resolve'] = $resolver;
                }
                $allFields[$name] = $field;
            }
        }

        return $allFields;
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->attributes();
        $interfaces = $this->interfaces();

        $attributes = array_merge($this->attributes, [
            'fields' => function () {
                return $this->getFields();
            },
        ], $attributes);

        if (count($interfaces)) {
            $attributes['interfaces'] = $interfaces;
        }

        return $attributes;
    }

    /**
     * Convert the Fluent instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAttributes();
    }

    /**
	 *  Convert to GraphqlType
	 *
     * @return GraphqlType
     */
    public function toType()
    {
        if ($this->inputObject) {
            return new InputObjectType($this->toArray());
        }
        if ($this->enumObject) {
            return new EnumType($this->toArray());
        }

        return new ObjectType($this->toArray());
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        $attributes = $this->getAttributes();

        return isset($attributes[$key]) ? $attributes[$key] : null;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        $attributes = $this->getAttributes();

        return isset($attributes[$key]);
    }

    /**
	 * Setter
	 *
     * @return viod
     */
    public function __set(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }
}
