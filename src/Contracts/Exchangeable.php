<?php

namespace Wolo\Contracts;

interface Exchangeable
{
    public function exchange(mixed $data): static;
}