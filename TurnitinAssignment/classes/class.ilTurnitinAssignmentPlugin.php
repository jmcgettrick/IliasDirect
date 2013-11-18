<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* Turnitin Assignment repository object plugin
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilTurnitinAssignmentPlugin extends ilRepositoryObjectPlugin
{
	function getPluginName()
	{
		return "TurnitinAssignment";
	}
	
	/**
	 * Get all MySQL Tii table data
	 */
	function getMySQLData()
	{
		global $tree, $ilDB;
		
		// Get the course data
		$query = $ilDB->query("SELECT RRXC.*, OD.title, RRXU.tii_usr_id FROM rep_robj_xtii_course RRXC".
								" LEFT JOIN object_data OD ON RRXC.obj_id = OD.obj_id".
								" LEFT JOIN rep_robj_xtii_users RRXU ON RRXU.usr_id = RRXC.tii_course_tutor");
		while ($rec = $ilDB->fetchAssoc($query))
		{
			$courses[$rec["obj_id"]] = array("title" => $rec["title"], "ref_id" => $rec["ref_id"], "tii_course_id" => $rec["tii_course_id"], "tutor_id" => $rec["tii_course_tutor"], "tii_tutor_id" => $rec["tii_usr_id"]);
			$child_items = $tree->getChildsByType($rec["ref_id"], "xtii");
			
			foreach ($child_items as $assignment)
			{
				// Get the assignment data
				$query2 = $ilDB->query("SELECT RRXA.id, OD.title, RRXA.tii_assign_id FROM rep_robj_xtii_assnment RRXA".
							" LEFT JOIN object_data OD".
							" ON RRXA.id = OD.obj_id" .
							" WHERE RRXA.id = ".$ilDB->quote($assignment["obj_id"], "integer"));
				while ($rec2 = $ilDB->fetchAssoc($query2))
				{
					$assignments[$rec2["id"]] = array("ref_id" => $assignment["ref_id"], "title" => $rec2["title"], "tii_assign_id" => $rec2["tii_assign_id"], "course_ref_id" => $rec["ref_id"]);
				}
			}
		}
		
		// Get submissions
		$query = $ilDB->query("SELECT RRXP.*, RRXU.tii_usr_id, U.email FROM rep_robj_xtii_papers RRXP".
								" LEFT JOIN rep_robj_xtii_users RRXU ON RRXU.usr_id = RRXP.usr_id" .
								" LEFT JOIN usr_data U ON RRXU.usr_id = U.usr_id");
		while ($rec = $ilDB->fetchAssoc($query))
		{
			$submissions[$rec["id"]] = array("id" => $rec["id"], "tii_paper_id" => $rec["tii_paper_id"], "assign_id" => $rec["assign_id"], "usr_id" => $rec["usr_id"], "tii_usr_id" => $rec["tii_usr_id"], "submission_date_time" => $rec["submission_date_time"], "email" => $rec["email"]); 	
		}
		
		// Get Users
		// Work out ordering by
		$order_by = "U.usr_id";
        $order_by_dir = "asc";
        if ($_REQUEST["user_table_nav"])
        {
        	$table_nav = explode(":", $_REQUEST["user_table_nav"]);
        	$order_by_request = $table_nav[0];
        	$order_by_dir = $table_nav[1];
        	$start_point = $table_nav[2];
        	
        	switch($order_by_request)
        	{
        		case "usr_id":
        			$order_by = "U.usr_id";
        			break;
        		case "tii_usr_id":
        			$order_by = "RRXU.tii_usr_id";
        			break;
        		case "name":
        			$order_by = "U.firstname, U.lastname";
        			break;
        		case "email":
        			$order_by = "U.email";
        			break;
        		case "username":
        			$order_by = "U.login";
        			break;
        	}
        }
        $query = $ilDB->query("SELECT RRXU.tii_usr_id, U.usr_id, U.firstname, U.lastname, U.email, U.login FROM usr_data U ".
								" LEFT JOIN rep_robj_xtii_users RRXU ON U.usr_id = RRXU.usr_id ".
								" WHERE U.login != 'anonymous' ".
								" ORDER BY ".$order_by." ".$order_by_dir);

		while ($rec = $ilDB->fetchAssoc($query))
		{
			$users[$rec["usr_id"]] = array("usr_id" => $rec["usr_id"], "tii_usr_id" => $rec["tii_usr_id"], "firstname" => $rec["firstname"], "lastname" => $rec["lastname"], "email" => $rec["email"], "login" => $rec["login"]);
			if (empty($users[$rec["usr_id"]]["tii_usr_id"]))
			{
				$users[$rec["usr_id"]]["tii_usr_id"] = "&nbsp;";
			}			
		}
		
		$mySQLData["courses"] = $courses;
		$mySQLData["assignments"] = $assignments;
		$mySQLData["submissions"] = $submissions;
		$mySQLData["users"] = $users;
				
		return $mySQLData;
	}
	
	/**
	 * Unlink users from Turnitin
	 */
	function unlinkUsers($usr_ids)
	{
		global $ilDB;
		
		if ($usr_ids == "all")
		{
			$ilDB->manipulate("DELETE FROM rep_robj_xtii_users");
			return true;
		}
		else if (count($usr_ids) > 0)
		{
			$usr_ids = implode(", ", $usr_ids);
			
			$ilDB->manipulate("DELETE FROM rep_robj_xtii_users WHERE usr_id IN (".$usr_ids.")");
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Load Configuration data
	*/
	function getConfigData()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xtii_config");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$var_array = array("api_url", "account_id", "shared_key", "grademark", "ets", "anon_marking", "institutional_repository", "student_emails", 
								"instructor_emails", "digital_receipts", "translated_matching");

			foreach ($var_array as $var)
			{
				$this->setVar($var, $rec[$var]);
			}
		}
	}
	
	/**
	* Update Plugin Configuration
	*/
	function updateConfigData()
	{
		global $ilDB;
		
		$ilDB->manipulate($up = "UPDATE rep_robj_xtii_config SET ".
				" api_url = ".$ilDB->quote($this->getVar("api_url"), "text").",".
				" account_id = ".$ilDB->quote($this->getVar("account_id"), "integer").",".
				" shared_key = ".$ilDB->quote($this->getVar("shared_key"), "text").",".
				" grademark = ".$ilDB->quote($this->getVar("grademark"), "integer").",".
				" ets = ".$ilDB->quote($this->getVar("ets"), "integer").",".
				" anon_marking = ".$ilDB->quote($this->getVar("anon_marking"), "integer").",".
				" translated_matching = ".$ilDB->quote($this->getVar("translated_matching"), "integer").",".
				" institutional_repository = ".$ilDB->quote($this->getVar("institutional_repository"), "integer").",".
				" student_emails = ".$ilDB->quote($this->getVar("student_emails"), "integer").",".
				" instructor_emails = ".$ilDB->quote($this->getVar("instructor_emails"), "integer").",".
				" digital_receipts = ".$ilDB->quote($this->getVar("digital_receipts"), "integer"));
	}
	
	/**
	 * Load the default assignment data
	 */
	function getDefaultAssignmentData()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xtii_default");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$var_array = array("is_online", "point_value", "instructions", "exclude_type", "exclude_value", "submit_papers_to", "generation_speed", 
				"exclude_bibliography", "exclude_quoted_materials", "submission_format", "late_submission", "students_view_reports", "anon", "translated", 
				"s_paper_check", "internet_check", "journal_check", "institution_check", "erater", "erater_handbook", "erater_spelling_dictionary", 
				"erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style");

			foreach ($var_array as $var)
			{
				$this->setVar($var, $rec[$var]);
			}
		}
	}
	
	/**
	* Update Default assignment data
	*/
	function updateDefaultAssignmentData($form_vars)
	{
		global $ilDB;
		
		$ilDB->manipulate($up = "UPDATE rep_robj_xtii_default SET ".
				" is_online = ".$ilDB->quote($form_vars->is_online, "integer").",".
				" point_value = ".$ilDB->quote($form_vars->point_value, "integer").",".
				" instructions = ".$ilDB->quote($form_vars->instructions, "text").",".
				" exclude_type = ".$ilDB->quote($form_vars->exclude_type, "integer").",".
				" exclude_value = ".$ilDB->quote($form_vars->exclude_value, "integer").",".
				" submit_papers_to = ".$ilDB->quote($form_vars->submit_papers_to, "integer").",".
				" generation_speed = ".$ilDB->quote($form_vars->generation_speed, "integer").",".
				" exclude_bibliography = ".$ilDB->quote($form_vars->exclude_bibliography, "integer").",".
				" exclude_quoted_materials = ".$ilDB->quote($form_vars->exclude_quoted_materials, "integer").",".
				" submission_format = ".$ilDB->quote($form_vars->submission_format, "integer").",".
				" late_submission = ".$ilDB->quote($form_vars->late_submission, "integer").",".
				" students_view_reports = ".$ilDB->quote($form_vars->students_view_reports, "integer").",".
				" anon = ".$ilDB->quote($form_vars->anon, "integer").",".
				" translated = ".$ilDB->quote($form_vars->translated, "integer").",".
				" s_paper_check = ".$ilDB->quote($form_vars->s_paper_check, "integer").",".
				" internet_check = ".$ilDB->quote($form_vars->internet_check, "integer").",".
				" journal_check = ".$ilDB->quote($form_vars->journal_check, "integer").",".
				" institution_check = ".$ilDB->quote($form_vars->institution_check, "integer").",".
				" erater = ".$ilDB->quote($form_vars->erater, "integer").",".
				" erater_handbook = ".$ilDB->quote($form_vars->erater_handbook, "integer").",".
				" erater_spelling_dictionary = ".$ilDB->quote($form_vars->erater_spelling_dictionary, "text").",".
				" erater_spelling = ".$ilDB->quote($form_vars->erater_spelling, "integer").",".
				" erater_grammar = ".$ilDB->quote($form_vars->erater_grammar, "integer").",".
				" erater_usage = ".$ilDB->quote($form_vars->erater_usage, "integer").",".
				" erater_mechanics = ".$ilDB->quote($form_vars->erater_mechanics, "integer").",".
				" erater_style = ".$ilDB->quote($form_vars->erater_style, "integer"));				
	}
	
	/**
	* Set variable
	*/
	function setVar($var, $value)
	{
		$this->$var = $value;
	}
	
	/**
	* Get variable
	*/
	function getVar($var)
	{
		return $this->$var;
	}
}
?>