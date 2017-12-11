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
     * Returns a 405 for an invalid input.
     *
     * @return void
     */
    public function testInvalidPostInput()
    {
        $this->json('POST', '/project', ['nameeee' => 'project1'])
            ->seeJson([
                'message' => 'No Project name defined.',
            ]);

        $this->assertEquals(
            405,
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
                    'message' => 'Project "noproject" not found.',
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
            'message' => 'Project "project1" not found.',
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
                'message' => 'Missing Project node id.'
            ]);
        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1Changed'])
        ->seeJson([
            'message' => 'Project "project1Changed" not found.',
        ]);
    }

    public function testUpdateWithIdNotFound()
    {
        $this->json('POST', '/project', ['name' => 'project1']);
        $this->json('GET', '/project', ['name' => 'project1']);
        $this->json('PUT', '/project', ['id' => 999, 'name' => 'project1Changed'])
            ->seeJson([
                'message' => 'Project node with id "999" not found.'
            ]);
        $this->assertEquals(
            404,
            $this->response->getStatusCode()
        );

        $this->json('GET', '/project', ['name' => 'project1Changed'])
        ->seeJson([
            'message' => 'Project "project1Changed" not found.',
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
            'message' => 'Project "project1" not found.',
        ]);
    }

    public function testDeleteNotExistingProject()
    {
        $this->json('DELETE', '/project', ['id' => 999])
        ->seeJson([
            'message' => 'Project node with id "999" not found.'
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
            'message' => 'Missing Project node id.'
        ]);
        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );
    }
}
