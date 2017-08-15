<?php
	
	/*
	 * Send as parameter an array with the output of the Ansible command execution.
	 * It is an array like:
	 *	array(68) {
	 *	  [0]=>
	 *	  string(0) ""
	 *	  [1]=>
	 *	  string(113) "PLAY [52.90.103.75] ********************"
	 *	  [2]=>
	 *	  string(0) ""
	 *	  [3]=>
	 *	  string(113) "TASK [deployment : Deployment ini | Installing git and APT dependecies]  ********************"
	 *    [4]=>
	 *	  string(45) "ok: [52.90.103.75] => (item=[u'git', u'acl'])"
	 *	  [5]=>
	 *	  string(0) ""
	 *	  [6]=>
	 *	  string(113) "TASK [deployment : Adding SSH deployment keys | Verifying that SSH folder exist] ********************"
	 *	  [7]=>
	 *	  string(18) "ok: [52.90.103.75]"
	 *	  ...
	 */
	function printAnsibleCommandExecution($outputArray){
		foreach ($outputArray as $index => $line) {

			// Skip empty lines
			if(!strlen($line)) continue;

			// Remove long strings "**************..." and trim spaces
			$line = trim(str_replace('*', '', $line));

			// Skip useless lines, like "PLAY [192.168.100.50]" and "PLAY RECAP "
			if(substr( $line, 0, strlen("PLAY ")) === "PLAY "){ 
				show(Loglevel::Trace, $line);
				continue;
			}

			// At this point, we have these kind of lines:
			// Lines showing task:
			//      TASK [wordpress : Download Wordpress to a temporal folder]
			// Result of the task
			//      ok: [192.168.100.50]
			//      fatal: [192.168.100.50]: FAILED! => {"changed": fals....
			//      to retry, use: --limit @/Users/maus/Projects/new_deployer/playbooks/pla
			// Global result of tag execution:
			//      192.168.100.50             : ok=1    changed=0    unreachable=0    failed=1
			// Debug messages, like:
			//      "msg": "Temporal filename ->788272323.sql<-"
			if(substr( $line, 0, strlen("fatal: ")) === "fatal: ") {
				showAndDie(["Error executing ansible task!!", $line]);
			}

			if(substr( $line, 0, strlen("TASK ")) === "TASK ") {
				show(Loglevel::Debug, $line);
				continue;
			}

			if ((substr( $line, 0, strlen("ok: ")) === "ok: ") || 
				(substr( $line, 0, strlen("changed: ")) === "changed: ")) {
				// We could print the result of this tag execution, but it is a wast of line and spaces
				// show(Loglevel::AnsibleResult, "done");
				show(Loglevel::Trace, $line);
				continue;
			}



			if(substr( $line, 0, strlen("\"msg\":")) === ("\"msg\":")) {
				// From "msg": "Temporal filename ->788272323.sql<-" we just want to return only
				// Temporal filename ->788272323.sql<- (without ")
				$message = substr(trim(explode(':',$line)[1]), 1, -1);
				show(Loglevel::Info, "DEBUG MESSAGE: $message");
				continue;
			}

			// Show global result of tag action line:
			$serverIp = $GLOBALS['environmentSettings']['project']->server->target;
			if(substr( $line, 0, strlen($serverIp)) === $serverIp) {
				// Line will look like:
				// 192.168.100.50             : ok=1    changed=0    unreachable=0    failed=0
				show(Loglevel::Trace, $line);
				$result = explode(':',$line)[1];
				show(Loglevel::Info, "RESULT: $result");
				continue;
			}
		}
	}

	function show($logLevel, $arrayMessages) {
		// If $arrayMessages is not an array, embed it into one array.
		if(!is_array($arrayMessages)) $arrayMessages = [$arrayMessages];

		switch ($logLevel) {
			// Ansible full command info....
			case Loglevel::Trace:
				$preBlock = "";
				$postBlock = "";
				$preLine = "//TRACE//";
				$postLine = "\n";
				break;
			case Loglevel::Debug:
				$preBlock = "";
				$postBlock = "";
				$preLine = "//DEBUG//";
				$postLine = "\n";
				break;
			case Loglevel::Info:
				$preBlock = "";
				$postBlock = "";
				$preLine = "//INFO///";
				$postLine = "\n";
				break;
			case Loglevel::Warn:
				die("NOT IMPLEMENTED WARN");
				break;
			// App errors, like enter a wrong project name with --assistant
			case Loglevel::Error:
				die("NOT IMPLEMENTED EROR");
				break;
			case Loglevel::Fatal:
				$preBlock = "";
				$postBlock = "////// FINISHED ////////\n";
				$preLine = "//FATAL//";
				$postLine = "\n";
				break;
			case Loglevel::AppMessage:
				$preBlock = "/////////\n";
				$postBlock = "/////////\n";
				$preLine = "/////////";
				$postLine = "\n";
				break;
			case Loglevel::AppList:
				$preBlock = "";
				$postBlock = "/////////\n";
				$preLine = "///////// --->";
				$postLine = "\n";
				break;
			case Loglevel::AppError:
				$preBlock = "";
				$postBlock = "";
				$preLine = "///////// OOOPPPSSSSS";
				$postLine = "\n";
				break;
		}
		
		// Only print if we are in the same logLevel or higher
		if($logLevel >= $GLOBALS['logLevel']) {
			echo $preBlock;
			foreach ($arrayMessages as $key => $value) {
				echo "$preLine $value $postLine";
			}
			echo $postBlock;
		}
	}

	function showAndDie($message){
		show(Loglevel::Fatal, $message);
		die();
	}

	function showUsageForBadParameters(){
		show(Loglevel::AppMessage, "Bad command line parameters");
		show(Loglevel::AppList, [
			"php ansela.php view-tags", 
			"php ansela.php view-playbooks",
			"php ansela.php view-playbook-variables PLAYBOOK-NAME", 
			"php ansela.php --assistant",
			"php ansela.php --direct PROJECT ENVIRONMENT ACTION"
		]);
	}