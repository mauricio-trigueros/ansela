<?php 

	/*
	 * php ansela.php --direct PROJECT ENVIRONMENT ACTION
	 * php ansela.php --assistant
	 */
	include './src/helpers.php';
	include './src/settings-helpers.php';
	include './src/core.php';
	include './src/print.php';
	include './src/cmd.php';
	include './src/commands.php';

	abstract class Loglevel{
		const Trace = 0;
		const Debug = 1;
		const Info  = 2;
		const Warn  = 3;
		const Error = 4;
		const Fatal = 5;
		const AppMessage   = 8;
		const AppList = 9;
		const AppError = 10;
	}

	// Set global log level
	$GLOBALS['logLevel'] = Loglevel::Trace;
	$GLOBALS['path'] = getcwd();
	
	show(Loglevel::Trace, "Starting...");

	/* 
	 * First we need to load project settings and user settings.
	 * - Project settings (projects.php) is a key value array which connects project names 
	 *   with _deployment projects folder ('myproject' => '/Users/me/projects/myproject/_deployment')
	 * - User settings (settings.php) is a key value array which holds particular information for that user, 
	 *   like path to SSH folder, virtualization type (vagrant or vmware, etc)
	 * Both arrays are placed in $GLOBALS array, so they can be used anywhere.
	 */ 
	$GLOBALS['projects'] = loadArrayFromPHP('projects.php');
	$GLOBALS['userSettings'] = loadArrayFromPHP('settings.php');

	// Take decision based on number of parameters
	switch (count($argv)) {
		case 2:
			switch($argv[1]) {
				case '--assistant':     return commandAssistant();
				case 'view-tags':       return commandViewTags();
				case 'view-playbooks':  return commandViewPlaybooks();
				default:                return showUsageForBadParameters();
			}
		case 3:
			switch($argv[1]) {
				case 'view-playbook-variables': return commandViewPlaybookVariables($argv[2]);
				default:                        return showUsageForBadParameters();
			}
		case 5:
			switch($argv[1]) {
				case '--direct':   return commandDirect($argv[2], $argv[3], $argv[4]);
				default:           return showUsageForBadParameters();
			}
		default:
			return showUsageForBadParameters();
	}



