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
 * \local_autoconfig\geoip class
 *
 * Download geoip database
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class geoip extends base {

    static $geoipurl = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz';

    // How often in seconds to redownload the db
    static $validtime = 2419200;  // 4 weeks

    public function update() {
        global $CFG;

        $geoipdir = $CFG->dataroot.'/geoip';
        $geoipdb = $geoipdir . '/GeoLiteCity.dat';

        // Create geoip directory if it doesn't exist
        if (!is_dir($geoipdir)) {
            $this->output("Creating geoip directory");
            mkdir($geoipdir, $CFG->directorypermissions);
        }

        // Download database if necessary
        $mtime = @filemtime($geoipdb);
        if (!$mtime || $mtime < time() - self::$validtime) {
            $this->output("Downloading GeoIP data...");
            $contents = file_get_contents(self::$geoipurl);
            if ($contents) {
                @rename($geoipdb, $geoipdb.'.bak');
                file_put_contents($geoipdb.'.gz', $contents);
                $return = 0;
                system('gunzip '. $geoipdb . '.gz', $return);
                if ($return !== 0) {
                    $this->output("Corrupt GeoIP data; restoring previous version!");
                    @rename($geoipdb.'.bak', $geoipdb);
                }
            } else {
                $this->output("Error retrieving GeoIP data!");
            }
        } else {
            $this->output("Not refreshing GeoIP database.");
        }

    }
}