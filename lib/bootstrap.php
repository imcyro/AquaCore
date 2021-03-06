<?php
use Aqua\Core\App;
use Aqua\Core\L10n;
use Aqua\Core\Exception\PHP;
use Aqua\Core\Exception\Assertion;
use Aqua\Core\Exception\CoreException;
use Aqua\Event\Event;
use Aqua\Log\ErrorLog;

$time = microtime(true);

(defined('Aqua\ROOT') and defined('Aqua\SCRIPT_NAME')) or die();
if(!defined('Aqua\PROFILE')) {
	/**
	 * @ignore
	 */
	define('Aqua\PROFILE', 'MAIN');
}
if(!defined('Aqua\ENVIRONMENT')) {
	/**
	 * @ignore
	 */
	define('Aqua\ENVIRONMENT', 'STANDARD');
}
if(!defined('PREG_BAD_UTF8_OFFSET_ERROR')) {
    /**
     * @ignore
     */
    define('PREG_BAD_UTF8_OFFSET_ERROR', 5);
}
define('AQUACORE', 1);

if(getenv('REQUEST_URI') === '/favicon.ico') {
	header('Content-Type: image/vnd.microsoft.icon');
	header('Content-Length: 0');
	die;
}
if(ini_get('register_globals')) {
	$sg = array($_REQUEST, $_SERVER, $_FILES);
	foreach($sg as $global) {
		foreach(array_keys($global) as $key) {
			unset(${$key});
		}
	}
}

include __DIR__ . '/functions/image_trim.php';
include __DIR__ . '/functions/secure_random_bytes.php';
include __DIR__ . '/functions/array_column.php';
include __DIR__ . '/functions/boolval.php';
include __DIR__ . '/functions/helpers.php';
include __DIR__ . '/Aqua/Event/SubjectInterface.php';
include __DIR__ . '/Aqua/Event/EventDispatcher.php';
include __DIR__ . '/Aqua/Autoloader/ClassMap.php';
include __DIR__ . '/Aqua/Autoloader/Autoloader.php';
include __DIR__ . '/Aqua/Core/App.php';
include __DIR__ . '/Aqua/Core/L10n.php';
include __DIR__ . '/Aqua/Core/Settings.php';
include __DIR__ . '/Aqua/Core/Exception/PHP.php';
include __DIR__ . '/Aqua/Core/Exception/Assertion.php';
include __DIR__ . '/Aqua/Core/Exception/CoreException.php';
include __DIR__ . '/Aqua/Storage/StorageInterface.php';
include __DIR__ . '/Aqua/Storage/StorageFactory.php';
include __DIR__ . '/Aqua/Http/Request.php';
include __DIR__ . '/Aqua/Http/Response.php';
include Aqua\ROOT . '/options.php';

set_include_path(Aqua\ROOT);

try {
	PHP::handleErrors();

	if(Aqua\ASSERT) {
		Assertion::enable();
	} else {
		Assertion::disable();
	}
	$settings_file = Aqua\ROOT . '/settings/application.php';
	App::registerAutoloaders();

	if(file_exists($settings_file)) {
		App::settings()->merge(include $settings_file);
		$settings = App::settings();
		$timezone = $settings->get('timezone', null);
		if($timezone && !date_default_timezone_set($timezone)) {
			throw new CoreException(
				__('exception', 'invalid-timezone', $timezone),
				CoreException::INVALID_TIMEZONE
			);
		}
	} else if(\Aqua\PROFILE === 'INSTALLER') {
		return;
	} else if(\Aqua\PROFILE === 'MAIN') {
		$url = 'http';
		$https = getenv('HTTPS');
		if($https === 'on' || $https === '1') {
			$url.= 's';
		}
		$url.= '://' . getenv('HTTP_HOST');
		$path = trim(substr(dirname(getenv('SCRIPT_FILENAME')), strlen(getenv('DOCUMENT_ROOT'))), '/\\');
		if($path) {
			$url.= '/' . $path;
		}
		$url .= '/install';
		App::response()->status(302)->redirect($url)->send();
		die;
	} else {
		die("AquaCore is not installed.\r\n");
	}
	App::defineConstants();
	App::registrySet('ac_time', $time);
	if(\Aqua\ENVIRONMENT === 'CLI' || php_sapi_name() === 'cli') {
		$args = array();
		for($i = 1; $i < $argc; ++$i) {
			if(preg_match('/([^=]+)=(.*)/', $argv[$i], $match)) {
				$args[$match[1]] = $match[2];
			} else {
				$args[$i] = $argv[$i];
			}
		}
		App::registrySet('arguments', $args);
		unset($args, $match);
	}
	switch(\Aqua\ENVIRONMENT) {
		case 'MINIMAL':
			break;
		case 'DEVELOPMENT':
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		default:
			if(App::settings()->get('tasks', false)) {
				register_shutdown_function(array( 'Aqua\\Schedule\\TaskManager', 'runTasks' ));
			}
			Event::bind('ragnarok.init', function() {
				App::upgrade();
			});
			include __DIR__ . '/Aqua/UI/Tag.php';
			include __DIR__ . '/Aqua/UI/Tag/Link.php';
			include __DIR__ . '/Aqua/UI/Tag/Script.php';
			include __DIR__ . '/Aqua/UI/ScriptManager.php';
			include __DIR__ . '/Aqua/UI/StyleManager.php';
			include __DIR__ . '/tags.php';
	}
	unset($settings);
	L10n::init();
} catch(Exception $exception) {
	ac_define_constants();
	if(!class_exists('Aqua\Log\ErrorLog', false)) {
		include __DIR__ . '/Aqua/Log/ErrorLog.php';
	}
	if(!class_exists('Aqua\UI\Template', false)) {
		include __DIR__ . '/Aqua/UI/Template.php';
	}
	if(!class_exists('Aqua\UI\Exception\TemplateException', false)) {
		include __DIR__ . '/Aqua/UI/Exception/TemplateException.php';
	}
	try {
		if(!ini_get('date.timezone') || !date_default_timezone_get()) {
			date_default_timezone_set('UTC');
			ini_set('date.timezone', 'UTC');
		}
		$error = ErrorLog::logText($exception);
		if(\Aqua\ENVIRONMENT === 'CLI') {
			die($exception->getMessage() . "\r\n");
		}
		$tpl = new \Aqua\UI\Template;
		$tpl->set('error', $error);
		echo $tpl->render('exception/layout');
		if(class_exists('Aqua\\Http\\Response', false)) {
			App::response()->send();
		}
	} catch(Exception $exception) {
		ErrorLog::logText($exception);
	}
	die;
}
