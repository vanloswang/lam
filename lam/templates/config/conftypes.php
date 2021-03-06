<?php
/*
$Id$

  This code is part of LDAP Account Manager (http://www.ldap-account-manager.org/)
  Copyright (C) 2004 - 2014  Roland Gruber

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/


/**
* Here the user can select the account types.
*
* @package configuration
* @author Roland Gruber
*/


/** Access to config functions */
include_once('../../lib/config.inc');
/** Access to account types */
include_once('../../lib/types.inc');

// start session
if (strtolower(session_module_name()) == 'files') {
	session_save_path("../../sess");
}
@session_start();

setlanguage();

// check if config is set
// if not: load login page
if (!isset($_SESSION['conf_config'])) {
	/** go back to login if password is invalid */
	require('conflogin.php');
	exit;
}

// check if user canceled editing
if (isset($_POST['cancelSettings'])) {
	metaRefresh("../login.php");
	exit;
}

$conf = &$_SESSION['conf_config'];

$errorsToDisplay = checkInput();

// check if button was pressed and if we have to save the settings or go to another tab
if (isset($_POST['saveSettings']) || isset($_POST['editmodules'])
	|| isset($_POST['edittypes']) || isset($_POST['generalSettingsButton'])
	|| isset($_POST['moduleSettings']) || isset($_POST['jobs'])) {
	if (sizeof($errorsToDisplay) == 0) {
		// check if all types have modules
		$activeTypes = $conf->get_ActiveTypes();
		for ($i = 0; $i < sizeof($activeTypes); $i++) {
			$selectedModules = $conf->get_AccountModules($activeTypes[$i]);
			if (sizeof($selectedModules) == 0) {
				// go to module selection
				metaRefresh("confmodules.php");
				exit;
			}
		}
		// go to final page
		if (isset($_POST['saveSettings'])) {
			metaRefresh("confsave.php");
			exit;
		}
		// go to modules page
		elseif (isset($_POST['editmodules'])) {
			metaRefresh("confmodules.php");
			exit;
		}
		// go to general page
		elseif (isset($_POST['generalSettingsButton'])) {
			metaRefresh("confmain.php");
			exit;
		}
		// go to module settings page
		elseif (isset($_POST['moduleSettings'])) {
			metaRefresh("moduleSettings.php");
			exit;
		}
		// go to jobs page
		elseif (isset($_POST['jobs'])) {
			metaRefresh("jobs.php");
			exit;
		}
	}
}

$typeSettings = $conf->get_typeSettings();
$allTypes = getTypes();
$activeTypes = $conf->get_ActiveTypes();
$availableTypes = array();
for ($i = 0; $i < sizeof($allTypes); $i++) {
	if (!in_array($allTypes[$i], $activeTypes)) {
		$availableTypes[$allTypes[$i]] = getTypeAlias($allTypes[$i]);
	}
}
natcasesort($availableTypes);

echo $_SESSION['header'];

echo "<title>" . _("LDAP Account Manager Configuration") . "</title>\n";

// include all CSS files
$cssDirName = dirname(__FILE__) . '/../../style';
$cssDir = dir($cssDirName);
$cssFiles = array();
$cssEntry = $cssDir->read();
while ($cssEntry !== false) {
	if (substr($cssEntry, strlen($cssEntry) - 4, 4) == '.css') {
		$cssFiles[] = $cssEntry;
	}
	$cssEntry = $cssDir->read();
}
sort($cssFiles);
foreach ($cssFiles as $cssEntry) {
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../style/" . $cssEntry . "\">\n";
}

echo "<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"../../graphics/favicon.ico\">\n";
echo "</head><body>\n";
// include all JavaScript files
$jsDirName = dirname(__FILE__) . '/../lib';
$jsDir = dir($jsDirName);
$jsFiles = array();
while ($jsEntry = $jsDir->read()) {
	if (substr($jsEntry, strlen($jsEntry) - 3, 3) != '.js') continue;
	$jsFiles[] = $jsEntry;
}
sort($jsFiles);
foreach ($jsFiles as $jsEntry) {
	echo "<script type=\"text/javascript\" src=\"../lib/" . $jsEntry . "\"></script>\n";
}

?>
		<table border=0 width="100%" class="lamHeader ui-corner-all">
			<tr>
				<td align="left" height="30">
					<a class="lamLogo" href="http://www.ldap-account-manager.org/" target="new_window">LDAP Account Manager</a>
				</td>
				<td align="right">
					<?php echo _('Server profile') . ': ' . $conf->getName(); ?>
					&nbsp;&nbsp;
				</td>
			</tr>
		</table>
		<br>
<?php

// print error messages
for ($i = 0; $i < sizeof($errorsToDisplay); $i++) call_user_func_array('StatusMessage', $errorsToDisplay[$i]);

echo ("<form action=\"conftypes.php\" method=\"post\">\n");

// hidden submit buttons which are clicked by tabs
echo "<div style=\"display: none;\">\n";
	echo "<input name=\"generalSettingsButton\" type=\"submit\" value=\" \">";
	echo "<input name=\"edittypes\" type=\"submit\" value=\" \">";
	echo "<input name=\"editmodules\" type=\"submit\" value=\" \">";
	echo "<input name=\"moduleSettings\" type=\"submit\" value=\" \">";
	echo "<input name=\"jobs\" type=\"submit\" value=\" \">";
echo "</div>\n";
	
// tabs
echo '<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">';

echo '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';
echo '<li id="generalSettingsButton" class="ui-state-default ui-corner-top" onmouseover="jQuery(this).addClass(\'tabs-hover\');" onmouseout="jQuery(this).removeClass(\'tabs-hover\');">';
	echo '<a href="#" onclick="document.getElementsByName(\'generalSettingsButton\')[0].click();"><img src="../../graphics/tools.png" alt=""> ';
	echo _('General settings') . '</a>';
echo '</li>';
echo '<li id="edittypes" class="ui-state-default ui-corner-top">';
	echo '<a href="#" onclick="document.getElementsByName(\'edittypes\')[0].click();"><img src="../../graphics/gear.png" alt=""> ';
	echo _('Account types') . '</a>';
echo '</li>';
echo '<li id="editmodules" class="ui-state-default ui-corner-top" onmouseover="jQuery(this).addClass(\'tabs-hover\');" onmouseout="jQuery(this).removeClass(\'tabs-hover\');">';
	echo '<a href="#" onclick="document.getElementsByName(\'editmodules\')[0].click();"><img src="../../graphics/modules.png" alt=""> ';
	echo _('Modules') . '</a>';
echo '</li>';
echo '<li id="moduleSettings" class="ui-state-default ui-corner-top" onmouseover="jQuery(this).addClass(\'tabs-hover\');" onmouseout="jQuery(this).removeClass(\'tabs-hover\');">';
	echo '<a href="#" onclick="document.getElementsByName(\'moduleSettings\')[0].click();"><img src="../../graphics/modules.png" alt=""> ';
	echo _('Module settings') . '</a>';
echo '</li>';
if (isLAMProVersion()) {
	echo '<li id="jobs" class="ui-state-default ui-corner-top" onmouseover="jQuery(this).addClass(\'tabs-hover\');" onmouseout="jQuery(this).removeClass(\'tabs-hover\');">';
		echo '<a href="#" onclick="document.getElementsByName(\'jobs\')[0].click();"><img src="../../graphics/clock.png" alt=""> ';
		echo _('Jobs') . '</a>';
	echo '</li>';
}
echo '</ul>';

?>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#edittypes').addClass('ui-tabs-active');
	jQuery('#edittypes').addClass('ui-state-active');
	jQuery('#edittypes').addClass('user-bright');
});
</script>

<div class="ui-tabs-panel ui-widget-content ui-corner-bottom user-bright">
<?php

$container = new htmlTable();

// show available types
if (sizeof($availableTypes) > 0) {
	$container->addElement(new htmlSubTitle(_("Available account types")), true);
	$availableContainer = new htmlTable();
	foreach ($availableTypes as $key => $value) {
		$availableContainer->addElement(new htmlImage('../../graphics/' . $key . '.png'));
		$availableContainer->addElement(new htmlOutputText($value));
		$availableContainer->addElement(new htmlSpacer('10px', null));
		$availableContainer->addElement(new htmlOutputText(getTypeDescription($key)));
		$button = new htmlButton('add_' . $key, 'add.png', true);
		$button->setTitle(_("Add"));
		$availableContainer->addElement($button, true);
	}
	$availableContainer->addElement(new htmlSpacer(null, '20px'), true);
	$container->addElement($availableContainer, true);
}

$_SESSION['conftypes_optionTypes'] = array();
// show active types
if (sizeof($activeTypes) > 0) {
	$container->addElement(new htmlSubTitle(_("Active account types")), true);
	$activeContainer = new htmlTable();
	for ($i = 0; $i < sizeof($activeTypes); $i++) {
		// title
		$titleGroup = new htmlGroup();
		$titleGroup->colspan = 6;
		$titleGroup->addElement(new htmlImage('../../graphics/' . $activeTypes[$i] . '.png'));
		$titleText = new htmlOutputText(getTypeAlias($activeTypes[$i]));
		$titleText->setIsBold(true);
		$titleGroup->addElement($titleText);
		$titleGroup->addElement(new htmlSpacer('10px', null));
		$titleGroup->addElement(new htmlOutputText(getTypeDescription($activeTypes[$i])));
		$activeContainer->addElement($titleGroup);
		// delete button
		$delButton = new htmlButton('rem_'. $activeTypes[$i], 'del.png', true);
		$delButton->alignment = htmlElement::ALIGN_RIGHT;
		$delButton->setTitle(_("Remove this account type"));
		$activeContainer->addElement($delButton, true); //del.png
		$activeContainer->addElement(new htmlSpacer(null, '5px'), true);
		// LDAP suffix
		$suffixInput = new htmlTableExtendedInputField(_("LDAP suffix"), 'suffix_' . $activeTypes[$i], $typeSettings['suffix_' . $activeTypes[$i]], '202');
		$suffixInput->setFieldSize(40);
		$activeContainer->addElement($suffixInput);
		$activeContainer->addElement(new htmlSpacer('20px', null));
		// list attributes
		if (isset($typeSettings['attr_' . $activeTypes[$i]])) {
			$attributes = $typeSettings['attr_' . $activeTypes[$i]];
		}
		else {
			$attributes = getDefaultListAttributes($activeTypes[$i]);
		}
		$attrsInput = new htmlTableExtendedInputField(_("List attributes"), 'attr_' . $activeTypes[$i], $attributes, '206');
		$attrsInput->setFieldSize(40);
		$activeContainer->addElement($attrsInput);
		$activeContainer->addNewLine();
		// type options
		$typeObj = new $activeTypes[$i];
		$typeConfigOptions = $typeObj->get_configOptions();
		if (!empty($typeConfigOptions)) {
			foreach ($typeConfigOptions as $typeConfigOption) {
				$activeContainer->addElement($typeConfigOption, true);
			}
			// save option types to session
			ob_start();
			$dummyIndex = 1;
			$typeConfigOptionTypes = parseHtml(null, $typeConfigOptions, array(), true, $dummyIndex, 'user');
			ob_end_clean();
			$_SESSION['conftypes_optionTypes'] = array_merge($_SESSION['conftypes_optionTypes'], $typeConfigOptionTypes);
		}
		// advanced options
		$advancedOptionsContent = new htmlTable();
		// LDAP filter
		$filter = '';
		if (isset($typeSettings['filter_' . $activeTypes[$i]])) {
			$filter = $typeSettings['filter_' . $activeTypes[$i]];
		}
		$filterInput = new htmlTableExtendedInputField(_("Additional LDAP filter"), 'filter_' . $activeTypes[$i], $filter, '260');
		$filterInput->setFieldSize(40);
		$advancedOptionsContent->addElement($filterInput);
		$advancedOptionsContent->addElement(new htmlSpacer('20px', null));
		// hidden type
		$hidden = false;
		if (isset($typeSettings['hidden_' . $activeTypes[$i]])) {
			$hidden = $typeSettings['hidden_' . $activeTypes[$i]];
		}
		$advancedOptionsContent->addElement(new htmlTableExtendedInputCheckbox('hidden_' . $activeTypes[$i], $hidden, _('Hidden'), '261'));
		if (isLAMProVersion() && ($conf->getAccessLevel() == LAMConfig::ACCESS_ALL)) {
			$advancedOptionsContent->addElement(new htmlSpacer('20px', null));
			$isReadOnly = false;
			if (isset($typeSettings['readOnly_' . $activeTypes[$i]])) {
				$isReadOnly = $typeSettings['readOnly_' . $activeTypes[$i]];
			}
			$readOnly = new htmlTableExtendedInputCheckbox('readOnly_' . $activeTypes[$i], $isReadOnly, _('Read-only'), '265');
			$readOnly->setElementsToDisable(array('hideNewButton_' . $activeTypes[$i], 'hideDeleteButton_' . $activeTypes[$i]));
			$advancedOptionsContent->addElement($readOnly);
		}
		$advancedOptionsContent->addNewLine();
		// custom label
		$customLabel = '';
		if (isset($typeSettings['customLabel_' . $activeTypes[$i]])) {
			$customLabel = $typeSettings['customLabel_' . $activeTypes[$i]];
		}
		$customLabelInput = new htmlTableExtendedInputField(_('Custom label'), 'customLabel_' . $activeTypes[$i], $customLabel, '264');
		$customLabelInput->setFieldSize(40);
		$advancedOptionsContent->addElement($customLabelInput);
		$advancedOptionsContent->addElement(new htmlSpacer('20px', null));
		if (isLAMProVersion() && ($conf->getAccessLevel() == LAMConfig::ACCESS_ALL)) {
			// hide button to create new accounts
			$hideNewButton = false;
			if (isset($typeSettings['hideNewButton_' . $activeTypes[$i]])) {
				$hideNewButton = $typeSettings['hideNewButton_' . $activeTypes[$i]];
			}
			$advancedOptionsContent->addElement(new htmlTableExtendedInputCheckbox('hideNewButton_' . $activeTypes[$i], $hideNewButton, _('No new entries'), '262'));
			$advancedOptionsContent->addElement(new htmlSpacer('20px', null));
			// hide button to delete accounts
			$hideDeleteButton = false;
			if (isset($typeSettings['hideDeleteButton_' . $activeTypes[$i]])) {
				$hideDeleteButton = $typeSettings['hideDeleteButton_' . $activeTypes[$i]];
			}
			$advancedOptionsContent->addElement(new htmlTableExtendedInputCheckbox('hideDeleteButton_' . $activeTypes[$i], $hideDeleteButton, _('Disallow delete'), '263'), true);
		}
		// build advanced options box
		$advancedOptions = new htmlAccordion('advancedOptions_' . $activeTypes[$i], array(_('Advanced options') => $advancedOptionsContent), false);
		$advancedOptions->colspan = 15;
		$activeContainer->addElement($advancedOptions, true);
		
		$activeContainer->addElement(new htmlSpacer(null, '40px'), true);
	}
	$container->addElement($activeContainer, true);
}

$tabindex = 1;
$dynamicTypeOptions = array();
foreach ($_SESSION['conftypes_optionTypes'] as $key => $value) {
	if (isset($typeSettings[$key])) {
		$dynamicTypeOptions[$key] = explode(LAMConfig::LINE_SEPARATOR, $typeSettings[$key]);
	}
}
parseHtml(null, $container, $dynamicTypeOptions, false, $tabindex, 'user');

echo "<input type=\"hidden\" name=\"postAvailable\" value=\"yes\">\n";

echo "</div></div>";

$buttonContainer = new htmlTable();
$buttonContainer->addElement(new htmlSpacer(null, '10px'), true);
$saveButton = new htmlButton('saveSettings', _('Save'));
$saveButton->setIconClass('saveButton');
$buttonContainer->addElement($saveButton);
$cancelButton = new htmlButton('cancelSettings', _('Cancel'));
$cancelButton->setIconClass('cancelButton');
$buttonContainer->addElement($cancelButton, true);
$buttonContainer->addElement(new htmlSpacer(null, '10px'), true);
parseHtml(null, $buttonContainer, array(), false, $tabindex, 'user');

echo "</form>\n";
echo "</body>\n";
echo "</html>\n";


/**
 * Checks user input and saves the entered settings.
 *
 * @return array list of errors
 */
function checkInput() {
	if (!isset($_POST['postAvailable'])) {
		return array();
	}
	$errors = array();
	$conf = &$_SESSION['conf_config'];
	$typeSettings = $conf->get_typeSettings();
	$accountTypes = $conf->get_ActiveTypes();
	$postKeys = array_keys($_POST);
	for ($i = 0; $i < sizeof($postKeys); $i++) {
		$key = $postKeys[$i];
		// check if remove button was pressed
		if (substr($key, 0, 4) == "rem_") {
			$type = substr($key, 4);
			$accountTypes = array_flip($accountTypes);
			unset($accountTypes[$type]);
			$accountTypes = array_flip($accountTypes);
			$accountTypes = array_values($accountTypes);
		}
		// check if add button was pressed
		else if (substr($key, 0, 4) == "add_") {
			$type = substr($key, 4);
			$accountTypes[] = $type;
		}
		// set suffixes
		elseif (substr($key, 0, 7) == "suffix_") {
			$typeSettings[$key] = trim($_POST[$key]);
			$type = substr($postKeys[$i], 7);
			if (strlen($_POST[$key]) < 1) {
				$errors[] = array("ERROR", _("LDAP Suffix is invalid!"), getTypeAlias($type));
			}
		}
		// set attributes
		elseif (substr($key, 0, 5) == "attr_") {
			$typeSettings[$key] = $_POST[$key];
			$type = substr($postKeys[$i], 5);
			if (!is_string($_POST[$key]) || !preg_match("/^((#[^:;]+)|([^:;]*:[^:;]+))(;((#[^:;]+)|([^:;]*:[^:;]+)))*$/", $_POST[$key])) {
				$errors[] = array("ERROR", _("List attributes are invalid!"), getTypeAlias($type));
			}
		}
		// set filter
		elseif (substr($key, 0, strlen('filter_')) == "filter_") {
			$typeSettings[$key] = $_POST[$key];
		}
		// set custom label
		elseif (strpos($key, 'customLabel_') === 0) {
			$typeSettings[$key] = $_POST[$key];
		}
	}
	$typeConfigOptions = extractConfigOptionsFromPOST($_SESSION['conftypes_optionTypes']);
	for ($i = 0; $i < sizeof($accountTypes); $i++) {
		// set hidden
		$key = "hidden_" . $accountTypes[$i];
		$typeSettings[$key] = (isset($_POST[$key]) && ($_POST[$key] == 'on'));
		if (isLAMProVersion() && ($conf->getAccessLevel() == LAMConfig::ACCESS_ALL)) {
			// set if new entries are allowed
			$key = "hideNewButton_" . $accountTypes[$i];
			$typeSettings[$key] = (isset($_POST[$key]) && ($_POST[$key] == 'on'));
			// set if deletion of entries is allowed
			$key = "hideDeleteButton_" . $accountTypes[$i];
			$typeSettings[$key] = (isset($_POST[$key]) && ($_POST[$key] == 'on'));
			// set if account type is read-only
			$key = "readOnly_" . $accountTypes[$i];
			$typeSettings[$key] = (isset($_POST[$key]) && ($_POST[$key] == 'on'));
		}
		// check dynamic type settings
		$typeObj = new $accountTypes[$i];
		$typeMessages = $typeObj->check_configOptions($typeConfigOptions);
		if (!empty($typeMessages)) {
			$errors = array_merge($errors, $typeMessages);
		}
	}
	// add dynamic type settings
	foreach ($typeConfigOptions as $key => $value) {
		$typeSettings[$key] = implode(LAMConfig::LINE_SEPARATOR, $value);
	}
	// save input
	$conf->set_typeSettings($typeSettings);
	$conf->set_ActiveTypes($accountTypes);
	return $errors;
}

?>




