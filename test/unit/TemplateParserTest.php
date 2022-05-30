<?php

use PHPUnit\Framework\TestCase;
use prowebcraft\template\Parser;

class TemplateParserTest extends TestCase
{

    /**
     * @param string $template
     * @param string $expected
     * @param array $variables
     * @param array $conditions
     * @return void
     * @dataProvider templateTestDataProvider
     */
    public function testTemplateParser(string $template, string $expected, array $variables, array $conditions = []): void
    {
        $parsed = Parser::parse($template, $variables, $conditions);
        self::assertEquals($expected, $parsed);
    }

    /**
     * Data provider for testTemplateParser
     * @return array[]
     */
    public function templateTestDataProvider(): array
    {
        return [
            [
                'Hello, {{firstname}}! Your order #{{Order Id}} is confirmed',
                'Hello, John Doe! Your order #12345-67-89 is confirmed',
                [
                    'firstname' => 'John Doe',
                    'Order Id' => '12345-67-89',
                ]
            ],
            [
                'Current date is {{current_date}}',
                'Current date is 2022-01-01',
                [
                    'current_date' => function($a, $b) {
                        return '2022-01-01';
                    },
                ]
            ],

        ];
    }
}
