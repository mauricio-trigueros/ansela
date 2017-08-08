## Working with wordpress themes in development

We need a folder where we will have all the WordPress theme files:
```
mkdir ~/my-projects/my-wp-theme/
```

Now we will mount this theme in the WordPress that we installed in chapter 1. So we need to edit
```
~/my-projects/my-wordpress/Vagrantfile
```
to point to our WordPress theme. We can do this adding a line:
```
config.vm.synced_folder "/Users/me/my-projects/my-wp-theme", "/var/www/wordpress/wp-content/themes/my-wp-theme", create: true, :nfs => { :mount_options => ["dmode=777","fmode=777"] }
```
(check Vagrantfile example in *sample/Vagrantfile*). Thanks to this line, we are mapping our Wordpress theme */Users/me/my-projects/my-wp-theme* into the remote Vagrant system, with path */var/www/wordpress/wp-content/themes/my-wp-theme*.

Now we need to reload the Vagrant settings, so we must go to *~/my-projects/my-wordpress*, and execute:
```
vagrant reload
```

When the server is restarted, we could go to http://192.168.100.50/wp-admin, and select the theme.

In this way, any change in our local folder **my-wp-theme** will be reflected instantaneously in the WordPress instance. 
