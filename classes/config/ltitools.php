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

require_once($CFG->dirroot.'/mod/lti/locallib.php');

/**
 * \local_autoconfig\ltitools class
 *
 * Configure lti tools
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->ltitools[] = array('lti_typename'=>'Piazza',
 *                                            'lti_toolurl'=>'https://piazza.com/connect',
 *                                            'lti_resourcekey'=>'key',
 *                                            'lti_password'=>'password',
 *                                            'lti_customparameters'=>'',
 *                                            'lti_coursevisible'=>1,
 *                                            'lti_launchcontainer'=>4,
 *                                            'lti_sendname'=>1,
 *                                            'lti_sendemailaddr'=>1,
 *                                            'lti_acceptgrades'=>0,
 *                                            'lti_forcessl'=>1,
 *                                            'lti_organizationid'=>'',
 *                                            'lti_organizationurl'=>'');
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ltitools extends base {

    protected function update_ltitool($data) {
        global $DB, $SITE;
        $data = (object) $data;
        if ($rec = $DB->get_record('lti_types', array('name'=>$data->lti_typename, 'course'=>$SITE->id), '*', IGNORE_MISSING)) {
            // update existing tool
            $this->output("Updating lti tool $data->lti_typename");
            $type = new \stdClass();
            $type->id = $rec->id;
            lti_update_type($type, $data);
        } else {
            // create a new tool
            $this->output("Creating lti tool $data->lti_typename");
            $type = new \stdClass();
            $type->state = LTI_TOOL_STATE_CONFIGURED;
            lti_add_type($type, $data);
        }
    }

    public function update() {
        $config = $this->get_config();

        if (!empty($config->ltitools)) {
            foreach ($config->ltitools as $data) {
                $this->update_ltitool($data);
            }
        }
    }
}