<?php 

class Magentleman_Tasks_Abstract
{

	// Found this somewhere random on the web; think it might have been StackOverflow
	private $foreground_colors = array(
		'black' => '0;30',
		'dark_gray' => '1;30',
		'blue' => '0;34',
		'light_blue' => '1;34',
		'green' => '0;32',
		'light_green' => '1;32',
		'cyan' => '0;36',
		'light_cyan' => '1;36',
		'red' => '0;31',
		'light_red' => '1;31',
		'purple' => '0;35',
		'light_purple' => '1;35',
		'brown' => '0;33',
		'yellow' => '1;33',
		'light_gray' => '0;37',
		'white' => '1;37'
		);

	private $background_colors = array(
		'black' => '40',
		'red' => '41',
		'green' => '42',
		'yellow' => '43',
		'blue' => '44',
		'magenta' => '45',
		'cyan' => '46',
		'light_gray' => '47'
		);

	private $publicity = array("exit", "public", "protected", "private", "all");
	private $inherited = array("exit", true, false);

	/**
	 * Nicely formatted output for us!
	 *
	 * @return void
	 * 
	 **/
	public function output($object) {
		//Zend_Debug::dump($object);
		$this->out("=> ", null, null, false);
		if (is_object($object)) {
			if(method_exists($object, 'toJson')) {
				if($object->toJson() == '[]') {
					$this->out("Blank object of type " . get_class($object), "cyan");
				} else {
					$this->out($this->indent($object->toJson()), "cyan") . "\n";
				}
			}
			elseif(method_exists($object, 'toArray')) { 
				$this->out(print_r($object->toArray(), true), "cyan") . "\n";
			} else {
				$this->out(Zend_Debug::dump($object, "", false), "cyan") . "\n";
			}
		} elseif(is_array($object)) {
			$this->out($this->indent(json_encode($object)), "cyan") . "\n";
		} else {
			$this->out(var_export($object, true), "cyan") . "\n";
		}
	}

	/**
	 * Indents a flat JSON string to make it more human-readable. Ripped
	 * this off from somewhere; again, probably StackOverflow
	 *
	 * @param string $json The original JSON string to process.
	 *
	 * @return string Indented version of the original JSON string.
	 **/
	private function indent($json) {

		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {

	        // Grab the next character in the string.
			$char = substr($json, $i, 1);

	        // Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;

	        // If this character is the end of an element, 
	        // output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}

	        // Add the character to the result string.
			$result .= $char;

	        // If the last character was the beginning of an element, 
	        // output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}

	/**
	 * Nice Time output, basically just takes a floating value of
	 * time in seconds (which looks butters) and makes it look nice
	 * (and green)
	 *
	 * @param float $duration The original value of time
	 * @param string $message An optional message to prepend
	 *
	 * @return void
	 **/
	public function outTime($duration, $message = false) {
		
		$precision = 1;
		$time = round($duration * 1000, $precision);

		if($message) {
			$this->out($message . ' [' . $time . 'ms]', 'green');
		} else {
			$this->out('[' . $time . 'ms]', 'green');
		}

	}

	/**
	 * Returns coloured string, pretty handy on the console
	 *
	 * @param string $string The string to output
	 * @param string $foreground The name of the colour for the foreground
	 * @param string $background The name of the colour for the background
	 * 
	 * @return void
	 * 
	 **/
	public function out($string, $foreground = null, $background = null, $linebreak=true) {
		$colored_string = "";

  		// Check if given foreground color found
		if (isset($this->foreground_colors[$foreground])) {
			$colored_string .= "\033[" . $this->foreground_colors[$foreground] . "m";
		}

  		// Check if given background color found
		if (isset($this->background_colors[$background])) {
			$colored_string .= "\033[" . $this->background_colors[$background] . "m";
		}

  		// Add string and end coloring
		$colored_string .=  $string . "\033[0m";

		if($linebreak) {
			echo $colored_string . "\n";
		} else {
			echo $colored_string;
		}

	}

	public function consoleOpts($opts, $first, $second) {

		try {

			$opts = new Zend_Console_Getopt($opts);
		    $opts->parse();

		} catch (Zend_Console_Getopt_Exception $e) {

		    $this->out('I do apologise! ' . $e->getMessage(), "red");
		    if($second) {
		    	echo "\n" . str_replace("magentleman", "magentleman " . $first . ':' . $second, $e->getUsageMessage()) . "\n";
		    } else {
		    	echo "\n" . str_replace("magentleman", "magentleman " . $first, $e->getUsageMessage()) . "\n";
		    }
		    exit;

		}

		if(isset($opts->help)) {

	    	$this->out("It's terribly simple to use me:", "green");
		    if($second) {
		    	echo "\n" . str_replace("magentleman", "magentleman " . $first . ':' . $second, $opts->getUsageMessage()) . "\n";
		    } else {
		    	echo "\n" . str_replace("magentleman", "magentleman " . $first, $opts->getUsageMessage()) . "\n";
		    }

		    exit;

		}

		return $opts;

	}

	public function clear() {
		system("clear");
	}

	public function getObjectOptions($object) {
		$this->out("");
		$this->out(" 1) Show methods", 'green');
		$this->out(" 2) Show properties", 'green');
		$this->out(" 3) Show inheritance", 'green');
		$this->out(" 4) Method details (search methods)", 'green');
		$this->out(" 5) Export as array, Json or XML", "green");
		$this->out("");
		$option = readline(' Choose an option: ');

		switch ($option) {
			case '1':
				$this->drawHeader($object, "View Methods");
				$this->out("");
				$this->out(" Which methods do you want to see?", 'yellow');
				$this->out("", 'green');
				$this->out(" 1) Public", 'green');
				$this->out(" 2) Protected", 'green');
				$this->out(" 3) Private", 'green');
				$this->out(" 4) All", 'green');
				$this->out("");

				$public = readline(' Choose an option:  ');
				$public = $this->publicity[$public];

				$this->drawHeader($object, "View Methods");
				$this->out("");
				$this->out(" Just methods on this class or inherited as well?", 'yellow');
				$this->out("", 'green');
				$this->out(" 1) Just this class", 'green');
				$this->out(" 2) Inherited as well please", 'green');
				$this->out("");

				$inherited = readline(' Choose an option:  ');
				$inherited = $this->inherited[$inherited];

				$this->drawHeader($object, "Methods");
				$this->out("");
				$this->listMethods($object, $inherited, $public);

				break;

			case '2':
				$this->drawHeader($object, "Properties");
				$this->out("");
				$this->out("All Properties:\n");
				$this->listProperties($object);
				break;

			case '3':
				$this->drawHeader($object, "Inheritance");
				$this->out("");
				$this->out(" Inheritance for " . get_class($object), "yellow");
				$this->listLineage($object);
				$this->out("");
				break;

			case '4':
				$this->drawHeader($object, "Method Search");
				$this->out("");
				$this->out(" What are you searching for?", 'yellow');
				$this->out("");
				$methodname = readline(' Search methods:  ');
				$this->drawHeader($object, "Results for " . $methodname);
				$this->out("");
				$this->out(" Search results for '" . $methodname . "'", "green");
				$this->searchMethods($object, $methodname);
				break;

			case '5':
				$this->drawHeader($object, "Choose Format");
				$this->out("");
				$this->out(" Which format would you like?", 'yellow');
				$this->out("");
				$this->out(" 1) Array", 'green');
				$this->out(" 2) Json", 'green');
				$this->out(" 3) Serialized", 'green');
				$this->out(" 4) XML", 'green');
				$this->out("");
				$format = readline(' Choose an option:  ');
				$this->drawHeader($object, "Export");
				$this->out("");
				$this->formatObject($object, $format);
			
			default:
				# code...
				break;
		}
	}

	public function drawHeader($object, $panel) {
		$width = exec('tput cols');

		if($object) {
			$heading = " Inspecting object " . get_class($object) . " - " . $panel;
		} else {
			$heading = " " . $panel;
		}
		$width = $width - strlen($heading);

		$padding = '';
		for ($i=0; $i < $width; $i++) { 
			$padding .= ' ';
		}
		$this->clear();
		
		$this->out($heading . $padding, "white", "red");
	}

	public function formatObject($object, $format) {

		switch ($format) {
			case '1':
				$this->out(var_export($object->toArray()), "cyan");
				break;

			case '2':
				$this->out($object->toJson(), "cyan");
				break;

			case '3':
				$this->out(serialize($object->toArray()), "cyan");
				break;

			case '4':
				$this->out($object->toXml(), "cyan");
				break;
			
		}

	}

	public function searchMethods($object, $methodname) {

		$reflection = new ReflectionClass($object);
		$methods = $reflection->getMethods();

		$results = array();

		foreach($methods as $method) {
			if(strpos(strtolower($method->name), strtolower($methodname))) {
				$results[] = $method;
			}
		}

		if(count($results)) {
			$this->out("");
			$counter = 0;
			foreach($results as $result) {

				$counter++;

				$this->out(" " . $counter . ')  ' . $result->name . "()", 'cyan');

				if(count($result->getParameters())) {
					$this->out('     ', null, null, false);
					foreach($result->getParameters() as $param) {
						$this->out($param->name . ', ', 'blue', null, false);
					}	
					$this->out("");
				}

				if($result->class !== get_class($object)) {
					$this->out("     (defined in " . $result->class . ")", "purple");
				}

			}
			$this->out("");
			$which = readline(" Which method did you want? ");

			$this->drawHeader($object, "Method " . $results[$which - 1]->name);
			$this->out("");
			$this->showMethod($results[$which - 1]);

		} else {
			$this->out("! No methods found\n", "red");
		}

	}

	public function showMethod($method) {

		$filename = $method->getFileName();
		$start_line = $method->getStartLine() - 1; // it's actually - 1, otherwise you wont get the function() block
		$end_line = $method->getEndLine();
		$length = $end_line - $start_line;

		$source = file($filename);
		$body = implode("", array_slice($source, $start_line, $length));
		$this->out(" Method:   " . $method->name, "cyan");

		if($count = count($method->getParameters())) {

			$this->out(" Params:   ", "cyan", null, false);
		
			$counter = 1;
			foreach($method->getParameters() as $param) {
				if($counter == $count) {
					$this->out($param->name, 'blue', null, false);
				} else {
					$this->out($param->name . ', ', 'blue', null, false);
				}

				$counter++;
			}	
			$this->out("");
		}

		$this->out(" Class:    " . $method->class, "cyan");
		$this->out(" File:     " . $method->getFileName(), "cyan");
		$this->out(" Line:     " . $start_line, "cyan");
		$this->out("");
		$this->out($body, "yellow");

	}

	public function listLineage($object) {

		$class = new ReflectionClass($object);

		$lineage = array();
		$this->out("");
		$this->out(" " . $class->getName(), "cyan", null, false);
		while ($class = $class->getParentClass()) {
			$this->out(" => ", "yellow", null, false);
			$this->out($class->getName(), "cyan", null, false);
				
		}

	}

	public function listProperties($object) {

		$reflection = new ReflectionClass($object);
		$properties = $reflection->getProperties();

		if(count($properties)) {

			foreach($properties as $property) {

				$this->out('  ' . $property->name, 'cyan', null, false);

				if($property->class !== get_class($object)) {
					$this->out(" (defined in " . $property->class . ")", "purple");
				} else {
					$this->out("");
				}

				$this->out("");

			}

		} else {

			$this->out("! No properties exist for this object", "red");

		}
	}

	protected function _getMethod($public) {

		switch ($public) {
			case 'protected':
				$method = ReflectionMethod::IS_PROTECTED;
				break;

			case 'private':
				$method = ReflectionMethod::IS_PRIVATE;
				break;

			case 'private':
				$method = ReflectionMethod::IS_PUBLIC;
				break;
			
			default:
				$method = false;
				break;
		}

		return $method;
	}

	public function listMethods($object, $justthis=false, $public) {

		$reflection = new ReflectionClass($object);

		$constant = $this->_getMethod($public);

		if($constant) { 
			$methods = $reflection->getMethods($constant);
		} else {
			$methods = $reflection->getMethods();
		}

		if(count($methods)) {

			foreach($methods as $method) {

				if($justthis && $method->class !== get_class($object)) continue;

				$this->out('  ' . $method->name . "()", 'cyan');

				if($count = count($method->getParameters())) {
					$counter = 1;
					$this->out("      ", null, null, false);
					foreach($method->getParameters() as $param) {
						if($counter == $count) {
							$this->out($param->name, 'blue', null, false);
						} else {
							$this->out($param->name . ', ', 'blue', null, false);
						}

						$counter++;
					}	
					$this->out("");
				}

				if($method->class !== get_class($object)) {
					$this->out("     (defined in " . $method->class . ")", "purple");
				}

				$this->out("");

			}

		} else {

			$this->out("! No " . $public . " methods exist for this object", "red");

		}
	}
}