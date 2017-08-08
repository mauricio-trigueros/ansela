<?php
	function getProjectNameFromCMD(){
		show(Loglevel::AppMessage, "Select an available project:");
		show(Loglevel::AppList, array_keys($GLOBALS['projects']));
		$projectName = readline(" Write project name: ");
		if(array_key_exists($projectName, $GLOBALS['projects'])) {
			show(Loglevel::AppMessage, "Selected project $projectName");
			return $projectName;
		} else {
			show(Loglevel::AppError, "Project $projectName do not exist");
			return getProjectNameFromCMD($GLOBALS['projects']);
		}
	}

	function getEnvironmentFromCMD($projectPath){
		show(Loglevel::AppMessage, "Select enviroment to work:");
		$environments = getEnvironmentsForProjectPath($projectPath);
		show(Loglevel::AppList, $environments);
		$environmentName = readline("Write enviroment name: ");
		if(in_array($environmentName, $environments)) {
			show(Loglevel::AppMessage, "Selected enviroment $environmentName");
			return $environmentName;
		} else {
			show(Loglevel::AppError, "Enviroment $environmentName do not exist");
			return getEnvironmentFromCMD($projectPath);
		}
	}

	function getActionFromCMD($environmentName, $projectPath){
		$actions = getActionsForProjectPath($environmentName, $projectPath);
		show(Loglevel::AppList, $actions);
		$actionName = readline("Write action name: ");
		if(in_array($actionName, $actions)) {
			show(Loglevel::AppMessage, "Selected action $actionName");
			return $actionName;
		} else {
			show(Loglevel::AppError, "Action $actionName do not exist");
			return getActionFromCMD($environmentName, $projectPath);
		}
	}

