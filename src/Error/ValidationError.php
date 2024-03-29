<?php

namespace Rebing\GraphQL\Error;

use GraphQL\Error\Error;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Contracts\Validation\Validator;

class ValidationError extends Error
{
    /** @var Validator */
    private $validator;

    public function __construct(string $message, Validator $validator)
    {
        parent::__construct($message);

        $this->validator = $validator;
    }

    public function getValidatorMessages()
    {
        return $this->validator->errors();
    }

    public function getValidator()
    {
        return $this->validator;
    }
}
