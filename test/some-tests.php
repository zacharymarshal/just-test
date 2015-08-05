<?php

use zacharyrankin\just_test\Test;

require_once __DIR__ . '/../vendor/autoload.php';

Test::create(
    "ok does the proper checks",
    function (Test $test) {
        $test->ok(false, "false cannot be okay, okay");
        $test->ok(true, "true should be okay");
    }
);

Test::create(
    "pass lets the test pass",
    function (Test $test) {
        $test->pass("all good");
    }
);

Test::create(
    "fail actually fails",
    function (Test $test) {
        $test->fail("such a good fail");
    }
);

Test::create(
    "notOk checks for falsey",
    function (Test $test) {
        $test->notOk(false, "false is not okay");
        $test->notOk(true, "true is okay");
    }
);

Test::create(
    "throws looks for exceptions",
    function (Test $test) {
        $test->throws(
            function () {
                return true;
            },
            "/Exception/",
            "you should have thrown an exception bruv"
        );
        $test->throws(
            function () {
                throw new Exception("Aww yea");
            },
            "/Exception/",
            "there you go"
        );
    }
);

Test::create(
    "doesNotThrow should check exceptions are not thrown",
    function (Test $test) {
        $test->doesNotThrow(
            function () {
                return true;
            },
            "looks good"
        );
        $test->doesNotThrow(
            function () {
                throw new Exception("Aww yea");
            },
            "you shouldn't have thrown an exception mate"
        );
    }
);

Test::create(
    "equals",
    function(Test $test) {
        $test->equals(1, 1, "should be good");
        $test->equals(0, 1, "should fail");
    }
);

Test::create(
    "not equals",
    function(Test $test) {
        $test->notEquals(0, 1, "should be good");
        $test->notEquals(1, 1, "should fail");
    }
);
