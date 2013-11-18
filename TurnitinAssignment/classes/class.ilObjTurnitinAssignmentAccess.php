<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/**
* Access/Condition checking for Turnitin Assignment object
*
* Please do not create instances of large application classes (like ilObjTurnitinAssignment)
* Write small methods within this class to determin the status.
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilObjTurnitinAssignmentAccess extends ilObjectPluginAccess
{

	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do usual RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $ilAccess;
                
                if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "read":
				if (!ilObjTurnitinAssignmentAccess::checkOnline($a_obj_id) &&
					!$ilAccess->checkAccessOfUser($a_user_id, "write", "", $a_ref_id))
				{
					return false;
				}
				break;
		}

		return true;
	}
	
	/**
	* Check online status of example object
	*/
	static function checkOnline($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT is_online FROM rep_robj_xtii_assnment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		return (boolean) $rec["is_online"];
	}
        
    static function getStartDate($a_id) 
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT start_date FROM rep_robj_xtii_assnment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["start_date"];
	}
		
	static function getEndDate($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT end_date FROM rep_robj_xtii_assnment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["end_date"];
	}
	
	static function getPostDate($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT posting_date FROM rep_robj_xtii_assnment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["posting_date"];
	}
	
	static function getPointValue($a_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT point_value FROM rep_robj_xtii_assnment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["point_value"];
	}
	
	static function checkSubmission($usr_id, $a_id)
	{
		global $ilUser, $ilDB;
		
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilObjTurnitinAssignment.php");
		$tii_usr_id = ilObjTurnitinAssignment::getTiiUserId($usr_id);
		
		if ($tii_usr_id != 0)
		{
			$query = $ilDB->query("SELECT P.tii_paper_id, P.submission_date_time, C.tii_course_id, C.tii_course_title, A.tii_assignment_title, A.tii_assign_id".
				" FROM rep_robj_xtii_papers P".
				" LEFT JOIN rep_robj_xtii_assnment A ON P.assign_id = A.tii_assign_id".
				" LEFT JOIN rep_robj_xtii_course C ON A.parent_course_obj_id = C.obj_id".
				" WHERE A.id = ".$ilDB->quote($a_id, "integer").
				" AND P.usr_id = ".$ilDB->quote($usr_id, "integer")
				);
			
			$rec = $ilDB->fetchAssoc($query);
		
			$tii_vars = array(
					"oid" => $rec["tii_paper_id"],
					"uid" => $tii_usr_id,
					"ufn" => $ilUser->getFirstname(),
					"uln" => $ilUser->getLastname(),
					"uem" => $ilUser->getEmail(),
					"utp" => 1,
					"cid" => $rec["tii_course_id"],
					"ctl" => $rec["tii_course_title"],
					"assignid" => $rec["tii_assign_id"],
					"assign" => $rec["tii_assignment_title"]
				);

			include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentTiiCall.php");
			$tii_call = new ilTurnitinAssignmentTiiCall();
			$tii_response = $tii_call->tiiCall("11", "2", $tii_vars);
                        $tii_response["submission_date_time"] = $rec["submission_date_time"];
			return $tii_response;
		}
		else
		{
			return false;
		}
	}
	
	static function getGrade($usr_id, $a_id)
	{
		global $ilUser, $ilDB;
		
		$tii_usr_id = ilObjTurnitinAssignment::getTiiUserId($usr_id);
		
		if ($tii_usr_id != 0)
		{
			$query = $ilDB->query("SELECT P.tii_paper_id, C.tii_course_id, C.tii_course_title, A.tii_assignment_title, A.tii_assign_id".
				" FROM rep_robj_xtii_papers P".
				" LEFT JOIN rep_robj_xtii_assnment A ON P.assign_id = A.tii_assign_id".
				" LEFT JOIN rep_robj_xtii_course C ON A.parent_course_obj_id = C.obj_id".
				" WHERE A.id = ".$ilDB->quote($a_id, "integer").
				" AND P.usr_id = ".$ilDB->quote($usr_id, "integer")
				);
			$rec = $ilDB->fetchAssoc($query);
		
			$tii_vars = array(
					"oid" => $rec["tii_paper_id"],
					"uid" => $tii_usr_id,
					"ufn" => $ilUser->getFirstname(),
					"uln" => $ilUser->getLastname(),
					"uem" => $ilUser->getEmail(),
					"utp" => 1,
					"cid" => $rec["tii_course_id"],
					"ctl" => $rec["tii_course_title"],
					"assignid" => $rec["tii_assign_id"],
					"assign" => $rec["tii_assignment_title"]
				);

			include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentTiiCall.php");
			$tii_call = new ilTurnitinAssignmentTiiCall();
			$tii_response = $tii_call->tiiCall("10", "2", $tii_vars);
			
                        if ($tii_response["object"][$usr_id]["score"] == "") {
                            return "";
                        } else {
                            return (int)$tii_response["object"][$usr_id]["score"];
                        }
		}
		else
		{
			return "";
		}
	}
}
?>