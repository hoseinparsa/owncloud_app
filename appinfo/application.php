<?php
namespace OCA\Recognition\AppInfo;
use \OCP\AppFramework\App;
use \OCA\Recognition\Controller\ApiController;
use \OCP\IContainer;

class Application extends App {


        public function __construct(array $urlParams= []) {
                parent::__construct('recognition', $urlParams);
                $container = $this->getContainer();

                $server = $container->getServer();

                $container->registerService('ApiController', function (IContainer $c) use ($server) {
                        return new ApiController(
                                $c->query('AppName'),
                                $c->query('Request'),
                                $c->query('ServerContainer')->getTagManager(),
                                $server->getURLGenerator(),
                                $server->getConfig(),
                                $server->getEventDispatcher(),
                                $server->getUserSession(),
                                $server->getAppManager(),
                                $server->getRootFolder()
                       );
                });

    }

        /**
         * register navigation entry
         */
        public function registerNavigation() {
                $appName = $this->getContainer()->getAppName();
                $server = $this->getContainer()->getServer();

		$eventDispatcher = \OC::$server->getEventDispatcher();
		if (\OC_User::isLoggedIn()) {
		    	$eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
        		\OCP\Util::addScript('recognition', 'recognition');
        		\OCP\Util::addStyle('recognition','recognition');
    			});
		}

                $server->getNavigationManager()->add(function () use ($appName, $server) {
                        return [
                                'id' => $appName,
                                'order' => 5,
                                'href' => $server->getURLGenerator()
                                        ->linkToRoute('recognition.api.index'),
                                'icon' => $server->getURLGenerator()
                                        ->imagePath($appName, 'ai.svg'),
                                'name' => $server->getL10N($appName)->t('MANA Recognition'),

                        ];
                });
        }

}
