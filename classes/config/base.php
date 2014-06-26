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

/**
 * \local_autoconfig\base class
 *
 * Base class for configuration classes.
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {

    protected $progress;

    function __construct(\progress_trace $progress = null) {
        if ($progress) {
            $this->progress = $progress;
        } else {
            $this->progress = new \null_progress_trace();
        }
    }

    protected function output($message, $level=0) {
        $this->progress->output($message, $level+1);
    }

    public function get_config() {
        global $CFG;

        if (isset($CFG->local_autoconfig)) {
            return $CFG->local_autoconfig;
        }
        return new \stdClass;
    }


    abstract public function update();
}