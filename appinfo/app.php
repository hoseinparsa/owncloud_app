<?php

//$eventDispatcher = \OC::$server->getEventDispatcher();
//if (\OC_User::isLoggedIn()) {
//    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
//        OCP\Util::addScript('recognition', 'recognition');
//	OCP\Util::addStyle('recognition','recognition');
//    });
//}

namespace OCA\Recognition\AppInfo;

$app = new Application();
$app->registerNavigation();



//\OC::$server->getNavigationManager()->add(function () {
//    $urlGenerator = \OC::$server->getURLGenerator();
//    return [
        // the string under which your app will be referenced in owncloud
//        'id' => 'recognition',
// 	'href' => $urlGenerator->linkToRoute('recognition.api.index'),
//        'icon' => $server->getURLGenerator()->imagePath($appName, 'add.svg'),

        // sorting weight for the navigation. The higher the number, the higher
        // will it be listed in the navigation
//	'appname' => 'recognition',
//	'order' => 50,
//    ];
//});
