<?php

function getResources($conn){
	$sql = "SELECT * FROM csm2.resources ORDER BY FIELD(resource_status,'Available','Not Available','To Be Removed'),resource_name,resource_lname,resource_fname";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getAvailableResources($conn){
	$sql = "SELECT * FROM csm2.resources WHERE resource_status='Available' ORDER BY resource_name desc,resource_lname desc,resource_fname desc";
	$stmt= $conn ->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getSpecificResource($conn,$resourceid){
	$sql = "SELECT * FROM csm2.resources WHERE resource_id =:resource_id LIMIT 1";
	$stmt=$conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$result = cleanForHtml($result);
	return $result;
}

function getQualifiedResources($conn,$postArray,$results){
	$mustHaves = array();
	$pluses = array();
	$plusesSatisfied=array();
	$finalList = array();
	if(ISSET($postArray['wantedSkill'])){
		foreach($postArray['wantedSkill'] as $key=>$value){
			if($value!=""){
				if($postArray['conditional'][$key]=="Must Have")$mustHaves[] = $value;
				else $pluses[] = $value;
			}
		}
		foreach($mustHaves as $musthave){
			if(ISSET($postArray['is_pskill']) &&
			in_array($musthave,$postArray['is_pskill']))$results = resWithoutPSFiltered($conn,$musthave,$results);
			else $results = resourcesSearchFilter($conn,$musthave,$results);
		}
		$resultSize = count($results);
		for($i=0;$i<$resultSize;$i++){
			$plusesSatisfied[] = 0;
			foreach($pluses as $aPlus){
				if(ISSET($postArray['is_pskill']) &&
				in_array($aPlus,$postArray['is_pskill'])){
				if(hasPrimarySkill($conn,$aPlus,$results[$i]['resource_id']))$plusesSatisfied[$i] = $plusesSatisfied[$i]+1;
				}//else $filteredResults  =  resourcesSearchFilter($conn,$aPlus,$results[$i]);
				//if(in_array($results[$i],$filteredResults))$plusesSatisfied[$i] = $plusesSatisfied[$i]+1;
				else if(searchResource($conn,$aPlus,$results[$i]))$plusesSatisfied[$i] = $plusesSatisfied[$i]+1;
			}
		}
		print_r($plusesSatisfied);
		arsort($plusesSatisfied);
		foreach($plusesSatisfied as $key=>$value){
			$finalList[] = $results[$key];
		}
	}
	return $finalList;
}



function primarySkillsList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.requestable_skills ORDER BY rs_name";
	else $sql = "SELECT rs.rs_id,rs.rs_name,rps.resource_id FROM csm2.requestable_skills rs left join csm2.res_ps rps ON rs.rs_id = rps.pskill_id && rps.resource_id =:resource_id ORDER BY rs.rs_name";
	
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getPrimarySkills($conn,$resourceid){
	$sql = "SELECT rs.rs_id,rs.rs_name,rps.resource_id FROM csm2.requestable_skills rs join csm2.res_ps rps ON rs.rs_id = rps.pskill_id && rps.resource_id =:resource_id ORDER BY rs.rs_name";
	
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}
function hasPrimarySkill($conn,$pSkill,$resourceid){
	$sql = "SELECT * FROM csm2.res_ps rps, csm2.requestable_skills rs
	WHERE rps.pskill_id = rs.rs_id && rs.rs_name = :pSkill
	&& resource_id=:resource_id";
	$stmt = $conn->prepare($sql);
	$stmt ->bindParam(':pSkill',$pSkill,PDO::PARAM_STR);
	$stmt ->bindParam(':resource_id',$resourceid,PDO::PARAM_STR);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!empty($result))return true;
	else return false;
}


function allResWithPrimarySkill($conn,$pSkill){
	$sql = "SELECT * FROM csm2.res_ps rps, csm2.requestable_skills rs
	WHERE rps.pskill_id = rs.rs_id && rs.rs_name = :pSkill";
	$stmt = $conn->prepare($sql);
	$stmt ->bindParam(':pSkill',$pSkill,PDO::PARAM_STR);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}

function resWithoutPSFiltered($conn,$pSkill,$resources){
	$psOwners = allResWithPrimarySkill($conn,$pSkill);
	if(empty($psOwners))return null;
	$psOwnersIDs = oneColumnDBResult($psOwners,'resource_id');
	$filteredResults = array();
	foreach($resources as $resource){
		if(in_array($resource['resource_id'],$psOwnersIDs))$filteredResults[] = $resource;
	}
	return $filteredResults;
}


function applicationCodesList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.application_codes ORDER BY ac_name";
	else $sql = "SELECT ac.ac_id,ac.ac_name,rac.resource_id FROM csm2.application_codes ac left join csm2.res_ac rac ON ac.ac_id = rac.ac_id && rac.resource_id =:resource_id ORDER BY ac.ac_name";
	
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getApplicationCodes($conn,$resourceid){
	$sql = "SELECT ac.ac_id,ac.ac_name,rac.resource_id FROM csm2.application_codes ac join csm2.res_ac rac ON ac.ac_id = rac.ac_id && rac.resource_id =:resource_id ORDER BY ac.ac_name";
	
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function databaseTypesList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.database_types ORDER BY dt_name";
	else $sql = "SELECT dt.dt_id,dt.dt_name,rdt.resource_id FROM csm2.database_types dt left join csm2.res_dt rdt ON dt.dt_id = rdt.dt_id && rdt.resource_id =:resource_id ORDER BY dt.dt_name";
	
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
	
}

function getDatabaseTypes($conn,$resourceid){
	$sql = "SELECT dt.dt_id,dt.dt_name,rdt.resource_id FROM csm2.database_types dt join csm2.res_dt rdt ON dt.dt_id = rdt.dt_id && rdt.resource_id=:resource_id ORDER BY dt.dt_name";

	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function hardwaresList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.hardwares ORDER BY hardware_name";
	else $sql = "SELECT hw.hardware_id,hw.hardware_name,rhw.resource_id FROM csm2.hardwares hw left join csm2.res_hws rhw ON hw.hardware_id = rhw.hardware_id && rhw.resource_id =:resource_id ORDER BY hw.hardware_name";
	
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}

function getHardwares($conn,$resourceid){
	$sql = "SELECT hw.hardware_id,hw.hardware_name,rhw.resource_id FROM csm2.hardwares hw join csm2.res_hws rhw ON hw.hardware_id = rhw.hardware_id && rhw.resource_id =:resource_id ORDER BY hw.hardware_name";

	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function iBMProductsList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.ibm_products ORDER BY ibmp_name";
	else $sql = "SELECT ibmp.ibmp_id,ibmp.ibmp_name,ribmp.resource_id FROM csm2.ibm_products ibmp left join csm2.res_ibmp ribmp ON ibmp.ibmp_id = ribmp.ibmp_id && ribmp.resource_id =:resource_id ORDER BY ibmp.ibmp_name";
	
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getIBMProducts($conn,$resourceid){
	$sql = "SELECT ibmp.ibmp_id,ibmp.ibmp_name,ribmp.resource_id FROM csm2.ibm_products ibmp join csm2.res_ibmp ribmp ON ibmp.ibmp_id = ribmp.ibmp_id && ribmp.resource_id =:resource_id ORDER BY ibmp.ibmp_name";

	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function iSVProductsList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.isv_products ORDER BY isvp_name";
	else $sql = "SELECT isvp.isvp_id,isvp.isvp_name,risvp.resource_id FROM csm2.isv_products isvp left join csm2.res_isvp risvp ON isvp.isvp_id = risvp.isvp_id && risvp.resource_id =:resource_id ORDER BY isvp.isvp_name";
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getISVProducts($conn,$resourceid){
	$sql = "SELECT isvp.isvp_id,isvp.isvp_name,risvp.resource_id FROM csm2.isv_products isvp join csm2.res_isvp risvp ON isvp.isvp_id = risvp.isvp_id && risvp.resource_id =:resource_id ORDER BY isvp.isvp_name";

	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function oSPlatformsList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.os_platforms ORDER BY platform_name";
	else $sql = "SELECT osp.platform_id,osp.platform_name,rosp.resource_id FROM csm2.os_platforms osp left join csm2.res_osp rosp ON osp.platform_id = rosp.platform_id && rosp.resource_id =:resource_id ORDER BY osp.platform_name";
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getOSPlatforms($conn,$resourceid){
	$sql = "SELECT osp.platform_id,osp.platform_name,rosp.resource_id FROM csm2.os_platforms osp join csm2.res_osp rosp ON osp.platform_id = rosp.platform_id && rosp.resource_id =:resource_id ORDER BY osp.platform_name";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function otherSkillsList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.other_skills ORDER BY skill_name";
	else $sql = "SELECT skills.skill_id,skills.skill_name,rs.resource_id FROM csm2.other_skills skills left join csm2.res_skills rs ON skills.skill_id = rs.skill_id && rs.resource_id =:resource_id ORDER BY skills.skill_name";
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getOtherSkills($conn,$resourceid){
	$sql = "SELECT skills.skill_id,skills.skill_name,rs.resource_id FROM csm2.other_skills skills join csm2.res_skills rs ON skills.skill_id = rs.skill_id && rs.resource_id =:resource_id ORDER BY skills.skill_name";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function titlesList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.titles ORDER BY title";
	else $sql = "SELECT tls.title_id,tls.title,rt.resource_id FROM csm2.titles tls left join csm2.res_titles rt ON tls.title_id = rt.title_id && rt.resource_id =:resource_id ORDER BY tls.title";
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getTitles($conn,$resourceid){
	$sql = "SELECT tls.title_id,tls.title,rt.resource_id FROM csm2.titles tls join csm2.res_titles rt ON tls.title_id = rt.title_id && rt.resource_id =:resource_id ORDER BY tls.title";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function transactionsList($conn,$resourceid){
	if($resourceid==null)$sql = "SELECT * FROM csm2.transactions ORDER BY transaction_name";
	else $sql = "SELECT txns.transaction_id,txns.transaction_name,rtxn.resource_id FROM csm2.transactions txns left join csm2.res_txns rtxn ON txns.transaction_id = rtxn.transaction_id && rtxn.resource_id =:resource_id ORDER BY txns.transaction_name";
	$stmt = $conn->prepare($sql);
	if($resourceid)$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getTransactions($conn,$resourceid){
	$sql = "SELECT txns.transaction_id,txns.transaction_name,rtxn.resource_id FROM csm2.transactions txns join csm2.res_txns rtxn ON txns.transaction_id = rtxn.transaction_id && rtxn.resource_id =:resource_id ORDER BY txns.transaction_name";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid,PDO::FETCH_ASSOC);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}



function addResource($conn,$postArray){
	$sql = "INSERT INTO csm2.resources (resource_name,resource_fname,resource_lname,type,email,phone1,phone2,resource_address,resource_notes,resource_created_by) values
	(:resource_name,:resource_fname,:resource_lname,:type,:email,:phone1,:phone2,:resource_address,:resource_notes,:adder)";
	$stmt= $conn->prepare($sql);
	$stmt->bindParam(':resource_name',$postArray['resource_name'],PDO::PARAM_STR);
	$stmt->bindParam(':resource_fname',$postArray['resource_fname'],PDO::PARAM_STR);
	$stmt->bindParam(':resource_lname',$postArray['resource_lname'],PDO::PARAM_STR);
	$stmt->bindParam(':type',$postArray['type'],PDO::PARAM_STR);
	$stmt->bindParam(':email',$postArray['email'],PDO::PARAM_STR);
	$stmt->bindParam(':phone1',$postArray['phone1'],PDO::PARAM_STR);
	$stmt->bindParam(':phone2',$postArray['phone2'],PDO::PARAM_STR);
	$stmt->bindParam(':resource_address',$postArray['resource_address'],PDO::PARAM_STR);
	$stmt->bindParam(':resource_notes',$postArray['resource_notes'],PDO::PARAM_STR);
	$stmt->bindParam(':adder',$_SESSION['userid'],PDO::PARAM_INT);
	
	$stmt->execute();
	$newResId = $conn->lastInsertId();
	
	$resourceName = $postArray['resource_fname'];
	if($postArray['resource_name'])$resourceName = $postArray['resource_name'].'_'.$resourceName;
	if($postArray['resource_lname'])$resourceName = $resourceName.'-'.$postArray['resource_lname'];
	
	$resumeFilename = $newResId."-".fileNameFormatting($resourceName,"resume");
	$resumeISSIFilename = $newResId."-".fileNameFormatting($resourceName,"resume_issi");
	$successOne = uploadFile(null,$resumeFilename,"resume","resume_uploads");
	$successTwo = uploadFile(null,$resumeISSIFilename,"resume_issi","resume_issi_uploads");
	
	if($successOne){
			$currentDate = customTimestamp();
			$sql = "UPDATE csm2.resources SET resume_upload=:filename,resume_uploaded_date=:upload_time WHERE resource_id = :resource_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':filename',$resumeFilename,PDO::PARAM_STR);
			$stmt->bindParam(':upload_time',$currentDate,PDO::PARAM_STR);
			$stmt->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
			$stmt->execute();
	}
	if($successTwo){
			$currentDate = customTimestamp();
			$sql = "UPDATE csm2.resources SET resume_issi_upload=:filename, resume_issi_uploaded_date=:upload_time WHERE resource_id =:resource_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':filename',$resumeISSIFilename,PDO::PARAM_STR);
			$stmt->bindParam(':upload_time',$currentDate,PDO::PARAM_STR);
			$stmt->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
			$stmt->execute();
	}
	
	$rSkills = getRequestableSkills($conn);
	$sqlReq = "INSERT INTO csm2.requestable_skills (rs_name) values(:value)";
	$stmtReq = $conn->prepare($sqlReq);
	$stmtReq->bindParam(':value',$value,PDO::PARAM_STR);
	
	if(ISSET($postArray['primary_skills'])){
		//Possible New Relations
		$relationsToAdd = $postArray['primary_skills'];
		$sqlRelate = "INSERT INTO csm2.res_ps (pskill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_primary_skills'])){
		$sqlRelate = "INSERT INTO csm2.res_ps (pskill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
	}
	
	if(ISSET($postArray['new_primary_skills'])){
		
		foreach($postArray['new_primary_skills'] as $value){
			if($postArray['new_primary_skills']!==""){
				if(!searchTwoDArray($value,$rSkills)){
					$stmtReq->execute();							
					$newEntry = $conn->lastInsertId();
					$value = $newEntry;
					
				} 
				else{
					$rSkillsTable = getRequestableSkills($conn);
					foreach($rSkillsTable as $rSkillRow){
						if(strcasecmp($rSkillRow['rs_name'],$postArray['new_primary_skills'])){
							$value = $rSkillRow['rs_id'];	
							break;
						}
					}
				}
				$stmtRelate->execute();
			}
		}
	}	
	
	if(ISSET($postArray['os_platforms'])){
		//Possible New Relations
		$relationsToAdd = $postArray['os_platforms'];
		$sqlRelate = "INSERT INTO csm2.res_osp (platform_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_os_platforms'])){
		
		$sqlNewSkill = "INSERT INTO csm2.os_platforms (platform_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_osp (platform_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_os_platforms'] as $value){
			if($postArray['new_os_platforms']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	

	if(ISSET($postArray['database_types'])){
		//Possible New Relations
		$relationsToAdd = $postArray['database_types'];
		$sqlRelate = "INSERT INTO csm2.res_dt (dt_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_database_types'])){
		
		$sqlNewSkill = "INSERT INTO csm2.database_types (dt_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_dt (dt_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_database_types'] as $value){
			if($postArray['new_database_types']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}

	if(ISSET($postArray['transactions'])){
		//Possible New Relations
		$relationsToAdd = $postArray['transactions'];
		$sqlRelate = "INSERT INTO csm2.res_txns (transaction_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_transactions'])){
		
		$sqlNewSkill = "INSERT INTO csm2.transactions (transaction_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_txns (transaction_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_transactions'] as $value){
			if($postArray['new_transactions']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	

	if(ISSET($postArray['application_codes'])){
		//Possible New Relations
		$relationsToAdd = $postArray['application_codes'];
		$sqlRelate = "INSERT INTO csm2.res_ac (ac_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_application_codes'])){
		
		$sqlNewSkill = "INSERT INTO csm2.application_codes (ac_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_ac (ac_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_application_codes'] as $value){
			if($postArray['new_application_codes']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['isv_products'])){
		//Possible New Relations
		$relationsToAdd = $postArray['isv_products'];
		$sqlRelate = "INSERT INTO csm2.res_isvp (isvp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_isv_products'])){
		
		$sqlNewSkill = "INSERT INTO csm2.isv_products (isvp_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_isvp (isvp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_isv_products'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_isv_products']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['ibm_products'])){
		//Possible New Relations
		$relationsToAdd = $postArray['ibm_products'];
		$sqlRelate = "INSERT INTO csm2.res_ibmp (ibmp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_ibm_products'])){
		
		$sqlNewSkill = "INSERT INTO csm2.ibm_products (ibmp_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_ibmp (ibmp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_ibm_products'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_ibm_products']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['hardwares'])){
		//Possible New Relations
		$relationsToAdd = $postArray['hardwares'];
		$sqlRelate = "INSERT INTO csm2.res_hws (hardware_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_hardwares'])){
		
		$sqlNewSkill = "INSERT INTO csm2.hardwares (hardware_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_hws (hardware_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_hardwares'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_hardwares']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['titles'])){
		//Possible New Relations
		$relationsToAdd = $postArray['titles'];
		$sqlRelate = "INSERT INTO csm2.res_titles (title_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_titles'])){
		
		$sqlNewSkill = "INSERT INTO csm2.titles (title) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_titles (title_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_titles'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_titles']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['other_skills'])){
		//Possible New Relations
		$relationsToAdd = $postArray['other_skills'];
		$sqlRelate = "INSERT INTO csm2.res_skills (skill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_other_skills'])){
		
		$sqlNewSkill = "INSERT INTO csm2.other_skills (skill_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_skills (skill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$newResId,PDO::PARAM_INT);
		foreach($postArray['new_other_skills'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_other_skills']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' added ';
	if($postArray['resource_name'])$activityDesc= $activityDesc.$postArray['resource_name'].' ';
	$activityDesc= $activityDesc.$postArray['resource_fname'].' ';
	if($postArray['resource_lname'])$activityDesc= $activityDesc.$postArray['resource_lname'];
	$currentDate = customTimestamp();

	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	
	ob_clean();//clean buffer
	header('Location:./index.php?page=Resources&action=viewResource&resourceid='.$newResId);

}


function editResource($conn,$postArray){
	$sqlRes = "UPDATE csm2.resources SET resource_name=:resource_name,resource_fname=:resource_fname
	,resource_lname=:resource_lname,email=:email,phone1=:phone1,phone2=:phone2,resource_address=:resource_address
	,resource_notes=:resource_notes,resource_status=:resource_status,resume_upload=:resume_upload,
	resume_uploaded_date=:resume_uploaded_date,resume_issi_upload=:resume_issi_upload,resume_issi_uploaded_date=:resume_issi_uploaded_date,
	resource_last_modified_by=:modifier
	WHERE resource_id =:resource_id";
	$stmtRes = $conn->prepare($sqlRes);
	$stmtRes->bindParam(':resource_name',$postArray["resource_name"],PDO::PARAM_STR);
	$stmtRes->bindParam(':resource_fname',$postArray["resource_fname"],PDO::PARAM_STR);
	$stmtRes->bindParam(':resource_lname',$postArray["resource_lname"],PDO::PARAM_STR);
	$stmtRes->bindParam(':email',$postArray["email"],PDO::PARAM_STR);
	$stmtRes->bindParam(':phone1',$postArray["phone1"],PDO::PARAM_STR);
	$stmtRes->bindParam(':phone2',$postArray["phone2"],PDO::PARAM_STR);
	$stmtRes->bindParam(':resource_address',$postArray["resource_address"],PDO::PARAM_STR);
	$stmtRes->bindParam(':resource_notes',$postArray["resource_notes"],PDO::PARAM_STR);
	$stmtRes->bindParam(':resource_status',$postArray["resource_status"],PDO::PARAM_STR);
	$stmtRes->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
	$stmtRes->bindParam(':resume_upload',$resume_uploaded_filename,PDO::PARAM_STR);
	$stmtRes->bindParam(':resume_uploaded_date',$resume_uploaded_date,PDO::PARAM_STR);
	$stmtRes->bindParam(':resume_issi_upload',$resume_issi_uploaded_filename,PDO::PARAM_STR);		
	$stmtRes->bindParam(':resume_issi_uploaded_date',$resume_issi_uploaded_date,PDO::PARAM_STR);
	$stmtRes->bindParam(':modifier',$_SESSION["userid"],PDO::PARAM_INT);
	
	if(ISSET($postArray['resume_uploaded_date']))$resume_uploaded_date = $postArray['resume_uploaded_date'];
	else $resume_uploaded_date ="";
	
	if(ISSET($postArray['resume_issi_uploaded_date']))$resume_issi_uploaded_date = $postArray['resume_issi_uploaded_date'];
	else $resume_issi_uploaded_date ="";
	
	if(ISSET($postArray['old_resume_name']))$resume_uploaded_filename = $postArray['old_resume_name'];
	else $resume_uploaded_filename = "";
	if(ISSET($postArray['old_resume_issi_name']))$resume_issi_uploaded_filename = $postArray['old_resume_issi_name'];
	else $resume_issi_uploaded_filename = "";
	

	if(ISSET($postArray['remove_resume'])){
		removeFile('resume_uploads',$postArray['old_resume_name']);
		$resume_upload_filename = "";
		$resume_uploaded_date = "";
	}
	
	if(ISSET($postArray['remove_resume_issi'])){
		removeFile('resume_issi_uploads',$postArray['old_resume_issi_name']);
		$resume_issi_upload_filename = "";
		$resume_issi_uploaded_date = "";
	}
	
	
	$resourceName = $postArray['resource_fname'];
	if($postArray['resource_name'])$resourceName = $postArray['resource_name'].'_'.$resourceName;
	if($postArray['resource_lname'])$resourceName = $resourceName.'-'.$postArray['resource_lname'];
	
	$resumeFilename = $postArray['resource_id']."-".fileNameFormatting($resourceName,"resume");
	$successOne = uploadFile($resume_uploaded_filename,$resumeFilename,"resume","resume_uploads");
	if($successOne){
		$resume_uploaded_date = customTimestamp();
		$resume_uploaded_filename = $resumeFilename;
	}
	
	$resumeISSIFilename = $postArray['resource_id']."-".fileNameFormatting($resourceName,"resume_issi");
	$successTwo = uploadFile($resume_issi_uploaded_filename,$resumeISSIFilename,"resume_issi","resume_issi_uploads");
	if($successTwo){
		$resume_issi_uploaded_date = customTimestamp();
		$resume_issi_uploaded_filename = $resumeISSIFilename;
	}
	

	//Finally updated Resource
	$stmtRes->execute();
	
	$rSkills = getRequestableSkills($conn);	
	$sqlReq = "INSERT INTO csm2.requestable_skills (rs_name) values(:value)";
	$stmtReq = $conn->prepare($sqlReq);
	$stmtReq->bindParam(':value',$value,PDO::PARAM_STR);
	
	
	if(ISSET($postArray['primary_skills'])){
		//Possible New Relations
		$relationsToAdd = $postArray['primary_skills'];
		$sqlRelate = "INSERT INTO csm2.res_ps (pskill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray['resource_id'],PDO::PARAM_INT);
		
		if(!empty($postArray['pskillsBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$pskillsBefore = explode(",",$postArray['pskillsBefore']);
			$relationsToAdd = array_diff($postArray['primary_skills'],$pskillsBefore);
			$currentRelations = implode(",",$postArray['primary_skills']);

			$sqlDelete= "DELETE FROM csm2.res_ps WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (pskill_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
		
	}
	else if(!empty($postArray['pskillsBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_ps WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	
	//Completely New Skill
	if(ISSET($postArray['new_primary_skills'])){
		$sqlRelate = "INSERT INTO csm2.res_ps (pskill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray['resource_id'],PDO::PARAM_INT);
		
	}
	
	
	$sqlRelate = "INSERT INTO csm2.res_ps (pskill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray['resource_id'],PDO::PARAM_INT);
	
	if(ISSET($postArray['new_primary_skills'])){
	foreach($postArray['new_primary_skills'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_primary_skills']!==""){
				if(!searchTwoDArray($value,$rSkills)){
					$stmtReq->execute();							
					$newEntry = $conn->lastInsertId();
					echo '<br/>newEntry: '.$newEntry;
					$value = $newEntry;
					
				} 
				else{
					$rSkillsTable = getRequestableSkills($conn);
					foreach($rSkillsTable as $rSkillRow){
							print_r($rSkillsTable);
							echo '<br/>';
							if(strcasecmp($rSkillRow['rs_name'],$value)){
							echo $rSkillRow['rs_name'].''.$rSkillRow['rs_id'].'<br/>';
							$value = $rSkillRow['rs_id'];	
							echo'found<br/>';
							echo 'value: '.$value.'<br/>';
							break;
						}
						else echo"not found";
					}
				}
				echo 'make relation <br/>';
				echo 'value: is '.$value;
				$stmtRelate->execute();
			}
		}
	}
	
	
	if(ISSET($postArray['os_platforms'])){
		//Possible New Relations
		$relationsToAdd = $postArray['os_platforms'];
		$sqlRelate = "INSERT INTO csm2.res_osp (platform_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['platformsBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$platformsBefore = explode(",",$postArray['platformsBefore']);
			$relationsToAdd = array_diff($postArray['os_platforms'],$platformsBefore);
			$currentRelations = implode(",",$postArray['os_platforms']);

			$sqlDelete= "DELETE FROM csm2.res_osp WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (platform_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['platformsBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_osp WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_os_platforms'])){
		
		$sqlNewSkill = "INSERT INTO csm2.os_platforms (platform_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_osp (platform_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_os_platforms'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_os_platforms']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['database_types'])){
		//Possible New Relations
		$relationsToAdd = $postArray['database_types'];
		$sqlRelate = "INSERT INTO csm2.res_dt (dt_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['dbtypesBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$dbtypesBefore = explode(",",$postArray['dbtypesBefore']);
			$relationsToAdd = array_diff($postArray['database_types'],$dbtypesBefore);
			$currentRelations = implode(",",$postArray['database_types']);

			$sqlDelete= "DELETE FROM csm2.res_dt WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (dt_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['dbtypesBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_dt WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_database_types'])){
		
		$sqlNewSkill = "INSERT INTO csm2.database_types (dt_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_dt (dt_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_database_types'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_database_types']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}

	if(ISSET($postArray['transactions'])){
		//Possible New Relations
		$relationsToAdd = $postArray['transactions'];
		$sqlRelate = "INSERT INTO csm2.res_txns (transaction_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['txnsBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$txnsBefore = explode(",",$postArray['txnsBefore']);
			$relationsToAdd = array_diff($postArray['transactions'],$txnsBefore);
			$currentRelations = implode(",",$postArray['transactions']);

			$sqlDelete= "DELETE FROM csm2.res_txns WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (transaction_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['txnsBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_txns WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_transactions'])){
		
		$sqlNewSkill = "INSERT INTO csm2.transactions (transaction_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_txns (transaction_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_transactions'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_transactions']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['application_codes'])){
		//Possible New Relations
		$relationsToAdd = $postArray['application_codes'];
		$sqlRelate = "INSERT INTO csm2.res_ac (ac_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['acBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$acBefore = explode(",",$postArray['acBefore']);
			$relationsToAdd = array_diff($postArray['application_codes'],$acBefore);
			$currentRelations = implode(",",$postArray['application_codes']);

			$sqlDelete= "DELETE FROM csm2.res_ac WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (ac_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['acBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_ac WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_application_codes'])){
		
		$sqlNewSkill = "INSERT INTO csm2.application_codes (ac_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_ac (ac_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_application_codes'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_application_codes']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}

	if(ISSET($postArray['isv_products'])){
		//Possible New Relations
		$relationsToAdd = $postArray['isv_products'];
		$sqlRelate = "INSERT INTO csm2.res_isvp (isvp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['isvpBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$isvpBefore = explode(",",$postArray['isvpBefore']);
			$relationsToAdd = array_diff($postArray['isv_products'],$isvpBefore);
			$currentRelations = implode(",",$postArray['isv_products']);

			$sqlDelete= "DELETE FROM csm2.res_isvp WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (isvp_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['isvpBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_isvp WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_isv_products'])){
		
		$sqlNewSkill = "INSERT INTO csm2.isv_products (isvp_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_isvp (isvp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_isv_products'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_isv_products']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['ibm_products'])){
		//Possible New Relations
		$relationsToAdd = $postArray['ibm_products'];
		$sqlRelate = "INSERT INTO csm2.res_ibmp (ibmp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['ibmpBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$ibmpBefore = explode(",",$postArray['ibmpBefore']);
			$relationsToAdd = array_diff($postArray['ibm_products'],$ibmpBefore);
			$currentRelations = implode(",",$postArray['ibm_products']);

			$sqlDelete= "DELETE FROM csm2.res_ibmp WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (ibmp_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['ibmpBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_ibmp WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_ibm_products'])){
		
		$sqlNewSkill = "INSERT INTO csm2.ibm_products (ibmp_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_ibmp (ibmp_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_ibm_products'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_ibm_products']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	if(ISSET($postArray['hardwares'])){
		//Possible New Relations
		$relationsToAdd = $postArray['hardwares'];
		$sqlRelate = "INSERT INTO csm2.res_hws (hardware_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['hardwaresBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$hardwaresBefore = explode(",",$postArray['hardwaresBefore']);
			$relationsToAdd = array_diff($postArray['hardwares'],$hardwaresBefore);
			$currentRelations = implode(",",$postArray['hardwares']);

			$sqlDelete= "DELETE FROM csm2.res_hws WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (hardware_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['hardwaresBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_hws WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_hardwares'])){
		
		$sqlNewSkill = "INSERT INTO csm2.hardwares (hardware_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_hws (hardware_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_hardwares'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_hardwares']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}

	if(ISSET($postArray['titles'])){
		//Possible New Relations
		$relationsToAdd = $postArray['titles'];
		$sqlRelate = "INSERT INTO csm2.res_titles (title_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['titlesBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$titlesBefore = explode(",",$postArray['titlesBefore']);
			$relationsToAdd = array_diff($postArray['titles'],$titlesBefore);
			$currentRelations = implode(",",$postArray['titles']);

			$sqlDelete= "DELETE FROM csm2.res_titles WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (title_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['titlesBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_titles WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_titles'])){
		
		$sqlNewSkill = "INSERT INTO csm2.titles (title) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_titles (title_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_titles'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_titles']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}

	if(ISSET($postArray['other_skills'])){
		//Possible New Relations
		$relationsToAdd = $postArray['other_skills'];
		$sqlRelate = "INSERT INTO csm2.res_skills (skill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		if(!empty($postArray['skillsBefore'])){
			//Previous Relations Exist. Delete What No Longer Exists
			$skillsBefore = explode(",",$postArray['skillsBefore']);
			$relationsToAdd = array_diff($postArray['other_skills'],$skillsBefore);
			$currentRelations = implode(",",$postArray['other_skills']);

			$sqlDelete= "DELETE FROM csm2.res_skills WHERE
			resource_id =:resource_id && NOT FIND_IN_SET (skill_id,:currentRelations)";
			$stmtDelete = $conn->prepare($sqlDelete);
			$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
			$stmtDelete->bindParam(':currentRelations',$currentRelations,PDO::PARAM_STR);
			$stmtDelete->execute();
			
			
		}
		//Add The New Relations
		foreach($relationsToAdd as $value){
			$stmtRelate->execute();
		}
	}
	else if(!empty($postArray['skillsBefore'])){
		//No Longer Has Relations. Old Ones Must Be Deleted
		$sqlDelete= "DELETE FROM csm2.res_skills WHERE resource_id =:resource_id";
		$stmtDelete = $conn->prepare($sqlDelete);
		$stmtDelete->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		$stmtDelete->execute();
	}
	//Completely New Skill
	if(ISSET($postArray['new_other_skills'])){
		
		$sqlNewSkill = "INSERT INTO csm2.other_skills (skill_name) values (:value)";
		$stmtNewSkill = $conn->prepare($sqlNewSkill);
		$stmtNewSkill->bindParam(':value',$value,PDO::PARAM_STR);

		$sqlRelate = "INSERT INTO csm2.res_skills (skill_id,resource_id) values (:value,:resource_id)";
		$stmtRelate = $conn->prepare($sqlRelate);
		$stmtRelate->bindParam(':value',$value,PDO::PARAM_STR);
		$stmtRelate->bindParam(':resource_id',$postArray["resource_id"],PDO::PARAM_INT);
		foreach($postArray['new_other_skills'] as $value){
			echo '<br/>value: '.$value;
			if($postArray['new_other_skills']!==""){
				if(!searchTwoDArray($value,$rSkills))$stmtReq->execute();							
				$stmtNewSkill->execute();
				$newEntry = $conn->lastInsertId();
				echo '<br/>newEntry: '.$newEntry;
				$value = $newEntry;
				$stmtRelate->execute();
			}
		}
	}
	
	
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' updated ';
	if($postArray['resource_name'])$activityDesc= $activityDesc.$postArray['resource_name'].' ';
	$activityDesc= $activityDesc.$postArray['resource_fname'].' ';
	if($postArray['resource_lname'])$activityDesc= $activityDesc.$postArray['resource_lname'];
	$currentDate = customTimestamp();
	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	
	ob_clean();//clean buffer
	header('Location:./index.php?page=Resources&action=viewResource&resourceid='.$postArray['resource_id']);
	
}

function deleteResource($conn,$resourceid){
	$sql = "DELETE FROM csm2.resources WHERE resource_id =:resource_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resource_id',$resourceid);
	$stmt->execute();
	
	ob_clean();
	header('Location:./index.php?page=Resources');
}

function resourcesSearchFilter($conn,$keyphrase,$resources){
	if(!$keyphrase)return $resources;
	else{ 
		
		$filteredResults = array();
		foreach($resources as $resource){
			$matchFound = searchResource($conn,$keyphrase,$resource);
			if($matchFound)$filteredResults[] = $resource;
		}
		return $filteredResults;
	}
}	
	
function searchResource($conn,$keyphrase,$resource){
	$matchFound = searchArray($keyphrase,$resource);
			if(!$matchFound){
					$pskills = getPrimarySkills($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$pskills);
					if($matchFound)return $matchFound;
					$dbtypes = getDatabaseTypes($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$dbtypes);
					if($matchFound)return $matchFound;
					$platforms = getOSPlatforms($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$platforms);
					if($matchFound)return $matchFound;
					$skills = getOtherSkills($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$skills);
					if($matchFound)return $matchFound;
					$ac= getApplicationCodes($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$ac);
					if($matchFound)return $matchFound;
					$hardwares = getHardwares($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$hardwares);
					if($matchFound)return $matchFound;
					$ibmp = getIBMProducts($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$ibmp);
					if($matchFound)return $matchFound;
					$isvp = getISVProducts($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$isvp);
					if($matchFound)return $matchFound;
					$titles = getTitles($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$titles);
					if($matchFound)return $matchFound;
					$txns = getTransactions($conn,$resource['resource_id']);
					$matchFound = searchTwoDArray($keyphrase,$txns);
			}
			return $matchFound;
}

function asOnResources($conn,$postArray,$results){
	$mustHaves = array();
	$pluses = array();
	$plusesSatisfied=array();
	$finalList = array();
	foreach($postArray['keyphrase'] as $key=>$value){
		if($value!=""){
			if($postArray['conditional'][$key]=="Must Have")$mustHaves[] = $value;
			else $pluses[] = $value;
		}
	}
	foreach($mustHaves as $musthave){
		$results = resourcesSearchFilter($conn,$musthave,$results);
	}
	$resultSize = count($results);
	for($i=0;$i<$resultSize;$i++){
		$plusesSatisfied[] = 0;
		foreach($pluses as $aPlus){
			$filteredResults  =  resourcesSearchFilter($conn,$aPlus,$results[$i]);
			if(in_array($results[$i],$filteredResults))$plusesSatisfied[$i] = $plusesSatisfied[$i]+1;
		}
	}
	arsort($plusesSatisfied);
	foreach($plusesSatisfied as $key=>$value){
		$finalList[] = $results[$key];
	}
	return $finalList;
}


?>