<?php
namespace Tests\Integration;

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
        $query->execute();
    }

    /**
     * Returns a 201 for a correct post call.
     *
     * @return void
     */
    public function testValidPost()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);

        $this->assertEquals(
            201,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 405 if we dont pass a name.
     *
     * @return void
     */
    public function testCreateWithoutName()
    {
        $this->json('POST', '/category', ['nameeee' => 'testcat'])
            ->seeJson([
                'message' => 'No Category name defined.',
            ]);

        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 409 if the project to create already exists.
     *
     * @return void
     */
    public function testCreateDuplicate()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('POST', '/category', ['name' => 'testcat'])
            ->seeJson([
                'message' => 'Category with name "testcat" already exists.',
            ]);

        $this->assertEquals(
            409,
            $this->response->getStatusCode()
        );
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
                'message' => 'Missing Category node id.'
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

    public function testDeleteCategory()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('GET', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('DELETE', '/category', ['id' => $nodeId])
        ->seeJson([
            'message' => 'Category node with id "'.$nodeId.'" got deleted.'
        ]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/category', ['name' => 'testcat'])
        ->seeJson([
            'message' => 'Category "testcat" not found.',
        ]);
    }

    public function testDeleteNotExistingCategory()
    {
        $this->json('DELETE', '/category', ['id' => 999])
        ->seeJson([
            'message' => 'Category node with id "999" not found.'
        ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testDeleteCategoryWithoutId()
    {
        $this->json('DELETE', '/category', ['name' => 'testcat'])
        ->seeJson([
            'message' => 'Missing Category node id.'
        ]);
        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );
    }
}
