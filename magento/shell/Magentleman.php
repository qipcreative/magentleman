<?php

require_once 'Magentleman/Tasks/Abstract.php';
require_once 'Magentleman/Tasks/Console.php';
require_once 'Magentleman/Tasks/Db.php';
require_once 'Magentleman/Tasks/Config.php';
require_once 'Magentleman/Tasks/Cache.php';

class Magentleman extends Magentleman_Tasks_Abstract {

	private $arguments;
	private $options;

	/**
	 * The constructor; let's get cracking!
	 *
	 * @return void
	 * 
	 **/
	public function __construct() {

		// Have to define our arguments as a global here
		global $argv;

		// Set everything up
		$this->setup();
		$this->addErrorHandler();

		// Parse the arguments to find out what we should be doing
		$this->parseArgs(array_slice($argv, 1));
		
		if(strpos($this->arguments[0], ':')) {

			$task = explode(':', $this->arguments[0]);
			$this->run($task[0], $task[1]);

		} else {

			$this->run($this->arguments[0]);

		}

	}

	

	/**
	 * Handle the initial setup, including profiling and bootstrapping the
	 * Magento environment
	 *
	 * @return void
	 * 
	 **/
	public function setup() {

		// We need to profiler to be enabled to get nice profiling information, duh!
		Varien_Profiler::enable();

		// Can't be bothered to look up what this does, 
		// but it sounds like the kind of thing which should be in here. 
		// We're developers aren't we!
		Mage::setIsDeveloperMode(true);

		// Bootstrap that environment! This does loads of
		// stuff and is pretty neccessary
		Mage::app('', 'store');
	}

	

	/**
	 * Sets up the error handler for Magentleman (errors should go straight
	 * to the console, but in red because everyone knows red means danger,
	 * that's just basic colour psychology)
	 *
	 * @return void
	 *  
	 **/
	private function addErrorHandler() {

		set_error_handler(array($this, 'error'));

	}

	/**
	 * The actual error handler, pretty basic, just outputs the message. The
	 * file and line variables are pretty pointless as it always just says the
	 * error occured within the eval. I'm gonna fix that at some point
	 *
	 * @return true
	 *  
	 **/
	public function error($number, $message, $file, $line) {

		$this->out("! " . $message, "red");
		return true;

	}

	public function run($task, $subtask=false) {

		// Create a callable version of the inputted task
		if($subtask) {
			$function = '_' . strtolower($subtask); 
		} else {
			$function = '_' . strtolower($task);
		}

		// Create the variable taskname
		$class = 'Magentleman_Tasks_' . $task;

		// Check to see if the Magentleman class ($this) has a function with the
		// same name as the task specified. If it does, run it! If not, give an error
		if(is_callable(array($class,  $function))) {
			$class = new $class();
			$class->$function($this->arguments);
		} elseif(is_callable(array($class, 'index'))) {
			$class = new $class();
			$class->index($this->arguments);
		} else {
			$this->out("! This is awkward. I can't find a task named " . $task . "\n", "red");
		}

	}

	/**
	 * Loop through the arguments (excluding the first one which is obviously always
	 * magentleman!) and parse them. Splits them into two arrays, one for options
	 * and one for tasks
	 *
	 * @param array $argv Not actually $argv itself but split from the constructor method
	 * 
	 * @return void
	 * 
	 **/
	public function parseArgs($argv) {

		$arguments = array();

		for ($i = 0, $count = count($argv); $i < $count; $i++)
		{
			$argument = $argv[$i];

			// If the CLI argument starts with a double hyphen, it is an option,
			// so we will extract the value and add it to the array of options
			// to be returned by the method.
			if (substr($argument, 0, 2) == '--')
			{
				// By default, we will assume the value of the options is true,
				// but if the option contains an equals sign, we will take the
				// value to the right of the equals sign as the value and
				// remove the value from the option key.
				list($key, $value) = array(substr($argument, 2), true);

				if (($equals = strpos($argument, '=')) !== false)
				{
					$key = substr($argument, 2, $equals - 2);

					$value = substr($argument, $equals + 1);
				}

				$this->options[$key] = $value;
			}
			// If the CLI argument does not start with a double hyphen it's
			// simply an argument to be passed to the console task so we'll
			// add it to the array of "regular" arguments.
			else
			{
				$this->arguments[] = $argument;
			}
		}

		if ( ! isset($this->arguments[0]))
		{
			$this->out("
   ______	 ,__ __                              _                               
   |    |	/|  |  |                            | |                              
   |    |	 |  |  |   __,   __,  _   _  _  _|_ | |  _   _  _  _    __,   _  _   
   |    |	 |  |  |  /  |  /  | |/  / |/ |  |  |/  |/  / |/ |/ |  /  |  / |/ |  
  _|____|_	 |  |  |_/\_/|_/\_/|/|__/  |  |_/|_/|__/|__/  |  |  |_/\_/|_/  |  |_/
 				  /|                                                 
 				  \|                                                 
", "white");
			$this->out("! I don't believe you specified a task. Please choose from one of the following:\n", "red");
			$this->out("  console | A charming interactive console for messing with Magento", "green");
			$this->out("  config  | Various tasks for interacting with the Magento config", "green");
			$this->out("  db      | Start an interactive MySQL prompt with your Magento connection details", "green");
			$this->out("  cache   | Perform various operations on the cache", "green");
			$this->out("");
			exit;
		}
		
	}

}