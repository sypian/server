<?php
namespace Tests\Models;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;
use App\Models\ProjectCategory;
use App\Models\Category;
use App\Models\Project;
use GraphAware\Neo4j\OGM\Common\Collection;

class ProjectCategoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testAttributes()
    {
        $this->assertClassHasAttribute('id', ProjectCategory::class);
        $this->assertClassHasAttribute('project', ProjectCategory::class);
        $this->assertClassHasAttribute('category', ProjectCategory::class);
    }

    /**
     * @return void
     */
    public function testNameSetterGetter()
    {
        $project = new Project();
        $category = new Category();
        $projectCategory = new ProjectCategory($project, $category);
        $this->assertEquals($project, $projectCategory->getProject());
        $this->assertEquals($category, $projectCategory->getCategory());
    }
}
