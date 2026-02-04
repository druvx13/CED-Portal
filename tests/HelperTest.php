<?php
/**
 * Copyright (C) 2026 NIKOL
 * Licensed under LUCA Free License v1.0
 * DO WHAT THE FUCK YOU WANT TO.
 */
use PHPUnit\Framework\TestCase;
use App\Utils\Helper;

class HelperTest extends TestCase {
    public function testSafeSlug() {
        $this->assertEquals('hello-world', Helper::safeSlug('Hello World'));
        $this->assertEquals('php-is-great', Helper::safeSlug('PHP is Great!'));
        $this->assertEquals('123-test', Helper::safeSlug('123 Test'));
    }

    public function testH() {
        $this->assertEquals('&lt;script&gt;', Helper::h('<script>'));
        $this->assertEquals('&quot;quote&quot;', Helper::h('"quote"'));
    }
}
