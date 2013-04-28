<?php

class Magentleman_Tasks_Config extends Magentleman_Tasks_Abstract {

	public function index() {
		$this->out("! You need to provide a task to complete within config:\n", "red");
		$this->out("  config:all  | Dump the whole configuration as a ruddy great XML file, useful for grep", "green");
		$this->out("  config:get  | Get a particular value from the config", "green");
		$this->out("  config:set  | Set a particular value in the config\n", "green");
	}

	/**
	 * Shows the whole XML string for the Magento config
	 *
	 * @return void
	 * 
	 **/
	public function _all() {

		$this->output(Mage::app()->getConfig()->getNode()->asNiceXml());
		exit;

	}

	/**
	 * Get a config value
	 *
	 * @return void
	 * 
	 **/
	public function _get($args) {

		$opts = array(
            'help|h'      => 'Displays usage information',
            'store|s=i'   => 'Specify a store (with ID)'
        );

        $opts = $this->consoleOpts($opts, 'config', 'get');

		if(!isset($args[1])) {
			$this->out("! You need to provide a key to get\n", "red");
			exit;
		}

		if(isset($opts->store)) {
			$config = Mage::getStoreConfig($args[1], $opts->store);
		} else {
			$config = Mage::getStoreConfig($args[1]);
		}

		if(!$config) {
			$this->out("! The key '" . $args[1] . "' doesn't seem to be defined\n", "red");
		} else {
			$this->output($config);
		}

		exit;


	}

	/**
	 * Set a config value
	 *
	 * @return void
	 * 
	 **/
	public function _set($args) {

		$opts = array(
            'help|h'      => 'Displays usage information',
            'store|s=i'   => 'Specify a store (with ID)'
        );

        $opts = $this->consoleOpts($opts, 'config', 'set');

		if(!isset($args[1])) {
			$this->out("! You need to provide a key to set\n", "red");
			exit;
		}

		if(!isset($args[2])) {
			$this->out("! You need to provide a new value\n", "red");
			exit;
		}

		if(isset($opts->store)) {
			$config = Mage::getStoreConfig($args[1], $opts->store); 
		} else {
			$config = Mage::getStoreConfig($args[1]);
		}

		if(is_array($config)) {
			$this->out("! This isn't a fully defined config path\n", "red");
			exit;
		}

		if(isset($opts->store)) {
			Mage::getModel('core/config')->saveConfig($args[1], $args[2], 'stores', $opts->store);
		} else {
			Mage::getModel('core/config')->saveConfig($args[1], $args[2]);
		}

		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();

		if(isset($opts->store)) {
			$config = Mage::getStoreConfig($args[1], $opts->store); 
		} else {
			$config = Mage::getStoreConfig($args[1]);
		}

		$this->out("'" . $args[1] . '\'', 'cyan', null, false);
		$this->out(' is now ', 'yellow', null, false);
		$this->output($config);

		exit;


	}

}