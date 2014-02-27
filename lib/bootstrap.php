<?php
use Aqua\Core\App;
use Aqua\Core\Settings;
use Aqua\Core\L10n;
use Aqua\Core\Exception\PHP;
use Aqua\Core\Exception\Assertion;
use Aqua\Core\Exception\CoreException;
use Aqua\UI\StyleManager;
use Aqua\User\Role;
use Aqua\User\Account;
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

if($_SERVER['REQUEST_URI'] === '/favicon.ico') {
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
include __DIR__ . '/functions/helpers.php';
include __DIR__ . '/Aqua/Event/SubjectInterface.php';
include __DIR__ . '/Aqua/Event/EventDispatcher.php';
include __DIR__ . '/Aqua/Autoloader/ClassMap.php';
include __DIR__ . '/Aqua/Autoloader/Autoloader.php';
include __DIR__ . '/Aqua/Core/App.php';
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
		$url = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1') ? 'https://' : 'http://') .
		       $_SERVER['HTTP_HOST'] . trim(substr(dirname($_SERVER['SCRIPT_FILENAME']), strlen($_SERVER['DOCUMENT_ROOT'])), '/\\') .
		       '/install';
		App::response()->status(302)->redirect($url)->send();
		die;
	} else {
		die('AquaCore is not installed.');
	}
	include __DIR__ . '/Aqua/Core/L10n.php';
	App::defineConstants();
	App::registrySet('ac_time', $time);
	L10n::init(App::settings()->get('language', 'en'));
	switch(\Aqua\ENVIRONMENT) {
		case 'MINIMAL':
			break;
		case 'DEVELOPMENT':
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		default:
			include __DIR__ . '/Aqua/UI/Tag.php';
			include __DIR__ . '/Aqua/UI/Tag/Link.php';
			include __DIR__ . '/Aqua/UI/Tag/Script.php';
			include __DIR__ . '/Aqua/UI/ScriptManager.php';
			include __DIR__ . '/Aqua/UI/StyleManager.php';
			include __DIR__ . '/tags.php';
	}
} catch(Exception $exception) {
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
		$error = ErrorLog::logText($exception);
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
