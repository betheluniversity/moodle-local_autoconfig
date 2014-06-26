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

/**
 * \local_autoconfig\gradescales class
 *
 * Configure gradescales
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->gradescales[] = array('name'=>'', 'scale'=>'', 'description'=>'');
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class gradescales extends base {

    protected function create_grade_scale($data) {
        global $DB;

        if (empty($data->name) || empty($data->scale)) {
            $this->output("Skipping scale $data->name");
            return;
        }

        $existing = $DB->get_record('scale', array('name'=>$data->name, 'courseid'=>0), 'id');
        if ($existing) {
            // Just create, don't update existing since they may be in use
            return;
        }
        $this->output ("Creating grade scale $data->name");
        $data->courseid = 0;
        $data->userid = 0;
        $data->descriptionformat = FORMAT_HTML;
        $data->time = time();

        $DB->insert_record('scale', $data);
    }

    public function update() {
        $config = $this->get_config();

        if (!empty($config->gradescales)) {
            foreach ($config->gradescales as $data) {
                $this->create_grade_scale((object) $data);
            }
        }
    }
}