<?php
/*
 * @package		mod-skillsoftmi
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version  = 2018022603;  // If version == 0 then module will not be installed
$plugin->requires = 2016120500;  // Requires this Moodle version (2.8)
$plugin->cron     = 60;           // Period for cron to check this module (secs)
$plugin->component = 'mod_skillsoftmi'; // Full name of the plugin (used for diagnostics)
$plugin->maturity = MATURITY_STABLE; // This is considered as ready for production sites.
