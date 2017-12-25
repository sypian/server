<?php
namespace Tests\Integration;

use Tests\TestCase;

class ProjectTest extends TestCase
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
        $this->json('POST', '/project', ['name' => 'project1']);

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
        $this->json('POST', '/project', ['nameeee' => 'project1'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'No Project name defined.',
                    ]
                ]
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
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('POST', '/project', ['name' => 'project1'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'Project with name "project1" already exists.',
                    ]
                ]
            ]);

        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    public function testValidGetRequest()
    {
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('GET', '/project', ['name' => 'project1'])
             ->seeJson([
                 'name' => 'project1',
             ]);
        $this->assertArrayHasKey('id', $this->response->getData(true));
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testProjectNotFound()
    {
        $this->json('GET', '/project', ['name' => 'noproject'])
                ->seeJson([
                    'errors' => [
                        [
                            'message' => 'Project "noproject" not found.',
                        ]
                    ]
                ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testValidUpdate()
    {
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('GET', '/project', ['name' => 'project1']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', '/project', ['id' => $nodeId, 'name' => 'project1Changed']);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1'])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Project "project1" not found.',
                ]
            ]
        ]);
        $this->json('GET', '/project', ['name' => 'project1Changed'])
        ->seeJson([
            'name' => 'project1Changed',
        ]);
    }

    public function testUpdateWithoutId()
    {
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('GET', '/project', ['name' => 'project1']);
        $this->json('PUT', '/project', ['name' => 'project1Changed'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'Missing Project node id.'
                    ]
                ]
            ]);
        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1Changed'])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Project "project1Changed" not found.',
                ]
            ]
        ]);
    }

    public function testUpdateWithIdNotFound()
    {
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('GET', '/project', ['name' => 'project1']);
        $this->json('PUT', '/project', ['id' => 999, 'name' => 'project1Changed'])
            ->seeJson([
                'errors' => [
                    [
                        'message' => 'Project node with id "999" not found.'
                    ]
                ]
            ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1Changed'])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Project "project1Changed" not found.',
                ]
            ]
        ]);
    }

    public function testDeleteProject()
    {
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('GET', '/project', ['name' => 'project1']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('DELETE', '/project', ['id' => $nodeId])
        ->seeJson([
            'message' => 'Project node with id "'.$nodeId.'" got deleted.'
        ]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1'])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Project "project1" not found.',
                ]
            ]
        ]);
    }

    public function testDeleteNotExistingProject()
    {
        $this->json('DELETE', '/project', ['id' => 999])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Project node with id "999" not found.'
                ]
            ]
        ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );
    }

    public function testDeleteProjectWithoutId()
    {
        $this->json('DELETE', '/project', ['name' => 'project1'])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Missing Project node id.'
                ]
            ]
        ]);
        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 400 for trying to create a project with a non existing category.
     *
     * @return void
     */
    public function testCreateWithNotExistingCategory()
    {
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat']])->seeJson([
            'errors' => [
                [
                    'message' => 'Category "testcat" does not exist.'
                ]
            ]
        ]);
        $this->assertEquals(
            400,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 200 for trying to create a project with an existing category.
     *
     * @return void
     */
    public function testCreateWithExistingCategory()
    {
        $this->json('POST', '/category', ['name' => 'testcat']);
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat']]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns the names of the related categories of a project.
     *
     * @return void
     */
    public function testGetProjectWithCategories()
    {
        $this->json('POST', '/category', ['name' => 'testcat1']);
        $this->json('POST', '/category', ['name' => 'testcat2']);
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat1', 'testcat2']]);

        $this->json('GET', '/project', ['name' => 'project1'])
             ->seeJson([
                 'name' => 'project1',
                 'categories' => ['testcat1', 'testcat2']
             ]);
        $this->assertArrayHasKey('id', $this->response->getData(true));
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testDeleteProjectWithCategories()
    {
        $this->json('POST', '/category', ['name' => 'testcat1']);
        $this->json('POST', '/category', ['name' => 'testcat2']);
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat1', 'testcat2']]);
        $this->json('GET', '/project', ['name' => 'project1']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('DELETE', '/project', ['id' => $nodeId])
        ->seeJson([
            'message' => 'Project node with id "'.$nodeId.'" got deleted.'
        ]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1'])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Project "project1" not found.',
                ]
            ]
        ]);
    }

    public function testUpdateAddCategory()
    {
        $this->json('POST', '/category', ['name' => 'testcat1']);
        $this->json('POST', '/category', ['name' => 'testcat2']);
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat1']]);
        $this->json('GET', '/project', ['name' => 'project1']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', '/project', [
            'id' => $nodeId,
            'name' => 'project1',
            'categories' => ['testcat1', 'testcat2'],
        ]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1'])
        ->seeJson([
            'name' => 'project1',
            'categories' => ['testcat1', 'testcat2'],
        ]);
    }

    public function testUpdateAddNotExistingCategory()
    {
        $this->json('POST', '/category', ['name' => 'testcat1']);
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat1']]);
        $this->json('GET', '/project', ['name' => 'project1']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', '/project', [
            'id' => $nodeId,
            'name' => 'project1',
            'categories' => ['testcat1', 'testcatFail'],
        ])
        ->seeJson([
            'errors' => [
                [
                    'message' => 'Category "testcatFail" not found.',
                ]
            ]
        ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1'])
        ->seeJson([
            'name' => 'project1',
            'categories' => ['testcat1'],
        ]);
    }

    public function testUpdateRemoveCategory()
    {
        $this->json('POST', '/category', ['name' => 'testcat1']);
        $this->json('POST', '/category', ['name' => 'testcat2']);
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat1', 'testcat2']]);
        $this->json('GET', '/project', ['name' => 'project1']);
        $nodeId = $this->response->getData(true)['id'];
        $this->json('PUT', '/project', ['id' => $nodeId, 'name' => 'project1', 'categories' => ['testcat2']]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1'])
        ->seeJson([
            'name' => 'project1',
            'categories' => ['testcat2'],
        ]);
    }
}
