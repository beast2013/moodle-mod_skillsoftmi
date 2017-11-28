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

$id = required_param('id', PARAM_INT);    // skillsoft ID, or
$user = optional_param('user', '', PARAM_BOOL);  // User report
$attempt = optional_param('attempt', '', PARAM_INT);  // attempt number


if (!empty($id)) {
	if (! $cm = get_coursemodule_from_id('skillsoftmi', $id)) {
        print_error('Course Module ID was incorrect');
    }
    if (! $course = $DB->get_record('course', array('id'=> $cm->course))) {
        print_error('Course is misconfigured');
    }
    if (! $skillsoft = $DB->get_record('skillsoftmi', array('id'=>$cm->instance))) {
        print_error('Course module is incorrect');
    }
	
} else {
	print_error('A required parameter is missing');
}
$url = new moodle_url('/mod/skillsoftmi/report.php',array('id'=>$cm->id));

$PAGE->set_url($url);

require_login($course->id, false, $cm);

$contextmodule = context_MODULE::instance($cm->id);


//Retrieve the localisation strings
$strskillsoft = get_string('modulename', 'skillsoftmi');
$strskillsofts = get_string('modulenameplural', 'skillsoftmi');
$strskillsoftid = get_string('skillsoft_assetid', 'skillsoftmi');
$strskillsoftsummary = get_string('skillsoft_summary', 'skillsoftmi');
$strlastmodified = get_string('lastmodified');

$strreport  = get_string('skillsoft_report', 'skillsoftmi');
$strattempt  = get_string('skillsoft_attempt', 'skillsoftmi');
$strallattempt  = get_string('skillsoft_allattempt', 'skillsoftmi');

//Navigation Links
$PAGE->set_title("$course->shortname: ".format_string($skillsoft->name));
$PAGE->set_heading($course->fullname);

//If user has viewreport permission enable "Report" link allowing viewing all usage of asset
if (has_capability('mod/skillsoftmi:viewreport', $contextmodule)) {
	$PAGE->navbar->add($strreport, new moodle_url('/mod/skillsoftmi/report.php', array('id'=>$cm->id)));
} else {
	$PAGE->navbar->add($strreport);
}

if ($user) {
	if (empty($attempt)) {
		$PAGE->navbar->add($strallattempt, new moodle_url('/mod/skillsoftmi/report.php', array('id'=>$cm->id,'user'=>'true')));
	} else {
		$PAGE->navbar->add($strallattempt, new moodle_url('/mod/skillsoftmi/report.php', array('id'=>$cm->id,'user'=>'true')));
		$PAGE->navbar->add($strattempt.' '.$attempt);
	}
}
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($skillsoft->name));

if ($user) {
	$currenttab = 'reports';
	require($CFG->dirroot . '/mod/skillsoftmi/tabs.php');
	//Print User Specific Data
	// Print general score data
	$table = new html_table();
	$table->tablealign='center';
	$table->head = array(
	$strattempt,
	get_string('skillsoft_firstaccess','skillsoftmi'),
	get_string('skillsoft_lastaccess','skillsoftmi'),
	get_string('skillsoft_completed','skillsoftmi'),
	get_string('skillsoft_lessonstatus','skillsoftmi'),
	get_string('skillsoft_totaltime','skillsoftmi'),
	get_string('skillsoft_firstscore','skillsoftmi'),
	get_string('skillsoft_currentscore','skillsoftmi'),
	get_string('skillsoft_bestscore','skillsoftmi'),
	get_string('skillsoft_accesscount','skillsoftmi'),
	);
	$table->align = array('left','left', 'left', 'left', 'center','center','right','right','right','right');
	$table->wrap = array('', '', '','','nowrap','nowrap','nowrap','nowrap','nowrap','nowrap');
	$table->width = '80%';
	$table->size = array('*', '*', '*', '*', '*', '*', '*', '*', '*', '*');


	if (empty($attempt)) {

		//Show all attempts
		skillsoftmi_event_log(SKILLSOFTmi_EVENT_REPORT_VIEWED, $skillsoft, $contextmodule, $cm);
		
		$maxattempts = skillsoftmi_get_last_attempt($skillsoft->id,$USER->id);
		if ($maxattempts == 0) {
			$maxattempts = 1;
		}

		for ($a = $maxattempts; $a > 0; $a--) {
			$row = array();
			$score = '&nbsp;';
			if ($trackdata = skillsoftmi_get_tracks($skillsoft->id,$USER->id,$a)) {
				$row[] = '<a href="'.new moodle_url('/mod/skillsoftmi/report.php', array('id'=>$cm->id,'user'=>'true','attempt'=>$trackdata->attempt)).'">'.$trackdata->attempt.'</a>';
				$row[] = isset($trackdata->{'[SUMMARY]firstaccess'}) ? userdate($trackdata->{'[SUMMARY]firstaccess'}):'';
				$row[] = isset($trackdata->{'[SUMMARY]lastaccess'}) ? userdate($trackdata->{'[SUMMARY]lastaccess'}):'';
				if ($skillsoft->completable == true) {
					$row[] = isset($trackdata->{'[SUMMARY]completed'}) ? userdate($trackdata->{'[SUMMARY]completed'}):'';
					$row[] = isset($trackdata->{'[CORE]lesson_status'}) ? $trackdata->{'[CORE]lesson_status'}:'';
					$row[] = isset($trackdata->{'[CORE]time'}) ? $trackdata->{'[CORE]time'}:'';
					$row[] = isset($trackdata->{'[SUMMARY]firstscore'}) ? $trackdata->{'[SUMMARY]firstscore'}:'';
					$row[] = isset($trackdata->{'[SUMMARY]currentscore'}) ? $trackdata->{'[SUMMARY]currentscore'}:'';
					$row[] = isset($trackdata->{'[SUMMARY]bestscore'}) ? $trackdata->{'[SUMMARY]bestscore'}:'';
				} else {
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
					$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
				}
				$row[] = isset($trackdata->{'[SUMMARY]accesscount'}) ? $trackdata->{'[SUMMARY]accesscount'} :'';
				$table->data[] = $row;
			}
		}

	} else {
		skillsoftmi_event_log(SKILLSOFTmi_EVENT_REPORT_VIEWED, $skillsoft, $contextmodule, $cm);
		$score = '&nbsp;';
		if ($trackdata = skillsoftmi_get_tracks($skillsoft->id,$USER->id,$attempt)) {
			$row[] = '<a href="'.new moodle_url('/mod/skillsoftmi/report.php', array('id'=>$cm->id,'user'=>'true','attempt'=>$trackdata->attempt)).'">'.$trackdata->attempt.'</a>';
			$row[] = isset($trackdata->{'[SUMMARY]firstaccess'}) ? userdate($trackdata->{'[SUMMARY]firstaccess'}):'';
			$row[] = isset($trackdata->{'[SUMMARY]lastaccess'}) ? userdate($trackdata->{'[SUMMARY]lastaccess'}):'';
			if ($skillsoft->completable == true) {
				$row[] = isset($trackdata->{'[SUMMARY]completed'}) ? userdate($trackdata->{'[SUMMARY]completed'}):'';
				$row[] = isset($trackdata->{'[CORE]lesson_status'}) ? $trackdata->{'[CORE]lesson_status'}:'';
				$row[] = isset($trackdata->{'[CORE]time'}) ? $trackdata->{'[CORE]time'}:'';
				$row[] = isset($trackdata->{'[SUMMARY]firstscore'}) ? $trackdata->{'[SUMMARY]firstscore'}:'';
				$row[] = isset($trackdata->{'[SUMMARY]currentscore'}) ? $trackdata->{'[SUMMARY]currentscore'}:'';
				$row[] = isset($trackdata->{'[SUMMARY]bestscore'}) ? $trackdata->{'[SUMMARY]bestscore'}:'';
			} else {
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
				$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
			}
			$row[] = isset($trackdata->{'[SUMMARY]accesscount'}) ? $trackdata->{'[SUMMARY]accesscount'} :'';
			$table->data[] = $row;
		}
	}
} else {
	require_capability('mod/skillsoftmi:viewreport', $contextmodule);
	skillsoftmi_event_log(SKILLSOFTmi_EVENT_REPORT_VIEWED, $skillsoft, $contextmodule, $cm);
	

	$currenttab = 'allreports';
	require($CFG->dirroot . '/mod/skillsoftmi/tabs.php');

	//Just report on the activity
	//SQL to get all get all userid/skillsoftid records
	$sql = "SELECT ai.userid, ai.skillsoftid
                        FROM {skillsoftmi_au_track} ai
                        WHERE ai.skillsoftid = ?
                        GROUP BY ai.userid,ai.skillsoftid";
	$params = array($skillsoft->id);


	$table = new html_table();
	$table->tablealign = 'center';
	$table->head = array(
	get_string('name'),
	$strattempt,
	get_string('skillsoft_firstaccess','skillsoftmi'),
	get_string('skillsoft_lastaccess','skillsoftmi'),
	get_string('skillsoft_completed','skillsoftmi'),
	get_string('skillsoft_lessonstatus','skillsoftmi'),
	get_string('skillsoft_totaltime','skillsoftmi'),
	get_string('skillsoft_firstscore','skillsoftmi'),
	get_string('skillsoft_currentscore','skillsoftmi'),
	get_string('skillsoft_bestscore','skillsoftmi'),
	get_string('skillsoft_accesscount','skillsoftmi'),
	);
	$table->align = array('left','left','left', 'left', 'left', 'center','center','right','right','right','right');
	$table->wrap = array('','','', '','','nowrap','nowrap','nowrap','nowrap','nowrap','nowrap');
	$table->width = '80%';
	$table->size = array('*','*','*', '*', '*', '*', '*', '*', '*', '*', '*');

	if ($skillsoftusers=$DB->get_records_sql($sql,$params))
	{
		foreach($skillsoftusers as $skillsoftuser){
				
			$maxattempts = skillsoftmi_get_last_attempt($skillsoft->id,$skillsoftuser->userid);
			if ($maxattempts == 0) {
				$maxattempts = 1;
			}

			for ($a = $maxattempts; $a > 0; $a--) {
				$row = array();
				$userdata = $DB->get_record('user',array('id'=>$skillsoftuser->userid),'id, firstname, lastname, picture, imagealt, email');

				$row[] = $OUTPUT->user_picture($userdata, array('courseid'=>$course->id)).' '.'<a href="'.$CFG->wwwroot.'/user/view.php?id='.$skillsoftuser->userid.'&amp;course='.$course->id.'">'.fullname($userdata).'</a>';

				$score = '&nbsp;';
				if ($trackdata = skillsoftmi_get_tracks($skillsoftuser->skillsoftid,$skillsoftuser->userid,$a)) {
					$row[] = $trackdata->attempt;
					$row[] = isset($trackdata->{'[SUMMARY]firstaccess'}) ? userdate($trackdata->{'[SUMMARY]firstaccess'}):'';
					$row[] = isset($trackdata->{'[SUMMARY]lastaccess'}) ? userdate($trackdata->{'[SUMMARY]lastaccess'}):'';
					if ($skillsoft->completable == true) {
						$row[] = isset($trackdata->{'[SUMMARY]completed'}) ? userdate($trackdata->{'[SUMMARY]completed'}):'';
						$row[] = isset($trackdata->{'[CORE]lesson_status'}) ? $trackdata->{'[CORE]lesson_status'}:'';
						$row[] = isset($trackdata->{'[CORE]time'}) ? $trackdata->{'[CORE]time'}:'';
						$row[] = isset($trackdata->{'[SUMMARY]firstscore'}) ? $trackdata->{'[SUMMARY]firstscore'}:'';
						$row[] = isset($trackdata->{'[SUMMARY]currentscore'}) ? $trackdata->{'[SUMMARY]currentscore'}:'';
						$row[] = isset($trackdata->{'[SUMMARY]bestscore'}) ? $trackdata->{'[SUMMARY]bestscore'}:'';
					} else {
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
						$row[] = $OUTPUT->help_icon( 'skillsoft_noncompletable','skillsoftmi',get_string('skillsoft_na','skillsoftmi'));
					}
					$row[] = isset($trackdata->{'[SUMMARY]accesscount'}) ? $trackdata->{'[SUMMARY]accesscount'} :'';
				} else {
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
					$row[] = '&nbsp;';
				}
				$table->data[] = $row;
			}
		}
	}
}
echo $OUTPUT->box_start('generalbox boxaligncenter');
echo html_writer::table($table);
echo $OUTPUT->box_end();

if (empty($noheader)) {
	echo $OUTPUT->footer();
}