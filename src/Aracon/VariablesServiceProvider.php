<?php
/**
 * Created by PhpStorm.
 * User: Aracon
 * Date: 20.02.16
 * Time: 15:39
 */

namespace Aracon;

use Silex\Application;
use Silex\ServiceProviderInterface;


class VariableService {
    private $db;
    private $table;
    private $logger;

    private $vars;

    public function __construct($db, $table, $loadOnConstruct = false)
    {
        $this->db = $db;
        $this->table = mysqli_real_escape_string($db, $table);
        $this->vars = array();
        $this->logger = null;
        if($loadOnConstruct) {
            $this->loadAll();
        }
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function loadAll() {
        $query = "SELECT `key`, `value` FROM `$this->table`";
        $res = mysqli_query($this->db, $query);
        if(!$res) {
            throw new \ErrorException("Database query failed");
        }
        while($row = mysqli_fetch_assoc($res)) {
            $this->vars[$row['key']] = unserialize($row['value']);
        }
    }

    public function set($name, $value) {
        $name = mysqli_real_escape_string($this->db, $name);
        $dbvalue = serialize($value);
        if(isset($this->vars[$name])) {
            $query = "UPDATE `$this->table` SET `value` = '$dbvalue' WHERE `key`='$name'";
        } else {
            $query = "INSERT INTO `$this->table` (`key`, `value`) VALUES('$name', '$dbvalue')";
        }

        if($this->logger) {
            $this->logger->addDebug("VariableService query",array('query' => $query));
        }

        $res = mysqli_query($this->db, $query);
        if(!$res) {
            throw new \Exception("Database query failed");
        }
        $this->vars[$name] = $value;
    }

    public function get($name, $default = '')
    {
        if(isset($this->vars[$name])) {
            return $this->vars[$name];
        } else {
            return $default;
        }
    }
}

class VariablesServiceProvider implements ServiceProviderInterface
{

    public function register(Application $app)
    {
        $app['var.table'] = 'variable';
        $app['var.db'] = null;
        $app['var'] = $app->share(function($app) {
            if($app['var.db']==null) {
                throw new \ErrorException("Database for VariablesServiceProvider not set");
            }
            $var_svc = new VariableService($app['var.db'], $app['var.table'], true);
            if(isset($app['monolog'])) {
                $var_svc->setLogger($app['monolog']);
            }
            return $var_svc;
        });
    }

    public function boot(Application $app)
    {
    }

}