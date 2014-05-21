<?php namespace KrisanAlfa\Kraken\Controller;

use Norm\Controller\NormController as NController;
use KrisanAlfa\Kraken\Contract\ControllerInterface;
use Bono\App;
use Norm\Norm;

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
