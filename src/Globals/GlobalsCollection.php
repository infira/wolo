<?php

namespace Wolo\Globals;


use JetBrains\PhpStorm\ArrayShape;
use Wolo\Hash;

class GlobalsCollection
{
    /**
     * @var GlobalsCollection[]
     */
    private array $collections = [];
    private array $data = [];

    public function __construct(private string $name, private string $parentCollectionName = '') {}

    /**
     * Generates new collection
     *
     * @param  string  $name  - collection name
     * @return GlobalsCollection
     */
    public function of(string $name): GlobalsCollection
    {
        if (!isset($this->collections[$name])) {
            $this->collections[$name] = new GlobalsCollection($name, $this->name);
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
     * @param  string|int  $key
     * @return bool
     */
    public function has(string|int $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Set new item
     *
     * @param  string|int  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put(string|int $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Add new item
     *
     * @param  mixed  $value
     * @return $this
     */
    public function add(mixed $value): static
    {
        $this->data[] = $value;

        return $this;
    }

    /**
     * @see GlobalsCollection::add()
     */
    public function append(mixed $value): static
    {
        return $this->add($value);
    }

    public function prepend(mixed $value, string|int $key = null): static
    {
        if (func_num_args() == 1) {
            array_unshift($this->data, $value);
        }
        else {
            $this->data = [$key => $value] + $this->data;
        }

        return $this;
    }

    /**
     * Push one or more items onto the end of the collection.
     *
     * @param  mixed  ...$values
     * @return $this
     */
    public function push(mixed...$values): static
    {
        foreach ($values as $value) {
            $this->data[] = $value;
        }

        return $this;
    }

    /**
     * Get item and then remove it
     *
     * @param  string|int  $key
     * @param  mixed  $default  if item were not found
     * @return mixed
     */
    public function pull(string|int $key, $default = null)
    {
        $value = $this->get($key, $default);
        if ($this->has($key)) {
            $this->forget($key);
        }


        return $value;
    }

    /**
     * Get item, if not found $returnOnNotFound will be returned
     *
     * @param  string|int  $key
     * @param  mixed  $default  - if not found then that is returned
     * @return mixed/bool
     */
    public function get(string|int $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->data[$key];
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param  string|int|string[]|int[]  $keys
     * @return $this
     */
    public function forget(string|int|array $keys): static
    {
        foreach ((array)$keys as $key) {
            if ($this->has($key)) {
                unset($this->data[$key]);
            }
        }

        return $this;
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
     * @param  callable  $callback
     * @return void
     */
    public function each(callable $callback): void
    {
        foreach ($this->data as $key => $value) {
            $callback($value, $key);
        }
    }

    /**
     * Call $callback for every collection, sub collection and every item<br />$callback($itemValue,$itemName,$collectionName)
     *
     * @param  callable  $callback
     * @return void
     */
    public function eachTree(callable $callback): void
    {
        foreach ($this->data as $name => $value) {
            $callback($value, $name, $this->name);
        }
        foreach ($this->collections as $collection) {
            $collection->eachTree($callback);
        }
    }

    /**
     * Call $callback for every collection<br />$callback($Collection,$collectionName)
     *
     * @param  callable  $callback
     * @return void
     */
    public function eachCollection(callable $callback): void
    {
        foreach ($this->collections as $name => $Collection) {
            $callback($Collection, $name);
        }
    }

    /**
     * get this all collections
     *
     * @return GlobalsCollection[]
     */
    public function collections(): array
    {
        return $this->collections;
    }

    /**
     * Execute $callback once by hash-sum of $parameters
     *
     * @param  mixed  ...$keys  - will be used to generate hash sum ID for storing $callback result <br>
     * If $keys contains only callback then hash sum will be generated Closure signature
     * @param  callable  $callback  method result will be set to memory for later use
     * @return mixed - $callback result
     * @noinspection PhpDocSignatureInspection
     * @see Hash::hashable()
     */
    public function once(...$keys): mixed
    {
        if (!$keys) {
            throw new \RuntimeException('parameters not defined');
        }
        $callback = $keys[array_key_last($keys)];
        if (!is_callable($callback)) {
            throw new \RuntimeException('last parameter must be callable');
        }
        //if at least one key is provided then use only keys to make hashable
        if (count($keys) > 1) {
            $keys = array_slice($keys, 0, -1);
        }
        array_push($keys, $this->parentCollectionName, $this->name);
        $cid = Hash::crc32b(...$keys);
        if (!$this->has($cid)) {
            $this->put($cid, $callback());
        }

        return $this->get($cid);
    }


    public function count(): int
    {
        return count($this->data);
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

    //region deprecated

    /**
     * @see static::has()
     * @deprecated
     */
    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @see static::put()
     * @deprecated
     */
    public function set(string|int $key, mixed $value): static
    {
        return $this->put($key, $value);
    }

    /**
     * @see static::forget()
     * @deprecated
     */
    public function delete(string|int|array $keys): static
    {
        return $this->forget($keys);
    }
    //endregion
}