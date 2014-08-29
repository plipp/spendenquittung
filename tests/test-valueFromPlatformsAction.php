<?php

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file)
    {
        return dirname($file) . '/';
    }
}

require_once("platforms/valueFromPlatformsAction.php");
require_once("platforms/platforms.php");

class ValueFromPlatformsActionTest extends PHPUnit_Framework_TestCase
{
    function testAverageProfitOfEmptyArray()
    {
        $this->assertEquals(-1, ValueFromPlatformsAction::averageFrom(array(), 0.001));
    }

    function testAverageProfitWithAllZeros()
    {
        $this->assertEquals(0, ValueFromPlatformsAction::averageFrom(array('a' => 0, 'b' => 0, 'c' => 0, 'd' => 0)), 0.001);
    }

    function testAverageProfitWithZeros()
    {
        $this->assertEquals(1.75, ValueFromPlatformsAction::averageFrom(array('a' => 0, 'b' => 3, 'c' => 4, 'd' => 0)), 0.001);
    }

    function testAverageProfitWithUnavailables()
    {
        $this->assertEquals(3.5, ValueFromPlatformsAction::averageFrom(array('a' => -1, 'b' => 3, 'c' => 4, 'd' => -1)), 0.001);
    }

    function testAverageProfitWithoutZeros()
    {
        $this->assertEquals(4.5, ValueFromPlatformsAction::averageFrom(array('a' => 5, 'b' => 3, 'c' => 4, 'd' => 6)), 0.001);
    }

    function testTitleOfEmptyArray()
    {
        $this->assertEquals("", ValueFromPlatformsAction::bestTitleFrom(array()));
    }

    function testBooklookerTitle()
    {
        $title = "a Title";
        $this->assertEquals($title, ValueFromPlatformsAction::bestTitleFrom(array(PlatformRegistry::BOOKLOOKER => $title)));
    }

    function testOtherStoresTitle()
    {
        $title = "a Title";
        $this->assertEquals($title, ValueFromPlatformsAction::bestTitleFrom(array(PlatformRegistry::ZVAB => $title)));
    }
}
 