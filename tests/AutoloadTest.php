<?php
require_once 'vendor/autoload.php';
require_once __DIR__.'/../colo/autoload.php';

class AutoloadTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \colo\Engine
     */
    private $app;

    function setUp() {
        $this->app = new \colo\Engine();
        $this->app->path(__DIR__.'/classes');
    }

    // Autoload a class
    function testAutoload(){
        $this->app->register('user', 'User');

        $loaders = spl_autoload_functions();

        $user = $this->app->user();

        $this->assertTrue(sizeof($loaders) > 0);
        $this->assertTrue(is_object($user));
        $this->assertEquals('User', get_class($user));
    }

    // Check autoload failure
    function testMissingClass(){
        $test = null;
        $this->app->register('test', 'NonExistentClass');

        if (class_exists('NonExistentClass')) {
            $test = $this->app->test();
        }

        $this->assertEquals(null, $test);
    }
}
