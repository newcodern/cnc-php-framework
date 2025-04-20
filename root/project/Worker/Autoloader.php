<?php

namespace Worker;

class Autoloader
{
    protected static $directories = [];

    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function addNamespace($namespace, $directory)
    {
        self::$directories[$namespace] = $directory;
    }

    protected static function autoload($class)
    {
        $namespace = strtok($class, '\\');

        if (isset(self::$directories[$namespace])) {
            $classPath = str_replace($namespace, '', $class);
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $classPath);
            $file = self::$directories[$namespace] . DIRECTORY_SEPARATOR . $classPath . '.php';

            if (file_exists($file)) {
                require $file;
            }
        }
    }
}