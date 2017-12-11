<?php
namespace Tests\Models;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;
use App\Models\Project;

class ProjectTest extends TestCase
{
    /**
     * @return void
     */
    public function testAttributes()
    {
        $this->assertClassHasAttribute('id', Project::class);
        $this->assertClassHasAttribute('name', Project::class);
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
}
