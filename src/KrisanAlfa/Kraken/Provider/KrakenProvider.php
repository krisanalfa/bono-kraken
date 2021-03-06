<?php namespace KrisanAlfa\Kraken\Provider;

use Bono\Provider\Provider;
use KrisanAlfa\Kraken\Kraken;

/**
 * KrakenProvider
 *
 * @property  mixed  options
 * @property  mixed  app
 * @category  Provider
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
class KrakenProvider extends Provider
{
    /**
     * Initialize the provider
     *
     * @return void
     */
    public function initialize()
    {
        $this->app->container->singleton('kraken', function () {
            return new Kraken();
        });
    }
}
