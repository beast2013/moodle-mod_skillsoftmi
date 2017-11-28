<?php
/*
 * @package		mod-skillsoftmi
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/skillsoftmi/locallib.php');

$settings->add(new admin_setting_configtext('skillsoftmi_olsaendpoint',
					get_string('skillsoft_olsaendpoint', 'skillsoftmi'),
					get_string('skillsoft_olsaendpointdesc', 'skillsoftmi'),
					''));

$settings->add(new admin_setting_configtext('skillsoftmi_olsacustomerid',
					get_string('skillsoft_olsacustomerid', 'skillsoftmi'),
					get_string('skillsoft_olsacustomeriddesc', 'skillsoftmi'),
					''));

$settings->add(new admin_setting_configtext('skillsoftmi_olsasharedsecret',
					get_string('skillsoft_olsasharedsecret', 'skillsoftmi'),
					get_string('skillsoft_olsasharedsecretdesc', 'skillsoftmi'),
					''));

$settings->add(new admin_setting_configtext('skillsoftmi_sessionpurge',
					get_string('skillsoft_sessionpurge', 'skillsoftmi'),
					get_string('skillsoft_sessionpurgedesc', 'skillsoftmi'),
                   	8,
                   	PARAM_INT));

$settings->add(new admin_setting_configselect('skillsoftmi_trackingmode',
			   get_string('skillsoft_trackingmode', 'skillsoftmi'),
			   get_string('skillsoft_trackingmodedesc', 'skillsoftmi'),
			   TRACK_TO_LMSmi,
			   skillsoftmi_get_tracking_method_array()));

$settings->add(new admin_setting_configselect('skillsoftmi_useridentifier',
			   get_string('skillsoft_useridentifier', 'skillsoftmi'),
			   get_string('skillsoft_useridentifierdesc', 'skillsoftmi'),
			   IDENTIFIER_USERIDmi,
			   skillsoftmi_get_user_identifier_array()));

$settings->add(new admin_setting_configtext('skillsoftmi_defaultssogroup',
					get_string('skillsoft_defaultssogroup', 'skillsoftmi'),
					get_string('skillsoft_defaultssogroupdesc', 'skillsoftmi'),
					'SkillSoft'));

$settings->add(new admin_setting_configtext('skillsoftmi_accountprefix',
			   get_string('skillsoft_accountprefix', 'skillsoftmi'),
			   get_string('skillsoft_accountprefixdesc', 'skillsoftmi'),
			   ''));

$settings->add(new admin_setting_configcheckbox('skillsoftmi_usesso',
				 get_string('skillsoft_usesso', 'skillsoftmi'),
				 get_string('skillsoft_usessodesc', 'skillsoftmi'),
				 0));			   
			   
$settings->add(new admin_setting_configtext('skillsoftmi_ssourl',
			   get_string('skillsoft_ssourl', 'skillsoftmi'),
			   get_string('skillsoft_ssourldesc', 'skillsoftmi'),
			   $CFG->wwwroot.'/mod/skillsoftmi/ssopreloader.php?a=%s'));
			   
$settings->add(new admin_setting_configselect('skillsoftmi_sso_actiontype',
			   get_string('skillsoft_sso_actiontype', 'skillsoftmi'),
			   get_string('skillsoft_sso_actiontypedesc', 'skillsoftmi'),
			   SSO_ASSET_ACTIONTYPE_SUMMARYmi,
			   skillsoftmi_get_sso_asset_actiontype_array()));

$settings->add(new admin_setting_configtext('skillsoftmi_reportstartdate',
			   get_string('skillsoft_reportstartdate', 'skillsoftmi'),
			   get_string('skillsoft_reportstartdatedesc', 'skillsoftmi'),
			   '01-JAN-2000'));			   

$settings->add(new admin_setting_configcheckbox('skillsoftmi_reportincludetoday',
			   get_string('skillsoft_reportincludetoday', 'skillsoftmi'),
			   get_string('skillsoft_reportincludetodaydesc', 'skillsoftmi'),
			   0));			   
			   
$settings->add(new admin_setting_configcheckbox('skillsoftmi_clearwsdlcache',
		   get_string('skillsoft_clearwsdlcache', 'skillsoftmi'),
		   get_string('skillsoft_clearwsdlcachedesc', 'skillsoftmi'),
		   0));

$settings->add(new admin_setting_configcheckbox('skillsoftmi_disableusagedatacrontask',
		get_string('skillsoft_disableusagedatacrontask', 'skillsoftmi'),
		get_string('skillsoft_disableusagedatacrontaskdesc', 'skillsoftmi'),
		0));

$settings->add(new admin_setting_configcheckbox('skillsoftmi_resetcustomreportcrontask',
		get_string('skillsoft_resetcustomreportcrontask', 'skillsoftmi'),
		get_string('skillsoft_resetcustomreportcrontaskdesc', 'skillsoftmi'),
		0));
		
//May-2013 (2013041400)
$settings->add(new admin_setting_configcheckbox('skillsoftmi_strictaiccstudentid',
		get_string('skillsoft_strictaiccstudentid', 'skillsoftmi'),
		get_string('skillsoft_strictaiccstudentiddesc', 'skillsoftmi'),
		1));		
		
//May-2013
$settings->add(new admin_setting_configtext('skillsoftmi_aiccwindowsettings',
			   get_string('skillsoft_aiccwindowsettings', 'skillsoftmi'),
			   get_string('skillsoft_aiccwindowsettingsdesc', 'skillsoftmi'),
			   'width=800,height=600'));	