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
 * \local_autoconfig\rssfeeds class
 *
 * Configure a news rss feed which is present on the front and my-moodle pages.
 * In order for this to work, the site and my-moodle pages must have an rss client block
 * added first.
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->rssnewsfeed = array('url'=>'feedurl',
 *                                             'autodiscovery'=>1,
 *                                             'title'=>'Feedtitle',
 *                                             'description'=>'',
 *                                             'shared'=>'1');
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rssfeeds extends base {

    protected function update_rss_feed($data) {
        global $DB;
        $rssfeed = (object) $data;
        $rec = $DB->get_record('block_rss_client', array('url'=>$rssfeed->url, 'shared'=>1), 'id');
        if ($rec) {
            $rssfeed->id = $rec->id;
            $this->output("Updating feed '$rssfeed->url'");
            $DB->update_record('block_rss_client', $rssfeed);
        } else {
            $this->output("Creating feed '$rssfeed->url'");
            $rssfeed->id = $DB->insert_record('block_rss_client', $rssfeed);
        }
        return $rssfeed->id;
    }

    protected function configure_rss_block_instance($rssid, $context) {
        global $DB;
        $block = $DB->get_record('block_instances', array('blockname'=>'rss_client', 'parentcontextid'=>$context->id), '*', IGNORE_MISSING);
        if (!$block) {
            return;
        }
        $this->output("Updating block configuration in context $context->id",1);
        $block->defaultweight = -3;

        // Set block instance config
        $blockconfig = new \stdClass();
        $blockconfig->display_description = 0;
        $blockconfig->shownumentries = 5;
        $blockconfig->rssid = array($rssid);
        $blockconfig->title = "";
        $blockconfig->block_rss_client_show_channel_link = 0;
        $blockconfig->block_rss_client_show_channel_image = 0;

        $block->configdata = base64_encode(serialize($blockconfig));
        $DB->update_record('block_instances', $block);

    }

    public function update() {
        global $DB, $SITE;
        $config = $this->get_config();

        if (!empty($config->rssnewsfeed)) {
            $rssid = $this->update_rss_feed($config->rssnewsfeed);
            // SITE course
            $this->configure_rss_block_instance($rssid, \context_course::instance($SITE->id));
            // Default my moodle page
            $this->configure_rss_block_instance($rssid, \context_system::instance());
        }
    }
}