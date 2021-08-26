<?php

use PhpCsFixer\Config;

$config = require __DIR__ . '/vendor/pararius/cs/php-cs-fixer.ta.php';
assert($config instanceof Config);
$config->getFinder()->in([
    __DIR__ . '/src',
    __DIR__ . '/tests',
]);

return $config;
