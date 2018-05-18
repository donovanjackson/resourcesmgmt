<?php
function getClients($conn){
	$sql = "SELECT * FROM csm2.clients ORDER BY FIELD(client_status,'Active','Inactive','To Be Removed'),client_name";
	$stmt = $conn -> prepare($sql);
	$stmt ->execute();
	$results = $stmt ->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getSpecificClient($conn,$clientid){
	$sql = "SELECT * FROM csm2.clients WHERE client_id = :client_id LIMIT 1";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$result = $stmt -> fetch(PDO::FETCH_ASSOC);
	$result = cleanForHtml($result);
	return $result;
}

function getContacts($conn,$clientid){
	$sql = "SELECT * FROM csm2.contacts WHERE client_id = :client_id";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getSalesReps($conn){
	$sql = "SELECT * FROM csm2.sales_representatives ORDER BY sr_name";
	$stmt = $conn -> prepare($sql);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function createNewSR($conn,$salesRep){
	$sql = "INSERT INTO csm2.sales_representatives(sr_name) values (:sales_rep)";
					$stmt = $conn->prepare($sql);
					$stmt->bindParam(':sales_rep',$salesRep,PDO::PARAM_STR);
					$stmt->execute();
					$newSR = $conn->lastInsertId();
					return $newSR;
}

function getClientsRepsArray($conn,$salesRepsString){
echo $salesRepsString;
	$sql ="SELECT * FROM csm2.sales_representatives WHERE FIND_IN_SET(sr_id,:salesRepsString)";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':salesRepsString',$salesRepsString,PDO::PARAM_STR);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}
function getClientsSRsAsArray($conn,$salesRepsString){
	$salesReps = getSalesReps($conn);
	$srArray = explode(",",$salesRepsString);
	$srNames = array();
	foreach($srArray as $srNumber){
		foreach($salesReps as $sr){
			if($sr['sr_id']==$srNumber){
				$srNames[] = $sr['sr_name'];
				break;
			}
		}
 	}
	return $srNames;
}

function getClientsSRsAsString($conn,$salesRepsString){
	$salesReps = getSalesReps($conn);
	$srArray = explode(",",$salesRepsString);
	$srNames = array();
	foreach($srArray as $srNumber){
		foreach($salesReps as $sr){
			if($sr['sr_id']==$srNumber){
				$srNames[] = $sr['sr_name'];
				break;
			}
		}
	}
	if(count($srNames)>1)$srNames[count($srNames)-1] = "and ".end($srNames);
	
	return implode(",",$srNames);
}



function getClientsSows($conn,$clientid){
	$sql = "SELECT * FROM csm2.sows WHERE client_id=:client_id ORDER BY sow_name";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getClientsActiveSows($conn,$clientid){
	$sql = "SELECT * FROM csm2.sows WHERE client_id=:client_id && sow_status='Active' 
	ORDER BY sow_name";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getHardwareSales($conn,$clientid){
	$sql = "SELECT * FROM csm2.hardware_sales WHERE client_id=:client_id ORDER BY hs_selling_date,sold_hardware_name";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getSoftwareSales($conn,$clientid){
	$sql = "SELECT * FROM csm2.software_sales WHERE client_id=:client_id ORDER BY ss_selling_date,sold_software_name";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getClientsRequests($conn,$clientid){
	$sql =  "SELECT * FROM csm2.requests WHERE client_id=:client_id ORDER BY request_name";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getClientsOpenRequests($conn,$clientid){
	$sql = "SELECT * FROM csm2.requests WHERE client_id=:client_id && request_status= 'Open' ORDER BY request_name";
	$stmt = $conn -> prepare($sql);
	$stmt->bindParam(':client_id',$clientid,PDO::PARAM_INT);
	$stmt -> execute();
	$results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}





function addClient($conn,$postArray){
	$salesReps = array();
	if(ISSET($postArray['sales_rep']))$salesReps = $postArray['sales_rep'];
	if(ISSET($postArray['new_sales_rep'])){
	$sqlSRep = "INSERT INTO csm2.sales_representatives (sr_name) value (:sr_name)";
		$stmtSRep = $conn->prepare($sqlSRep);
		$stmtSRep -> bindParam(':sr_name',$srName,PDO::PARAM_STR);
		foreach($postArray['new_sales_rep'] as $srName){
			if($srName != ""){
				$stmtSRep->execute();
				$newSRepId = $conn->lastInsertId();
				$salesReps[] = $newSRepId;
			}
		}
	}
	if(!empty($salesReps))$salesReps = implode(',',$salesReps);
	else $salesReps = "";
	
	$sql = "INSERT INTO csm2.clients (client_name,main_phone,sales_rep,client_address,client_notes,client_created_by) values
	(:client_name,:main_phone,:salesReps,:client_address,:client_notes,:adder)";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':client_name',$_POST['client_name'],PDO::PARAM_STR);
	$stmt->bindParam(':main_phone',$_POST['main_phone'],PDO::PARAM_STR);
	$stmt->bindParam(':salesReps',$salesReps,PDO::PARAM_STR);
	$stmt->bindParam(':client_address',$_POST['client_address'],PDO::PARAM_STR);
	$stmt->bindParam(':client_notes',$_POST['client_notes'],PDO::PARAM_STR);
	$stmt->bindParam(':adder',$_SESSION['userid'],PDO::PARAM_INT);
	$stmt->execute();
	$lastId = $conn->lastInsertId();
	
	
	
	$sql = "INSERT INTO csm2.contacts (contact_name,office_phone,cell_phone,home_phone,fax,email,
			secretary_name,secretary_phone,contact_notes,client_id) values
			(:contact_name,:office_phone,:cell_phone,:home_phone,:fax
			,:email,:secretary_name,:secretary_phone,:contact_notes,:client_id)";
			
			$stmt = $conn->prepare($sql);
			$stmt->bindParam(':contact_name',$contact_name,PDO::PARAM_STR);
			$stmt->bindParam(':office_phone',$office_phone,PDO::PARAM_STR);
			$stmt->bindParam(':cell_phone',$cell_phone,PDO::PARAM_STR);
			$stmt->bindParam(':home_phone',$home_phone,PDO::PARAM_STR);
			$stmt->bindParam(':fax',$fax,PDO::PARAM_STR);
			$stmt->bindParam(':email',$email,PDO::PARAM_STR);
			$stmt->bindParam(':secretary_name',$secretary_name,PDO::PARAM_STR);
			$stmt->bindParam(':secretary_phone',$secretary_phone,PDO::PARAM_STR);
			$stmt->bindParam(':contact_notes',$contact_notes,PDO::PARAM_STR);
			$stmt->bindParam(':client_id',$lastId,PDO::PARAM_INT);
			
			
	
	foreach($postArray['contact_name'] as $key=>$value){
	if(trim($value)!=""){
			$contact_name = $value;
			$office_phone = $postArray["office_phone"][$key];
			$cell_phone = $postArray["cell_phone"][$key];
			$home_phone = $postArray["home_phone"][$key];
			$fax = $postArray["fax"][$key];
			$email = $postArray["email"][$key];
			$secretary_name = $postArray["secretary_name"][$key];
			$secretary_phone = $postArray["secretary_phone"][$key];
			$contact_notes = $postArray["contact_notes"][$key];
			$stmt->execute();
		}		
	}
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' added '.$postArray['client_name'];
	$currentDate = customTimestamp();
	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
	values(:activityDesc,:currentDate)";
	
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
	$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
	$stmt->execute();
	
	ob_clean();//clean buffer
	header('Location:./index.php?page=Clients');
	
	
}

function editClient($conn,$postArray){
	
	$salesReps = array();
	if(ISSET($postArray['sales_rep']))$salesReps = $postArray['sales_rep'];
	if(ISSET($postArray['new_sales_rep'])){
	$sqlSRep = "INSERT INTO csm2.sales_representatives (sr_name) value (:sr_name)";
		$stmtSRep = $conn->prepare($sqlSRep);
		$stmtSRep -> bindParam(':sr_name',$srName,PDO::PARAM_STR);
		foreach($postArray['new_sales_rep'] as $srName){
			if($srName != ""){
				$stmtSRep->execute();
				$newSRepId = $conn->lastInsertId();
				$salesReps[] = $newSRepId;
			}
		}
	}
	if(!empty($salesReps))$salesReps = implode(',',$salesReps);
	else $salesReps = "";
	
	
	$sql = "UPDATE csm2.clients SET client_name=:client_name,main_phone=:main_phone,sales_rep=:salesReps,
	client_address=:client_address,client_notes=:client_notes,client_status=:client_status,client_last_modified_by=:modifier
	WHERE client_id=:client_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':client_name',$postArray['client_name'],PDO::PARAM_STR);
	$stmt->bindParam(':main_phone',$postArray['main_phone'],PDO::PARAM_STR);
	$stmt->bindParam(':salesReps',$salesReps,PDO::PARAM_STR);
	$stmt->bindParam(':client_address',$postArray['client_address'],PDO::PARAM_STR);
	$stmt->bindParam(':client_notes',$postArray['client_notes'],PDO::PARAM_STR);
	$stmt->bindParam(':client_status',$postArray['client_status'],PDO::PARAM_STR);
	$stmt->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
	$stmt->bindParam(':modifier',$_SESSION['userid'],PDO::PARAM_INT);
	$stmt->execute();
	
	$sql="DELETE FROM csm2.contacts WHERE contact_id=:contact_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':contact_id',$contactId,PDO::PARAM_INT);
	$contactsDeleted = explode(',',$postArray['contacts_deleted']);
	foreach($contactsDeleted as $contactId){
		if($contactId)$stmt->execute();
	}
	
	
	$sqlInsert = "INSERT INTO csm2.contacts (contact_name,office_phone,cell_phone,home_phone,fax,email,
				secretary_name,secretary_phone,contact_notes,client_id) values
				(:contact_name,:office_phone,:cell_phone,:home_phone,:fax
				,:email,:secretary_name,:secretary_phone,:contact_notes,:client_id)";
	$sqlUpdate = "UPDATE csm2.contacts SET contact_name=:contact_name,office_phone=:office_phone
				,cell_phone=:cell_phone,home_phone=:home_phone,fax=:fax
				,email=:email,secretary_name=:secretary_name,secretary_phone=:secretary_phone
				,contact_notes=:contact_notes,client_id=:client_id 
				WHERE contact_id=:contact_id";
	
	$stmtInsert = $conn->prepare($sqlInsert);
		$stmtInsert->bindParam(':contact_name',$contact_name,PDO::PARAM_STR);
			$stmtInsert->bindParam(':office_phone',$office_phone,PDO::PARAM_STR);
			$stmtInsert->bindParam(':cell_phone',$cell_phone,PDO::PARAM_STR);
			$stmtInsert->bindParam(':home_phone',$home_phone,PDO::PARAM_STR);
			$stmtInsert->bindParam(':fax',$fax,PDO::PARAM_STR);
			$stmtInsert->bindParam(':email',$email,PDO::PARAM_STR);
			$stmtInsert->bindParam(':secretary_name',$secretary_name,PDO::PARAM_STR);
			$stmtInsert->bindParam(':secretary_phone',$secretary_phone,PDO::PARAM_STR);
			$stmtInsert->bindParam(':contact_notes',$contact_notes,PDO::PARAM_STR);
			$stmtInsert->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
	$stmtUpdate = $conn->prepare($sqlUpdate);
		$stmtUpdate->bindParam(':contact_name',$contact_name,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':office_phone',$office_phone,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':cell_phone',$cell_phone,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':home_phone',$home_phone,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':fax',$fax,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':email',$email,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':secretary_name',$secretary_name,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':secretary_phone',$secretary_phone,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':contact_notes',$contact_notes,PDO::PARAM_STR);
			$stmtUpdate->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
			$stmtUpdate->bindParam(':contact_id',$contact_id,PDO::PARAM_INT);
	foreach($postArray['contact_id'] as $key=>$value){
		if($value===""){
			
			$contact_name = $postArray["contact_name"][$key];
			$office_phone = $postArray["office_phone"][$key];
			$cell_phone = $postArray["cell_phone"][$key];
			$home_phone = $postArray["home_phone"][$key];
			$fax = $postArray["fax"][$key];
			$email = $postArray["email"][$key];
			$secretary_name = $postArray["secretary_name"][$key];
			$secretary_phone = $postArray["secretary_phone"][$key];
			$contact_notes = $postArray["contact_notes"][$key];
			$stmtInsert->execute();
		}
		else{
			$contact_name = $postArray["contact_name"][$key];
			$office_phone = $postArray["office_phone"][$key];
			$cell_phone = $postArray["cell_phone"][$key];
			$home_phone = $postArray["home_phone"][$key];
			$fax = $postArray["fax"][$key];
			$email = $postArray["email"][$key];
			$secretary_name = $postArray["secretary_name"][$key];
			$secretary_phone = $postArray["secretary_phone"][$key];
			$contact_notes = $postArray["contact_notes"][$key];
			$contact_id = $value;
			$stmtUpdate->execute();
		}
	}
	
	$sqlDelete = "DELETE FROM csm2.hardware_sales WHERE hardware_sale_id NOT IN (:hsIdArray)";
	$stmtDelete = $conn->prepare($sqlDelete);
	$stmtDelete->bindParam(':hsIdArray',$hsIdArray,PDO::PARAM_STR);
	if(ISSET($postArray['hardware_sale_id']))$hsIdArray = implode($postArray['hardware_sale_id']);
	else $hsIdArray = "-1";
	$stmtDelete->execute();
	
	$sqlInsert = "INSERT INTO csm2.hardware_sales (sold_hardware_name,hs_buying_contact
			,hs_bc_phone,hs_selling_date,client_id) values
			(:hardware_name,:hs_buying_contact,:hs_bc_phone
			,:hs_selling_date,:client_id)";
	$sqlUpdate = "UPDATE csm2.hardware_sales SET sold_hardware_name=:hardware_name,hs_buying_contact=:hs_buying_contact,
			hs_bc_phone=:hs_bc_phone, hs_selling_date=:hs_selling_date,client_id=:client_id
			WHERE hardware_sale_id=:hardware_sale_id";
	
	$stmtInsert = $conn->prepare($sqlInsert);
		$stmtInsert->bindParam(':hardware_name',$hardware_name,PDO::PARAM_STR);
		$stmtInsert->bindParam(':hs_buying_contact',$hs_buying_contact,PDO::PARAM_STR);
		$stmtInsert->bindParam(':hs_bc_phone',$hs_bc_phone,PDO::PARAM_STR);
		$stmtInsert->bindParam(':hs_selling_date',$hs_selling_date,PDO::PARAM_STR);
		$stmtInsert->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);

	$stmtUpdate = $conn->prepare($sqlUpdate);
		$stmtUpdate->bindParam(':hardware_name',$hardware_name,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':hs_buying_contact',$hs_buying_contact,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':hs_bc_phone',$hs_bc_phone,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':hs_selling_date',$hs_selling_date,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':client_id',$postArray['client_id'],PDO::PARAM_INT);
		$stmtUpdate->bindParam(':hardware_sale_id',$hardware_sale_id,PDO::PARAM_INT);

	if(ISSET($postArray['hardware_sale_id'])){
		foreach($postArray['hardware_sale_id'] as $key=>$value){
			if($value===""){
				$hardware_name = $postArray["sold_hardware_name"][$key];
				$hs_buying_contact = $postArray["hs_buying_contact"][$key];
				$hs_bc_phone = $postArray["hs_bc_phone"][$key];
				$hs_selling_date = $postArray["hs_selling_date"][$key];
				$stmtInsert->execute();
			}
			else{
				$hardware_name = $postArray["sold_hardware_name"][$key];
				$hs_buying_contact = $postArray["hs_buying_contact"][$key];
				$hs_bc_phone = $postArray["hs_bc_phone"][$key];
				$hs_selling_date = $postArray["hs_selling_date"][$key];
				$hardware_sale_id = $value;
				$stmtUpdate->execute();
			}
		}
	}
	
	$sqlDelete = "DELETE FROM csm2.software_sales WHERE software_sale_id NOT IN (:ssIdArray)";
	$stmtDelete = $conn->prepare($sqlDelete);
	$stmtDelete->bindParam(':ssIdArray',$ssIdArray,PDO::PARAM_STR);
	if(ISSET($postArray['software_sale_id']))$ssIdArray = implode($postArray['software_sale_id']);
	else $ssIdArray = "-1";
	$stmtDelete->execute();
	
	$sqlInsert = "INSERT INTO csm2.software_sales (sold_software_name,ss_buying_contact
			,ss_bc_phone,ss_selling_date,client_id) values
			(:software_name,:ss_buying_contact,:ss_bc_phone
			,:ss_selling_date,:client_id)";
	$sqlUpdate = "UPDATE csm2.software_sales SET sold_software_name=:software_name,ss_buying_contact=:ss_buying_contact,
			ss_bc_phone=:ss_bc_phone,ss_selling_date=:ss_selling_date,client_id=:client_id 
			WHERE software_sale_id=:software_sale_id";
	
	$stmtInsert = $conn->prepare($sqlInsert);
		$stmtInsert->bindParam(':software_name',$software_name,PDO::PARAM_STR);
		$stmtInsert->bindParam(':ss_buying_contact',$ss_buying_contact,PDO::PARAM_STR);
		$stmtInsert->bindParam(':ss_bc_phone',$ss_bc_phone,PDO::PARAM_STR);
		$stmtInsert->bindParam(':ss_selling_date',$ss_selling_date,PDO::PARAM_STR);
		$stmtInsert->bindParam(':client_id',$_POST['client_id'],PDO::PARAM_INT);

	$stmtUpdate = $conn->prepare($sqlUpdate);
		$stmtUpdate->bindParam(':software_name',$software_name,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':ss_buying_contact',$ss_buying_contact,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':ss_bc_phone',$ss_bc_phone,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':ss_selling_date',$ss_selling_date,PDO::PARAM_STR);
		$stmtUpdate->bindParam(':client_id',$_POST['client_id'],PDO::PARAM_INT);
		$stmtUpdate->bindParam(':software_sale_id',$software_sale_id,PDO::PARAM_INT);

	if(ISSET($postArray['software_sale_id'])){
		foreach($postArray['software_sale_id'] as $key=>$value){
			if($value===""){
				
				$software_name = $postArray["sold_software_name"][$key];
				$ss_buying_contact = $postArray["ss_buying_contact"][$key];
				$ss_bc_phone = $postArray["ss_bc_phone"][$key];
				$ss_selling_date = $postArray["ss_selling_date"][$key];
				$stmtInsert->execute();
			}
			else{
				
				$software_name = $postArray["sold_software_name"][$key];
				$ss_buying_contact = $postArray["ss_buying_contact"][$key];
				$ss_bc_phone = $postArray["ss_bc_phone"][$key];
				$ss_selling_date = $postArray["ss_selling_date"][$key];
				$software_sale_id = $value;
				$stmtUpdate->execute();
			}
		}
	}
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' updated '.$postArray['client_name'];
	$currentDate = customTimestamp();
	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	ob_clean();//clean buffer
	header('Location:./index.php?page=Clients');

}	

function deleteClient($conn,$clientid){
	$sql = "DELETE FROM csm2.clients WHERE client_id =:client_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':client_id',$clientid);
	$stmt->execute();
	
	ob_clean();
	header('Location:./index.php?page=Clients');
}
	
function clientsSearchFilter($conn,$keyphrase,$clients){
	if(!$keyphrase)return $clients;
	else{
		$filteredResults = array();
		foreach($clients as $client){
			$matchFound = searchClient($conn,$keyphrase,$client);
			if($matchFound)$filteredResults[] = $client;
		}
		return $filteredResults;
	}
}

function searchClient($conn,$keyphrase,$client){
	$matchFound = searchArray($keyphrase,$client);
	if(!$matchFound){
		$contacts = getContacts($conn,$client['client_id']);
		$matchFound = searchTwoDArray($keyphrase,$contacts);
		if($matchFound)return $matchFound;
		$salesReps = getClientsSRsAsArray($conn,$client['sales_rep']);
		$matchFound = searchArray($keyphrase,$salesReps);
		if($matchFound)return $matchFound;
		$cSows = getClientsSows($conn,$client['client_id']);
		$matchFound = searchTwoDArray($keyphrase,$cSows);
		if($matchFound)return $matchFound;
		$cHSs= getHardwareSales($conn,$client['client_id']);
		$matchFound = searchTwoDArray($keyphrase,$cHSs);
		if($matchFound)return $matchFound;
		$cSSs= getSoftwareSales($conn,$client['client_id']);
		$matchFound = searchTwoDArray($keyphrase,$cSSs);
		if($matchFound)return $matchFound;
		$cReqs= getClientsRequests($conn,$client['client_id']);
		$matchFound = searchTwoDArray($keyphrase,$cReqs);
	}
	return $matchFound;
}	
	
function asOnClients($conn,$postArray,$results){
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
		$results = clientsSearchFilter($conn,$musthave,$results);
	}
	$resultSize = count($results);
	for($i=0;$i<$resultSize;$i++){
		$plusesSatisfied[] = 0;
		foreach($pluses as $aPlus){
			$filteredResults  =  clientsSearchFilter($conn,$aPlus,$filteredResults);
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