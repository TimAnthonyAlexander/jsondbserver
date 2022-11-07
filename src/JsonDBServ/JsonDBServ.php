<?php

namespace src\JsonDBServ;

use src\JsonDB\JsonDB;

class JsonDBServ
{
    public static function execute(string $command, string ...$arguments): string
    {
        if (method_exists(self::class, $command)) {
            return self::$command(...$arguments);
        }
        echo sprintf('Command "%s" not found.%s', $command, PHP_EOL);
        die(255);
    }

    private static function insert(string ...$arguments): string
    {
        $table = $arguments[0] ?? null;
        $json  = $arguments[1] ?? null;
        if (empty($table) || empty($json)) {
            print 'Usage: jsondbserver insert <table> <json>' . PHP_EOL;
            die(255);
        }

        $jsonDB = new JsonDB($table);
        $maxId = $jsonDB->getMaxId();
        try {
            $jsonDB->add('id', $maxId + 1, json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        } catch (\JsonException $e) {
            print 'Invalid JSON.' . PHP_EOL . $e->getMessage() . PHP_EOL;
            die(255);
        }
    }
}
