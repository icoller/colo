<?php
require_once 'vendor/autoload.php';
require_once __DIR__.'/../Colo.php';

class ColoTest extends PHPUnit_Framework_TestCase
{
    function setUp() {
        Colo::init();
    }

    // Checks that default components are loaded
    function testDefaultComponents(){
        $request = Colo::request();
        $response = Colo::response();
        $router = Colo::router();
        $view = Colo::view();

        $this->assertEquals('colo\net\Request', get_class($request));
        $this->assertEquals('colo\net\Response', get_class($response));
        $this->assertEquals('colo\net\Router', get_class($router));
        $this->assertEquals('colo\template\View', get_class($view));
    }

    // Test get/set of variables
    function testGetAndSet(){
        Colo::set('a', 1);
        $var = Colo::get('a');

        $this->assertEquals(1, $var);

        Colo::clear();
        $vars = Colo::get();

        $this->assertEquals(0, count($vars));

        Colo::set('a', 1);
        Colo::set('b', 2);
        $vars = Colo::get();

        $this->assertEquals(2, count($vars));
        $this->assertEquals(1, $vars['a']);
        $this->assertEquals(2, $vars['b']);
    }

    // Register a class
    function testRegister(){
        Colo::path(__DIR__.'/classes');

        Colo::register('user', 'User');
        $user = Colo::user();

        $loaders = spl_autoload_functions();

        $this->assertTrue(sizeof($loaders) > 0);
        $this->assertTrue(is_object($user));
        $this->assertEquals('User', get_class($user));
    }

    // Map a function
    function testMap(){
        Colo::map('map1', function(){
            return 'hello';
        });

        $result = Colo::map1();

        $this->assertEquals('hello', $result);
    }

    // Unmapped method
    function testUnmapped() {
        $this->setExpectedException('Exception', 'doesNotExist must be a mapped method.');

        Colo::doesNotExist();
    }
}
