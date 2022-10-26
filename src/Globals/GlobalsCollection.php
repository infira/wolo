<?php

namespace Wolo\Globals;


use JetBrains\PhpStorm\ArrayShape;
use ReflectionException;
use RuntimeException;
use Wolo\Str;

final class GlobalsCollection
{
    private array $collections = [];

    private array $data = [];

    private string $name;

    public function __construct(string $collectionName)
    {
        $this->name = $collectionName;
    }

    /**
     * Generates new collection
     *
     * @param string $name - collection name
     * @return GlobalsCollection
     */
    public function of(string $name): GlobalsCollection
    {
        if (!isset($this->collections[$name])) {
            $this->collections[$name] = new GlobalsCollection($name);
        }

        return $this->collections[$name];
    }

    /**
     * Returns collection name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Checks if the item exists by key
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @see GlobalsCollection::exists()
     */
    public function has(string $key): bool
    {
        return $this->exists($key);
    }

    /**
     * Set new item
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Add new item
     *
     * @param mixed $value
     * @return void
     */
    public function add(mixed $value): void
    {
        $this->data[] = $value;
    }

    /**
     * @see GlobalsCollection::add()
     */
    public function append(mixed $value): void
    {
        $this->data[] = $value;
    }

    /**
     * Get item, if not found $returnOnNotFound will be returned
     *
     * @param string $key
     * @param mixed $default - if not found then that is returned
     * @return mixed/bool
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->exists($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * delete bye key
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        if ($this->exists($key)) {
            unset($this->data[$key]);
        }

        return true;
    }

    /**
     * get all items
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get current collection tree
     * [
     *      [collections] => [
     *          [collection1] => [
     *              [collections] => [...]
     *              [items] => [...]
     *          ]
     *      ]
     *      [items] => [
     *          "item1"=>"item value",
     *          "item2"=>"item2 value"
     *      ]
     *      .....
     */
    #[ArrayShape(['collections' => "array", 'items' => "array"])] public function tree(): array
    {
        $data = ['collections' => []];
        $this->eachCollection(function ($Collection, $collectionName) use (&$data) {
            $data['collections'][$collectionName] = ['collections' => [], 'items' => $Collection->getItems()];
            $tree = $Collection->getTree();
            if ($tree['collections']) {
                $data['collections'][$collectionName]['collections'] = $tree['collections'];
            }
        });
        $data['items'] = $this->all();

        return $data;
    }

    /**
     * Call $callback for every item in current collection<br />$callback($itemValue,$itemName)
     *
     * @param callable $callback
     * @return void
     */
    public function each(callable $callback): void
    {
        foreach ($this->data as $key => $value) {
            call_user_func($callback, $value, $key);
        }
    }

    /**
     * Call $callback for every collection, sub collection and every item<br />$callback($itemValue,$itemName,$collectionName)
     *
     * @param callable $callback
     * @return void
     */
    public function eachTree(callable $callback): void
    {
        foreach ($this->data as $name => $value) {
            call_user_func_array($callback, [$value, $name, $this->name]);
        }
        foreach ($this->collections as $name => $Collection) {
            $Collection->eachTree($callback);
        }
    }

    /**
     * Call $callback for every collection<br />$callback($Collection,$collectionName)
     *
     * @param callable $callback
     * @return void
     */
    public function eachCollection(callable $callback): void
    {
        foreach ($this->collections as $name => $Collection) {
            call_user_func_array($callback, [$Collection, $name]);
        }
    }

    /**
     * get this all collections
     *
     * @return array
     */
    public function collections(): array
    {
        return $this->collections;
    }

    /**
     * Execute $callback once by hash-sum of $parameters
     *
     * @param mixed ...$parameters - will be used to generate hash sum ID for storing $callback result
     * @param callable $callback method result will be set to memory for later use
     * @return mixed - $callback result
     * @throws ReflectionException
     * @noinspection PhpDocSignatureInspection
     */
    public function once(...$parameters): mixed
    {
        if (!$parameters) {
            throw new RuntimeException('parameters not defined');
        }
        $callback = $parameters[array_key_last($parameters)];
        if (!is_callable($callback)) {
            throw new RuntimeException('last parameter must be callable');
        }
        $cid = hash("crc32b", Str::hashable($parameters));
        if (!$this->exists($cid)) {
            $this->set($cid, $callback());
        }

        return $this->get($cid);
    }

    /**
     * Flush current data and collections
     *
     * @return bool
     */
    public function flush(): bool
    {
        $this->collections = [];
        $this->data = [];

        return true;
    }
}