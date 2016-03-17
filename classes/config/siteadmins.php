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
 * \local_autoconfig\siteadmins class
 *
 * Configure site admins
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->siteadmins = 'admin,username1,username2';
 *
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class siteadmins extends base {

    protected function find_or_add_user($username) {
        global $DB;

        if ($userid = $DB->get_field('user', 'id', array('username'=>$username, 'deleted'=>0))) {
            return $userid;  // found user, so done
        }

        // not found, so you could try adding an external user here...
        return false;
    }

    public function update() {
        $config = $this->get_config();

        if (!empty($config->siteadmins)) {
            $adminusernames = explode(',',$config->siteadmins);
            $admins = array();
            foreach ($adminusernames as $username) {
                $username = trim($username);
                if (empty($username)) {
                    continue;
                }
                try {
                    $userid = $this->find_or_add_user($username);
                    if ($userid) {
                        $admins[] = $userid;
                    } else {
                        $this->output("No account for $username");
                    }
                } catch (\Exception $e) {
                    $this->output($e->getMessage());
                }
            }
            if (empty($admins)) {
                $this->output("No site admins, bailing out.");
                return;
            }
            $this->output("Configured ".count($admins)." site admins.");
            set_config('siteadmins', implode(',', $admins));
        }
    }
}