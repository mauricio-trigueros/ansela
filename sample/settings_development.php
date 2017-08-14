<?php 
	// Example of file with settings
	$settings = array(

		// this block is mandatory for any project
		'project' => array(
			// project name, same than in projects.php, to keep consistency
			// we use the name in several places along the deployment
			// for example, as nginx virtualhost name
			'name' => 'my-project',
			// project folder in the remote host (in local should match Vagrantfile)
			// in this place we will mount the code (Vagrant) or deploy (staging / production) the code
			'root' => '/var/www/my-project-running',
			// environment
			'environment' => 'development',
			// information related about how we connect to the server
			'server' => array(
				// the target is the server IP
				'target'	=> '192.168.1.1',
				'user'		=> 'ubuntu',
				// the previous user must be sudo, but it can ask the password every time we run "sudo..." or not.
				// If it ask for the password, then we need to send this password to Ansible, so set "ask_sudo_pwd" to 'true', and
				// when you run AnSeLa, Ansible will prompt "SUDO password:", then type the sudo password for that user.
				// If in remote system we do not need to type the password (we can run "sudo..." withot prompting password), then
				// set ask_sudo_pwd to 'false' (this could be the case of default user ubuntu, in Ubuntu Server LTS 16.04).
				// 'ask_sudo_pwd' is boolean, but needs to be written as 'true' or 'false' (or 'true' and something different than 'true' as false)
				'ask_sudo_pwd' => false,
				// ssh key path to connect to the server.
				// In Vagrant we can now the path with command "vagrant ssh-config", field IdentityFile
				// There is a shortcut function when you use Vagrant, just type vagrantKey()
				// For Staging or Production you can use getKeyPath('my-key.pem'), that will try to find the key 'my-key.pem' inside the SSH project folder,
				// and if it fails, then inside the user SSH folder (defined in ssh_folder, inside settings.php). If still fails, an exception will be launched.
				// Of course, you can set the absolute path as string.
				'sshkey' => '/path/to/my/key'
			)
		),

		'environment_settings' => array(
			// This is useful for some tags, for example in django_clear_cache or django_create_cache_table
			// we need to inject variable "PASSENGER_APP_ENV", so if we need to use these tags, we will need
			// to include this "PASSENGER_APP_ENV" variable
			'myvar-key' => 'myvar-value'
		),

		// Information related the the remote sever system
		'system' => array(
			'set_locales' => array(
				'name' 		=> 'en_US.UTF-8',
				'package' 	=> 'language-pack-en',
				'timezone'	=> 'Europe/Stockholm'
			),
			'add_new_users' => array(
				array(
					'username' => 'userman',
					// Password needs to be encrypted like that:
					// mkpasswd  -m sha-512 -s <<< xxxx
					// (where cleartext password is xxxx)
					'password' => '$6$BFX/J7X.....'
				)
			),
			'add_ssh_keys' => array(
				array(
					// user that will own the key (it will be under /home/user/.ssh/)
					'user' => 'userman',
					// name that we want to give to that key
					'ssh_key_name' => 'mykey.pub',
					// content of that particular key, we can paste it directly, like "-----BEGIN RSA PRIVATE KEY-----......"
					// or we can read the content with the helper method getKeyContent('mykey.pem'), that it will try to find the key 'mykey.pem' first
					// inside the SSH project folder, and then inside the user SSH folder (settings.php file). If the system can not find it, then dies.
					'ssh_key_content' => getKeyContent('mykey.pem'),
					// these fields (host and hostname) are not mandatory, just add the keys to the config file
					// this can be useful to match several ssh keys of different projects, all under github.com repository
					// host is the "virtual host" for each project (like api.github.com, admin.github.com.....)
					// hostname is the "real host", in that case it would be always "github.com"
					'host' => 'admin.bitbucket.org',
					'hostname' => 'bitbucket.org'
				)
			)
		),

		// Information related to system software
		'system_soft' => array(
			// Do not support MFA
			'msmtp' => array(
				'user' => 'mygmail@gmail.com',
				'pass' => 'mypassword'
			)
		),

		'mysql' => array(
			// Root credentials (usually when we create a dev server)
			'root' => array(
				'username' => 'root',
				'password' => 'testtest'
			),
			'user' => array(
				'username' => 'root',
				'password' => 'testtest'
			),
			'server' => array(
				'host' => '192.168.100.55',
				'port' => '3306',
				'db'   => 'mydatabasename'
			)
		),

		'postgresql'  => array(
			// Sometimes we need to connect to the database as root user
			// (for example in tag "postgresql_import_database", to drop current database)
			'root' => array(
				'username' => 'xxx',
				'password' => 'xxx'
			),
			'server' => array(
				'host' => '192.168.100.55',
				'port' => '5432',
				'db'   => 'mydatabasename'
			),
			// Username/Password that our app will use to connect to the server
			'user' => array(
				'username' => 'xxx', // use non capital letters
				'password' => 'xxx'
			),
			// If we need to restart the sequence of one table id, to fix issue 
			// "duplicate key value violates unique constraint", then write table name
			// in variable "restart_sequences" check playbooks/postgresql/tasks/sequence_restart.yml
			'restart_sequences' => array(
				'my_table_one',
				'my_table_two'
			),
			// File to read with tag "postgresql_import_database"
			'dump_to_import' => projectPath().'/dumps/my-file.sql',
			// Folder to write with tag "postgresql_export_database", file name will be
			// yyy-mm-dd:HH:MM:SS.sql
			'dump_folder' => projectPath().'/dumps'
		),

		'nodejs' => array(
			// node_0.10, node_0.12, node_4.x, node_5.x, etc...
			'version' => 'node_5.x'
		),

		'deployment' => array (
			// path in remote server where we will clone the project,
			// it should finish with "/"
			'local_path' => '/tmp/myproject',
			// we can update from local, for example, we can zip the dist folder in our project root, 
			// and use action "deployment_fromlocal" to deploy from our local machine
			'fromlocal_zip' => projectPath().'dist.zip',
			// rollback related settings, if something goes wrong, we can restore previous releases.
			// every time that we release, we zip current production folder into a export_PROJECTNAME_DATE.tar.gz file
			'rollback' => array(
				// path, in remote server (ending in / ), where we will keep the tar.gz files
				'path' => '/home/mybackups/rollback/',
				// number of archives files to keep. Should be an integer, the archive files we want plus one.
				// copies = 2 -> we have production, and ONE copy of production
				// copies = 3 -> we have production, and TWO latest archives
				'copies' => 3
			),			
			// folder from our repo that we need to move to project.root
			// for example "dist", if we compile our project into a "dist" folder, we want to move to production folder ONLY
			// the "dist" folder. In the same way, if we want to deploy everything, then we must set "build_folder" to '""'
			'build_folder' => '""',
			// information related to the git clone and checkout process
			'checkout' => array (
				// remote system user that we will execute git clone
				'user' => 'ubuntu',
				// key to use (assuming that the repository is private): /home/ubuntu/.ssh/ubuntu_key.pem
				// note that this path and this key is placed in the remote server.
				'ssh_key_name' => 'ubuntu_key.pem',
				// content for that key
				'ssh_key_content' => getKeyContent('ubuntu_key.pem')
			),
			// owner/permissions to set to project.root once the deployment finish
			'permissions' => array (
				'owner' => 'www-data',
				'group' => 'www-data',
				'mode' => '0755'
			),
			'hosting' => array (
				'provider' 	=> 'github.com',
				'user' 		 	=> 'user',
				'repo' 			=> 'myrepo',
				'branch' 		=> 'dev'
			),
			// files/folders to remove, when the deployment is finish
			// usually "_deployment", "Readme.md"....
			'to_remove' => array ( '_deployment')
		),

		'nginx' => array(
			// Full path in our computer to the nginx virtual host
			// usually we have a nginx folder in our _deployment, so we can use
			// projectPath().'nginx/wordpress'
			'virtualhost' => projectPath().'nginx/wordpress',
			// Full path in our computer to the nginx passenger template
			// usually in our nginx folder in our _deployment, so we can use
			// projectPath().'nginx/wordpress'
			'passenger_wsgi_template' => '/path/to/template'
		),

		'wordpress' => array(
			'download' => array(
				'url' => 'https://wordpress.org/wordpress-4.8.tar.gz',
				//sha256 of the previous file
				'sha256sum' => '39210d593700dc26c58a53b38172be63ea3da67020d80bb2cf34b396b732dd4d'
			),
			// information that you enter on fres wordpress installations
			'install' => array(
				// server path where worpdress files reside
				'path' => '/var/www/wordpress/',
				// blogs URL (www.mysite.com, 192.168.1.1 ...)
				'target' => '192.168.1.1',
				// pay attention to the format standard: en_GB, es_ES ...
				// https://make.wordpress.org/polyglots/teams/
				'language' => 'en_GB',
				'title' => 'My Patata Blog',
				'table_prefix' => 'wp_'
			),
			// Admin credentials
			'admin' => array(
				'name' => 'Patata',
				'password' => 'PatataPass',
				'email' => 'mauricio@houseofradon.com'
			),
			'permission' => array(
				'user' => 'www-data',
				'group' => 'www-data'
			),
			// plugins that we want to install when we trigger tag "wordpress-install-plugins"
			// the full url looks like: http://svn.wp-plugins.org/advanced-custom-fields/tags/4.4.9/
			// so we build it like http://svn.wp-plugins.org/NAME/VERSION, if there is no version, leave it 
			// as empty string
			'plugins' => array(
				array( 'name' => 'advanced-custom-fields', 'version' => 'tags/4.4.9' ),
				array( 'name' => 'tinymce-advanced', 'version' => 'tags/4.1.7' ),
				array( 'name' => 'w3-total-cache', 'version' => 'tags/0.9.4.1' ),
				array( 'name' => 'global-settings', 'version' => '' )
			),
			// Uploads folder. 
			'uploads' => array(
				// Folder where we place all the export. The name syntax is PROJECT.NAME_updates_DATE
				'export_folder' => projectPath().'uploads',
				// File to import. It needs to have at root level all the folder years "2017", "2016"...
				'import_file' => projectPath().'uploads/my-project_2017-08-14--12-09-36.zip'
			)
		)

	);

	return json_encode($settings);