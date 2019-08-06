<?php
use Lead\Box\Box;

function fastroute_handler() {
}

$box = box('fast-route', new Box());

$box->service('cache_file', function() {
    return sys_get_temp_dir() . "/fast_route." . rand(0, 100000) . ".cache";
});

$box->service('build-routes', function() {
    return function($generator, $options = []) {
        $placeholderTemplate = function($name, $pattern) {
            return "{{$name}:{$pattern}}";
        };

        $segmentTemplate = function($segment, $greedy) {
            return "[{$segment}]" . ($greedy !== '?' ? $greedy : '');
        };

        return $generator->generate($placeholderTemplate, $segmentTemplate, $options);
    };
});

$box->service('add-routes', function() use($box) {
    $file = $box->get('cache_file');

    return function($routes) use ($file) {
        $router = FastRoute\cachedDispatcher(function($router) use ($routes) {
            foreach ($routes as $route) {
                foreach ($route['methods'] as $method) {
                    $router->addRoute($method, $route['pattern'], 'fastroute_handler');
                }
            }
        }, [
            'cacheFile' => $file,
            'cacheDisabled' => false,
        ]);

        return $router;
    };
});

$box->service('cleanup', function() use($box) {
    $file = $box->get('cache_file');

    return function () use ($file) {
        if (file_exists($file)) {
            unlink($file);
        }
    };
});

