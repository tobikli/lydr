<?php

declare(strict_types=1);

namespace Hostinger\Tests\Integration\Utils;

use Hostinger\Tests\Integration\TestCase;
use Hostinger\WpHelper\Utils;

class UtilsTest extends TestCase
{
    public function test_is_plugin_activate(): void
    {
        activate_plugin('hello.php');

        $this->assertTrue(Utils::isPluginActive('hello'));
    }
}
