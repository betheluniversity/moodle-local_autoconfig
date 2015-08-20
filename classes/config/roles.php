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
 * \local_autoconfig\roles class
 *
 * Configure roles
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->roles_definition_dir :  Directory to search for role definitions to install
 * $CFG->local_autoconfig->role_sort_order :  role sort order, by shortname
 *
 *  Settings configuration by role shortname:
 *
 * $CFG->local_autoconfig->gradebookroles_byshortname
 * $CFG->local_autoconfig->profileroles_byshortname
 * $CFG->local_autoconfig->report_usage_roles_byshortname
 * $CFG->local_autoconfig->coursecontact_byshortname
 * $CFG->local_autoconfig->block_quickmail_roleselection_byshortname
 * $CFG->local_autoconfig->block_uwcourseinfo_roles_byshortname
 * $CFG->local_autoconfig->enrol_wisc_studentrole_byshortname
 * $CFG->local_autoconfig->enrol_wisc_ferparole_byshortname
 * $CFG->local_autoconfig->enrol_wisc_teacherrole_byshortname
 * $CFG->local_autoconfig->enrol_wisc_tarole_byshortname
 * $CFG->local_autoconfig->enrol_wisc_otherrole_byshortname
 * $CFG->local_autoconfig->enrol_lifelonglearning_studentrole_byshortname
 * $CFG->local_autoconfig->enrol_lifelonglearning_ferpastudentrole_byshortname
 * $CFG->local_autoconfig->enrol_lifelonglearning_teacherrole_byshortname
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class roles extends base {

    /**
     * Update or create a role based on an xml role definition
     *
     * Much of this code is copied from {@see core_role_define_role_table_advanced}
     *
     * @param string $xmlfile
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    protected function update_role($xmlfile) {
        global $DB;

        $this->output("Processing role definition ".basename($xmlfile));

        $xml = file_get_contents($xmlfile);
        if ($xml === false) {
            throw new \moodle_exception("Unable to read preset file $xmlfile");
        }

        if (!$info = \core_role_preset::parse_preset($xml)) {
            throw new \coding_exception('Invalid role preset');
        }

        $role = new \stdClass();
        $role->shortname = $info['shortname'];
        $role->name = $info['name'];
        $role->description = $info['description'];
        $role->archetype = $info['archetype'];

        $roleid = $DB->get_field('role', 'id', array('shortname'=>$role->shortname));
        if ($roleid !== false) {
            //$this->output("Processing role $role->shortname", 1);
        } else {
            $this->output("Creating role $role->shortname", 1);
        }

        // Create/update role
        if (!$roleid) {
            // Creating role.
            $roleid = create_role($role->name, $role->shortname, $role->description, $role->archetype);
            // Initialize to archetype first so that we inherit any new permissions from the archtype.
            reset_role_capabilities($roleid);
        } else {
            // Updating role.
            $role->id = $roleid;
            $DB->update_record('role', $role);
        }

        // Assignable contexts.
        // Don't just call set_role_contextlevels because that will delete/recreate all db rows each time
        $dbcontextlevels = $DB->get_records_menu('role_context_levels', array('roleid' => $roleid), '', 'id,contextlevel');
        if (array_diff($dbcontextlevels, $info['contextlevels']) || array_diff($info['contextlevels'], $dbcontextlevels)) {
            // something changed
            set_role_contextlevels($roleid, $info['contextlevels']);
        }

        // Set allowed roles.
        $this->save_allow('assign', $roleid, $info['allowassign']);
        $this->save_allow('override', $roleid, $info['allowoverride']);
        $this->save_allow('switch', $roleid, $info['allowswitch']);

        // Permissions.
        $this->save_permissions($roleid, $info['permissions']);
    }

    /**
     * Save role capabilities
     *
     * @param integer $roleid
     * @param array $wanted
     */
    protected function save_permissions($roleid, $wanted) {
        global $DB;

        $systemcontext = \context_system::instance();

        $oldpermissions = $DB->get_records_menu('role_capabilities',
                array('roleid' => $roleid, 'contextid' => $systemcontext->id),
                '', 'capability,permission');


        $changes = false;
        foreach ($wanted as $capability => $permission) {
            $oldpermission = isset($oldpermissions[$capability])? $oldpermissions[$capability] : CAP_INHERIT;
            if ($oldpermission != $permission) {
                \assign_capability($capability, $permission, $roleid, $systemcontext, true);
                $changes = 1;
            }
        }

        if ($changes) {
            $event = \core\event\role_capabilities_updated::create(
                    array(
                            'context' => $systemcontext,
                            'objectid' => $roleid
                    )
            );
            $event->trigger();
            $this->output('Updated role permissions',1);
        }

    }

    /**
     * Save role allow assign/override/switch
     * @param string $type
     * @param integer $thisroleid
     * @param array $wanted
     */
    protected function save_allow($type, $thisroleid, $wanted) {
        global $DB;

        // Replace role id -1 with our role id, but only if our role id isn't already there
        // It's a bit odd how \core_role_preset::parse_preset handles this case
        $thisrolepos = array_search(-1, $wanted);
        if ($thisrolepos !== false) {
            if (in_array($thisroleid, $wanted)) {
                unset($wanted[$thisrolepos]);
            } else {
                $wanted[$thisrolepos] = $thisroleid;
            }
        }

        $current = array_keys($this->get_allow_roles_list($type, $thisroleid));

        $addfunction = 'allow_'.$type;
        $deltable = 'role_allow_'.$type;
        $field = 'allow'.$type;

        foreach ($current as $roleid) {
            if (!in_array($roleid, $wanted)) {
                $DB->delete_records($deltable, array('roleid'=>$thisroleid, $field=>$roleid));
                continue;
            }
            $key = array_search($roleid, $wanted);
            unset($wanted[$key]);
        }

        foreach ($wanted as $roleid) {
            $addfunction($thisroleid, $roleid);
        }
    }

    /**
     * Returns an array of roles of the allowed type.
     *
     * @param string $type Must be one of: assign, switch, or override.
     * @param int $roleid
     * @return array
     */
    protected function get_allow_roles_list($type, $roleid) {
        global $DB;

        if ($type !== 'assign' and $type !== 'switch' and $type !== 'override') {
            debugging('Invalid role allowed type specified', DEBUG_DEVELOPER);
            return array();
        }

        if (empty($roleid)) {
            return array();
        }

        $sql = "SELECT r.*
        FROM {role} r
        JOIN {role_allow_{$type}} a ON a.allow{$type} = r.id
        WHERE a.roleid = :roleid
        ORDER BY r.sortorder ASC";
        return $DB->get_records_sql($sql, array('roleid'=>$roleid));
    }

    /**
     * Update all roles
     *
     * @param array $xmlfiles
     */
    protected function update_all_roles(array $xmlfiles) {
        foreach ($xmlfiles as $xmlfile) {
            $this->update_role($xmlfile);
        }
    }

    /**
     * Read all role definitions from configured directory
     *
     * @throws \moodle_exception
     * @return multitype:string
     */
    protected function get_role_definitions() {
        $config = $this->get_config();
        $xmlfiles = array();
        // scan directory
        if (isset($config->roles_definition_dir)) {
            $dir = $config->roles_definition_dir;
            if (!$dh = opendir($dir)) {  // Can't open it for some reason.
                throw new \moodle_exception('Can\t open role definition directory '.$dir);
            }
            while (false !== ($file = readdir($dh))) {
                if (preg_match('/.*\.xml$/', $file)) {
                    $xmlfiles[] = $dir.'/'.$file;
                }
            }
            closedir($dh);
        }
        return $xmlfiles;
    }

    /**
     * Return number of roles on the system
     *
     * @return number
     */
    protected function count_system_roles() {
        $allroles = \get_all_roles();
        return count($allroles);
    }

    /**
     * Convert comma-separated list of shortnames to a comma-separated list of roleids
     * @param string $shortnames
     * @return string
     */
    protected function convert_shortnames_to_roleids($shortnames) {
        global $DB;

        if (empty($shortnames)) {
            return '';
        }

        $roles = $DB->get_records_list('role', 'shortname', explode(',', $shortnames), '', 'id, shortname');
        return implode(',', array_keys($roles));
    }

    protected function update_role_dependent_settings() {
        $this->output('Updating role dependent settings');

        $config = $this->get_config();

        // config settings of the form "$plugin|$name", or $name
        $settings = array('gradebookroles',
                          'profileroles',
                          'coursecontact',
                          'block_quickmail_roleselection',
                          'report_usage|roles',
                          'block_uwcourseinfo|roles',
                          'enrol_wisc|studentrole',
                          'enrol_wisc|ferparole',
                          'enrol_wisc|teacherrole',
                          'enrol_wisc|tarole',
                          'enrol_wisc|otherrole',
                          'enrol_lifelonglearning|studentrole',
                          'enrol_lifelonglearning|ferpastudentrole',
                          'enrol_lifelonglearning|teacherrole',
        );

        foreach ($settings as $setting) {
            if (strpos($setting, '|')) {
                list ($plugin, $name) = explode('|', $setting);
            } else {
                list ($plugin, $name) = array('', $setting);
            }

            // Name in our config
            // For example gradebookroles_byshortname  or  report_usage_roles_byshortname
            $configname = str_replace('|', '_', $setting).'_byshortname';
            if (isset($config->{$configname})) {
                $value = $this->convert_shortnames_to_roleids($config->{$configname});
                $this->output($configname,1);
                set_config($name, $value, $plugin);
            }
        }
    }

    /**
     * Fix the roles.sortorder field in the database.
     */
    function fix_role_sortorder() {
        global $DB;

        $config = $this->get_config();
        if (isset($config->role_sort_order)) {

            $this->output("Fixing role sort order");
            $allroles = get_all_roles();
            $byshortname = array();
            foreach ($allroles as $role) {
                $byshortname[$role->shortname] = $role;
            }

            $cnt = 1;
            $maxcnt = $DB->get_field('role', 'MAX(sortorder) + 1', array());
            foreach (explode(',', $config->role_sort_order) as $shortname) {
                if (!isset($byshortname[$shortname])) {
                    // no such role
                    continue;
                }
                // update sort order
                $role = $byshortname[$shortname];
                if ($role->sortorder != $cnt) {
                    // There's a unique index which will prevent us from duplicating a sortorder.  So, move any
                    // conflicting one out of the way first.
                    if ($conflict = $DB->get_record('role', array('sortorder'=>$cnt), 'id', IGNORE_MISSING)) {
                        $DB->set_field('role', 'sortorder', $maxcnt, array('id'=>$conflict->id));
                        $maxcnt++;
                    }
                    $DB->set_field('role', 'sortorder', $cnt, array('id'=>$role->id));
                }
                unset($byshortname[$shortname]);
                $cnt++;
            }

            // anything else goes at the end
            foreach ($byshortname as $role) {
                if ($role->sortorder != $cnt) {
                    // There's a unique index which will prevent us from duplicating a sortorder.  So, move any
                    // conflicting one out of the way first.
                    if ($conflict = $DB->get_record('role', array('sortorder'=>$cnt), 'id', IGNORE_MISSING)) {
                        $DB->set_field('role', 'sortorder', $maxcnt, array('id'=>$conflict->id));
                        $maxcnt++;
                    }
                    $DB->set_field('role', 'sortorder', $cnt, array('id'=>$role->id));
                }
                $cnt++;
            }
        }
    }

    public function update() {

        $xmlfiles = $this->get_role_definitions();

        $beforecount = $this->count_system_roles();

        $this->update_all_roles($xmlfiles);

        $aftercount = $this->count_system_roles();

        // If the above created a new role, then we have to load the presets all over again, as
        // the first time through we might have missed some data referencing nonexistant roles.

        if ($beforecount != $aftercount) {
            $this->output("**Roles were created, so repeating role update.");
            $this->update_all_roles($xmlfiles);
        }

        // Force accessinfo refresh for users
        \context_system::instance()->mark_dirty();

        $this->update_role_dependent_settings();
        $this->fix_role_sortorder();
    }
}