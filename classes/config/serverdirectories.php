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
 * \local_autoconfig\languagepacks class
 *
 * Create various server directories
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class serverdirectories extends base {

    public function update() {
        global $CFG;

        // Create quarantine directory if it doesn't exist
        if (!empty($CFG->quarantinedir) && !is_dir($CFG->quarantinedir)) {
            $this->output("Creating quarantine directory");
            mkdir($CFG->quarantinedir, $CFG->directorypermissions);
        }

        // Create a symlink for mod_certificate, if the global_config destination exists.
        // This is how we keep certificate resources synchronized between sites.
        $globalcertdir = '/home/moodle-data/global_config/mod_certificate';
        $certdir = $CFG->dataroot . '/mod/certificate';
        if (file_exists($globalcertdir) && !file_exists($certdir)) {
            $this->output("Linking certificate upload directory to $globalcertdir");
            make_upload_directory('mod');
            symlink($globalcertdir, $certdir);
        }
    }
}