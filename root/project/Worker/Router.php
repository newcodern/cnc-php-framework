<?php

namespace Worker;

include_once dirname(__DIR__) . '/Worker/Autoloader.php';

Autoloader::register();
Autoloader::addNamespace('Utopia', dirname(__DIR__) . '/Utopia');
Autoloader::addNamespace('Contingent', dirname(__DIR__) . '/Contingent');
Autoloader::addNamespace('Controllers', dirname(__DIR__) . '/Controllers');
Autoloader::addNamespace('Worker', dirname(__DIR__) . '/Worker');

use Contingent\Pusat_Komunikasi;

Pusat_Komunikasi::register();
class Router
{
    protected static $routes = [];
    private $currentPrefix = '';

    public function addRoute($uri, $handler)
    {
        $this->routes[$uri] = $handler;
    }

    public function group($prefix, $callback)
    {
        $oldPrefix = $this->currentPrefix;
        $this->currentPrefix .= rtrim($prefix, '/');
        $callback($this);
        $this->currentPrefix = $oldPrefix;
    }

    public static function get($uri, $action)
    {
        self::$routes['GET'][$uri] = $action;
    }

    public static function post($uri, $action)
    {
        self::$routes['POST'][$uri] = $action;
    }

    public static function dispatch()
{
    $uri = isset($_GET['uri']) ? '/' . ltrim($_GET['uri'], '/') : '/';
    $method = $_SERVER['REQUEST_METHOD'];

    if ($uri === '/') {
        $uri = '/1';
    }

    // Check for direct match first
    if (isset(self::$routes[$method][$uri])) {
        list($controller, $method) = explode('@', self::$routes[$method][$uri]);
        $controller = "Controllers\\$controller";
        call_user_func_array([new $controller, $method], []);
        return;
    }

    // Check for pattern match
    foreach (self::$routes[$method] as $route => $action) {
        $pattern = self::buildPattern($route);
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            list($controller, $method) = explode('@', $action);
            $controller = "Controllers\\$controller";
            call_user_func_array([new $controller, $method], $matches);
            return;
        }
    }

    self::handleNotFound();
}

    private function matchRoute($uri, $route)
    {
        $pattern = $this->buildPattern($route);
        $uri = rtrim($uri, '/');

        if (isset($this->routes[$uri])) {
            return ['__direct__' => $uri];
        }

        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches);

            preg_match_all('/\{([a-zA-Z_]+)\}/', $route, $paramMatches);
            $paramNames = $paramMatches[1];
            $params = array_combine($paramNames, $matches);

            return $params;
        }

        return false;
    }

    private static function buildPattern($route)
{
    $pattern = preg_replace_callback('/\{([a-zA-Z_]+)\}/', function ($matches) {
        return '([^\/]+)';
    }, $route);

    $pattern = str_replace('/', '\/', $pattern);
    $pattern = "~^{$pattern}/?$~";

    return $pattern;
}

    private function callControllerMethod($controllerName, $methodName, $params)
{
    if (strpos($controllerName, 'Controller') !== false) {
        $controllerNameWithNamespace = '\Controllers\\' . $controllerName;
        $filePath = '../Controllers/' . str_replace('\\', '/', $controllerName) . '.php';

        if (file_exists($filePath)) {
            require_once $filePath;
            $controller = new $controllerNameWithNamespace();

            if (method_exists($controller, $methodName)) {
                call_user_func_array([$controller, $methodName], $params);
            } else {
                $this->handleNotFound();
            }
        } else {
            $this->handleNotFound();
        }
    } else {
        $this->handleNotFound();
    }
}


protected static function handleNotFound()
    {
        http_response_code(404);
        echo "404 Not Found";
        exit();
    }
}
