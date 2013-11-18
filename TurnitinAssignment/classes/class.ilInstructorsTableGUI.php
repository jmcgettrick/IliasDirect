<?php
include_once("./Services/Table/classes/class.ilTable2GUI.php");

class ilInstructorsTableGUI extends ilTable2GUI
{
	protected $show_checkboxes;

	function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $ilCtrl, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);

       	$this->setId("tutor".$a_parent_obj->id);
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("rep_robj_xtii_tii_usr_id"));
        $this->addColumn($lng->txt("rep_robj_xtii_username"));
        $this->addColumn($lng->txt("rep_robj_xtii_name"));
        $this->addColumn($lng->txt("rep_robj_xtii_email"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "removeTutor"));
        $this->setShowRowsSelector(true);
		$this->setExternalSorting(true);

		// Set Row limit to show if it's changed
		if (isset($_REQUEST["tutor_trows"]))
        {
        	$_SESSION["tbl_limit_custom"] = (int)$_REQUEST["tutor_trows"];
        	$this->resetOffset();
        }
        if ((int)$_SESSION["tbl_limit_custom"] > 0)
        {
			$this->setLimit($_SESSION["tbl_limit_custom"]);
        }

        //$this->setDefaultOrderField("usr_id");
        //$this->setDefaultOrderDirection("asc");

        $this->show_checkboxes = 0;
       	if (count($a_parent_obj->object->instructors) > 1)
       	{
       		$this->show_checkboxes = 1;
       		$this->addMultiCommand("removeTutor", $lng->txt("rep_robj_xtii_remove_tutor"));
       	}

        $this->setRowTemplate("tpl.tii_tutor_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");

        $this->setData($a_parent_obj->object->instructors);
	}

    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
       	global $lng;

       	if ($this->show_checkboxes == 0)
       	{
       		$this->tpl->removeBlockData("checkb");
       	}
       	else
       	{
       		$this->tpl->setVariable("VAL_ID", $a_set["usr_id"]);
       	}

       	$this->tpl->setVariable("VAL_USER_ID", $a_set["usr_id"]);
       	$this->tpl->setVariable("VAL_TII_USER_ID", $a_set["tii_usr_id"]);
       	$this->tpl->setVariable("VAL_NAME", $a_set["firstname"]." ".$a_set["lastname"]);
       	$this->tpl->setVariable("VAL_EMAIL", $a_set["email"]);
       	$this->tpl->setVariable("VAL_USERNAME", $a_set["login"]);
    }
}
?>