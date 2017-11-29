<?php

class migrateFromInitController extends AbstractController
{
    public function runStrategy()
    {
        $fname = Helper::get('savedir').'/schema.php';
        if (!file_exists($fname)) {
            echo "File: {$fname} does not exist!\n";
            die;
        }

        if ($this->_checkNeedInitDB() && $this->askForRewriteInformation()) {
            require_once $fname;
            $sc = new Schema();
            $sc->load(Helper::getDbObject());
        }

        global $cli_params;
        $controller = Helper::getController('migrate', $cli_params['command']['args']);
        $controller->runStrategy();
    }


    private function _checkNeedInitDB()
    {
        $db = Helper::getDbObject();
        $config = Helper::getConfig();

        // проверим существование бд, если нету попробуем создать
        if (!$db->query('SHOW DATABASES LIKE "' . $config['db'] . '"')->fetch_assoc()) {
            $db->query('CREATE DATABASE ' . $config['db'] . ' DEFAULT CHARACTER SET utf8');
            return true;
        }
        if ($db->query('SHOW TABLES FROM ' . $config['db'] . ' LIKE "' . $config['versiontable'] . '"')->fetch_assoc()) {
            return false;
        }
        return true;
    }


    public function askForRewriteInformation()
    {
        if (intval(Helper::get('forceyes'))) {
            return true;
        }
        if (intval(Helper::get('noninteractive'))) {
            die;
        }
        $c = '';
        do {
            if ($c != "\n") {
                echo 'Can I rewrite tables in database (all data will be lost) [y/n]? ';
                ob_flush();
            }
            $c = fread(STDIN, 1);
            if ($c === 'Y' or $c === 'y') {
                return true;
            }
            if ($c === 'N' or $c === 'n') {
                echo "\nSkip init db strategy\n";
                return false;
            }
        } while (true);
    }
}