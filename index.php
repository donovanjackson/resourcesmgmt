<?php
session_name('removed for security reasons');
session_start();

include ('views.php');
include('safemysql.class.php');
include ('csmAJAX.php');

$session = $_SESSION;

//$_GET = anti_hack($_GET);
$get = $_GET;
if(ISSET($_POST)){
$post = $_POST;

$post = filterOutEmpties($post);
$post = anti_hack($post);
}


if(ISSET($get['page']) && $get['page']=="Ajax");
else displayLayout();


if(!ISSET($_SESSION['authenticated'])){
	if(ISSET($get['action']) && $get['action']=="login")checkCred();
	else displayLogin();
}
else {
	$conn = connectToDB();
	if(ISSET($_SESSION['potential-threat']))displayQuarantinedPage($conn);
	else if(ISSET($get['action']) && $get['action']=="logout")logout();
	else if(ISSET($get['page'])){
		if($get['page']=="AdvancedSearch")displayAdvancedSearchPage($conn);
		if($get['page']=="ASResults" && ISSET($_POST)&& checkToken($post))displayASResultsPage($conn);
		else if($get['page']=="Clients"){
			if(ISSET($get['action'])){
				if($get['action']=="Search"&& checkToken($post))displayClientsPage($conn);
				else if($get['action']=="createClient")displayCreateClientPage($conn);
				else if($get['action']=="addClient" && ISSET($post)&& checkToken($post))addClient($conn,$post);
				else if($get['action']=="editClient")displayEditClientPage($conn,$get['clientid']);
				else if($get['action']=="deleteClient" && $_SESSION['role']=="administrator")deleteClient($conn,$get['clientid']);
				else if($get['action']=="saveClientChanges" && ISSET($post)&& checkToken($post))editClient($conn,$post);
				else if($get['action']=="viewClient")displayViewClientPage($conn,$get['clientid']);
			}
			else displayClientsPage($conn);
		}
		else if($get['page']=="Requests"){
			if(ISSET($get['action']) && $_SESSION['role']=="administrator"){
				if($get['action']=="Search")displayRequestsPage($conn);
				else if($get['action']=="createRequest")displayCreateRequestPage($conn);
				else if($get['action']=="addRequest" && ISSET($post) && checkToken($post) && $_SESSION['role']=="administrator")addRequest($conn,$post);
				else if($get['action']=="editRequest")displayEditRequestPage($conn,$get['requestid']);
				else if($get['action']=="deleteRequest" && $_SESSION['role']=="administrator")deleteRequest($conn,$get['requestid']);
				else if($get['action']=="saveRequestChanges" && ISSET($post) && checkToken($post) && $_SESSION['role']=="administrator")editRequest($conn,$post);
				else if($get['action']=="viewRequest")displayViewRequestPage($conn,$get['requestid']);
				else displayRequestsPage($conn);
			}
			else displayRequestsPage($conn);
		}
		else if($get['page']=="Resources"){
			if(ISSET($get['action'])){
				if($get['action']=="Search"&& checkToken($post))displayResourcesPage($conn);
				else if($get['action']=="createResource")displayCreateResourcePage($conn);
				else if($get['action']=="addResource" && ISSET($post)&& checkToken($post))addResource($conn,$post);
				else if($get['action']=="editResource")displayEditResourcePage($conn,$get['resourceid']);
				else if($get['action']=="deleteResource" && $_SESSION['role']=="administrator")deleteResource($conn,$get['resourceid']);
				else if($get['action']=="saveResourceChanges" && ISSET($post)&& checkToken($post))editResource($conn,$post);
				else if($get['action']=="filterResources")displayResourcesPage($conn);
				else if($get['action']=="viewResource")displayViewResourcePage($conn,$get['resourceid']);
			}
			else displayResourcesPage($conn);
		}
		else if($get['page']=="Sows"){
			if(ISSET($get['action'])){
				if($get['action']=="Search")displaySowsPage($conn);
				else if($get['action']=="createSow")displayCreateSowPage($conn);
				else if($get['action']=="addSow" && ISSET($post)&& checkToken($post))addSow($conn,$post);
				else if($get['action']=="editSow")displayEditSowPage($conn,$get['sowid']);
				else if($get['action']=="deleteSow" && $_SESSION['role']=="administrator")deleteSow($conn,$get['sowid']);
				else if($get['action']=="saveSowChanges" && ISSET($post)&& checkToken($post))editSow($conn,$post);
				else if($get['action']=="viewSow")displayViewSowPage($conn,$get['sowid']);
			}
			else displaySowsPage($conn);
		}
		else if($get['page']=="Users" && $_SESSION['role']=="administrator"){
			if(ISSET($get['action'])){
				if($get['action']=="Search")displayUsersPage($conn);
				else if($get['action']=="createUser")displayCreateUserPage($conn);
				else if($get['action']=="addUser" && ISSET($post)&& checkToken($post))addUser($conn,$post);
				else if($get['action']=="editUser")displayEditUserPage($conn,$get['userid']);
				else if($get['action']=="saveUserChanges" && ISSET($post)&& checkToken($post))editUser($conn,$post);
				else if($get['action']=="viewUser")displayViewUserPage($conn,$get['userid']);
			}
			else displayUsersPage($conn);
		}
		else if($get['page']=="Ajax")ajaxMethodRequest($get,$conn);
		else if($get['page']=="Reports")displayReportsPage($conn);
		else if($get['page']=="testPage" && $_SESSION['role']=="administrator")testPage($conn);
	}
		
	else if(ISSET($get['action'])){
		if($get['action']=="changePass" )displayChangePasswordPage();
		else if($get['action']=="commitPass"&& ISSET($post) && checkToken($post))changePassword($conn,$post);
		else if($get['action']=="viewFile"&& ISSET($_GET['directory']) && ISSET($_GET['filename']))pullFile($_GET['directory'],$_GET['filename']);
		else if($get['action']=="doTest" && $_SESSION['role']=="administrator" && ISSET($post))TestRecievePage($conn,$post);
		else displayClientsPage($conn);

	}
	else displayClientsPage($conn);
}

	
	


if(ISSET($get['page']) && $get['page']=="Ajax");
else displayFooter();


?>