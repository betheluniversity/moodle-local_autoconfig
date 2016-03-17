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

/**
 * \local_autoconfig\languagepacks class
 *
 * Configure language packs
 *
 * Configuration:
 *
 * $CFG->local_autoconfig->languagepacks : comma-separated list of language packs to install
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class languagepacks extends base {

    protected function install($lang) {
        $this->output("Installing language pack: $lang");
        $installer = new \lang_installer($lang);
        $results = $installer->run();
        foreach ($results as $langcode => $langstatus) {
            if ($langstatus === \lang_installer::RESULT_DOWNLOADERROR) {
                $a       = new \stdClass();
                $a->url  = $installer->lang_pack_url($langcode);
                $a->dest = $CFG->dataroot.'/lang';
                $this->output(get_string('remotedownloaderror', 'error', $a));
            }
        }
    }

    protected function is_installed($lang) {
        static $installedlangs;

        if (is_null($installedlangs)) {
            $installedlangs = get_string_manager()->get_list_of_translations(true);
        }
        return isset($installedlangs[$lang]);
    }

    public function update() {
        make_upload_directory('lang');
        $config = $this->get_config();

        $this->output('Installing language packs');
        if (!empty($config->languagepacks_install)) {
            $langs = explode(',', $config->languagepacks_install);
            if ($langs) {
                foreach ($langs as $lang) {
                    if (!$this->is_installed($lang)) {
                        $this->install($lang);
                    }
                }
            }
        }
    }
}