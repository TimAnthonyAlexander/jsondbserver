<?php

namespace src\JsonDBServ;

class Manual {
    public static function route(string $command) {
        $command = strtolower($command);
        if (method_exists(self::class, $command)) {
            self::$command();
            die(0);
        }

        echo sprintf('Command "%s" not found.%s', $command, PHP_EOL);
        die(255);
    }

    private static function general() {
        $methods = get_class_methods(self::class);
        $methods = array_filter($methods, fn($method) => $method !== 'route' && $method !== 'general');

        $output = '';

        $output .= 'Possible commands:' . PHP_EOL;
        foreach ($methods as $method) {
            $output .= sprintf(' - %s%s', $method, PHP_EOL);
        }

        $output .= PHP_EOL;
        $output .= 'For more information about a command, use:'.PHP_EOL.'jsondbserver help <command>';

        print $output;
        die(0);
    }

    private static function insert() {
        echo <<<EOT
Usage: jsondbserver insert <table> <json>
EOT;
    }

    private static function delete() {
        echo <<<EOT
Usage: jsondbserver delete <table> <idCol> <idVal>
EOT;
    }

    private static function update() {
        echo <<<EOT
Usage: jsondbserver update <table> <idCol> <idVal> <newRowData>
EOT;
    }

    private static function select()
    {
        echo <<<EOT
Usage: jsondbserver select <table> <where> <allowLike> <sortBy> <sortByColumn> <sortByType> <descending> <firstWhereAnd> <wheres>
EOT;
    }
}
