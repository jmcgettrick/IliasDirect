<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");

/**
* User Interface class for Turnitin Assignment repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjTurnitinAssignmentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjTurnitinAssignmentGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
*
*/
class ilObjTurnitinAssignmentGUI extends ilObjectPluginGUI
{
	protected $output;
	/**
	* Initialisation
	*/
	protected function afterConstructor()
	{
		// anything needed after object has been constructed
		// - Example: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
	}

	/**
	* Get type.
	*/
	final function getType()
	{
		return "xtii";
	}

	/**
	 * Overwrite creation form - as we only allow creation within a course
	 */
	function initCreationForms($a_new_type)
	{
		global $tree;

		// Check that the parent item is a course or group, if not disable creation
		$parent_nodes = array_reverse($tree->getNodePath($_GET["ref_id"]));
		foreach ($parent_nodes as $parent_node) {
			$parent = $parent_node;
			if ($parent_node['type'] == 'crs' || $parent_node['type'] == 'grp')
			{
				break;
			}
		}

		if ($parent['type'] == 'crs' || $parent['type'] == 'grp')
		{
			$forms = array(
				self::CFORM_NEW => $this->initCreateForm($a_new_type),
				// self::CFORM_IMPORT => $this->initImportForm($a_new_type),
				self::CFORM_CLONE => $this->fillCloneTemplate(null, $a_new_type)
				);

			return $forms;
		}
		else
		{
			ilUtil::sendFailure($this->txt("msg_creation_outwith_course_not_allowed"));

			include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			$form = new ilPropertyFormGUI();
			$form->setTarget("_top");
			$form->setFormAction($this->ctrl->getFormAction($this, "save"));
			$form->addCommandButton("cancel", $this->lng->txt("cancel"));

			$form->setShowTopButtons(0);

			$forms = array(
				self::CFORM_NEW => $form
				);

			return array($form);
		}
	}

	public function cancel() {
		$this->ctrl->returnToParent($this);
	}

	/**
	 * Include a loading bar
	 */
	public function includeLoadingBar($link = "showSubmissions")
	{
		global $tpl, $ilCtrl;

		if ($_SESSION["connection_failed"] == 1)
		{
			ilUtil::sendFailure($this->txt("tii_connection_failed"), true);
			$_SESSION["connection_failed"] = 0;
		}

		$tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/loading_bar.js");

		$js_vars = new ilTemplate("tpl.loading_bar.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");

		$js_vars->setVariable("JS_LOADING_TEXT", $this->txt("js_loading_text"));
		$js_vars->setVariable("JS_SAVING_TEXT", $this->txt("js_saving_text"));
		$js_vars->setVariable("JS_SYNCHING_TEXT", $this->txt("js_synching_text"));
		$js_vars->setVariable("JS_SYNCHING_SUBMISSIONS_LINK", $ilCtrl->getLinkTarget($this, "showSubmissions"));
		$js_vars->setVariable("JS_SYNCHING_SUBMISSIONS_LINK_TEXT", $this->txt("back_to_submissions"));
		$js_vars->setVariable("JS_SUBMISSIONS_LINK", $link);
		$this->output = $js_vars->get();
	}

	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
		$this->includeLoadingBar();

		if ($cmd == "view")
		{
			$cmd = "showSubmissions";
		}

		switch ($cmd)
		{
			case "editSettings": // list all commands that need write permission here
			case "updateSettings":
			case "deleteSubmissions":
			case "bulkDownloadSubmissions":
			case "exportSubmissions":
			case "exportSubmissionsFile":
			case "syncStudents":
			case "turnOffAnonMarking":
			case "tiiInstructors":
			case "removeTutor":
			case "addTutor":
				$this->checkPermission("write");
				$this->$cmd();
				break;

			case "showSubmissions":
			case "showDetails":				// list all commands that need read permission here
			case "submitPaper":
			case "uploadPaper":
			case "viewSubmission":
			case "downloadSubmission":
			case "openGrademark":
			case "openOriginalityReport":
				$this->checkPermission("read");
				$this->$cmd();
				break;
			case "showLoadRedirectSubmissions":
				$this->checkPermission("read");
				$this->showLoadRedirect("submissions");
				break;
			case "showLoadRedirectSettings":
				$this->checkPermission("read");
				$this->showLoadRedirect("settings");
				break;
			case "showLoadRedirectDetails":
				$this->checkPermission("read");
				$this->showLoadRedirect("details");
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd()
	{
		return "showLoadRedirectSettings";//"editSettings";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd()
	{
		if($this->object->course_details["isTutor"] == true || $this->object->course_details["isAdmin"] == true)
		{
			return "showSubmissions";
		}
		else
		{
			return "showDetails";
		}
	}

	/**
	* Set tabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilCtrl, $ilAccess, $ilUser;

		// tab for the "show submissions" command
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("submissions", $this->txt("submissions"), $ilCtrl->getLinkTarget($this, "showSubmissions"));
		}

		// tab for the "show details" command
		if ($this->object->course_details["isMember"] == true)
		{
			$ilTabs->addTab("details", $this->txt("show_details"), $ilCtrl->getLinkTarget($this, "showDetails"));
		}

		// tab for standard info screen
		$this->addInfoTab();

		$end_date = $this->object->getVar("end_date");
                $start_date = $this->object->getVar("start_date");
		$course_details =  $this->object->getVar("course_details");
		// tab for the "submit paper" command
		if (
			(
				($this->object->course_details["isAdmin"] == true || $this->object->course_details["isTutor"] == true)
				&&
				(
					count($this->object->getVar("unsubmitted_students")) > 0 || $this->object->number_of_unsubmitted_students > 0
					|| ($this->object->getVar("generation_speed") != "0" && count($course_details["students"]) > 0)
				)
				&&
				(
					strtotime($end_date["date"]." ".$end_date["time"]) > time() || $this->object->getVar("late_submission")
				)
			)
			||
			($this->object->course_details["isMember"] == true && $this->object->checkIfSubmissionAllowed($ilUser->getId())
                                && strtotime($start_date["date"]." ".$start_date["time"]) < time())
			)
		{
			$tab_heading = $this->txt("submit_paper");

			if (($this->object->course_details["isMember"] == true) && !empty($this->object->submissions[$ilUser->getId()]["objectID"]))
			{
				$tab_heading = $this->txt("resubmit_paper");
			}
			$ilTabs->addTab("submit", $tab_heading, $ilCtrl->getLinkTarget($this, "submitPaper"));
		}

		// tab for "settings"
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$ilTabs->addTab("settings", $this->txt("settings"), $ilCtrl->getLinkTarget($this, "editSettings"));
		}

		// tab for "Turnitin Instructors"
		if ($this->object->course_details["isAdmin"] == true || $this->object->course_details["isTutor"] == true)
		{
			$ilTabs->addTab("tiiInstructors", $this->txt("tii_instructors"), $ilCtrl->getLinkTarget($this, "tiiInstructors")."&refresh_instructors=1");
		}

		// tab for permissions
		$this->addPermissionTab();
	}

	/**
	 * Show Loading bar when you first enter assignment then redirect to submissions
	 */
	function showLoadRedirect($redirect = "submissions")
	{
		global $tpl, $ilTabs;
		$ilTabs->activateTab($redirect);

		$link = "showSubmissions";
		switch ($redirect)
		{
			case "settings":
				$link = "editSettings";
				break;
			case "details":
				$link = "showDetails";
				break;
		}
		$this->includeLoadingBar($link);

		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/show_loading_bar.js");
		$tpl->setContent($this->output);
	}

	/**
	* Edit Settings. This commands uses the form class to display an input form.
	*/
	function editSettings()
	{
		global $tpl, $ilTabs;

		$ilTabs->activateTab("settings");
		$this->initSettingsForm();
		$this->getSettingsValues();
		$tpl->setContent($this->output.$this->form->getHTML());
	}

	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initSettingsForm()
	{
		global $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$ti->setMaxLength(85);
		$this->form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$ta->setCols(100);
		$ta->setRows(8);
		$this->form->addItem($ta);

		// is_online
		$cb = new ilCheckboxInputGUI($this->lng->txt("online"), "is_online");
		$this->form->addItem($cb);

		// point value
		$ti = new ilTextInputGUI($this->txt("point_value"), "point_value");
		$ti->setRequired(true);
		$ti->setMaxLength(4);
		$this->form->addItem($ti);

		// Sub Header for Optional Settings
        $section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->txt('dates'));
		$this->form->addItem($section);

		// Start Date
		$dt = new ilDateTimeInputGUI($this->txt("start_date"), "start_date");
		$dt->setDate(new ilDate(date("Y-m-d H:i:s"),IL_CAL_DATE));
        $dt->setShowTime(true);
        $this->form->addItem($dt);

        // End Date
		$dt = new ilDateTimeInputGUI($this->txt("end_date"), "end_date");
		$next_week = strtotime("+7 days");
        $dt->setDate(new ilDate(date("Y-m-d H:i:s", $next_week),IL_CAL_DATE));
        $dt->setShowTime(true);
        $this->form->addItem($dt);

		// Posting Date
		$dt = new ilDateTimeInputGUI($this->txt("post_date"), "posting_date");
		$next_week_plus_1 = strtotime("+8 days");
        $dt->setDate(new ilDate(date("Y-m-d H:i:s", $next_week_plus_1),IL_CAL_DATE));
        $dt->setShowTime(true);
        $this->form->addItem($dt);

        // Sub Header for Optional Settings
        $section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->txt('optional_settings'));
		$this->form->addItem($section);

        // Special instructions
		$ta = new ilTextAreaInputGUI($this->txt("special_instructions"), "ainst");
		$ta->setCols(100);
		$ta->setRows(8);
		$this->form->addItem($ta);

		// Submission format
        $si = new ilSelectInputGUI($this->txt("submission_format"), "submission_format");
        $options = array(
        		"0" => $this->txt("any"),
        		"1" => $this->txt("text"),
        		"2" => $this->txt("file")
        	);
        $si->setOptions($options);
        $this->form->addItem($si);

		// Set whether late submissions are allowed
		$rg = new ilRadioGroupInputGUI($this->txt("late_submissions"), "late_submission");
		$ro = new ilRadioOption($this->txt("yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($this->txt("no"), "0");
		$rg->addOption($ro);
		$this->form->addItem($rg);

		// Sub Header for Optional Settings
        $section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->txt('originality_report_settings'));
		$this->form->addItem($section);

		// When to generate report
        $si = new ilSelectInputGUI($this->txt("report_generation_speed"), "generation_speed");
        $options = array(
        		"0" => $this->txt("immediately_first"),
        		"1" => $this->txt("immediately_overwrite"),
        		"2" => $this->txt("on_due_date")
        	);
        $si->setOptions($options);
        $this->form->addItem($si);

		// Exclude Bibliography
		$rg = new ilRadioGroupInputGUI($this->txt("exclude_bibliography"), "exclude_bibliography");
		$ro = new ilRadioOption($this->txt("yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($this->txt("no"), "0");
		$rg->addOption($ro);
		if ($this->object->number_of_submissions > 0)
		{
			$rg->setDisabled(true);
		}
		$ro->setInfo($this->txt("exclude_bibliography_help"));
		$this->form->addItem($rg);

		// Exclude Quotes
		$rg = new ilRadioGroupInputGUI($this->txt("exclude_quoted_materials"), "exclude_quoted_materials");
		$ro = new ilRadioOption($this->txt("yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($this->txt("no"), "0");
		$rg->addOption($ro);
		if ($this->object->number_of_submissions > 0)
		{
			$rg->setDisabled(true);
		}
		$ro->setInfo($this->txt("exclude_quoted_materials_help"));
		$this->form->addItem($rg);

		// Exclude small matches
		$rg = new ilRadioGroupInputGUI($this->txt("exclude_type"), "exclude_type");
		$ro = new ilRadioOption($this->txt("no"), "0");
		$rg->addOption($ro);

		$ro = new ilRadioOption($this->txt("exclude_type_by_word"), "1");
			// nest percentage option
        	$word_count = new ilTextInputGUI($this->txt("word_count"), "exclude_value1");
        	$word_count->setRequired(true);
        	$ro->addSubItem($word_count);
		$rg->addOption($ro);

		$ro = new ilRadioOption($this->txt("exclude_type_by_percent"), "2");
			// nest word count option
        	$percentage = new ilTextInputGUI($this->txt("percentage"), "exclude_value2");
        	$percentage->setRequired(true);
        	$ro->addSubItem($percentage);
		$rg->addOption($ro);
		$this->form->addItem($rg);

		// Set whether students can view originality reports
		$rg = new ilRadioGroupInputGUI($this->txt("students_allowed_to_view_reports"), "students_view_reports");
		$ro = new ilRadioOption($this->txt("yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($this->txt("no"), "0");
		$rg->addOption($ro);
		$this->form->addItem($rg);

		// Enable translated matching
		if ($this->object->plugin_config["translated_matching"] == 1)
		{
			$rg = new ilRadioGroupInputGUI($this->txt("enable_translated_matching"), "translated");
			$ro = new ilRadioOption($this->txt("yes"), "1");
			$rg->addOption($ro);
			$ro = new ilRadioOption($this->txt("no"), "0");
			$rg->addOption($ro);
			$this->form->addItem($rg);
		}

		// Enable anonymous marking
		if ($this->object->plugin_config["anon_marking"] == 1)
		{
			$rg = new ilRadioGroupInputGUI($this->txt("enable_anon_marking"), "anon");
			$ro = new ilRadioOption($this->txt("yes"), "1");
			$rg->addOption($ro);
			$ro = new ilRadioOption($this->txt("no"), "0");
			$rg->addOption($ro);
			if ($this->object->number_of_submissions > 0)
			{
				$rg->setDisabled(true);
			}
			$this->form->addItem($rg);
		}

        // Repository to submit papers to
        $si = new ilSelectInputGUI($this->txt("submit_papers_to"), "submit_papers_to");
        $options = array(
        		"0" => $this->txt("no_repository"),
        		"1" => $this->txt("standard_repository")
        	);
		if ($this->object->plugin_config["institutional_repository"] == 1)
		{
			$options["2"] = $this->txt("institutional_repository");
		}
        $si->setOptions($options);
        $this->form->addItem($si);

        // Comparison options
        $cbg = new ilCheckboxGroupInputGUI($this->txt("comparison_options"), "paper_compare");
        foreach (array("s_paper_check", "internet_check", "journal_check") as $option_value) // "institution_check"
        {
        	$cb = new ilCheckboxInputGUI($this->txt($option_value), $option_value);
        	$cb->setValue($option_value);
        	$cbg->addOption($cb);
        }
        $this->form->addItem($cbg);

		if ($this->object->plugin_config["grademark"] == 1 && $this->object->plugin_config["ets"] == 1)
		{
	       	// E-rater Grammar check
	        $rg = new ilRadioGroupInputGUI($this->txt("enable_erater_grammar_check"), "erater");
			$ro = new ilRadioOption($this->txt("no"), "0");
			$rg->addOption($ro);

			$ro = new ilRadioOption($this->txt("yes"), "1");
				// nest select ets handbook option
	        	$si = new ilSelectInputGUI($this->txt("select_ets_handbook"), "erater_handbook");
		        $options = array(
		        		"1" => $this->txt("advanced"),
		        		"2" => $this->txt("high_school"),
		        		"3" => $this->txt("middle_school"),
		        		"4" => $this->txt("elementary"),
		        		"5" => $this->txt("english_learners")
		        	);
		        $si->setOptions($options);
		        $ro->addSubItem($si);

	        	// nest select english dictionary option
	        	$rg2 = new ilRadioGroupInputGUI($this->txt("select_english_dictionary"), "erater_spelling_dictionary");
	        	$ro2 = new ilRadioOption($this->txt("en_us"), "en_US");
				$rg2->addOption($ro2);
				$ro2 = new ilRadioOption($this->txt("en_gb"), "en_GB");
				$rg2->addOption($ro2);
				$ro2 = new ilRadioOption($this->txt("en_both"), "en");
				$rg2->addOption($ro2);
				$ro->addSubItem($rg2);

				// Comparison options
		        $cbg = new ilCheckboxGroupInputGUI($this->txt("categories_enabled_default"), "erater_defaults");
		        foreach (array("erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style") as $option_value)
		        {
		        	$cb = new ilCheckboxInputGUI($this->txt($option_value), $option_value);
		        	$cb->setValue($option_value);
		        	$cbg->addOption($cb);
		        }
		        $ro->addSubItem($cbg);
			$rg->addOption($ro);

			$this->form->addItem($rg);
		}

		$this->form->addCommandButton("updateSettings", $this->txt("save"));

		$this->form->setTitle($this->txt("edit_settings"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Get values for edit properties form
	*/
	function getSettingsValues()
	{
                $values = array();
		$values["title"] = $this->object->getVar("title");
                $values["desc"] = $this->object->getLongDescription();
		$values["is_online"] = $this->object->getVar("is_online");
		$values["point_value"] = $this->object->getVar("point_value");
		$values["start_date"] = $this->object->getVar("start_date");
		$values["end_date"] = $this->object->getVar("end_date");
		$values["posting_date"] = $this->object->getVar("posting_date");
		$values["ainst"] = urldecode($this->object->getVar("instructions"));
		$values["submission_format"] = $this->object->getVar("submission_format");
		$values["late_submission"] = $this->object->getVar("late_submission");
		$values["exclude_bibliography"] = $this->object->getVar("exclude_bibliography");
		$values["exclude_quoted_materials"] = $this->object->getVar("exclude_quoted_materials");
		$values["exclude_type"] = $this->object->getVar("exclude_type");

		if ($values["exclude_type"] != 0)
		{
			$values["exclude_value".$values["exclude_type"]] = $this->object->getVar("exclude_value");
		}

		$values["generation_speed"] = $this->object->getVar("generation_speed");
		$values["submit_papers_to"] = $this->object->getVar("submit_papers_to");
		$values["students_view_reports"] = $this->object->getVar("students_view_reports");

		if ($this->object->plugin_config["anon_marking"] == 1)
		{
			$values["anon"] = $this->object->getVar("anon");
		}

		if ($this->object->plugin_config["translated_matching"] == 1)
		{
			$values["translated"] = $this->object->getVar("translated");
		}

		// Loop through comparison checks
		$comparison_check_vars = array("s_paper_check", "internet_check", "journal_check"); // , "institution_check"
		foreach ($comparison_check_vars as $comparison_check)
		{
			$comparison_values[$comparison_check] = $this->object->getVar($comparison_check);
			if ($comparison_values[$comparison_check] == 1)
			{
				$values["paper_compare"][] = $comparison_check;
			}
		}

		if ($this->object->plugin_config["grademark"] == 1 && $this->object->plugin_config["ets"] == 1)
		{
			$values["erater"] = $this->object->getVar("erater");
			$values["erater_handbook"] = $this->object->getVar("erater_handbook");
			$values["erater_spelling_dictionary"] = $this->object->getVar("erater_spelling_dictionary");

			// Loop through default checks
			$default_check_vars = array("erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style");
			foreach ($default_check_vars as $default_check)
			{
				$erater_defaults[$default_check] = $this->object->getVar($default_check);
				if ($erater_defaults[$default_check] == 1)
				{
					$values["erater_defaults"][] = $default_check;
				}
			}
		}

		$this->form->setValuesByArray($values);
	}

	/**
	* Update properties
	*/
	public function updateSettings()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$ilTabs->activateTab("settings");

		$this->initSettingsForm();

		if ($this->form->checkInput())
		{
			$error = array();

			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));

			$point_value = (int)$this->form->getInput("point_value");
			$this->object->setVar("point_value", $point_value);

			if ($point_value < 0 || $point_value > 1000)
			{
				$error[] = 	$this->txt("point_value_must_be_int");
			}

			// Validate dates
			$var_start_date = $this->form->getInput("start_date");
			$start_date = strtotime($var_start_date["date"]." ".$var_start_date["time"]);

			$var_end_date = $this->form->getInput("end_date");
			$end_date = strtotime($var_end_date["date"]." ".$var_end_date["time"]);

			$var_posting_date = $this->form->getInput("posting_date");
			$posting_date = strtotime($var_posting_date["date"]." ".$var_posting_date["time"]);

			if ($end_date <= $start_date)
			{
				$error[] = $this->txt("end_date_not_before_start");
			}

			if ($posting_date < $start_date)
			{
				$error[] = $this->txt("posting_date_not_before_start");
			}

			$this->object->setDate("start_date", $this->form->getInput("start_date"));
			$this->object->setDate("end_date", $this->form->getInput("end_date"));
			$this->object->setDate("posting_date", $this->form->getInput("posting_date"));

			$this->object->setVar("is_online", $this->form->getInput("is_online"));
			$this->object->setVar("instructions", $this->form->getInput("ainst"));
			$this->object->setVar("submission_format", $this->form->getInput("submission_format"));
			$this->object->setVar("late_submission", $this->form->getInput("late_submission"));
			$this->object->setVar("exclude_bibliography", $this->form->getInput("exclude_bibliography"));
			$this->object->setVar("exclude_quoted_materials", $this->form->getInput("exclude_quoted_materials"));
			$this->object->setVar("exclude_type", $this->form->getInput("exclude_type"));

			switch ($this->object->getVar("exclude_type"))
			{
				case 0:
					$this->object->setVar("exclude_value", 0);
					break;
				case 1:
					$this->object->setVar("exclude_value", $this->form->getInput("exclude_value1"));
					break;
				case 2:
					$this->object->setVar("exclude_value", $this->form->getInput("exclude_value2"));
					break;
			}

			$this->object->setVar("generation_speed", $this->form->getInput("generation_speed"));
			$this->object->setVar("submit_papers_to", $this->form->getInput("submit_papers_to"));
			$this->object->setVar("students_view_reports", $this->form->getInput("students_view_reports"));

			if ($this->object->plugin_config["anon_marking"] == 1)
			{
				$this->object->setVar("anon", $this->form->getInput("anon"));
			} else {
				$this->object->setVar("anon", 0);
			}

			if ($this->object->plugin_config["translated_matching"] == 1)
			{
				$this->object->setVar("translated", $this->form->getInput("translated"));
			} else {
				$this->object->setVar("translated", 0);
			}

			// Loop through comparison checks
			$comparison_checks = array("s_paper_check", "internet_check", "journal_check"); //, "institution_check"
			foreach ($comparison_checks as $check)
			{
				$value = 0;
				if (in_array($check, $this->form->getInput("paper_compare")))
				{
					$value = 1;
				}
				$this->object->setVar($check, $value);
			}

			// ETS settings
			if ($this->object->plugin_config["grademark"] == 1 && $this->object->plugin_config["ets"] == 1)
			{
				$this->object->setVar("erater", $this->form->getInput("erater"));
				if ($this->object->getVar("erater") == 1)
				{
					$this->object->setVar("erater_handbook", $this->form->getInput("erater_handbook"));
					$this->object->setVar("erater_spelling_dictionary", $this->form->getInput("erater_spelling_dictionary"));

					// Loop through comparison checks
					$default_checks = array("erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style");
                                        foreach ($default_checks as $check)
					{
						$value = 0;
                                                $erater_defaults = $this->form->getInput("erater_defaults");
                                                if (!empty($erater_defaults))
                                                {
                                                    if (in_array($check, $erater_defaults))
                                                    {
                                                            $value = 1;
                                                    }
                                                }
						$this->object->setVar($check, $value);
					}
				}
			}

			if (empty($error))
			{
				$this->object->update();
				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
				$ilCtrl->redirect($this, "editSettings");
			}
			else
			{
				ilUtil::sendFailure(implode("<br/>", $error), true);
			}
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->output.$this->form->getHtml());
	}

	/**
	 * Format Date
	 */
	function formatDate($phpDate)
	{
		$dateArray = explode("-", $phpDate);
		return $dateArray["2"]."/".$dateArray["1"]."/".$dateArray["0"];
	}

	/**
	 * Show details
	 */
	function showDetails()
	{
		global $tpl, $ilTabs, $ilCtrl, $ilUser;

		$ilTabs->activateTab("details");

		// Get template for assignment details
		$details = new ilTemplate("tpl.assignment_details.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
		$details->setVariable("NOSCRIPT_WARNING", $this->txt("noscript_warning"));

                // Show description
                if ($this->object->getLongDescription() == "")
                {
                        $details->removeBlockData("assignment_details");
                }
                else
                {
                        $details->setVariable("TEXT_ASSIGNMENT_DETAILS_HEADER", $this->txt("description"));
                        $details->setVariable("TEXT_ASSIGNMENT_DETAILS", $this->object->getLongDescription());
                }

                // Show instructions
                if (trim($this->object->getVar("instructions")) == "")
                {
                        $details->removeBlockData("instructions");
                }
                else
                {
                        $details->setVariable("TEXT_INSTRUCTIONS_HEADER", $this->txt("special_instructions"));
                        $details->setVariable("TEXT_INSTRUCTIONS", $this->object->getVar("instructions"));
                }

                // Show dates
                $details->setVariable("TEXT_DATES_SUBMISSIONS_HEADER", $this->txt("dates_submissions_header"));

                if ($this->object->getVar("generation_speed") == "1")
                {
                        $details->setVariable("TEXT_LATE_SUBMISSIONS", $this->txt("resubmissions_allowed"));
                }
                else
                {
                        $details->setVariable("TEXT_LATE_SUBMISSIONS", $this->txt("resubmissions_not_allowed"));
                }

                if ($this->object->getVar("late_submission"))
                {
                        $details->setVariable("TEXT_RESUBMISSIONS", $this->txt("late_submissions_allowed"));
                }
                else
                {
                        $details->setVariable("TEXT_RESUBMISSIONS", $this->txt("late_submissions_not_allowed"));
                }

                $start_date_time = $this->object->getVar("start_date");
                $details->setVariable("LABEL_START_DATE", $this->txt("start_date"));
                $start_date = ilDatePresentation::formatDate(new ilDateTime(strtotime($start_date_time["time"]." ".$start_date_time["date"]),IL_CAL_UNIX));
                $details->setVariable("TEXT_START_DATE", $start_date);

                $end_date_time = $this->object->getVar("end_date");
                $details->setVariable("LABEL_END_DATE", $this->txt("end_date"));
                $end_date = ilDatePresentation::formatDate(new ilDateTime(strtotime($end_date_time["time"]." ".$end_date_time["date"]),IL_CAL_UNIX));
                $details->setVariable("TEXT_END_DATE", $end_date);

                $posting_date_time = $this->object->getVar("posting_date");
                $details->setVariable("LABEL_POST_DATE", $this->txt("post_date"));
                $posting_date = ilDatePresentation::formatDate(new ilDateTime(strtotime($posting_date_time["time"]." ".$posting_date_time["date"]),IL_CAL_UNIX));
                $details->setVariable("TEXT_POST_DATE", $posting_date);

                if (strtotime($start_date_time["date"]." ".$start_date_time["time"]) > time())
                {
                    $details->setVariable("TEXT_SUBMISSION_STATUS", $this->txt("submission_until_start"));
                    $details->removeBlockData("submit_link");
                }
                else
                {
                    if ($this->object->checkIfSubmissionAllowed($ilUser->getId()))
                    {
                        $submission_date = ilDatePresentation::formatDate(new ilDateTime(strtotime(
                                                $this->object->submissions[$ilUser->getId()]["date_submitted"]),IL_CAL_UNIX));

                        if (empty($this->object->submissions[$ilUser->getId()]))
                        {
                            $details->setVariable("TEXT_SUBMISSION_STATUS", $this->txt("no_submission_yet"));
                            $tab_heading = $this->txt("submit_paper");
                        }
                        else
                        {
                            $details->setVariable("TEXT_SUBMISSION_STATUS", $this->txt("submission_made")." ".$this->txt("on")." ".$submission_date);
                            $tab_heading = $this->txt("resubmit_paper");
                        }

                        $details->setVariable("TEXT_SUBMIT_LINK", $ilCtrl->getLinkTarget($this, "submitPaper"));
                        $details->setVariable("TEXT_SUBMIT_LINK_TEXT", $tab_heading);
                    }
                    else
                    {
                            $details->removeBlockData("submit_link");
                            if (empty($this->object->submissions[$ilUser->getId()]))
                            {
                                    $details->setVariable("TEXT_SUBMISSION_STATUS", $this->txt("submission_end_date_past"));
                            }
                            else
                            {
                                    $details->setVariable("TEXT_SUBMISSION_STATUS", $this->txt("submission_made"));
                            }
                    }
                }

                if (empty($this->object->submissions[$ilUser->getId()]))
                {
                        $details->removeBlockData("download_button");
                }
                else
                {
                        // Show View and Download links
                        $details->setVariable("LINK_VIEW_FILE", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "viewSubmission"));
                        $details->setVariable("LINK_DOWNLOAD_FILE", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "downloadSubmission"));
                        $details->setVariable("VAL_PAPER_ID", $this->object->submissions[$ilUser->getId()]["objectID"]);
                        $details->setVariable("TEMPLATE_DIR", "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/images");
                        $details->setVariable("VIEW_FILE", $this->txt("view_file"));
                        $details->setVariable("DOWNLOAD_FILE", $this->txt("download_file"));

                        $posting_date = $this->object->getVar("posting_date");
                        $posting_date = strtotime($posting_date["date"]." ".$posting_date["time"]);

                        // Show Originality report text
                        $details->setVariable("ORIGINALITY_REPORT", $this->txt("originality_report"));

                        // Display submission details
                        if ($this->object->getVar("students_view_reports"))
                        {
                                $details->removeBlockData("originality_report_not_available");
                                $details->setVariable("LINK_ORIG_REPORT", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "openOriginalityReport"));
                                if ($posting_date <= time())
                                {
                                        switch ($this->object->submissions[$ilUser->getId()]["similarityScore"])
                                        {
                                                case "-1":
                                                        $details->setVariable("TEXT_PENDING_REPORT", $this->txt("report_pending"));
                                                        $details->removeBlockData("originality_report_score");
                                                        break;
                                                default:
                                                        $score = $this->object->submissions[$ilUser->getId()]["overlap"];
                                                        $lng_overlay = "";

                                                        if ($this->object->plugin_config["translated_matching"] && $this->object->getVar("translated"))
                                                        {
                                                                if ($this->object->submissions[$ilUser->getId()]["translated_matching"]["similarityScore"] > 0
                                                                        && $this->object->submissions[$ilUser->getId()]["translated_matching"]["overlap"] > $score)
                                                                {
                                                                        $score = $this->object->submissions[$ilUser->getId()]["translated_matching"]["overlap"];
                                                                        $lng_overlay = $this->txt("eng_abbreviation");
                                                                }
                                                        }

                                                        $details->setVariable("VAL_ORIG_REPORT_SCORE", $score);
                                                        $details->setVariable("VAL_LNG_OVERLAY_TEXT", $lng_overlay);
                                                        $details->setVariable("HIDE_SCORE", "");
                                                        $details->removeBlockData("originality_report_pending");
                                                        break;
                                        }
                                }
                                else
                                {
                                        $details->setVariable("TEXT_PENDING_REPORT", $this->txt("report_pending"));
                                        $details->removeBlockData("originality_report_score");
                                }
                        }
                        else
                        {
                                $details->setVariable("ORIGINALITY_REPORT_NOT_AVAILABLE", $this->txt("originality_report_not_available"));
                                $details->removeBlockData("originality_report_pending");
                                $details->removeBlockData("originality_report_score");
                        }

                        if ($this->object->plugin_config["grademark"]
                                && $posting_date <= time() && $this->object->submissions[$ilUser->getId()]["score"] != "")
                        {
                                $details->setVariable("GRADE_HEADER", $this->txt("grade"));

                                $details->setVariable("VAL_GRADE_SCORE", $this->object->submissions[$ilUser->getId()]["score"]);
                                $details->setVariable("VALUE_MAX_GRADE", $this->object->getVar("point_value"));
                                $details->setVariable("VIEW_GRADEMARK_REPORT", $this->txt("view_grademark_report"));
                                $details->setVariable("LINK_GRADEMARK", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "openGrademark"));
                        }
                        else
                        {
                                $details->removeBlockData("grade_mark");
                        }
                }

                $output = $this->output;
                $output .= $details->get();

                $tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
		$tpl->setContent($output);
	}

	/**
	 * Show the instructors that are registered with Tii with a form below to add other tutors if there are any
	 */
	function tiiInstructors()
	{
		global $tpl, $ilTabs, $ilCtrl;
		$output = $this->output;
		$ilTabs->activateTab("tiiInstructors");

		// Get template for intro text
		$details = new ilTemplate("tpl.intro_text.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
		$details->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");

    	// Set details
    	$details->setVariable("INTRO_TEXT", $this->txt("multi_instructors_intro_text"));
		$output .= $details->get();

		// List Submissions
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilInstructorsTableGUI.php");
		$table_gui = new ilInstructorsTableGUI($this, "view");
		$output .= $table_gui->getHTML();

		$tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/show_name.js");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/open_document_viewer.js");

		// Show list of unlinked tutors
		if (count($this->object->getVar("unlinked_instructors")) > 0)
		{
			include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
			$this->form = new ilPropertyFormGUI();

			$si = new ilSelectInputGUI($this->txt("select_tutor"), "tutor_id");
        	$options = $this->object->getVar("unlinked_instructors");
        	$si->setOptions($options);
        	$this->form->addItem($si);

			$this->form->addCommandButton("addTutor", $this->txt("add_tutor"));

			$this->form->setTitle($this->txt("link_tutor_with_tii"));
			$this->form->setFormAction($ilCtrl->getFormAction($this));

			$output .= $this->form->getHTML();
		}
		$tpl->setContent($output);
	}

	/**
	 * Add Tutor to Tii
	 */
	function addTutor()
	{
		global $ilCtrl;

		$_SESSION["refresh_instructors"] = 1;
		if ($this->object->addTutorToTii())
		{
			ilUtil::sendSuccess($this->txt("tutor_added"), true);
		}
		else
		{
			ilUtil::sendFailure($this->txt("tutor_not_added"), true);
		}

		$ilCtrl->redirect($this, "tiiInstructors");
	}

	/**
	 * Remove Tutor from Tii
	 */
	function removeTutor()
	{
		global $ilCtrl;

		$_SESSION["refresh_instructors"] = 1;
		$msg = $this->object->removeUserFromClass($_POST["usr_ids"][0], "2");

		if ($msg == "Success")
		{
			ilUtil::sendSuccess($this->txt("tutor_removed"), true);
		}

		$ilCtrl->redirect($this, "tiiInstructors");
	}

	/**
	 * Show submissions
	 */
	function showSubmissions()
	{
		global $tpl, $ilTabs, $ilCtrl;

		$ilTabs->activateTab("submissions");
		$output = $this->output;

		// Get template for synch students details
		$details = new ilTemplate("tpl.submissions_buttons.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");

                $details->setVariable("NOSCRIPT_WARNING", $this->txt("noscript_warning"));

                $details->setVariable("REFRESH_LINK", $ilCtrl->getLinkTarget($this, "showSubmissions")."&refresh_submissions=1");
                $details->setVariable("REFRESH_SUBMISSIONS", $this->txt("refresh_submissions"));

                $details->setVariable("SYNC_LINK", $ilCtrl->getLinkTarget($this, "syncStudents")."&refresh_submissions=1");
                $details->setVariable("COURSE_ID", $this->object->getId());
                $details->setVariable("SYNCH_STUDENTS", $this->txt("synch_students"));
                $details->setVariable("SYNCHING", $this->txt("synching"));
                $details->setVariable("CLOSE", $this->txt("close"));
		$output .= $details->get();

		// jQuery form for enabling the student's name to be shown when clicked if anonymous marking is enabled
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$show_name_form = new ilPropertyFormGUI();

		// description
		$ta = new ilTextAreaInputGUI($this->txt("reason_anon_marking_off"), "anon_reason");
		$ta->setInfo($this->txt("note_setting_permanent"));
		$ta->setCols(46);
		$ta->setRows(6);
		$show_name_form->addItem($ta);

		$hi = new ilHiddenInputGUI("paper_id");
		$show_name_form->addItem($hi);

		$show_name_form->addCommandButton("turnOffAnonMarking", $this->txt("save"));

		$show_name_form->setTitle($this->txt("turn_off_anon_marking"));
		$show_name_form->setFormAction($ilCtrl->getFormAction($this));

		$details = new ilTemplate("tpl.show_name.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
		$details->setVariable("CLOSE", $this->txt("close"));
		$details->setVariable("REVEAL_NAME_FORM", $show_name_form->getHTML());

		$output .= $details->get();

		// List Submissions
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilSubmissionTableGUI.php");
		$table_gui = new ilSubmissionTableGUI($this, "view");
		$output .= $table_gui->getHTML();

		$tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/show_name.js");
		$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/open_document_viewer.js");

		// Show download all files link
		if ($this->object->number_of_submitted_students > 0)
		{
			$details = new ilTemplate("tpl.download_files.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
			$details->setVariable("DOWNLOAD_ALL_LINK", $ilCtrl->getLinkTarget($this, "bulkDownloadSubmissions"));
			$details->setVariable("TEXT_DOWNLOAD_FILES", $this->txt("download_all_submissions"));
			$output .= $details->get();
		}

		$tpl->setContent($output);
	}

	/**
	 * Delete submissions
	 */
	function deleteSubmissions()
	{
		global $ilCtrl;

		foreach ($_POST["submission_ids"] as $paper_id)
		{
			$this->object->deleteTiiSubmission($paper_id);
		}
		ilUtil::sendSuccess($this->txt("msg_papers_deleted"), true);

		$_SESSION["refresh_submissions"] = 1;
		$ilCtrl->redirect($this, "showSubmissions");
	}

	/**
	 * View submission
	 */
	function viewSubmission()
	{
		$this->object->openDocument($_GET["paper_id"], "viewFile");
	}

	/**
	 * Download submission
	 */
	function downloadSubmission()
	{
		$this->object->openDocument($_GET["paper_id"], "downloadFile");
	}

	/**
	 * Open originality report
	 */
	function openOriginalityReport()
	{
		$this->object->openDocument($_GET["paper_id"], "originalityReport");
	}

	/**
	 * Open grademark
	 */
	function openGrademark()
	{
		$this->object->openDocument($_GET["paper_id"], "grademark");
	}

	/**
	 * Bulk download submissions, save the ids in a csv file
	 */
	function bulkDownloadSubmissions()
	{
		//global $ilCtrl;

		/*if (count($_POST["submission_ids"]) == 0)
		{
			ilUtil::sendFailure($this->txt("no_papers_selected"), true);
			$ilCtrl->redirect($this, "showSubmissions");
		}
		else
		{*/
			$msg = $this->object->bulkDownloadTiiSubmissions();//$_POST["submission_ids"]

			if ($msg == "Success")
			{
				ilUtil::sendSuccess($this->txt("msg_papers_downloaded"), true);
			}
			else
			{
				ilUtil::sendFailure($msg, true);
			}

			$this->showSubmissions();
		//}
	}

	/**
	 * Redirect user to submission download page
	 */
	function exportSubmissions()
	{
		global $tpl, $ilCtrl, $ilTabs;

		$ilTabs->activateTab("submissions");
		$output = $this->output;

		if (count($_POST["submission_ids"]) == 0)
		{
			ilUtil::sendFailure($this->txt("no_submissions_selected"), true);
			$ilCtrl->redirect($this, "showSubmissions");
		}
		else
		{
			$_SESSION["submission_ids"] = $_POST["submission_ids"];

			// Get template for intro text
			$details = new ilTemplate("tpl.export_submissions.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");

    		// Set details
    		$details->setVariable("TEXT_DOWNLOAD_EXPORT_INTRO", $this->txt("download_export_intro"));
    		$details->setVariable("LINK_DOWNLOAD_EXPORT", $ilCtrl->getLinkTarget($this, "exportSubmissionsFile"));
    		$details->setVariable("TEXT_DOWNLOAD_EXPORT_LINK", $this->txt("here"));
			$output .= $details->get();
			$tpl->setContent($output);

			$tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/download_export.js");
		}
	}

	/**
	 * Export submission details
	 */
	function exportSubmissionsFile()
	{
		global $ilUser, $ilCtrl;

		if (count($_SESSION["submission_ids"]) == 0)
		{
			ilUtil::sendFailure($this->txt("no_submissions_selected"), true);
			$ilCtrl->redirect($this, "showSubmissions");
		}
		else
		{
			// Export CSV of submissions
			$csv_file = "export_".$ilUser->getId()."_".time().".csv";

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: text/csv; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"".$csv_file."\";");
			header("Content-Transfer-Encoding: binary");

			echo $this->txt("paper_id").",".$this->txt("author").",".$this->txt("submission_title").",".$this->txt("similarity").",";

			if ($this->object->plugin_config["grademark"])
			{
				echo $this->txt("grade").",";
			}

			echo $this->txt("submission_date")."\n";

			foreach ($_SESSION["submission_ids"] as $submission)
			{
				$user_id = $this->object->getUserIdOfSubmission($submission);

				$submission_details = $this->object->submissions[$user_id];

				echo $submission_details["objectID"].",";

				if ($submission_details["anon"] == 1)
				{
					echo $this->txt("anon_marking_enabled").",";
				}
				else
				{
					echo $submission_details["firstname"]." ".$submission_details["lastname"].",";
				}

				echo $submission_details["title"].",".$submission_details["report_score_to_show"]."%,";

				if ($this->object->plugin_config["grademark"])
				{
					echo $submission_details["score"].",";
				}

				echo date("d/m/Y", strtotime($submission_details["date_submitted"]))."\n";
			}

			unset($_SESSION["submission_ids"]);
			exit;
		}
	}

	/**
	 * Sync Students
	 */
	function syncStudents()
	{
		$synching = $this->object->syncStudentsWithTii();
		if ($synching["error"] == "N")
		{
			echo $this->txt("sync_success");
		}
		else if ($synching["error"] == "D")
		{
			echo $this->txt("sync_issue");

			$i = 0;
			foreach ($synching["problem_users"] as $user)
			{
				$i++;
				if ($i != 1)
				{
					echo ",";
				}
				echo " ".$user;
			}
		}
		else
		{
			echo $this->txt("sync_fail");

			$i = 0;
			foreach ($synching["problem_users"] as $user)
			{
				$i++;
				if ($i != 1)
				{
					echo ",";
				}
				echo " ".$user;
			}
		}
		exit;
	}

	function turnOffAnonMarking()
	{
		global $ilCtrl;
		$this->object->turnOffAnonMarking();
		$_SESSION["refresh_submissions"] = 1;
		//ilUtil::sendSuccess($this->txt("msg_paper_submitted"), true);
		$ilCtrl->redirect($this, "showSubmissions");
	}

	/**
	 * Submit Paper
	 */
	function submitPaper()
	{
            global $tpl, $ilTabs, $ilUser;

            $output = $this->output;
            $ilTabs->activateTab("submit");

            $this->initSubmitPaperForm();

            if ($this->object->checkIfSubmissionAllowed($ilUser->getId()) && !empty($this->object->submissions[$ilUser->getId()]["objectID"]))
            {
                // Get template for assignment details
                $details = new ilTemplate("tpl.resubmission.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
                $details->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");

                // Set details
                $details->setVariable("RESUBMISSION_NOTICE", $this->txt("resubmission_notice"));

                $output .= $details->get();
            }

            $output .= $this->form->getHTML();

            $tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/switch_paper_source.js");
            $tpl->setContent($output);
	}

	function initSubmitPaperForm()
	{
            global $ilCtrl;

            include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
            $this->form = new ilPropertyFormGUI();

            // Show list of unsubmitted students so that tutor can submit for them
            if ($this->object->course_details["isAdmin"] == true || $this->object->course_details["isTutor"] == true)
            {
                    $si = new ilSelectInputGUI($this->txt("select_student"), "student_id");

            if ($this->object->getVar("generation_speed") != "0")
            {
                    $options = $this->object->getVar("all_students");
            }
            else
            {
                    $options = $this->object->getVar("unsubmitted_students");
            }

            $si->setOptions($options);
            $this->form->addItem($si);
            }

            // title
            $ti = new ilTextInputGUI($this->txt("submission_title"), "title");
            $this->form->addItem($ti);

            // Submission format
            if ($this->object->submission_format == 0)
            {
                $si = new ilSelectInputGUI($this->txt("submission_format"), "submission_format");
                $options = array(
                            "2" => $this->txt("file"),
                            "1" => $this->txt("text")
                    );
                $si->setOptions($options);
                $this->form->addItem($si);
            }
            else
            {
                $hi = new ilHiddenInputGUI("submission_format");
                $hi->setValue($this->object->submission_format);
                $this->form->addItem($hi);
            }

            // file
            if (($this->object->submission_format == 0 || $this->object->submission_format == 2)
                    )// && (empty($_REQUEST["submission_format"]) || $_REQUEST["submission_format"] == 2)
            {
                    $fi = new ilFileInputGUI($this->txt("browse_file"), "paper");
                    $this->form->addItem($fi);
            }

            // cut and paste
            if (($this->object->submission_format == 0 || $this->object->submission_format == 1)
                    )// && (empty($_REQUEST["submission_format"]) || $_REQUEST["submission_format"] == 1)
            {
                    $ta = new ilTextAreaInputGUI($this->txt("cut_and_paste_paper"), "paper_text");
                    $ta->setCols(100);
                    $ta->setRows(8);
                    $this->form->addItem($ta);
            }

            $this->form->addCommandButton("uploadPaper", $this->txt("upload"));

            $this->form->setTitle($this->txt("submit_paper"));
            $this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	 * Submit Paper
	 */
	function uploadPaper()
	{
		global $tpl, $ilCtrl, $ilTabs;

		$output = $this->output;
		$this->initSubmitPaperForm();

                $msg = $this->object->uploadPaper();

                if ($msg == "Success")
                {
                        $_SESSION["refresh_submissions"] = 1;
                        ilUtil::sendSuccess($this->txt("msg_paper_submitted"), true);

                        if ($this->object->course_details["isAdmin"] == true || $this->object->course_details["isTutor"] == true)
                        {
                                $ilCtrl->redirect($this, "showSubmissions");
                        }
                        else
                        {
                                $ilCtrl->redirect($this, "showDetails");
                        }
                }
                else
                {
                        $ilTabs->activateTab("submit");

                        $this->form->setValuesByPost();
                        $output .= $this->form->getHtml();
                        $tpl->addJavaScript("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/js/switch_paper_source.js");
                        $tpl->setContent($output);

                        ilUtil::sendFailure($msg, true);
                }
	}
}
?>