#!/usr/local/bin/php
<?php

namespace bin;

use src\JsonDBServ\JsonDBServ;

$arg1     = $argv[1] ?? '';
$restArgs = array_slice($argv, 2);

require __DIR__.'/../vendor/autoload.php';

if (empty($arg1)) {
    echo 'Usage: jsondbserver <command> [arguments]'.PHP_EOL;
    exit;
}

JsonDBServ::execute($arg1, ...$restArgs);
