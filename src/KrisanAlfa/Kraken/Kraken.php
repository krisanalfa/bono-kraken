<?php namespace KrisanAlfa\Kraken;

use ArrayAccess;
use ReflectionParameter;
use ReflectionClass;
use Closure;
use Bono\App;
use Exception;

/**
 * Container that can resolve any dependency
 *
 * @category  Container
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
class Kraken implements ArrayAccess
{
    /**
     * The list of resolved dependencies
     *
     * @var array
     */
    protected $resolved = array();

    /**
     * The list of dependencies that has been registered
     *
     * @var array
     */
    protected $registry = array();

    /**
     * The list of instances from contracts
     *
     * @var array
     */
    protected $instances = array();

    /**
     * The list of rebound callbacks
     *
     * @var array
     */
    protected $reboundCallbacks = array();

    /**
     * The list of resolved callbacks
     *
     * @var array
     */
    protected $resolvingCallbacks = array();

    /**
     * The list of resolved callbacks as global callbacks
     *
     * @var array
     */
    protected $globalResolvingCallbacks = array();

    /******************************************************************************************************************/

    /**
     * Get the Closure to be used when building a type.
     *
     * @param string $contract
     * @param string $concrete
     *
     * @return \Closure
     */
    protected function getClosure($contract, $concrete)
    {
        return function ($container, $parameters = array()) use ($contract, $concrete)
        {
            $method = ($contract == $concrete) ? 'build' : 'resolve';

            return $container->$method($concrete, $parameters);
        };
    }

    /**
     * Get the rebound callbacks for a given type.
     *
     * @param string $contract
     *
     * @return array
     */
    protected function getReboundCallbacks($contract)
    {
        return (isset($this->reboundCallbacks[$contract])) ? $this->reboundCallbacks[$contract] : [];
    }

    /**
     * Get the concrete type for a given abstract.
     *
     * @param string $contract
     *
     * @return mixed $concrete
     */
    protected function getConcrete($contract)
    {
        return (isset($this->registry[$contract])) ? $this->registry[$contract]['concrete'] : $contract;
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param array $parameters
     * @param array $primitives
     *
     * @return array
     */
    protected function getDependencies($parameters, array $primitives = array())
    {
        $dependencies = array();

        foreach ($parameters as $parameter)
        {
            $dependency = $parameter->getClass();

            if (array_key_exists($parameter->name, $primitives))
            {
                $dependencies[] = $primitives[$parameter->name];
            }
            elseif (is_null($dependency))
            {
                $dependencies[] = $this->resolveNonClass($parameter);
            }
            else
            {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return (array) $dependencies;
    }

    /**
     * Resolve a non-class hinted dependency.
     *
     * @param ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws KrakenException
     */
    protected function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable())
        {
            return $parameter->getDefaultValue();
        }
        else
        {
            $message = "Dependency {$parameter} unresolved nor they're registered.";

            throw new KrakenException($message);
        }
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     *
     * @throws KrakenException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try
        {
            return $this->resolve($parameter->getClass()->name);
        }
        catch (KrakenException $e)
        {
            if ($parameter->isOptional())
            {
                return $parameter->getDefaultValue();
            }
            else
            {
                throw $e;
            }
        }
    }

    /**
     * If extra parameters are passed by numeric ID, rekey them by argument name.
     *
     * @param array $dependencies
     * @param array $parameters
     *
     * @return array
     */
    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value)
        {
            if (is_numeric($key))
            {
                unset($parameters[$key]);

                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Fire all of the resolving callbacks.
     *
     * @param       $contract
     * @param mixed $object
     *
     * @return void
     */
    protected function fireResolvingCallbacks($contract, $object)
    {
        if (isset($this->resolvingCallbacks[$contract]))
        {
            $this->fireCallbackArray($object, $this->resolvingCallbacks[$contract]);
        }

        $this->fireCallbackArray($object, $this->globalResolvingCallbacks);
    }

    /**
     * Fire an array of callbacks with an object.
     *
     * @param mixed $object
     * @param array $callbacks
     *
     * @return void
     */
    protected function fireCallbackArray($object, array $callbacks)
    {
        foreach ($callbacks as $callback)
        {
            call_user_func($callback, $object, $this);
        }
    }

    /******************************************************************************************************************/

    /**
     * Wrap a Closure such that it is shared.
     *
     * @param Closure $closure
     *
     * @return Closure
     */
    protected function share(Closure $closure)
    {
        return function ($container) use ($closure)
        {
            static $object;

            $object = $object ?: $closure($container);

            return $object;
        };
    }

    /**
     * Fire the "rebound" callbacks for the given abstract type.
     *
     * @param $contract
     *
     * @internal param string $abstract
     *
     * @return void
     */
    protected function rebound($contract)
    {
        $instance = $this->resolve($contract);

        foreach ($this->getReboundCallbacks($contract) as $callback)
        {
            call_user_func($callback, $this, $instance);
        }
    }

    /******************************************************************************************************************/

    /**
     * Determine if a given type is shared.
     *
     * @param $contract
     *
     * @internal param string $abstract
     *
     * @return bool
     */
    protected function isShared($contract)
    {
        $shared = (isset($this->registry[$contract]['shared'])) ? $this->registry[$contract]['shared'] : false;

        return isset($this->instances[$contract]) or $shared === true;
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param mixed $concrete
     * @param       $contract
     *
     * @internal param string $abstract
     *
     * @return bool
     */
    protected function isBuildable($concrete, $contract)
    {
        return $concrete === $contract or $concrete instanceof Closure;
    }

    /**
     * Determine if the given abstract type has been registered.
     *
     * @param $contract
     *
     * @internal param string $abstract
     *
     * @return bool
     */
    protected function hasBeenRegistered($contract)
    {
        return isset($this[$contract]) or isset($this->instances[$contract]);
    }

    /******************************************************************************************************************/

    /**
     * Register a binding with the container.
     *
     * @param                     $contract
     * @param Closure|string|null $concrete
     * @param bool                $shared
     *
     * @internal param string $abstract
     * @return void
     */
    public function register($contract, $concrete = null, $shared = false)
    {
        unset($this->instances[$contract]);

        $concrete = $concrete ?: $contract;

        if (! $concrete instanceof Closure) $concrete = $this->getClosure($contract, $concrete);

        $this->registry[$contract] = compact('concrete', 'shared');

        if ($this->hasBeenRegistered($contract)) $this->rebound($contract);
    }

    /**
     * Bind a shared Closure into the container.
     *
     * @param          $contract
     * @param \Closure $closure
     *
     * @internal param string $abstract
     * @return void
     */
    public function shared($contract, Closure $closure)
    {
        return $this->register($contract, $this->share($closure), true);
    }

    /**
     * Register a shared binding in the container.
     *
     * @param                     $contract
     * @param Closure|string|null $concrete
     *
     * @internal param string $abstract
     * @return void
     */
    public function singleton($contract, $concrete = null)
    {
        return $this->register($contract, $concrete, true);
    }

    /******************************************************************************************************************/

    /**
     * Resolve the given type from the container.
     *
     * @param       $contract
     * @param array $parameters
     *
     * @internal param string $abstract
     * @return mixed
     */
    public function resolve($contract, $parameters = array())
    {
        $this->resolved[$contract] = true;

        if (isset($this->instances[$contract])) return $this->instances[$contract];

        $concrete = $this->getConcrete($contract);

        $method = ($this->isBuildable($concrete, $contract)) ? 'build' : 'resolve';

        $object = $this->$method($concrete, $parameters);

        if ($this->isShared($contract)) $this->instances[$contract] = $object;

        $this->fireResolvingCallbacks($contract, $object);

        return $object;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param  string $concrete
     * @param  array  $parameters
     * @return mixed
     *
     * @throws KrakenException
     */
    public function build($concrete, $parameters = array())
    {
        if ($concrete instanceof Closure) return $concrete($this, $parameters);

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable())
        {
            $message = "Dependency [$concrete] is not registered, thus the concrete is not instantiable.";

            throw new KrakenException($message);
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) return new $concrete();

        $dependencies = $constructor->getParameters();

        $parameters = $this->keyParametersByArgument($dependencies, $parameters);

        $instances = $this->getDependencies($dependencies, $parameters);

        return $reflector->newInstanceArgs($instances);
    }

    /******************************************************************************************************************/

    /**
     * Determine if a given offset exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->registry[$key]);
    }

    /**
     * Get the value at a given offset.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->resolve($key);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $value = ($value instanceof Closure) ? $value : $value = function () use ($value) { return $value; };

        $this->register($key, $value);
    }

    /**
     * Unset the value at a given offset.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->registry[$key]);

        unset($this->instances[$key]);
    }
}
