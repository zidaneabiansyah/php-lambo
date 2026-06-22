<?php

use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public function testSlugify()
    {
        $this->assertEquals('hello-world', StringUtils::slugify('Hello World'));
        $this->assertEquals('belajar-php', StringUtils::slugify('Belajar PHP!'));
        $this->assertEquals('a-b-c', StringUtils::slugify('a---b___c'));
        $this->assertEquals('', StringUtils::slugify(''));
    }

    public function testTruncate()
    {
        $text = 'Hello World PHP';
        $this->assertEquals('Hello...', StringUtils::truncate($text, 5));
        $this->assertEquals('Hello World', StringUtils::truncate($text, 11, ''));
        $this->assertEquals($text, StringUtils::truncate($text, 100));
    }

    public function testToCamelCase()
    {
        $this->assertEquals('helloWorld', StringUtils::toCamelCase('hello world'));
        $this->assertEquals('belajarPhp', StringUtils::toCamelCase('Belajar PHP'));
        $this->assertEquals('fooBar', StringUtils::toCamelCase('foo-bar'));
        $this->assertEquals('hello', StringUtils::toCamelCase('hello'));
    }

    /** @dataProvider palindromeProvider */
    public function testIsPalindrome(string $text, bool $expected)
    {
        $this->assertEquals($expected, StringUtils::isPalindrome($text));
    }

    public static function palindromeProvider(): array
    {
        return [
            'simple' => ['radar', true],
            'mixed case' => ['Radar', true],
            'with spaces' => ['race car', true],
            'with punctuation' => ['A man, a plan, a canal, Panama', true],
            'not palindrome' => ['hello', false],
            'empty string' => ['', true],
            'single char' => ['a', true],
        ];
    }
}
