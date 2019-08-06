<?php

$generator->template('/controller{%id%}/action{%id%}/{id}/{arg1}/{arg2}');

$buildRoutes = box('switch-route')->get('build-routes');

list($ids, $routes) = $buildRoutes($generator, [
    'isolated' => box('benchmark')->get('isolated')
]);

$addRoutes = box('switch-route')->get('add-routes');
$addRoutes($routes);

$router = box('switch-route')->get('function_name');

$benchmark->run('SwitchRoute', function() use ($box, $generator, $router, $ids, $routes) {
    $strategy = box('benchmark')->get('strategy');

    foreach ($generator->methods() as $method) {
        $id = $strategy($ids, $method);

        $params = $router($method, "/controller{$id}/action{$id}/{$id}/arg1/arg2");

        if ($params[2] !== (string) $id) {
            return false;
        }
    }
});

$cleanup = box('switch-route')->get('cleanup');
$cleanup();

