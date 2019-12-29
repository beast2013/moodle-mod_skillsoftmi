<?php
// This file is part of Moodle - http://moodle.org/
//
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
 * Privacy class for requesting user data.
 *
 * @package    mod_skillsoftmi
 * @copyright  2019 Leslie Shier <lessheir@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_skillsoftmi\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy class for requesting user data.
 *
 * @copyright  2019 Leslie Shier <lesshier@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
  \core_privacy\local\metadata\provider,
  \core_privacy\local\request\core_userlist_provider,
  \core_privacy\local\request\plugin\provider
{

  /**
   * Return the fields which contain personal data.
   *
   * @param   collection $collection The initialised collection to add items to.
   * @return  collection A listing of user data stored through this system.
   */
  public static function get_metadata(collection $collection): collection
  {
    $collection->add_database_table('skillsoftmi_au_track', [
      'userid' => 'privacy:metadata:userid',
      'attempt' => 'privacy:metadata:attempt',
      'element' => 'privacy:metadata:au_track:element',
      'value' => 'privacy:metadata:au_track:value',
      'timemodified' => 'privacy:metadata:timemodified'
    ], 'privacy:metadata:skillsoftmi_au_track');

    $collection->add_database_table('skillsoftmi_session_track', [
      'userid' => 'privacy:metadata:userid',
      'sessionid' => 'privacy:metadata:session_track:sessionid',
      'timecreated' => 'privacy:metadata:session_track:timecreated'
    ], 'privacy:metadata:skillsoftmi_session_track');

    $collection->add_database_table('skillsoftmi_report_results', [
      'loginname' => 'privacy:metadata:report_results:loginname',
      'lastname' => 'privacy:metadata:report_results:lastname',
      'firstname' => 'privacy:metadata:report_results:firstname',
      'firstaccessdate' => 'privacy:metadata:report_results:firstaccessdate',
      'lastaccessdate' => 'privacy:metadata:report_results:lastaccessdate',
      'completeddate' => 'privacy:metadata:report_results:completeddate',
      'firstscore' => 'privacy:metadata:report_results:firstscore',
      'currentscore' => 'privacy:metadata:report_results:currentscore',
      'bestscore' => 'privacy:metadata:report_results:bestscore',
      'lessionstatus' => 'privacy:metadata:report_results:lessionstatus',
      'duration' => 'privacy:metadata:report_results:duration',
      'accesscount' => 'privacy:metadata:report_results:accesscount',
      'userid' => 'privacy:metadata:userid',
      'processed' => 'privacy:metadata:report_results:processed',
      'attempt' => 'privacy:metadata:attempt'
    ], 'privacy:metadata:skillsoftmi_report_results');

    $collection->add_database_table('skillsoftmi_tdr', [
      'timestamp' => 'privacy:metadata:tdr:timestamp',
      'userid' => 'privacy:metadata:userid',
      'username' => 'privacy:metadata:tdr:username',
      'reset' => 'privacy:metadata:tdr:reset',
      'format' => 'privacy:metadata:tdr:format',
      'data' => 'privacy:metadata:tdr:data',
      'context' => 'privacy:metadata:tdr:context',
      'processed' => 'privacy:metadata:tdr:processed'
    ], 'privacy:metadata:skillsoftmi_tdr');

    $collection->add_external_location_link('aicc', [
      'data' => 'privacy:metadata:aicc:data'
    ], 'privacy:metadata:aicc:externalpurpose');

    return $collection;
  }

  /**
   * Get the list of contexts that contain user information for the specified user.
   *
   * @param int $userid The user to search.
   * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
   */
  public static function get_contexts_for_userid(int $userid): contextlist
  {
    $sql1 = "SELECT ctx.id
                  FROM {%s} ss
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                   AND ctx.contextlevel = :modlevel
                 WHERE ss.userid = :userid";

    $sql2 = "SELECT ctx.id
                  FROM {%s} ss
                  JOIN {skillsoftmi} s
                    ON s.assetid = ss.assetid
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = s.id
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                   AND ctx.contextlevel = :modlevel
                 WHERE s.courseid IN (SELECT e.courseid FROM {enrol} e JOIN {user_enrolments} ue ON ue.enrolid = e.id WHERE ue.userid = :userid)
                   AND ss.userid = :userid";

    $params = ['modlevel' => CONTEXT_MODULE, 'userid' => $userid];
    $contextlist = new contextlist();
    $contextlist->add_from_sql(sprintf($sql1, 'skillsoftmi_au_track'), $params);
    $contextlist->add_from_sql(sprintf($sql1, 'skillsoftmi_session_track'), $params);
    $contextlist->add_from_sql(sprintf($sql2, 'skillsoftmi_tdr'), $params);
    $contextlist->add_from_sql(sprintf($sql2, 'skillsoftmi_report_results'), $params);

    return $contextlist;
  }

  /**
   * Get the list of users who have data within a context.
   *
   * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
   */
  public static function get_users_in_context(userlist $userlist)
  {
    $context = $userlist->get_context();

    if (!is_a($context, \context_module::class)) {
      return;
    }

    $sqla = "SELECT ss.userid
                  FROM {%s} ss
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                   AND ctx.contextlevel = :modlevel
                 WHERE ctx.id = :contextid";

    $sqlb = "SELECT ss.userid
                  FROM {%s} ss
                  JOIN {skillsoftmi} s
                    ON s.assetid = ss.assetid
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = s.id
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                   AND ctx.contextlevel = :modlevel
                 WHERE ss.userid IN (SELECT eu.userid FROM {enrol} e JOIN {user_enrolments} ue ON ue.enrolid = e.id WHERE e.courseid = cm.courseid)
                   AND ctx.id = :contextid";

    $params = ['modlevel' => CONTEXT_MODULE, 'contextid' => $context->id];

    $userlist->add_from_sql('userid', sprintf($sqla, 'skillsoftmi_au_track'), $params);
    $userlist->add_from_sql('userid', sprintf($sqla, 'skillsoftmi_session_track'), $params);
    $userlist->add_from_sql('userid', sprintf($sqlb, 'skillsoftmi_tdr'), $params);
    $userlist->add_from_sql('userid', sprintf($sqlb, 'skillsoftmi_report_results'), $params);
  }

  /**
   * Export all user data for the specified user, in the specified contexts.
   *
   * @param approved_contextlist $contextlist The approved contexts to export information for.
   */
  public static function export_user_data(approved_contextlist $contextlist)
  {
    global $DB;

    // Remove contexts different from COURSE_MODULE.
    $contexts = array_reduce($contextlist->get_contexts(), function ($carry, $context) {
      if ($context->contextlevel == CONTEXT_MODULE) {
        $carry[] = $context->id;
      }
      return $carry;
    }, []);

    if (empty($contexts)) {
      return;
    }

    $user = $contextlist->get_user();
    $userid = $user->id;
    // Get skillsoftmi data.
    foreach ($contexts as $contextid) {
      $context = \context::instance_by_id($contextid);
      $data = helper::get_context_data($context, $user);
      writer::with_context($context)->export_data([], $data);
      helper::export_context_files($context, $user);
    }

    // Get au_track data.
    list($insql, $inparams) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED);
    $sql = "SELECT ss.id,
                       ss.attempt,
                       ss.element,
                       ss.value,
                       ss.timemodified,
                       ctx.id as contextid
                  FROM {skillsoftmi_au_track} ss
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ctx.id $insql
                   AND ss.userid = :userid";
    $params = array_merge($inparams, ['userid' => $userid]);

    $alldata = [];
    $autracks = $DB->get_recordset_sql($sql, $params);
    foreach ($autracks as $track) {
      $alldata[$track->contextid][$track->attempt][] = (object) [
        'element' => $track->element,
        'value' => $track->value,
        'timemodified' => transform::datetime($track->timemodified),
      ];
    }
    $autracks->close();

    // The scoes_track data is organised in: {Course name}/{SkillSoftmi activity name}/{My attempts}/{Attempt X}/data.json
    // where X is the attempt number.
    array_walk($alldata, function ($attemptsdata, $contextid) {
      $context = \context::instance_by_id($contextid);
      array_walk($attemptsdata, function ($data, $attempt) use ($context) {
        $subcontext = [
          get_string('skillsoft_myattempts', 'skillsoftmi'),
          get_string('skillsoft_attempt', 'skillsoftmi') . " $attempt"
        ];
        writer::with_context($context)->export_data(
          $subcontext,
          (object) ['autrack' => $data]
        );
      });
    });

    // Get session_track data.
    $sql = "SELECT ss.id,
                       ss.sessionid,
                       ss.timecreated,
                       ctx.id as contextid
                  FROM {skillsoftmi_session_track} ss
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ctx.id $insql
                   AND ss.userid = :userid";
    $params = array_merge($inparams, ['userid' => $userid]);

    $alldata = [];
    $sessionstrack = $DB->get_recordset_sql($sql, $params);
    foreach ($sessionstrack as $sessiontrack) {
      $alldata[$sessiontrack->contextid][] = (object) [
        'sessionid' => $sessiontrack->sessionid,
        'timecreated' => transform::datetime($sessiontrack->timecreated),
      ];
    }
    $sessionstrack->close();

    // The aicc_session data is organised in: {Course name}/{SCORM activity name}/{My sessions}/data.json
    // In this case, the attempt hasn't been included in the json file because it can be null.
    array_walk($alldata, function ($data, $contextid) {
      $context = \context::instance_by_id($contextid);
      $subcontext = [
        get_string('skillsoft_mysessions', 'skillsoftmi')
      ];
      writer::with_context($context)->export_data(
        $subcontext,
        (object) ['sessions' => $data]
      );
    });

    // Get tdr data.
    $sql = "SELECT ss.id,
                       ss.timestamp,
                       ss.username,
                       ss.reset,
                       ss.format,
                       ss.data,
                       ss.context,
                       ss.processed,
                       ctx.id as contextid
                  FROM {skillsoftmi_tdr} ss
                  JOIN {skillsoftmi} s
                    ON s.assetid = ss.assetid
                  JOIN {course_modules} cm
                    ON cm.instance = s.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ss.userid = :userid
                   AND ctx.id $insql";
    $params = array_merge($inparams, ['userid' => $userid]);

    $alldata = [];
    $tdrs = $DB->get_recordset_sql($sql, $params);
    foreach ($tdrs as $tdr) {
      $alldata[$tdr->contextid][] = (object) [
        'timestamp' => transform::datetime($tdr->timestamp),
        'username' => $tdr->username,
        'reset' => $tdr->reset,
        'format' => $tdr->format,
        'data' => $tdr->data,
        'context' => $tdr->context,
        'processed' => $tdr->processed,
      ];
    }
    $tdrs->close();

    // The aicc_session data is organised in: {Course name}/{SCORM activity name}/{My TDRs}/data.json
    // In this case, the attempt hasn't been included in the json file because it can be null.
    array_walk($alldata, function ($data, $contextid) {
      $context = \context::instance_by_id($contextid);
      $subcontext = [
        get_string('skillsoft_mytdr', 'skillsoftmi')
      ];
      writer::with_context($context)->export_data(
        $subcontext,
        (object) ['TDRs' => $data]
      );
    });

    // Get report_results data.
    $sql = "SELECT ss.id,
                       ss.loginname,
                       ss.lastname,
                       ss.firstname,
                       ss.firstaccessdate,
                       ss.lastaccessdate,
                       ss.completeddate,
                       ss.firstscore,
                       ss.currentscore,
                       ss.bestscore,
                       ss.lessonstatus,
                       ss.duration,
                       ss.accesscount,
                       ss.processed,
                       ss.attempt,
                       ctx.id as contextid
                   FROM {skillsoftmi_report_results} ss
                   JOIN {skillsoftmi} s
                     ON s.assetid = ss.assetid
                   JOIN {course_modules} cm
                     ON cm.instance = s.id
                   JOIN {context} ctx
                     ON ctx.instanceid = cm.id
                  WHERE ss.userid = :userid
                    AND ctx.id $insql";
    $params = array_merge($inparams, ['userid' => $userid]);

    $alldata = [];
    $reportresults = $DB->get_recordset_sql($sql, $params);
    foreach ($reportresults as $reportresult) {
      $alldata[$reportresult->contextid][$reportresult->attempt][] = (object) [
        'loginname' => $reportresult->loginname,
        'lastname' => $reportresult->lastname,
        'firstname' => $reportresult->firstname,
        'firstaccessdate' => transform::datetime($reportresult->firstaccessdate),
        'lastaccessdate' => transform::datetime($reportresult->lastaccessdate),
        'completeddate' => transform::datetime($reportresult->completeddate),
        'firstscore' => $reportresult->firstscore,
        'currentscore' => $reportresult->currentscore,
        'bestscore' => $reportresult->bestscore,
        'lessonstatus' => $reportresult->lessonstatus,
        'duration' => $reportresult->duration,
        'accesscount' => $reportresult->accesscount,
        'processed' => $reportresult->processed,
      ];
    }
    $reportresult->close();

    // The scoes_track data is organised in: {Course name}/{SkillSoftmi activity name}/{My Report Results}/{Attempt X}/data.json
    // where X is the attempt number.
    array_walk($alldata, function ($attemptsdata, $contextid) {
      $context = \context::instance_by_id($contextid);
      array_walk($attemptsdata, function ($data, $attempt) use ($context) {
        $subcontext = [
          get_string('skillsoft_myreportresults', 'skillsoftmi'),
          get_string('skillsoft_attempt', 'skillsoftmi') . " $attempt"
        ];
        writer::with_context($context)->export_data(
          $subcontext,
          (object) ['reportresults' => $data]
        );
      });
    });
  }

  /**
   * Delete all user data which matches the specified context.
   *
   * @param context $context A user context.
   */
  public static function delete_data_for_all_users_in_context(\context $context)
  {
    // This should not happen, but just in case.
    if ($context->contextlevel != CONTEXT_MODULE) {
      return;
    }

    // Prepare SQL to gather all IDs to delete.
    $sqla = "SELECT ss.id
                  FROM {%s} ss
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                   AND cm.module = m.id
                 WHERE cm.id = :cmid";

    $sqlb = "SELECT ss.userid
                  FROM {%s} ss
                  JOIN {skillsoftmi} s
                    ON s.assetid = ss.assetid
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = s.id
                   AND cm.module = m.id
                 WHERE cm.id = :cmid";
    $params = ['cmid' => $context->instanceid];

    static::delete_data('skillsoftmi_au_track', $sqla, $params);
    static::delete_data('skillsoftmi_session_track', $sqla, $params);
    static::delete_data('skillsoftmi_tdr', $sqlb, $params);
    static::delete_data('skillsoftmi_report_results', $sqlb, $params);
  }

  /**
   * Delete all user data for the specified user, in the specified contexts.
   *
   * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
   */
  public static function delete_data_for_user(approved_contextlist $contextlist)
  {
    global $DB;

    // Remove contexts different from COURSE_MODULE.
    $contextids = array_reduce($contextlist->get_contexts(), function ($carry, $context) {
      if ($context->contextlevel == CONTEXT_MODULE) {
        $carry[] = $context->id;
      }
      return $carry;
    }, []);

    if (empty($contextids)) {
      return;
    }
    $userid = $contextlist->get_user()->id;
    // Prepare SQL to gather all completed IDs.
    list($insql, $inparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);
    $sqla = "SELECT ss.id
                  FROM {%s} ss
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ss.userid = :userid
                   AND ctx.id $insql";

    $sqlb = "SELECT ss.id
                  FROM {%s} ss
                  JOIN {skillsoftmi} s
                    ON s.assetid = ss.assetid
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = s.id
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ss.userid = :userid
                   AND ctx.id $insql";
    $params = array_merge($inparams, ['userid' => $userid]);

    static::delete_data('skillsoftmi_au_track', $sqla, $params);
    static::delete_data('skillsoftmi_session_track', $sqla, $params);
    static::delete_data('skillsoftmi_tdr', $sqlb, $params);
    static::delete_data('skillsoftmi_report_results', $sqlb, $params);
  }

  /**
   * Delete multiple users within a single context.
   *
   * @param   approved_userlist       $userlist The approved context and user information to delete information for.
   */
  public static function delete_data_for_users(approved_userlist $userlist)
  {
    global $DB;
    $context = $userlist->get_context();

    if (!is_a($context, \context_module::class)) {
      return;
    }

    // Prepare SQL to gather all completed IDs.
    $userids = $userlist->get_userids();
    list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
    $sqla = "SELECT ss.id
                  FROM {%s} ss
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = ss.skillsoftid
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ctx.id = :contextid
                   AND ss.userid $insql";

    $sqlb = "SELECT ss.id
                  FROM {%s} ss
                  JOIN {skillsoftmi} s
                    ON s.assetid = ss.assetid
                  JOIN {modules} m
                    ON m.name = 'skillsoftmi'
                  JOIN {course_modules} cm
                    ON cm.instance = s.id
                   AND cm.module = m.id
                  JOIN {context} ctx
                    ON ctx.instanceid = cm.id
                 WHERE ctx.id = :contextid
                   AND ss.userid $insql";
    $params = array_merge($inparams, ['contextid' => $context->id]);

    static::delete_data('skillsoftmi_au_track', $sqla, $params);
    static::delete_data('skillsoftmi_session_track', $sqla, $params);
    static::delete_data('skillsoftmi_tdr', $sqlb, $params);
    static::delete_data('skillsoftmi_report_results', $sqlb, $params);
  }

  /**
   * Delete data from $tablename with the IDs returned by $sql query.
   *
   * @param  string $tablename  Table name where executing the SQL query.
   * @param  string $sql    SQL query for getting the IDs of the scoestrack entries to delete.
   * @param  array  $params SQL params for the query.
   */
  protected static function delete_data(string $tablename, string $sql, array $params)
  {
    global $DB;

    $skillsofttracksids = $DB->get_fieldset_sql(sprintf($sql, $tablename), $params);
    if (!empty($skillsofttracksids)) {
      list($insql, $inparams) = $DB->get_in_or_equal($skillsofttracksids, SQL_PARAMS_NAMED);
      $DB->delete_records_select($tablename, "id $insql", $inparams);
    }
  }
}
