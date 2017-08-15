## Create a clean Wordpress development instance

**Scenario:** we finished [the previous document](1_Creating_clean_wordpress_development_environment.md), and we have our default Wordpress instance up and running. We want to improve these settings, with the next features:

+ Add a MySQL dump file with a dumped Wordpress database
+ Add some files to the library

#### Adding a default mysql database
First we need the SQL file, that we can leave in *~/my-projects/my-wordpress/dumps/myfile.sql*

Then we need to include this path in our *settings_development.php* file, under the *dump* value, like
```php
	<?php 
		$settings = array(
			'mysql' => array(
				'dumps' => array(
					'export_folder' => projectPath().'dumps',
					'import_file' => projectPath().'dumps/my-file.sql'
				),
				.... other parameters ....
			)
		);
		...
```

The tag to import the database is called **mysql_import-database**. Check that, in order to import the MySQL database in the dev environment, we will need the MySQL tools, so we can install them including previously the tag **mysql_install-client**

We can add these two tags to our previous *deploy* action, or create a new action, as we will do here:
```php
	<?php 
		$tags = array(
			'development'	=> array(
				'deploy' => array ( ... ),
				'update-database' => array(
					'mysql_install-client',
					'mysql_populate-db'
				)
			)
		);
		...
```

Then you could execute these new action with:
```
php ansela.php --direct wordpress development update-database
```

Notice that in *settings_development.php*, we set two values for *dumps*. One is *export_folder*, that is the folder where we export the database dumps, with the tag **mysql_export-database**. The format name is explained in [the settings example](sample/settings_development.php). The other file, *import_file*, is the SQL file that we use with **mysql_import-database**.

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
				'uploads' => array(
					'export_folder' => projectPath().'uploads',
					'import_file' => projectPath().'uploads/uploads.zip'
				),
				.... other parameters ....
			)
		);
		...
```

Then use the tag 'wordpress-uploads'. As usual, we can include these tags to the deploy action, or create a new action, as shown:
```php
	<?php 
		$tags = array(
			'development'	=> array(
				'update-database' => array ( ... ),
				'add-uploads' => array(
					'wordpress_import-uploads'
				)
			)
		);
		...
```

And execute the code using:
```
php ansela.php --direct wordpress development add-uploads
```

As we explained for *mysql*, in the the folder *export_folder* (inside *uploads*) we will place the uploads export, executed with tag **wordpress_export-uploads**.

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
					'wordpress_install-plugins'
				)
			)
		);
		...
```
And execute the code using:
```
php ansela.php --direct wordpress development install-plugins
```

Sometimes the plugins are not public (for example if you buy an "unlock" version, and you get a zip file with the plugin), so you need to install them from a zip file. To do this, you can update the *wordpress* settings to include a *local_plugins* attribute, like:
```php
	<?php 
		$settings = array(
			'wordpress' => array(
				'local_plugins' => array(
					projectPath().'plugins/advanced-custom-fields-pro.zip'
				),
				.... other parameters ....
			)
		);
		...
```
In this case, the plugin that we are going to install is the pro version of *Advanced Custom Fields*. The tag that performs this action is **wordpress_install-local-plugins**.

The next step would be to move our current WordPress instance into Amazon Web Services. This will be explained in the third tutorial, [creating WordPress in AWS](3_Creating_wordpress_in_AWS.md)
