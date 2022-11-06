<?php

namespace Wolo\Request\Support;


class RequestVariableCollection
{
    protected array $data;

    public function __construct(array &$value)
    {
        $this->data = &$value;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key = null, mixed $default = null): mixed
    {
        if($key === null) {
            return $this->data;
        }
        if($this->has($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function delete(string|int|array $keys): void
    {
        foreach((array)$keys as $key) {
            if($this->has($key)) {
                unset($this->data[$key]);
            }
        }
    }


    public function flush(): void
    {
        $this->data = [];
    }
}