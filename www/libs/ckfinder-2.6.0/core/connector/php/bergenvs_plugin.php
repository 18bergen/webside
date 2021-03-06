<?php

define('BG_ROOT_PATH', dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
define('BG_INC_PATH', BG_ROOT_PATH . '/includes/');

require_once(BG_ROOT_PATH . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(BG_ROOT_PATH);
$dotenv->load();

Sentry\init(['dsn' => $_ENV['SENTRY_DSN']]);

require_once(BG_INC_PATH . 'head.php');
require_once(BG_CLASS_PATH . 'ckfinderplugin.php');

$bg18 = new ckFinderPlugin();
$dp0->prepareClassInstance($bg18);
$bg18->initialize();
