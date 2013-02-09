<?php

class Magentleman_Tasks_Console extends Magentleman_Tasks_Abstract {

	private $greeting = "Good day to you! How may I be of assistance?";
	private $profiling = false;
	private $database = false;

	public function index() {

		try {
		
			$opts = new Zend_Console_Getopt(
		        array(
		            'help|h'      => 'Displays usage information',
		            'profiling|p'   => 'Turn on Magento\'s profiler',
		            'database|d' 	=> 'Turn on profiling of db queries'
		        )
		    );

		    $opts->parse();

		} catch (Zend_Console_Getopt_Exception $e) {

		    $this->out('I do apologise! ' . $e->getMessage(), "red");
		    echo "\n" . str_replace("magentleman", "magentleman console", $e->getUsageMessage()) . "\n";
		    exit;

		}

		// Help action
		
	    if(isset($opts->help)) {

	    	$this->out("It's terribly simple to use me:", "green");
	    	echo "\n";
		    echo str_replace("magentleman", "magentleman console", $opts->getUsageMessage());
		    echo "\n";

		    exit;

		}

		if(isset($opts->profiling)) {

	    	$this->profiling = true;

		}

		if(isset($opts->database)) {

	    	$this->database = true;

		}

		$this->clear();
		$this->out("");
		$this->out(" " . $this->greeting, "green");
		$this->out(" To get help using this console, visit our website at http://www.qipcreative.com/magentleman\n", "yellow");
    	$this->startConsole();

	}

	/**
	 * The main loop
	 *
	 * @return void
	 * 
	 **/
	public function startConsole() {

		require_once 'ShallowParser.php';
		$parser = new Boris_ShallowParser();

		$buf = '';
		$prompt = '>> ';

		// Oh so infinite
		for (;;) {

			// Read the line from the CLI. If we haven't yet terminated the current statement,
			// add an asterisk to the prompt as a little visual hint
			
			$line = readline($buf == '' ? $prompt : str_pad('?> ', strlen($prompt), ' ', STR_PAD_LEFT));

			// Add to the history
			
			if($line == 'clear') {
				$this->clear();
				continue;
			}
			
			readline_add_history($line);

			if ($line === false) {
				break;
			}

			$buf .= $line;

			if($this->profiling) {
				Varien_Profiler::reset('test');
				Varien_Profiler::start('test');
			}

			if($this->database) {
				$res = Mage::getSingleton('core/resource')->getConnection('core_read');
				$profiler = $res->getProfiler();
				$profiler->setEnabled(true);
				$profiler->clear();
			}

			// This is some clever stuff from Boris. It parses the current command
			// and finds out what the heck is going on in there and if it's finished
			// yet.
			
			if ($statements = $parser->statements($buf)) {
				$buf = '';
				$fromglobalfunction = false;
				// Then try each statement
				foreach ($statements as $stmt) {
					try {

						// The exciting bit
						$result = eval($stmt);

						if($result !== 'fromglobalfunction') {
							$this->output($result);
						} else {
							$fromglobalfunction = true;
						}

					} catch (Exception $e) {

						$this->out("! " . $e->getMessage(), 'red');

						continue;
					}
				}

				if($this->profiling) {
					Varien_Profiler::stop('test');
				}
				
				if($this->database) {
					if($profiler->getEnabled()) {
						$totalTime    = $profiler->getTotalElapsedSecs();
						$queryCount   = $profiler->getTotalNumQueries();
						$longestTime  = 0;
						$longestQuery = null;
						if($queryCount) {
							foreach ($profiler->getQueryProfiles() as $query) {
								if ($query->getElapsedSecs() > $longestTime) {
									$longestTime  = $query->getElapsedSecs();
									$this->out($query->getQuery(), "yellow", null, false);
									$this->outTime($query->getElapsedSecs());
								}
							}
						}
					}
				}

				if($this->profiling && !$fromglobalfunction) {
					$this->outTime(Varien_Profiler::fetch('test'), "Duration: ");
				}


			}
		}
	}

}

// Global function definitions for use on the console. Pretty ugly but hey!


// Cooooool inspection function. Pretty handy
function i($object) {

	for(;;) {

		$abstract = new Magentleman_Tasks_Abstract();
		$abstract->drawHeader($object, "Overview");
		$abstract->out("\n Inspecting your object:\n", 'yellow');
		$abstract->out(" Type:     " . gettype($object), 'cyan');
		if(is_object($object)) {
			$abstract->out(" Class:    " . get_class($object), 'cyan');
			$abstract->getObjectOptions($object);
		}
		if(is_string($object)) {
			$abstract->out(" Length:   " . strlen($object), 'cyan');
			$abstract->out(" Content:  " . $object, 'cyan');
		}
		$abstract->out("");
		$exit = readline(" Do you want to carry on inspecting? (y/n) ");
		if($exit == 'n') break;

	}

	$abstract->clear();
	return 'fromglobalfunction';
}

function b($string) {
	$layout = Mage::app()->getLayout();
	$block = $layout->getBlockSingleton($string);
	return $block;
}

function h($string) {
	return Mage::helper($string);
}

function m($string, $id=null) {

	if($id) {

		return Mage::getModel($string)->load($id);

	}

	return Mage::getModel($string);
}