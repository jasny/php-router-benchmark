<?php

$generator->template('/controller{%id%}/action{%id%}/{id}/{arg1}/{arg2}');

$buildRoutes = box('jasny')->get('build-routes');

list($ids, $routes) = $buildRoutes($generator, [
    'isolated' => box('benchmark')->get('isolated')
]);

$benchmark->run('Jasny', function() use ($box, $generator, $ids, $routes) {

    $addRoutes = box('jasny')->get('add-routes');
    $router = $addRoutes($routes);

    $strategy = box('benchmark')->get('strategy');

    foreach ($generator->methods() as $method) {
        $id = $strategy($ids, $method);

        $params = $router("/controller{$id}/action{$id}/{$id}/arg1/arg2", $method);

        if ($params['id'] !== (string) $id) {
            return false;
        }
    }

});
