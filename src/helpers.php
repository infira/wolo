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
if (!function_exists('debugFunctionArgs')) {
    function debugFunctionArgs(): void
    {
        $trace = debug_backtrace()[1];
        if (isset($trace['class'])) {
            $ref = new \ReflectionMethod($trace['class'], $trace['function']);
        }
        else {
            $ref = new \ReflectionFunction($trace['function']);
        }
        $values = $trace['args'];
        $countValues = count($values);
        $names = array_map(function (\ReflectionParameter $param) {
            return $param->getName();
        }, $ref->getParameters());
        $countNames = count($names);;
        if ($countNames === $countValues) {
            debug(array_combine($names, $values));

            return;
        }
        if ($countNames > $countValues) {
            debug(
                array_merge(
                    array_combine(array_slice($names, 0, $countValues), $values),
                    ['un_matched_parameters' => array_slice($names, $countValues)]
                )
            );

            return;
        }
        debug(
            array_merge(
                array_combine($names, array_slice($values, 0, $countNames)),
                ['un_matched_values' => array_slice($values, $countNames)]
            )
        );

        return;
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
        $handler = Globals::get('wolo.getTraceDefaultHandler', static function ($file, $line) {
            return $file.':'.$line;
        });

        return array_map(static fn($item) => $handler($item['file'] ?? '', $item['line'] ?? 0), array_slice($backTrace, $startAt));
    }
}


if (!function_exists('checkArray')) {
    function checkArray($array): bool
    {
        return is_array($array) && count($array) > 0;
    }
}

