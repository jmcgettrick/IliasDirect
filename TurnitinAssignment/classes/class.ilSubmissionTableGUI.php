<?php
include_once("./Services/Table/classes/class.ilTable2GUI.php");

class ilSubmissionTableGUI extends ilTable2GUI
{
	protected $end_date;
	protected $posting_date;
	protected $anon;
	protected $translated_matching;
	protected $grademark;
	protected $downloadable;
	
	function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $ilCtrl, $lng;
 
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setId("submissions".$a_parent_obj->id);
        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("rep_robj_xtii_author"), "lastname");
        $this->addColumn($lng->txt("rep_robj_xtii_submission_title"), "title");
        $this->addColumn($lng->txt("rep_robj_xtii_similarity"), "overlap", "", false, "rightish");
        
        $this->translated_matching = 0;
        if ($a_parent_obj->object->plugin_config["translated_matching"] == 1 && $a_parent_obj->object->translated == 1)
        {
        	$this->translated_matching = 1;
        }
        
        $this->grademark = 0;
        if ($a_parent_obj->object->plugin_config["grademark"] == 1)
        {
        	$this->grademark = 1;
        	$this->addColumn($lng->txt("rep_robj_xtii_grade"), "score", "", false, "center");
        }
        
        $this->addColumn($lng->txt("rep_robj_xtii_response"), "", "", false, "center");
        $this->addColumn($lng->txt("rep_robj_xtii_file"), "", "", false, "center");
        $this->addColumn($lng->txt("rep_robj_xtii_paper_id"), "paper_id");
        $this->addColumn($lng->txt("rep_robj_xtii_submission_date"), "date_submitted");
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "showSubmissions"));
        $this->setShowRowsSelector(true);
		$this->setExternalSorting(true);
		$this->setNoEntriesText($lng->txt("rep_robj_xtii_no_students_enrolled"));
        
        // Set Row limit to show if it's changed
		if (isset($_REQUEST["submissions_trows"]))
        {
        	$_SESSION["tbl_limit_custom"] = (int)$_REQUEST["submissions_trows"];
        	$this->resetOffset();
        }
        if ((int)$_SESSION["tbl_limit_custom"] > 0)
        {
			$this->setLimit($_SESSION["tbl_limit_custom"]);
        }
        
        $this->end_date = strtotime($a_parent_obj->object->end_date["date"]." ".$a_parent_obj->object->end_date["time"]);
        $this->posting_date = strtotime($a_parent_obj->object->posting_date["date"]." ".$a_parent_obj->object->posting_date["time"]);
        $this->anon = 0;
        if ($a_parent_obj->object->plugin_config["anon_marking"] == 1 && $a_parent_obj->object->anon == 1)
        {
        	$this->anon = 1;	
        }
        
        $this->setDefaultOrderField("paper_id");
        $this->setDefaultOrderDirection("desc");
        
		// Show download link, don't show it if anon marking is enabled and not past posting date yet
        $this->downloadable = "Y";
        if ($this->anon == 1 && $this->posting_date > time())
        {
        	$this->downloadable = "N";
        }
        /*if ($this->downloadable == "Y")
        {
        	$this->addMultiCommand("", $lng->txt("rep_robj_xtii_download_submissions"));
        }*/
        $this->addMultiCommand("deleteSubmissions", $lng->txt("rep_robj_xtii_delete_submissions"));
        $this->addMultiCommand("exportSubmissions", $lng->txt("rep_robj_xtii_export_data"));
        
        $this->setSelectAllCheckbox("submission_ids");
        $this->setRowTemplate("tpl.submission_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment");

        $this->setData($a_parent_obj->object->submissions);
		
        $this->setTitle($lng->txt("rep_robj_xtii_submission_table_title"));
	}
 
    /**
     * Fill a single data row.
     */
    protected function fillRow($a_set)
    {
       	global $lng, $ilCtrl;

       	$this->tpl->setVariable("CSS_FILE", "tii.css");
       	
       	// Checkbox
       	if (!empty($a_set["objectID"]))
       	{
       		$this->tpl->setVariable("HIDE_CHECKBOX", "");
        	$this->tpl->setVariable("ID", $a_set["objectID"]);
       	}
       	else 
       	{
       		$this->tpl->setVariable("HIDE_CHECKBOX", "hidden");
       	}

       	// Name
        if ($this->anon == 1 && ($a_set["anon"] == "" || $a_set["anon"] == 1) && $this->posting_date > time())
        {
        	$this->tpl->setVariable("VAL_AUTHOR", $lng->txt("rep_robj_xtii_anon_marking_enabled"));        	
        	if (empty($a_set["title"]))
       		{
        		$this->tpl->setVariable("POPUP_CLASS", "");
       		}
       		else
       		{
       			$this->tpl->setVariable("POPUP_CLASS", "launch_popup_anon_form");
       		}
        }
        else
        {
        	$this->tpl->setVariable("VAL_AUTHOR", $a_set["firstname"]." ".$a_set["lastname"]);
        	$this->tpl->setVariable("POPUP_CLASS", "");
        }
       	
       	// If title is empty then there is no submission
       	if (!empty($a_set["title"]))
       	{
       		// Title
       		if ($this->downloadable == "Y")
        	{
       			$this->tpl->setVariable("VAL_SUBMISSION_TITLE", $a_set["title"]);
        	}
        	else
        	{
       			$this->tpl->setVariable("VAL_NO_SUBMISSION_TITLE", $a_set["title"]);
        	}
       		
       		// Similarity Score
       		switch ($a_set["similarityScore"])
	        {
	        	case "-2":
	        	case "-1":
	        		$this->tpl->setVariable("HIDE_SCORE", "hidden");
	        		$this->tpl->setVariable("HIDE_PENDING_REPORT", "");
	        		$this->tpl->setVariable("ORIG_REPORT_NO_SUBMISSION", "hidden");
	        		break;	
	        	default:
	        		$this->tpl->setVariable("VAL_ORIG_REPORT_SCORE", $a_set["report_score_to_show"]);
					$this->tpl->setVariable("VAL_LNG_OVERLAY_TEXT", $a_set["report_score_lng_overlay"]);
	        		$this->tpl->setVariable("HIDE_SCORE", "");
	        		$this->tpl->setVariable("HIDE_PENDING_REPORT", "hidden");
	        		$this->tpl->setVariable("ORIG_REPORT_NO_SUBMISSION", "hidden");
	        		$this->tpl->setVariable("LINK_ORIG_REPORT", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "openOriginalityReport"));
	        		break;
	        }
	        
	        // Grade
	        if ($this->grademark == 1)
	        {
		        $this->tpl->setVariable("LINK_GRADE_FILE", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "openGrademark"));
		        $this->tpl->setVariable("GRADE_FILE", $lng->txt("rep_robj_xtii_grade_file"));
		        $this->tpl->setVariable("GRADE_SUBMISSION", "");
		        
		        if ($a_set["gradeMarkStatus"] != 0)
				{
					$this->tpl->setVariable("GRADE_SUBMISSION", "");
					$this->tpl->setVariable("GRADE_NO_SUBMISSION", "hidden");
					
			        if ($a_set["score"] == "")
			    	{
			    		$this->tpl->setVariable("HIDE_GRADE_IMAGE", "");
			    		$this->tpl->setVariable("HIDE_GRADE_SCORE", "hidden");
			    	}
			    	else
			    	{
			    		$this->tpl->setVariable("VAL_GRADE_SCORE", $a_set["score"]);
			    		$this->tpl->setVariable("HIDE_GRADE_IMAGE", "hidden");
			    		$this->tpl->setVariable("HIDE_GRADE_SCORE", "");
			    	}
				}
				else
				{
					$this->tpl->setVariable("GRADE_NO_SUBMISSION", "");
	       			$this->tpl->setVariable("GRADE_SUBMISSION", "hidden");
				}
	        }
	        else
	        {
	        	$this->tpl->removeBlockData("grade");
	        }
	        
			// Response
			$this->tpl->setVariable("RESPONSE_NO_SUBMISSION", "hidden");
       		$this->tpl->setVariable("RESPONSE_SUBMISSION", "");
			$img = "icon-dot";
			$response_text = $lng->txt("rep_robj_xtii_student_not_viewed");
			if ($a_set["student_responses"])
			{
				$viewed_date_time = strtotime($a_set["student_responses"]["response_time"]); 
				$img = "icon-student-read";
				$response_text = $lng->txt("rep_robj_xtii_student_last_viewed")." ".date("d/m/Y", $viewed_date_time)." ".$lng->txt("rep_robj_xtii_at")." ".date("H:i", $viewed_date_time);
			}
			$this->tpl->setVariable("RESPONSE_IMAGE", $img);
			$this->tpl->setVariable("RESPONSE_TEXT", $response_text);
			
			// File Link
			if ($this->downloadable == "Y")
        	{
				$this->tpl->setVariable("FILE_NO_SUBMISSION", "hidden");
       			$this->tpl->setVariable("FILE_SUBMISSION", "");
       			$this->tpl->setVariable("FILE_NOT_DOWNLOADABLE", "hidden");
				$this->tpl->setVariable("LINK_FILE_TITLE", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "viewSubmission"));
	        	$this->tpl->setVariable("LINK_FILE", $ilCtrl->getLinkTargetByClass("ilobjturnitinassignmentgui", "downloadSubmission"));
	        	$this->tpl->setVariable("VAL_FILE", $a_set["objectID"]);
	        	$this->tpl->setVariable("DOWNLOAD_FILE", $lng->txt("rep_robj_xtii_download_file"));
        	}
        	else
        	{
        		$this->tpl->setVariable("FILE_NOT_DOWNLOADABLE", "");
        		$this->tpl->setVariable("CANT_DOWNLOAD_FILE", $lng->txt("rep_robj_xtii_anon_docs_not_download"));
        		$this->tpl->setVariable("FILE_SUBMISSION", "hidden");
        		$this->tpl->setVariable("FILE_NO_SUBMISSION", "hidden");
        	}

	        // Paper Id
	        $this->tpl->setVariable("VAL_PAPER_ID", $a_set["objectID"]);

	        // Date
                $date_submitted = ilDatePresentation::formatDate(new ilDate(strtotime($a_set["date_submitted"]),IL_CAL_UNIX));
        	if (strtotime($a_set["date_submitted"]) > $this->end_date)
	        {
	        	$this->tpl->setVariable("VAL_DATE", $date_submitted);
	        	$this->tpl->setVariable("VAL_DATE_CLASS", "late_submission_date");
	        }
	        else
	        {
	        	$this->tpl->setVariable("VAL_DATE", $date_submitted);
	        }
       	}
       	else
       	{
       		// Title
       		$this->tpl->setVariable("VAL_NO_SUBMISSION_TITLE", "-- ".$lng->txt("rep_robj_xtii_table_no_submission")." --");

       		// Similarity Score
       		$this->tpl->setVariable("HIDE_SCORE", "hidden");
       		$this->tpl->setVariable("HIDE_PENDING_REPORT", "hidden");
       		$this->tpl->setVariable("ORIG_REPORT_NO_SUBMISSION", "");
			
			// Translated Similarity Score
			if ($this->translated_matching == 1)
			{
				$this->tpl->setVariable("HIDE_TRANSLATED_SCORE", "hidden");
	       		$this->tpl->setVariable("HIDE_PENDING_TRANSLATED_REPORT", "hidden");
	       		$this->tpl->setVariable("TRANSLATED_REPORT_NO_SUBMISSION", "");
	       		$this->tpl->setVariable("HIDE_TRANSLATED_REPORT_ERROR", "hidden");
	       		$this->tpl->setVariable("HIDE_TRANSLATED_REPORT_NOT_APPLICABLE", "hidden");
			}
			else
			{
				$this->tpl->removeBlockData("translated_similarity");
			}
			
       		// Grade
       		if ($this->grademark == 1)
	        {
       			$this->tpl->setVariable("GRADE_NO_SUBMISSION", "");
       			$this->tpl->setVariable("GRADE_SUBMISSION", "hidden");
	        }
	        else
	        {
	        	$this->tpl->removeBlockData("grade");
	        }
       		
       		// Response
       		$this->tpl->setVariable("RESPONSE_NO_SUBMISSION", "");
       		$this->tpl->setVariable("RESPONSE_SUBMISSION", "hidden");
       		$img = "icon-dot";
			$this->tpl->setVariable("RESPONSE_IMAGE", $img);
       		
       		// File
       		$this->tpl->setVariable("FILE_NO_SUBMISSION", "");
       		$this->tpl->setVariable("FILE_SUBMISSION", "hidden");
       		$this->tpl->setVariable("FILE_NOT_DOWNLOADABLE", "hidden");
       		
       		// Paper Id
	        $this->tpl->setVariable("VAL_PAPER_ID", "--");
	        
	        // Date
	        if ($this->end_date > time())
	        {
	        	$this->tpl->setVariable("VAL_DATE", "--");
	        }
	        else
	        {
	        	$this->tpl->setVariable("VAL_DATE", $lng->txt("rep_robj_xtii_late"));
	        	$this->tpl->setVariable("VAL_DATE_CLASS", "late_submission_date");
	        } 
       	}
    } 
}
?>