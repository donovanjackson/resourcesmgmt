<?php

include ('clientFunctions.php');
include ('resourceFunctions.php');
include ('sowFunctions.php');
include ('requestFunctions.php');
include ('userFunctions.php');

function connectToDB(){

$dsn = 'removed for security reasons';
$user = 'removed for security reasons';
$password = 'removed for security reasons';
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $password,$opt);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	return $conn;
	
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}





	//$conn = new PDO('$dsn','$user','$password');
	//$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	
	//return $conn;
}
function customTimestamp(){
	return date('D M j Y g:i:s a');
}

function filterOutEmpties($array){
	foreach($array as $key=>$element){
		if(!is_array($element)){
			if(!strlen($element))unset($array[$key]);
		}
		else $array[$key] = filterOutEmpties($element);
	}
	return $array;
}

function arrayToStringOptions($array){
if(count($array)>1)$array[count($array)-1] = "or ".end($array);
$formattedStr = implode(",",$array);
$formattedStr = str_replace(",",", ",$formattedStr);
return $formattedStr;
}

function arrayToStringSet($array){
if(count($array)>1)$array[count($array)-1] = "and ".end($array);
$formattedStr = implode(",",$array);
$formattedStr = str_replace(",",", ",$formattedStr);
return $formattedStr;
}

function oneColumnDBResult($dbResults,$columnName){
	$columnValues = array();
	foreach($dbResults as $row){
		$columnValues[] = $row[$columnName];
	}
	return $columnValues;
}

function generate_secure_token($length = 16) { 
    return bin2hex(openssl_random_pseudo_bytes($length));  // important! this has to be a crytographically secure random generator 
} 

function cleanForHtml($data){
	if(!is_array($data)){
		$data = xss_clean($data);
		$data = html_escape($data);
		return $data;
	}
	else{
		foreach($data as $key=>$value){
			$data[$key] = cleanForHtml($value);
		}
		return $data;
	}
	
}

function html_escape($raw_input) { 
    return htmlspecialchars($raw_input, ENT_COMPAT, 'UTF-8'); 
} 


function prepForWebsite($data){
	if(!is_array($data)){
		$data =  escape_decode($data);
		return $data;
	}
	else{
		foreach($data as $key=>$value){
			$data[$key] = prepForWebsite($value);
		}
		return $data;
	}
}


function escape_decode($raw_input){
	return htmlspecialchars_decode($raw_input, ENT_QUOTES); 
}

function  anti_hack($data){
	if(ISSET($_SESSION['potential-threat']))return $data;
	if(!is_array($data)){
			$cleaned = xss_clean($data);
			//$data =  html_escape($data);
			if($data != $cleaned && ISSET($_SESSION['unwanted_tags']))$_SESSION['potential-threat'] = 1;
			$cleaned =  html_escape($cleaned);
			return $cleaned;
			
	}
	else{
		foreach($data as $key=>$value){
			$data[$key] = anti_hack($value);
		}
	}
	return $data;
}

/*
 * XSS filter from mbijon / xss_clean.php
 *
 * This was built from numerous sources
 * (thanks all, sorry I didn't track to credit you)
 * 
 * It was tested against *most* exploits here: http://ha.ckers.org/xss.html
 * WARNING: Some weren't tested!!!
 * Those include the Actionscript and SSI samples, or any newer than Jan 2011
 *
 *
 * TO-DO: compare to SymphonyCMS filter:
 * https://github.com/symphonycms/xssfilter/blob/master/extension.driver.php
 * (Symphony's is probably faster than my hack)
 */
function xss_clean($data)
{
		$oldData = $data;
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
	
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
 
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
 
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
 
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
 
        do
        {
                // Remove really unwanted tags
                $old_data = $data;
                $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
				if($oldData!=$data)return "Input ".$data." is a possible XSS Attack and Breach of Website. Report To Administrator";
        }
        while ($old_data !== $data);
				if($oldData!=$data)return "Input ".$data." is a possible XSS Attack and Breach of Website. Report To Administrator";
        // we are done...
		return $data;
}

function quarantineUser($conn){
	$sql = "UPDATE csm2.users SET user_status='Deactivated' WHERE user_id=:user_id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam(':user_id',$_SESSION['userid'],PDO::PARAM_INT);
	$stmt->execute();
	unset($_SESSION['potential-threat']);
	unset($_SESSION['unwanted_tags']);
	session_unset();
	session_destroy();
}

function getRecentActivities($conn){
	$sql = "SELECT * FROM csm2.recent_activities ORDER BY recent_activity_id DESC LIMIT 20";
	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $results;
}

function checkToken($postArray){
	if(isset($postArray['action_token']) && isset($_SESSION['action_token']) && $postArray['action_token'] == $_SESSION['action_token'])return true;
	else return false;
}

function searchArray($needle,$haystack){
	foreach($haystack as $key=>$value){
				if(stripos($value,$needle)!==false)return true;
			}
	return false;
}

function searchTwoDArray($needle,$twoDArray){
	if(!$twoDArray)return false;
	foreach($twoDArray as $haystack){
				if(searchArray($needle,$haystack))return true;
			}
	return false;		
}


function getUSStates(){
$states = array("Alabama","Alaska","Arizona","Arkansas","California",
"Colorado","Connecticut","Delaware","Florida","Georgia",
"Hawaii","Idaho","Illinois","Indiana","Iowa",
"Kansas","Kentucky","Louisiana","Maine","Maryland",
"Massachusetts","Michigan","Minnesota","Mississippi","Missouri",
"Montana","Nebraska","Nevada","New Hampshire","New Jersey",
"New Mexico","New York","North Carolina","North Dakota","Ohio",
"Oklahoma","Oregon","Pennsylvania","Rhode Island","South Carolina",
"South Dakota","Tennessee","Texas","Utah","Vermont",
"Virginia","Washington","West Virginia","Wisconsin","Wyoming");
return $states;
}



function clientFieldnames(){
$fieldnames = array("client_name","main_phone","client_address");
}
function requestsFieldnames(){
$fieldnames = array();

}
function resourceFieldnames(){
$fieldnames = array();

}
function sowFieldnames(){
$fieldnames = array();

}
function userFieldnames(){
$fieldnames = array();

}



function searchDBRelations($table,$fields,$keyphrases){
	$sql = " SELECT * FROM";
	$conditions = " Where";
	for($i=0;$i<count($fields);$i++){
		if($keyphrases[$i]){
			
		}
	}
}


function getPossibleRoles(){
$roles = array("user","administrator");
return $roles;
}

function fileNameFormatting($filename,$fieldname){
	 $noGood = array(" ","~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]",
                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                   "â€”", "â€“", ",", "<", ".", ">", "/", "?");
	 $formatedName =  str_replace($noGood,"_",$filename);
	 $temp = explode(".", $_FILES[$fieldname]['name']);
	 $extension = end($temp);
	 return $formatedName.'.'.$extension;
}

function uploadFile($prevName,$givenName,$fieldname,$directory){
	//$directory = '/var/'.$directory;
	if($_FILES[$fieldname]['name']){
		$filename = $_FILES[$fieldname]['name'];
		$file_type = $_FILES[$fieldname]['type'];
		$file_tmpname = $_FILES[$fieldname]['tmp_name'];
		
		$allowedExts = array("gif", "jpeg", "jpg", "png","doc","docx","pdf");
		$temp = explode(".", $filename);
		$extension = end($temp);
		
		if (in_array($extension, $allowedExts)) {
		  if ($_FILES[$fieldname]["error"] > 0) {
			return false;
		  } else {
			if ($prevName!=null && file_exists($directory."/". $prevName)){
				if($directory=="sow_uploads") {
					$removalTime = date('Y-m-d_H-i-s');
					$removedFileName = explode(".",$givenName);
					array_pop($removedFileName);
					$removedFileName = implode($removedFileName);
					$removedFileName = $removedFileName."_datetimeRemoved_".$removalTime.".".$extension;
					rename("sow_uploads/".$prevName,"oldSowRecords/".$removedFileName);
				}
				else unlink($directory."/". $prevName);
			}
			  move_uploaded_file($file_tmpname,
			  $directory."/". $givenName);
			  return true;
			
		  }
		} else {
		  return false;
		}
	}
	else return false;
}

function pullFile($directory,$filename){
	//$directory = '/var/'.$directory;
	$temp = explode(".", $filename);
	$extension = end($temp);
	$ctype="";
	switch ($extension) {
		case "pdf": $ctype="application/pdf"; break;
		case "doc": $ctype="application/msword"; break;
		case "gif": $ctype="image/gif"; break;
		case "png": $ctype="image/png"; break;
		case "jpe": $ctype= "image/jpeg";break;
		case "jpg": $ctype="image/jpg"; break;
	}

	if($extension == "application/msword") $ctype = "application/octet-stream";
	header('Content-type:'.$ctype);
	if($ctype == "application/octet-stream") header('Content-Disposition:attachment;filename='.$filename);
	else header('Content-Disposition:inline;filename='.$filename);
	header('Content-Transfer-Encoding: binary');
	header('Content-Length:'. filesize($directory."/".$filename));
	header('Accept-Ranges: bytes'); 
	header('Pragma: no-cache'); 
	header('Expires: 0'); 
	header('Cache-Control:public');
	ob_clean();
	readfile($directory."/".$filename);
	flush();
}

function removeFile($directory,$filename){
	//$directory = '/var/'.$directory;
	unlink($directory."/". $filename);
}
	
function checkCred(){
	$conn = connectToDB();
	$username = $_POST['username'];
	$pass = $_POST['password'];
	$ip = $_SERVER['REMOTE_ADDR'];
	$accessedBefore = TRUE;
	$sql = "SELECT * FROM csm2.users WHERE username=:username LIMIT 1";
	$stmt=$conn->prepare($sql);
	$stmt->bindParam(':username',$username,PDO::PARAM_STR);
	$stmt->execute();
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if($result!=null && checkPass($pass,$result) && $result['user_status']!="Deactivated"&& $result['user_status']!="To Be Removed"){
		//after user authenticated
		if(ISSET($_SESSION['failedLogins'])) unset($_SESSION['failedLogins']);
		unset($_SESSION['potential-threat']);
		if(!$result['last_accessed']){
			$accessedBefore = FALSE;
			$_SESSION['firstLogin'] = TRUE;
		}
		setLastAccessed($conn,$result['user_id']);
		$_SESSION['authenticated'] = TRUE;
		$_SESSION['username'] = $username;
		$_SESSION['userid'] = $result['user_id'];
		$_SESSION['fname'] = $result['user_fname'];
		$_SESSION['lname'] = $result['user_lname'];
		$_SESSION['role'] = $result['role'];
		$_SESSION['ip'] = $ip;
		$_SESSION['action_token'] = generate_secure_token();
	}
	else {
		/*$sql = "INSERT INTO csm2.failed_logins (username,ip_address) values(:username:,ip_address)";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':username',$username,PDO::PARAM_STR);
		$stmt->bindParam(':ip_address',$ipAddress,PDo::PARAM_STR);
		$stmt->execute();
		loginRestrict($conn);
		*/
		$_SESSION['loginError'] = "Incorrect Username and/or Password";
		if(!ISSET($_SESSION['failedLogins'])) $_SESSION['failedLogins']=1;
		else $_SESSION['failedLogins']+=1;
		if($_SESSION['failedLogins']==5)
		{
			$_SESSION['loginError'] = 'Too many failed attempts to login. Login 
			shutdown for 5 minutes. Try again later';
			unset($_SESSION['failedLogins']);
		}	
	}
	
	ob_clean();//clean buffer
	if($accessedBefore)header('Location:./index.php');
	else header('Location:./index.php?action=changePass');
}

function loginRestrict($conn){
	$throttle = array(10=>1,20=>2,30=>'recaptcha');
	
	
	//SELECT COUNT(1) AS failed FROM failed_logins WHERE attempted > DATE_SUB(NOW(), INTERVAL 15 minute);
	$sql = "SELECT";
	$stmt = $conn -> prepare($sql);
	
	
	//$sql = 'SELECT MAX(attempted) AS attempted FROM failed_logins';

}

function checkPass($pass,$row){
	$salted = $pass.$row['salt'];
	$sha1Version =sha1($salted);
	return $sha1Version == $row['password'];
}


function logout(){
	session_unset();
	session_destroy();
	ob_get_contents();//process buffer more
	ob_end_clean();//clean buffer
	header('Location:./index.php');
}
?>