<?php
include_once("./Services/Table/classes/class.ilTable2GUI.php");

class ilLogTiiTableGUI extends ilTable2GUI
{
	function __construct($a_parent_obj, $a_parent_cmd, $data)
    {
        global $ilCtrl, $lng;
 
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
       	$this->setId("log".$a_parent_obj->id);
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("rep_robj_xtii_date_time"), "date_time");
        $this->addColumn($lng->txt("rep_robj_xtii_usr_id"), "", "", "", "right");
        $this->addColumn($lng->txt("rep_robj_xtii_fid"), "", "", "", "right");
        $this->addColumn($lng->txt("rep_robj_xtii_fcmd"), "", "", "", "right");
        $this->addColumn($lng->txt("rep_robj_xtii_status"));
        $this->addColumn($lng->txt("rep_robj_xtii_rcode"), "", "", "", "right");
        $this->addColumn($lng->txt("rep_robj_xtii_msg"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "tiiLog"));
        $this->setShowRowsSelector(true);
		$this->setExternalSorting(true);
		
		// Set Row limit to show if it's changed
		if (isset($_REQUEST["log_trows"]))
        {
        	$_SESSION["tbl_limit_custom"] = (int)$_REQUEST["log_trows"];
        	$this->resetOffset();
        }
        if ((int)$_SESSION["tbl_limit_custom"] > 0)
        {
			$this->setLimit($_SESSION["tbl_limit_custom"]);
        }
        
        $this->setDefaultOrderField("date_time");
        $this->setDefaultOrderDirection("asc");

        $this->setRowTemplate("tpl.tii_log_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");
        $this->setData($data);
	}
 
    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
       	global $lng, $ilCtrl;
       	
       	$this->tpl->setVariable("VAL_DATE_TIME", $a_set["date_time"]);
       	$this->tpl->setVariable("VAL_UID", $a_set["usr_id"]);
       	$this->tpl->setVariable("VAL_FID", $a_set["fid"]);
       	$this->tpl->setVariable("VAL_FCMD", $a_set["fcmd"]);
       	$this->tpl->setVariable("VAL_STATUS", $a_set["status"]);
       	$this->tpl->setVariable("VAL_RCODE", $a_set["rcode"]);
       	$this->tpl->setVariable("VAL_MSG", $a_set["msg"]);
    } 
}
?>