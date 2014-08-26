<?php namespace KrisanAlfa\Kraken\Controller;

use Norm\Controller\NormController as NController;
use KrisanAlfa\Kraken\Contract\ControllerInterface;
use Bono\App;
use Norm\Norm;

/**
 * NormController
 *
 * @category  Controller
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
class NormController extends NController implements ControllerInterface
{
    public function __construct()
    {
    }

    public function initialize(App $app, $uri)
    {
        parent::__construct($app, $uri);

        $this->collection = Norm::factory($this->clazz);
    }
}
