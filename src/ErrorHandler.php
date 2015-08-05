<?php
namespace zacharyrankin\just_test;

class ErrorHandler
{
    private $handled_error = false;

    public function register()
    {
        set_error_handler([$this, 'handleError'], E_ALL);
        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleFatalError']);
        register_shutdown_function([$this, 'exitOnError']);

        ini_set('log_errors', 0);
        ini_set('display_errors', 0);
    }

    public function handleError($type, $message, $file, $line)
    {
        $this->handled_error = true;
        $error_message = self::getErrorMessage($type, $message, $file, $line);
        $this->outputError($error_message);
    }

    public function handleException($exception)
    {
        $this->handled_error = true;
        $this->outputError(str_replace("\n", "\n    ", "{$exception}"));
    }

    public function handleFatalError()
    {
        $error = error_get_last();
        if (empty($error)) {
            return;
        }

        if (!$this->isFatalError($error)) {
            return;
        }

        $this->handleError(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
    }

    public function exitOnError()
    {
        if ($this->handled_error) {
            exit(1);
        }
    }

    /**
     * @param int $type
     * @param string $message
     * @param string $file
     * @param int $line
     * @return string
     */
    private static function getErrorMessage($type, $message, $file, $line)
    {
        return sprintf(
            "%s: %s in %s on line %s",
            self::getErrorType($type),
            $message,
            $file,
            $line
        );
    }

    /**
     * @param int $type
     * @return string
     */
    private static function getErrorType($type)
    {
        switch ($type) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return "Fatal error";
                break;
            case E_RECOVERABLE_ERROR:
                return "Catchable fatal error";
                break;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return "Warning";
                break;
            case E_PARSE:
                return "Parse error";
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                return "Notice";
                break;
            case E_STRICT:
                return "Strict standards";
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return "Deprecated";
                break;
            default:
                return "Unknown error";
                break;
        }
    }

    /**
     * @param $error_message
     */
    private function outputError($error_message)
    {
        $handle = fopen('php://stderr', 'w');
        fwrite(
            $handle,
            <<<ERROR
not ok 0 error
  ---
    {$error_message}
  ...
ERROR
        );
        fclose($handle);
        exit(1);
    }

    /**
     * @param $error
     * @return bool
     */
    private function isFatalError($error)
    {
        return $this->getErrorType($error['type']) === "Fatal error";
    }
}
