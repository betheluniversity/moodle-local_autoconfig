<?php
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle auto configuration
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autoconfig;

defined('MOODLE_INTERNAL') || die();

/**
 * \local_autoconfig\autoconfig class
 *
 * Autoconfig driver class
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class autoconfig {

    /**
     * Return an array of all enabled config class names
     *
     * @return multitype:string
     */
    function get_config_classes() {
        // static ordered list, for now
        return array(
                'cacheconfig',
                'languagepacks',
                'categories',
                'roles',
                'plugins',
                'blocks',
                'gradescales',
                'serverdirectories',
                'anonusers',
                'rssfeeds',
                'repositories',
                'portfolios',
                'ltitools',
                'siteadmins',
                'geoip',
        );
    }

    /**
     * Execute config classes
     *
     * @param \progress_trace $progress
     * @param string | null $configclasses comma-separated list of config class names or null for all
     */
    function execute(\progress_trace $progress, $configclasses = null) {
        $progress->output('===Auto configuration start===');

        $allconfigclasses = $this->get_config_classes();
        if (!$configclasses) {
            $configclasses = $allconfigclasses;
        } else {
            $configclasses = array_intersect($configclasses, $allconfigclasses);
        }

        // Loop and execute each config class
        foreach ($configclasses as $configclass) {
            try {
                $this->execute_configclass($progress, $configclass);
            } catch (\Exception $e) {
                $progress->output('Error executing config class '.$configclass.' : '.$e->getMessage(), 1);
                $progress->output($e->getTraceAsString(), 1);
            }
        }

        // Lots of changes need this cache flushed
        \core_plugin_manager::reset_caches();

        cli_heading('Auto configuration end');
    }

    /**
     * Execute one config class by classname
     *
     * @param \progress_trace $progress
     * @param string $configclass
     */
    function execute_configclass(\progress_trace $progress, $configclass) {
        $progress->output("Executing $configclass");
        $classname = __NAMESPACE__.'\\config\\'.$configclass;
        $configobj = new $classname($progress);
        $configobj->update();
    }
}