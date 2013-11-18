<#1>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'parent_course_obj_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'tii_assignment_title' => array(
		'type' => 'text',
		'length' => '255'
	),
	'is_online' => array(
		'type' => 'integer',
		'length' => 1
	),
	'point_value' => array(
		'type' => 'integer',
		'length' => 4
	),
	'start_date' => array(
		'type' => 'timestamp'
	),
	'end_date' => array(
		'type' => 'timestamp'
	),
	'posting_date' => array(
		'type' => 'timestamp'
	),
	'tii_assign_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'instructions' => array(
		'type' => 'clob'
	),
	'exclude_type' => array(
		'type' => 'integer',
		'length' => 1
	),
	'exclude_value' => array(
		'type' => 'integer',
		'length' => 4
	),
	'submit_papers_to' => array(
		'type' => 'integer',
		'length' => 4
	),
	'generation_speed' => array(
		'type' => 'integer',
		'length' => 1
	),
	'submission_format' => array(
		'type' => 'integer',
		'length' => 1
	),
	'late_submission' => array(
		'type' => 'integer',
		'length' => 1
	),
	'students_view_reports' => array(
		'type' => 'integer',
		'length' => 1
	),
	'anon' => array(
		'type' => 'integer',
		'length' => 1
	),
	'translated' => array(
		'type' => 'integer',
		'length' => 1
	),
	'exclude_bibliography' => array(
		'type' => 'integer',
		'length' => 1
	),
	'exclude_quoted_materials' => array(
		'type' => 'integer',
		'length' => 1
	),
	's_paper_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'internet_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'journal_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'institution_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_handbook' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_spelling_dictionary' => array(
		'type' => 'text',
		'length' => 10
	),
	'erater_spelling' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_grammar' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_usage' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_mechanics' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_style' => array(
		'type' => 'integer',
		'length' => 1
	)
);

$ilDB->createTable("rep_robj_xtii_assnment", $fields);
$ilDB->addPrimaryKey("rep_robj_xtii_assnment", array("id"));
?>
<#2>
<?php
$fields = array(
	'ref_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'obj_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'tii_course_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'tii_course_title' => array(
		'type' => 'text',
		'length' => '255'
	),
    'tii_course_tutor' => array(
        'type' => 'integer',
		'length' => 4
    ),
	'course_end_date' => array(
		'type' => 'timestamp'
	)
);

$ilDB->createTable("rep_robj_xtii_course", $fields);
$ilDB->addPrimaryKey("rep_robj_xtii_course", array("ref_id"));
?>
<#3>
<?php
$fields = array(
	'usr_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'tii_usr_id' => array(
		'type' => 'integer',
		'length' => 4
	)
);

$ilDB->createTable("rep_robj_xtii_users", $fields);
$ilDB->addPrimaryKey("rep_robj_xtii_users", array("usr_id"));
?>
<#4>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'usr_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'title' => array(
		'type' => 'text',
		'length' => 200
	),
	'filename' => array(
		'type' => 'text',
		'length' => 200
	),
	'assign_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'tii_paper_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'submission_date_time' => array(
		'type' => 'timestamp'
	)
);

$ilDB->createTable("rep_robj_xtii_papers", $fields);
$ilDB->addPrimaryKey("rep_robj_xtii_papers", array("id"));
$ilDB->createSequence("rep_robj_xtii_papers", 1);
?>
<#5>
<?php
$fields = array(
	'api_url' => array(
		'type' => 'text',
		'length' => '255'
	),
	'account_id' => array(
		'type' => 'integer',
		'length' => 4
	),
	'shared_key' => array(
		'type' => 'text',
		'length' => 10
	),
	'grademark' => array(
		'type' => 'integer',
		'length' => 1
	),
	'ets' => array(
		'type' => 'integer',
		'length' => 1
	),
	'anon_marking' => array(
		'type' => 'integer',
		'length' => 1
	),
	'translated_matching' => array(
		'type' => 'integer',
		'length' => 1
	),
	'institutional_repository' => array(
		'type' => 'integer',
		'length' => 1
	),
	'student_emails' => array(
		'type' => 'integer',
		'length' => 1
	),
	'instructor_emails' => array(
		'type' => 'integer',
		'length' => 1
	),
	'digital_receipts' => array(
		'type' => 'integer',
		'length' => 1
	)
);

$ilDB->createTable("rep_robj_xtii_config", $fields);
$ilDB->manipulate("INSERT INTO rep_robj_xtii_config ".
			"(api_url, account_id, shared_key, grademark, ets, anon_marking, translated_matching, institutional_repository, student_emails, instructor_emails, digital_receipts)".
			" VALUES (".
			$ilDB->quote("", "text").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote("", "text").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").
			")");
?>
<#6>
<?php
$query = $ilDB->query("SELECT obj_id, title FROM object_data".
						" WHERE title = ".$ilDB->quote("il_crs_admin", "text").
						" OR  title = ".$ilDB->quote("il_crs_tutor", "text").
						" OR  title = ".$ilDB->quote("il_crs_member", "text").
						" OR  title = ".$ilDB->quote("Roles", "text").
						" OR  title = ".$ilDB->quote("Author", "text")
					);						
while ($rec = $ilDB->fetchAssoc($query))
{
	switch ($rec["title"])
	{
		case "il_crs_admin":
			$admin_id = $rec["obj_id"];
			break;
		case "il_crs_tutor":
			$tutor_id = $rec["obj_id"];
			break;
		case "il_crs_member":
			$member_id = $rec["obj_id"];
			break;
		case "Roles":
			$parent = $rec["obj_id"];
			break;
		case "Author":
			$author_id = $rec["obj_id"];
			break;
	}
	
}
/*$ops_id = 0;
$query = $ilDB->query("SELECT ops_id FROM object_data".
						" WHERE title = ".$ilDB->quote("create", "text").
						" AND ops_id = ".$ilDB->quote("create_".$type, "text")
					);
$rec = $ilDB->fetchAssoc($query);
$ops_id = $rec["ops_id"];*/

$type = "xtii";
$statement = $ilDB->prepareManip("INSERT INTO rbac_templates (rol_id, type, ops_id, parent) VALUES (?,?,?,?)",
        		array("integer", "text", "integer", "integer"));
$data = array(
		array($admin_id, $type, 1, $parent),
        	array($admin_id, $type, 2, $parent),
        	array($admin_id, $type, 3, $parent),
        	array($admin_id, $type, 4, $parent),
        	array($admin_id, $type, 6, $parent),
        	array($tutor_id, $type, 2, $parent),
        	array($tutor_id, $type, 3, $parent),
        	array($tutor_id, $type, 4, $parent),
        	array($tutor_id, $type, 6, $parent),
        	array($member_id, $type, 2, $parent),
        	array($member_id, $type, 3, $parent)
        ); //array($author_id, "crs", $ops_id, $parent), array($tutor_id, "crs", $ops_id, $parent),
$ilDB->executeMultiple($statement, $data);
?>
<#7>
<?php
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'usr_id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'date_time' => array(
		'type' => 'timestamp'
	),
	'type' => array(
		'type' => 'text',
		'length' => 10
	),
	'fid' => array(
		'type' => 'integer',
		'length' => 4
	),
	'fcmd' => array(
		'type' => 'integer',
		'length' => 4
	),
	'status' => array(
		'type' => 'text',
		'length' => 20
	),
	'rcode' => array(
		'type' => 'integer',
		'length' => 8
	),
	'msg' => array(
		'type' => 'clob'
	)
);
$ilDB->createTable("rep_robj_xtii_log", $fields);
$ilDB->addPrimaryKey("rep_robj_xtii_log", array("id"));
$ilDB->createSequence("rep_robj_xtii_log", 1);
?>
<#8>
<?php
$fields = array(
	'is_online' => array(
		'type' => 'integer',
		'length' => 1
	),
	'point_value' => array(
		'type' => 'integer',
		'length' => 4
	),
	'instructions' => array(
		'type' => 'clob'
	),
	'exclude_type' => array(
		'type' => 'integer',
		'length' => 1
	),
	'exclude_value' => array(
		'type' => 'integer',
		'length' => 4
	),
	'submit_papers_to' => array(
		'type' => 'integer',
		'length' => 4
	),
	'generation_speed' => array(
		'type' => 'integer',
		'length' => 4
	),
	'submission_format' => array(
		'type' => 'integer',
		'length' => 1
	),
	'late_submission' => array(
		'type' => 'integer',
		'length' => 1
	),
	'students_view_reports' => array(
		'type' => 'integer',
		'length' => 1
	),
	'anon' => array(
		'type' => 'integer',
		'length' => 1
	),
	'translated' => array(
		'type' => 'integer',
		'length' => 1
	),
	'exclude_bibliography' => array(
		'type' => 'integer',
		'length' => 1
	),
	'exclude_quoted_materials' => array(
		'type' => 'integer',
		'length' => 1
	),
	's_paper_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'internet_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'journal_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'institution_check' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_handbook' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_spelling_dictionary' => array(
		'type' => 'text',
		'length' => 10
	),
	'erater_spelling' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_grammar' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_usage' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_mechanics' => array(
		'type' => 'integer',
		'length' => 1
	),
	'erater_style' => array(
		'type' => 'integer',
		'length' => 1
	)
);

$ilDB->createTable("rep_robj_xtii_default", $fields);
$ilDB->manipulate("INSERT INTO rep_robj_xtii_default ".
			"(is_online, point_value, exclude_type, exclude_value, submit_papers_to, generation_speed, exclude_bibliography, exclude_quoted_materials,".
			" submission_format, late_submission, students_view_reports, anon, translated, s_paper_check, internet_check, journal_check, institution_check, erater, " .
			" erater_handbook, erater_spelling_dictionary, erater_spelling, erater_grammar, erater_usage, erater_mechanics, erater_style".
			") VALUES (".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(100, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(0, "integer").",".
			$ilDB->quote(2, "integer").",".
			$ilDB->quote("en_GB", "text").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").",".
			$ilDB->quote(1, "integer").
			")");
?>