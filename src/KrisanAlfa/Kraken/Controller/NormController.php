<?php namespace KrisanAlfa\Kraken\Controller;

use Norm\Controller\NormController as BaseController;
use KrisanAlfa\Kraken\Contract\ControllerInterface;
use Bono\App;
use Norm\Norm;
use ROH\Util\Inflector;

/**
 * NormController
 *
 * @category  Controller
 * @package   Bono
 * @author    Krisan Alfa Timur <krisan47@gmail.com>
 * @copyright 2013 PT Sagara Xinix Solusitama
 */
class NormController extends BaseController implements ControllerInterface
{
    public function __construct()
    {
    }

    public function initialize(App $app, $uri)
    {
        parent::__construct($app, $uri);
    }
}
