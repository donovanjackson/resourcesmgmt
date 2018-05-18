<?php
//be sure to save 2 copies of sow. 1 for access by website. 1 to put on record(in a folder) and cant be overwritten
function getSows($conn){
	$sql = "SELECT * FROM csm2.sows left join csm2.clients using(client_id) ORDER BY FIELD(sow_status,'Active','Expired','Completed','Canceled','Replaced','To Be Removed'),sow_name";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getActiveSows($conn){
	$sql = "SELECT * FROM csm2.sows left join csm2.clients using(client_id) WHERE sow_status='Active' ORDER BY sow_name";
	$stmt=$conn->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getSpecificSow($conn,$sowid){
	$sql = "SELECT * FROM csm2.sows left JOIN csm2.clients using (client_id) WHERE sow_id =:sow_id LIMIT 1";
	$stmt=$conn->prepare($sql);
	$stmt->bindParam(':sow_id',$sowid,PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$result = cleanForHtml($result);
	return $result;
}



function addSow($conn,$postArray){
		$sql = "INSERT INTO csm2.sows (sow_name,client_id,primary_contact,subcontractor_disguise,start_date,end_date,summary,billing,sow_created_by) values
		(:sow_name,:client_id,:primary_contact,:subcontractor_disguise,:start_date,:end_date,:summary,:billing,:adder)";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':sow_name',$postArray['sow_name'],PDO::PARAM_STR);
		$stmt->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
		$stmt->bindParam(':primary_contact',$postArray['primary_contact'],PDO::PARAM_STR);
		$stmt->bindParam(':subcontractor_disguise',$postArray['subcontractor_disguise'],PDO::PARAM_STR);
		$stmt->bindParam(':start_date',$postArray['start_date'],PDO::PARAM_STR);
		$stmt->bindParam(':end_date',$postArray['end_date'],PDO::PARAM_STR);
		$stmt->bindParam(':summary',$postArray['summary'],PDO::PARAM_STR);
		$stmt->bindParam(':billing',$postArray['billing'],PDO::PARAM_STR);
		$stmt->bindParam(':adder',$_SESSION['userid'],PDO::PARAM_INT);
		$stmt->execute();
		$lastId = $conn->lastInsertId();
		
		$sowFilename = $lastId."-".fileNameFormatting($postArray['sow_name'],"sow");
		$success = uploadFile(null,$sowFilename,"sow","sow_uploads");
		if($success){
			$dateUploaded = date('Y-m-d H:i:s');
			$sql = "UPDATE csm2.sows SET sow_upload=:filename, date_uploaded=:date_uploaded WHERE sow_id =:sow_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':filename',$sowFilename,PDO::PARAM_STR);
			$stmt->bindParam(':date_uploaded',$dateUploaded,PDO::PARAM_INT);
			$stmt->bindParam(':sow_id',$lastId,PDO::PARAM_INT);
			$stmt->execute();
		}
		
		$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' added '.$postArray['user_name'];
		$currentDate = customTimestamp();
		$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	
		ob_clean();//clean buffer
		header('Location:./index.php?page=Sows');
	}
	
function editSow($conn,$postArray){
	$sql = "UPDATE csm2.sows SET sow_name=:sow_name,client_id=:client_id
	,primary_contact=:primary_contact,subcontractor_disguise=:subcontractor_disguise,start_date=:start_date
	,end_date=:end_date,summary=:summary,billing=:billing,sow_status=:sow_status,sow_last_modified_by=:modifier
	WHERE sow_id =:sow_id";
	$stmt = $conn->prepare($sql);
		$stmt->bindParam(':sow_name',$postArray['sow_name'],PDO::PARAM_STR);
		$stmt->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
		$stmt->bindParam(':primary_contact',$postArray['primary_contact'],PDO::PARAM_STR);
		$stmt->bindParam(':subcontractor_disguise',$postArray['subcontractor_disguise'],PDO::PARAM_STR);
		$stmt->bindParam(':start_date',$postArray['start_date'],PDO::PARAM_STR);
		$stmt->bindParam(':end_date',$postArray['end_date'],PDO::PARAM_STR);
		$stmt->bindParam(':summary',$postArray['summary'],PDO::PARAM_STR);
		$stmt->bindParam(':billing',$postArray['billing'],PDO::PARAM_STR);
		$stmt->bindParam(':sow_status',$postArray['sow_status'],PDO::PARAM_STR);
		$stmt->bindParam(':sow_id',$postArray['sow_id'],PDO::PARAM_INT);
		$stmt->bindParam(':modifier',$_SESSION['userid'],PDO::PARAM_INT);
		$stmt->execute();
		$lastId = $conn->lastInsertId();
	
	$prevName = $postArray['prev_name'];
	$sowFilename = $postArray['sow_id']."-".fileNameFormatting($postArray['sow_name'],"sow");
	$success = uploadFile($prevName,$sowFilename,"sow","sow_uploads");
	
	
	if($success){
			$dateUploaded = date('Y-m-d H:i:s');
			$sql = "UPDATE csm2.sows SET sow_upload=:filename, date_uploaded=:date_uploaded WHERE sow_id =:sow_id";
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':filename',$sowFilename,PDO::PARAM_STR);
			$stmt->bindParam(':date_uploaded',$dateUploaded,PDO::PARAM_INT);
			$stmt->bindParam(':sow_id',$postArray['sow_id'],PDO::PARAM_INT);
			$stmt->execute();
	}
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' updated '.$postArray['sow_name'];
	$currentDate = customTimestamp();
	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
		
	ob_clean();//clean buffer
	header('Location:./index.php?page=Sows');
}

function deleteSow($conn,$sowid){
	$sql = "DELETE FROM csm2.sows WHERE sow_id =:sow_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':sow_id',$sowid);
	$stmt->execute();

	ob_clean();
	header('Location:./index.php?page=Sows');
}
	
function hasExpired($conn,$sowid){
		$sql = "UPDATE csm2.sows SET status='expired'WHERE sow_id=:sow_id";
		$stmt= $conn->prepare($sql);
		$stmt->bindParam(':sow_id',$sowid,PDO::PARAM_INT);
		$stmt->execute();
}	

function sowsSearchFilter($conn,$keyphrase,$sows){
	if(!$keyphrase)return $sows;
	else{
		$filteredResults = array();
		foreach($sows as $sow){
			$matchFound = searchArray($keyphrase,$sow);
			if($matchFound)$filteredResults[] = $sow;
		}
		return $filteredResults;
	}
}

function asOnSows($conn,$postArray,$results){
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
		$results = sowsSearchFilter($conn,$musthave,$results);
	}
	$resultSize = count($results);
	for($i=0;$i<$resultSize;$i++){
		$plusesSatisfied[] = 0;
		foreach($pluses as $aPlus){
			$filteredResults  =  sowsSearchFilter($conn,$aPlus,$filteredResults);
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