## Working with wordpress themes in AWS (staging or production)

Lets assume that the WordPress theme that we started in the previous tutorial is ready to be published. The code must be placed in a repository (lets assume that is a private repository). If the repository is private, we will need to create a private deployment SSH key, and add it to the repository. In this way, we will be able to clone the code, but this key does not allow us to push any changes to the repository.

As usual, we will create a **_deployment** folder inside our current project *my-wp-theme*:
```
mkdir ~/my-projects/my-wp-theme/_deployment
```

We need to add this new project **my-wp-theme** (with path **/Users/me/my-projects/my-wp-theme/_deployment**) to the file **projects.php**.

Then we need to create our **settings_staging.php** and **tags.php** inside this *_deployment* folder. In this case we do not have an "development" environment, we will use another project for that (the one that we created in step 1). In this way, if we need to create more repository with plugins or another wordpress resources, we could still use the project 1, because it is not related or linked to any project.

The file **settings_staging.php** looks like:
```php
<?php 
	$settings = array(
		'project' => array(
			'name' => 'my-wp-theme',
			'root' => '/var/www/wordpress/wp-content/themes/my-wp-theme',
			'environment' => 'staging',
			'server' => array(
				'target'	=> '152.90.103.75',
				'user'		=> 'ubuntu',
				'ask_sudo_pwd' => false,
				'sshkey' => getKeyPath('my-ssh-key.pem')
			)
		),
		'system' => array(
			'add_new_users' => array(
				array(
					'username' => 'wordpressuser',
					// mkpasswd  -m sha-512 -s <<< wordpress
					'password' => '$6$V691KA3IG7tWQ$zWWxJcqyasvEE41rXA0dKxiipoBA5cGDicwPhA7lIJfKv6l1hCxouoIymfMapGuM8CS5afF4B/7NYz3lpi4IP/'
				)
			)
		),
		'deployment' => array (
			'local_path' => '/tmp/my-wp-theme/',
			'rollback' => array(
				'path' => '/home/wordpressuser/my-wp-theme-rollback/',
				'copies' => 3
			),
			'build_folder' => '""',
			'checkout' => array (
				'user' => 'wordpressuser',
				'ssh_key_name' => 'my-wp-theme-deployment.pem',
				'ssh_key_content' => getKeyContent('my-wp-theme-deployment.pem')
			),
			'permissions' => array (
				'owner' => 'wordpressuser',
				'group' => 'wordpressuser',
				'mode' => '0755'
			),
			'hosting' => array (
					'provider' 	    => 'bitbucket.org',
					'user' 		 	=> 'my-user',
					'repo' 			=> 'wordpress-theme-demo',
					'branch' 		=> 'master'
			),
			'to_remove' => array ( '_deployment')
		),
	);
	return json_encode($settings);
```

As we see in the *system*, we create a new user called **wordpressuser** in the remote system. This user will have its own "home" folder on the remote server (for example, we will keep in this home folder all the *rollbacks* of the projects we deploy, as we will see later). It also will have an *ssh* folder with the deployment key, to clone the project from the repository.

The file **tags.php** looks like:
```php
<?php 
	$tags = array(
		'staging' => array(
			'co-deploy' => array(
				'system_add-new-users'
			),
			'update' => array(
				'deployment_static'
			)
		)
	);

	return json_encode($tags);
```

Do not forget that the server it has been used earlier to install WordPress, so we do not to install anything extra, just add the new user (action *co-deploy*), and then perform the code updates (action *update*).

There are several types of deployment, depending on the kind of project. In this case we just need to place the code in the right folder, that is all, so we pick the *deployment_static* type. This tag will add the mandatory SSH key, clone the project, and leave the code in the right folder.

#### Rollback version

In settings.php -> rollback -> path we specify the folder that will contains the "old copies" every time that we deploy a new version. We archive old versions with a prefix *export_*, then the project name, and finally the date. So a file will look like: 
```
export_my-wp-theme_2017-07-25_16-35-21.tar.gz
```

If we want to rollback our deployment code, we need to create the action in **tags.php** first:
```php
	'rollback' => array(
		'deployment_rollback'
	)
```

And then we can use it:
```php
php ansela.php --direct my-wp-theme staging rollback
```

Keep in mind that this action will rollback ONLY the latest version. We will keep as many version as you specify in settings.php -> rollback -> copies, but if you want to rollback any of them, you will need to do it manually.

