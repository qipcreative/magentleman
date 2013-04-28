<?php

class Magentleman_Tasks_Db extends Magentleman_Tasks_Abstract {

	public function index() {

		$config  = Mage::getConfig()->getResourceConnectionConfig("default_setup");
        $_host = $config->host;
        $_uname = $config->username;
        $_pass = $config->password;
        $_dbname = $config->dbname;

        exec('mysql -h ' . $_host . ' -u ' . $_uname . ' -p' . $_pass . ' ' . $_dbname);

	}

}