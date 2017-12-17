<?php
namespace Tests\Models;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;
use App\Models\Category;
use GraphAware\Neo4j\OGM\Common\Collection;

class CategoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testAttributes()
    {
        $this->assertClassHasAttribute('id', Category::class);
        $this->assertClassHasAttribute('name', Category::class);
        $this->assertClassHasAttribute('projects', Category::class);
    }

    public function testNameSetterGetter()
    {
        $category = new Category();
        $category->setName('testname');
        $this->assertEquals('testname', $category->getName());
    }

    /**
     * @return void
     */
    public function testGetProjects()
    {
        $category = new Category();
        $this->assertInstanceOf(Collection::class, $category->getProjects());
    }
}
