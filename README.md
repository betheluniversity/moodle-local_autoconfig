# Moodle Plugin: local_autoconfig

[Matt Petro](matt.petro@wisc.edu) (University of Wisconsin - Madison) created this plugin for Moodle 2.8 to help automate
creating and deploying Moodle instances en masse. The gist of it is this:

1. After deploying a "stock" version of Moodle, install this plugin
2. Use this plugin to upload a file that stores all the custom settings that your environment uses
3. This plugin applies those changes automatically

Conversely, this plugin can be installed on an already-customized deployment of Moodle and export its customized settings 
for re-deployment elsewhere.

After its creation, Matt Petro updated it a couple times so that it was compatible with Moodle 3.1. At that time, 
UW-Madison moved away from Moodle and he stopped maintaining the plugin.

Matt Putz asked the Web Services team to implement local_autoconfig in our Dev (and eventually Prod) deployments, so 
Phil got in contact with Petro in April 2019 to gain access to his 
[original repository](https://git.doit.wisc.edu/uw-moodle/moodle-local_autoconfig). Since Petro had stopped maintaining
that repository, he gave permission for this fork to be made so that any changes that need to be made to make the plugin 
compatible with Moodle 3.6+ can be tracked here.
