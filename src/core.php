<?php	

	function addIpToAnsibleHosts($ip, $projectname){
		show(Loglevel::Info, "Checking if ip $ip is in ansible-hosts...");
		$filePath = getcwd().'/ansible-hosts';
		$ansibleHosts = file_get_contents($filePath);
		if (strpos($ansibleHosts, $ip) !== false) {
			show(Loglevel::Info, "IP $ip ($projectname) already included in ansible-hosts...");
		} else {
			show(Loglevel::Info, "IP $ip ($projectname) NOT included in ansible-hosts, including it...");
			$message = "\n\n# Project: $projectname---$ip\n[$projectname---$ip]\n$ip\n[$projectname---$ip:vars]\nansible_python_interpreter=/usr/bin/python2.7\n";
			file_put_contents($filePath, $message, FILE_APPEND | LOCK_EX);
		}
	}

	function executeAction(){
		show(Loglevel::AppMessage, ["Executing tags:"]);
		show(Loglevel::AppList, $GLOBALS['actionTags']);

		//TODO, if vagrant box, check that we run "vagrant up"
		//TODO check that machine is up and running

		addIpToAnsibleHosts($GLOBALS['environmentSettings']['project']->server->target, $GLOBALS['environmentSettings']['project']->name);

		foreach ($GLOBALS['actionTags'] as $tag) {
			show(Loglevel::AppMessage, ["Executing tag $tag"]);
			$executionResult = executeAnsibleCommand(
				$GLOBALS['environmentSettings']['project']->server->user,
				$GLOBALS['environmentSettings']['project']->server->sshkey,
				getcwd()."/playbooks/playbooks.yml",
				json_encode($GLOBALS['environmentSettings']),
				$tag,
				getcwd()."/ansible-hosts"
			);
			// Print output
			printAnsibleCommandExecution($executionResult);
		}
	}

	function executeAnsibleCommand($user, $key, $playbook, $extraVars, $tags, $hosts){
		$output = [];
		$askBecomePass = '';
		if($GLOBALS['environmentSettings']['project']->server->ask_sudo_pwd == 'true') $askBecomePass .= "--ask-become-pass";
		$command = "ansible-playbook -u $user --private-key='$key' --extra-vars='$extraVars' --tags $tags -i $hosts $playbook $askBecomePass";
		exec($command, $output);
		return $output;
	}	