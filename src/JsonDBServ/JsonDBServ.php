<?php

namespace src\JsonDBServ;

use JetBrains\PhpStorm\NoReturn;
use JsonException;
use src\JsonDB\JsonDB;

class JsonDBServ
{
    /**
     * @param string $command
     * @param string ...$arguments
     * @return void
     */
    #[NoReturn] public static function execute(string $command, string ...$arguments): void
    {
        $command = strtolower($command);

        if (method_exists(self::class, $command) && !in_array(strtolower($command), ['execute', 'generatejsondb'], true)) {
            self::$command(...$arguments);
        }

        echo sprintf('Command "%s" not found.%s', $command, PHP_EOL);
        die(255);
    }

    #[NoReturn] private static function help(string ...$arguments): void
    {
        Manual::route($arguments[0] ?? 'general');
        die(0);
    }

    /**
     * @param string ...$arguments
     * @return void
     */
    #[NoReturn] private static function insert(string ...$arguments): void
    {
        $jsonDB = self::generateJsonDB($arguments);
        $json  = $arguments[1] ?? null;
        if (empty($json)) {
            print 'Usage: jsondbserver insert <table> <json>';
            die(255);
        }

        $maxId = $jsonDB->getMaxId();
        try {
            $jsonDB->add('id', $maxId + 1, json_decode($json, true, 512, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            print 'Invalid JSON.' . PHP_EOL . $e->getMessage() . PHP_EOL;
            die(255);
        }

        print "Inserted. 1 row affected." . PHP_EOL;
        die(0);
    }

    /**
     * @param string ...$arguments
     * @return void
     * @throws JsonException
     */
    #[NoReturn] private static function delete(string ...$arguments): void
    {
        $jsonDB = self::generateJsonDB($arguments);
        $idCol = $arguments[1] ?? null;
        $idVal = $arguments[2] ?? null;

        if (empty($idCol) || empty($idVal)) {
            print 'Usage: jsondbserver delete <table> <idCol> <idVal>';
            die(255);
        }

        $jsonDB->delete($idCol, $idVal);

        print "Deleted. 1 row affected." . PHP_EOL;
        die(0);
    }

    /**
     * @throws JsonException
     */
    #[NoReturn] private static function select(string ...$arguments): void
    {
        $jsonDB = self::generateJsonDB($arguments);

        $where = json_decode($arguments[1] ?? '[]', true, 512, JSON_THROW_ON_ERROR);
        $allowLike = (bool) ($arguments[2] ?? 'false');
        $sortBy = (bool) ($arguments[3] ?? 'true');
        $sortByColumn = $arguments[4] ?? 'id';
        $sortByType = $arguments[5] ?? 'string';
        $descending = (bool) ($arguments[6] ?? 'true');
        $firstWhereAnd = (bool) ($arguments[7] ?? 'true');

        if ($where === null) {
            print 'Usage: jsondbserver select <table> <where> <allowLike> <sortBy> <sortByColumn> <sortByType> <descending> <firstWhereAnd> <wheres>';
            die(255);
        }

        $result = $jsonDB->selectWhere(
            where: $where ?? [],
            allowLike: $allowLike,
            sortBy: $sortBy,
            sortByColumn: $sortByColumn,
            sortByType: $sortByType,
            descending: $descending,
            whereIsAnd: $firstWhereAnd,
            furtherWheres: array_slice($arguments, 8)
        );

        print json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        die(0);
    }

    private static function selectPage(string ...$arguments): void
    {
        $jsonDB = self::generateJsonDB($arguments);

        $page = (int) ($arguments[1] ?? '1');
        $perPage = (int) ($arguments[2] ?? '10');
        $where = json_decode($arguments[3] ?? '[]', true, 512, JSON_THROW_ON_ERROR);
        $allowLike = (bool) ($arguments[4] ?? 'false');
        $sortBy = (bool) ($arguments[5] ?? 'true');
        $sortByColumn = $arguments[6] ?? 'id';
        $sortByType = $arguments[7] ?? 'string';
        $descending = (bool) ($arguments[8] ?? 'true');
        $firstWhereAnd = (bool) ($arguments[9] ?? 'true');
        $wheres = array_slice($arguments, 10);

        if ($where === null) {
            print 'Usage: jsondbserver select <table> <page> <perpage> <where> <allowLike> <sortBy> <sortByColumn> <sortByType> <descending> <firstWhereAnd> <wheres>';
            die(255);
        }

        $result = $jsonDB->getPage($page, $perPage, $where, $sortBy, $sortByColumn, $sortByType, $descending, $allowLike, $firstWhereAnd, $wheres);

        print json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        die(0);
    }

    /**
     * @throws JsonException
     */
    #[NoReturn] private static function deletetable(string ...$arguments): void
    {
        $jsonDB = self::generateJsonDB($arguments);

        $count = count($jsonDB->getContent());
        $jsonDB->deleteAll();

        print "Deleted. $count rows affected." . PHP_EOL;
        die(0);
    }

    /**
     * @param array $arguments
     * @return JsonDB
     */
    private static function generateJsonDB(array $arguments): JsonDB
    {
        $table = $arguments[0] ?? null;
        if (empty($table)) {
            print 'Usage: jsondbserver <command> <arguments>' . PHP_EOL;
            die(255);
        }

        return new JsonDB($table);
    }
}
