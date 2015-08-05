<?php

use zacharyrankin\just_test\Test;

require_once __DIR__ . '/../vendor/autoload.php';

Test::create(
    "something better fail",
    function (Test $test) {
        $test->pass('Yup');
        $a = [];
        $a->bloo();
    }
);
