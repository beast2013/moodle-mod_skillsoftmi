<?php
/*
 * @package		mod-skillsoftmi
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/skillsoftmi/locallib.php');


//TODO: Find how to do this in Moodle2

$PAGE->requires->js('/mod/skillsoftmi/skillsoft.js');
$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // skillsoft asset instance ID - it should be named as the first character of the module

if (!empty($id)) {
	if (! $cm = get_coursemodule_from_id('skillsoftmi', $id)) {
		print_error('Course Module ID was incorrect');
	}
	if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
		print_error('Course is misconfigured');
	}
	if (! $skillsoft = $DB->get_record('skillsoftmi', array('id'=> $cm->instance))) {
		print_error('Course module is incorrect');
	}
} else if (!empty($a)) {
	if (! $skillsoft = $DB->get_record('skillsoftmi', array('id'=> $a))) {
		print_error('Course module is incorrect');
	}
	if (! $course = $DB->get_record('course', array('id'=> $skillsoft->course))) {
		print_error('Course is misconfigured');
	}
	if (! $cm = get_coursemodule_from_instance('skillsoftmi', $skillsoft->id, $course->id)) {
		print_error('Course Module ID was incorrect');
	}
} else {
	print_error('A required parameter is missing');
}
$url = new moodle_url('/mod/skillsoftmi/view.php', array('id'=>$cm->id));

require_login($course->id, false, $cm);

$context = context_COURSE::instance($course->id);

$strskillsofts = get_string('modulenameplural', 'skillsoftmi');
$strskillsoft  = get_string('modulename', 'skillsoftmi');

if (isset($SESSION->skillsoftmi_id)) {
	unset($SESSION->skillsoftmi_id);
}

$SESSION->skillsoftmi_id = $skillsoft->id;
$SESSION->skillsoftmi_status = 'Not Initialized';
$SESSION->skillsoftmi_mode = 'normal';
$SESSION->skillsoftmi_attempt = 1;

$pagetitle = strip_tags($course->shortname.': '.format_string($skillsoft->name).' ('.format_string($skillsoft->assetid).')');

skillsoftmi_event_log(SKILLSOFTmi_EVENT_ACTIVITY_VIEWED, $skillsoft, $context, $cm);

$PAGE->set_url($url);
//
// Print the page header
//
$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

$attempt = skillsoftmi_get_last_attempt($skillsoft->id, $USER->id);
if ($attempt == 0) {
	$attempt = 1;
}
$currenttab = 'info';
require($CFG->dirroot . '/mod/skillsoftmi/tabs.php');
$viewreport=get_string('skillsoft_viewreport','skillsoftmi');



//echo '<div class="reportlink"><a href="'.new moodle_url('/mod/skillsoft/report.php', array('id'=>$skillsoft->id, 'user'=>'true', 'attempt'=>$attempt)).'">'.$viewreport.'</a></div>';
// Print the main part of the page
//print_heading(format_string($skillsoft->name).' ('.format_string($skillsoft->assetid).')');

if (!empty($skillsoft->intro)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_summary', 'skillsoftmi').'</h3>'.format_text($skillsoft->intro), 'generalbox boxaligncenter boxwidthwide', 'summary');
}
if (!empty($skillsoft->audience)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_audience', 'skillsoftmi').'</h3>'.format_text($skillsoft->audience), 'generalbox boxaligncenter boxwidthwide', 'audience');
}
if (!empty($skillsoft->prereq)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_prereq', 'skillsoftmi').'</h3>'.format_text($skillsoft->prereq), 'generalbox boxaligncenter boxwidthwide', 'prereq');
}
if (!empty($skillsoft->duration)) {
	echo $OUTPUT->box('<h3>'.get_string('skillsoft_duration', 'skillsoftmi').'</h3>'.format_text($skillsoft->duration), 'generalbox boxaligncenter boxwidthwide', 'duration');
}
echo $OUTPUT->box(skillsoftmi_view_display($skillsoft, $USER, true), 'generalbox boxaligncenter boxwidthwide', 'courselaunch');

echo $OUTPUT->footer();