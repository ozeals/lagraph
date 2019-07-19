<?php

declare(strict_types=1);

namespace Rebing\GraphQL\Tests\Unit;

use Rebing\GraphQL\Tests\TestCase;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Tests\Support\Objects\ExamplesQuery;

class GraphQLQueryTest extends TestCase
{
    /**
     * Test query.
     */
    public function testQueryAndReturnResult(): void
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);

        $this->assertObjectHasAttribute('data', $result);

        $this->assertEquals($result->data, [
            'examples' => $this->data,
        ]);
    }

    /**
     * Test query methods.
     */
    public function testQuery(): void
    {
        $resultArray = GraphQL::query($this->queries['examples']);
        $result = GraphQL::queryAndReturnResult($this->queries['examples']);

        $this->assertIsArray($resultArray);
        $this->assertArrayHasKey('data', $resultArray);
        $this->assertEquals($resultArray['data'], $result->data);
    }

    /**
     * Test query with variables.
     */
    public function testQueryAndReturnResultWithVariables(): void
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithVariables'], [
            'index' => 0,
        ]);

        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examples' => [
                $this->data[0],
            ],
        ]);
    }

    /**
     * Test query with complex variables.
     */
    public function testQueryAndReturnResultWithFilterVariables(): void
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesWithFilterVariables'], [
            'filter' => [
                'test' => 'Example 1',
            ],
        ]);

        $this->assertObjectHasAttribute('data', $result);
        // When XDebug is used with breaking on exceptions the real error will
        // be visible in case the recursion for getInputTypeRules runs away.
        // GraphQL\Error\Error: Maximum function nesting level of '256' reached, aborting!
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesFiltered' => [
                $this->data[0],
            ],
        ]);
    }

    /**
     * Test query with authorize.
     */
    public function testQueryAndReturnResultWithAuthorize(): void
    {
        $result = $this->graphql($this->queries['examplesWithAuthorize'], [
            'expectErrors' => true,
        ]);
        $this->assertNull($result['data']['examplesAuthorize']);
        $this->assertEquals('Unauthorized', $result['errors'][0]['message']);
    }

    /**
     * Test query with schema.
     */
    public function testQueryAndReturnResultWithSchema(): void
    {
        $result = GraphQL::queryAndReturnResult($this->queries['examplesCustom'], null, [
            'schema' => [
                'query' => [
                    'examplesCustom' => ExamplesQuery::class,
                ],
            ],
        ]);

        $this->assertObjectHasAttribute('data', $result);
        $this->assertCount(0, $result->errors);
        $this->assertEquals($result->data, [
            'examplesCustom' => $this->data,
        ]);
    }

    /**
     * Test query with error.
     *
     * If an error was encountered before execution begins, the data entry should not be present in the result.
     */
    public function testQueryWithError(): void
    {
        $result = $this->graphql($this->queries['examplesWithError'], [
            'expectErrors' => true,
        ]);

        $this->assertArrayHasKey('errors', $result);
        $this->assertCount(1, $result['errors']);
        $this->assertArrayHasKey('message', $result['errors'][0]);
        $this->assertArrayHasKey('locations', $result['errors'][0]);
    }

    /**
     * Test query with validation error.
     */
    public function testQueryWithValidationError(): void
    {
        $result = $this->graphql($this->queries['examplesWithValidation'], [
            'expectErrors' => true,
        ]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('extensions', $result['errors'][0]);
        $this->assertArrayHasKey('validation', $result['errors'][0]['extensions']);
        $this->assertTrue($result['errors'][0]['extensions']['validation']->has('index'));
    }

    /**
     * Test query with validation without error.
     */
    public function testQueryWithValidation(): void
    {
        $result = $this->graphql($this->queries['examplesWithValidation'], [
            'variables' => [
                'index' => 0,
            ],
        ]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }

    /**
     * Tests that the custom default field resolver from the static method is invoked.
     */
    public function testCustomDefaultFieldResolverStaticClass(): void
    {
        $this->app['config']->set('graphql.defaultFieldResolver', [static::class, 'exampleDefaultFieldResolverForTest']);

        $result = GraphQL::queryAndReturnResult($this->queries['examplesCustom'],
            null, [
                'schema' => [
                    'query' => [
                        'examplesCustom' => ExamplesQuery::class,
                    ],
                ],
            ]);

        $expectedDataResult = [
            'examplesCustom' => [
                [
                    'test' => 'defaultFieldResolver static method value',
                ],
                [
                    'test' => 'defaultFieldResolver static method value',
                ],
                [
                    'test' => 'defaultFieldResolver static method value',
                ],
            ],
        ];
        $this->assertSame($expectedDataResult, $result->data);
        $this->assertCount(0, $result->errors);
    }

    public static function exampleDefaultFieldResolverForTest(): string
    {
        return 'defaultFieldResolver static method value';
    }

    /**
     * Tests that the custom default field resolver from the closure is invoked.
     */
    public function testCustomDefaultFieldResolverClosure(): void
    {
        $this->app['config']->set('graphql.defaultFieldResolver', function () {
            return 'defaultFieldResolver closure value';
        });

        $result = GraphQL::queryAndReturnResult($this->queries['examplesCustom'],
            null, [
                'schema' => [
                    'query' => [
                        'examplesCustom' => ExamplesQuery::class,
                    ],
                ],
            ]);

        $expectedDataResult = [
            'examplesCustom' => [
                [
                    'test' => 'defaultFieldResolver closure value',
                ],
                [
                    'test' => 'defaultFieldResolver closure value',
                ],
                [
                    'test' => 'defaultFieldResolver closure value',
                ],
            ],
        ];
        $this->assertSame($expectedDataResult, $result->data);
        $this->assertCount(0, $result->errors);
    }
}
