<?php
/*
 *
 * Class to handle Turnitin API calls
 *
 */

// Constant Variables
define('TII_ENCRYPT',0);
define('TII_DIAGNOSTIC',0);
define('TII_SRC',12);//63

class ilTurnitinAssignmentTiiCall
{
	var $tii_variables_not_to_post = array("attached_filename", "paper_ids", "shared_key", "api_url");

	function tiiCall($fid, $fcmd, $tii_vars)
	{
		include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilTurnitinAssignmentPlugin.php");
		$pl = new ilTurnitinAssignmentPlugin();
		$pl->getConfigData();

		$tii_vars["aid"] = $pl->getVar("account_id");
		$tii_vars["shared_key"] = $pl->getVar("shared_key");
		$tii_vars["api_url"] = $pl->getVar("api_url");
		$tii_vars["fid"] = $fid;
		$tii_vars["fcmd"] = $fcmd;
		$tii_vars["gmtime"] = $this->getGMT();
		$tii_vars["idsync"] = $this->getIdSync($fid, $fcmd, (int)$tii_vars["create_session"]);
		$tii_vars['diagnostic'] = TII_DIAGNOSTIC;
		$tii_vars['src'] = TII_SRC;
		$tii_vars['encrypt'] = TII_ENCRYPT;
		$tii_vars['md5'] = $this->generateMd5($tii_vars);

		return $this->makeTiiCall($tii_vars, "post");
	}

	function logAction($fid, $fcmd, $msg)
	{
		global $ilUser, $ilDB;
		if (!file_exists(CLIENT_DATA_DIR.'/TurnitinAssignment/')) {
	        mkdir(CLIENT_DATA_DIR.'/TurnitinAssignment/', 0777, true);
	    }

		$fp = fopen(CLIENT_DATA_DIR.'/TurnitinAssignment/tiiLogFile.txt', 'a');
		fwrite($fp, "\r\n"."Date: ".date("d/m/Y H:i:s")." | Fid: ".$fid." | Fcmd: ".$fcmd."\r\n".$msg."\r\n==========================================");
		fclose($fp);

		$id = $ilDB->nextID('rep_robj_xtii_log');

		$ilDB->manipulate("INSERT INTO rep_robj_xtii_log ".
			"(id, usr_id, date_time, type, fid, fcmd, status, rcode".
			") VALUES (".
			$ilDB->quote($id, "integer").",".
			$ilDB->quote($ilUser->getId(), "integer").",".
			$ilDB->quote(date('Y-m-d H:i:s'), "timestamp").",".
			$ilDB->quote("call", "text").",".
			$ilDB->quote($fid, "integer").",".
			$ilDB->quote($fcmd, "integer").",".
			$ilDB->quote("Calling", "text").",".
			$ilDB->quote(0, "integer").
			")");

		$ilDB->update("rep_robj_xtii_log", array(
        		"msg" => array("clob", $msg)),
        		array('id' => array('integer', $id))
        		);

	}

	function makeTiiCall($tii_vars, $method = "post") {
		$query_string = $this->arrayToQueryString($tii_vars);
		// echo $tii_vars["api_url"].$query_string."<br/>===========<br/>";
		$this->logAction($tii_vars['fid'], $tii_vars['fcmd'], $tii_vars["api_url"].$query_string);
		if (($tii_vars['fid'] == 6 && $tii_vars['fcmd'] == "1") || $tii_vars['fid'] == 7 || ($tii_vars['fid'] == 13 && ($tii_vars['fcmd'] == "1" || $tii_vars['fcmd'] == "3")))
		{
			?>
			<script type="text/javascript">
			<!--
				window.location = '<?php echo $tii_vars["api_url"].$query_string; ?>';
 			//-->
 			</script>
 			<?php
		}
		else if ($tii_vars['fid'] == 21)
		{
			?>
			<script type="text/javascript">
			<!--
				window.open('<?php echo $tii_vars["api_url"].$query_string; ?>', '_blank', 'height=100,width=300');
			//-->
 			</script>
 			<?php
		}
		else if ($tii_vars['fcmd'] == "1")
		{
			header("Location: ".$tii_vars["api_url"].$query_string);
			exit;
		}
		else
		{
			if ($method == "get") {
				if (TII_DIAGNOSTIC == 1) {
					$response = file_get_contents($tii_vars["api_url"].$query_string);
				} else {
					$xml = simplexml_load_file($tii_vars["api_url"].$query_string);
					$response = $this->returnVarsFromXML($xml, $tii_vars['fid'], $tii_vars['fcmd']);
				}
			} else {

				// open connection
				$ch = curl_init();

				// set the url, number of POST vars, POST data
				curl_setopt($ch, CURLOPT_URL, $tii_vars["api_url"]);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $tii_vars);

				// execute post
				$result = curl_exec($ch);
				//print_r(curl_getinfo($ch));

				// close connection
				curl_close($ch);

				if (TII_DIAGNOSTIC == 1) {
					$response = $result;
				} else {
					$response = $this->returnVarsFromXML($result, $tii_vars['fid'], $tii_vars['fcmd']);
				}
			}
		}

		return $response;
	}

	function returnVarsFromXML($xml_string, $fid, $fcmd)
	{
		/*if ($fid == 4 && $fcmd == 7)
		{
			echo $xml_string."<br/>++++++++++++++++<br/>";
		}*/
		//$this->logAction($fid, $fcmd, $xml_string);
		$xml = simplexml_load_string($xml_string);
		if (!$xml)
		{
			$response["rcode"] = 0;
			$response["rmessage"] = "There is a problem connecting to Turnitin";
		}
		else
		{
			foreach ($xml->children() as $k => $v)
			{
				switch ($k)
				{
					case "object":
						include_once("./Customizing/global/plugins/Services/Repository/RepositoryObject/TurnitinAssignment/classes/class.ilObjTurnitinAssignment.php");

						$object_id = 0;
						$object_children = "";

						foreach ($v->children() as $k2 => $v2)
						{
							if ($k2 == "objectID")
							{
								$object_id = (int)$v2;
							}
							if ($k2 == "userid")
							{
								$user_id = ilObjTurnitinAssignment::getIliasUserId((int)$v2);
							}
							if ($k2 == "student_responses")
							{
								foreach ($v2->children() as $k3 => $v3)
								{
									foreach ($v3->children() as $k4 => $v4)
									{
										$object_children[$k2][$k4] = (string)$v4;
									}
								}
							}
							else if ($k2 == "translated_matching")
							{
								foreach ($v2->children() as $k3 => $v3)
								{
									$object_children[$k2][$k3] = (string)$v3;
								}
							}
							else
							{
								$object_children[$k2] = (string)$v2;
							}
						}
						if ($user_id != 0)
						{
							$response["object"][$user_id] = $object_children;//."_".$object_id
						}
						else
						{
							$response["object"] = $object_children;
						}
						break;

					case "students":
					case "instructors":
						$object_children = array();
						foreach ($v->children() as $k2 => $v2)
						{
							$object_children[] = (int)$v2->userid;
						}
						$response[$k] = $object_children;
						break;

					default:
						$response[$k] = (string)$v;
						break;

				}
			}
		}
		$response["status"] = "Fail";
		if (strlen($response["rcode"]) <= 2 && $response["rcode"] != 0) {
			$response["status"] = "Success";
		}
		return $response;
	}

	function getGMT() {
		$gmt = gmdate("YmdHi");
		$gmt = substr($gmt, 0, strlen($gmt)-1);
		return $gmt;
	}

	function getIdSync($fid, $fcmd, $create_session = 0) {
		$idsync = 0;
		if ((($fid == 1) || (($fid == 2 || $fid == 4 || $fid == 5 || $fid == 6) && ($fcmd == 1 || $fcmd == 2))) && $create_session == 0) {
			$idsync = 1;
		}
		return $idsync;
	}

	function arrayToQueryString ($tii_vars) {
		// Put the posted variables into a query string
		global $tii_variables_not_to_post;
		$query_string = "?";
		foreach ($tii_vars as $k => $v) {
			if (!in_array($k, $this->tii_variables_not_to_post)) {
				$query_string .= $k."=".urlencode($v)."&";
			}
		}
		$query_string = substr($query_string, 0, strlen($query_string)-1);
		return $query_string;
	}

	function generateMd5($tii_vars) {
		global $tii_variables_not_to_post;
		$not_in_md5_string = array_merge(
								$this->tii_variables_not_to_post,
								array("src", "new_teacher_email", "session-id", "idsync", "pdata", "starttime", "score", "max_points", "attached_file",
										"allPapers", "export_data", "ainst", "late_accept_flag", "submit_papers_to", "report_gen_speed", "s_view_report",
										"exclude_biblio", "exclude_quoted", "exclude_type", "exclude_value", "anon", "s_paper_check", "internet_check",
										"journal_check", "institution_check", "create_session", "erater", "erater_handbook", "erater_spelling_dictionary",
										"erater_spelling", "erater_grammar", "erater_usage", "erater_mechanics", "erater_style", "anon_reason",
										"translated_matching"
								)
							);
		ksort($tii_vars);
		$md5_string = "";
		foreach ($tii_vars as $k => $v) {
			if (!in_array($k, $not_in_md5_string)) {
				$md5_string .= $v;
			}
		}
		$md5_string .= $tii_vars["shared_key"];
		return md5($md5_string);
	}
}
?>