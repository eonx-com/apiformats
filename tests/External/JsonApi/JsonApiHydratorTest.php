<?php
declare(strict_types=1);

namespace Tests\EoneoPay\ApiFormats\External\JsonApi;

use EoneoPay\ApiFormats\External\Libraries\JsonApi\JsonApiHydrator;
use PHPUnit\Framework\TestCase;
use WoohooLabs\Yang\JsonApi\Schema\Document;
use WoohooLabs\Yang\JsonApi\Schema\JsonApi;
use WoohooLabs\Yang\JsonApi\Schema\Links;
use WoohooLabs\Yang\JsonApi\Schema\ResourceObjects;

/**
 * @covers \EoneoPay\ApiFormats\External\Libraries\JsonApi\JsonApiHydrator
 */
class JsonApiHydratorTest extends TestCase
{
    /**
     * Test basic hydration from an empty document
     *
     * @return void
     */
    public function testJsonApiHydrationFromEmptyDocument(): void
    {
        $document = new Document(new JsonApi('1.0', []), [], new Links([]), new ResourceObjects([], [], true), []);
        $jsonApiHydrator = new JsonApiHydrator();

        self::assertSame([], $jsonApiHydrator->hydrate($document));
    }

    /**
     * Test basic hydration from an multiple resources
     *
     * @return void
     */
    public function testJsonApiHydrationFromMultipleResources(): void
    {
        $document = new Document(
            new JsonApi('1.0', []),
            [],
            new Links([]),
            new ResourceObjects(
                [
                    [
                        'type' => 'a',
                        'id' => '1'
                    ],
                    [
                        'type' => 'b',
                        'id' => '2'
                    ]
                ],
                [
                    [
                        'type' => 'b',
                        'id' => '0',
                        'attributes' => [
                            'a' => 'A',
                            'b' => 'B'
                        ]
                    ]
                ],
                false
            ),
            []
        );
        $jsonApiHydrator = new JsonApiHydrator();

        self::assertSame(
            [
                'a' => [
                    ['id' => '1', 'type' => 'a'],
                    ['id' => '2', 'type' => 'b']
                ]
            ],
            $jsonApiHydrator->hydrate($document)
        );
    }

    /**
     * Test basic hydration from an single resource
     *
     * @return void
     */
    public function testJsonApiHydrationFromSingleResource(): void
    {
        $document = new Document(
            new JsonApi('1.0', []),
            [],
            new Links([]),
            new ResourceObjects(
                [
                    'type' => 'a',
                    'id' => '1',
                    'relationships' => [
                        'x' => [
                            'data' => [
                                'type' => 'b',
                                'id' => '0'
                            ]
                        ],
                        'y' => [
                            'data' => [
                                'type' => 'b',
                                'id' => '1'
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'type' => 'b',
                        'id' => '0',
                        'attributes' => [
                            'a' => 'A',
                            'b' => 'B'
                        ]
                    ]
                ],
                true
            ),
            []
        );
        $jsonApiHydrator = new JsonApiHydrator();

        self::assertSame(
            [
                'a' => [
                    'id' => '1',
                    'type' => 'a',
                    'x' => [
                        'id' => '0',
                        'type' => 'b',
                        'a' => 'A',
                        'b' => 'B'
                    ]
                ]
            ],
            $jsonApiHydrator->hydrate($document)
        );
    }

    /**
     * Test basic hydration from an single resource with multiple relationships
     *
     * @return void
     */
    public function testJsonApiHydrationFromSingleResourceWithMultipleRelationships(): void
    {
        $document = new Document(
            new JsonApi('1.0', []),
            [],
            new Links([]),
            new ResourceObjects(
                [
                    'type' => 'a',
                    'id' => '1',
                    'relationships' => [
                        'y' => [
                            'data' => [
                                'type' => 'd',
                                'id' => '0'
                            ]
                        ],
                        'z' => [
                            'data' => [
                                [
                                    'type' => 'd',
                                    'id' => '1'
                                ],
                                [
                                    'type' => 'e',
                                    'id' => '2'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'type' => 'd',
                        'id' => '1',
                        'attributes' => [
                            'a' => 'A',
                            'b' => 'B'
                        ]
                    ]
                ],
                true
            ),
            []
        );
        $jsonApiHydrator = new JsonApiHydrator();

        self::assertSame(
            [
                'a' => [
                    'id' => '1',
                    'type' => 'a',
                    'z' => [
                        [
                            'id' => '1',
                            'type' => 'd',
                            'a' => 'A',
                            'b' => 'B'
                        ]
                    ]
                ]
            ],
            $jsonApiHydrator->hydrate($document)
        );
    }
}
