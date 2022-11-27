<?php


use Wolo\Globals\Globals;
use Wolo\VarDumper;

if (!function_exists('debug')) {
    function debug(mixed...$var): void
    {
        if (isset($_GET['traceDebug'])) {
            VarDumper::debug(...['debugTrace' => getTrace(1)]);
        }
        VarDumper::debug(...$var);
    }
}

if (!function_exists('cleanOutput')) {
    function cleanOutput(): void
    {
        if (ob_get_contents()) {
            ob_clean();
            ob_end_clean();
        }
    }
}


if (!function_exists('debugClean')) {
    function debugClean(mixed ...$var): void
    {
        cleanOutput();
        debug(...$var);
    }
}

if (!function_exists('setGetTraceItemHandler')) {
    function setGetTraceItemHandler(callable $callback): void
    {
        Globals::put('wolo.getTraceDefaultHandler', $callback);
    }
}


if (!function_exists('getTrace')) {
    function getTrace(int $startAt = 0): array
    {
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $handler = Globals::get('wolo.getTraceDefaultHandler', static function ($item) {
            if (!isset($item['file'])) {
                return $item;
            }

            return $item['file'].':'.$item['line'];
        });

        return array_map($handler, array_slice($backTrace, $startAt));
    }
}


if (!function_exists('checkArray')) {
    function checkArray($array): bool
    {
        return is_array($array) && count($array) > 0;
    }
}

