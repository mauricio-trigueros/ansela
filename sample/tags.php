<?php 

	// Example of tags to install a Dev wordpress site.
	$install_wordpress = array (
		'system_set_locales',                 // Setting system locales
		'system_upgrade_packages',            // Update all pacakges to latest version
		'mysql-install-dev-server',           // First install the dev mysql server
		'mysql-server-create-user',           // Create MySQL user that Wordpress will use
		'mysql-server-create-db-with-user',   // Create MySQL table with previous user
		'php-install-php7-fpm',               // Installing PHP FPM (for Nginx)
		'wordpress-install-dependencies',     // Like php-mysql...
		'wordpress-download-wordpress',       // Download and unzip Wordpress
		'wordpress-add-wp-config',            // Add wp-config.php file
		'nginx_install_default',              // Install nginx
		'nginx_add_virtualhost',              // Add virtualhost
		'wordpress-install-wordpress'         // Perform Wordpress frontend installation
	);

	// Lets use a previous database, that also contains some external uploads folder
	// Populate wordpress database and uploads folder
	$wordpress_database_and_uploads = array(
		'mysql-install-client',               // Install MySQL client utilities
		'mysql-populate-db',                  // Populate database
		'system-soft_install_unzip',          // Install unzip (to unzip uploads.zip file)
		'wordpress-uploads'                   // Uploads the uploads zip file
	);

	// Example of file with settings
	$tags = array(
		'development'	=> array(
			'deploy' => array (
				'install-python-27',
				'install-nginx',
				'update-apt'
				),
			'reset-database' => array (
				'clean-db',
				'remove'
				)
		),
		'staging'	=> array(
			'deploy' => array (
				'install-python-27',
				'install-nginx',
				'update-apt'
				),
			'reset-database' => array (
				'clean-db',
				'remove'
				)
		),
		'production'	=> array(
			'deploy' => array (
				'install-python-27',
				'install-nginx',
				'update-apt'
				),
			'reset-database' => array (
				'clean-db',
				'remove'
				)
		)
	);

	return json_encode($tags);
	#return "LALAALAL"; -->