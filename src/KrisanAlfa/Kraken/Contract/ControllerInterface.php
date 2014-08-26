<?php namespace KrisanAlfa\Kraken\Contract;

use Bono\App;

/**
 * ControllerInterface
 *
 * @category  Contract
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
interface ControllerInterface
{
    /**
     * We override parent controller __construct() here
     *
     * @param App    $app     Bono App instance
     * @param string $baseUri Base uri of controller
     *
     * @return void
     */
    public function initialize(App $app, $baseUri);

    /**
     * Define mapRoute()
     *
     * @return void
     */
    public function mapRoute();
}
