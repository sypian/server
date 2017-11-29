<?php
namespace Tests\Api;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * Returns a 200 for a correct call.
     *
     * @return void
     */
    public function testValidCall()
    {
        $this->post('/category', ['name' => 'testcat']);

        $this->assertEquals(
            200,
            $this->response->getStatusCode()
        );
    }

    /**
     * Returns a 405 for an invalid input.
     *
     * @return void
     *
     * @dataProvider getInvalidInputs
     */
    public function testInvalidInput($input, $responseContent)
    {
        $this->post('/category', $input);

        $this->assertEquals(
            405,
            $this->response->getStatusCode()
        );
        $this->assertEquals(
            $responseContent,
            $this->response->getContent()
        );
    }

    public function getInvalidInputs()
    {
        return [
            [
                ['keyyy' => 'testcat', 'falseproperty' => 'test'],
                'No category name defined.',
            ],
            [
                ['name' => 'testcat', 'falseproperty' => 'test'],
                'Property "falseproperty" not supported.',
            ],
            [
                ['name' => 'testcat', 'falseproperty1' => 'test', 'falseproperty2' => 'test'],
                'Properties "falseproperty1", "falseproperty2" not supported.',
            ],
            [
                [],
                'Empty request.',
            ]
        ];
    }
}
