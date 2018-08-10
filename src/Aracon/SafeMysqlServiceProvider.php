<?php
/**
 * Created by PhpStorm.
 * User: Aracon
 * Date: 20.02.16
 * Time: 14:00
 */

namespace Aracon;

use Bitrix24\Presets\App\App;
use Silex\Application;
use Pimple\ServiceProviderInterface;
use SafeMySQL;

class SafeMysqlServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {

        $app['safemysql.user'] = '';
        $app['safemysql.pass'] = '';
        $app['safemysql.host'] = 'localhost';
        $app['safemysql.charset'] = 'utf8';
        $app['safemysql.socket'] = null;
        $app['safemysql.port'] = null;
        $app['safemysql.errmode'] = 'exception';
        $app['safemysql.exception'] = 'Exception';

        $this->setAppMysqli($app);
        $this->setAppSafeMysql($app);

        $app['mysqli.ping'] = $app->protect(function($app) {
            //$app['monolog']->addDebug('mysqli ping');
            @mysqli_query($app['mysqli'], 'SELECT LAST_INSERT_ID()');

            //$app['monolog']->addDebug('mysqli ping errno '.mysqli_errno($app['mysqli']));

            if (mysqli_errno($app['mysqli']) == 2006) {
                $res = mysqli_real_connect($app['mysqli'], $app['safemysql.host'],
                    $app['safemysql.user'], $app['safemysql.password'], $app['safemysql.dbname'],
                    $app['safemysql.port'], $app['safemysql.socket']);

                if ( !$res )
                {
                    throw new \Exception(mysqli_connect_errno()." ".mysqli_connect_error());
                }

                if(!mysqli_set_charset($app['mysqli'], $app['safemysql.charset'])) {
                    throw new \Exception(mysqli_error($app['mysqli']));
                }
            }
        });

    }

    private function setAppMysqli(Application $app) {
        $app['mysqli'] = function (Application $app) {
            $conn = $this->connectToMysql($app);
            return $conn;
        };
    }

    private function setAppSafeMysql(Application $app) {
        $app['safemysql'] = function ($app) {
            $param = array(
                'mysqli' => $app['mysqli'],
                'errmode' => $app['safemysql.errmode'],
                'exception' => $app['safemysql.exception'],
            );
            return new SafeMySQL($param);
        };
    }

    public function connectToMysql(Application $app) {
        @$conn = mysqli_connect($app['safemysql.host'],
            $app['safemysql.user'], $app['safemysql.password'], $app['safemysql.dbname'],
            $app['safemysql.port'], $app['safemysql.socket']);

        if ( !$conn )
        {
            throw new \Exception(mysqli_connect_errno()." ".mysqli_connect_error());
        }

        if(!mysqli_set_charset($conn, $app['safemysql.charset'])) {
            throw new \Exception(mysqli_error($conn));
        }
        return $conn;
    }

    public function boot(Application $app)
    {
    }

}