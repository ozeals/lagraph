<?php

namespace Rebing\GraphQL\Support;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type as GraphqlType;

abstract class InputType extends Type
{
    /**
	 *  Convert to GraphqlType
	 *
     * @return GraphqlType
     */
    public function toType()
    {
        return new InputObjectType($this->toArray());
    }
}
