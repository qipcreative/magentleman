<?php

class Magentleman_Tasks_Cache extends Magentleman_Tasks_Abstract {

	public function index() {
		$this->out("! You need to provide a task to complete within cache:\n", "red");
		$this->out("  cache:clear | Clear all caches", "green");
		$this->out("  cache:get   | Get a value from the cache", "green");
		$this->out("  cache:set   | Set a value in the cache", "green");
		$this->out("  cache:list  | List set values in the cache", "green");
		$this->out("  cache:tags  | Show available cache tags", "green");
		$this->out("");
	}

	public function _get($args) {
		$this->out($args[1] . ":");
		$output = Mage::app()->getCache()->load($args[1]);
		$this->output($output);
	}

	public function _set($args) {
		Mage::app()->getCache()->save($args[2], $args[1]);
		$this->out('Set "' . $args[1] . '" as "' . $args[2] . '"', 'green');
	}

	public function _list($args) {

		if(isset($args[1])) {
			$this->out("Matching tag: " . $args[1] . "\n", 'green');
			$this->output(Mage::app()->getCache()->getIdsMatchingTags(array($args[1])));
		} else {
			$this->out("Matching all tags:\n", 'green');
			$this->output(Mage::app()->getCache()->getIdsMatchingTags());
		}

		
		
	}

	public function _tags() {
		$cache = Mage::getModel('core/cache')->getTypes();
		foreach($cache as $type) {
			$this->out($type['tags'] . ": " . $type['description'], 'cyan');
		}
		$this->out("\nUse magentleman cache:list TAG_NAME to list set values for each tag", "green");
		$this->out("");
	}

	public function _clear() {
		Mage::getConfig()->init();

		$types = Mage::app()->getCacheInstance()->getTypes();

		try {
			$this->out("Cleaning data cache...\n", "yellow");
			flush();
			foreach ($types as $type => $data) {
				$this->out("Removing $type ... ", "cyan", null, false);
				Mage::app()->getCacheInstance()->clean($data["tags"]) ? $this->out("[OK]", 'green') : $this->out("[ERROR]", 'red');
			}
		} catch (exception $e) {
			die("[ERROR:" . $e->getMessage() . "]");
		}

		echo "\n";

		try {
			$this->out("Cleaning stored cache... ", "yellow", null, false);
			flush();
			Mage::app()->getCacheInstance()->clean() ? $this->out("[OK]", 'green') : $this->out("[ERROR]", 'red');
			echo "\n";
		} catch (exception $e) {
			die("[ERROR:" . $e->getMessage() . "]");
		}

		try {
			$this->out("Cleaning merged JS/CSS...", "yellow", null, false);
			flush();
			Mage::getModel('core/design_package')->cleanMergedJsCss();
			Mage::dispatchEvent('clean_media_cache_after');
			$this->out("[OK]\n", "green");
		} catch (Exception $e) {
			die("[ERROR:" . $e->getMessage() . "]");
		}

		try {
			$this->out("Cleaning image cache... ", "yellow", null, false);
			flush();
			Mage::getModel('catalog/product_image')->clearCache();
			$this->out("[OK]", "green");
		} catch (exception $e) {
			die("[ERROR:" . $e->getMessage() . "]");
		}
	}

}