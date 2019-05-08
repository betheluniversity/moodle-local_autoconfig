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

    /*
        This function used to be imported from /local/uwmoodle/util/uwmoodle_util_helper.php
        To bypass having a dependency on another custom plugin, I just copy/pasted the function directly from 
        https://git.doit.wisc.edu/uw-moodle/moodle-local_uwmoodle/blob/master/util/uwmoodle_util_helper.php#L241
    */
    public static function create_anon_users($count) {
        global $DB, $CFG;

        // TODO: We should create user contexts, and maybe call event handlers, etc.  Otherwise we rely on cron to fix up the accounts.

        for ($i = 1; $i <= $count; ++$i) {
            $username = "anon$i";
            if (!$DB->record_exists('user', array('username'=>$username))) {
                //create anon user
                $user = new stdClass;
                $user->username = $username;

                $user->firstname = "anonfirstname$i";
                $user->lastname = "anonlastname$i";
                $user->city = 'Perth';
                $user->country = 'AU';
                $user->lastip = '127.0.0.1';
                $user->password = 'restored';
                $user->email = "anon$i@doesntexist.com";
                $user->emailstop = 1;
                $user->confirmed  = 1;
                $user->timecreated   = time();
                $user->timemodified  = 0;
                $user->mnethostid = $CFG->mnet_localhost_id;
                $user->lang = $CFG->lang;

                $user->id = $DB->insert_record('user', $user);
                echo '+';
            } else {
                echo '.';
            }
        }
    }

    protected function count_anonusers() {
        global $DB;

        return $DB->count_records_select('user', "username LIKE 'anon%' AND username NOT LIKE '%wisc%'");
    }

    public function update() {
        $config = $this->get_config();

        if (empty($config->anonusers)) {
            return;
        }

        $numanon = $this->count_anonusers();
        if ($config->anonusers > $numanon) {
            $this->output("Creating $config->anonusers anon users");
            $this->create_anon_users($config->anonusers);
            $this->output('done');
        }
    }
}
