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
 * Web auto configuration script
 *
 * @package    local
 * @subpackage autoconfig
 * @copyright  2014 University of Wisconsin
 * @author     Matt petro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);
define('LOCAL_AUTOCONFIG', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');

// Check access permissions.
$systemcontext = context_system::instance();
require_login();
require_capability('moodle/site:config', $systemcontext);

// send mime type and encoding
@header('Content-Type: text/plain; charset=utf-8');

// we do not want html markup in emulated CLI
@ini_set('html_errors', 'off');

$configtypes = optional_param_array('types', array(), PARAM_ALPHA);


// Ensure errors are well explained
$CFG->debug = DEBUG_NORMAL;

$PAGE->set_pagelayout('maintenance');
$PAGE->set_url('/local/autoconfig/autoconfig.php');

$progress = new text_progress_trace();

// Auto configure site
$autoconfig = new \local_autoconfig\autoconfig();
$autoconfig->execute($progress, $configtypes);

$progress->finished();