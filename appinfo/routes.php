<?php
namespace OCA\Recognition\AppInfo;

$application = new Application();
$application->registerRoutes($this, array(
    'routes' => array(
	array('name' => 'api#index', 		'url' => '/', 			'verb' => 'GET'),
        array('name' => 'api#SaveSettings',    	'url' => '/api/v1/SaveSettings','verb' => 'POST'),
        array('name' => 'api#AddEmp', 		'url' => '/api/v1/AddEmp', 	'verb' => 'POST'),
        array('name' => 'api#RecFile', 		'url' => '/api/v1/Recognition', 'verb' => 'POST'),
    )
));


