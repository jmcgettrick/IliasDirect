<?php

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
 
/**
 * Turnitin Assignment configuration user interface class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilTurnitinAssignmentConfigGUI extends ilPluginConfigGUI
{
	/**
	* Handles all commmands, default is "configure"
	*/
	function performCommand($cmd)
	{
		if ($cmd == "view")
		{
			if ($_REQUEST["user_table_nav"] || $_REQUEST["user_trows"])
			{
				$cmd = "tiiShowUsers";
			}
			if ($_REQUEST["log_table_nav"] || $_REQUEST["log_trows"])
			{
				$cmd = "tiiLog";
			}
		}

		switch ($cmd)
		{
			case "configure":
			case "save":
			case "defaultAssignment":
			case "updateDefault":
			case "tiiLog":
			case "tiiMySQLDump":
			case "unlinkUsers":
			case "linkOrRelinkUsers":
			case "exportUsers":
			case "tiiShowUsers":
			case "testTiiConnection":
			case "exportLog":
			case "exportMySQL":
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Set tabs
	 */
	function setTabs($tab_to_activate = "configuration")
	{
		global $ilTabs, $ilCtrl, $lng;
		
		$ilTabs->addTab("configuration", $lng->txt("rep_robj_xtii_configuration"), $ilCtrl->getLinkTarget($this, "configure"));
		$ilTabs->addTab("default_assignment", $lng->txt("rep_robj_xtii_default_assignment_settings"), $ilCtrl->getLinkTarget($this, "defaultAssignment"));
		$ilTabs->addTab("test_connection", $lng->txt("rep_robj_xtii_test_connection"), $ilCtrl->getLinkTarget($this, "testTiiConnection"));
		$ilTabs->addTab("tii_log", $lng->txt("rep_robj_xtii_tii_log"), $ilCtrl->getLinkTarget($this, "tiiLog"));
		$ilTabs->addTab("tii_mysql_dump", $lng->txt("rep_robj_xtii_tii_mysql_dump"), $ilCtrl->getLinkTarget($this, "tiiMySQLDump"));
		$ilTabs->addTab("tii_unlink_users", $lng->txt("rep_robj_xtii_tii_un_re_link_users"), $ilCtrl->getLinkTarget($this, "tiiShowUsers"));
		
		$ilTabs->activateTab($tab_to_activate);
	}

	/**
	 * Configure screen
	 */
	function configure()
	{
		global $tpl;
		
		$this->setTabs("configuration");
				
		$form = $this->initConfigurationForm();
				
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Init configuration form.
	 *
	 * @return object form object
	 */
	public function initConfigurationForm()
	{
		global $lng, $ilCtrl;		
	
		$pl = $this->getPluginObject();
		$pl->getConfigData();
			
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// API URL
		$ti = new ilTextInputGUI($pl->txt("api_url"), "api_url");
		$ti->setRequired(true);		
		$ti->setMaxLength(255);
		$ti->setSize(50);
		$form->addItem($ti);
		
		// Account id
		$ti = new ilTextInputGUI($pl->txt("account_id"), "account_id");
		$ti->setRequired(true);
		$ti->setValidationRegexp("/^\d+$/");
		$ti->setMaxLength(10);
		$ti->setSize(20);
		$form->addItem($ti);
	
		// Shared key
		$ti = new ilTextInputGUI($pl->txt("shared_key"), "shared_key");
		$ti->setRequired(true);		
		$ti->setMaxLength(10);
		$ti->setSize(10);
		$ti->setInfo($pl->txt("setup_in_tii"));
		$form->addItem($ti);
		
		// Sub Header for Enabling Features
        $section = new ilFormSectionHeaderGUI();
		$section->setTitle($pl->txt('enable_features'));
		$form->addItem($section);
		
		// Student emails
		$cb = new ilCheckboxInputGUI($pl->txt("student_emails"), "student_emails");
		$form->addItem($cb);
		
		// Instructor emails
		$cb = new ilCheckboxInputGUI($pl->txt("instructor_emails"), "instructor_emails");
		$form->addItem($cb);
		
		// Digital receipts
		$cb = new ilCheckboxInputGUI($pl->txt("digital_receipts"), "digital_receipts");
		$form->addItem($cb);
		
		// Grademark
		$cb = new ilCheckboxInputGUI($pl->txt("grademark"), "grademark");
		$form->addItem($cb);
		
		// ETS
		$cb = new ilCheckboxInputGUI($pl->txt("ets"), "ets");
		$form->addItem($cb);
		
		// Translated Matching
		$cb = new ilCheckboxInputGUI($pl->txt("translated_matching"), "translated_matching");
		$form->addItem($cb);
		
		// Anonymous Marking
		$cb = new ilCheckboxInputGUI($pl->txt("anon_marking"), "anon_marking");
		$form->addItem($cb);
		
		// Institutional Repository
		$cb = new ilCheckboxInputGUI($pl->txt("institutional_repository"), "institutional_repository");
		$form->addItem($cb);
	
		$form->addCommandButton("save", $lng->txt("save"));
	                
		$form->setTitle($pl->txt("turnitin_assignment_plugin_configuration"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		// Set Form values		
		$values["api_url"] = $pl->getVar("api_url");
		$values["account_id"] = $pl->getVar("account_id");
		$values["shared_key"] = $pl->getVar("shared_key");
		$values["grademark"] = $pl->getVar("grademark");
		$values["ets"] = $pl->getVar("ets");
		$values["anon_marking"] = $pl->getVar("anon_marking");
		$values["translated_matching"] = $pl->getVar("translated_matching");
		$values["institutional_repository"] = $pl->getVar("institutional_repository");
		$values["student_emails"] = $pl->getVar("student_emails");
		$values["instructor_emails"] = $pl->getVar("instructor_emails");
		$values["digital_receipts"] = $pl->getVar("digital_receipts");
		$form->setValuesByArray($values);
		
		return $form;
	}
		
	/**
	 * Save form input
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initConfigurationForm();
		if ($form->checkInput())
		{
			$pl->setVar("api_url", trim($form->getInput("api_url")));
			$pl->setVar("account_id", trim($form->getInput("account_id")));
			$pl->setVar("shared_key", trim($form->getInput("shared_key")));
			$pl->setVar("grademark", $form->getInput("grademark"));
			$pl->setVar("ets", $form->getInput("ets"));
			$pl->setVar("anon_marking", $form->getInput("anon_marking"));
			$pl->setVar("translated_matching", $form->getInput("translated_matching"));
			$pl->setVar("institutional_repository", $form->getInput("institutional_repository"));
			$pl->setVar("student_emails", $form->getInput("student_emails"));
			$pl->setVar("instructor_emails", $form->getInput("instructor_emails"));
			$pl->setVar("digital_receipts", $form->getInput("digital_receipts"));
			$pl->updateConfigData();

			ilUtil::sendSuccess($pl->txt("config_saved"), true);
			$ilCtrl->redirect($this, "configure");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
	
	/**
	 * Save Default Assignment settings that will be used when creating a new assignment 
	 */
	function defaultAssignment()
	{
		global $tpl;
		
		$this->setTabs("default_assignment");
				
		$form = $this->initDefaultAssignmentForm();

		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Get Default Assignment setting values
	 */
	function initDefaultAssignmentForm()
	{
		global $lng, $ilCtrl;		
		
		$pl = $this->getPluginObject();
		$pl->getDefaultAssignmentData();
		$pl->getConfigData();
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// is_online
		$cb = new ilCheckboxInputGUI($lng->txt("online"), "is_online");
		$form->addItem($cb);
		
		// point value
		$ti = new ilTextInputGUI($lng->txt("rep_robj_xtii_point_value"), "point_value");
		$ti->setRequired(true);
		$ti->setMaxLength(4);
		$form->addItem($ti);
		
		// Sub Header for Optional Settings
        $section = new ilFormSectionHeaderGUI();
		$section->setTitle($lng->txt('rep_robj_xtii_optional_settings'));
		$form->addItem($section);
        
        // Special instructions
		$ta = new ilTextAreaInputGUI($lng->txt("rep_robj_xtii_special_instructions"), "ainst");
		$ta->setCols(100);
		$ta->setRows(8);
		$form->addItem($ta);
		
		// Submission Format
		$si = new ilSelectInputGUI($lng->txt("rep_robj_xtii_submission_format"), "submission_format");
        $options = array(
        		"0" => $lng->txt("rep_robj_xtii_any"),
        		"1" => $lng->txt("rep_robj_xtii_text"),
        		"2" => $lng->txt("rep_robj_xtii_file")
        	);
        $si->setOptions($options);
        $form->addItem($si);
		
		// Set whether late submissions are allowed
		$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_late_submissions"), "late_submission");
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
		$rg->addOption($ro);
		$form->addItem($rg);
		
		// Sub Header for Optional Settings
        $section = new ilFormSectionHeaderGUI();
		$section->setTitle($lng->txt('rep_robj_xtii_originality_report_settings'));
		$form->addItem($section);
		
		// When to generate report
        $si = new ilSelectInputGUI($lng->txt("rep_robj_xtii_report_generation_speed"), "generation_speed");
        $options = array(
        		"0" => $lng->txt("rep_robj_xtii_immediately_first"),
        		"1" => $lng->txt("rep_robj_xtii_immediately_overwrite"),
        		"2" => $lng->txt("rep_robj_xtii_on_due_date")
        	);
        $si->setOptions($options);
        $form->addItem($si);
		
		// Exclude Bibliography
		$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_exclude_bibliography"), "exclude_bibliography");
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
		$rg->addOption($ro);
		if (count($pl->object->submissions))
		{
			$rg->setDisabled(true);
		}
		$form->addItem($rg);
		
		// Exclude Quotes
		$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_exclude_quoted_materials"), "exclude_quoted_materials");
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
		$rg->addOption($ro);
		if (count($pl->object->submissions))
		{
			$rg->setDisabled(true);
		}
		$form->addItem($rg);
		
		// Exclude small matches
		$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_exclude_type"), "exclude_type");
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
		$rg->addOption($ro);
				
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_exclude_type_by_word"), "1");		
			// nest percentage option
        	$word_count = new ilTextInputGUI($lng->txt("rep_robj_xtii_word_count"), "exclude_value1");
        	$word_count->setRequired(true);
        	$ro->addSubItem($word_count);		
		$rg->addOption($ro);
		
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_exclude_type_by_percent"), "2");
			// nest word count option
        	$percentage = new ilTextInputGUI($lng->txt("rep_robj_xtii_percentage"), "exclude_value2");
        	$percentage->setRequired(true);
        	$ro->addSubItem($percentage);
		$rg->addOption($ro);
		$form->addItem($rg);
		
		// Set whether students can view originality reports
		$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_students_allowed_to_view_reports"), "students_view_reports");
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");
		$rg->addOption($ro);
		$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
		$rg->addOption($ro);
		$form->addItem($rg);
		
		if ($pl->translated_matching == 1)
		{
			// Enable translated matching
			$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_enable_translated_matching"), "translated");
			$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");
			$rg->addOption($ro);
			$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
			$rg->addOption($ro);
			$form->addItem($rg);
		}

		if ($pl->anon_marking == 1)
		{
			// Enable anonymous marking
			$rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_enable_anon_marking"), "anon");
			$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");
			$rg->addOption($ro);
			$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
			$rg->addOption($ro);
			$form->addItem($rg);
		}
		       
        // Repository to submit papers to
        $si = new ilSelectInputGUI($lng->txt("rep_robj_xtii_submit_papers_to"), "submit_papers_to");
        $options = array(
        		"0" => $lng->txt("rep_robj_xtii_no_repository"),
        		"1" => $lng->txt("rep_robj_xtii_standard_repository")        
        	);
		if ($pl->institutional_repository == 1)
		{
			$options["2"] = $lng->txt("rep_robj_xtii_institutional_repository");
		}	
        $si->setOptions($options);
        $form->addItem($si);
        
        // Comparison options
        $cbg = new ilCheckboxGroupInputGUI($lng->txt("rep_robj_xtii_comparison_options"), "paper_compare");
        foreach (array("s_paper_check", "internet_check", "journal_check") as $option_value) // "institution_check"
        {
        	$cb = new ilCheckboxInputGUI($lng->txt("rep_robj_xtii_".$option_value), $option_value);
        	$cb->setValue($option_value);
        	$cbg->addOption($cb);
        }
        $form->addItem($cbg);
        
        if ($pl->grademark == 1 && $pl->ets == 1)
		{
			// E-rater Grammar check
	        $rg = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_enable_erater_grammar_check"), "erater");
			$ro = new ilRadioOption($lng->txt("rep_robj_xtii_no"), "0");
			$rg->addOption($ro);
			
			$ro = new ilRadioOption($lng->txt("rep_robj_xtii_yes"), "1");		
				// nest select ets handbook option
	        	$si = new ilSelectInputGUI($lng->txt("rep_robj_xtii_select_ets_handbook"), "erater_handbook");
		        $options = array(
		        		"1" => $lng->txt("rep_robj_xtii_advanced"),
		        		"2" => $lng->txt("rep_robj_xtii_high_school"),
		        		"3" => $lng->txt("rep_robj_xtii_middle_school"),
		        		"4" => $lng->txt("rep_robj_xtii_elementary"),
		        		"5" => $lng->txt("rep_robj_xtii_english_learners")
		        	);
		        $si->setOptions($options);
		        $ro->addSubItem($si);
	        	
	        	// nest select english dictionary option
	        	$rg2 = new ilRadioGroupInputGUI($lng->txt("rep_robj_xtii_select_english_dictionary"), "erater_spelling_dictionary");
	        	$ro2 = new ilRadioOption($lng->txt("rep_robj_xtii_en_us"), "en_US");
				$rg2->addOption($ro2);
				$ro2 = new ilRadioOption($lng->txt("rep_robj_xtii_en_gb"), "en_GB");
				$rg2->addOption($ro2);
				$ro2 = new ilRadioOption($lng->txt("rep_robj_xtii_en_both"), "en");
				$rg2->addOption($ro2);
				$ro->addSubItem($rg2);
				
				// Comparison options
		        $cbg = new ilCheckboxGroupInputGUI($lng->txt("rep_robj_xtii_categories_enabled_default"), "erater_defaults");
		        foreach (array("erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style") as $option_value)
		        {
		        	$cb = new ilCheckboxInputGUI($pl->txt($option_value), $option_value);
		        	$cb->setValue($option_value);
		        	$cbg->addOption($cb);
		        }
		        $ro->addSubItem($cbg);
				$rg->addOption($ro);
	
			$form->addItem($rg);
		}
		
		$form->addCommandButton("updateDefault", $lng->txt("rep_robj_xtii_save"));
		$form->setFormAction($ilCtrl->getFormAction($this));
		
	    $form->setTitle($lng->txt("rep_robj_xtii_edit_settings"));
		
		// Set Form values		
		$values["is_online"] = $pl->getVar("is_online");
		$values["point_value"] = $pl->getVar("point_value");
		$values["instructions"] = $pl->getVar("instructions");
		$values["exclude_type"] = $pl->getVar("exclude_type");
		$values["exclude_value"] = $pl->getVar("exclude_value");
		$values["submit_papers_to"] = $pl->getVar("submit_papers_to");
		$values["generation_speed"] = $pl->getVar("generation_speed");
		$values["exclude_bibliography"] = $pl->getVar("exclude_bibliography");
		$values["exclude_quoted_materials"] = $pl->getVar("exclude_quoted_materials");
		$values["submission_format"] = $pl->getVar("submission_format");
		$values["late_submission"] = $pl->getVar("late_submission");
		$values["students_view_reports"] = $pl->getVar("students_view_reports");
		$values["anon"] = $pl->getVar("anon");
		$values["translated"] = $pl->getVar("translated");
		
		// Loop through comparison checks
		$comparison_check_vars = array("s_paper_check", "internet_check", "journal_check"); // , "institution_check"
		foreach ($comparison_check_vars as $comparison_check)
		{
			$comparison_values[$comparison_check] = $pl->getVar($comparison_check);
			if ($comparison_values[$comparison_check] == 1)
			{
				$values["paper_compare"][] = $comparison_check;
			}
		}

		$values["erater"] = $pl->getVar("erater");
		$values["erater_handbook"] = $pl->getVar("erater_handbook");
		$values["erater_spelling_dictionary"] = $pl->getVar("erater_spelling_dictionary");
		$values["erater_spelling"] = $pl->getVar("erater_spelling");
		$values["erater_grammar"] = $pl->getVar("erater_grammar");
		$values["erater_usage"] = $pl->getVar("erater_usage");
		$values["erater_mechanics"] = $pl->getVar("erater_mechanics");
		$values["erater_style"] = $pl->getVar("erater_style");
		$form->setValuesByArray($values);

		return $form;
	}
	
	/**
	 * Update default assignment settings
	 */
	function updateDefault()
	{
		global $tpl, $lng, $ilCtrl;
	
		$pl = $this->getPluginObject();
		
		$form = $this->initDefaultAssignmentForm();
		
		if ($form->checkInput())
		{
			$pl->setVar("is_online", $form->getInput("is_online"));
			$pl->setVar("point_value", $form->getInput("point_value"));
			$pl->setVar("instructions", $form->getInput("instructions"));
			$pl->setVar("exclude_type", $form->getInput("exclude_type"));
			$pl->setVar("exclude_value", $form->getInput("exclude_value"));
			$pl->setVar("submit_papers_to", $form->getInput("submit_papers_to"));
			$pl->setVar("generation_speed", $form->getInput("generation_speed"));
			$pl->setVar("exclude_bibliography", $form->getInput("exclude_bibliography"));
			$pl->setVar("exclude_quoted_materials", $form->getInput("exclude_quoted_materials"));
			$pl->setVar("submission_format", $form->getInput("submission_format"));
			$pl->setVar("late_submission", $form->getInput("late_submission"));
			$pl->setVar("students_view_reports", $form->getInput("students_view_reports"));
			$pl->setVar("anon", $form->getInput("anon"));
			$pl->setVar("translated", $form->getInput("translated"));
			
			// Loop through comparison checks
			$comparison_checks = array("s_paper_check", "internet_check", "journal_check"); //, "institution_check"
			foreach ($comparison_checks as $check)
			{
				$value = 0;
				if (in_array($check, $form->getInput("paper_compare")))
				{
					$value = 1;
				}
				$pl->setVar($check, $value);
			}
			
			$pl->setVar("erater", $form->getInput("erater"));
			$pl->setVar("erater_handbook", $form->getInput("erater_handbook"));
			$pl->setVar("erater_spelling_dictionary", $form->getInput("erater_spelling_dictionary"));
			$pl->setVar("erater_spelling", $form->getInput("erater_spelling"));
			$pl->setVar("erater_grammar", $form->getInput("erater_grammar"));
			$pl->setVar("erater_usage", $form->getInput("erater_usage"));
			$pl->setVar("erater_mechanics", $form->getInput("erater_mechanics"));
			$pl->setVar("erater_style", $form->getInput("erater_style"));
			$pl->updateDefaultAssignmentData($pl);

			ilUtil::sendSuccess($pl->txt("default_assignment_data_saved"), true);
			$ilCtrl->redirect($this, "defaultAssignment");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}		
	}
	
	/**
	 * Test the connection to Turnitin - just creates admin as a tutor
	 */
	function testTiiConnection()
	{
		global $tpl, $ilUser, $lng;
		
		$this->setTabs("test_connection");
		
		if ($this->simpleCreateUser($ilUser->getID(), 2))
		{
			$output = $lng->txt("rep_robj_xtii_tii_connection_successful");
		}
		else
		{
			$output = $lng->txt("rep_robj_xtii_tii_connection_failed");
		}
		
		$tpl->setContent($output);	
	}
	
	function simpleCreateUser($usr_id, $user_type = 1)
	{
		global $ilDB;
		
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilObjTurnitinAssignment.php");
		include_once("./Services/User/classes/class.ilObjUser.php");
		
		$user_details = new ilObjUser($usr_id);
		
		$tii_vars = array(
				"uem" => $user_details->getEmail(), 
				"ufn" => $user_details->getFirstname(), 
				"uln" => $user_details->getLastname(), 
				"utp" => $user_type
			);

		// Use username as Tii Password, must be 6 letters so double it up if need be e.g. rootroot
		/*$password = $user_details->getLogin();
		if (strlen($password) < 6)
		{
			$password .= $user_details->getLogin();
		}
		$tii_vars["upw"] = $password;*/
		
		if (ilObjTurnitinAssignment::getTiiUserId($usr_id) != 0)
		{
			$tii_vars["uid"] = ilObjTurnitinAssignment::getTiiUserId($usr_id);
		}
		
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentTiiCall.php");
		$tii_call = new ilTurnitinAssignmentTiiCall();
		
		$tii_response = $tii_call->tiiCall("1", "2", $tii_vars);
		
		if ($tii_response["status"] == "Success") 
		{ 
			$ilDB->manipulate("INSERT INTO rep_robj_xtii_users (usr_id, tii_usr_id) ".
						" VALUES (".$ilDB->quote($usr_id, "integer").", ".$ilDB->quote($tii_response["userid"], "integer").")".
						" ON DUPLICATE KEY UPDATE tii_usr_id = ".$ilDB->quote($tii_response["userid"], "integer"));					
			
			$msg = "User I(".$usr_id.") T(".$tii_response["userid"].")";
			
			ilObjTurnitinAssignment::logAction("1", "2", $tii_response["status"], $tii_response["rcode"], $msg);
			return (int)$tii_response["userid"];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Tii log screen - show entries from the last 10 days
	 */
	function tiiLog()
	{
		global $tpl, $ilDB, $lng, $ilCtrl;
		
		$this->setTabs("tii_log");
		
		$query = $ilDB->query("SELECT * FROM rep_robj_xtii_log ".
			" WHERE date_time >= ".$ilDB->quote(date("Y-m-d H:i:s", strtotime("20 days ago")), "timestamp").
			" ORDER BY date_time DESC, id DESC"
			);
		
		while ($rec = $ilDB->fetchAssoc($query))
		{
			$data[$rec["id"]] = $rec;
		}
		
		// Show Log
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilLogTiiTableGUI.php");
		$table_gui = new ilLogTiiTableGUI($this, "view", $data);
		$tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
		
		$output = $table_gui->getHTML();
		
		// Show export records form
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// Start Date
		$si = new ilSelectInputGUI($lng->txt("rep_robj_xtii_start_from"), "date_from");
    	for ($i=0; $i <= 20; $i++)
    	{
    		$options[date("Y-m-d H:i:s", time()-24*60*60*$i)] = date("D jS F Y", time()-24*60*60*$i);
    	}
    	$si->setOptions($options);
    	$this->form->addItem($si);
		
		// End Date
		$si = new ilSelectInputGUI($lng->txt("rep_robj_xtii_until"), "date_to");
    	$si->setOptions($options);
    	$this->form->addItem($si);
		
		$this->form->addCommandButton("exportLog", $lng->txt("rep_robj_xtii_export_logs"));
                
		$this->form->setTitle($lng->txt("rep_robj_xtii_export_logs"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	
		$output .= $this->form->getHTML();
		
		$tpl->setContent($output);
	}
	
	/**
	 * 
	 */
	function exportLog()
	{
		global $ilDB, $lng, $ilCtrl;
		
		$query = $ilDB->query("SELECT * FROM rep_robj_xtii_log ".
			" WHERE date_time >= ".$ilDB->quote(date("Y-m-d", strtotime($_REQUEST["date_from"]))." 00:00:00", "timestamp").
			" AND date_time <= ".$ilDB->quote(date("Y-m-d", strtotime($_REQUEST["date_to"]))." 23:59:59", "timestamp").
			" ORDER BY date_time DESC, id DESC"
			);
		
		if ($ilDB->numRows($query) > 0)
		{
			// Export CSV of submissions
			$csv_file = "export_logs_".time().".csv";
			
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: text/csv; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"".$csv_file."\";");
			header("Content-Transfer-Encoding: binary");
			
			echo "Logs from ".date("D jS F Y", strtotime($_REQUEST["date_from"]))." to ".date("D jS F Y", strtotime($_REQUEST["date_to"]))."\n";
			
			echo $lng->txt("rep_robj_xtii_date_time").",".$lng->txt("rep_robj_xtii_usr_id").",".$lng->txt("rep_robj_xtii_fid").",";
			echo $lng->txt("rep_robj_xtii_fcmd").",".$lng->txt("rep_robj_xtii_status").",".$lng->txt("rep_robj_xtii_rcode").",";
			echo $lng->txt("rep_robj_xtii_msg")."\n";
			
			while ($rec = $ilDB->fetchAssoc($query))
			{
				echo $rec["date_time"].",".$rec["usr_id"].",".$rec["fid"].",".$rec["fcmd"].",".$rec["status"].",".$rec["rcode"].",".$rec["msg"]."\n";
			}
			
			exit;
		}
		else
		{
			$ilCtrl->redirect($this, "tiiLog");	
		}
	}
	
	/**
	 * Show a MySQL dump of Tii tables
	 */
	function tiiMySQLDump()
	{
		global $tpl, $ilCtrl, $lng;
		
		$pl = $this->getPluginObject();
		$this->setTabs("tii_mysql_dump");
		
		$mysql_data = $pl->getMySQLData();
        
        // Define template and add CSS file in        
        $list_tpl = new ilTemplate("tpl.mysql_data_dump.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
        $list_tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
        $list_tpl->fillCssFiles();
        
        $list_tpl->setVariable("COURSES_HEADER", $lng->txt("rep_robj_xtii_mysql_dump_courses"));
        $list_tpl->setVariable("TABLE_HEADER_COURSE_TITLE", $lng->txt("rep_robj_xtii_mysql_dump_title"));
        $list_tpl->setVariable("TABLE_HEADER_ILIAS_OBJ_ID", $lng->txt("rep_robj_xtii_mysql_dump_ilias_obj_id"));
        $list_tpl->setVariable("TABLE_HEADER_ILIAS_REF_ID", $lng->txt("rep_robj_xtii_mysql_dump_ilias_ref_id"));
        $list_tpl->setVariable("TABLE_HEADER_TII_ID", $lng->txt("rep_robj_xtii_mysql_dump_turnitin_id"));
        $list_tpl->setVariable("TABLE_HEADER_COURSE_TUTOR_ID", $lng->txt("rep_robj_xtii_mysql_dump_course_tutor_id"));
        $list_tpl->setVariable("TABLE_HEADER_COURSE_TUTOR_TII_ID", $lng->txt("rep_robj_xtii_mysql_dump_course_tutor_tii_id"));
        
		// Show Courses
        foreach($mysql_data["courses"] as $k => $v)
        {
            $list_tpl->setCurrentBlock("course_list_block");
			$list_tpl->setVariable("COURSE_TITLE", $v["title"]);
			$list_tpl->setVariable("COURSE_OBJ_ID", $k);
			$list_tpl->setVariable("COURSE_REF_ID", $v["ref_id"]);
			$list_tpl->setVariable("COURSE_TII_ID", $v["tii_course_id"]);
			$list_tpl->setVariable("COURSE_TUTOR", $v["tutor_id"]);
			$list_tpl->setVariable("COURSE_TII_TUTOR", $v["tii_tutor_id"]);
			$list_tpl->parseCurrentBlock();
        }
        $list_tpl->touchBlock("course_list_footer");
        
        $list_tpl->setVariable("ASSIGNMENTS_HEADER", $lng->txt("rep_robj_xtii_mysql_dump_assignments"));
        $list_tpl->setVariable("TABLE_HEADER_ASSIGNMENT_TITLE", $lng->txt("rep_robj_xtii_mysql_dump_title"));
        $list_tpl->setVariable("TABLE_HEADER_ILIAS_OBJ_ID", $lng->txt("rep_robj_xtii_mysql_dump_ilias_obj_id"));
        $list_tpl->setVariable("TABLE_HEADER_ILIAS_REF_ID", $lng->txt("rep_robj_xtii_mysql_dump_ilias_ref_id"));
        $list_tpl->setVariable("TABLE_HEADER_ILIAS_COURSE_REF_ID", $lng->txt("rep_robj_xtii_mysql_dump_ilias_course_ref_id"));
        $list_tpl->setVariable("TABLE_HEADER_TII_ID", $lng->txt("rep_robj_xtii_mysql_dump_turnitin_id"));
                
        // Show Assignments
        foreach($mysql_data["assignments"] as $k => $v)
        {
            $list_tpl->setCurrentBlock("assignment_list_block");
			$list_tpl->setVariable("ASSIGNMENT_TITLE", $v["title"]);
			$list_tpl->setVariable("ASSIGNMENT_OBJ_ID", $k);
			$list_tpl->setVariable("ASSIGNMENT_REF_ID", $v["ref_id"]);
			$list_tpl->setVariable("ASSIGNMENT_COURSE_REF_ID", $v["course_ref_id"]);
			$list_tpl->setVariable("ASSIGNMENT_TII_ID", $v["tii_assign_id"]);
			$list_tpl->parseCurrentBlock();
        }
        $list_tpl->touchBlock("assignment_list_footer");
        
        $list_tpl->setVariable("SUBMISSIONS_HEADER", $lng->txt("rep_robj_xtii_mysql_dump_submissions"));
        $list_tpl->setVariable("TABLE_HEADER_SUB_REF_ID", $lng->txt("rep_robj_xtii_mysql_dump_submission_ref_id"));
        $list_tpl->setVariable("TABLE_HEADER_SUB_TII_ID", $lng->txt("rep_robj_xtii_mysql_dump_submission_tii_id"));
        $list_tpl->setVariable("TABLE_HEADER_ASS_REF_ID", $lng->txt("rep_robj_xtii_mysql_dump_assignment_ref_id"));
        $list_tpl->setVariable("TABLE_HEADER_USER_ID", $lng->txt("rep_robj_xtii_mysql_dump_ilias_user_id"));
        $list_tpl->setVariable("TABLE_HEADER_TII_USER_ID", $lng->txt("rep_robj_xtii_mysql_dump_tii_user_id"));
        $list_tpl->setVariable("TABLE_HEADER_TII_USER_EMAIL", $lng->txt("rep_robj_xtii_mysql_dump_date_time"));
        $list_tpl->setVariable("TABLE_HEADER_TII_DATE_TIME", $lng->txt("rep_robj_xtii_mysql_dump_user_email"));
        
		// Show Submissions
        if (count($mysql_data["submissions"]) > 0)
        {
	        foreach($mysql_data["submissions"] as $k => $v)
	        {
	        	$list_tpl->setCurrentBlock("submission_list_block");
	        	$list_tpl->setVariable("SUBMISSION_REF_ID", $v["id"]);
	        	$list_tpl->setVariable("SUBMISSION_TII_ID", $v["tii_paper_id"]);
	        	$list_tpl->setVariable("ASSIGNMENT_REF_ID", $v["assign_id"]);
	        	$list_tpl->setVariable("USER_ID", $v["usr_id"]);
	        	$list_tpl->setVariable("USER_TII_ID", $v["tii_usr_id"]);
	        	$list_tpl->setVariable("USER_EMAIL", $v["email"]);
	        	$list_tpl->setVariable("SUBMISSION_DATE_TIME", $v["submission_date_time"]);        	
	        	$list_tpl->parseCurrentBlock();
        	}
        }
        $list_tpl->touchBlock("submission_list_footer");
        
        $list_tpl->setVariable("EXPORT_LINK", $ilCtrl->getLinkTarget($this, "exportMySQL"));
        $list_tpl->setVariable("EXPORT_LINK_TEXT", $lng->txt("rep_robj_xtii_export_mysql"));
        
        $output = $list_tpl->get();
                
		$tpl->setContent($output);
	}
	
	/**
	 * Export MySQL dump
	 */
	function exportMySQL()
	{
		global $lng;
		
		$this->setTabs("tii_mysql_dump");
		
		$pl = $this->getPluginObject();
		$mysql_data = $pl->getMySQLData();
		
		// Export CSV of submissions
		$csv_file = "export_mysql_".time().".csv";
			
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"".$csv_file."\";");
		header("Content-Transfer-Encoding: binary");
			
		echo "MySQL Dump from ".date("H:I:s D jS F Y")."\n";
		
		echo "\n".$lng->txt("rep_robj_xtii_mysql_dump_courses")."\n";
		
		echo $lng->txt("rep_robj_xtii_mysql_dump_title").",".$lng->txt("rep_robj_xtii_mysql_dump_ilias_obj_id").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_ilias_ref_id").",".$lng->txt("rep_robj_xtii_mysql_dump_turnitin_id").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_course_tutor_id").",".$lng->txt("rep_robj_xtii_mysql_dump_course_tutor_tii_id")."\n";
		
		foreach($mysql_data["courses"] as $k => $v)
        {
        	echo $v["title"].",".$k.",".$v["ref_id"].",".$v["tii_course_id"].",".$v["tutor_id"].",".$v["tii_tutor_id"]."\n";
        }

		echo "\n".$lng->txt("rep_robj_xtii_mysql_dump_assignments")."\n";
		
		echo $lng->txt("rep_robj_xtii_mysql_dump_title").",".$lng->txt("rep_robj_xtii_mysql_dump_ilias_obj_id").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_ilias_ref_id").",".$lng->txt("rep_robj_xtii_mysql_dump_ilias_course_ref_id").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_turnitin_id")."\n";
		
		foreach($mysql_data["assignments"] as $k => $v)
        {
			echo $v["title"].",".$k.",".$v["ref_id"].",".$v["course_ref_id"].",".$v["tii_assign_id"]."\n";
		}

		echo "\n".$lng->txt("rep_robj_xtii_mysql_dump_submissions")."\n";
		
		echo $lng->txt("rep_robj_xtii_mysql_dump_submission_ref_id").",".$lng->txt("rep_robj_xtii_mysql_dump_submission_tii_id").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_assignment_ref_id").",".$lng->txt("rep_robj_xtii_mysql_dump_ilias_user_id").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_tii_user_id").",".$lng->txt("rep_robj_xtii_mysql_dump_date_time").",";
		echo $lng->txt("rep_robj_xtii_mysql_dump_user_email")."\n";
		
		foreach($mysql_data["submissions"] as $k => $v)
        {
			echo $v["id"].",".$v["tii_paper_id"].",".$v["assign_id"].",".$v["usr_id"].",".$v["tii_usr_id"].",".$v["submission_date_time"].",".$v["email"]."\n";
        }
		
		exit;
	}
	
	/**
	 * Show Users and let admin unlink them
	 */
	function tiiShowUsers()
	{
		global $tpl, $ilCtrl;
		
		$pl = $this->getPluginObject();
		$this->setTabs("tii_unlink_users");
		
		$mysql_data = $pl->getMySQLData();
		
		// Define template and add CSS file in        
        $list_tpl = new ilTemplate("tpl.unlink_users.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
        $list_tpl->addCss("Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/templates/default/tii.css");
        $list_tpl->fillCssFiles();

        $list_tpl->setVariable("UNLINK_ALL_USERS", $pl->txt("unlink_all_users"));
        $list_tpl->setVariable("UNLINK_ALL_USERS_LINK", $ilCtrl->getLinkTarget($this, "unlinkUsers")."&usr_ids=all");
        $output = $list_tpl->get();

		// Show Users Turnitin Ids		
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilUserTiiTableGUI.php");
		$table_gui = new ilUserTiiTableGUI($this, "view", $mysql_data["users"]);
		$output .= $table_gui->getHTML();
        
		$tpl->setContent($output);
	}
	
	/**
	 * Export submission details
	 */
	function exportUsers()
	{
		global $lng, $ilCtrl;
		
		if (count($_REQUEST["usr_ids"]) == 0)
		{
			ilUtil::sendFailure($this->txt("no_users_selected"), true);
			$ilCtrl->redirect($this, "tiiShowUsers");
		}
		else
		{
			$pl = $this->getPluginObject();
			$mysql_data = $pl->getMySQLData();
			
			// Export CSV of submissions
			$csv_file = "export_users_".time().".csv";
			
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: text/csv; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"".$csv_file."\";");
			header("Content-Transfer-Encoding: binary");
						
			echo $lng->txt("rep_robj_xtii_usr_id").",".$lng->txt("rep_robj_xtii_tii_usr_id").",".$lng->txt("rep_robj_xtii_name").",";
			echo $lng->txt("rep_robj_xtii_email").",".$lng->txt("rep_robj_xtii_username")."\n";
					
			foreach ($_REQUEST["usr_ids"] as $user)
			{	
				echo $mysql_data["users"][$user]["usr_id"].",".$mysql_data["users"][$user]["tii_usr_id"].",".$mysql_data["users"][$user]["firstname"].",";
				echo $mysql_data["users"][$user]["lastname"].",".$mysql_data["users"][$user]["email"].",".$mysql_data["users"][$user]["login"]."\n";
			}
			exit;	
		}
	}
	
	function linkOrRelinkUsers()
	{
		$pl = $this->getPluginObject();
		$pl->unlinkUsers($_REQUEST["usr_ids"]);
		
		foreach ($_REQUEST["usr_ids"] as $usr_id)
		{
			if ($this->simpleCreateUser($usr_id, 1))
			{
				ilUtil::sendSuccess($pl->txt("users_linked"), true);
			}
			else
			{
				ilUtil::sendFailure($pl->txt("users_link_failure"), true);
			}
		}
		
		$this->tiiShowUsers();
	}
	
	/**
	 * Unlink Users
	 */
	function unlinkUsers()
	{
		$pl = $this->getPluginObject();
		
		if ($pl->unlinkUsers($_REQUEST["usr_ids"]))
		{
			ilUtil::sendSuccess($pl->txt("users_unlinked"), true);
		}
		else
		{
			ilUtil::sendFailure($pl->txt("users_unlink_failure"), true);
		}
		
		$_SESSION["refresh_submissions"] = 1;
		$_SESSION["refresh_instructors"] = 1;
		
		$this->tiiShowUsers();
	}
}
?>