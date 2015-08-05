<?php

use zacharyrankin\just_test\Test;

require_once __DIR__ . '/../vendor/autoload.php';

Test::create(
    "we should be testing globs",
    function (Test $test) {
        $test->pass("more testing is good");
    }
);
