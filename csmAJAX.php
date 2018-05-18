<?php
function ajaxMethodRequest($getArray,$conn){
	header('Content-Type: text/xml');
	echo'<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
	if($getArray['action']="checkUsername")checkUsername($conn);
}


function checkUsername($conn){
	echo '<response>';
	if(ISSET($_GET['username']) && trim($_GET['username'])!=""){
		$username = $_GET['username'];
		$usernames = getUsernames($conn);
		$lcUsername = strtolower($username);
		$lcUsernames = array_map('strtolower',$usernames);
		if(in_array($lcUsername,$lcUsernames))echo 'Invalid';
		else echo 'Valid';
	}
	else echo 'Invalid';
	
	echo '</response>';
}

?>