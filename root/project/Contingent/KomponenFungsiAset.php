<?php
namespace Contingent;

class KomponenFungsiAset
{
    protected static $basePath = '/hanako/project/public/';

    public static function setBasePath($basePath)
    {
        // Set the base path for assets
        self::$basePath = rtrim($basePath, '/') . '/';
    }

    public static function url($path)
    {
        // Concatenate the base path and the asset path
        return self::$basePath . ltrim($path, '/');
    }
}