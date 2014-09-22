<?php namespace KrisanAlfa\Kraken\Controller;

use Norm\Controller\NormController as NController;
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

    public function delete($id)
    {
        $id = explode(',', $id);
        if ($this->request->isPost() || $this->request->isDelete()) {

            $single = false;
            if (count($id) === 1) {
                $single = true;
            }

            $this->data['entries'] = array();
            foreach ($id as $value) {
                $model = $this->collection->findOne($value);

                if (is_null($model)) {
                    if ($single) {
                        $this->app->notFound();
                    }
                    continue;
                }

                $model->remove();

                $this->data['entries'][] = $model;
            }

            h('notification.info', $this->clazz.' deleted.');

            h('controller.delete.success', array(
                'models' => $this->data['entries'],
            ));
        }

        $this->data['ids'] = $id;
    }

    public function delegate($method, $args = array())
    {
        $options = array(
            'method' => $method,
            'controller' => $this,
        );
        $this->app->applyHook('bono.controller.before', $options, 1);

        $this->app->filter('controller.method', function ($humanize) use ($method) {
            return ($humanize) ? Inflector::humanize($method) : $method;
        });

        $argCount = count($args);
        switch ($argCount) {
            case 0:
                $this->$method();
                break;
            case 1:
                $this->$method($args[0]);
                break;
            case 2:
                $this->$method($args[0], $args[1]);
                break;
            case 3:
                $this->$method($args[0], $args[1], $args[2]);
                break;
            case 4:
                $this->$method($args[0], $args[1], $args[2], $args[3]);
                break;
            case 5:
                $this->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
                break;
            default:
                call_user_func_array(array($this, $method), $args);
        }
        $this->app->applyHook('bono.controller.after', $options, 20);
    }
}
