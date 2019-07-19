<?php

namespace Rebing\GraphQL\Support;

use Closure;
use GraphQL\Type\Definition\Type as GraphqlType;
use GraphQL\Type\Definition\InterfaceType as BaseInterfaceType;

abstract class InterfaceType extends Type
{
    /**
	 *  Get type Resolver
	 *
     * @return ?Closure
     */
    protected function getTypeResolver()
    {
        if (! method_exists($this, 'resolveType')) {
            return null;
        }

        $resolver = [$this, 'resolveType'];

        return function () use ($resolver) {
            $args = func_get_args();

            return call_user_func_array($resolver, $args);
        };
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        $resolver = $this->getTypeResolver();
        if (isset($resolver)) {
            $attributes['resolveType'] = $resolver;
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
        return new BaseInterfaceType($this->toArray());
    }
}
