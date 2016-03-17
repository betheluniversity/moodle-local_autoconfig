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

require_once($CFG->libdir . '/portfoliolib.php');

/**
 * \local_autoconfig\portfolios class
 *
 * Configure portfolios
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->portfolios[] = array('plugin'=>'googledocs',
 *                                                'name'=>'Google Drive',
 *                                                'clientid'=>'clientid',
 *                                                'secret'=>'clientsecret',
 *                                                'visible'=>1);
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class portfolios extends base {

    protected function update_portfolio($data) {
        global $DB;
        $data = (object) $data;
        if ($rec = $DB->get_record('portfolio_instance', array('plugin'=>$data->plugin), '*', IGNORE_MISSING)) {
            // update existing portfolio
            $this->output("Updating portfolio $data->plugin");
            $instance = portfolio_instance($rec->id);
            $instance->set_config((array)$data);
            $instance->save();
        } else {
            // create a new portfolio
            $this->output("Creating portfolio $data->plugin");
            portfolio_static_function($data->plugin, 'create_instance', $data->plugin, $data->name, $data);
        }
    }

    public function update() {
        $config = $this->get_config();

        if (!empty($config->portfolios)) {
            foreach ($config->portfolios as $data) {
                $this->update_portfolio($data);
            }
        }
    }
}