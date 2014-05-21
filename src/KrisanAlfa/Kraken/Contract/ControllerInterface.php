<?php namespace KrisanAlfa\Kraken\Contract;

use Bono\App;

/**
 * ControllerInterface
 *
 * @category  Contract
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @link      https://github.com/krisanalfa/bonoblade
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
