<?php namespace KrisanAlfa\Kraken\Controller;

use Bono\Controller\Controller as BonoController;
use KrisanAlfa\Kraken\Contract\ControllerInterface;
use Bono\App;

/**
 * Controller
 *
 * @category  Controller
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
abstract class Controller extends BonoController implements ControllerInterface
{
    /**
     * @var null
     */
    private $collection = null;

    /**
     * Reset the construct from parent
     */
    public function __construct()
    {
    }

    /**
     * We override parent controller __construct() here
     *
     * @param App    $app Bono App instance
     * @param string $uri Base uri of controller
     *
     * @return void
     */
    public function initialize(App $app, $uri)
    {
        parent::__construct($app, $uri);
    }
}
