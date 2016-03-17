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

require_once($CFG->libdir . '/coursecatlib.php');

/**
 * \local_autoconfig\categories class
 *
 * Create course categories
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->categories[] = array('name'=>'Category name',
 *                                              'idnumber'=>'Category idnumber',
 *                                              'description'=>'',
 *                                              'parentidnumber'=>'parent idnumber or empty');
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories extends base {

    protected function update_category($data) {
        global $DB;
        $data = (object) $data;
        if (empty($data->idnumber) || empty($data->name)) {
            $this->output("Skipping category with missing idnumber or name");
            return;
        }
        if ($rec = $DB->get_record('course_categories', array('idnumber'=>$data->idnumber), '*', IGNORE_MISSING)) {
            // update existing category, but don't try to update parent
            $this->output("Updating category '$data->name'");
            $rec->name = $data->name;
            $rec->description = $data->description;
            $DB->update_record('course_categories', $rec);
        } else {
            // create a new category
            $this->output("Creating category '$data->name'");
            if (empty($data->parentidnumber)) {
                // top level category
                $data->parent = 0;
            } else {
                // find parent
                $data->parent = $DB->get_field('course_categories', 'id', array('idnumber'=>$data->parent), IGNORE_MISSING);
                if (!$data->parent) {
                    $this->output("Can't locate parent category with idnumber '$data->parentidnumber'");
                    return;
                }
            }
            \coursecat::create($data);
        }
    }

    public function update() {
        $config = $this->get_config();

        if (!empty($config->categories)) {
            foreach ($config->categories as $data) {
                $this->update_category($data);
            }
        }
    }
}