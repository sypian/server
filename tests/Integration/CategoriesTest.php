<?php
namespace Tests\Integration;

use Tests\TestCase;

class CategoriesTest extends TestCase
{
    protected $ids = [];
    protected $entities = [];

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
        $this->json('POST', '/category', ['name' => 'testcat1']);
        $this->ids['testcat1'] = $this->response->getData(true)['id'];
        $this->entities['testcat1'] = [
            'id' => $this->ids['testcat1'],
            'name' => 'testcat1',
        ];
        $this->json('POST', '/category', ['name' => 'testcat2']);
        $this->ids['testcat2'] = $this->response->getData(true)['id'];
        $this->entities['testcat2'] = [
            'id' => $this->ids['testcat2'],
            'name' => 'testcat2',
        ];
        $this->json('POST', '/category', ['name' => 'testcat3']);
        $this->ids['testcat3'] = $this->response->getData(true)['id'];
        $this->entities['testcat3'] = [
            'id' => $this->ids['testcat3'],
            'name' => 'testcat3',
        ];
        $this->json('POST', '/category', ['name' => 'testcat4']);
        $this->ids['testcat4'] = $this->response->getData(true)['id'];
        $this->entities['testcat4'] = [
            'id' => $this->ids['testcat4'],
            'name' => 'testcat4',
        ];
        $this->json('POST', '/project', ['name' => 'project1', 'categories' => ['testcat1', 'testcat2']]);
        $this->ids['project1'] = $this->response->getData(true)['id'];
        $this->entities['project1'] = [
            'id' => $this->ids['project1'],
            'name' => 'project1',
            'categories' => [
                [
                    'id' => $this->ids['testcat1'],
                    'name' => 'testcat1',
                ],
                [
                    'id' => $this->ids['testcat2'],
                    'name' => 'testcat2',
                ],
            ],
        ];
        $this->json('POST', '/project', ['name' => 'project2', 'categories' => ['testcat1']]);
        $this->ids['project2'] = $this->response->getData(true)['id'];
        $this->entities['project2'] = [
            'id' => $this->ids['project2'],
            'name' => 'project2',
            'categories' => [
                [
                    'id' => $this->ids['testcat1'],
                    'name' => 'testcat1',
                ],
            ],
        ];
        $this->json('POST', '/project', ['name' => 'project3', 'categories' => ['testcat3']]);
        $this->ids['project3'] = $this->response->getData(true)['id'];
        $this->entities['project3'] = [
            'id' => $this->ids['project3'],
            'name' => 'project3',
            'categories' => [
                [
                    'id' => $this->ids['testcat3'],
                    'name' => 'testcat3',
                ],
            ],
        ];
        $this->json('POST', '/project', ['name' => 'project4']);
        $this->ids['project4'] = $this->response->getData(true)['id'];
        $this->entities['project4'] = [
            'id' => $this->ids['project4'],
            'name' => 'project4',
            'categories' => [],
        ];
        $this->entities['testcat1']['projects'] = [
            [
                'id' => $this->ids['project1'],
                'name' => 'project1',
            ],
            [
                'id' => $this->ids['project2'],
                'name' => 'project2',
            ],
        ];
        $this->entities['testcat2']['projects'] = [
            [
                'id' => $this->ids['project1'],
                'name' => 'project1',
            ],
        ];
        $this->entities['testcat3']['projects'] = [
            [
                'id' => $this->ids['project3'],
                'name' => 'project3',
            ],
        ];
        $this->entities['testcat4']['projects'] = [];
    }

    public function testGetAllCategories()
    {
        $this->json('GET', '/categories')
             ->seeJson($this->entities['testcat1'])
             ->seeJson($this->entities['testcat2'])
             ->seeJson($this->entities['testcat3'])
             ->seeJson($this->entities['testcat4']);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testGetCategoriesWithName()
    {
        $this->json('GET', '/categories?name=testcat4')
             ->seeJsonEquals([$this->entities['testcat4']]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testGetCategoriesByProject()
    {
        $this->json('GET', '/categories?project=project3')
             ->seeJsonEquals([$this->entities['testcat3']]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testGetCategoriesByNameAndProject()
    {
        $this->json('GET', '/categories?project=project2&name=testcat1')
             ->seeJsonEquals([$this->entities['testcat1']]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    public function testGetEmptyList()
    {
        $this->json('GET', '/categories?name=somecategory')
             ->seeJsonEquals([]);
        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }
}
