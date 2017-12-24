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
}
