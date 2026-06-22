<?php

namespace App;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use InvalidArgumentException;

class Container
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];

    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared,
        ];
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->aliases[$abstract]);
    }

    public function get(string $abstract): mixed
    {
        $abstract = $this->resolveAlias($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (!$this->has($abstract)) {
            return $this->autowire($abstract);
        }

        $binding = $this->bindings[$abstract];
        $concrete = $binding['concrete'];

        if ($concrete instanceof Closure) {
            $object = $concrete($this);
        } elseif (is_string($concrete)) {
            $object = $this->resolve($concrete);
        } else {
            $object = $concrete;
        }

        if ($binding['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function resolve(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete);
        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("Class $concrete is not instantiable");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());
        return $reflection->newInstanceArgs($dependencies);
    }

    private function autowire(string $class): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Target $class is not binding and not a class");
        }

        return $this->resolve($class);
    }

    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $param) {
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();
                $dependencies[] = $this->get($className);

            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();

            } elseif ($type !== null && $type->isBuiltin() && !$param->isDefaultValueAvailable()) {
                throw new InvalidArgumentException(
                    "Cannot resolve parameter \${$param->getName()} in " . $param->getDeclaringClass()->getName()
                );
            } else {
                $dependencies[] = null;
            }
        }

        return $dependencies;
    }

    private function resolveAlias(string $abstract): string
    {
        while (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }
        return $abstract;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
