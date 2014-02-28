<?php

namespace Poorcode\Router;

use Poorcode\Exception\NotFoundException;

class Router {

    private $routes;

    function __construct()
    {
        $this->routes = [];
    }

    public function register($route, $callback, $owner = null)
    {
        $this->routes[] = $this->deconstruct($route, $callback, $owner);
    }

    private function deconstruct($routeString, $callback, $owner = null)
    {
        $argumentCount = 0;
        $routeRegex = "/^" . str_replace("/", "\\/", preg_replace('/\$[a-z]+/', '([^/]*)', $routeString, -1, $matches)) . "$/";
        return [
            'regex' => $routeRegex,
            'argumentCount' => $argumentCount,
            'callback' => function($arguments) use ($callback, $owner) {
                    if (!is_null($owner)) {
                        $callback = [$owner, $callback];
                    }
                    return call_user_func_array($callback, $arguments);
                }
        ];
    }

    public function route($inputRoute)
    {
        foreach ($this->routes as $route) {
            $matches = [];
            if (preg_match($route['regex'], $inputRoute, $matches)) {
                $matches = array_slice($matches, 1);
                return $route['callback']($matches);
            }
        }
        throw new NotFoundException("Couldn't find the provided route");
    }

} 