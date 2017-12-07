<?php
namespace Tests\Api;

use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * Clear the database before each test.
     *
     * It does not reset node ids!
     */
    public function setUp()
    {
        parent::setUp();
        $entityManager = app()->make('Neo4j\EntityManager');
        $query = $entityManager->createQuery('MATCH (n) DETACH DELETE n');
        $result = $query->execute();
    }
    /**
     * Returns a 200 for a correct post call.
     *
     * @return void
     */
    public function testValidPost()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);

        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 405 for an invalid input.
     *
     * @return void
     *
     * @dataProvider getInvalidPostInputs
     */
    public function testInvalidPostInputs($input, $responseContent)
    {
        $this->json('POST', '/category', $input)
            ->seeJson([
                'message' => $responseContent,
            ]);

        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );
    }

    /**
     * @return mixed[]
     */
    public function getInvalidPostInputs(): array
    {
        return [
            [
                ['keyyy' => 'testcat', 'falseproperty' => 'test'],
                'No category name defined.',
            ],
            [
                ['name' => 'testcat', 'falseproperty' => 'test'],
                'Property "falseproperty" not supported.',
            ],
            [
                ['name' => 'testcat', 'falseproperty1' => 'test', 'falseproperty2' => 'test'],
                'Properties "falseproperty1", "falseproperty2" not supported.',
            ],
            [
                [],
                'Empty request.',
            ]
        ];
    }

    public function testValidGetRequest()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('GET', '/category', ['name' => 'testcat'])
             ->seeJson([
                 'name' => 'testcat',
             ]);
        $this->assertArrayHasKey('id', $this->response->getData(true));
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testCategoryNotFound()
    {
        $this->json('GET', '/category', ['name' => 'nocategory'])
                ->seeJson([
                    'message' => 'Category "nocategory" not found.',
                ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testValidUpdate()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('GET', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', '/category', ['id' => $nodeId, 'name' => 'testcatChanged']);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/category', ['name' => 'testcat'])
        ->seeJson([
            'message' => 'Category "testcat" not found.',
        ]);
        $this->json('GET', '/category', ['name' => 'testcatChanged'])
        ->seeJson([
            'name' => 'testcatChanged',
        ]);
    }

    public function testUpdateWithoutId()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('GET', '/category', ['name' => 'testcat']);
        $this->json('PUT', '/category', ['name' => 'testcatChanged'])
            ->seeJson([
                'message' => 'Missing category node id.'
            ]);
        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/category', ['name' => 'testcatChanged'])
        ->seeJson([
            'message' => 'Category "testcatChanged" not found.',
        ]);
    }

    public function testUpdateWithIdNotFound()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('GET', '/category', ['name' => 'testcat']);
        $this->json('PUT', '/category', ['id' => 999, 'name' => 'testcatChanged'])
            ->seeJson([
                'message' => 'Category node with id "999" not found.'
            ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/category', ['name' => 'testcatChanged'])
        ->seeJson([
            'message' => 'Category "testcatChanged" not found.',
        ]);
    }
}
