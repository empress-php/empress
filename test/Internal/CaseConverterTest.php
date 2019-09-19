<?php

namespace Empress\Test\Internal;

use Amp\PHPUnit\AsyncTestCase;
use Empress\Internal\CaseConverter;

class CaseConverterTest extends AsyncTestCase
{

    /** @dataProvider casedDataProvider */
    public function testKebabCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('camel-case', $converter->kebabCasify());
    }

    /** @dataProvider casedDataProvider */
    public function testSnakeCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('camel_case', $converter->snakeCasify());
    }

    /** @dataProvider casedDataProvider */
    public function testPascalCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('CamelCase', $converter->pascalCasify());
    }

    /** @dataProvider casedDataProvider */
    public function testCamelCase(string $actual)
    {
        $converter = new CaseConverter($actual);

        $this->assertEquals('camelCase', $converter->camelCasify());
    }

    public function casedDataProvider()
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
