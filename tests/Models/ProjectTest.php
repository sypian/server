<?php
namespace Tests\Models;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;
use App\Models\Project;
use GraphAware\Neo4j\OGM\Common\Collection;

class ProjectTest extends TestCase
{
    /**
     * @return void
     */
    public function testAttributes()
    {
        $this->assertClassHasAttribute('id', Project::class);
        $this->assertClassHasAttribute('name', Project::class);
        $this->assertClassHasAttribute('categories', Project::class);
    }

    /**
     * @return void
     */
    public function testNameSetterGetter()
    {
        $project = new Project();
        $project->setName('testname');
        $this->assertEquals('testname', $project->getName());
    }

    /**
     * @return void
     */
    public function testGetCategories()
    {
        $project = new Project();
        $this->assertInstanceOf(Collection::class, $project->getCategories());
    }
}
