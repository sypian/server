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
        $this->post('/category', ['key' => 'testcat', 'properties' => []]);

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
                ['keyyy' => 'testcat', 'properties' => []],
                'No category key defined',
            ],
            [
                ['key' => 'testcat', 'proooperties' => []],
                'No category properties defined',
            ]
        ];
    }
}
