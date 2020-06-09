<?php
require_once 'vendor/autoload.php';
require_once __DIR__.'/../Colo.php';

class RenderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \colo\Engine
     */
    private $app;

    function setUp() {
        $this->app = new \colo\Engine();
        $this->app->set('colo.views.path', __DIR__.'/views');
    }

    // Render a view
    function testRenderView(){
        $this->app->render('hello', array('name' => 'Bob'));

        $this->expectOutputString('Hello, Bob!');
    }

    // Renders a view into a layout
    function testRenderLayout(){
        $this->app->render('hello', array('name' => 'Bob'), 'content');
        $this->app->render('layouts/layout');

        $this->expectOutputString('<html>Hello, Bob!</html>');
    }
}
