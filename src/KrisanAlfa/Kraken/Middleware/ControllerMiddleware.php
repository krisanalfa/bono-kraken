<?php namespace KrisanAlfa\Kraken\Middleware;

use Slim\Middleware;
use KrisanAlfa\Kraken\Contract\ControllerInterface;
use KrisanAlfa\Kraken\KrakenException;

/**
 * ControllerMiddleware
 *
 * @category  Middleware
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
class ControllerMiddleware extends Middleware
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Bono\App
     */
    protected $app;

    /**
     * Call the middleware
     *
     * @return void
     */
    public function call()
    {
        $this->options = $this->app->config('kraken.controllers');

        if (empty($this->options['mapping'])) {
            $this->next->call();

            return;
        }

        $mapping = $this->options['mapping'];

        $this->configureMapping($mapping);

        $this->next->call();
    }

    /**
     * Mapping controller to current route
     *
     * @param array $mapping Map of controller base route from configuration
     *
     * @return void
     */
    protected function configureMapping(array $mapping)
    {
        $resourceUri = $this->app->request->getResourceUri();

        foreach ($mapping as $uri => $Map) {
            if (is_int($uri)) {
                $uri = $Map;
                $Map = null;
            }

            if (strpos($resourceUri, $uri) === 0) {
                $this->registerController($Map, $uri);

                break;
            }
        }
    }

    /**
     * Register the active controller to the container
     *
     * @param string $Map Controller class name
     * @param string $uri Base URI of it's controller given in the first argument
     *
     * @throws \KrisanAlfa\Kraken\KrakenException
     * @return void
     */
    protected function registerController($Map, $uri)
    {
        if (is_null($Map)) {
            if (! isset($this->options['default'])) {
                throw new KrakenException('URI "'.$uri.'" does not have suitable controller class "'.$Map.'"');
            }

            $Map = $this->options['default'];
        }

        if (!empty($this->app)) {
            $this->app->controller = $controller = $this->app->kraken->resolve($Map);
        }

        if (! isset($controller)) {
            return;
        }

        if (! $controller instanceof ControllerInterface) {
            throw new KrakenException(
                'Controller "'.$Map.'" should be instance of \KrisanAlfa\Kraken\Contract\ControllerInterface.'
            );
        }

        $controller->initialize($this->app, $uri);
    }
}
