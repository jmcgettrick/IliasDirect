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

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/**
* ListGUI implementation for Example object plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*
* PLEASE do not create instances of larger classes here. Use the
* ...Access class to get DB data and keep it small.
*
* @author 		Alex Killing <alex.killing@gmx.de>
*/
class ilObjTurnitinAssignmentListGUI extends ilObjectPluginListGUI
{
	/**
	* Init type
	*/
	function initType()
	{
        $this->setType("xtii");
	}

	/**
	* Get name of gui class handling the commands
	*/
	function getGuiClass()
	{
        return "ilObjTurnitinAssignmentGUI";
	}

	/**
	* Get commands
	*/
	function initCommands()
	{
        global $tree, $ilUser;

		// These should be somewhere else ideally but the init function can't be overwritten in ilObjectPluginListGUI
		$this->cut_enabled = false;
		$this->link_enabled = false;
		$this->copy_enabled = false;
		$this->repository_transfer_enabled = false;

		// Set session vars to refresh
		$_SESSION["refresh_submissions"] = 1;
		$_SESSION["refresh_instructors"] = 1;

		// Get the node id
		$node_id = $_REQUEST["ref_id"];
		if (!$_REQUEST["ref_id"]) {
			$target = explode("_", $_REQUEST["target"]);
			$node_id = $target[1];
		}

		// Get parent item details
		$parent_nodes = array_reverse($tree->getNodePath($node_id));
		foreach ($parent_nodes as $parent_node) {
			$parent = $parent_node;

			if ($parent_node['type'] == 'crs' || $parent_node['type'] == 'grp') {
				break;
			}
		}
		$parent_details = $tree->getNodeData($parent_node['child']);

		// Get Course/Group id
		if ($parent_details['type'] == 'crs')
		{
			// Work out if user is a student to deduce default command
			include_once("Modules/Course/classes/class.ilCourseParticipants.php");
			$participants = new ilCourseParticipants($parent_details['obj_id']);
		}
		else
		{
			// Work out if user is a student to deduce default command
			include_once("Modules/Group/classes/class.ilGroupParticipants.php");
			$participants = new ilGroupParticipants($parent_details['obj_id']);
		}

		$default_command = "showLoadRedirectSubmissions"; //"showSubmissions";
		if ($participants->isMember($ilUser->getId()) || !empty($_SESSION["member_view_container"]))
		{
			$default_command = "showLoadRedirectDetails"; //"showDetails";
		}

		return array
		(
			array(
				"permission" => "read",
				"cmd" => $default_command,
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "editSettings",
				"txt" => $this->txt("edit"),
				"default" => false)
		);
	}

	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilDB, $ilUser, $tree;

        $this->enableCheckbox(false);
		$props = array();

		$this->plugin->includeClass("class.ilObjTurnitinAssignmentAccess.php");

		if (!ilObjTurnitinAssignmentAccess::checkOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $this->txt("status"), "value" => $this->txt("offline"));
		}

        $end_date = ilDatePresentation::formatDate(new ilDateTime(strtotime(ilObjTurnitinAssignmentAccess::getEndDate($this->obj_id)),IL_CAL_UNIX));
        $props[] = array("alert" => true, "property" => $this->txt("due_date"), "value" => $end_date, "newline" => true);

		$post_date = strtotime(ilObjTurnitinAssignmentAccess::getPostDate($this->obj_id));

		// Get parent item details
		$parent_nodes = array_reverse($tree->getNodePath($this->ref_id));
		foreach ($parent_nodes as $parent_node) {
			$parent = $parent_node;

			if ($parent_node['type'] == 'crs' || $parent_node['type'] == 'grp')
			{
				break;
			}
		}
		$parent_details = $tree->getNodeData($parent_node['child']);

		// Get Course/Group id
		if ($parent_details['type'] == 'crs')
		{
			// Work out if user is a student to deduce default command
			include_once("Modules/Course/classes/class.ilCourseParticipants.php");
			$participants = new ilCourseParticipants($parent_details['obj_id']);
		}
		else
		{
			// Work out if user is a student to deduce default command
			include_once("Modules/Group/classes/class.ilGroupParticipants.php");
			$participants = new ilGroupParticipants($parent_details['obj_id']);
		}

        // Get plugin configuration data
        $this->plugin->includeClass("class.ilTurnitinAssignmentPlugin.php");
		$pl = new ilTurnitinAssignmentPlugin();
		$pl->getConfigData();
		$this->plugin_config = array("grademark" => $pl->grademark, "ets" => $pl->ets, "anon_marking" => $pl->anon_marking,
										"institutional_repository" => $pl->institutional_repository, "student_emails" => $pl->student_emails,
										"instructor_emails" => $pl->instructor_emails, "digital_receipts" => $pl->digital_receipts,
										"translated_matching" => $pl->translated_matching);

		if ($participants->isMember($ilUser->getId()))
		{
            if (strtotime(ilObjTurnitinAssignmentAccess::getStartDate($this->obj_id)) > time())
            {
                $alert = true;
                $status_text = $this->txt("no_submission_until_start");
            }
            else
            {
                $alert = false;
                $submission = ilObjTurnitinAssignmentAccess::checkSubmission($ilUser->getId(), $this->obj_id);
                if (!$submission || empty($submission["objectID"]))
                {
                    $status_text = $this->txt("no_submission");
                }
                else
                {
                    $submission_date = ilDatePresentation::formatDate(new ilDateTime(strtotime($submission["submission_date_time"]),IL_CAL_UNIX));
                    $status_text = $this->txt("submission_made")." ".$this->txt("on")." ".$submission_date;
				}
            }
            $props[] = array("alert" => $alert, "property" => $this->txt("status"), "value" => $status_text, "newline" => true);

            $point_value = ilObjTurnitinAssignmentAccess::getPointValue($this->obj_id);
			if ($this->plugin_config["grademark"] && $point_value > 0 && $post_date < time())
			{
				$grade = ilObjTurnitinAssignmentAccess::getGrade($ilUser->getId(), $this->obj_id);

				if (is_int($grade))
				{
                    $grade_text = $grade."/".$point_value;
				}
				else
				{
                    $grade_text = $this->txt("not_yet_graded");
				}

				$props[] = array("alert" => false, "property" => $this->txt("grade"), "value" => $grade_text, "newline" => true);
			}
		}
		return $props;
	}
}
?>
