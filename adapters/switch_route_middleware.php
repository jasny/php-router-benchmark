<?php

use Lead\Box\Box;
use Jasny\SwitchRoute\Generator;
use Jasny\SwitchRoute\NoInvoker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response;

$box = box('switch-route-middleware', new Box());

$box->service('class_name', function() {
    return 'RouteMiddleware' . rand(0, 100000);
});

$box->service('script_file', function() use ($box) {
    $class = $box->get('class_name');

    return sys_get_temp_dir() . "/{$class}.php";
});

$box->service('generator', function() {
    return new Generator(new Generator\GenerateRouteMiddleware());
});

$box->service('handler', function() {
    return new class() implements RequestHandlerInterface{
        public $goodResponse;
        public $badResponse;

        public $expected;

        function __construct() {
            $this->goodResponse = new Response();
            $this->badResponse = new Response();
        }

        function handle(ServerRequestInterface $request): ResponseInterface {
            return $request->getAttribute('route:{id}') === $this->expected ? $this->goodResponse : $this->badResponse;
        }
    };
});

$box->service('build-routes', function() {
    return function($generator, $options = []) {
        $placeholderTemplate = function($name, $pattern) {
            return "{{$name}}";
        };

        return $generator->generate($placeholderTemplate, null, $options);
    };
});

$box->service('add-routes', function() use ($box) {
    return function($routes) use ($box) {
        $class = $box->get('class_name');
        $file = $box->get('script_file');
        $generator = $box->get('generator');

        $generator->generate($class, $file, function () use ($routes) {
            $switchRoutes = [];

            foreach ($routes as $route) {
                $key = join('|', $route['methods']) . ' ' . $route['pattern'];
                $switchRoutes[$key] = ['action' => 'nop'];
            }

            return $switchRoutes;
        }, false);

        require_once $file;
    };
});

$box->service('cleanup', function() use($box) {
    $file = $box->get('script_file');

    return function () use ($file) {
        if (file_exists($file)) {
            unlink($file);
        }
    };
});

