#!/usr/bin/env php
<?php
require_once __DIR__.'/init.php';
$cli_params = Helper::parseCommandLineArgs($argv);
if (empty($cli_params['options']['config'])) {
    $cli_params['options']['config'] = __DIR__.DIRECTORY_SEPARATOR.'config.ini';
    if (!is_file($cli_params['options']['config'])) {
        $cli_params['options']['config'] = getcwd().DIRECTORY_SEPARATOR.'config.ini';
    }
    if (!is_file($cli_params['options']['config'])) {
        $cli_params['options']['config'] = getcwd().DIRECTORY_SEPARATOR.'/config/config.ini';
    }
}
$config = array();
if (file_exists($cli_params['options']['config'])) {
    $config = parse_ini_file($cli_params['options']['config']);
}
$config = array_replace($config, $cli_params['options']);

//command line overrides everything
Helper::setConfig($config);
if (!Helper::checkConfigEnough()) {
    Output::error('mmp: could not find config file "'.$cli_params['options']['config'].'"');
    die(1);
}
$controller = Helper::getController($cli_params['command']['name'], $cli_params['command']['args']);
if ($controller !== false) {
    try {
        $db = false;
        $db = Helper::getDbObject();
        $db->begin_transaction();
        $controller->runStrategy();
    } catch (Exception $e) {
        if (!empty($msg = $e->getMessage())) {
            Output::verbose('[31m' . $msg . '[37m');
        }
        if ($e->getCode() !== 0xffff && $db) {
            $db->rollback();
        }
        die(1);
    }
} else {
    Output::error('mmp: unknown command "'.$cli_params['command']['name'].'"');
    Helper::getController('help')->runStrategy();
    die(1);
}