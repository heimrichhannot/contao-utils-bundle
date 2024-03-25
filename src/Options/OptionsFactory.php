<?php

namespace HeimrichHannot\UtilsBundle\Options;

use BadMethodCallException;

class OptionsFactory
{
    protected array $options = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->$key ?? $this->options[$key] ?? $default;
    }

    public function set(string $key, mixed $value): static
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        }
        $this->options[$key] = $value;
        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->$key) || isset($this->options[$key]);
    }

    public function del(string $key): static
    {
        unset($this->$key);
        unset($this->options[$key]);
        return $this;
    }

    protected function methodNameToOptionKey(string $name): string
    {
        return lcfirst(substr($name, 3));
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (strlen($name) < 4) {
            throw new BadMethodCallException(sprintf('Method %s does not exist', $name));
        }

        $numArgs = count($arguments);
        if ($numArgs < 1 || $numArgs > 1) {
            throw new BadMethodCallException(sprintf('Method %s expects exactly one argument', $name));
        }

        $key = static::methodNameToOptionKey($name);
        $prefix = substr($name, 0, 3);

        return match ($prefix)
        {
            'set' => $this->set($key, $arguments[0]),
            'get' => $this->get($key, $arguments[0] ?? null),
            'has' => $this->has($key),
            'del' => $this->del($key),
            default => throw new BadMethodCallException(sprintf('Method %s does not exist', $name)),
        };
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value)
    {
        $this->set($name, $value);
    }

    public static function create(): static
    {
        return new static();
    }
}