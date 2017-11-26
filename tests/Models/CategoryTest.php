<?php
namespace Tests\Models;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testAttributes()
    {
        $this->assertClassHasAttribute('id', Category::class);
        $this->assertClassHasAttribute('name', Category::class);
    }

    public function testNameSetterGetter()
    {
        $category = new Category();
        $category->setName('testname');
        $this->assertEquals('testname', $category->getName());
    }
}
