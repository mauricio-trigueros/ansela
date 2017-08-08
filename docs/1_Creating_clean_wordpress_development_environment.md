## Creating a clean Wordpress development instance

We are going to install a local Wordpress instance with one single command line.

In summary, we are going to create a *Vagrantfile*, then we will create a file *settings_development.php*, that will include:

+ Information to connect to the Vagrant instance (ip, SSH key...)
+ Information about the MySQL server we are going to install (the root user/password, the user we are going to create for WordPress)
+ Information about the WordPress version that we are going to install (where to download it, the data for the *wp-config.php* file)

We will have an extra file, *tags.php*, that will indicate to Ansible the steps to execute (like install mysql development server, add one user for Wordpress, download wordpress...)

#### Register the project in *projects.php*

First we crate a folder in our computer:
```
mkdir ~/my-projects/my-wordpress/
```
If we already have a project, it could be a good idea to create a **_deployment** folder in the root of your project, and leave there all the files.

Then we need to update **projects.php** file to add the project name and project path. Inside file, just add another line like:
```php
	<?php 
		$projects = array(
			'wordpress-project' => '/Users/me/my-projects/my-wordpress/'
		);
		return json_encode($projects);
```
Check out that the path needs to be an absolute path.

#### Create a Vagrant instance

Then we need a **Vagrant file**. We can take the sample/Vagrantfile template, and replace the included IP (192.168.100.55) with the IP that we want to use (it needs to be in the same range, lets take for example *192.168.100.50*). Be careful about the IP ranges, this will depend mostly on your Virtualbox settings. Related to these deployment scripts, there are no restrictions, as soon as the instance IP is up and running, and you can SSH it, it is fine.

We need to start the Vagrant box, with the command:
```
vagrant up
```

To check that the instance is up and running, we can try to ssh it:
```
ssh vagrant@192.168.100.50
```
Default password is *vagrant*. We could also connect writting [vagrant ssh](https://www.vagrantup.com/docs/cli/ssh.html).

#### Create the development environment to populate the machine

Now we need to create the files that Ansible will use to populate the Vagrant instance.

We need two files, *tags.php* and *settings_development.php*.

As we explained, *tags.php* will contain all the steps to populate the instance
```php
	<?php 
		$tags = array(
			'development'	=> array(
				'deploy' => array (
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
				)
			)
		);
		return json_encode($tags);
```
As we see, we have one single environment (*development*) that has only a single tag (*deploy*).
We could have more than one tag or more than one environment, then it would look like:
```php
	<?php 
		$tags = array(
			'development' => array(
				'deploy' => array (...),
				'update' => array (...),
				'soft-update' => array (...)
			),
			'staging' => array(
				'install' => array (...),
				'change' => array (...),
			),
			'production' => array(
				'update' => array (...)
			)
		);
		return json_encode($tags);
```

Some of the previous tags needs some data. For example *php-install-php7-fpm* does not need any special data to perform the action, but on the othe hand *mysql-server-create-user* will need the username and password that we want to create.

To provide this information, we must create *settings_development.php* file:
```php
<?php 
	// This is a PHP file, we can use PHP variables
	$ip = '192.168.100.50';
	$settings = array(
		'project' => array(
			'name' => 'my-project',
			'root' => '/var/www/my-project-running',
			'environment' => 'development',
			'server' => array(
				'target'		=> $ip,
				'user'			=> 'vagrant',
				'ask_sudo_pwd' 	=> 'vagrant',
				'sshkey' 		=> vagrantKey()
			)
		),
		'system' => array(
			'set_locales' => array(
				'name'		=> 'en_US.UTF-8',
				'package' 	=> 'language-pack-en',
				'timezone'	=> 'Europe/Stockholm'
			)
		),
		'mysql' => array(
			'root' => array(
				'username' => 'root',
				'password' => 'testtest'
			),
			'user' => array(
				'username' => 'dev-user',
				'password' => 'dev-pass'
			),
			'server' => array(
				'host' => $ip,
				'port' => '3306',
				'db'   => 'mydatabasename'
			)
		),
		'wordpress' => array(
			'download' => array(
				'url' 			=> 'https://wordpress.org/wordpress-4.8.tar.gz',
				'sha256sum' 	=> '39210d593700dc26c58a53b38172be63ea3da67020d80bb2cf34b396b732dd4d'
			),
			'install' => array(
				'path' 			=> '/var/www/wordpress/',
				'target' 		=> $ip,
				'language' 		=> 'en_GB',
				'title' 		=> 'My Nice Patata Blog',
				'table_prefix' 	=> 'wp_'
			),
			'admin' => array(
				'name' 		=> 'Patata',
				'password' 	=> 'PatataPass',
				'email' 	=> 'papapa@papapapapapa.com'
			),
			'permission' => array(
				'user' 	=> 'www-data',
				'group' => 'www-data'
			)
		),
		'nginx' => array(
			'virtualhost' => projectPath().'nginx/wordpress'
		)
	);
	return json_encode($settings);
```
To get an explanation about each particular variable, open the file **sample/settings_development.php**.

The array *project* is mandatory for all projects, and contains general information about the project (name, path...). The other first level array keys, like *system*, *mysql*, *wordpress*, ... contains the variables for the playbook with names *system*, *mysql*, *wordpress*, ... that are hosted in *playbooks/system*, *playbooks/mysql*, *playbook/wordpress* ...

As you see, value *sshkey* (inside *server* -> *project*) needs to be the path to the vagrant ssh key. It must be a path like: */Users/me/my-projects/my-wordpress/.vagrant/machines/default/virtualbox/private_key*. There is a bunch of functions (inside *deployer/settings-helpers.php*), that you can use to help to define these kind of variables. In this case, *vagrantKey()* function will return this Vagrant SSH key path.

#### Populate the instance
To run the project in a single command:
```
php ansela.php --direct wordpress development deploy
```

If you want to use the assistant, just execute:
```
php ansela.php --assistant
```
and follow the instructions.

Now, if you open the url **192.168.100.50**, you should be able to see the Wordpress site up and running.

Usually we would like to use our own database, plugins and library files in the Wordpress installation. We will explain this topic in the next document [completing wordpress development](2_Completing_wordpress_development_environment.md).
