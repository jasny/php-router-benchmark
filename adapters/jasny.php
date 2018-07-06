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

function generateCode($pointer, $i = 0, $indent = "") {
    if (count($pointer) === 1 && isset($pointer['*'])) {
        return generateCode($pointer['*'], $i+1, $indent); // only a default case
    }

    $code = "switch (" . ($i < 0 ? "\$host" : "\$parts[$i] ?? ''") . ") {\n";
    
    foreach ($pointer as $key => $sub) {
        $code .= $key !== '*' ? "{$indent}  case '$key': " : "{$indent}  default: ";
        if (is_array($sub)) {
            $code .= generateCode($sub, $i+1, $indent . "  ");
        } else {
            $code .= "return [";

            foreach ($sub as $var => $i) {
              $code .= "'$var' => \$parts[$i], ";
            }

            $code .= "];\n";
        }
    }

    $code .= "{$indent}}\n";

    return $code;
}


$box->service('add-routes', function() {

    return function($routes) {
        $grid = [];

        foreach ($routes as $route) {
            $parts = explode('/', trim($route['pattern'], '/'));

            if (!isset($grid[$route['host']])) {
                $grid[$route['host']] = [];
            }

            $pointer =& $grid[$route['host']];
            $vars = [];

            foreach ($parts as $i => $part) {
                [$match, $var] = explode(':', $part) + [1 => null];

                if (!isset($pointer[$match])) {
                  $pointer[$match] = [];
                }

                if (isset($var)) {
                    $vars[$var] = $i;
                }
                $pointer =& $pointer[$match];
            }

            $pointer[''] = (object)$vars;
        }

        $code = "<?php\n"
            . "return function(\$url, \$method, \$host = null) {\n"
            . "  \$parts = explode('/', trim(\$url, '/'));\n"
            . "  " . generateCode($grid, -1, "  ")
            . "};";
        
        file_put_contents("/tmp/router.php", $code);

        return include "/tmp/router.php";
    };
});
