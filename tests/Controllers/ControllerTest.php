<?php
namespace Tests\Controllers;

use App\Http\Controllers\Controller;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    /**
     * @dataProvider getFormattedParams
     */
    public function testGetFormattedParams(array $input, string $expected)
    {
        $controller = new Controller();
        $this->assertEquals($expected, $controller->getFormattedParams($input));
    }

    public function getFormattedParams()
    {
        return [
            [
                ['test'], '"test"',
            ],
            [
                ['first', 'second', 'third'], '"first", "second", "third"',
            ],
            [
                ['with"quote', 'another"'], '"with\"quote", "another\""',
            ],
        ];
    }

    public function testGetEmptyErrors()
    {
        $controller = new Controller();
        $this->assertEquals([], $controller->getErrors());
    }

    public function testAddError()
    {
        $controller = new Controller();
        $controller->addError('generic error message');
        $this->assertEquals([['message' => 'generic error message']], $controller->getErrors());
    }

    public function testAddErrors()
    {
        $controller = new Controller();
        $controller->addError('generic error message1');
        $controller->addError('generic error message2');
        $this->assertEquals([
            ['message' => 'generic error message1'],
            ['message' => 'generic error message2']
        ], $controller->getErrors());
    }

    public function testGetEmptyPayload()
    {
        $controller = new Controller();
        $this->assertEquals([], $controller->getPayload());
    }

    public function testAddMessage()
    {
        $controller = new Controller();
        $controller->addToPayload('generic test message', 'message');
        $this->assertEquals(['message' => 'generic test message'], $controller->getPayload());
    }

    public function testAssumeMessageAsPayloadKey()
    {
        $controller = new Controller();
        $controller->addToPayload('generic test message');
        $this->assertEquals(['message' => 'generic test message'], $controller->getPayload());
    }

    public function testAddMultipleData()
    {
        $controller = new Controller();
        $controller->addToPayload('generic test message', 'message');
        $controller->addToPayload(123, 'number');
        $this->assertEquals(['message' => 'generic test message','number' => 123], $controller->getPayload());
    }

    public function testOverwritePayloadKey()
    {
        $controller = new Controller();
        $controller->addToPayload('generic test message', 'message');
        $controller->addToPayload('generic test message overwrite', 'message');
        $this->assertEquals(['message' => 'generic test message overwrite'], $controller->getPayload());
    }

    public function testGenerateJsonResponse()
    {
        $controller = new Controller();
        $controller->addToPayload('generic test message', 'message');
        $controller->addError('generic error message1');
        $controller->addError('generic error message2');
        $response = $controller->generateJsonResponse(500);
        $this->assertEquals([
            'message' => 'generic test message',
            'errors' => [
                ['message' => 'generic error message1'],
                ['message' => 'generic error message2'],
            ]
        ], $response->getData(true));
        $this->assertEquals(500, $response->getStatusCode());
    }
}
