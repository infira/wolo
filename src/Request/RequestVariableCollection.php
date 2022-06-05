<?php

namespace Wolo\Request;


class RequestVariableCollection
{
	private array $data;
	
	public function __construct(array &$value)
	{
		$this->data = &$value;
	}
	
	public function all(): array
	{
		return $this->data;
	}
	
	public function get(string $name = null, mixed $default = null): mixed
	{
		if ($name === null) {
			return $this->data;
		}
		if (self::exists($name)) {
			return $this->data[$name];
		}
		
		return $default;
	}
	
	public function set(string $name, $value)
	{
		$this->data[$name] = $value;
	}
	
	public function delete(string $name)
	{
		if ($this->has($name)) {
			unset($this->data[$name]);
		}
	}
	
	public function unset(string $key)
	{
		$this->delete($key);
	}
	
	public function exists(string $name): bool
	{
		return array_key_exists($name, $this->data);
	}
	
	public function has(string $name): bool
	{
		return $this->exists($name);
	}
	
	public function flush()
	{
		$this->data = [];
	}
}