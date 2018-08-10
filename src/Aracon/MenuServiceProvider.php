<?php
/**
 * Project: quest-ans.loc
 * User: Aracon
 * Date: 19.10.2016
 */

namespace Aracon;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class MenuServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['app.menu'] = array();
        $app['app.menu.rendered'] = function ($app) {
            return $this->renderMenu($app);
        };

        $app->before(function (Request $request, Application $app) {
            if(isset($app['view.add'])) {
                $app['view.add']($app, 'menu', $this->renderMenu($app));
            }
        });
    }

    public function boot(Application $app)
    {

    }

    private function renderMenu(Application $app)
    {
        $menu = array();
        foreach ($app['app.menu'] as $item) {
            $show = TRUE;
            if (isset($app['security.authorization_checker'])) {
                if (isset($item['role']) && $item['role']) {
                    if (!isset($app['user']) || !$app['security.authorization_checker']->isGranted($item['role'])) {
                        $show = FALSE;
                    }
                }
                if (isset($item['anonymous-only']) && $item['anonymous-only']) {
                    if (isset($app['user']) && $app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
                        $show = FALSE;
                    }
                }
            }
            if ($show) {
                $menu[] = $item;
            }
        }
        return $menu;
    }

}