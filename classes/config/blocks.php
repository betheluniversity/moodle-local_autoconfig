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

require_once($CFG->libdir.'/blocklib.php');

/**
 * \local_autoconfig\blocks class
 *
 * Configure blocks
 *
 * Configuration:  (See config-dist.php for information on the format)
 *
 * $CFG->local_autoconfig->defaultblocks_site = 'rss_client:uwservices,participants,messages';
 * $CFG->local_autoconfig->defaultblocks_my = 'rss_client:course_overview_uwmoodle:uwservices,calendar_upcoming,private_files,messages';
 *
 * A full block refresh is triggered when either of these change.
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class blocks extends base {

    // Same as blocks_parse_default_blocks_list() but with content region support
    protected function parse_default_my_blocks_list($blocksstr) {
        $blocks = array();
        $bits = explode(':', $blocksstr);
        if (!empty($bits)) {
            $leftbits = trim(array_shift($bits));
            if ($leftbits != '') {
                $blocks[BLOCK_POS_LEFT] = explode(',', $leftbits);
            }
        }
        if (!empty($bits)) {
            $rightbits =trim(array_shift($bits));
            if ($rightbits != '') {
                $blocks['content'] = explode(',', $rightbits);
            }
        }
        if (!empty($bits)) {
            $rightbits =trim(array_shift($bits));
            if ($rightbits != '') {
                $blocks[BLOCK_POS_RIGHT] = explode(',', $rightbits);
            }
        }
        return $blocks;
    }

    public function update() {
        global $SITE, $CFG, $DB;
        $config = $this->get_config();

        // Site home blocks
        $rebuildsiteblocks = !empty($config->defaultblocks_site)
                      && $config->defaultblocks_site != get_config('local_autoconfig', 'defaultblocks_site_saved');
        if ($rebuildsiteblocks) {
            $this->output("Rebuilding site blocks");
            $context = \context_course::instance($SITE->id);
            blocks_delete_all_for_context($context->id);

            // Ugly hack
            $CFG->defaultblocks_override = $config->defaultblocks_site;
            blocks_add_default_course_blocks($SITE);
            set_config('defaultblocks_site_saved', $config->defaultblocks_site, 'local_autoconfig');
        }

        // Default My page blocks
        $rebuildmyblocks = !empty($config->defaultblocks_my)
            && $config->defaultblocks_my != get_config('local_autoconfig', 'defaultblocks_my_saved');
        if ($rebuildmyblocks) {
            // The default My page is a little different from a course, so we have to write custom code.
            $this->output("Rebuilding default my blocks");
            $context = \context_system::instance();

            // Delete all block instances.
            // We can't use blocks_delete_all_for_context since the admin tree and nav blocks are also in the system context.
            $instances = $DB->get_records('block_instances', array('parentcontextid' => $context->id, 'pagetypepattern'=>'my-index'));
            foreach ($instances as $instance) {
                blocks_delete_instance($instance);
            }

            if ($defaultmypage = $DB->get_record('my_pages', array('userid'=>null, 'name'=>'__default', 'private'=>1))) {
                $subpagepattern = $defaultmypage->id;
            } else {
                $subpagepattern = null;
            }

            // Parse list of blocks
            $blocknames = $this->parse_default_my_blocks_list($config->defaultblocks_my);
            $page = new \moodle_page();
            $page->set_context($context);

            // Add the new blocks
            $page->blocks->add_blocks($blocknames, 'my-index', $subpagepattern, false);
            set_config('defaultblocks_my_saved', $config->defaultblocks_my, 'local_autoconfig');
        }

    }
}