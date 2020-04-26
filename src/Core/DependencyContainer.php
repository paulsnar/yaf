<?php declare(strict_types=1);
namespace PN\Yaf\Core;

class DependencyContainer
{
  private
    $implementations = [ ],
    $singletons = [ ],
    $constructors = [ ];

  public function get(string $classOrInterface): object
  {
    if ($classOrInterface === static::class) {
      return $this;
    }

    $class = $this->implementations[$classOrInterface] ?? $classOrInterface;

    if (array_key_exists($class, $this->singletons)) {
      return $this->singletons[$class];
    }

    try {
      $instance = $this->instantiate($class);
    } catch (DependencyException $exc) {
      throw $exc;
    } catch (\Throwable $exc) {
      throw new \RuntimeException("Failed to instantiate {$class}", 0, $exc);
    }

    return $instance;
  }

  public function instantiate(string $className)
  {
    if (array_key_exists($className, $this->constructors)) {
      return $this->constructors[$className]($this);
    }

    $class = new \ReflectionClass($className);

    if ($class->isInterface()) {
      throw new DependencyException("Interface {$className} not satisfied");
    }

    $constructor = $class->getConstructor();
    if ($constructor === null) {
      $instance = new $className();
    } else {
      $arguments = [ ];

      foreach ($constructor->getParameters() as $parameter) {
        $type = $parameter->getType();
        if ($type === null) {
          throw new DependencyException("Cannot inject untyped parameter " .
            $parameter->getName() . " in {$className} constructor");
        }
        $arguments[] = $this->get($type->getName());
      }

      $instance = new $className(...$arguments);
    }

    $this->singletons[$className] = $instance;
    return $instance;
  }

  public function provides(string $interface, $classOrConstructor): void
  {
    if (is_callable($classOrConstructor)) {
      $this->constructors[$interface] = $classOrConstructor;
    } else if (is_string($classOrConstructor)) {
      $this->implementations[$interface] = $classOrConstructor;
    } else {
      throw new \RuntimeException('Cannot save interface provision with ' .
        gettype($classOrConstructor) . ' implementation');
    }
  }

  public function store($instance): void
  {
    $class = get_class($instance);
    $this->singletons[$class] = $instance;
  }
}
