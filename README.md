# just-test

minimalistic tap-producing testing framework, inspired by substack/tape.

## Usage

```php
use just_test\Test;

Test::create(
    "how do you call your test",
    function (Test $test) {
        $test->pass("woot");
    }
);
```

## TODO

 - [ ] More documentation
 - [ ] I still do not like Test::create(), there must be a cleaner syntax
 - [ ] Better tests :O
