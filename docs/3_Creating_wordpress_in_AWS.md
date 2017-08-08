## Creating a Wordpress instance inside and AWS EC2 instance

First, we need to crate a new file **settings_staging.php**, where we will add all settings for this new environment. Then we will add the actions we want in the same **tags.php** file (we could also re-use some of the existing actions).

We assume that we have an EC2 instance (running a clean Ubuntu 16.04 LTS version), with the firewall well set up, and an SSH key to access.

We must try to SSH the instance to check that is accessible:
```
ssh ubuntu@152.90.103.75 -i ~/.ssh/my-ssh-key.pem
```

So, the basic first task is to install the locales, upgrade the installed packages, and set the unattended-upgrades.

To do this, our **settings_staging.php** looks like:
```php
<?php 
	$settings = array(
		'project' => array(
			'name' => 'my-project',
			'root' => '/var/www/my-project-running',
			'environment' => 'staging',
			'server' => array(
				'target'	=> '152.90.103.75',
				'user'		=> 'ubuntu',
				'ask_sudo_pwd' => false,
				'sshkey' => getKeyPath('my-ssh-key.pem')
			)
		),
		'system' => array(
			'set_locales' => array(
				'name'		=> 'en_US.UTF-8',
				'package' 	=> 'language-pack-en',
				'timezone'	=> 'Europe/Stockholm'
			)
		)
	);
	return json_encode($settings);
```

Check that to get the path for the SSH key that we need to connect to the instance, we use a method (*getKeyPath*) that has as parameter the key name (*my-ssh-key.pem*). This method will try to find the file, first in the project folder (*~/my-projects/my-wordpress/ssh/my-ssh-key.pem*), otherwise in the *ssh_folder* defined in *settings.php*. If file is not found, and exception is launched and the script finishes.

The **tags.php** file looks like:
```php
	<?php 
		$tags = array(
			'staging' => array(
				'deploy' => array(
					'system_install_python_27',
					'system_set_locales',
					'system_upgrade_packages'
				)
			)
		);
		return json_encode($tags);
```

Check that the first tag for action *deploy*, that is **system_install_python_27** is mandatory. This tag will install python on the remote server, that is mandatory to run any Ansible command. If you open *ansible-hosts*, you will find the path to the python file (in the remote server):
```
# Project: my-project
[152.90.103.75]
152.90.103.75
[152.90.103.75:vars]
ansible_python_interpreter=/usr/bin/python2.7
```
(Note that this file is genearted dinamically)

Finally, to send our first commands to the remote server, just run:
```
php ansela.php --direct wordpress staging deploy
```

#### Including the MySQL server

Then we need to have a MySQL server (we will not install a MySQL server in the same instance). We will need to know the host, the port, the database name, the user and the pass.

To finish our clean WordPress installation in AWS, the **settings_staging.php** looks like:

```php
<?php 
	$settings = array(
		'project' => array(
			'name' => 'my-project',
			'root' => '/var/www/my-project-running',
			'environment' => 'staging',
			'server' => array(
				'target'	=> '152.90.103.75',
				'user'		=> 'ubuntu',
				'ask_sudo_pwd' => false,
				'sshkey' => getKeyPath('my-ssh-key.pem')
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
			'user' => array(
				'username' => 'testuser',
				'password' => '98te45ra'
			),
			'server' => array(
				'host' => 'radonwebsite.c5mz83y6m5jq.us-east-1.rds.amazonaws.com',
				'port' => '3306',
				'db'   => 'wordpress-test'
			)
		),
		'wordpress' => array(
			'download' => array(
				'url' => 'https://wordpress.org/wordpress-4.8.tar.gz',
				'sha256sum' => '39210d593700dc26c58a53b38172be63ea3da67020d80bb2cf34b396b732dd4d'
			),
			'install' => array(
				'path' => '/var/www/wordpress/',
				'target' => '152.90.103.75',
				'language' => 'en_GB',
				'title' => 'My Nice Patata Blog',
				'table_prefix' => 'wp_'
			),
			'admin' => array(
				'name' => 'Patata',
				'password' => 'PatataPass',
				'email' => 'papapa@papapapapapa.com'
			),
			'permission' => array(
				'user' => 'www-data',
				'group' => 'www-data'
			)
		),
		'nginx' => array(
			'virtualhost' => projectPath().'nginx/wordpress'
		)
	);
	return json_encode($settings);
```

And the **tags.php** file, for staging looks like:
```php
<?php 
	// Example of file with settings
	$tags = array(
		'staging' => array(
			'deploy' => array(
				'system_install_python_27',
				'system_set_locales',
				'system_upgrade_packages',
				'php-install-php7-fpm',
				'wordpress-install-dependencies',
				'wordpress-download-wordpress',
				'wordpress-add-wp-config',
				'nginx_install_default',
				'nginx_add_virtualhost',
				'wordpress-install-wordpress'
			)
		)
	);

	return json_encode($tags);
```



