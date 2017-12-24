<?php
namespace Tests\Traits;

use App\Http\Controllers\JsonResponseTrait;
use PHPUnit\Framework\TestCase;
use Tests\Traits\Stubs\JsonResponseTraitStub;

class JsonResponseTraitTest extends TestCase
{
    public function testGetEmptyErrors()
    {
        $stub = new JsonResponseTraitStub();
        $this->assertEquals([], $stub->getErrors());
    }

    public function testAddError()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addError('generic error message');
        $this->assertEquals([['message' => 'generic error message']], $stub->getErrors());
    }

    public function testAddErrors()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addError('generic error message1');
        $stub->addError('generic error message2');
        $this->assertEquals([
            ['message' => 'generic error message1'],
            ['message' => 'generic error message2']
        ], $stub->getErrors());
    }

    public function testGetEmptyPayload()
    {
        $stub = new JsonResponseTraitStub();
        $this->assertEquals([], $stub->getPayload());
    }

    public function testAddMessage()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addToPayload('generic test message', 'message');
        $this->assertEquals(['message' => 'generic test message'], $stub->getPayload());
    }

    public function testAssumeMessageAsPayloadKey()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addToPayload('generic test message');
        $this->assertEquals(['message' => 'generic test message'], $stub->getPayload());
    }

    public function testAddMultipleData()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addToPayload('generic test message', 'message');
        $stub->addToPayload(123, 'number');
        $this->assertEquals(['message' => 'generic test message','number' => 123], $stub->getPayload());
    }

    public function testOverwritePayloadKey()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addToPayload('generic test message', 'message');
        $stub->addToPayload('generic test message overwrite', 'message');
        $this->assertEquals(['message' => 'generic test message overwrite'], $stub->getPayload());
    }

    public function testGenerateJsonResponse()
    {
        $stub = new JsonResponseTraitStub();
        $stub->addToPayload('generic test message', 'message');
        $stub->addError('generic error message1');
        $stub->addError('generic error message2');
        $response = $stub->generateJsonResponse(500);
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
