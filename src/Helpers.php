<?php

namespace Rebing\GraphQL;

class Helpers
{
    public static function isLumen()
    {
        return class_exists('Laravel\Lumen\Application');
    }
}
