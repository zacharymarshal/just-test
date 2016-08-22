<?php
namespace zacharyrankin\just_test;

use Exception;
use InvalidArgumentException;
use PHP_CodeCoverage;

class Test
{
    /**
     * @var int
     */
    public static $number = 0;
    /**
     * @var int
     */
    public static $passed = 0;
    /**
     * @var int
     */
    public static $failed = 0;

    /**
     * @var bool
     */
    public static $initialized = false;

    public static $coverage;

    /**
     *
     */
    public static function create()
    {
        self::init();

        $args = func_get_args();
        $name = null;
        $options = [];
        if (count($args) === 3) {
            list($name, $options, $func) = $args;
        } elseif (count($args) === 2) {
            list($name, $func) = $args;
        } elseif (count($args) === 1) {
            list($func) = $args;
        } else {
            throw new InvalidArgumentException("Invalid arguments");
        }

        // Skip test
        if (!empty($options['skip'])) {
            return;
        }

        if ($name) {
            echo "# {$name}\n";
        }

        if (!isset(self::$coverage)) {
            self::$coverage = new PHP_CodeCoverage;
        }
        self::$coverage->start($name);
        call_user_func($func, new Test);
        self::$coverage->stop();
    }

    /**
     * @param null $message
     */
    public function pass($message = null)
    {
        ++self::$passed;
        $number = ++self::$number;
        echo "ok {$number} {$message}\n";
    }

    /**
     * @param null $message
     * @param array $options
     */
    public function fail($message = null, $options = [])
    {
        $operator = $expected = $actual = $at = null;
        extract($options, EXTR_IF_EXISTS);

        ++self::$failed;
        $number = ++self::$number;
        echo "not ok {$number} {$message}\n";

        if ($options) {
            echo <<<MORE
  ---
    operator: {$operator}
    expected: {$this->formatValue($expected)}
    actual:   {$this->formatValue($actual)}
    at: {$at['file']}:{$at['line']}
  ...

MORE;
        }
    }

    /**
     * @param $value
     * @param null $message
     */
    public function ok($value, $message = null)
    {
        $expected = true;
        if ($value === $expected) {
            $this->pass($message);
        } else {
            $this->fail(
                $message,
                [
                    'operator' => 'ok',
                    'expected' => $expected,
                    'actual'   => $value,
                    'at'       => debug_backtrace()[0],
                ]
            );
        }
    }

    /**
     * @param $value
     * @param null $message
     */
    public function notOk($value, $message = null)
    {
        $expected = false;
        if ($value === $expected) {
            $this->pass($message);
        } else {
            $this->fail(
                $message,
                [
                    'operator' => 'notOk',
                    'expected' => $expected,
                    'actual'   => $value,
                    'at'       => debug_backtrace()[0],
                ]
            );
        }
    }

    /**
     * @param $actual
     * @param $expected
     * @param null $message
     */
    public function equals($actual, $expected, $message = null)
    {
        if ($actual == $expected) {
            $this->pass($message);
        } else {
            $this->fail(
                $message,
                [
                    'operator' => 'equals',
                    'expected' => $expected,
                    'actual'   => $actual,
                    'at'       => debug_backtrace()[0],
                ]
            );
        }
    }

    /**
     * @param $actual
     * @param $expected
     * @param null $message
     */
    public function notEquals($actual, $expected, $message = null)
    {
        if ($actual != $expected) {
            $this->pass($message);
        } else {
            $this->fail(
                $message,
                [
                    'operator' => 'notEquals',
                    'expected' => null,
                    'actual'   => $actual,
                    'at'       => debug_backtrace()[0],
                ]
            );
        }
    }

    /**
     * @param $func
     * @param $expected
     * @param null $message
     */
    public function throws($func, $expected, $message = null)
    {
        $actual = null;
        try {
            call_user_func($func);
        } catch (Exception $e) {
            $actual = sprintf(
                "exception '%s' with message '%s'",
                get_class($e),
                $e->getMessage()
            );
        }

        if ($actual && preg_match($expected, $actual)) {
            $this->pass($message);

            return;
        }

        $this->fail(
            $message,
            [
                'operator' => 'throws',
                'expected' => $expected,
                'actual'   => $actual,
                'at'       => debug_backtrace()[0],
            ]
        );
    }

    /**
     * @param $func
     * @param $message
     */
    public function doesNotThrow($func, $message)
    {
        $actual = null;
        try {
            call_user_func($func);
        } catch (Exception $e) {
            $actual = sprintf(
                "exception '%s' with message '%s'",
                get_class($e),
                $e->getMessage()
            );
        }

        if (!$actual) {
            $this->pass($message);

            return;
        }

        $this->fail(
            $message,
            [
                'operator' => 'doesNotThrow',
                'actual'   => $actual,
                'at'       => debug_backtrace()[0],
            ]
        );
    }

    /**
     *
     */
    private static function init()
    {
        if (self::$initialized) {
            return;
        }

        $error_handler = new ErrorHandler;
        $error_handler->register();

        register_shutdown_function(
            function () {
                echo "\n";
                echo "1.." . self::$number . "\n";
                echo "# tests " . self::$number . "\n";
                echo "# pass  " . self::$passed . "\n";
                if (self::$failed > 0) {
                    echo "# fail  " . self::$failed . "\n\n";
                } else {
                    echo "\n# ok\n\n";
                }

                $writer = new \PHP_CodeCoverage_Report_HTML;
                $writer->process(self::$coverage, '/tmp/code-coverage-report');

                exit((self::$failed > 0 ? 1 : 0));
            }
        );

        self::$initialized = true;
    }

    /**
     * @param $value
     * @return string
     */
    private function formatValue($value)
    {
        if (is_bool($value)) {
            return ($value ? 'true' : 'false');
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }
}
