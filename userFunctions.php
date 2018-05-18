<?php

function getUsers($conn){
	$sql = "SELECT * FROM csm2.users ORDER BY FIELD(user_status,'Active','Deactivated','To Be Removed'),user_fname,user_lname";
	$stmt = $conn->query($sql);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	return $results;
}

function getUsernames($conn){
	$sql = "SELECT username FROM csm2.users";
	$stmt = $conn->query($sql);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$results = cleanForHtml($results);
	$usernames = array();
	foreach($results as $username){
		$usernames[] = $username['username'];
	}
	return $usernames;
}

function  getCreator($conn,$creatorid){
	$sql = "SELECT user_fname, user_lname FROM csm2.users WHERE user_id = :user_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':user_id',$creatorid,PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$result = cleanForHtml($result);
	return $result;
}


function getSpecificUser($conn,$userid){
	$sql = "SELECT * FROM csm2.users WHERE user_id =:user_id LIMIT 1";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':user_id',$userid,PDO::PARAM_INT);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$result = cleanForHtml($result);
	return $result;
}


function changePassword($conn,$postArray){
	$user = getSpecificUser($conn,$_SESSION['userid']);
	if(checkPass($postArray['old_pass'],$user)){
		$salt = makeSalt();
		$encrypted = encryptPass($postArray['new_pass'],$salt);
		$sql = "UPDATE csm2.users SET password=:password , salt=:salt
		WHERE user_id=:user_id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':password',$encrypted,PDO::PARAM_STR);
		$stmt->bindParam(':salt',$salt,PDO::PARAM_STR);
		$stmt->bindParam(':user_id',$_SESSION['userid'],PDO::PARAM_INT);
		$stmt->execute();
		
		ob_clean();
		header('Location:http:./index.php');
	}
	else{
		$_SESSION['wrongPass'] = "Password Is Not Correct";
		ob_clean();
		header('Location:http:./index.php?action=changePass');
	}
}

function makeRandomPassword(){
	$randomPass ="";		
		$randos = "abcdefghijklmnopqrstuvwxyz01234567890";
		for($i=0;$i<8;$i++) {
			$randomPass .= $randos[mt_rand(0,strlen($randos)-1)];
		}
	return $randmPass;	
}


function makeSalt(){
	$salt = "";
		$randos = "abcdefghijklmnopqrstuvwxyz01234567890";
		for($i=0;$i<40;$i++) {
			$salt .= $randos[mt_rand(0,strlen($randos)-1)];
		}
	return $salt;	

}

function encryptPass($password,$salt){
	$encrypted = $password.$salt;
	$encrypted = sha1($encrypted);
	return $encrypted;
}	
	
function addUser($conn,$postArray){
		$success = 0;
		//$pass = makeRandomPassword();//to be used after smtp server setup
		$pass = strtolower(substr($_POST['user_fname'],0,1)).strtolower($_POST['user_lname']).'1nfinity';
		$salt = makeSalt();
		$encrypted = encryptPass($pass,$salt);
		$dateCreated = customTimestamp();
		$creator = $_SESSION['userid'];
		$sql = "INSERT INTO csm2.users (username,password,user_fname,user_lname,role,salt,email,phone,date_created,created_by,last_modified_by) values
		(:username,'$encrypted',:user_fname,:user_lname,:role,'$salt',:email,:phone,'$dateCreated','$creator','$creator')";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':username',$postArray['username'],PDO::PARAM_STR);
		$stmt->bindParam(':user_fname',$postArray['user_fname'],PDO::PARAM_STR);
		$stmt->bindParam(':user_lname',$postArray['user_lname'],PDO::PARAM_STR);
		$stmt->bindParam(':role',$postArray['role'],PDO::PARAM_STR);
		$stmt->bindParam(':email',$postArray['email'],PDO::PARAM_STR);
		$stmt->bindParam(':phone',$postArray['phone'],PDO::PARAM_STR);
		$stmt->execute();
		
		
		$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' added '.$postArray['user_fname'].' '.$postArray['user_lname'];
		$currentDate = customTimestamp();
		
		$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
		ob_clean();//clean buffer
		header('Location:http:./index.php?page=Users');
	}

function deactivateUser($conn,$userid){
	$sql = "UPDATE csm2.users SET user_status='Deactivated' WHERE user_id=:user_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':user_id',$userid,PDO::PARAM_INT);
	$stmt->execute();
	ob_clean();//clean buffer
	header('Location:http:./index.php?page=Users');
}	
	
function editUser($conn,$postArray){
	$sql = "UPDATE csm2.users SET user_fname=:user_fname,user_lname=:user_lname
	,role=:role, email=:email,phone=:phone,user_status=:user_status WHERE user_id=:user_id";
	$stmt = $conn->prepare($sql);
		$stmt->bindParam(':user_fname',$postArray['user_fname'],PDO::PARAM_STR);
		$stmt->bindParam(':user_lname',$postArray['user_lname'],PDO::PARAM_STR);
		$stmt->bindParam(':role',$postArray['role'],PDO::PARAM_STR);
		$stmt->bindParam(':email',$postArray['email'],PDO::PARAM_STR);
		$stmt->bindParam(':phone',$postArray['phone'],PDO::PARAM_STR);
		$stmt->bindParam(':user_status',$postArray['user_status'],PDO::PARAM_STR);
		$stmt->bindParam(':user_id',$postArray['user_id'],PDO::PARAM_INT);
		$stmt->execute();
	
	$activityDesc = 'User '.$_SESSION['fname'].' '.$_SESSION['lname'].' updated '.$postArray['user_fname'].' '.$postArray['user_lname'];
	$currentDate = customTimestamp();

	$sql = "INSERT INTO csm2.recent_activities (activity_desc,activity_date) 
		values(:activityDesc,:currentDate)";
		
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':activityDesc',$activityDesc,PDO::PARAM_STR);
		$stmt->bindParam(':currentDate',$currentDate,PDO::PARAM_STR);
		$stmt->execute();
	ob_clean();//clean buffer
	header('Location:http:./index.php?page=Users');
	
}	
	

function usersSearchFilter($conn,$keyphrase,$users){
	if(!$keyphrase)return $users;
	else{
		$filteredResults = array();
		foreach($users as $user){
			$matchFound = searchArray($keyphrase,$user);
			if($matchFound)$filteredResults[] = $user;
		}
		return $filteredResults;
	}
}	

function setLastAccessed($conn,$userid){
	$currentTime = customTimestamp();
	$sql= "UPDATE csm2.users SET last_accessed = '$currentTime'
		WHERE user_id = :user_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':user_id',$userid,PDO::PARAM_INT);
	$stmt->execute();
}
?>