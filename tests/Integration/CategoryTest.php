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
     * Returns a 200 for a correct post call.
     *
     * @return void
     */
    public function testValidPost()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->assertArrayHasKey('id', $this->response->getData(true));
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 400 if we dont pass a name.
     *
     * @return void
     */
    public function testCreateWithoutName()
    {
        $this->json('POST', '/category', ['nameeee' => 'testcat'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'No Category name defined.',
                    ]
                ],
            ]);

        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 400 if the project to create already exists.
     *
     * @return void
     */
    public function testCreateDuplicate()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('POST', '/category', ['name' => 'testcat'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'Category with name "testcat" already exists.',
                    ]
                ],
            ]);

        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    public function testValidGetRequest()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('GET', "/category/$nodeId")
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
        $this->json('GET', '/category/999')
                ->seeJson([
                    'errors' => [
                        [
                            'message' => 'Category with id "999" not found.',
                        ],
                    ]
                ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testValidUpdate()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', "/category/$nodeId", ['name' => 'testcatChanged']);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', "/category/$nodeId")
        ->seeJson([
            'name' => 'testcatChanged',
        ]);
    }

    public function testUpdateChangedId()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', "/category/$nodeId", ['id' => $nodeId+1, 'name' => 'testcatChanged'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'Changing the Category id is not allowed.'
                    ],
                ]
            ]);
        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    public function testUpdateWithIdNotFound()
    {
        $this->json('PUT', '/category/999', ['id' => 999, 'name' => 'testcatChanged'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'Category with id "999" not found.'
                    ],
                ]
            ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testDeleteCategory()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('DELETE', "/category/$nodeId")
        ->seeJson([
            'message' => 'Category with id "'.$nodeId.'" got deleted.'
        ]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', "/category/$nodeId")
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Category with id "'.$nodeId.'" not found.',
                ],
            ]
        ]);
    }

    public function testDeleteNotExistingCategory()
    {
        $this->json('DELETE', '/category/999')
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Category with id "999" not found.'
                ],
            ]
        ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testDeleteCategoryWithConfiguredProject()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat']]);
        $this->json('DELETE', "/category/$nodeId")
        ->seeJson([
            'message' => 'Category with id "'.$nodeId.'" got deleted.'
        ]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', "/category/$nodeId")
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Category with id "'.$nodeId.'" not found.',
                ],
            ]
        ]);
    }
}
