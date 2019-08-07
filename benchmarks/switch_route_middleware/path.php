<?php
use Zend\Diactoros\ServerRequest;

$generator->template('/controller{%id%}/action{%id%}/{id}/{arg1}/{arg2}');

$buildRoutes = box('switch-route-middleware')->get('build-routes');

list($ids, $routes) = $buildRoutes($generator, [
    'isolated' => box('benchmark')->get('isolated')
]);

$addRoutes = box('switch-route-middleware')->get('add-routes');
$addRoutes($routes);

$benchmark->run('SwitchRoute (psr)', function() use ($box, $generator, $ids, $routes) {
    $class = box('switch-route-middleware')->get('class_name');
    $middleware = new $class();

    $strategy = box('benchmark')->get('strategy');

    $handler = box('switch-route-middleware')->get('handler');

    foreach ($generator->methods() as $method) {
        $id = $strategy($ids, $method);
        $handler->expected = (string)$id;

        $request = new ServerRequest([], [], "/controller{$id}/action{$id}/{$id}/arg1/arg2", $method);
        $response = $middleware->process($request, $handler);

        if ($response === $handler->badResponse) {
            return false;
        }
    }
});

$cleanup = box('switch-route-middleware')->get('cleanup');
$cleanup();

