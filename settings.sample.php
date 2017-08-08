<?php
	$settings = array(
		// ssh_folder must end with "/"
		'ssh_folder' => '/Users/myself/.ssh/',
		// virtualization can be "virtualbox"
		'virtualization' => 'virtualbox'
	);

	return json_encode($settings);