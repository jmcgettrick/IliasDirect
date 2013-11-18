<?php
include_once("./Services/Table/classes/class.ilTable2GUI.php");

class ilUserTiiTableGUI extends ilTable2GUI
{
	function __construct($a_parent_obj, $a_parent_cmd, $data)
    {
        global $ilCtrl, $lng;
 
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
       	$this->setId("user".$a_parent_obj->id);
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("rep_robj_xtii_usr_id"), "usr_id");
        $this->addColumn($lng->txt("rep_robj_xtii_tii_usr_id"), "tii_usr_id");
        $this->addColumn($lng->txt("rep_robj_xtii_name"), "name");
        $this->addColumn($lng->txt("rep_robj_xtii_email"), "email");
        $this->addColumn($lng->txt("rep_robj_xtii_username"), "username");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "unlinkUsers"));
        $this->setShowRowsSelector(true);
		$this->setExternalSorting(true);
		
		// Set Row limit to show if it's changed
		if (isset($_REQUEST["user_trows"]))
        {
        	$_SESSION["tbl_limit_custom"] = (int)$_REQUEST["user_trows"];
        	$this->resetOffset();
        }
        if ((int)$_SESSION["tbl_limit_custom"] > 0)
        {
			$this->setLimit($_SESSION["tbl_limit_custom"]);
        }
		        
        $this->setDefaultOrderField("usr_id");
        $this->setDefaultOrderDirection("asc");
        
        $this->addMultiCommand("unlinkUsers", $lng->txt("rep_robj_xtii_unlink_user"));
        $this->addMultiCommand("linkOrRelinkUsers", $lng->txt("rep_robj_xtii_link_relink_user"));
        $this->addMultiCommand("exportUsers", $lng->txt("rep_robj_xtii_export_user_data"));
       
        $this->setSelectAllCheckbox("usr_ids");
        $this->setRowTemplate("tpl.tii_user_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
        
        $this->setData($data);
	}
 
    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
       	global $lng;
       	
       	$this->tpl->setVariable("VAL_USER_ID", $a_set["usr_id"]);
       	$this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);
       	$this->tpl->setVariable("VAL_TII_USER_ID", $a_set["tii_usr_id"]);
       	$this->tpl->setVariable("VAL_NAME", $a_set["firstname"]." ".$a_set["lastname"]);
       	$this->tpl->setVariable("VAL_EMAIL", $a_set["email"]);
       	$this->tpl->setVariable("VAL_USERNAME", $a_set["login"]);
    }
}
?>