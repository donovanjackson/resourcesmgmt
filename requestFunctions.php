<?php
function getRequests($conn){
	$sql = "SELECT * FROM csm2.requests left join csm2.clients using(client_id) ORDER BY FIELD(request_status,'Open','Closed','To Be Removed'),request_name,client_name";
	$stmt = $conn->prepare($sql);
	$stmt->execute();	
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;

}

function getOpenRequests($conn){
	$sql = "SELECT * FROM csm2.requests left join csm2.clients using(client_id) WHERE request_status='Open' ORDER BY request_name,client_name";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;

}


function getSpecificRequest($conn,$requestid){
	$sql="SELECT * FROM csm2.requests WHERE request_id=:request_id  LIMIT 1";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':request_id',$requestid,PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$result = cleanForHtml($result);
	return $result;
}

function getSpecificDetailedRequest($conn,$requestid){
	//specific request joined with client info
	$sql="SELECT * FROM csm2.requests left join csm2.clients using(client_id) WHERE request_id=:request_id  LIMIT 1";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':request_id',$requestid,PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$request = $result;
	return $request;
}


function getRequestableSkills($conn){
	$sql = "SELECT * FROM csm2.requestable_skills ORDER BY rs_name";
	$stmt = $conn->query($sql);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function namesOfResumesSent($conn,$resumesSentList){
	
	$sql = "SELECT * FROM csm2.resources WHERE FIND_IN_SET(resource_id,:resumesSentList)";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':resumesSentList',$resumesSentList,PDO::PARAM_STR);
	$stmt->execute();
	$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!empty($resources)){
		$namesOfResources = array();
		foreach($resources as $resource){
			$resName="";
			if($resource['resource_name'])$resName= $resource['resource_name'].': ';
			$resName = $resName.$resource['resource_fname'];
			if($resource['resource_lname'])$resName  = $resName.' '.$resource['resource_lname'];
			$namesOfResources[] = $resName;
		}
		return 	$namesOfResources;
	}
	else return null;
}


function getSkillsRequested($conn,$rsIdList){ 
	$sql = "SELECT * FROM csm2.requestable_skills WHERE FIND_IN_SET(rs_id,:rsIdList) ORDER BY rs_name";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':rsIdList',$rsIdList,PDO::PARAM_STR);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function skillListAndRequested($conn,$requestid){ 
	$sql = "SELECT rs.rs_id,rs.rs_name,sreq.request_id FROM csm2.requestable_skills rs left join csm2.skills_requested sreq ON rs.rs_id = sreq.rs_id && sreq.request_id =:request_id ORDER BY rs.rs_name";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':request_id',$requestid,PDO::PARAM_INT);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function addRequest($conn,$postArray){
	$sql = "INSERT INTO csm2.requests(request_name,request_desc, client_id,type,budget,length,
	location,requested_skills,request_created_by,resumes_sent) values
	(:request_name,:request_desc,:client_id,:type,:budget,:length,:location,
	:requested_skills,:adder,:resumes)";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':request_name',$postArray['request_name'],PDO::PARAM_STR);
	$stmt->bindParam(':request_desc',$postArray['request_desc'],PDO::PARAM_STR);
	$stmt->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
	$stmt->bindParam(':type',$postArray['type'],PDO::PARAM_STR);
	$stmt->bindParam(':budget',$postArray['budget'],PDO::PARAM_STR);
	$stmt->bindParam(':length',$postArray['length'],PDO::PARAM_STR);
	$stmt->bindParam(':location',$postArray['location'],PDO::PARAM_STR);
	$stmt->bindParam(':requested_skills',$requestedSkills,PDO::PARAM_STR);
	$stmt->bindParam(':adder',$_SESSION['userid'],PDO::PARAM_INT);
	$stmt->bindParam(':resumes',$rsString,PDO::PARAM_STR);
	
	$requestedSkills = array();
	
	$rsString = "";
	if(ISSET($postArray['resumes_sent']))$rsString = implode(",",$postArray['resumes_sent']);
	$sqlRS = "INSERT INTO csm2.requestable_skills (rs_name) values (:value)";
	
	$stmtRS = $conn->prepare($sqlRS);
	
	$stmtRS->bindParam(':value',$value,PDO::PARAM_STR);
	
	if(ISSET($postArray['rs_id'])){
		foreach($postArray['rs_id'] as $key=>$value){
			if($value !="" && is_numeric($value))$requestedSkills[] = $value;
		}
	}
	if(ISSET($postArray['new_rs'])){
		foreach($postArray['new_rs'] as $key=>$value){
			$stmtRS->execute();
			$newEntry = $conn->lastInsertId();
			echo $newEntry;
			$requestedSkills[] = $newEntry;
		}
	}
	$requestedSkills = implode(',',$requestedSkills);
	echo $requestedSkills;
	$stmt->execute();
	$requestid = $conn -> lastInsertId();
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' added '.$postArray['request_name'];
	$currentDate = customTimestamp();
	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	
	ob_clean();
	header('Location:./index.php?page=Requests&action=viewRequest&requestid='.$requestid);
}//when is it ok to represent array as string in db.

function editRequest($conn,$postArray){
	$sql = "UPDATE csm2.requests SET request_name=:request_name, request_desc=:request_desc,client_id=:client_id,
	type=:type, budget=:budget, length=:length, location=:location,requested_skills=:requested_skills,
	request_status=:request_status,request_last_modified_by=:modifier,resumes_sent=:resumes
	WHERE request_id =:request_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':request_name',$postArray['request_name'],PDO::PARAM_STR);
	$stmt->bindParam(':request_desc',$postArray['request_desc'],PDO::PARAM_STR);
	$stmt->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
	$stmt->bindParam(':type',$postArray['type'],PDO::PARAM_STR);
	$stmt->bindParam(':budget',$postArray['budget'],PDO::PARAM_STR);
	$stmt->bindParam(':length',$postArray['length'],PDO::PARAM_STR);
	$stmt->bindParam(':location',$postArray['location'],PDO::PARAM_STR);
	$stmt->bindParam(':requested_skills',$requestedSkills,PDO::PARAM_STR);
	$stmt->bindParam(':request_status',$postArray['request_status'],PDO::PARAM_STR);
	$stmt->bindParam(':request_id',$postArray['request_id'],PDO::PARAM_INT);
	$stmt->bindParam(':modifier',$_SESSION['userid'],PDO::PARAM_INT);
	$stmt->bindParam(':resumes',$rsString,PDO::PARAM_STR);
	
	$requestedSkills = array();
	
	$rsString = "";
	if(ISSET($postArray['resumes_sent']))$rsString = implode(",",$postArray['resumes_sent']);
	
	$sqlRS = "INSERT INTO csm2.requestable_skills (rs_name) values (:value)";
	
	$stmtRS = $conn->prepare($sqlRS);
	
	$stmtRS->bindParam(':value',$value,PDO::PARAM_STR);
	
	if(ISSET($postArray['rs_id'])){
		foreach($postArray['rs_id'] as $key=>$value){
			if($value !="" && is_numeric($value))$requestedSkills[] = $value;
		}
	}
	if(ISSET($postArray['new_rs'])){
		foreach($postArray['new_rs'] as $key=>$value){
			$stmtRS->execute();
			$newEntry = $conn->lastInsertId();
			echo $newEntry;
			$requestedSkills[] = $newEntry;
		}
	}
	
	$requestedSkills = implode(',',$requestedSkills);
	echo $requestedSkills;
	$stmt->execute();
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' updated '.$postArray['request_name'];
	$currentDate = customTimestamp();
	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	
	/*ob_clean();
	header('Location:./index.php?page=Requests&action=viewRequest&requestid='.$postArray['request_id']);
*/}

function deleteRequest($conn,$requestid){
	$sql = "DELETE FROM csm2.requests WHERE request_id =:request_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':request_id',$requestid);
	$stmt->execute();
	
	ob_clean();
	header('Location:./index.php?page=Requests');
}

function requestsSearchFilter($conn,$keyphrase,$requests){
	if(!$keyphrase)return $requests;
	else{
		$filteredResults = array();
		foreach($requests as $request){
			$matchFound = searchArray($keyphrase,$request);
			if($matchFound)$filteredResults[] = $request;
			else {
				$requestedSkills = getSkillsRequested($conn,$request['requested_skills']);
				if(!empty($requestedSkills))$matchFound = searchTwoDArray($keyphrase,$requestedSkills);
				if($matchFound)$filteredResults[] = $request;
			}
			
		}
		return $filteredResults;
	}
}

function asOnRequests($conn,$postArray,$results){
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
		$results = requestsSearchFilter($conn,$musthave,$results);
	}
	$resultSize = count($results);
	for($i=0;$i<$resultSize;$i++){
		$plusesSatisfied[] = 0;
		foreach($pluses as $aPlus){
			$filteredResults  =  requestsSearchFilter($conn,$aPlus,$results[$i]);
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