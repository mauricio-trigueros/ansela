## Create a clean Wordpress development instance

**Scenario:** we finished [the previous document](1_Creating_clean_wordpress_development_environment.md), and we have our default Wordpress instance up and running. We want to improve these settings, with the next features:

+ Add a MySQL dump file with a dumped Wordpress database
+ Add some files to the library

#### Adding a default mysql database
First we need the SQL file, that we can leave in *~/my-projects/my-wordpress/mysql/myfile.sql*

Then we need to include this path in our *settings_development.php* file, under the *dump* value, like
```php
	<?php 
		$settings = array(
			'mysql' => array(
				'dump' => projectPath().'mysql/myfile.sql'
				.... other parameters ....
			)
		);
		...
```

The tag to import the database is called **mysql-populate-db**. Check that, in order to import the MySQL database in the dev environment, we will need the MySQL tools, so we can install them including previously the tag **mysql-install-client**

We can add these two tags to our previous *deploy* action, or create a new action, as we will do here:
```php
	<?php 
		$tags = array(
			'development'	=> array(
				'deploy' => array ( ... ),
				'update-database' => array(
					'mysql-install-client',
					'mysql-populate-db'
				)
			)
		);
		...
```

Then you could execute these new action with:
```
php ansela.php --direct wordpress development update-database
```

#### Adding the Media Library (Uploads)
The idea is very similar. We have a **uploads.zip** file, that we can leave in our project root. 

This file must contain all the uploaded files, same folder structute than Wordpress uploads folder. Usally, the structure is something like:
```
2017/
2017/07/
2017/07/astronauta1.jpg
2017/07/astronauta2.jpg
2017/07/astronauta4.jpg
2017/07/mars1.jpg
...
```

Then we need to include this file in *settings_development.php* file:
```php
	<?php 
		$settings = array(
			'wordpress' => array(
				'uploads' => projectPath().'uploads.zip',
				.... other parameters ....
			)
		);
		...
```

In the remote development server, we must install first the unzip program (tag **system-soft_install_unzip**), and then use the tag 'wordpress-uploads'. As usual, we can include these tags to the deploy action, or create a new action, as shown:
```php
	<?php 
		$tags = array(
			'development'	=> array(
				'update-database' => array ( ... ),
				'add-uploads' => array(
					'system-soft_install_unzip',
					'wordpress-uploads'
				)
			)
		);
		...
```

And execute the code using:
```
php ansela.php --direct wordpress development add-uploads
```

#### Installing some plugins
To finish this tutorial, we will include some plugins.

We need to update the *wordpress* settings to include the plugins list:
```php
	<?php 
		$settings = array(
			'wordpress' => array(
				'plugins' => array(
					array( 'name' => 'advanced-custom-fields', 'version' => 'tags/4.4.9' ),
					array( 'name' => 'tinymce-advanced', 'version' => 'tags/4.1.7' ),
					array( 'name' => 'w3-total-cache', 'version' => 'tags/0.9.4.1' )
				),
				.... other parameters ....
			)
		);
		...
```

And update the actions:
```php
	<?php 
		$tags = array(
			'development'	=> array(
				'update-database' => array ( ... ),
				'install-plugins' => array(
					'wordpress-install-plugins'
				)
			)
		);
		...
```
And execute the code using:
```
php ansela.php --direct wordpress development install-plugins
```

The next step would be to move our current WordPress instance into Amazon Web Services. This will be explained in the third tutorial, [creating WordPress in AWS](3_Creating_wordpress_in_AWS.md)
