<?php

namespace Contingent;
use Utopia\KoSu;
use \PDO;

class DB
{
    private static $connections = [];
    private static $globalConnection;

    public static function setupGlobalConnection($name = 'default')
    {
        if (!isset(self::$connections[$name])) {
            $config = self::loadConfig()[$name];
            self::$connections[$name] = KoSu::getInstance($config['host'], $config['dbname'], $config['username'], $config['password']);
        }

        self::$globalConnection = self::$connections[$name];
    }

    public static function connection($name = 'default'): KoSu
    {
        if (self::$globalConnection) {
            return self::$globalConnection;
        }

        if (!isset(self::$connections[$name])) {
            $config = self::loadConfig()[$name];
            self::$connections[$name] = KoSu::getInstance($config['host'], $config['dbname'], $config['username'], $config['password']);
        }

        return self::$connections[$name];
    }

    private static function loadConfig()
    {
        return [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'xcxiwnpm_mirny',
                'username' => 'xcxiwnpm_general',
                'password' => 'm2pw}.j@r4XH',
            ],
            'database_Mirny_Ostrov' => [
                'host' => 'localhost',
                'dbname' => 'xcxiwnpm_mirny',
                'username' => 'xcxiwnpm_general',
                'password' => 'm2pw}.j@r4XH',
            ],
        ];
    }
}