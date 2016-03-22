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
 * Auto configuration settings.
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2016 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $PAGE;

$modname = 'local_autoconfig';
$plugin = 'local/autoconfig';

if ($hassiteconfig) {
    $autoconfig = new admin_externalpage('autoconfig', 'Run autoconfig', "$CFG->wwwroot/local/autoconfig/autoconfig.php", array('moodle/site:config'));
    $ADMIN->add('localplugins', $autoconfig);
}