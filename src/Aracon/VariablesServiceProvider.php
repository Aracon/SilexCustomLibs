<?php
/**
 * Created by PhpStorm.
 * User: Aracon
 * Date: 20.02.16
 * Time: 15:39
 */

namespace Aracon;

use Silex\Application;
use Pimple\ServiceProviderInterface;


class VariablesServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['var.table'] = 'variable';
        $app['var.db'] = null;
        $app['var'] = function($app) {
            if($app['var.db']==null) {
                throw new \ErrorException("Database for VariablesServiceProvider not set");
            }
            $var_svc = new VariableService($app['var.db'], $app['var.table'], true);
            if(isset($app['monolog'])) {
                $var_svc->setLogger($app['monolog']);
            }
            return $var_svc;
        };
    }

    public function boot(Application $app)
    {
    }

}