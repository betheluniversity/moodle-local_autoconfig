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

namespace local_autoconfig\config;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/cache/locallib.php');

/**
 * \local_autoconfig\cacheconfig class
 *
 * Configure caching
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->cacheinstances[] = array('plugin'=>'file',
 *                                                  'name'=>'Cluster local cache',
 *                                                  'lock'=>'cachelocal_file_default',
 *                                                  'path'=>'/tmp/somewhere',
 *                                                  'autocreate'=>1,
 *                                                  'mappings' => array('core/string','core/langmenu','core/databasemeta','core/htmlpurifier'));
 *
 * This is a one-time configuration which doesn't update unless the cache store is deleted manually.
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cacheconfig extends base {


    public function update() {
        $config = $this->get_config();

        if (!empty($config->cacheinstances)) {
            \cache_helper::update_definitions();
            $stores = \cache_administration_helper::get_store_instance_summaries();
            foreach ($config->cacheinstances as $data) {
                $data = (object) $data;
                if (array_key_exists($data->name, $stores)) {
                    // already there, so skip
                    continue;
                }
                $this->output("Creating cache store '$data->name'");
                $config = \cache_administration_helper::get_store_configuration_from_data($data);
                $config['lock'] = $data->lock;
                $writer = \cache_config_writer::instance();
                $writer->add_store_instance($data->name, $data->plugin, $config);
                foreach($data->mappings as $definition) {
                    $writer->set_definition_mappings($definition, array($data->name));
                }
            }
        }
    }
}