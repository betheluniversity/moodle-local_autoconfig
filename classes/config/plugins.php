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

require_once($CFG->libdir.'/componentlib.class.php');
require_once($CFG->libdir.'/adminlib.php');

/**
 * \local_autoconfig\plugins class
 *
 * Configure various plugins
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->filter_disabled : comma-separated list of filters to disable
 * $CFG->local_autoconfig->filter_on : comma-separated list of filters to turn on
 * $CFG->local_autoconfig->filter_off : comma-separated list of filters to turn off   (off but available)
 * $CFG->local_autoconfig->filter_order : comma-separated list of filters
 *
 * $CFG->local_autoconfig->block_enable : comma-separated list of blocks to enable
 * $CFG->local_autoconfig->block_disable : comma-separated list of blocks to disable
 *
 * $CFG->local_autoconfig->activity_enable : comma-separated list of activities to enable
 * $CFG->local_autoconfig->activity_disable : comma-separated list of activities to disable
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugins extends base {

    protected function set_filter_state($filter, $state) {
        filter_set_global_state($filter, $state);
        if ($state == TEXTFILTER_DISABLED) {
            filter_set_applies_to_strings($filter, false);
        }
    }

    protected function update_filter_order() {
        global $DB;
        $config = $this->get_config();

        if (isset($config->filter_order)) {
            $order = $config->filter_order;

            $syscontext = \context_system::instance();
            $filters = $DB->get_records('filter_active', array('contextid' => $syscontext->id), 'sortorder ASC');
            $byname = array();
            foreach ($filters as $filter) {
                $byname[$filter->filter] = $filter;
            }

            $cnt = 1;
            // Assign specified order
            foreach (explode(',', $order) as $filtername) {
                if (!isset($byname[$filtername])) {
                    continue;
                }
                $filter = $byname[$filtername];
                if ($filter->sortorder != $cnt) {
                    $DB->set_field('filter_active', 'sortorder', $cnt, array('id'=>$filter->id));
                }
                $cnt++;
                unset($byname[$filtername]);
            }
            // Any other filters at end
            foreach ($byname as $filter) {
                if ($filter->sortorder != $cnt) {
                    $DB->set_field('filter_active', 'sortorder', $cnt, array('id'=>$filter->id));
                }
                $cnt++;
            }
        }
    }

    protected function update_filters() {
        $this->output("Updating filter configuration");
        $config = $this->get_config();

        $statuses = array (TEXTFILTER_DISABLED => 'disabled',
                           TEXTFILTER_OFF => 'off',
                           TEXTFILTER_ON => 'on');
        foreach ($statuses as $state => $statename) {
            $configname = "filter_$statename";
            if (!empty($config->{$configname})) {
                $filters = explode(',', $config->{$configname});
                foreach ($filters as $filter) {
                    $this->set_filter_state($filter, $state);
                }
            }
        }
        $this->update_filter_order();
        reset_text_filters_cache();
    }

    protected function show_hide_blocks($names, $visible) {
        global $DB;
        if (empty($names)) {
            return;
        }
        list ($sql, $params) = $DB->get_in_or_equal($names);
        $DB->set_field_select('block', 'visible', $visible, "name $sql", $params);
    }

    protected function update_blocks() {
        $this->output("Updating block configuration");
        $config = $this->get_config();
        if (isset($config->block_enable)) {
            $this->show_hide_blocks(explode(',', $config->block_enable), 1);
        }
        if (isset($config->block_disable)) {
            $this->show_hide_blocks(explode(',', $config->block_disable), 0);
        }
    }

    // See also admin/modules.php
    protected function show_hide_activities($names, $visible) {
        global $DB;
        $modules = $DB->get_records_list("modules", "name", $names);
        foreach ($modules as $module) {
            if ($module->visible == $visible) {
                continue; // nothing to do
            }
            $DB->set_field("modules", "visible", $visible, array("id"=>$module->id));

            if ($visible) {
                $DB->set_field('course_modules', 'visible', '1', array('visibleold'=>1, 'module'=>$module->id)); // Get the previous saved visible state for the course module.
                // Increment course.cacherev for courses where we just made something visible.
                // This will force cache rebuilding on the next request.
                increment_revision_number('course', 'cacherev',
                                  "id IN (SELECT DISTINCT course
                                    FROM {course_modules}
                                   WHERE visible=1 AND module=?)",
                                           array($module->id));
            } else {
                // Remember the visibility status in visibleold
                // and hide...
                $sql = "UPDATE {course_modules}
                   SET visibleold=visible, visible=0
                 WHERE module=?";
                $DB->execute($sql, array($module->id));
                // Increment course.cacherev for courses where we just made something invisible.
                // This will force cache rebuilding on the next request.
                increment_revision_number('course', 'cacherev',
                              "id IN (SELECT DISTINCT course
                                FROM {course_modules}
                               WHERE visibleold=1 AND module=?)",
                                               array($module->id));
            }
        }
    }

    protected function update_activities() {
        $this->output("Updating activity configuration");
        $config = $this->get_config();
        if (isset($config->activity_enable)) {
            $this->show_hide_activities(explode(',', $config->activity_enable), 1);
        }
        if (isset($config->activity_disable)) {
            $this->show_hide_activities(explode(',', $config->activity_disable), 0);
        }
    }

    public function update() {
        $this->update_filters();
        $this->update_blocks();
        $this->update_activities();

        admin_get_root(true, false);  // settings not required - only pages
    }
}