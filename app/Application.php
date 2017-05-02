<?php

namespace App;

use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

class Application {

    private $dispatcher;
    private $templating;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__. "/../views/%name%");
        $this->templating = new PhpEngine(new TemplateNameParser(), $loader);
        $this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            $r->addRoute("GET", "/", [$this, "index"]);
            $r->addRoute("GET", "/post/{id:\d+}", [$this, "post"]);
        });
    }

    public static function getInstance() {
        return new Application();
    }

    public function index() {
        return array("view" => "index.php", "data" => array());
    }

    public function post($id) {
        return array("view" => "post.php", "data" => array("id" => $id));
    }

    public function start() {
        $httpMethod = $_SERVER["REQUEST_METHOD"];
        $uri = $_SERVER["REQUEST_URI"];

        if (false !== $pos = strpos($uri, "?")) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        $modelAndView = call_user_func_array($routeInfo[1], $routeInfo[2]);
        echo $this->templating->render($modelAndView["view"], $modelAndView["data"]);
    }

}