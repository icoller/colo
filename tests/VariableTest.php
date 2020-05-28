<?php
require_once 'vendor/autoload.php';
require_once __DIR__.'/../colo/autoload.php';

class VariableTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \colo\Engine
     */
    private $app;

    function setUp() {
        $this->app = new \colo\Engine();
    }
    // Set and get a variable
    function testSetAndGet() {
        $this->app->set('a', 1);
        $var = $this->app->get('a');
        $this->assertEquals(1, $var);
    }

    // Clear a specific variable
    function testClear() {
        $this->app->set('b', 1);
        $this->app->clear('b');
        $var = $this->app->get('b');
        $this->assertEquals(null, $var);
    }

    // Clear all variables
    function testClearAll() {
        $this->app->set('c', 1);
        $this->app->clear();
        $var = $this->app->get('c');
        $this->assertEquals(null, $var);
    }

    // Check if a variable exists
    function testHas() {
        $this->app->set('d', 1);
        $this->assertTrue($this->app->has('d'));
    }
}
