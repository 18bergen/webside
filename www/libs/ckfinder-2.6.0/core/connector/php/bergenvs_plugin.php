<?php

require_once(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/vendor/autoload.php');
$dotenv = new Dotenv\Dotenv(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))));
$dotenv->load();

define('BG_INC_PATH',dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/includes/');
require_once(BG_INC_PATH.'head.php');
require_once(BG_CLASS_PATH.'ckfinderplugin.php');

$bg18 = new ckFinderPlugin();
$dp0->prepareClassInstance($bg18);
$bg18->initialize();
