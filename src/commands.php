<?php

	function commandViewTags(){
		show(Loglevel::AppList, getAnselaTags());
	}

	function commandViewPlaybooks() {
		show(Loglevel::AppMessage, "Showing the playbooks available...");
		$playbooksDirContent = preg_grep('/^([^.])/', scandir('./playbooks'));
		// We want only folders inside "playbooks", we want to skip "playbooks.retry" for example
		foreach($playbooksDirContent as $key => $value) {
			if(!is_dir("./playbooks/$value")) unset($playbooksDirContent[$key]);
		}
		show(Loglevel::AppList, $playbooksDirContent);
	}

	function commandAssistant() {
		$projectName = getProjectNameFromCMD();
		$projectPath = getProjectPath($projectName);
		$environmentName = getEnvironmentFromCMD($projectPath);
		$actionName = getActionFromCMD($environmentName, $projectPath);
		commandDirect($projectName, $environmentName, $actionName);
	}

	function commandViewPlaybookVariables($playbookName) {
		show(Loglevel::AppMessage, "Showing the playbooks variables for variable $playbookName");
		if(!file_exists($GLOBALS['path']."/playbooks/$playbookName")) {
			showAndDie("Directory do not exist! playbook name $playbookName do not exist!");
		}
		$variables = [];
		$playBookTaskFolder = $GLOBALS['path']."/playbooks/$playbookName/";
		// When iterating the directoy, we need to ignore files starting with "."
		$playbookRoot = preg_grep('/^([^.])/', scandir($playBookTaskFolder));
		foreach($playbookRoot as $subfolder){
			foreach(preg_grep('/^([^.])/', scandir("$playBookTaskFolder/$subfolder")) as $file) {
				$fileContent = file_get_contents("$playBookTaskFolder/$subfolder/$file");
				// Regex to get all content between double curly braces (ansible variables)
				preg_match_all('/{{(.*?)}}/', $fileContent, $matches);
				// Matches is one array containing two keys: 
				//  [0]: contains all variables like "{{ mongo.server.host }}"
				//  [1]: contains all variables like " mongo.server.host "
				// So we take $matches[1], but removing first and last white space
				$variables = array_merge($variables, array_map('trim', $matches[1]));
			}
		} 
		// $variables will contain repeated values, we need to remove duplicates
		$uniqueVariables = array_unique($variables);
		// And variables that are not interested (like item for loops, but also item.regexp, item.newline...)
		foreach($uniqueVariables as $key => $value) {
			if(preg_match('/^item(.*)/i', $value)) {
				unset($uniqueVariables[$key]);
			}
		}
		$uniqueVariables = array_diff($uniqueVariables, array('item'));
		show(Loglevel::AppList, $uniqueVariables);
	}

	function commandDirect($projectName, $environmentName, $actionName) {
		$projectPath = getProjectPath($projectName);
		$GLOBALS['environmentSettings'] = getEnvSettings($environmentName, $projectPath);
		$GLOBALS['actionTags'] = getTagsForAction($environmentName, $projectPath, $actionName);
		return executeAction();
	}
