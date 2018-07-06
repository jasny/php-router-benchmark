<?php

use Lead\Box\Box;

$box = box('jasny', new Box());

$box->service('build-routes', function() {

    return function($generator, $options = []) {
        $placeholderTemplate = function($name, $pattern) {
            return "*:{$name}";
        };

        return $generator->generate($placeholderTemplate, null, $options);
    };
});

function generateCode($pointer, $i) {
    if (count($pointer) === 1 && isset($pointer['*'])) {
        return generateCode($pointer['*'], $i+1); // only a default case
    }

    $code = "switch (" . ($i < 0 ? "\$host" : "\$segments[$i] ?? ''") . ") {";
    
    foreach ($pointer as $key => $sub) {
        $code .= $key !== '*' ? "case '$key': " : "default: ";
        if ($key !== '') {
            $code .= generateCode($sub, $i+1);
        } else {
            $code .= "switch (\$method) {";

            foreach ($sub as $methods => $vars) {
                $code .= "case '" . str_replace('|', "':case '", $methods) . "':"
                  . "return [";

                foreach ($vars as $var => $i) {
                    $code .= "'$var' => \$segments[$i], ";
                }

                $code .= "];";
            }

            $code .= "}";
        }
    }

    $code .= "}";

    return $code;
}


$box->service('add-routes', function() {

    return function($routes) {
        $grid = [];

        foreach ($routes as $route) {
            $segments = explode('/', trim($route['pattern'], '/'));

            if (!isset($grid[$route['host']])) {
                $grid[$route['host']] = [];
            }

            $pointer =& $grid[$route['host']];
            $vars = [];

            foreach ($segments as $i => $segment) {
                [$match, $var] = explode(':', $segment) + [1 => null];

                if (!isset($pointer[$match])) {
                  $pointer[$match] = [];
                }

                if (isset($var)) {
                    $vars[$var] = $i;
                }
                $pointer =& $pointer[$match];
            }

            $pointer[''][join('|', $route['methods'])] = $vars;
        }

        $code = "<?php\n"
            . "return function(\$url, \$method, \$host = null) {"
            . "\$segments = explode('/', trim(\$url, '/'));"
            . generateCode($grid, -1)
            . "};";
        
        file_put_contents("/tmp/router.php", $code);

        return include "/tmp/router.php";
    };
});
