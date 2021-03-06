<?php
use Aqua\Core\App;
use Aqua\Log\ErrorLog;
use Aqua\Permission\PermissionSet;
use Aqua\Router\Router;
use Aqua\UI\Form;
use Aqua\UI\Template;

define('DEFAULT_TIMEZONE', ini_get('date.timezone'));

if(!DEFAULT_TIMEZONE) {
	date_default_timezone_set('UTC');
	ini_set('date.timezone', 'UTC');
}


define('Aqua\ROOT', str_replace('\\', '/', dirname(__DIR__)));
define('Aqua\SCRIPT_NAME', basename(__FILE__));
define('Aqua\PROFILE', 'INSTALLER');
define('Aqua\ENVIRONMENT', 'MINIMAL');
define('Aqua\REWRITE', false);

include __DIR__ . '/../lib/bootstrap.php';
include __DIR__ . '/AquaCoreSetup.php';
include __DIR__ . '/functions.php';

App::response()->capture();

function __setup($key) {
	$arguments = func_get_args();
	array_shift($arguments);
	return App::registryGet('setup')->translate($key, $arguments);
}

try {
	$setup = new AquaCoreSetup(App::request(), App::response());
	App::registrySet('setup', $setup);
	App::autoloader('Page')->addDirectory(__DIR__ . '/application/pages');
	$permissionSet = new PermissionSet;
	$permissionSet->set('setup')->allowAll();
	$router = new Router;
	$router->add('setup')->map('/*', '/setup/:path');
	echo App::dispatcher($router, $permissionSet)->dispatch(App::user(), App::response());
	$setup->commit();
} catch(Exception $exception) {
	$error = ErrorLog::logText($exception);
	App::response()->endCapture(false)->capture();
	$tpl = new Template;
	$tpl->set('error', $error);
	echo $tpl->render('exception/layout');
}
App::response()->send();
