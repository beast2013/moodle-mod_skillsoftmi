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
require_once($CFG->dirroot.'/mod/skillsoftmi/olsalib.php');

require_login();

$id = required_param('assetid', PARAM_TEXT);       // AssetID

if (empty($id)) {
	error('A required parameter is missing');
}

if (strtolower($id) != 'sso') {
	$response = AI_GetXmlAssetMetaDatami($id);
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
<title><?php echo get_string('skillsoft_metadatatitle', 'skillsoftmi');?></title>
<script type="text/javascript">
	//<![CDATA[
	function doit() {
		<?php
		if (strtolower($id) != 'sso') {
		 	if ($response->success) {
				print "window.opener.document.getElementById('id_name').value=".'"'.olsadatatohtmlmi($response->result->title->_).'";';
				print "window.opener.document.getElementById('id_launch').value=".'"'.$response->result->launchurl->_.'";';
				print "window.opener.document.getElementById('id_duration').value=".'"'.$response->result->duration.'";';
				print "window.opener.setTextArea(window.opener,'introeditor',".'"'.olsadatatohtmlmi($response->result->description->_).'");';
				print "window.opener.setTextArea(window.opener,'audienceeditor',".'"'.olsadatatohtmlmi($response->result->audience).'");';
				print "window.opener.setTextArea(window.opener,'prereqeditor',".'"'.olsadatatohtmlmi($response->result->prerequisites).'");';
				print "window.close();";
			} else {
				print "document.getElementById('waitingmessage').style.display = 'none';";
				print "document.getElementById('errormessage').style.display = 'block';";
			}
		} else {
			if (!$CFG->skillsoftmi_trackingmode == TRACK_TO_LMSmi) {
				print "window.opener.document.getElementById('id_name').value=".'"'.get_string('skillsoft_ssoassettitle', 'skillsoftmi').'";';
				print "window.opener.document.getElementById('id_launch').value=".'"'.$CFG->skillsoftmi_ssourl.'";';
				print "window.opener.document.getElementById('id_duration').value='0';";
				print "window.opener.setTextArea(window.opener,'introeditor',".'"'.get_string('skillsoft_ssoassetsummary', 'skillsoftmi').'");';
				print "window.opener.setTextArea(window.opener,'audienceeditor','');";
				print "window.opener.setTextArea(window.opener,'prereqeditor','');";
				print "window.close();";
			} else {
				print "document.getElementById('waitingmessage').style.display = 'none';";
				print "document.getElementById('errormessage').style.display = 'block';";
			}
		}

		?>

	}

	//]]>
</script>
</head>
<body onload="doit();">
<div id="waitingmessage"><p><?php echo get_string('skillsoft_metadatasetting', 'skillsoftmi');?></p></div>
<div id="errormessage" style="display:none;"><p>
<?php
if (strtolower($id) != 'sso') {
	print '<p>'.get_string('skillsoft_metadataerror', 'skillsoftmi').'</p>';
	print '<p>'.$response->errormessage.'</p>';
} else {
	if ($CFG->skillsoftmi_trackingmode == TRACK_TO_LMSmi) {
		print '<p>'.get_string('skillsoft_ssomodeerror', 'skillsoftmi').'</p>';
	}
}
?>
</p></div>
</body>
</html>
