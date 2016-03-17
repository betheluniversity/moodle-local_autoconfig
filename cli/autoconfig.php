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
 * CLI auto configuration script
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
define('LOCAL_AUTOCONFIG', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');

$opts = getopt('hyc:');
if (isset($arguments['h'])) {
    cli_error('Usage: autoconfig.php -c type1,type2,...');
}

$configtypes = array();
if (isset($opts['c'])) {
    $configtypes = explode(',',$opts['c']);
}


// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

$progress = new text_progress_trace();

// Auto configure site
$autoconfig = new \local_autoconfig\autoconfig();
$autoconfig->execute($progress, $configtypes);

$progress->finished();