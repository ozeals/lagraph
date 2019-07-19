<?php

namespace Rebing\GraphQL\Support;

use GraphQL\Type\Definition\Type as GraphqlType;
use GraphQL\Type\Definition\EnumType as GraphqlEnumType;

abstract class EnumType extends Type
{
    /**
	 *  Convert to GraphqlType
	 *
     * @return GraphqlType
     */
    public function toType()
    {
        return new GraphqlEnumType($this->toArray());
    }
}
