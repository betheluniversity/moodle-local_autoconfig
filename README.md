# Moodle Plugin: local_autoconfig

[Matt Petro](mailto:matt.petro@wisc.edu) (University of Wisconsin - Madison) created this plugin for Moodle 2.8 to help automate
creating and deploying Moodle instances en masse. The gist of it is this:

1. After deploying a "stock" version of Moodle, install this plugin
2. Create an additional config file at $rootdir, and add your custom settings to that file.
3. Require that additional config in config.php, right before the setup.php require
4. Run `php local/autoconfig/cli/autoconfig.php`
5. That script will import all the custom settings specified in the additional file, and then configure each of the modules to match those specifications.

At the time of writing, this plugin does not have a web interface, nor can it export the custom settings of a Moodle instance for usage elsewhere.

After its creation, Matt Petro updated it a couple times so that it was compatible with Moodle 3.1. At that time, 
UW-Madison moved away from Moodle and he stopped maintaining the plugin.

Matt Putz asked the Web Services team to implement local_autoconfig in our Dev (and eventually Prod) deployments, so 
Phil got in contact with Petro in April 2019 to gain access to his 
[original repository](https://git.doit.wisc.edu/uw-moodle/moodle-local_autoconfig). Since Petro had stopped maintaining
that repository, he gave permission for this fork to be made so that any changes that need to be made to make the plugin 
compatible with Moodle 3.6+ can be tracked here.

Currently, this plugin is compatible with 3.6 but its functionality is pretty limited.
