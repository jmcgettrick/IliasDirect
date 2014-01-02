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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

include_once("./Services/User/classes/class.ilObjUser.php");

/**
* Application class for Turnitin Assignment repository object.
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*/
class ilObjTurnitinAssignment extends ilObjectPlugin
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0)
	{
		parent::__construct($a_ref_id);
	}

	protected function beforeCreate()
	{
		return true;
	}

	/**
	* Get type.
	*/
	final function initType()
	{
		$this->setType("xtii");
	}

	/**
	* Create object
	*/
	function doCreate()
	{
		global $ilDB;

		$default_start_date = time();
		$this->setDate("start_date", date('Y-m-d H:i:s', $default_start_date), "string");

		$default_end_date = strtotime("+7 day");
		$this->setDate("end_date", date('Y-m-d H:i:s', $default_end_date), "string");

		$default_posting_date = strtotime("+8 day");
		$this->setDate("posting_date", date('Y-m-d H:i:s', $default_posting_date), "string");

		// Get plugin configuration data
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentPlugin.php");
		$pl = new ilTurnitinAssignmentPlugin();
		$pl->getDefaultAssignmentData();

		$ilDB->manipulate("INSERT INTO rep_robj_xtii_assnment ".
			"(id, parent_course_obj_id, is_online, point_value, start_date, end_date, posting_date, tii_assign_id, exclude_type, exclude_value,".
			" submit_papers_to, generation_speed, exclude_bibliography, exclude_quoted_materials, submission_format, late_submission, ".
			" students_view_reports, anon, translated, s_paper_check, internet_check, journal_check, institution_check, erater, erater_handbook, ".
			" erater_spelling_dictionary, erater_spelling, erater_grammar, erater_usage, erater_mechanics, erater_style".
			") VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote($pl->getVar("is_online"), "integer").",".
			$ilDB->quote($pl->getVar("point_value"), "integer").",".
			$ilDB->quote(date('Y-m-d H:i:s', $default_start_date), "timestamp").",".
			$ilDB->quote(date('Y-m-d H:i:s', $default_end_date), "timestamp").",".
			$ilDB->quote(date('Y-m-d H:i:s', $default_posting_date), "timestamp").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote($pl->getVar("exclude_type"), "integer").",".
			$ilDB->quote($pl->getVar("exclude_value"), "integer").",".
			$ilDB->quote($pl->getVar("submit_papers_to"), "integer").",".
			$ilDB->quote($pl->getVar("generation_speed"), "integer").",".
			$ilDB->quote($pl->getVar("exclude_bibliography"), "integer").",".
			$ilDB->quote($pl->getVar("exclude_quoted_materials"), "integer").",".
			$ilDB->quote($pl->getVar("submission_format"), "integer").",".
			$ilDB->quote($pl->getVar("late_submission"), "integer").",".
			$ilDB->quote($pl->getVar("students_view_reports"), "integer").",".
			$ilDB->quote($pl->getVar("anon"), "integer").",".
			$ilDB->quote($pl->getVar("translated"), "integer").",".
			$ilDB->quote($pl->getVar("s_paper_check"), "integer").",".
			$ilDB->quote($pl->getVar("internet_check"), "integer").",".
			$ilDB->quote($pl->getVar("journal_check"), "integer").",".
			$ilDB->quote($pl->getVar("institution_check"), "integer").",".
			$ilDB->quote($pl->getVar("erater"), "integer").",".
			$ilDB->quote($pl->getVar("erater_handbook"), "integer").",".
			$ilDB->quote($pl->getVar("erater_spelling_dictionary"), "text").",".
			$ilDB->quote($pl->getVar("erater_spelling"), "integer").",".
			$ilDB->quote($pl->getVar("erater_grammar"), "integer").",".
			$ilDB->quote($pl->getVar("erater_usage"), "integer").",".
			$ilDB->quote($pl->getVar("erater_mechanics"), "integer").",".
			$ilDB->quote($pl->getVar("erater_style"), "integer").
			")");

		$ilDB->update("rep_robj_xtii_assnment", array(
        		"instructions" => array("clob", $pl->getVar("instructions"))),
        		array('id' => array('integer', $this->getId()))
        		);

		// Refresh Submission incase of copying/cloning in which case we need to synch course as it could be new
		$_SESSION["current_assn_ref_id"] = $this->getRefId();
		$_SESSION["current_assn_obj_id"] = $this->getId();
		$_SESSION["refresh_submissions"] = 1;
		$_SESSION["refresh_instructors"] = 1;
	}

	/**
	 * Get Assignment details from database
	 */
	function getLocalAssignmentDetails()
	{
		global $ilDB;

	 	$set = $ilDB->query("SELECT * FROM rep_robj_xtii_assnment ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);

		while ($rec = $ilDB->fetchAssoc($set))
		{
			$var_array = array("parent_course_obj_id", "tii_assignment_title", "is_online", "point_value", "tii_assign_id", "instructions",
								"exclude_bibliography", "exclude_quoted_materials", "exclude_type", "exclude_value", "submit_papers_to",
								"generation_speed", "submission_format", "late_submission", "students_view_reports", "anon", "translated",
								"s_paper_check", "internet_check", "journal_check", "erater", "erater_handbook", "erater_spelling_dictionary",
								"erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style"); //"institution_check"

			foreach ($var_array as $var)
			{
				$this->setVar($var, $rec[$var]);
			}

			$date_array = array("start_date", "end_date", "posting_date");
			foreach ($date_array as $date)
			{
				$this->setDate($date, $rec[$date], "string");
			}
		}
	}

	/**
	* Read data from db
	*/
	function doRead()
	{
		global $ilDB, $ilUser, $ilAccess, $rbacadmin, $rbacreview, $tree;

		// Override php.ini time limit as multiple calls to the API may take some time.
		set_time_limit(0);

		// Set variable whether to refresh submissions
		if (($_SESSION["current_assn_ref_id"] != $this->getRefId() || $_SESSION["current_assn_obj_id"] != $this->getId())
			|| !empty($_REQUEST["refresh_submissions"]))
		{
			$_SESSION["refresh_submissions"] = 1;
		}

		// Get plugin configuration data
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentPlugin.php");
		$pl = new ilTurnitinAssignmentPlugin();
		$pl->getConfigData();
		$this->plugin_config = array("grademark" => $pl->grademark, "ets" => $pl->ets, "anon_marking" => $pl->anon_marking,
										"institutional_repository" => $pl->institutional_repository, "student_emails" => $pl->student_emails,
										"instructor_emails" => $pl->instructor_emails, "digital_receipts" => $pl->digital_receipts,
										"translated_matching" => $pl->translated_matching);

		// Set parent course data
		$this->setCourseDetails($this->getRefId(), $this->getVar("parent_course_obj_id"));
		$course_details = $this->getVar("course_details");

		$this->getLocalAssignmentDetails();

		// If we're editing settings then get whether submissions have started (for anon marking, etc) and number of unsubmitted students
		if ($_REQUEST["cmd"] == "editSettings" || $_REQUEST["cmd"] == "showSummary")
		{
			$query = $ilDB->query("SELECT * FROM rep_robj_xtii_papers P" .
								" LEFT JOIN rep_robj_xtii_assnment A ON A.tii_assign_id = P.assign_id ".
								" WHERE A.id = ".$ilDB->quote($this->getId(), "integer")
			);

			$this->number_of_submissions = $ilDB->numRows($query);
			$this->number_of_unsubmitted_students = count($course_details["students"]) - $this->number_of_submissions;
		}

		// Set variable whether to refresh submissions
		if (!empty($_REQUEST["refresh_instructors"]))
		{
			$_SESSION["refresh_instructors"] = 1;
		}

		$cmd_not_refresh = array("showLoadRedirectSubmissions", "showLoadRedirectSettings", "showLoadRedirectDetails");

		if ($_REQUEST["selected_cmd"] != "removeFromSystem" && !(in_array($_REQUEST["cmd"], $cmd_not_refresh)))
		{
			// Create/Update course in Tii - only do on first visit to assignment or when refreshing data
			if (!empty($course_details["ref_id"]) && $_SESSION["refresh_submissions"] == 1)
			{
				$this->syncCourseWithTii();
				// Reset course details variable (may be necessary)
				$course_details = $this->getVar("course_details");
				$_SESSION["refresh_instructors"] = 1;
			}

			// Create assignment in Tii - Turnitin Synching is done here as we don't have a reference id for the object when it's created
			if ($this->getVar("tii_assign_id") == 0)
			{
				// Create Tii Session if it hasn't been created in syncCourseWithTii
				if (empty($_SESSION["tii_session_id"]))
				{
					$this->createTiiSession($course_details["tii_course_tutor"], 2);
				}

				$this->syncAssignmentWithTii("add");
				$this->submissions = array();
			}
			else
			{
				if ($_SESSION["refresh_submissions"] == 1)
				{
					// Get assignment details from Turnitin as they will always be correct and overwrite local settings
					// Use either the first tutor or admin if there is none to create the class in Tii
					$tutor_id = $course_details["owner"];
					if ($course_details["isTutor"])
					{
						$tutor_id = $ilUser->getId();
					}
					else
					{
						if (!empty($course_details["tutors"]))
						{
							$tutor_id = $course_details["tutors"][0];
						}
					}

					// Get tutor details
					$tutor_details = new ilObjUser($tutor_id);
					$tutor_details->tii_usr_id = $this->getTiiUserId($tutor_id);

					if (!empty($tii_assignment_title)) {
						$title = $this->getVar("tii_assignment_title");
					} else if (!empty($actual_title)) {
						$title = $this->getTitle();
						$this->setVar("tii_assignment_title", $title);
					} else {
						$title = $this->lng->txt('obj_xtii')." ".$this->getVar("tii_assign_id");
						$this->setVar("tii_assignment_title", $title);
					}

					$tii_vars = array(
						"cid" => $course_details["tii_course_id"],
						"ctl" => $course_details["tii_course_title"],
						"uid" => $tutor_details->tii_usr_id,
						"uem" => $tutor_details->getEmail(),
						"ufn" => $tutor_details->getFirstname(),
						"uln" => $tutor_details->getLastname(),
						"utp" => 2,
						"assignid" => $this->getVar("tii_assign_id"),
						"assign" => $title,
						"session-id" => $_SESSION["tii_session_id"]
					);

					$tii_response = $this->tiiCall("4", "7", $tii_vars);

                    if ($tii_response["status"] == "Success")
					{
                        $ilDB->manipulate("UPDATE rep_robj_xtii_assnment".
                                        " SET tii_assignment_title = ".$ilDB->quote(html_entity_decode($tii_response[object][assign], ENT_COMPAT | ENT_HTML401, 'UTF-8'), "text").
                                        " WHERE id = ".$ilDB->quote($this->getId(), "integer")
                                        );

                        $this->_writeTitle($this->getId(), html_entity_decode($tii_response[object][assign], ENT_COMPAT | ENT_HTML401, 'UTF-8'));

						$data = array("parent_course_obj_id" => $this->parent_course_obj_id, "is_online" => $this->is_online,
							"point_value" => $tii_response[object][maxpoints], "start_date" => date("Y-m-d H:i:s", strtotime($tii_response[object][dtstart])),
							"end_date" => date("Y-m-d H:i:s", strtotime($tii_response[object][dtdue])), "posting_date" => date("Y-m-d H:i:s", strtotime($tii_response[object][dtpost])),
							"exclude_bibliography" => $this->exclude_bibliography,
							"exclude_quoted_materials" => $this->exclude_quoted_materials, "exclude_type" => $this->exclude_type, "exclude_value" => $this->exclude_value,
							"submit_papers_to" => $tii_response[object][repository], "generation_speed" => $tii_response[object][generate],
							"submission_format" => $this->submission_format, "late_submission" => $tii_response[object][latesubmissions],
							"anon" => $tii_response[object][anon], "translated" => $this->translated, "students_view_reports" => $tii_response[object][sviewreports],
							"s_paper_check" => $tii_response[object][searchpapers], "internet_check" => $tii_response[object][searchinternet],
							"journal_check" => $tii_response[object][searchjournals], "erater" => $tii_response[object][erater],
							"erater_handbook" => $tii_response[object][ets_handbook],
							"erater_spelling_dictionary" => $tii_response[object][ets_dictionary], "erater_spelling" => $tii_response[object][ets_spelling],
							"erater_grammar" => $tii_response[object][ets_grammar], "erater_usage" => $tii_response[object][ets_usage],
							"erater_mechanics" => $tii_response[object][ets_mechanics], "erater_style" => $tii_response[object][ets_style],
							"instructions" => html_entity_decode($tii_response[object][ainst], ENT_COMPAT | ENT_HTML401, 'UTF-8')
						);//"institution_check" => $tii_response[object][searchinstitution]

						// Call Update the assignment in the db
						$this->updateQuery($this->getId(), $data);
					}
					else
					{
						$this->logAction("4", "7", $tii_response["status"], $tii_response["rcode"], $tii_response["rmessage"]);
					}

					// Update Local details ($this)
					$this->getLocalAssignmentDetails();
				}

				// Do an API call to get all the submissions for this assignment and store them
				$this->setTiiSubmissions();
			}

			// For tutors populate an array with unsubmitted students and remove tutors/students who are no longer associated with class
			if ($course_details["isTutor"] || $course_details["isAdmin"])
			{
				$this->setUnsubmittedStudents();

				// Use either the first tutor or admin if there is none to create the class in Tii
				$tutor_id = $course_details["owner"];
				if ($course_details["isTutor"])
				{
					$tutor_id = $ilUser->getId();
				}
				else
				{
					if (!empty($course_details["tutors"]))
					{
						$tutor_id = $course_details["tutors"][0];
					}
				}

				// Get tutor details
				$tutor_details = new ilObjUser($tutor_id);
				$tutor_details->tii_user_id = $this->getTiiUserId($tutor_id);

				if (empty($_SESSION["instructors_from_tii"]) || $_SESSION["refresh_instructors"] == 1)
				{
					// Get all students and instructors from Turnitin
					$tii_vars = array(
						"uid" => $tutor_details->tii_user_id,
						"uem" => $tutor_details->getEmail(),
						"ufn" => $tutor_details->getFirstname(),
						"uln" => $tutor_details->getLastname(),
						"utp" => 2,
						"cid" => $course_details["tii_course_id"],
						"ctl" => $course_details["title"]
					);

					if (!empty($_SESSION["tii_session_id"]))
					{
						$tii_vars["session-id"] = $_SESSION["tii_session_id"];
					}

					// Make Turnitin call and log response
					$tii_response = $this->tiiCall("19", "5", $tii_vars);
					// Set the instructor arrays
					$this->setTiiTutors($tii_response["instructors"]);

					if ($tii_response["status"] == "Success")
					{
						// Remove Students who are no longer associated with this class
						if (count($tii_response["students"]) > 0)
						{
							foreach ($tii_response["students"] as $student_id)
							{
								$ilias_user_id = $this->getIliasUserId($student_id);
								if (!in_array($ilias_user_id, $course_details["students"]))
								{
									$this->removeUserFromClass($ilias_user_id, 1);
								}
							}
						}
					}
					else
					{
						$msg = $tii_response["rmessage"];
						$this->logAction("19", "5", $tii_response["status"], $tii_response["rcode"], $msg);
					}
					$_SESSION["refresh_instructors"] = 0;
				}
				else
				{
					$this->setTiiTutors($_SESSION["instructors_from_tii"]);
				}
			}

			// End Tii Session - it is created in syncCourseWithTii
			if (!empty($_SESSION["tii_session_id"]))
			{
				$this->endTiiSession($course_details["tii_course_tutor"], 2);
			}

			// Work out ordering of submissions
			if (count($this->submissions) > 0)
	        {
	        	$this->sortSubmissionsArray($this->submissions);
	        }

			// Set Session vars so we don't synch on every function performed
			$_SESSION["current_assn_ref_id"] = $this->getRefId();
			$_SESSION["current_assn_obj_id"] = $this->getId();
			$_SESSION["refresh_submissions"] = 0;
		}
	}

	/**
	 * Perform query to update Assignment in Database
	 */
	function updateQuery($id, $data)
	{
		global $ilDB;

		$ilDB->manipulate($up = "UPDATE rep_robj_xtii_assnment SET ".
				" parent_course_obj_id = ".$ilDB->quote($data["parent_course_obj_id"], "integer").",".
				" is_online = ".$ilDB->quote($data["is_online"], "integer").",".
				" point_value = ".$ilDB->quote($data["point_value"], "integer").",".
				" start_date = ".$ilDB->quote($data["start_date"], "timestamp").",".
				" end_date = ".$ilDB->quote($data["end_date"], "timestamp").",".
				" posting_date = ".$ilDB->quote($data["posting_date"], "timestamp").",".
				" exclude_bibliography = ".$ilDB->quote($data["exclude_bibliography"], "integer").",".
				" exclude_quoted_materials = ".$ilDB->quote($data["exclude_quoted_materials"], "integer").",".
				" exclude_type = ".$ilDB->quote($data["exclude_type"], "integer").",".
				" exclude_value = ".$ilDB->quote($data["exclude_value"], "integer").",".
				" submit_papers_to = ".$ilDB->quote($data["submit_papers_to"], "integer").",".
				" generation_speed = ".$ilDB->quote($data["generation_speed"], "integer").",".
				" submission_format = ".$ilDB->quote($data["submission_format"], "integer").",".
				" late_submission = ".$ilDB->quote($data["late_submission"], "integer").",".
				" anon = ".$ilDB->quote($data["anon"], "integer").",".
				" translated = ".$ilDB->quote($data["translated"], "integer").",".
				" students_view_reports = ".$ilDB->quote($data["students_view_reports"], "integer").",".
				" s_paper_check = ".$ilDB->quote($data["s_paper_check"], "integer").",".
				" internet_check = ".$ilDB->quote($data["internet_check"], "integer").",".
				" journal_check = ".$ilDB->quote($data["journal_check"], "integer").",".
				" erater = ".$ilDB->quote($data["erater"], "integer").",".
				" erater_handbook = ".$ilDB->quote($data["erater_handbook"], "integer").",".
				" erater_spelling_dictionary = ".$ilDB->quote($data["erater_spelling_dictionary"], "text").",".
				" erater_spelling = ".$ilDB->quote($data["erater_spelling"], "integer").",".
				" erater_grammar = ".$ilDB->quote($data["erater_grammar"], "integer").",".
				" erater_usage = ".$ilDB->quote($data["erater_usage"], "integer").",".
				" erater_mechanics = ".$ilDB->quote($data["erater_mechanics"], "integer").",".
				" erater_style = ".$ilDB->quote($data["erater_style"], "integer").
				" WHERE id = ".$ilDB->quote($id, "integer")
			);//","." institution_check = ".$ilDB->quote($data["institution_check"], "integer").

		$ilDB->update("rep_robj_xtii_assnment", array(
        		"instructions" => array("clob", $data["instructions"])),
        		array('id' => array('integer', $id))
        		);
	}

	/**
	 * Update data
	 */
	function doUpdate()
	{
		global $ilDB, $ilUser, $tree;

		// Get plugin configuration data
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentPlugin.php");
		$pl = new ilTurnitinAssignmentPlugin();
		$pl->getConfigData();
		$this->plugin_config = array("grademark" => $pl->grademark, "ets" => $pl->ets, "anon_marking" => $pl->anon_marking,
										"institutional_repository" => $pl->institutional_repository, "student_emails" => $pl->student_emails,
										"instructor_emails" => $pl->instructor_emails, "digital_receipts" => $pl->digital_receipts,
										"translated_matching" => $pl->translated_matching);

		$start_date_array = $this->getVar("start_date");
		$start_date = $start_date_array["date"]." ".$start_date_array["time"];

		$end_date_array = $this->getVar("end_date");
		$end_date = $end_date_array["date"]." ".$end_date_array["time"];

		$posting_date_array = $this->getVar("posting_date");
		$posting_date = $posting_date_array["date"]." ".$posting_date_array["time"];

		// Determine if Anonymous marking is enabled and update accordingly
		$anon = ($this->plugin_config["anon_marking"] == 1) ? $this->anon : 0;

		// Determine if Translated matching is enabled and update accordingly
		$translated = ($this->plugin_config["translated_matching"] == 1) ? $this->translated : 0;

		// Determine if Grademark and ETS settings are enabled and update accordingly
		$erater = ($this->plugin_config["grademark"] == 1 && $this->plugin_config["ets"] == 1) ? $this->erater : 0;

		$data = array("parent_course_obj_id" => $this->parent_course_obj_id, "is_online" => $this->is_online, "point_value" => $this->point_value,
					"start_date" => $start_date, "end_date" => $end_date, "posting_date" => $posting_date, "exclude_bibliography" => $this->exclude_bibliography,
					"exclude_quoted_materials" => $this->exclude_quoted_materials, "exclude_type" => $this->exclude_type, "exclude_value" => $this->exclude_value,
					"submit_papers_to" => $this->submit_papers_to, "generation_speed" => $this->generation_speed, "submission_format" => $this->submission_format,
					"late_submission" => $this->late_submission, "anon" => $anon, "translated" => $translated,
					"students_view_reports" => $this->students_view_reports, "s_paper_check" => $this->s_paper_check, "internet_check" => $this->internet_check,
					"journal_check" => $this->journal_check, "erater" => $erater, "erater_handbook" => $this->erater_handbook,
					"erater_spelling_dictionary" => $this->erater_spelling_dictionary, "erater_spelling" => $this->erater_spelling,
					"erater_grammar" => $this->erater_grammar, "erater_usage" => $this->erater_usage, "erater_mechanics" => $this->erater_mechanics,
					"erater_style" => $this->erater_style, "instructions" =>  $this->instructions
			);//"institution_check" => $this->institution_check

		// Call Update the assignment in the db
		$this->updateQuery($this->getId(), $data);

		// If cloning don't synch as it will do it when redirected to that assignment
		if ($_SESSION["cloning_assignment"] != "Y")
		{
            $course_details = $this->getVar("course_details");

			// Create/Update course in Tii
			$this->syncCourseWithTii();

			// Create/Update assignment in Tii
			$this->syncAssignmentWithTii("edit");

			// Work out tutor id that we're currently using in session
			$tutor_id = $course_details["owner"];
			if ($course_details["isTutor"])
			{
				$tutor_id = $ilUser->getId();
			}
			else
			{
				if (!empty($course_details["tutors"]))
				{
					$tutor_id = $course_details["tutors"][0];
				}
			}

			// End Tii Session - it is created in syncCourseWithTii
			$this->endTiiSession($tutor_id, 2);
		}

		unset($_SESSION["cloning_assignment"]);

		$_SESSION["refresh_submissions"] = 1;
	}

	/**
	* Delete data from db
	*/
	function doDelete()
	{
		global $ilDB;

		// Delete assignment in Tii
		$this->setCourseDetails($this->getRefId(), $this->getVar("parent_course_obj_id"));
		$this->syncAssignmentWithTii("delete");

		$ilDB->manipulate("DELETE FROM rep_robj_xtii_papers WHERE ".
			" assign_id = ".$ilDB->quote($this->getVar("tii_assign_id"), "integer")
			);

		$ilDB->manipulate("DELETE FROM rep_robj_xtii_assnment WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
	}


	/**
	 * Check whether parent object being copied to is a course else return false
	 */
	/*protected function beforeCloneObject()
	{
		global $tree;

		$target_data = $tree->getNodeData($_REQUEST["target"]);
		print_r($target_data);
		echo "<br/>===<br/>";
		print_r($_REQUEST);
		exit;

		if ($target_data["type"] == "crs")
		{
			return true;
		}
		else
		{
			return false;
		}
	}*/

	/**
	* Do Cloning of Assignment
	*/
	function doCloneObject($new_obj, $a_target_id, $a_copy_id)
	{
		global $ilDB, $tree;

		$var_array = array("is_online", "point_value", "tii_assign_id", "instructions", "exclude_bibliography", "exclude_quoted_materials",
							"exclude_type", "exclude_value", "submit_papers_to", "generation_speed", "late_submission", "students_view_reports",
							"anon", "translated", "s_paper_check", "internet_check", "journal_check", "erater", "erater_handbook",
							"erater_spelling_dictionary", "erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics",
							"erater_style", "submission_format"); //"institution_check"
		foreach ($var_array as $var)
		{
			$new_obj->$var = $this->getVar($var);
		}

		// If dates are in the past then set them to defaults.
		$start_date = $this->getVar('start_date');
		if (strtotime($start_date['date']." ".$start_date['time']) < time())
		{
			$default_start_date = time();
			$new_obj->setDate("start_date", date('Y-m-d H:i:s', $default_start_date), "string");

			$default_end_date = strtotime("+7 day");
			$new_obj->setDate("end_date", date('Y-m-d H:i:s', $default_end_date), "string");

			$default_posting_date = strtotime("+8 day");
			$new_obj->setDate("posting_date", date('Y-m-d H:i:s', $default_posting_date), "string");
		}
		else
		{
			$date_array = array("start_date", "end_date", "posting_date");
			foreach ($date_array as $date)
			{
				$new_obj->$date = $this->getVar($date);
			}
		}

		$course_details = $tree->getParentNodeData($new_obj->getRefId());

		$new_obj->parent_course_obj_id = $course_details["obj_id"];
		$_SESSION["cloning_assignment"] = "Y";
		$new_obj->update();
	}

	/**
	 * Make Call to Turnitin API
	 */
	function tiiCall($fid, $fcmd, $tii_vars)
	{
		global $ilCtrl;

		$this->plugin->includeClass("class.ilTurnitinAssignmentTiiCall.php");
		$tii_call = new ilTurnitinAssignmentTiiCall();
		$tii_response = $tii_call->tiiCall($fid, $fcmd, $tii_vars);

        if ($tii_response["rcode"] == 0 && $fcmd != 1)
		{
			//$ilCtrl->redirect($this, "connectionFailed");
			$_SESSION["connection_failed"] = 1;
		}

		return $tii_response;
	}

	/**
	* Log Tii Actions
	*/
	function logAction($fid, $fcmd, $status, $rcode, $msg)
	{
		global $ilUser, $ilDB;

		$id = $ilDB->nextID('rep_robj_xtii_log');

		$ilDB->manipulate("DELETE FROM rep_robj_xtii_log WHERE ".
			" date_time <= ".$ilDB->quote(date('Y-m-d H:i:s', strtotime("20 days ago")), "timestamp")
			);

		$ilDB->manipulate("INSERT INTO rep_robj_xtii_log ".
			"(id, usr_id, date_time, type, fid, fcmd, status, rcode".
			") VALUES (".
			$ilDB->quote($id, "integer").",".
			$ilDB->quote($ilUser->getId(), "integer").",".
			$ilDB->quote(date('Y-m-d H:i:s'), "timestamp").",".
			$ilDB->quote("response", "text").",".
			$ilDB->quote($fid, "integer").",".
			$ilDB->quote($fcmd, "integer").",".
			$ilDB->quote($status, "text").",".
			$ilDB->quote($rcode, "integer").
			")");

		$ilDB->update("rep_robj_xtii_log", array(
        		"msg" => array("clob", $msg)),
        		array('id' => array('integer', $id))
        		);
	}

	/**
	 * Create Session with Turnitin, essential for multiple calls due to Tii database synching
	 */
	function createTiiSession($usr_id, $user_type)
	{
		// Get user details
		$user_details = new ilObjUser($usr_id);
		$user_details->tii_user_id = $this->getTiiUserId($usr_id);

		$tii_vars = array(
			"uid" => $user_details->tii_user_id,
			"uem" => $user_details->getEmail(),
			"ufn" => $user_details->getFirstname(),
			"uln" => $user_details->getLastname(),
			"utp" => $user_type
		);

		// Set whether response email has to be sent
		if (($user_type == 1 && $this->plugin_config["student_emails"] == 0) || ($user_type == 2 && $this->plugin_config["instructor_emails"] == 0))
		{
			$tii_vars["dis"] = 1;
		}

		// Make Turnitin call and log response
		$tii_response = $this->tiiCall("17", "2", $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success") {
			$msg = "User I(".$usr_id.") T(".$user_details->tii_user_id.") : Session created (".$tii_response["sessionid"].")";
		}
		$this->logAction("17", "2", $tii_response["status"], $tii_response["rcode"], $msg);

		$_SESSION["tii_session_id"] = $tii_response["sessionid"];
	}

	/**
	 * End Session with Turnitin
	 */
	function endTiiSession($usr_id, $user_type)
	{
		// Get user details
		$user_details = new ilObjUser($usr_id);
		$user_details->tii_user_id = $this->getTiiUserId($usr_id);

		$tii_vars = array(
			"uid" => $user_details->tii_user_id,
			"uem" => $user_details->getEmail(),
			"ufn" => $user_details->getFirstname(),
			"uln" => $user_details->getLastname(),
			"utp" => $user_type,
			"session-id" => $_SESSION["tii_session_id"]
		);

		// Set whether response email has to be sent
		if (($user_type == 1 && $this->plugin_config["student_emails"] == 0) || ($user_type == 2 && $this->plugin_config["instructor_emails"] == 0))
		{
			$tii_vars["dis"] = 1;
		}

		// Make Turnitin call and log response
		$tii_response = $this->tiiCall("18", "2", $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success") {
			$msg = "User I(".$usr_id.") T(".$user_details->tii_user_id.") : Session ended (".$_SESSION["tii_session_id"].")";
		}
		$this->logAction("18", "2", $tii_response["status"], $tii_response["rcode"], $msg);

		$_SESSION["tii_session_id"] = 0;
	}

	function syncAssignmentWithTii($mode)
	{
		global $ilDB, $ilUser;
		$recall = "N";
		$tii_vars2 = array();

		// Set fcmd by mode
		switch ($mode)
		{
			case "add":
				$fcmd = 2;
				break;
			case "edit":
				$fcmd = 3;
				break;
			case "delete":
				$fcmd = 4;
				break;
		}

		// Get parent course data
		$course_details = $this->getVar("course_details");

		// Use either the first tutor or admin if there is none to create the class in Tii
		$tutor_id = $course_details["owner"];
		if ($course_details["isTutor"])
		{
			$tutor_id = $ilUser->getId();
		}
		else
		{
			$tutor_id = (empty($course_details["tutors"])) ? $course_details["admins"][0] : $course_details["tutors"][0];
		}

		// Get tutor details
		$tutor_details = new ilObjUser($tutor_id);
		$tutor_details->tii_usr_id = $this->getTiiUserId($tutor_id);

		// Set Tii Vars
		if ($fcmd != 4)
		{
			// Set the parent course id in the database, this should only ever run once
			if ($this->getVar("parent_course_obj_id") == 0)
			{
				$ilDB->manipulate($up = "UPDATE rep_robj_xtii_assnment SET ".
					" parent_course_obj_id = ".(int)$ilDB->quote($course_details["obj_id"], "integer").
					" WHERE id = ".$ilDB->quote($this->getId(), "integer")
				);
				$this->setVar("parent_course_obj_id", $course_details["obj_id"]);
			}

			$start_date_array = $this->getVar("start_date");
			$start_date = $start_date_array["date"]." ".$start_date_array["time"];

			$end_date_array = $this->getVar("end_date");
			$end_date = $end_date_array["date"]." ".$end_date_array["time"];

			$posting_date_array = $this->getVar("posting_date");
			$posting_date = $posting_date_array["date"]." ".$posting_date_array["time"];

			$tii_vars2 = array(
				"max_points" => $this->getVar("point_value"),
				"dtstart" => $start_date,
				"dtdue" => $end_date,
				"dtpost" => $posting_date,
				"ainst" => $this->getVar("instructions"),
				"exclude_biblio" => $this->getVar("exclude_bibliography"),
				"exclude_quoted" => $this->getVar("exclude_quoted_materials"),
				"exclude_type" => $this->getVar("exclude_type"),
				"exclude_value" => $this->getVar("exclude_value"),
				"submit_papers_to" => $this->getVar("submit_papers_to"),
				"anon" => $this->getVar("anon"),
				"translated_matching" => $this->getVar("translated"),
				"report_gen_speed" => $this->getVar("generation_speed"),
				"late_accept_flag" => $this->getVar("late_submission"),
				"s_view_report" => $this->getVar("students_view_reports"),
				"s_paper_check" => $this->getVar("s_paper_check"),
				"internet_check" => $this->getVar("internet_check"),
				"journal_check" => $this->getVar("journal_check"),
				"erater" => $this->getVar("erater"),
				"erater_handbook" => $this->getVar("erater_handbook"),
				"erater_spelling_dictionary" => $this->getVar("erater_spelling_dictionary"),
				"erater_spelling" => $this->getVar("erater_spelling"),
				"erater_grammar" => $this->getVar("erater_grammar"),
				"erater_usage" => $this->getVar("erater_usage"),
				"erater_mechanics" => $this->getVar("erater_mechanics"),
				"erater_style" => $this->getVar("erater_style"),
				"ctl" => $course_details["title"],
			);//"institution_check"
		}
		else
		{
			$tii_vars2["ctl"] = $course_details["tii_course_title"];
		}

		$tii_vars = array(
				"cid" => $course_details["tii_course_id"],
				"uid" => $tutor_details->tii_usr_id,
				"uem" => $tutor_details->getEmail(),
				"ufn" => $tutor_details->getFirstname(),
				"uln" => $tutor_details->getLastname(),
				"utp" => 2
			);

		if ($this->getVar("tii_assign_id") != 0)
		{
			$tii_vars["assignid"] = $this->getVar("tii_assign_id");
			$tii_vars["assign"] = $this->getVar("tii_assignment_title");
			$title_to_save = $tii_vars["assign"];

			if ($this->getVar("tii_assignment_title") != $this->getTitle())
			{
				$tii_vars["newassign"] = $this->getTitle();
			}
		}
		else
		{
			// Assignment names must be unique on creation so we add a datestamp on the end then recursively remove it
			$tii_vars["assign"] = $this->getTitle()."_".date("YmdHis");
			if (strtotime($tii_vars2["dtstart"]) <= time()) {
				$tii_vars2["dtstart"] = date('Y-m-d H:i:s');
			}
			$recall = "Y";
		}

		if (!empty($_SESSION["tii_session_id"]))
		{
			$tii_vars["session-id"] = $_SESSION["tii_session_id"];
		}

		$tii_vars = array_merge($tii_vars, $tii_vars2);

		$tii_response = $this->tiiCall("4", $fcmd, $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success")
		{
			$msg = "Course I (".$course_details["obj_id"].") T(".$tii_response["classid"].") ".
				": Assignment I (".$this->getId().") T(".$tii_response["assignmentid"].") ".
				": Tutor I(".$course_details["tii_course_tutor"].") T(".$tii_response["userid"].")";

			$title_to_save = $tii_vars["assign"];
			if (!empty($tii_vars["newassign"]))
			{
				$title_to_save = $tii_vars["newassign"];
			}

			$ilDB->manipulate("UPDATE rep_robj_xtii_assnment".
						" SET tii_assign_id = ".$ilDB->quote($tii_response["assignmentid"], "integer").", ".
						" tii_assignment_title = ".$ilDB->quote($title_to_save, "text").
						" WHERE id = ".$ilDB->quote($this->getId(), "integer")
						);

			$this->setVar("tii_assign_id", $tii_response["assignmentid"]);
			$this->setVar("tii_assignment_title", $tii_vars["assign"]);

			$this->logAction("4", $fcmd, $tii_response["status"], $tii_response["rcode"], $msg);

			if ($recall == "Y")
			{
				// Recall this function to correct title name
				$this->syncAssignmentWithTii("edit");
			}
		}
		else
		{
			$recall = "N";
			$this->logAction("4", $fcmd, $tii_response["status"], $tii_response["rcode"], $msg);
		}
	}

	/**
	 * Sync course with Turnitin
	 */
	function syncCourseWithTii($tutor_id = 0)
	{
		global $ilDB, $ilUser;

		$recall = "N";
		$course_details = $this->getVar("course_details");

		// Create/Edit the course
		// Use either the first tutor or admin if there is none to create the class in Tii
		if ($tutor_id == 0)
		{
			$tutor_id = $course_details["owner"];
			if ($course_details["isTutor"])
			{
				$tutor_id = $ilUser->getId();
			}
			else
			{
				if (!empty($course_details["tutors"]))
				{
					$tutor_id = $course_details["tutors"][0];
				}
			}
		}

		// Get tutor details
		$tutor_details = new ilObjUser($tutor_id);
		$tutor_details->tii_usr_id = $this->getTiiUserId($tutor_id);

		// We must now either create or edit the tutor's details and create the session id
		if ($tutor_details->tii_usr_id != 0 && empty($_SESSION["tii_session_id"]))
		{
			$this->createTiiSession($tutor_id, "2");
		}
		$tutor_details->tii_usr_id = $this->createTiiUser($tutor_id, 2);

		// Create/edit class and join as tutor
		$tii_vars = array(
			"ctl" => $course_details["title"],
			"uid" => $tutor_details->tii_usr_id,
			"uem" => $tutor_details->getEmail(),
			"ufn" => $tutor_details->getFirstname(),
			"uln" => $tutor_details->getLastname(),
			"utp" => 2,
			"session-id" => $_SESSION["tii_session_id"]
		);

		if ($course_details["tii_course_id"] != 0)
		{
			$tii_vars["cid"] = $course_details["tii_course_id"];
		}
		else
		{
			// Course names must be unique on creation so we add a datestamp on the end then recursively remove it
			$tii_vars["ctl"] = $course_details["title"]."_".date("YmdHis");
			$recall = "Y";
		}

		// Create/Edit class in Tii
		$tii_response = $this->tiiCall("2", "2", $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success")
		{
			$msg = "Course I (".$course_details["obj_id"].") T(".$tii_response["classid"].") ".
				": Tutor I(".$tutor_id.") T(".$tii_response["userid"].")";

			$ilDB->manipulate("INSERT INTO rep_robj_xtii_course (ref_id, obj_id, tii_course_id, tii_course_title, tii_course_tutor)".
							" VALUES (".$ilDB->quote($course_details["ref_id"], "integer").", ".
										$ilDB->quote($course_details["obj_id"], "integer").", ".
										$ilDB->quote($tii_response["classid"], "integer").", ".
										$ilDB->quote($tii_vars["ctl"], "text").", ".
										$ilDB->quote($tutor_id, "integer").")".
							" ON DUPLICATE KEY UPDATE tii_course_id = ".$ilDB->quote($tii_response["classid"], "integer").",".
														" tii_course_title = ".$ilDB->quote($tii_vars["ctl"], "text").",".
														" tii_course_tutor = ".$ilDB->quote($tutor_id, "integer")
							);

			$this->logAction("2", "2", $tii_response["status"], $tii_response["rcode"], $msg);

			// Update Course details
			$this->setCourseDetails($this->getRefId());

			if ($recall == "Y")
			{
				// Recall this function to correct title name
				$this->syncCourseWithTii();
			}
			else
			{
				// Update Course details
				$course_details = $this->getVar("course_details");

				// Update course end date if necessary
				$computed_course_date = $this->workOutCourseEndDate($course_details["obj_id"], $course_details["create_date"]);
				if (date("Y-m-d H:i:s", $computed_course_date) != $course_details["course_end_date"])
				{
					$tii_vars["ced"] = date("Ymd", $computed_course_date);
					$tii_response = $this->tiiCall("2", "3", $tii_vars);
					$msg = $tii_response["rmessage"];
					if ($tii_response["status"] == "Success")
					{
						$msg = "Course I (".$course_details["obj_id"].") T(".$tii_response["classid"].") ".
							": Tutor I(".$tutor_id.") T(".$tutor_details->tii_usr_id.")";
						$this->logAction("2", "3", $tii_response["status"], $tii_response["rcode"], $msg);

						$ilDB->manipulate($up = "UPDATE rep_robj_xtii_course SET ".
												" course_end_date = ".$ilDB->quote(date("Y-m-d H:i:s", $computed_course_date), "timestamp").
												" WHERE ref_id = ".$ilDB->quote($course_details["ref_id"], "integer")
										);
					}
					else
					{
						$this->logAction("2", "3", $tii_response["status"], $tii_response["rcode"], $msg);
					}
				}
			}
		}
		else
		{
			$this->logAction("2", "2", $tii_response["status"], $tii_response["rcode"], $msg);
		}

		// Remove any courses from our tables that have been deleted from the system previously
		$ilDB->manipulate("DELETE FROM rep_robj_xtii_course ".
							" WHERE (SELECT COUNT(obj_id) FROM object_data WHERE obj_id = rep_robj_xtii_course.obj_id) = 0"
							);
	}

	/**
	 * Join all students in this class to Tii, create/edit the user as well
	 */
	function syncStudentsWithTii()
	{
		global $ilDB;
		$error = "N";
		$problem_users = array();

		$course_details = $this->getVar("course_details");
		foreach ($course_details["students"] as $k => $v)
		{
			// Check whether email address is already being used by another user account - Tii requires a unique email address, Ilias does not
			if ($this->checkUserEmailDoesntExistInTii($v))
			{
				// If user is being created rather than edited then a session id will be created, class will also be joined in both cases
				$tii_student_id = $this->createTiiUser($v, 1, $course_details["tii_course_id"], $course_details["tii_course_title"]);

				if ($tii_student_id == 0)
				{
					$error = "P";
					$user_details = new ilObjUser($v);
					$problem_users[] = $user_details->getLogin();
				}

				if (!empty($_SESSION["tii_session_id"]))
				{
					$this->endTiiSession($v, 1);
				}
			}
			else
			{
				$error = "D";
				$user_details = new ilObjUser($v);
				$problem_users[] = $user_details->getLogin();
				$msg = $this->txt("tii_duplicate_email");
			}
		}

		$return["error"] = $error;
		$return["problem_users"] = $problem_users;

		return $return;
	}

	/**
	 * Set the parent course details for use with synching and relating assignment to it
	 */
	function setCourseDetails($ref_id, $course_obj_id = 0)
	{
		global $tree, $ilUser, $ilDB;

		$parent_nodes = array_reverse($tree->getNodePath($ref_id));
		foreach ($parent_nodes as $parent_node) {
			$parent = $parent_node;

			if ($parent_node['type'] == 'crs' || $parent_node['type'] == 'grp')
			{
				break;
			}
		}
		$parent_details = $tree->getNodeData($parent_node['child']);

		if ((int)$parent_details["obj_id"] == 0)
		{
			$parent_details["obj_id"] = $course_obj_id;
			$query = $ilDB->query("SELECT * FROM rep_robj_xtii_course WHERE obj_id = ".$ilDB->quote($parent_details["obj_id"], "integer"));
		}
		else
		{
			$query = $ilDB->query("SELECT * FROM rep_robj_xtii_course WHERE ref_id = ".$ilDB->quote($parent_details["ref_id"], "integer"));
		}

		// Get Stored Tii details
		$rec = $ilDB->fetchAssoc($query);

		$parent_details["tii_course_id"] = (int)$rec["tii_course_id"];
		$parent_details["tii_course_title"] = $rec["tii_course_title"];
		if (empty($parent_details["title"]))
		{
			$parent_details["title"] = $rec["tii_course_title"];
		}
		$parent_details["tii_course_tutor"] = (int)$rec["tii_course_tutor"];
		$parent_details["course_end_date"] = $rec["course_end_date"];

		// Get Course/Group id
		if ($parent_details['type'] == 'crs')
		{
			// Work out if user is a student to deduce default command
			include_once("Modules/Course/classes/class.ilCourseParticipants.php");
			$participants = new ilCourseParticipants($parent_details["obj_id"]);
		}
		else
		{
			// Work out if user is a student to deduce default command
			include_once("Modules/Group/classes/class.ilGroupParticipants.php");
			$participants = new ilGroupParticipants($parent_details["obj_id"]);
		}

		// Get tutors and students
		$parent_details["admins"] = $participants->getAdmins();
		$parent_details["tutors"] = $participants->getTutors();
		$parent_details["students"] = $participants->getMembers();

		// Get current role of user
		$parent_details["isMember"] = $participants->isMember($ilUser->getId());
		$parent_details["isTutor"] = $participants->isTutor($ilUser->getId());
		$parent_details["isAdmin"] = $participants->isAdmin($ilUser->getId());

		$this->course_details = $parent_details;
	}

	/**
	 * Get current tutors from Tii
	 */
	function setTiiTutors($instructors)
	{
		$course_details = $this->getVar("course_details");
		$this->instructors = array();
		$_SESSION["instructors_from_tii"] = $instructors;
		$unlinked_instructors = $course_details["tutors"];

		if (count($instructors) > 0)
		{
			foreach ($instructors as $instructor_tii_id)
			{
				$instructor_id = $this->getIliasUserId($instructor_tii_id);
				$user_details = new ilObjUser($instructor_id);

				$this->instructors[$instructor_id] = array("usr_id" => $instructor_id,"firstname" => $user_details->getFirstname(),
						"lastname" => $user_details->getLastname(), "tii_usr_id" => $instructor_tii_id,
						"email" => $user_details->getEmail(), "login" => $user_details->getLogin()
						);

				if (in_array($instructor_id, $unlinked_instructors))
				{
					$unlinked_instructors = array_diff($unlinked_instructors, array($instructor_id));
				}
		}
		}
		foreach ($unlinked_instructors as $k => $unlinked_instructor_id)
		{
			$user_details = new ilObjUser($unlinked_instructor_id);
			$new_unlinked_instructors[$unlinked_instructor_id] = $user_details->getFirstname()." ".$user_details->getLastname()." (".$user_details->getLogin().")";
		}

		$this->unlinked_instructors = $new_unlinked_instructors;
	}

	/**
	 * Get Submissions from Tii
	 */
	function setTiiSubmissions()
	{
		global $ilUser;

		$course_details = $this->getVar("course_details");

		if (empty($_SESSION["submissions_from_tii"]) || $_SESSION["refresh_submissions"] == 1)
		{
			$this->submissions = array();

			$tii_vars = array(
				"cid" => $course_details["tii_course_id"],
				"ctl" => $course_details["title"],
				"assignid" => $this->getVar("tii_assign_id"),
				"assign" => $this->getVar("tii_assignment_title"),
				"uid" => $this->getTiiUserId($ilUser->getId()),
				"uem" => $ilUser->getEmail(),
				"ufn" => $ilUser->getFirstname(),
				"uln" => $ilUser->getLastname(),
				"utp" => 1
			);

			if ($course_details["isTutor"])
			{
				$tii_vars["utp"] = 2;
			}

			if ($course_details["isAdmin"])
			{
				// Get tutor details
				$tutor_details = new ilObjUser($course_details["tii_course_tutor"]);

				$tii_vars["uid"] = $this->getTiiUserId($course_details["tii_course_tutor"]);
				$tii_vars["uem"] = $tutor_details->getEmail();
				$tii_vars["ufn"] = $tutor_details->getFirstname();
				$tii_vars["uln"] = $tutor_details->getLastname();
				$tii_vars["utp"] = 2;
			}

			if (!empty($_SESSION["tii_session_id"]))
			{
				$tii_vars["session-id"] = $_SESSION["tii_session_id"];
			}

			$tii_response = $this->tiiCall("10", "2", $tii_vars);
			if ($tii_response["status"] != "Success")
			{
				$msg = $tii_response["rmessage"];
				$this->logAction("10", "2", $tii_response["status"], $tii_response["rcode"], $msg);
			}
			else
			{
				$submissions = array();
				if (count($tii_response["object"]) > 0)
				{
					foreach ($tii_response["object"] as $k => $v)
					{
						$submissions[$k] = $v;
					}
				}
				$_SESSION["submissions_from_tii"] = $submissions;
				$this->submissions = $submissions;
				$_SESSION["refresh_submissions"] = 0;
			}
		}
		else
		{
			$this->submissions = $_SESSION["submissions_from_tii"];
		}
	}

	/**
	 * Populate the students array with unsubmitted students
	 */
	function setUnsubmittedStudents()
	{
		$course_details = $this->getVar("course_details");
		$all_students = array();
		$unsubmitted_students = array();

		$this->number_of_submitted_students = count($this->submissions);

		foreach ($course_details["students"] as $student)
		{
			$user_details = new ilObjUser($student);
			$all_students[$student] = $user_details->getFirstname()." ".$user_details->getLastname();

			if (empty($this->submissions[$student]))
			{
				$this->submissions[$student] = array("firstname" => $user_details->getFirstname(), "lastname" => $user_details->getLastname(),
					"userid" => $this->getTiiUserId($student)
					);

				$unsubmitted_students[$student] = $user_details->getFirstname()." ".$user_details->getLastname();
			}
			else
			{
				//Get the similarity score to use when re-ordering
				$this->submissions[$student]["report_score_to_show"] = $this->submissions[$student]["overlap"];
				$this->submissions[$student]["report_score_lng_overlay"] = "";

				if ($this->submissions[$student]["translated_matching"]["similarityScore"] > 0
					&& $this->submissions[$student]["translated_matching"]["overlap"] > $this->submissions[$student]["report_score_to_show"])
	        	{
					$this->submissions[$student]["report_score_to_show"] = $this->submissions[$student]["translated_matching"]["overlap"];
					$this->submissions[$student]["report_score_lng_overlay"] = $this->txt("eng_abbreviation");
				}
			}
		}

		$this->setVar("all_students", $all_students);
		$this->setVar("unsubmitted_students", $unsubmitted_students);
		$this->setVar("number_of_unsubmitted_students", count($unsubmitted_students));
	}

	/**
	 * Sort the submissions array
	 */
	function sortSubmissionsArray(&$data)
	{
		$order_fields = array("objectID");
        $order_dir = "desc";

        if ($_REQUEST["submissions_table_nav"])
        {
        	$table_nav = explode(":", $_REQUEST["submissions_table_nav"]);
        	$order_field_request = $table_nav[0];
        	$order_dir = $table_nav[1];

        	switch($order_field_request)
        	{
        		case "lastname":
        			$order_fields = array("lastname", "firstname");
        			break;
        		case "paper_id":
        			$order_fields = array("objectID");
        			break;
        		default:
        			$order_fields = array($order_field_request);
        			break;
        	}
        }

		$code = '$retval = strnatcmp($a["'.$order_fields[0].'"], $b["'.$order_fields[0].'"]);';
		for($i=1; $i < count($order_fields); $i++)
		{
			$code .= 'if(!$retval) $retval = strnatcmp($a["'.$order_fields[$i].'"], $b["'.$order_fields[$i].'"]);';
		}
		$code .= 'return $retval;';
		if ($order_dir == "desc")
		{
			uasort($data, create_function('$b,$a', $code));
		}
		else
		{
			uasort($data, create_function('$a,$b', $code));
		}
	}

	/**
	 * Check if submission is allowed
	 */
	function checkIfSubmissionAllowed($usr_id)
	{
		$end_date = $this->getVar("end_date");

		$submitted = "N";
		if (!empty($this->submissions[$usr_id]["objectID"]))
		{
			$submitted = "Y";
		}

		if (strtotime($end_date["date"]." ".$end_date["time"]) < time())
		{
			if ($this->getVar("late_submission") == 1 && ($submitted == "N" || $this->getVar("generation_speed") != 0))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if ($submitted == "N" || $this->getVar("generation_speed") != 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Check that user we want to create doesn't have an email address that hasn't already been registered with Tii
	 */
	function checkUserEmailDoesntExistInTii($usr_id)
	{
		global $ilDB;

		$user_details = new ilObjUser($usr_id);
		$user_details->tii_usr_id = $this->getTiiUserId($usr_id);

		$query = $ilDB->query("SELECT usr_id FROM usr_data ".
								" WHERE email = ".$ilDB->quote($user_details->getEmail(), "text").
								" AND usr_id != ".$ilDB->quote($usr_id, "integer").
								" AND (SELECT COUNT(tii_usr_id) FROM rep_robj_xtii_users WHERE usr_id = usr_data.usr_id) > 0 ");

		// Error if this email address is already being used
		return ($ilDB->numRows($query) == 0) ? true : false;
	}

	/**
	 * Create User on Turnitin
	 */
	function createTiiUser($usr_id, $usertype = 1, $tii_course_id = 0, $tii_course_title = "")
	{
		global $ilDB;

		$user_details = new ilObjUser($usr_id);
		$user_details->tii_usr_id = $this->getTiiUserId($usr_id);

		// Create Tii Vars
		$tii_vars = array(
			"utp" => $usertype,
			"uem" => $user_details->getEmail(),
			"ufn" => $user_details->getFirstname(),
			"uln" => $user_details->getLastname()
			);

		if ($user_details->tii_usr_id != 0)
		{
			$tii_vars["uid"] = $user_details->tii_usr_id;
		}

		// Assign Session Id or create session
		if (!empty($_SESSION["tii_session_id"]))
		{
			$tii_vars["session-id"] = $_SESSION["tii_session_id"];
		}
		else if ($user_details->tii_usr_id == 0)
		{
			$tii_vars["create_session"] = 1;
		}
		else if ($tii_course_id != 0)
		{
			// This should only ever be a member
			$this->createTiiSession($usr_id, "1");
			$tii_vars["session-id"] = $_SESSION["tii_session_id"];
		}

		// Set whether response emails have to be sent
		if ($this->plugin_config["instructor_emails"] == 0 && $usertype == 2)
		{
			$tii_vars["dis"] = 1;
		}
		if ($this->plugin_config["student_emails "] == 0 && $usertype == 1)
		{
			$tii_vars["dis"] = 1;
		}

		$tii_response = $this->tiiCall("1", "2", $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success")
		{
			$msg = "User I(".$usr_id.")";
			if ($tii_vars["create_session"] == 1)
			{
				$msg .= " : Session Created (".$tii_response["sessionid"].")";
				$_SESSION["tii_session_id"] = $tii_response["sessionid"];

				$this->logAction("1", "2", $tii_response["status"], $tii_response["rcode"], $msg);

				// Recall to create tii user id
				$tii_usr_id = $this->createTiiUser($usr_id, $usertype, $tii_course_id, $tii_course_title);
			}
			else
			{
				$msg .= " T(".$tii_response["userid"].")";
				$tii_usr_id = $tii_response["userid"];

				$ilDB->manipulate("INSERT INTO rep_robj_xtii_users (usr_id, tii_usr_id) ".
						" VALUES (".$ilDB->quote($usr_id, "integer").", ".$ilDB->quote($tii_response["userid"], "integer").")".
						" ON DUPLICATE KEY UPDATE tii_usr_id = ".$ilDB->quote($tii_response["userid"], "integer"));

				$this->logAction("1", "2", $tii_response["status"], $tii_response["rcode"], $msg);

				// Join class
				if ($usertype == 1 && $tii_course_id != 0)
				{
					$tii_vars["cid"] = $tii_course_id;
					$tii_vars["ctl"] = $tii_course_title;
					$tii_vars["uid"] = $tii_response["userid"];

					$tii_response = $this->tiiCall("3", "2", $tii_vars);
					$msg = $tii_response["rmessage"];
					if ($tii_response["status"] == "Success")
					{
						$msg = "User I(".$usr_id.") T(".$tii_response["userid"].") Course T (".$tii_course_id.")";
					}
					$this->logAction("3", "2", $tii_response["status"], $tii_response["rcode"], $msg);
				}
			}
		}
		else
		{
			$this->logAction("1", "2", $tii_response["status"], $tii_response["rcode"], $msg);
		}

		return $tii_usr_id;
	}

	/**
	 * Remove User from their role in the class
	 */
	function removeUserFromClass($usr_id, $usertype)
	{
		$course_details = $this->getVar("course_details");

		$user_details = new ilObjUser($usr_id);
		$user_details->tii_usr_id = $this->getTiiUserId($usr_id);

		if (empty($_SESSION["tii_session_id"]))
		{
			$this->createTiiSession($usr_id, $usertype);
		}

		// Remove User from Class in Turnitin
		$tii_vars = array(
			"uid" => $user_details->tii_usr_id,
			"uem" => $user_details->getEmail(),
			"ufn" => $user_details->getFirstname(),
			"uln" => $user_details->getLastname(),
			"utp" => $usertype,
			"cid" => $course_details["tii_course_id"],
			"ctl" => $course_details["title"]
		);

		// Set whether response email has to be sent
		if ($usertype == 2 && $this->plugin_config["instructor_emails"] == 0)
		{
			$tii_vars["dis"] = 1;
		}

		if ($usertype == 1 && $this->plugin_config["student_emails"] == 0)
		{
			$tii_vars["dis"] = 1;
		}

		if (!empty($_SESSION["tii_session_id"]))
		{
			$tii_vars["session-id"] = $_SESSION["tii_session_id"];
		}

		$tii_response = $this->tiiCall("19", "2", $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success") {
			$msg = "Course I(".$course_details["obj_id"].") T(".$course_details["tii_course_id"].")".
					" : Tutor I(".$usr_id.") T(".$user_details->tii_usr_id.")";
		}
		$this->logAction("19", "2", $tii_response["status"], $tii_response["rcode"], $msg);

		if ($tii_response["status"] == "Success")
		{
			return "Success";
		}
		else
		{
			return $msg;
		}
	}

	/**
	 * Add Tutor to class
	 */
	function addTutorToTii()
	{
		$tii_usr_id = $this->createTiiUser((int)$_REQUEST["tutor_id"], "2");

		if (empty($_SESSION["tii_session_id"]))
		{
			$this->createTiiSession($_REQUEST["tutor_id"], "2");
		}

		if ($tii_usr_id == 0)
		{
			return false;
		}
		else
		{
			$this->syncCourseWithTii((int)$_REQUEST["tutor_id"]);
			return true;
		}
	}

	/**
	 * Upload Paper and send to Tii
	 */
	function uploadPaper()
	{
		global $ilUser, $ilDB;
		$error = "N";
		$title = $_REQUEST["title"];
		$course_details = $this->getVar("course_details");

        // Get Student Id and details that submission is for
        $student_id = (!empty($_REQUEST["student_id"])) ? (int)$_REQUEST["student_id"] : $ilUser->getId();

        // Check whether user is a tutor or if student they aren't submitting for someone else
        if (!$course_details["isTutor"] && !$course_details["isAdmin"] && $ilUser->getId() != $student_id)
        {
        	$error = "Y";
        	$msg = $this->lng->txt('no_permission');
        }

		// Check whether email address is already being used by another user account - Tii requires a unique email address, Ilias does not
		if (!$this->checkUserEmailDoesntExistInTii($student_id))
		{
			$error = "Y";
			$msg = $this->txt("tii_duplicate_email");
		}

		// If submission is text then save it to a text file
		if ($_REQUEST["submission_format"] == 1)
		{
            if (trim($_REQUEST["paper_text"]) == "")
            {
                $msg = $this->txt("please_provide_submission_text");
				$error = "Y";
            }
            else
            {
				$filename = "paper_".$this->tii_assign_id."_".$student_id."_".date("YmdHis").".txt";
				$fp = fopen(CLIENT_DATA_DIR.'/TurnitinAssignment/'.$filename, 'a');
				fwrite($fp, $_REQUEST["paper_text"]);
				fclose($fp);
            }
		}

		if ($_REQUEST["submission_format"] == 2)
		{
			if ($_FILES["paper"]["error"] == 4)
			{
				$msg = $this->txt("file_doesnt_exist");
				$error = "Y";
			}
			else if ($_FILES["paper"]["error"] > 0)
			{
				$msg = $this->txt("file_could_not_be_uploaded")." ".$this->txt("file_error_code")." (".$_FILES["paper"]["error"].")";
				$error = "Y";
			}
			else
			{
                // Redo filename - naming convention is filename_userid_datetime.ext
                $path_info = pathinfo($_FILES["paper"]['name']);
                // If no title specified then use file name as title
                if (trim($_REQUEST["title"]) == "")
                {
                    $title = $path_info['filename'];
                }
                $filename = $path_info['filename']."_".$student_id."_".date("YmdHis").".".$path_info['extension'];

                if (!move_uploaded_file($_FILES["paper"]['tmp_name'], CLIENT_DATA_DIR.'/TurnitinAssignment/'.$filename))
                {
                    $msg = $this->txt("file_could_not_be_uploaded")." ".$this->txt("file_error_code")." (-)";
                    $error = "Y";
                }
			}
		}

		if ($error == "N")
		{
			$this->createTiiUser($student_id, 1, $course_details["tii_course_id"], $course_details["tii_course_title"]);

			// Get student details and tii id
			$student_details = new ilObjUser($student_id);
			$student_details->tii_usr_id = $this->getTiiUserId($student_id);

			$query = $ilDB->query("SELECT tii_paper_id FROM rep_robj_xtii_papers".
					" WHERE usr_id = ".$ilDB->quote($student_id, "integer").
					" AND assign_id = ".$ilDB->quote($this->getVar("tii_assign_id"), "integer")
					);

			$object_id = 0;
			if ($ilDB->numRows($query))
			{
				$rec = $ilDB->fetchAssoc($query);
				$object_id = $rec["tii_paper_id"];
			}

			$tii_vars = array(
				"cid" => $course_details["tii_course_id"],
				"ctl" => $course_details["tii_course_title"],
				"assignid" => $this->getVar("tii_assign_id"),
				"assign" => $this->getVar("tii_assignment_title"),
				"utp" => 1,
				"uid" => $student_details->tii_usr_id,
				"uem" => $student_details->getEmail(),
				"ufn" => $student_details->getFirstname(),
				"uln" => $student_details->getLastname(),
				"ptl" => $title,
				"ptype" => 2,
				"pdata" => '@'.CLIENT_DATA_DIR.'/TurnitinAssignment/'.$filename
				);

			if ($object_id != 0)
			{
				$tii_vars["oid"] = $object_id;
			}

			if (!empty($_SESSION["tii_session_id"]))
			{
				$tii_vars["session-id"] = $_SESSION["tii_session_id"];
			}

			$tii_response = $this->tiiCall("5", "2", $tii_vars);
			$msg = $tii_response["rmessage"];

			if ($tii_response["status"] == "Success")
			{
				$paper_id = $ilDB->nextID('rep_robj_xtii_papers');

				$ilDB->manipulate("INSERT INTO rep_robj_xtii_papers ".
					"(id, usr_id, title, filename, assign_id, tii_paper_id, submission_date_time".
					") VALUES (".
					$ilDB->quote($paper_id, "integer").",".
					$ilDB->quote($student_id, "text").",".
					$ilDB->quote($_REQUEST["title"], "text").",".
					$ilDB->quote($filename, "text").",".
					$ilDB->quote($this->getVar("tii_assign_id"), "integer").",".
					$ilDB->quote($tii_response["objectID"], "integer").",".
					$ilDB->quote(date('Y-m-d H:i:s'), "timestamp").")".
					" ON DUPLICATE KEY UPDATE title = ".$ilDB->quote($_REQUEST["title"], "text").",".
											" filename = ".$ilDB->quote($filename, "text").",".
											" submission_date_time = ".$ilDB->quote(date('Y-m-d H:i:s'), "timestamp")
					);

				$msg = "Paper T(".$tii_response["objectID"].") : User I(".$student_id.") T(".$student_details->tii_usr_id.")";
				$this->logAction("5", "2", $tii_response["status"], $tii_response["rcode"], $msg);

				$msg = "Success";
			}
			else
			{
				$this->logAction("5", "2", $tii_response["status"], $tii_response["rcode"], $msg);
			}

			if (!empty($_SESSION["tii_session_id"]))
			{
				$this->endTiiSession($student_id, 1);
			}
		}

		return $msg;
	}

	/**
	 * Delete a submission from Turnitin
	 */
	function deleteTiiSubmission($paper_id)
	{
		global $ilUser, $ilDB;

		$course_details = $this->getVar("course_details");

		$tutor_details = $ilUser;
		if (!$course_details["isTutor"])
		{
			$tutor_details = new ilObjUser($course_details["tii_course_tutor"]);
		}

		$tii_vars = array(
				"uid" => $this->getTiiUserId($tutor_details->getId()),
				"uem" => $tutor_details->getEmail(),
				"ufn" => $tutor_details->getFirstname(),
				"uln" => $tutor_details->getLastname(),
				"utp" => 2,
				"oid" => $paper_id
				);

		$tii_response = $this->tiiCall("8", "2", $tii_vars);
		$msg = $tii_response["rmessage"];
		if ($tii_response["status"] == "Success")
		{
			$msg = " Paper deleted T(".$paper_id.") by User I(".$tutor_details->getId().") T(".$this->getTiiUserId($tutor_details->getId()).")";

			$query = $ilDB->query("SELECT * FROM rep_robj_xtii_papers " .
							" WHERE tii_paper_id = ".$ilDB->quote($paper_id, "integer")
					);

			if ($ilDB->numRows($query) > 0)
			{
				$rec = $ilDB->fetchAssoc($query);

				try {
					unlink(CLIENT_DATA_DIR.'/TurnitinAssignment/'.$rec["filename"]);

					$query = $ilDB->manipulate("DELETE FROM rep_robj_xtii_papers " .
							" WHERE tii_paper_id = ".$ilDB->quote($paper_id, "integer")
					);
				}
				catch (Exception $e)
				{
					$this->logAction("8", "2", "Local Delete Failed", 0, $e->getMessage());
				}
			}
		}
		$this->logAction("8", "2", $tii_response["status"], $tii_response["rcode"], $msg);
	}

	/**
	 * Open Tii Document Viewer
	 */
	function openDocument($paper_id, $mode)
	{
		global $ilUser;

		$course_details = $this->getVar("course_details");

		$fcmd = 1;
		switch ($mode)
		{
			case "viewFile":
				$fid = 7;
				break;
			case "downloadFile":
				$fid = 7;
				$fcmd = 2;
				break;
			case "originalityReport":
				$fid = 6;
				break;
			case "grademark":
				$fid = 13;
				break;
		}

		if ($course_details["isMember"] || $course_details["isTutor"])
		{
			$tii_vars2 = array(
				"uid" => $this->getTiiUserId($ilUser->getId()),
				"uem" => $ilUser->getEmail(),
				"ufn" => $ilUser->getFirstname(),
				"uln" => $ilUser->getLastname()
				);

			$tii_vars2["utp"] = ($course_details["isMember"]) ? 1 : 2;
		}
		else
		{
			// Get tutor details
			$tutor_details = new ilObjUser($course_details["tii_course_tutor"]);

			$tii_vars2 = array(
				"uid" => $this->getTiiUserId($course_details["tii_course_tutor"]),
				"uem" => $tutor_details->getEmail(),
				"ufn" => $tutor_details->getFirstname(),
				"uln" => $tutor_details->getLastname(),
				"utp" => 2
				);
		}

		// For some reason the course id and title have to be included when downloading a file, not in the view calls though
		$tii_vars = array(
			"oid" => $paper_id,
			"cid" => $course_details["tii_course_id"],
			"ctl" => $course_details["tii_course_title"]
			);

		$tii_vars = array_merge($tii_vars, $tii_vars2);
		$tii_response = $this->tiiCall($fid, $fcmd, $tii_vars);
		if ($tii_response["status"] != "Success")
		{
			$this->logAction($fid, $fcmd, $tii_response["status"], $tii_response["rcode"], $tii_response["rmessage"]);
		}
	}

	/**
	 * Turn off Anonymous Marking for the clicked submission
	 */
	function turnOffAnonMarking()
	{
		global $ilUser;

		$course_details = $this->getVar("course_details");

		$tutor_id = $ilUser->getId();
		$tutor_details = $ilUser;
		if (!$course_details["isTutor"] && $course_details["isAdmin"])
		{
			$tutor_id = $course_details["tii_course_tutor"];
			$tutor_details = new ilObjUser($tutor_id);
		}
		$_REQUEST["anon_reason"] = (empty($_REQUEST["anon_reason"])) ? "Reason: No specific reason" : "Reason: ".$_REQUEST["anon_reason"];

		$tii_vars = array(
			"oid" => $_REQUEST["paper_id"],
			"anon_reason" => $_REQUEST["anon_reason"],
			"uid" => $this->getTiiUserId($tutor_id),
			"utp" => 2,
			"uem" => $tutor_details->getEmail(),
			"ufn" => $tutor_details->getFirstname(),
			"uln" => $tutor_details->getLastname()
			);

		$tii_response = $this->tiiCall("16", "3", $tii_vars);
		if ($tii_response["status"] != "Success")
		{
			$this->logAction("16", "3", $tii_response["status"], $tii_response["rcode"], $tii_response["rmessage"]);
		}
	}

	/**
	 * Download the selected submissions
	 */
	function bulkDownloadTiiSubmissions()//$paper_ids
	{
		$course_details = $this->getVar("course_details");

		// Write paper ids to csv file
		//$filename = "bulk_download_".date("Ymd_His").".csv";
		//$fp = fopen('./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/uploaded_files/'.$filename, 'w');
		//foreach($paper_ids as $paper)
		//{
		//	fwrite($fp, $paper.",");
		//}
		//fclose($fp);

		// Get tutor details
		$tutor_details = new ilObjUser($course_details["tii_course_tutor"]);

		$tii_vars = array(
			"uid" => $this->getTiiUserId($course_details["tii_course_tutor"]),
			"uem" => $tutor_details->getEmail(),
			"ufn" => $tutor_details->getFirstname(),
			"uln" => $tutor_details->getLastname(),
			"utp" => 2,
			"assignid" => $this->getVar("tii_assign_id"),
			"assign" => $this->getTitle(),
			"cid" => $course_details["tii_course_id"],
			"ctl" => $course_details["title"]
			//,"attached_file" => "@Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/uploaded_files/".$filename,
			);

		$tii_response = $this->tiiCall("21", "1", $tii_vars);
		if ($tii_response["status"] != "Success")
		{
			$this->logAction("21", "1", $tii_response["status"], $tii_response["rcode"], $tii_response["rmessage"]);
		}
	}

	/**
	 * The Course end date is either 6 months from creation date or 6 months after the end date of the last assignment
	 */
	function workOutCourseEndDate($crs_obj_id, $course_creation_date)
	{
		global $ilDB;

		$query = $ilDB->query("SELECT end_date FROM rep_robj_xtii_assnment ".
			" WHERE parent_course_obj_id = ".$ilDB->quote($crs_obj_id, "integer").
			" ORDER BY end_date DESC" .
			" LIMIT 1 "
			);
		if ($ilDB->numRows($query) == 0)
		{
			$course_end_date = strtotime('+6 months', strtotime($course_creation_date));
		}
		else
		{
			$rec = $ilDB->fetchAssoc($query);
			$course_end_date = strtotime('+6 months', strtotime($rec["end_date"]));
		}

		return $course_end_date;
	}

	/**
	 * Get the Ilias user id from the paper id
	 */
	function getUserIdOfSubmission($paper_id)
	{
		global $ilDB;
		$usr_id = 0;

		$query = $ilDB->query("SELECT usr_id FROM rep_robj_xtii_papers ".
			" WHERE tii_paper_id = ".$ilDB->quote($paper_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($query))
		{
			$usr_id = (int)$rec["usr_id"];
		}

		return $usr_id;
	}

	/**
	 * Get the Turnitin User Id from the Ilias User Id
	 */
	static function getTiiUserId($usr_id)
	{
		global $ilDB;
		$tii_usr_id = 0;

		$query = $ilDB->query("SELECT tii_usr_id FROM rep_robj_xtii_users ".
			" WHERE usr_id = ".$ilDB->quote($usr_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($query))
		{
			$tii_usr_id = (int)$rec["tii_usr_id"];
		}

		return $tii_usr_id;
	}

	/**
	 * Get the Ilias User Id from the Turnitin User Id
	 */
	function getIliasUserId($tii_usr_id)
	{
		global $ilDB;
		$usr_id = 0;

		$query = $ilDB->query("SELECT usr_id FROM rep_robj_xtii_users ".
			" WHERE tii_usr_id = ".$ilDB->quote($tii_usr_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($query))
		{
			$usr_id = (int)$rec["usr_id"];
		}

		return $usr_id;
	}

	/**
	 * Set date as a string
	 */
	function setDate($var, $value, $type = "array")
	{
		if ($type == "string")
		{
			$datetime = explode(" ", $value);
			$datetime["date"] = $datetime[0];
			$datetime["time"] = $datetime[1];
		}
		else
		{
			$datetime = $value;
		}
		$this->$var = $datetime;
	}

	/**
	 * Set class attribute
	 */
	function setVar($var, $value)
	{
		$this->$var = $value;
	}

	/**
	 * Get class attribute
	 */
	function getVar($var)
	{
        return $this->$var;
	}
}