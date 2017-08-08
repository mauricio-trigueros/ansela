<?php
	
	/* 
	 * This file contains some helpers functions that you can use in settings_development.php, settings_staging.php ...
	 */

	/* 
	 * This function is meant to use inside another function calls, to get the path of the project we are using.
	 * We read debug_backtrace, and get the PHP who calls, for example:
	 * /Users/me/Deleteme/my-wordpress/settings_development.php, so we return then
	 * /Users/me/Deleteme/my-wordpress/
	 */
	function getProjectSettingsPath($debug_backtrace_object) {
		list(, $caller) = $debug_backtrace_object;
		return dirname($caller['args'][0]).'/';
	}

	/* 
	 * Returns the path of the project who calls the function.
	 * Useful to point files relative to project settings main folder, for example:
	 * 'nginx' => array(
	 *		'virtualhost' => projectPath().'nginx/wordpress'
	 *	)
	 */
	function projectPath() {
		return getProjectSettingsPath(debug_backtrace(false));
	}

	/*
	 * Return the path to the Vagrant SSH key, from the settings_ENV.php file where it is executed.
	 * An example of path can be: /Users/me/Deleteme/my-wordpress/.vagrant/machines/default/virtualbox/private_key
	 * For example:
	 *	'server' => array(
	 *		'sshkey' => vagrantKey()
	 *      .....
	 *	)
	 */
	function vagrantKey() {
		$projectPath = getProjectSettingsPath(debug_backtrace(false));
		$keyPath = $projectPath.'.vagrant/machines/default/'.$GLOBALS['userSettings']['virtualization'].'/private_key';
		return $keyPath;
	}

	function getKeyPath($keyName) {
		show(Loglevel::Info, "Getting SSH key path with name $keyName");
		$projectPath = getProjectSettingsPath(debug_backtrace(false));

		$sshProjectPath = $projectPath."ssh/".$keyName;
		show(Loglevel::Debug, "Checking if path $sshProjectPath exist...");
		if (file_exists($sshProjectPath)) {
			show(Loglevel::Debug, "SSH key $keyName found in $sshProjectPath");
			return $sshProjectPath;
		}

		$sshUserPath = $GLOBALS['userSettings']['ssh_folder'].$keyName;
		show(Loglevel::Debug, "Checking if path $sshUserPath exist...");
		if (file_exists($sshUserPath)) {
			show(Loglevel::Debug, "SSH key $keyName found in $sshUserPath");
			return $sshUserPath;
		}

		return showAndDie("No file found $keyName");
	}

	function getKeyContent($keyName){
		show(Loglevel::Info, "Getting SSH key content with name $keyName");
		$projectPath = getProjectSettingsPath(debug_backtrace(false));
		
		$sshProjectPath = $projectPath."ssh/".$keyName;
		show(Loglevel::Debug, "Checking if path $sshProjectPath exist...");
		if (file_exists($sshProjectPath)) {
			show(Loglevel::Debug, "SSH key $keyName found in $sshProjectPath");
			return file_get_contents($sshProjectPath);
		}
		
		$sshUserPath = $GLOBALS['userSettings']['ssh_folder'].$keyName;
		show(Loglevel::Debug, "Checking if path $sshUserPath exist...");
		if (file_exists($sshUserPath)) {
			show(Loglevel::Debug, "SSH key $keyName found in $sshUserPath");
			return file_get_contents($sshUserPath);
		}
		
		return showAndDie("No file found $keyName");
	}
