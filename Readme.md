# AnSeLa (Ansible Settings Layer)

**AnSeLa** (*Ansible Settings Layer*) is a layer built on top of Ansible, designed to handle all Ansilble settings and environments for several projects.

With a single command like:
```
php ansela.php --direct my-project staging deploy
```

AnSeLa will execute a bunch of Ansible tags (defined with the action *deploy*), with the settings provided by the environment *staging* against the project *my-project*. Following the same idea, if you want to do the same against production, it is as easy as changing the command line:
```
php ansela.php --direct my-project production deploy
```

Or if you have defined another actions (like *clean-cache* or similar), just replace it in the command call:
```
php ansela.php --direct my-project staging clean-cache
```

This project is designed to handle an **Ubuntu 16.04 LTS** server, and it has been written in **PHP** (included by default in any operative system), and tested in Mac OS Sierra.

The project has as main goals main goals: 
+ Create fully functional develop environments.
+ Deploy code to the server, from local computer or code repository (Github/BitBucket).
+ Perform some tasks on the server, like cleaning cache etc.

## Introduction

AnSeLa is designed to handle as many projects as you want, based on the next ideas:

1. You must include a **_deployment** folder in your project. This folder will contain:
	+ A **Vagrant** file, that will describe your development environment
	 	- It is not mandatory, you need this file if you are using a development environment.
	+ A **tags.php** file, that will indicate the orders to execute (like *mysql-install-dev-server*) for each environment. 
		- It takes the name *tag*, because it is a bunch of Ansible tasks, grouped by a [tag name](http://docs.ansible.com/ansible/latest/playbooks_tags.html).
		- This file is mandatory, and will contain the *tags* for all the environments.
		- The PHP file must return a valid JSON file.
	+ The settings for each environment (**settings_develpment.php**, **settings_staging.php**, ...), that will contain the data to feed the actions in *tags.php*
	    - It is mandatory to have at least one environment (otherwise AnSeLa is useless).
	    - The PHP file must return a valid JSON file.
	+ Some extra folders for other assets necessary for the deployment. The folder name and structure is not fixed, just keep the file and folder names according to your settings file for each environment. An example of these folders is:
		- An **Nginx** folder to keep all the virtual host for Nginx
		- An **ssh** folder to keep all the SSH keys (the deployment keys that we will install on the server to deploy the code, or the ssh keys to connect to the server). The system can also read the SSH keys from your *~/.ssh* folder, as you specify it in the *settings.php* file.
 2. All AnSeLa actions (like *deploy*, *update*...) need to be run in a single command line. It does not matter how complex is the action to perform, but probably, as complex is the action, more steps (Ansible tags) are involved in the execution.
 3. You do not need to have always the three main environments (*development*, *staging*, *production*), you can have only one of them (for example, if you just want to deploy static code to any server), two of them.... In addition, you could handle more than the default environments, just adding files like **settings_MY-ENVIRONMENT-NAME.php**
 4. AnSeLa is project-independent. All the settings that work for one kind of project, it must work for the next project, using the same technology. For example, if you write a *tag* to dump a Mongo database, this *tag* must work for any project that uses Mongo databases (and has all mandatory variables defined).

## Dependencies

To use this program, you will need to have in your system:

### PHP
All these deployment scripts are based on PHP. You need minimum PHP version *5.5*, you can check your PHP version executing:
```
php -v
```

### Ansible
[Ansible](http://docs.ansible.com/ansible/index.html) is an IT automation tool, that we use to populate the server.
This project has been tested with Ansible version *2.3.1.0*
You can test your Ansible version executing: 
```
ansible --version
```

### Virtualbox
[Virtualbox](https://www.virtualbox.org/) is a virtualization tool that we will use to create our development environments.
This project has been tested with Virtualbox version *5.1.22r115126*
You can test your Virtualbox version executing:
```
vboxmanage --version
```
If you do not need development servers (you just want to use servers that are up and running), probably you will not need to install Virtualbox.

### Vagrant
[Vagrant](https://www.vagrantup.com/) is a tool to manage the lifecycle of virtual machines. We use it to create development servers.
This project has been tested with Vagrant version *1.9.5*
```
vagrant --version
```

## Program settings

Two files are mandatory to be able to run the program: **projects.php** and **settings.php**. You can rename *projects.sample.php* and *settings.sample.php*, and take them as boilerplate.

The first file, **projects.php**, it is just an array that contains all the project names (as key), and where does this project live in your computer, the project absolute path (as a value), exported as a JSON file.

The second file, **settings.php**, is another array with some settings to use in the program. For example, *ssh_folder*, that is the full path to your SSH folder, so the program will be able to use your SSH keys to connect to the servers. As the previous file, this array is exported as JSON file.

## Working with the program

To make it work, just execute:
```
php ansela.php
```

You will see all the options that you can use with AnSeLa. To perform deployments, the two main options are **--assistant** and **--direct**. If you choose, *--assistant*, then you will get a terminal prompt asking for an available project. You will see the projects list (taken from *projects.php*), and then you will need to type the project name you want to use. Then, in the same way, you will need to pick the *environment* and the *action*, as soon as you provided correct values, the system will start performing the requested action.

If you want to automatize the execution, you should use the **--direct** option. Then you should include in the terminal call the project name, the environment and the action to execute, like that:
```
php ansela.php --direct PROJECT-NAME ENVIRONMENT ACTION
```

To adjust the verbose level, you can change the logging threshold with the variable **$GLOBALS['logLevel']** in *ansela.php*. You can choose between:

+ *Loglevel::Trace*
+ *Loglevel::Debug*
+ *Loglevel::Info*
+ *Loglevel::Warn*
+ *Loglevel::Error*
+ *Loglevel::Fatal*

### AnSeLa Commands
```
php ansela.php view-tags
```
List all the tags that you can use in your *actions*, in **tags.php**.

```
php ansela.php view-playbooks
```
List all the Ansible playbooks that we have implemented. It matches all the folder names inside *playbooks* folder.
All *tags* start with the playbook name. So, for example, the tag *deployment_python* is located inside *deployment* playbook; the tag *mongo-drop-database* belongs to the *mongo* playbook, etc.

```
php ansela.php view-playbook-variables PLAYBOOK-NAME
```
Shows all the variables used in the playbook *PLAYBOOK-NAME*.

## Tutorials
1. [Creating a clean wordpress development environment](docs/1_Creating_clean_wordpress_development_environment.md)
2. [Completing the WordPress development environment](docs/2_Completing_wordpress_development_environment.md)
3. [Creating a WordPress instance in AWS](docs/3_Creating_wordpress_in_AWS.md)
4. [Working with WordPress themes in development](docs/4_Working_with_wordpress_themes_in_development.md)
5. [Working with Wordpress themes in AWS](docs/5_Working_with_wordpress_themes_in_AWS.md)
6. [Working with Wordpress plugins](docs/5_Working_with_wordpress_themes_in_AWS.md)

## Contact
Please contact mauricio@houseofradon.com for any questions or problem.

