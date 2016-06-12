<?php
namespace Atmosphere\Typehint
{
    class Typehint
    {
        const PCRE = '#^argument (?<index>\d+) passed to ((?<class>[^\:]+)?(\::)?(?<function>[^ ]+)) must be an instance of (?<hint>[^,]+), (?<type>\w+) given#i';
        
        private static $MagicTypehints = array(
            '#()_and_()#'
        );
        private static $Typehints = array(
            'boolean'   => 'is_bool',
            'integer'   => 'is_int',
            'int'   => 'is_int',
            'float'     => 'is_float',
            'string'    => 'is_string',
            'resource'  => 'is_resource',
        );
        
        private static function getTypehintedArgument($backtrace, $class, $function, $index, &$value)
        {
            foreach ($backtrace as $trace) {
                if (!array_key_exists('function', $trace) OR strtolower($trace['function']) !== $function) {
                    continue;
                }
                if (!empty($class)) {
                    if (!array_key_exists('class', $trace) OR $trace['class'] !== $class) {
                        continue;
                    }
                }
                $value = $trace['args'][$index - 1];
                return true;
            }
            return false;
        }
        public static function handleTypehint($ErrLevel, $ErrMessage)
        {
            if ($ErrLevel == E_RECOVERABLE_ERROR) {
                if (preg_match(self::PCRE, $ErrMessage, $ErrMatches)){
                    $hint = $ErrMatches['hint'];
                    $hint = explode('\\', $hint);
                    $hint = $hint[count($hint)-1];
                    $hint = strtolower($hint);
                    $function = preg_replace('#\W+#i', '', $ErrMatches['function']);
                    $class = $ErrMatches['class'];
                    $index = $ErrMatches['index'];
                    if (array_key_exists($hint, self::$Typehints)){
                        $ThBacktrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
                        $ThArgValue  = null;
                        if (self::getTypehintedArgument($ThBacktrace, $class, $function, $index, $ThArgValue)){
                            if (call_user_func(self::$Typehints[$hint], $ThArgValue)){
                                return true;
                            }
                        }
                    }
                    return false;
                }
            }
            //
        }
        
        
        private static function isXhr ()
        {
            if (!array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) OR empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    	        return false;
    	    } elseif (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    	        return false;
    	    }
    	    return true;
        }
        private static function isMethodGet()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                return true;
            }
            return false;
        }
        private static function isMethodPost()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                return true;
            }
            return false;
        }
        private static function isMethodHeader()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'HEADER') {
                return true;
            }
            return false;
        }
        private static function isMethodPut()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                return true;
            }
            return false;
        }
        private static function isMethodDelete()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                return true;
            }
            return false;
        }
    }
}