<?php

namespace Wolo\AttributesBag;

trait AttributesBagManager
{
    private function attributes(): AttributesBag
    {
        if (!isset($this->attributes)) {
            $this->attributes = new AttributesBag([]);
        }

        return $this->attributes;
    }

    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes()->offsetSet($key, $value);

        return $this;
    }

    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes()->get($key, $default);
    }


    public function hasAttribute(string $key): bool
    {
        return $this->attributes()->has($key);
    }


    public function getAttributes(): array
    {
        return $this->attributes()->toArray();
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes()->setAttributes($attributes);

        return $this;
    }


    public function mergeAttributes(array $attributes): static
    {
        $this->attributes()->mergeAttributes($attributes);

        return $this;
    }
}