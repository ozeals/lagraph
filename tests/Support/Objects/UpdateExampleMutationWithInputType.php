<?php

declare(strict_types=1);

namespace Rebing\GraphQL\Tests\Support\Objects;

use Illuminate\Support\Arr;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;

class UpdateExampleMutationWithInputType extends Mutation
{
    protected $attributes = [
        'name' => 'updateExample',
    ];

    public function type(): Type
    {
        return GraphQL::type('Example');
    }

    protected function rules(array $args = []): array
    {
        return [
            'test' => ['required'],
        ];
    }

    public function args(): array
    {
        return [
            'test' => [
                'name' => 'test',
                'type' => Type::string(),
            ],

            'test_with_rules' => [
                'name'  => 'test',
                'type'  => Type::string(),
                'rules' => ['required'],
            ],

            'test_with_rules_closure' => [
                'name'  => 'test',
                'type'  => Type::string(),
                'rules' => function () {
                    return ['required'];
                },
            ],

            'test_with_rules_nullable_input_object' => [
                'name'  => 'test',
                'type'  => GraphQL::type('ExampleValidationInputObject'),
                'rules' => ['nullable'],
            ],

            'test_with_rules_non_nullable_input_object' => [
                'name'  => 'test',
                'type'  => Type::nonNull(GraphQL::type('ExampleValidationInputObject')),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args): array
    {
        return [
            'test' => Arr::get($args, 'test'),
        ];
    }
}
