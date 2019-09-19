<?php

namespace Empress\Test\Internal;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Internal\CaseConverter;

class CaseConverterTest extends AsyncTestCase
{

    /** @dataProvider provideCasedNames */
    public function testKebabCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('camel-case', $converter->kebabCasify());
    }

    /** @dataProvider provideCasedNames */
    public function testSnakeCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('camel_case', $converter->snakeCasify());
    }

    /** @dataProvider provideCasedNames */
    public function testPascalCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('CamelCase', $converter->pascalCasify());
    }

    /** @dataProvider provideCasedNames */
    public function testCamelCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('camelCase', $converter->camelCasify());
    }

    public function provideCasedNames()
    {
        return [
            ['camelCase'],
            ['CamelCase'],
            ['camel-case'],
            ['camel_case'],
            ['Camel-Case'],
            ['Camel_case'],
            ['camel_Case'],
        ];
    }
}
