<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testHtmlEscapeFunction()
    {
        $result = h('<script>alert("xss")</script>');
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $result);
    }

    public function testHtmlEscapeWithNull()
    {
        $result = h(null);
        $this->assertEquals('', $result);
    }

    public function testHtmlEscapeWithNormalString()
    {
        $result = h('Hello World');
        $this->assertEquals('Hello World', $result);
    }

    public function testHtmlEscapeWithQuotes()
    {
        $result = h('He said "hello"');
        $this->assertEquals('He said &quot;hello&quot;', $result);
    }
}
