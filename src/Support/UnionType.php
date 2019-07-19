<?php

namespace Rebing\GraphQL\Support;

use GraphQL\Type\Definition\Type as GraphqlType;
use GraphQL\Type\Definition\UnionType as BaseUnionType;

abstract class UnionType extends Type
{
    /**
     * @return array
     */
    abstract public function types();

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $types = $this->types();

        if (count($types)) {
            $attributes['types'] = $types;
        }

        if (method_exists($this, 'resolveType')) {
            $attributes['resolveType'] = [$this, 'resolveType'];
        }

        return $attributes;
    }

    /**
	 *  Convert to GraphqlType
	 *
     * @return GraphqlType
     */
    public function toType()
    {
        return new BaseUnionType($this->toArray());
    }
}
