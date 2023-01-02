<?php

namespace Wolo\Callables;

use Closure;
use Infira\Error\Error;
use ReflectionException;
use ReflectionParameter;
use ReflectionType;
use RuntimeException;
use Wolo\Reflection\Reflection;
use Wolo\TypeJuggling;

/**
 * @template TParamValue - callback value in hand
 * @template TArguments - array of all callback provided arguments
 */
class CallableInterceptor
{
    public const CONVERT_ALWAYS = 1;
    public const CONVERT_WHEN_TYPE_NOT_MATCH = 2;
    /**
     * @var callable
     */
    private $callback;


    /**
     * @var ReflectionParameter[]
     */
    private array $params;
    private array $paramConverters = [];

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public static function from(callable $callback): static
    {
        return new static($callback);
    }

    private function addConverter(string|int|array $argIndex, int $when, callable $converter): static
    {
        foreach ((array)$argIndex as $index) {
            if (isset($this->paramConverters[$index])) {
                throw new RuntimeException('converter is already defined');
            }
            $this->paramConverters[$index] = [
                'when' => $when,
                'converter' => $converter
            ];
        }

        return $this;
    }

    /**
     * intercept callable argument by index
     *
     * @param  string|int|array  $argIndex
     * @param  callable(ReflectionParameter, TParamValue, TArguments): mixed  $converter
     * @return $this
     */
    public function at(string|int|array $argIndex, callable $converter): static
    {
        return $this->addConverter($argIndex, self::CONVERT_ALWAYS, $converter);
    }

    /**
     * intercept callable argument by index when argument type does not match
     *
     * @param  string|int|array  $argIndex  argument position, or '*' for all arguments
     * @param  callable(ReflectionType, TParamValue, TArguments): mixed  $converter
     * @return $this
     */
    public function atWhenNoTypeMatch(string|int|array $argIndex, callable $converter): static
    {
        return $this->addConverter($argIndex, self::CONVERT_WHEN_TYPE_NOT_MATCH, $converter);
    }


    /**
     * @param  bool  $auto  - when all converters are passed try automatically cast type
     * @return Closure
     * @throws ReflectionException
     */
    public function get(bool $auto = true): Closure
    {
        if (!$this->paramConverters && !$auto) {
            return $this->callback;
        }
        $this->params = Reflection::getParameters($this->callback);

        return function () use ($auto) {
            $arguments = func_get_args();
            foreach ($arguments as $index => $value) {
                if (!isset($this->params[$index])) { //too many arguments were provided
                    continue;
                }
                $reflectionParam = $this->params[$index];
                $type = $reflectionParam->getType();

                if (isset($this->paramConverters[$index])) {
                    $converter = $this->paramConverters[$index];
                }
                elseif (isset($this->paramConverters['*'])) {
                    $converter = $this->paramConverters['*'];
                }

                if (!isset($converter) && !$auto) {
                    continue;
                }

                if (isset($converter) && $converter['when'] === self::CONVERT_ALWAYS) {
                    $conversion = ($converter['converter'])($reflectionParam, $value, $arguments);
                    if (!($conversion instanceof ContinueCallbackContract)) {
                        $arguments[$index] = $conversion;
                        continue;
                    }
                    unset($conversion);
                }

                if (is_null($type)) {
                    continue;
                }


                if (TypeJuggling::valueMatches($value, $type)) {
                    continue;
                }

                if (isset($converter) && $converter['when'] === self::CONVERT_WHEN_TYPE_NOT_MATCH) {
                    $conversion = ($converter['converter'])($type, $value, $arguments);

                    if (!($conversion instanceof ContinueCallbackContract)) {
                        $arguments[$index] = $conversion;
                        continue;
                    }
                    unset($conversion);
                }

                if ($auto) {
                    $arguments[$index] = TypeJuggling::cast($value, $type);
                }
            }

//            Error::setDebug('$arguments', [
//                '$arguments' => $arguments,
//                '$this->>params' => Reflection::getParameterNamesAndTypes($this->callback),
//            ]);

            return ($this->callback)(...$arguments);
        };
    }

    public static function continue(): ContinueCallbackContract
    {
        return new class implements ContinueCallbackContract {

        };
    }

    public static function break(): BreakCallbackContract
    {
        return new class implements BreakCallbackContract {

        };
    }
}