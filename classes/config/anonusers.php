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

require_once($CFG->dirroot.'/local/uwmoodle/util/uwmoodle_util_helper.php');

/**
 * \local_autoconfig\anonusers class
 *
 * Prepopulate anon users.  We create these ahead of time so that instructors can anonymize course restores.
 *
 * Configuration:
 * $CFG->local_autoconfig->anonusers : number of anon users to create
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class anonusers extends base {

    protected function count_anonusers() {
        global $DB;

        return $DB->count_records_select('user', "firstname LIKE 'anonfirstname%'");
    }

    public function update() {
        $config = $this->get_config();

        if (empty($config->anonusers)) {
            return;
        }

        $numanon = $this->count_anonusers();
        if ($config->anonusers > $numanon) {
            $this->output("Creating $config->anonusers anon users");
            \uwmoodle_util_helper::create_anon_users($config->anonusers);
            $this->output('done');
        }
    }
}