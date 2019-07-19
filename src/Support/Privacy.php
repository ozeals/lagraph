<?php

namespace Rebing\GraphQL\Support;

abstract class Privacy
{
    public function fire()
    {
        return $this->validate(func_get_args()[0]);
    }

    /**
     * @param  array  $queryArgs  Arguments given with the query/mutation
     * @return bool Return `true` to allow access to the field in question,
     *   `false otherwise
     */
    abstract public function validate(array $queryArgs);
}
