<?php

namespace Wolo\AttributesBag;

interface HasAttributes
{
    public function setAttribute(string $key, mixed $value): static;

    public function getAttribute(string $key, mixed $default = null): mixed;

    public function hasAttribute(string $key): bool;

    public function getAttributes(): array;

    public function setAttributes(array $attributes): static;

    public function mergeAttributes(array $attributes): static;
}