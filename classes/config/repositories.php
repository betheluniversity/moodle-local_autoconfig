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

require_once($CFG->dirroot . '/repository/lib.php');

/**
 * \local_autoconfig\repositories class
 *
 * Configure repositories
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->repositories[] = array('repos'=>'boxnet',
 *                                                'pluginname'=>'',
 *                                                'clientid'=>'clientid',
 *                                                'clientsecret'=>'clientsecret',
 *                                                'visible'=>1);
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repositories extends base {

    protected function update_repository($data) {
        $data = (object) $data;
        $type = new \repository_type($data->repos, (array)$data, $data->visible);
        $success = true;
        $this->output("Processing $data->repos repository");
        if ($repoid = $type->create(true)) {
            $this->output("Created");
        } else {
            // Couldn't create, so try update
            $type->update_options((array)$data);
            $this->output("Updated");
        }
    }

    public function update() {
        $config = $this->get_config();

        if (!empty($config->repositories)) {
            foreach ($config->repositories as $data) {
                $this->update_repository($data);
            }
        }
    }
}