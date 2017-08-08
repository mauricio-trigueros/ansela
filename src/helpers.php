<?php

	/*
	 * Loads an array from a external php file.
	 * If $fileNamePath is wrong, or there are PHP syntax errors, dies.
	 * $fileNamePath needs to be like <?php $settings = array(); return json_encode($settings);
	 */
	function loadArrayFromPHP($fileNamePath){
		show(Loglevel::Debug, "Loading $fileNamePath ....");
		
		// Check that file exist
		if (!file_exists($fileNamePath))
			return showAndDie("Mandatory file $fileNamePath do not exist");
		
		// Validate PHP syntax
		$output = shell_exec("php -l '$fileNamePath' 2>/dev/null");
		$ok = 'No syntax errors detected in';
		if (!(substr($output, 0, strlen($ok)) === $ok))
			return showAndDie("Mandatory file $fileNamePath has wrong PHP syntax");
		// Load the file and convert it to PHP array
		
		$rawPHPFile = include($fileNamePath);
		// Check that the file can be imported, if file is just "HELLOOOO", it will pass "php -l",
		// but when we include it, it will be just empty, an integer with value "1"
		// The PHP file needs to return something, that is:
		// return json_encode($array);
		// otherwise the script will stop here
		if ($rawPHPFile === 1) showAndDie("Mandatory file $fileNamePath can not be PHP imported");
		
		// Check that PHP return is a proper JSON file, that we do not have just:
		// <?php return "HELLO WORLD";
		$rawFile = json_decode($rawPHPFile);
		if(is_null($rawFile)) showAndDie("Mandatory file $fileNamePath is not exporting a valid JSON object");
		
		// Check array to return
		$array = (array)($rawFile);
		if(empty($array)) showAndDie("Mandatory file $fileNamePath can not be converted to JSON");

		show(Loglevel::Debug, "File $fileNamePath loaded");
		return (array)($rawFile);
	}

	/* 
	 * Returns the path to project name passed as parameter (inside projects.php)
	 * If projectName do not exist, or path do not exist, it dies.
	 * Path is always returned ending in /
	 */
	function getProjectPath($projectName){
		show(Loglevel::Info, "Getting path for project $projectName ...");
		
		if(!array_key_exists($projectName, $GLOBALS['projects']))
			showAndDie("Project $projectName not found in projects array");
		
		$projectPath = $GLOBALS['projects'][$projectName];
		if(!file_exists($projectPath)) 
			showAndDie("Project $projectName has a path $projectPath that do not exist \n");

		# If last path character is not /, we add it.
		if(substr($projectPath, -1) != '/') $projectPath .= '/';

		show(Loglevel::Info, "Project $projectName has a valid path $projectPath");
		return $projectPath;
	}

	/*
	 * Return the environment settings for the environment name passed as parameter, that
	 * should be one define in $ENVIRONMENTS.
	 * If environment is wrong, or file has errors, then dies with an error.
	 */
	function getEnvSettings($environmentName, $projectPath){
		show(Loglevel::Debug, "Getting environment $environmentName for project $projectPath");
		$environments = getEnvironmentsForProjectPath($projectPath);

		# Check that environment name is valid
		if(!in_array($environmentName, $environments)){
			show(Loglevel::AppMessage, [
				"Envirnoment $environmentName is not valid.", 
				"Valid environments are:"
			]);
			show(Loglevel::AppList, $environments);
			die();
		}
		return loadArrayFromPHP($projectPath."settings_$environmentName.php");
	}

	/*
	 * Return the environments available for the project passed as parameter, in format
	 * ssfd
	 * It opens "tags.php" file and read the first level keys.
	 */
	function getEnvironmentsForProjectPath($projectPath){
		show(Loglevel::Info, "Getting environments for $projectPath");
		$tagsArray = loadArrayFromPHP($projectPath."tags.php");
		return (array_keys($tagsArray));
	}

	function getActionsForProjectPath($validEnvName, $validProjectPath){
		$tagsArray = loadArrayFromPHP($validProjectPath."tags.php");
		return array_keys((array)$tagsArray[$validEnvName]);
	}

	function getTagsForAction($environmentName, $projectPath, $actionName){
		$tagsArray = loadArrayFromPHP($projectPath."tags.php");
		$environmentArray = (array)$tagsArray[$environmentName];
		// Check that tags.php has action key, like tags.php["development"]["deploy"]
		// This could happen if you use --direct, where you input all parameters without checking
		if (!array_key_exists($actionName, $environmentArray)){
			show(Loglevel::AppError, "Action $actionName do not exist. Valid actions are:");
			show(Loglevel::AppList, array_keys($environmentArray));
			die();
		}
		return (array)$environmentArray[$actionName];
	}
