<?php
$generator->template('/controller{%id%}/action{%id%}/{id}/{arg1}/{arg2}');

$buildRoutes = box('fast-route-cache')->get('build-routes');

list($ids, $routes) = $buildRoutes($generator, [
    'isolated' => box('benchmark')->get('isolated')
]);

$addRoutes = box('fast-route-cache')->get('add-routes');
$router = $addRoutes($routes);

$benchmark->run('FastRoute (cache)', function() use ($box, $generator, $router, $ids, $routes) {
    $strategy = box('benchmark')->get('strategy');

    foreach ($generator->methods() as $method) {
        $id = $strategy($ids, $method);

        $result = $router->dispatch($method, "/controller{$id}/action{$id}/{$id}/arg1/arg2");
        $params = $result[2];

        if ($params['id'] !== (string) $id) {
            return false;
        }
    }
});

$cleanup = box('fast-route-cache')->get('cleanup');
$cleanup();

