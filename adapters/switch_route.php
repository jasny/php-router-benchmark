<?php

use Lead\Box\Box;
use Jasny\SwitchRoute\Generator;
use Jasny\SwitchRoute\Invoker;

$box = box('switch-route', new Box());

$box->service('function_name', function() {
    return 'switch_route_' . rand(0, 100000);
});

$box->service('script_file', function() use ($box) {
    $fn = $box->get('function_name');

    return sys_get_temp_dir() . "/{$fn}.php";
});

$box->service('generator', function() {
    $invoker = new class () extends Invoker {
        public function generateInvocation(
            ?string $controller,
            ?string $action,
            callable $genArg,
            string $new = '(new %s)'
        ): string {
            return "[\$segments[0], \$segments[1], " . $genArg('id', 'string', '') . "]";
        }
    };

    return new Generator(new Generator\GenerateFunction($invoker));
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
        $fn = $box->get('function_name');
        $file = $box->get('script_file');
        $generator = $box->get('generator');

        $generator->generate($fn, $file, function () use ($routes) {
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

