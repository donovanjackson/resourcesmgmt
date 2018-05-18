<?php
include ('mainFunctions.php');

function displayLayout(){
	echo '	
		
		<html>
		<!DOCTYPE html> 
		<head>
		<title>
        Infinity System\'s CSM
        </title>
		<link rel="stylesheet" href="css/jquery-ui-1.10.4.custom.min.css"/>
		<link rel="stylesheet" href="css/layout-css.css"/>
		
		<script src="js/jquery-1.11.1.js" type="text/javascript"></script>
		<script src="js/jquery-ui-1.10.4.js" type="text/javascript"></script>
		<script src="js/jquery.qtip-1.0.0-rc3.min.js" type="text/javascript"></script>
		<script src="js/jfunctionality.js" type="text/javascript"></script>
		<script src="js/ajaxfunctionality.js" type="text/javascript"></script>
		<script src="js/screening-input.js" type="text/javascript"></script>
		<meta charset="ISO-8859-1">
		</head>
		<body>';
		echo'
		<div id="container">
		<div id="header">
		<div id="banner"><!--Infinity System\'s CSM--></div>';
		if(ISSET($_SESSION['authenticated'])) displayLoggedInBar();
		echo'</div><hr/><hr/>	
		<div id="maincontent">';
			
}

function displayLoggedInBar(){
	echo '
	<div id="loggedInBar"><div id="loggedIn"><span>
		Welcome,'.$_SESSION['fname'].' '.$_SESSION['lname'].'</span> | 
		<span id="logout"><button type="button"  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?action=logout">Logout</a></button></span>
		<br/><a href="http://localhost/resourcesmgmt/index.php?action=changePass">Change Password?</a></div>
	
	<div id="menu">
			<!--<div class="menuBox"><span><a href="http://localhost/resourcesmgmt/index.php">Home</a></span></div>-->
			<div class="menuBox"><span><a href="http://localhost/resourcesmgmt/index.php?page=Clients">Manage Clients</a></span></div>
			<div class="menuBox"><span><a href="http://localhost/resourcesmgmt/index.php?page=Resources">Manage Resources</a></span></div>
			<div class="menuBox"><span><a href="http://localhost/resourcesmgmt/index.php?page=Sows">Manage Sows</a></span></div>
			<div class="menuBox"><span><a href="http://localhost/resourcesmgmt/index.php?page=Requests">Manage Requests</a></span></div>
			<div class="menuBox"><span><a href="http://localhost/resourcesmgmt/index.php?page=Reports">Reports</a></span></div>';
	if($_SESSION['role']=="administrator") echo '<div class="menuBox" style="margin-left:5px;"><span><a href="http://localhost/resourcesmgmt/index.php?page=Users">Manage Users</a></span></div>';

	echo '</div></div>';
}


function displayLogin(){

	echo '<form action="index.php?action=login" method="post">
			Username: <input type="text" maxlength="45" name="username"><br/>
			Password: <input type="password" name="password"><br/>';
			if(ISSET($_SESSION['loginError'])){
				echo '<span style="color:d66">'.$_SESSION['loginError'].'</span><br/>';
				unset($_SESSION['loginError']);
			}
			echo '<input type="submit" value="Login">
	
		<!--<span><a href="">Forget Password?Have it sent to your email.</a></span>-->';
}

function displayhomePage(){
	echo 'Welcome Home';
	
}

function displayChangePasswordPage(){
	echo '<form action="index.php?action=commitPass" method="post">
	<div class="entries">';
	if(ISSET($_SESSION['firstLogin'])){
		echo '<div id="firstTimeMessage" >If this is your first time logging in, it is suggested you change your password</div>';
		unset($_SESSION['firstLogin']);
	}
	echo' <label for="old_pass" class="input_name">Old Password:</label><input id="old_pass" name="old_pass" class="input_value requiredInput" type="password">
		<span>* Checked Upon Submission Of New Password.</span>';
	if(ISSET($_SESSION['wrongPass'])){
		echo '<span> Original Password Submitted Was Incorrect</span>';
		unset($_SESSION['wrongPass']);
	}
	echo'<br/>
	<label for="new_pass" class="input_name">New Password:</label><input  id="new_pass" name="new_pass" class="input_value requiredInput" type="password">
	<span>* Has To Be At Least 7 characters long</span>
	<button class="togglePassType" data-ref="new_pass">View</button><br/>
	<label for="conf_pass" class="input_name">Confirm New Password:</label><input  id="conf_pass" name="conf_pass" class="input_value requiredInput" type="password">
	<span>* Has To Match New Password</span>
	<button class="togglePassType" data-ref="conf_pass">View</button><br/>
	<input type="submit" value="Make Change">
	</div>
	<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
	
	
}


function displayAdvancedSearchPage($conn){
	echo '<div class="pageTitle"><h1>Advanced Search</h1><div>';
	echo '<div><h2>List Out Keyphrases To Try Against All Clients
	, Resources, Requests, and S.O.W.s</h2></div>';
	echo'<div class="entries">';
	echo'<form action="http://localhost/resourcesmgmt/index.php?page=ASResults" method="post">
	
	<div id="keyphrasesWanted">
	<div class="requiredKeyphrase">
	Keyphrase: <input name="keyphrase[]" type="text" maxlength="45">
	<select name="conditional[]">
	<option value="Must Have">Must Have</option>
	<option value="A Plus">A Plus</option>
	</select>
	</div>
	</div>
	<input type="button" id="another_keyphrase" value="Add Keyphrase"><br/>
	<input type="submit" value="Perform Search"><input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form></div>';
}

function displayAdvancedSearchOption(){
echo ' | <button type="button"  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=AdvancedSearch">Advanced Search</a></button>';
}

function displayASResultsPage($conn){
	echo'<div><h1>Results Of The Advanced Search-';
	$searchParams = "";
	for($i=0;$i<count($_POST['keyphrase']);$i++){
		if($_POST['keyphrase'][$i]!=null)$searchParams = $searchParams.' '.$_POST['conditional'][$i].': '.$_POST['keyphrase'][$i].',';
	}
	$searchParams = substr($searchParams,0,-1);
	echo $searchParams.'</h1></div>';
	$clients = getClients($conn);
	$results = asOnClients($conn,$_POST,$clients);
	echo '<div class="pageTitle"><h1>Clients</h1><div>';
	if($results){
		echo '<div class="pageListings">
		<div class="searchbar">
				<form action="http://localhost/resourcesmgmt/index.php?page=Clients&action=Search" method="post">
					<input type="submit" name="search" value="Search Clients"><input name="keyphrase" type="text" maxlength="45">';
				displayAdvancedSearchOption();
				echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
			</div>';//div.searchbar
		echo'<div class="mgmtTools"><button type="button" id="createClient" style="background:none;"  class="buttonLink">
		<a href="http://localhost/resourcesmgmt/index.php?page=Clients&action=createClient">Create New Client</a></button></div>';
		
		
		echo '<div class="table">';
		echo'<div class="tableHead">
		<table  class="tableHead">
				<tr>
					<th>Client Name</th>
					<th>Main Phone</th>
					<th>Address</th>
					<th>Sales Representative(s)</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
		</table></div>';//div.tableHead	
				
		if($results){
			echo'<div class="tableBody"><table  class="tableBody">';
			for($i=0;$i<count($results);$i++){
				$results[$i]['sales_rep']=getClientsSRsAsString($conn,$results[$i]['sales_rep']);
			echo'<tr>
					<td>'.$results[$i]['client_name'].'</td>
					<td>'.$results[$i]['main_phone'].'</td>
					<td>'.$results[$i]['client_address'].'</td>
					<td>'.$results[$i]['sales_rep'].'</td>
					<td>'.$results[$i]['client_status'].'</td>
					<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Clients&action=viewClient&clientid='.$results[$i]['client_id'].'">View</a>
				<a href="http://localhost/resourcesmgmt/index.php?page=Clients&action=editClient&clientid='.$results[$i]['client_id'].'">Edit</a></td>
				</tr>';	
			}	
			echo '</table></div>';//div.tableBody
		}
		echo '</div>';//div.table
		echo'</div>';//div.pageListings
	}
	else echo '<div>No Matches</div>';
	$resources = getResources($conn);
	$results = asOnResources($conn,$_POST,$resources);
	echo '<div class="pageTitle"><h1>Resources</h1><div>';
	if($results){
		
		echo '<div class="pageListings">
		<div class="searchbar">
				<form action="http://localhost/resourcesmgmt/index.php?page=Resources&action=Search" method="post">
					<input type="submit" name="search" value="Search Resources"><input name="keyphrase" type="text" maxlength="45">';
				displayAdvancedSearchOption();
				echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
			</div>';
		//Addition Options for page. not relevant to any particular entry.
		echo'<div class="mgmtTools"><button type="button" id="createUser" style="background:none;"  class="buttonLink">
		<a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=createResource">Create New Resource</a></button></div>';
		echo '<div class="table">';
		echo'<div class="tableHead">
		<table  class="tableHead">
				<tr>
					<th>Resource Name</th>
					<th>Last Name</th>
					<th>First Name</th>
					<th>Contact Number</th>
					<th>2nd Contact #</th>
					<th>email</th>
					<th>Address</th>
					<th>Resume</th>
					<th>ISSI Resume</th>
					<th>Notes</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
		</table></div>';	
				
		if($results){
			echo'<div class="tableBody"><table  class="tableBody">';
			for($i=0;$i<count($results);$i++){
				echo'<tr>
					<td>'.$results[$i]['resource_name'].'</td>
					<td>'.$results[$i]['resource_lname'].'</td>
					<td>'.$results[$i]['resource_fname'].'</td>
					<td>';
					if($results[$i]['phone1']!=="")echo '1st: '.$results[$i]['phone1'];
					if($results[$i]['phone2']!=="")echo '<br/>2nd: '.$results[$i]['phone2'];
					echo'</td>
					<td></td>
					<td>'.$results[$i]['email'].'</td>
					<td>'.$results[$i]['resource_address'].'</td>
					<td>';
					if($results[$i]['resume_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_uploads&filename='.$results[$i]['resume_upload'].'" value="View Resume">';
					echo '</td>
					<td>';
					if($results[$i]['resume_issi_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_issi_uploads&filename='.$results[$i]['resume_issi_upload'].'" value="View Resume ISSI">';
					echo '</td>
					<td>'.$results[$i]['notes'].'</td>
					<td>'.$results[$i]['resource_status'].'</td>
					<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=viewResource&resourceid='.$results[$i]['resource_id'].'">View</a> 
					<a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=editResource&resourceid='.$results[$i]['resource_id'].'">Edit</a></td>
				</tr>';	
			}	
			echo '</table></div>';//div.tableBody
		}
		echo '</div>';//div.table
		echo'</div>';//div.pageListings
	}
	else echo '<div>No Matches</div>';
	$requests = getRequests($conn);
	$results = asOnRequests($conn,$_POST,$requests);
	echo '<div class="pageTitle"><h1>Requests</h1><div>';
	if($results){
		echo '<div class="pageListings">
		<div class="searchbar">
				<form action="http://localhost/resourcesmgmt/index.php?page=Requests&action=Search" method="post">
					<input type="submit" name="search" value="Search Requests"><input name="keyphrase" type="text" maxlength="45">';
				displayAdvancedSearchOption();
				echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
			</div>';
		//Addition Options for page. not relevant to any particular entry.
		echo'<div class="mgmtTools"><button type="button" id="createSow" style="background:none;"  class="buttonLink">
		<a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=createRequest">Create New Requests</a></button></div>';
		
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>Request Name</th>
					<th>Request Description</th>
					<th>Requester</th>
					<th>Type</th>
					<th>Budget</th>
					<th>Length</th>
					<th>Location</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
	</table>		
	</div>';	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($results as $request){
			echo'<tr>
					<td>'.$request['request_name'].'</td>
						<td>'.$request['request_desc'].'</td>
						<td>'.$request['client_name'].'</td>
						<td>'.$request['type'].'</td>
						<td>'.$request['budget'].'</td>
						<td>'.$request['length'].'</td>
						<td>'.$request['location'].'</td>
						<td>'.$request['request_status'].'</td>
						<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=viewRequest&requestid='.$request['request_id'].'">View</a> 
					<a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=editRequest&requestid='.$request['request_id'].'">Edit</a></td>
					</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
	echo'</div>';
	}
	else echo '<div>No Matches</div>';
	$sows = getSows($conn);
	$results = asOnSows($conn,$_POST,$sows);
	echo '<div class="pageTitle"><h1>S.O.W.s(Statements of Work)</h1><div>';
	if($results){
		
		echo '<div class="pageListings">
		<div class="searchbar">
				<form action="http://localhost/resourcesmgmt/index.php?page=Sows&action=Search" method="post">
					<input type="submit" name="search" value="Search S.O.W.s"><input name="keyphrase" type="text" maxlength="45">';
				displayAdvancedSearchOption();
				echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
			</div>';
		//Addition Options for page. not relevant to any particular entry.
		echo'<div class="mgmtTools"><button type="button" id="createSow" style="background:none;"  class="buttonLink">
		<a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=createSow">Create New Sow</a></button></div>';
		
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>S.O.W. Name</th>
					<th>Brief Summary</th>
					<th>Client</th>
					<th>Client Primary Contact</th>
					<th>S.O.W.</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Billing Rate</th>
					<th>Status</th>
					<th>Subcontractor</th>
					<th>Actions</th>
				</tr>
	</table></div>';	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($results as $sow){
			echo'<tr>
					<td>'.$sow['sow_name'].'</td>
						<td>'.$sow['summary'].'</td>
						<td>'.$sow['client_name'].'</td>
						<td>'.$sow['primary_contact'].'</td>
						<td>';
						if($sow['sow_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$sow['sow_upload'].'" value="View S.O.W. File">';
						echo '</td>
						<td>'.$sow['start_date'].'</td>
						<td>'.$sow['end_date'].'</td>
						<td>'.$sow['billing'].'</td>
						<td>'.$sow['sow_status'].'</td>
						<td>'.$sow['subcontractor_disguise'].'</td>
						<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=viewSow&sowid='.$sow['sow_id'].'">View</a> 
						<a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=editSow&sowid='.$sow['sow_id'].'">Edit</a></td>
					</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
	echo'</div>';
	}
	else echo '<div>No Matches</div>';
	
	
}

function displayCreateClientPage($conn){
	$states = getUSStates();
	$salesReps = getSalesReps($conn);
	echo '<div class="client_manage"><div class="pageTitle"><h1>Add A Client</h1><div>';
	echo'<form action="http://localhost/resourcesmgmt/index.php?page=Clients&action=addClient" method="post">
		<div class="entries">';
		echo '<div class="sectionTitle"><h2>Client Details</h2></div>';			
		echo'<div class= "newEntryInfo-Threes">
		<span class="input_name">Company Name:</span><br/>
		<span class="input_value"><input name="client_name" type="text" maxlength="45" class="requiredInput"></span><br/>
		<span class="input_name">Main Phone:</span><br/>
		<span class="input_value"><input name="main_phone" type="text" maxlength="45"></span><br/>
		<span class="input_name">Address:</span><br/>
		<textarea name="client_address" rows="3" class="contact_info"></textarea><br/>
		</div>
		<div class= "newEntryInfo-Threes">
		<span class="input_name">Brief Notes:</span><br/>
		<textarea name="client_notes" rows="3" class="contact_info"></textarea><br/>
		</div>';//.newEntryInfo-Threes
		echo'<div class= "newEntryInfo-Threes">
		<div class="skillCategory">Sales Representatives</div>
		<div class="toggleCheckboxes">';
		foreach($salesReps as $salesRep){
		echo '<button type="button" class="tglBtn">
		<input type="checkbox" name="sales_rep[]" class="tglCB" value="'.$salesRep['sr_id'].'">'.$salesRep['sr_name'].'
		</button><br/>';
		}
		echo'</div>';//.toggleCheckboxes
		echo'<div class="newOptions"></div><input class="addNewOption" data-giveName="new_sales_rep[]" type="button" value="Add Sales Rep">';//.newEntryInfo-
		echo'</div>';//.newEntryInfo-Threes
		echo'</div>';//.entries
		echo'<div class="entries">
		<div class="sectionTitle"><h2>Contacts Of Client</h2></div><ul>
		<div name="clients_contacts" id="clients_contacts">
			<div class="contact"><li>
			<label for="contact_name[]">Full Name:</label><input type="text" maxlength="45" name="contact_name[]" class="contact_info requiredInput">
			<label for="office_phone[]"></label>Office Phone#:<input type="text" maxlength="45" name="office_phone[]" class="contact_info">
			<label for="cell_phone[]"></label>Cell Phone#:<input type="text" maxlength="45" name="cell_phone[]" class="contact_info">
			<label for="home_phone[]"></label>Home Phone#:<input type="text" maxlength="45" name="home_phone[]" class="contact_info">
			<label for="email[]">Email:</label><input type="text" maxlength="45" name="email[]" class="contact_info"><br/>
			<label for="secretary_name[]">Secretary Name:</label><input type="text" maxlength="45" name="secretary_name[]" class="contact_info">
			<label for="secretary_phone[]">Secretary Phone:</label><input type="text" maxlength="45" name="secretary_phone[]" class="contact_info">
			<label for="fax[]"></label>Fax:<input type="text" maxlength="45" name="fax[]" class="contact_info">
			<label for="contact_notes[]">Brief Notes</label><textarea name="contact_notes[]" class="contact_info"></textarea>
			</li></div>
			
		</div></ul>
			<input type="button" id="another_contact" value="Add Another Contact">
		</div>';//.
		
		echo'<div class="submitDiv"><input type="submit" value="Save New Client"></div>
	<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form></div>';
}


function displayEditClientPage($conn,$clientid){
	$result = getSpecificClient($conn,$clientid);
	if($result != null){
		$states = getUSStates();
		$result['sales_rep'] = explode(",",$result['sales_rep']);
		$contacts = getContacts($conn,$clientid);
		$clientsSows = getClientsActiveSows($conn,$clientid);
		$salesReps = getSalesReps($conn);
		$hardwareSales = getHardwareSales($conn,$clientid);
		$softwareSales = getSoftwareSales($conn,$clientid);
		$requests = getClientsOpenRequests($conn,$clientid);
		echo '<div class="client_manage"><div class="pageTitle"><h1>Edit Client: ';
		if(ISSET($result['client_name']))echo $result['client_name'];
		else echo $result['client_fname'];
		echo'</h1></div>';//.pageTitle
		echo'<form action="http://localhost/resourcesmgmt/index.php?page=Clients&action=saveClientChanges" method="post">
			<div class="entries">';
			echo '<div class="sectionTitle"><h2>Client Details</h2></div>';				
			echo'<div class= "newEntryInfo-Threes">
			<span class="input_name">Company Name:</span><br/>
			<span class="input_value"><input name="client_name" type="text" maxlength="45" value="'.html_escape($result['client_name']).'"  class="requiredInput"></span><br/>
			<span class="input_name">Main Phone:</span><br/>
			<span class="input_value"><input name="main_phone" type="text" maxlength="45" value="'.$result['main_phone'].'"></span><br/>
			<span class="input_name">Address:</span><br/>
			<textarea name="client_address" rows="3" class="contact_info">'.$result['client_address'].'</textarea><br/>
			</div>';//.newEntryInfo-Threes
			echo'<div class= "newEntryInfo-Threes">
			<span class="input_name">Brief Notes:</span><br/>
			<textarea name="client_notes" rows="3" class="contact_info">'.$result['client_notes'].'</textarea><br/>
			<span class="input_name">Status:</span><br/>
			<span class="input_value">
			<select name="client_status">
			<option value="Active"';
			if($result['client_status']=="Active")echo ' selected';
			echo '>Active</option>
			<option value="Inactive"';
			if($result['client_status']=="Inactive")echo ' selected';
			echo '>Inactive</option>
			<option value="To Be Removed"';
			if($result['client_status']=="To Be Removed")echo ' selected';
			echo '>To Be Removed</option>
			</select>
			</span><br/>
			</div>';//.newEntryInfo-Threes
			echo'<div class= "newEntryInfo-Threes">
			<div class="skillCategory">Sales Representatives</div>
			<div class="toggleCheckboxes">';
			foreach($salesReps as $salesRep){
			echo '<button type="button" class="tglBtn';
			if(ISSET($result['sales_rep']) && in_array($salesRep['sr_id'],$result['sales_rep'])) echo ' checked';
			echo'">
			<input type="checkbox" name="sales_rep[]" class="tglCB" value="'.$salesRep['sr_id'].'"';
			if(ISSET($result['sales_rep']) && in_array($salesRep['sr_id'],$result['sales_rep']))echo' checked';
			echo'>'.$salesRep['sr_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="newOptions"></div><input class="addNewOption" data-giveName="new_sales_rep[]" type="button" value="Add Sales Rep">';
			echo'</div>';//.newEntryInfo-Threes
			echo'</div>';//.entries
			echo'<div = class="entries">
			
			<div class="sectionTitle"><h2>Contacts Of:';
			echo $result['client_name'];
			echo'</h2></div><ul>
			<div name="clients_contacts" id="clients_contacts">
			<div class="contact"><li>
			<label for="contact_name[]">Full Name:</label><input type="text" maxlength="45" name="contact_name[]" class="contact_info requiredInput" value="'.$contacts[0]['contact_name'].'">
			<label for="office_phone[]"></label>Office Phone#:<input type="text" maxlength="45" name="office_phone[]" class="contact_info" value="'.$contacts[0]['office_phone'].'">
			<label for="cell_phone[]"></label>Cell Phone#:<input type="text" maxlength="45" name="cell_phone[]" class="contact_info" value="'.$contacts[0]['cell_phone'].'">
			<label for="home_phone[]"></label>Home Phone#:<input type="text" maxlength="45" name="home_phone[]" class="contact_info" value="'.$contacts[0]['home_phone'].'">
			<label for="email[]">Email:</label><input type="text" maxlength="45" name="email[]" class="contact_info" value="'.$contacts[0]['email'].'"><br/>
			<label for="secretary_name[]">Secretary Name:</label><input type="text" maxlength="45" name="secretary_name[]" class="contact_info" value="'.$contacts[0]['secretary_name'].'">
			<label for="secretary_phone[]">Secretary Phone:</label><input type="text" maxlength="45" name="secretary_phone[]" class="contact_info" value="'.$contacts[0]['secretary_phone'].'">
			<label for="fax[]"></label>Fax:<input type="text" maxlength="45" name="fax[]" class="contact_info" value="'.$contacts[0]['fax'].'">
			<label for="contact_notes[]">Brief Notes</label><textarea name="contact_notes[]" class="contact_info">'.$contacts[0]['contact_notes'].'</textarea>
			<input name="contact_id[]" class="contactId" type="hidden" value="'.$contacts[0]['contact_id'].'">
			</li></div>';
			if(count($contacts)>1){
				for($i=1;$i<count($contacts);$i++){
				echo'
					<div><hr/><li>
			<label for="contact_name[]">Full Name:</label><input type="text" maxlength="45" name="contact_name[]" class="contact_info requiredInput" value="'.$contacts[$i]['contact_name'].'">
			<label for="office_phone[]"></label>Office Phone#:<input type="text" maxlength="45" name="office_phone[]" class="contact_info" value="'.$contacts[$i]['office_phone'].'">
			<label for="cell_phone[]"></label>Cell Phone#:<input type="text" maxlength="45" name="cell_phone[]" class="contact_info" value="'.$contacts[$i]['cell_phone'].'">
			<label for="home_phone[]"></label>Home Phone#:<input type="text" maxlength="45" name="home_phone[]" class="contact_info" value="'.$contacts[$i]['home_phone'].'">
			<label for="email[]">Email:</label><input type="text" maxlength="45" name="email[]" class="contact_info" value="'.$contacts[$i]['email'].'"><br/>
			<label for="secretary_name[]">Secretary Name:</label><input type="text" maxlength="45" name="secretary_name[]" class="contact_info" value="'.$contacts[$i]['secretary_name'].'">
			<label for="secretary_phone[]">Secretary Phone:</label><input type="text" maxlength="45" name="secretary_phone[]" class="contact_info" value="'.$contacts[$i]['secretary_phone'].'">
			<label for="fax[]"></label>Fax:<input type="text" maxlength="45" name="fax[]" class="contact_info" value="'.$contacts[$i]['fax'].'">
			<label for="contact_notes[]">Brief Notes</label><textarea name="contact_notes[]" class="contact_info">'.$contacts[$i]['contact_notes'].'</textarea>
			<input name="contact_id[]" class="contactId" type="hidden" value="'.$contacts[$i]['contact_id'].'">
			<button type="button" class="delete_contact">Delete Contact</button></li></div>';
				}
			}
			echo '</div></ul>
			<input type="hidden" id="contactsDeleted" name="contacts_deleted">
			<input type="button" id="another_contact" value="Add Another Contact">
			</div>
			<div class="entries">
			<div class="sectionTitle"><h2>'.$result['client_name'].'\'s Activity</h2></div>
			<div id="client_activity">
			<h3>Active SOWS:</h3>
			<div id= "active_sows" class="cActvity">
			
			<ul>';
			
			if($clientsSows !=null){
				foreach($clientsSows as $sow){
				echo '<li>'.$sow['sow_name'].'<input type="button" class="viewer" data-url="http://localhost/resourcesmgmt/index.php?page=Sows&action=viewSow&sowid='.$sow['sow_id'].'" value="View S.O.W. Entry">
				<ul>
				<li>Summary: '.$sow['summary'].'</li>
				<li>SubContractor Identity: '.$sow['subcontractor_disguise'].'</li>
				<li>';
				if($sow['sow_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$sow['sow_upload'].'" value="View S.O.W. File">';
				echo'</li>
				</ul>
				</li>';
				}
			}
			echo'</ul>
			</div>';//#active_sows.cActivty
			echo'<h3>Hardware Sales:</h3>
			<div id="hardware_sales" class="cActvity">
			
			<ul id="clients_hSales">';
			if($hardwareSales !=null){
				foreach($hardwareSales as $hSale){
				echo'<div><li>
				<label for="sold_hardware_name[]">Hardware Name:</label><input type="text" maxlength="45" name="sold_hardware_name[]" value="'.$hSale['sold_hardware_name'].'">
				<label for="hs_buying_contact[]">Sold To(Contact): </label><input type="text" maxlength="45" name="hs_buying_contact[]" value="'.$hSale['hs_buying_contact'].'">
				<label for="hs_bc_phone[]">Buyer\'s Phone Number: </label><input type="text" maxlength="45" name="hs_bc_phone[]" value="'.$hSale['hs_bc_phone'].'">
				<label for="hs_selling_date[]">Selling Date: </label><input type="text" maxlength="45" class="datepicker" name="hs_selling_date[]" value="'.$hSale['hs_selling_date'].'">
				<input name="hardware_sale_id[]" class="hsId" type="hidden" value="'.$hSale['hardware_sale_id'].'">
				<button class="delete_hSale">Delete Sale</button></li></div>';	
				}
			}
			echo '</ul>
			<input type="hidden" id="hsDeleted" name="hs_deleted">
			</div>';//#hardware_sales.cActivity
			echo'<input type="button" id="another_hSale" value="Add Another Hardware Sale"><br/>
			<h3>Software Sales:</h3>
			<div id="software_sales" class="cActvity">
			
			<ul id="clients_sSales">';
			if($softwareSales !=null){
				foreach($softwareSales as $sSale){
					echo'<div><li>
				<label for="sold_hardware_name[]">Software Name:</label><input type="text" maxlength="45" name="sold_software_name[]" value="'.$sSale['sold_software_name'].'">
				<label for="ss_buying_contact[]">Sold To(Contact): </label><input type="text" maxlength="45" name="ss_buying_contact[]" value="'.$sSale['ss_buying_contact'].'">
				<label for="ss_bc_phone[]">Buyer\'s Phone Number: </label><input type="text" maxlength="45" name="ss_bc_phone[]" value="'.$sSale['ss_bc_phone'].'">
				<label for="ss_selling_date[]">Selling Date: </label><input type="text" maxlength="45" class="datepicker" name="ss_selling_date[]" value="'.$sSale['ss_selling_date'].'">
				<input name="software_sale_id[]" class="ssId" type="hidden" value="'.$sSale['software_sale_id'].'">
				<button class="delete_sSale">Delete Sale</button></li></div>';
				}
			}
			echo '</ul>
			<input type="hidden" id="ssDeleted" name="ss_deleted">
			</div>';//#software_sales.cActivity
			echo'<input type="button" id="another_sSale" value="Add Another Software Sale"><br/>
			<h3>Open Requests:</h3>
			<div id="Requests" class="cActvity">
			
			<ul>';
			if($requests !=null){
				foreach($requests as $request){
					$requestedSkills = getSkillsRequested($conn,$request['requested_skills']);
					echo' <li><span>'.$request['request_name'].'</span><input type="button" class="viewer" data-url="http://localhost/resourcesmgmt/index.php?page=Requests&action=viewRequest&requestid='.$request['request_id'].'" value="View Request Entry">
					<ul>
				<li><div class="caReqKey">Type:</div><div class="caReqValue">'.$request['type'].'</div><div class="caReqKey">Budget:</div><div class="caReqValue">'.$request['budget'].'</div><div class="caReqKey">Length:</div><div class="caReqValue">'.$request['length'].'</div></li>';
				if($request['location']) echo'<li><span>Location: <span><span>'.$request['location'].'</span></li>';
				if($request['request_desc'])echo '<li>Description: '.$request['request_desc'].'</li>';
				if(!empty($requestedSkills)){
					$rsNamesArray = oneColumnDBResult($requestedSkills,'rs_name');
					$rsNamesSet = arrayToStringSet($rsNamesArray);
					echo'<li>Skills Required: '.$rsNamesSet.'</li>';
				}
				else echo'<li>Skills Required: No Skills Specified</li>';
				echo'</ul></li>';
				}
			}
			else echo '<li>No Requests</li>';
			echo '</ul></div>';//#Requests.cActivity
			echo'</div>';//#client_activity
			
			echo'</div>';//.entries
			echo'<input name="client_id" type="hidden" value="'.$result['client_id'].'">
			<div class="submitDiv"><input type="submit" value="Save Changes To Client"></div>
			<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></div></form>';
	}
	else
	{
		displayClientsPage($conn);
	}
}

function displayViewClientPage($conn,$clientid){
	$result = getSpecificClient($conn,$clientid);
	if($result != null){
		$states = getUSStates();
		$contacts = getContacts($conn,$clientid);
		$clientsSows = getClientsActiveSows($conn,$clientid);
		$hardwareSales = getHardwareSales($conn,$clientid);
		$softwareSales = getSoftwareSales($conn,$clientid);
		$requests = getClientsOpenRequests($conn,$clientid);
		echo '<div class="client_manage"><div class="pageTitle"><h1>Edit Client: ';
		if(ISSET($result['client_name']))echo $result['client_name'];
		else echo $result['client_fname'];
		echo'</h1></div>';//.pageTitle
		echo'<form action="http://localhost/resourcesmgmt/index.php?page=Clients&action=saveClientChanges" method="post">
		<div class="entries">';
		echo '<div class="sectionTitle"><h2>Client Details</h2></div>';				
		echo'<div class= "newEntryInfo-Threes"><ul class="viewingPageList">
		<li>Company Name: '.$result['client_name'].'</li>
		<li>Main Phone: '.$result['main_phone'].'</li>
		<li>Address: '.$result['client_address'].'</li>
		</ul></div>';//.newEntryInfo-Threes
		echo'<div class= "newEntryInfo-Threes"><ul class="viewingPageList">
		<li>Brief Notes: '.$result['client_notes'].'</li>
		<li>Status: '.$result['client_status'].'</li>
		</ul></div>';//.newEntryInfo-Threes
		echo'<div class= "newEntryInfo-Threes">
		<div class="skillCategory">Sales Representatives</div>
		<div class="viewingPageBox"><ul class="viewingPageList">';
		//echo $result['sales_rep'];
		if(!is_null($result['sales_rep'])){
			$clientsSRs = getClientsRepsArray($conn,$result['sales_rep']);
			$namesofSalesReps = oneColumnDBResult($clientsSRs,'sr_name');
			
			foreach($namesofSalesReps as $srName){
				echo '<li>'.$srName.'</li>';
			}
		}
		echo'</ul></div>';//.viewingPageBox
		echo'</div>';//.newEntryInfo-Threes
		echo'</div>';//.entries
		echo'<div = class="entries">
		
		<div class="sectionTitle"><h2>Contacts Of:';
		echo $result['client_name'];
		echo'</h2></div><ul  class ="viewingPageList">
		<div name="clients_contacts" id="clients_contacts">';
		if(!empty($contacts)){
			$numOfContacts = count($contacts);
			for($i=0;$i<$numOfContacts;$i++){
			echo'
				<div><li>
				<label for="contact_name[]">Full Name:</label><input type="text" maxlength="45" name="contact_name[]" class="contact_info requiredInput" value="'.$contacts[$i]['contact_name'].'" readonly>
				<label for="office_phone[]"></label>Office Phone#:<input type="text" maxlength="45" name="office_phone[]" class="contact_info" value="'.$contacts[$i]['office_phone'].'" readonly>
				<label for="cell_phone[]"></label>Cell Phone#:<input type="text" maxlength="45" name="cell_phone[]" class="contact_info" value="'.$contacts[$i]['cell_phone'].'" readonly>
				<label for="home_phone[]"></label>Home Phone#:<input type="text" maxlength="45" name="home_phone[]" class="contact_info" value="'.$contacts[$i]['home_phone'].'" readonly>
				<label for="email[]">Email:</label><input type="text" maxlength="45" name="email[]" class="contact_info" value="'.$contacts[$i]['email'].'" readonly><br/>
				<label for="secretary_name[]">Secretary Name:</label><input type="text" maxlength="45" name="secretary_name[]" class="contact_info" value="'.$contacts[$i]['secretary_name'].'" readonly>
				<label for="secretary_phone[]">Secretary Phone:</label><input type="text" maxlength="45" name="secretary_phone[]" class="contact_info" value="'.$contacts[$i]['secretary_phone'].'" readonly>
				<label for="fax[]"></label>Fax:<input type="text" maxlength="45" name="fax[]" class="contact_info" value="'.$contacts[$i]['fax'].'" readonly>
				<label for="contact_notes[]">Brief Notes</label><textarea name="contact_notes[]" class="contact_info" readonly>'.$contacts[$i]['contact_notes'].'</textarea>
				<input name="contact_id[]" class="contactId" type="hidden" value="'.$contacts[$i]['contact_id'].'">';
			}
		}
		echo '</div></ul>';
		echo'</div>';//.entries
		
		echo'<div class="entries">
		<div class="sectionTitle"><h2>'.$result['client_name'].'\'s Activity</h2></div>
		<div id="client_activity">
		<h3>Active SOWS:</h3>
		<div id= "active_sows" class="cActvity">
		
		<ul>';
		if($clientsSows !=null){
			foreach($clientsSows as $sow){
			echo '<li>'.$sow['sow_name'].'<input type="button" class="viewer" data-url="http://localhost/resourcesmgmt/index.php?page=Sows&action=viewSow&sowid='.$sow['sow_id'].'" value="View S.O.W. Entry">
			<ul>
			<li>Summary: '.$sow['summary'].'</li>
			<li>SubContractor Identity: '.$sow['subcontractor_disguise'].'</li>
			<li>';
			if($sow['sow_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$sow['sow_upload'].'" value="View S.O.W. File">';
			echo'</li>
			</ul>
			</li>';
			}
		}
		echo'</ul>
		</div>
		<h3>Hardware Sales:</h3>
		<div id="hardware_sales" class="cActvity">
		
		<ul id="clients_hSales"  class ="viewingPageList">';
		if($hardwareSales !=null){
			foreach($hardwareSales as $hSale){
			echo'<div><li>
			<label for="sold_hardware_name[]">Hardware Name:</label><input type="text" maxlength="45" name="sold_hardware_name[]" value="'.$hSale['sold_hardware_name'].'" readonly>
			<label for="hs_buying_contact[]">Sold To(Contact): </label><input type="text" maxlength="45" name="hs_buying_contact[]" value="'.$hSale['hs_buying_contact'].'" readonly>
			<label for="hs_bc_phone[]">Buyer\'s Phone Number: </label><input type="text" maxlength="45" name="hs_bc_phone[]" value="'.$hSale['hs_bc_phone'].'" readonly>
			<label for="hs_selling_date[]">Selling Date: </label><input type="text" maxlength="45" class="datepicker" name="hs_selling_date[]" value="'.$hSale['hs_selling_date'].'" readonly>
			<input name="hardware_sale_id[]" class="hsId" type="hidden" value="'.$hSale['hardware_sale_id'].'">
			</li></div>';	
			}
		}
		echo '</ul>
		</div>
		<h3>Software Sales:</h3>
		<div id="software_sales" class="cActvity">
		
		<ul id="clients_sSales"  class ="viewingPageList">';
		if($softwareSales !=null){
			foreach($softwareSales as $sSale){
				echo'<div><li>
			<label for="sold_hardware_name[]">Software Name:</label><input type="text" maxlength="45" name="sold_software_name[]" value="'.$sSale['sold_software_name'].'" readonly>
			<label for="ss_buying_contact[]">Sold To(Contact): </label><input type="text" maxlength="45" name="ss_buying_contact[]" value="'.$sSale['ss_buying_contact'].'" readonly>
			<label for="ss_bc_phone[]">Buyer\'s Phone Number: </label><input type="text" maxlength="45" name="ss_bc_phone[]" value="'.$sSale['ss_bc_phone'].'" readonly>
			<label for="ss_selling_date[]">Selling Date: </label><input type="text" maxlength="45" class="datepicker" name="ss_selling_date[]" value="'.$sSale['ss_selling_date'].'" readonly>
			<input name="software_sale_id[]" class="ssId" type="hidden" value="'.$sSale['software_sale_id'].'">
			</li></div>';
			}
		}
		echo '</ul>
		</div>
		<h3>Open Requests:</h3>
		<div id="Requests" class="cActvity">
		
		<ul>';
		if($requests !=null){
			foreach($requests as $request){
				$requestedSkills = getSkillsRequested($conn,$request['requested_skills']);
				echo' <li><span>'.$request['request_name'].'</span><input type="button" class="viewer" data-url="http://localhost/resourcesmgmt/index.php?page=Requests&action=viewRequest&requestid='.$request['request_id'].'" value="View Request Entry">
				<ul>
			<li><div class="caReqKey">Type:</div><div class="caReqValue">'.$request['type'].'</div><div class="caReqKey">Budget:</div><div class="caReqValue">'.$request['budget'].'</div><div class="caReqKey">Length:</div><div class="caReqValue">'.$request['length'].'</div></li>';
			if($request['location']) echo'<li><span>Location: <span><span>'.$request['location'].'</span></li>';
			if($request['request_desc'])echo '<li>Description: '.$request['request_desc'].'</li>';
			if(!empty($requestedSkills)){
				$rsNamesArray = oneColumnDBResult($requestedSkills,'rs_name');
				$rsNamesSet = arrayToStringSet($rsNamesArray);
				echo'<li>Skills Required: '.$rsNamesSet.'</li>';
			}
			else echo'<li>Skills Required: No Skills Specified</li>';
			echo'</ul></li>';
			}
		}
		else echo '<li>No Requests</li>';
		echo '</ul></div>
		</div>';//#client_activity
		echo'</div>';//.entries
		echo '<div class="nextPage">
		<button  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=Clients&action=editClient&clientid='.$result['client_id'].'">Edit Client</a></button>';
		if($_SESSION['role']=="administrator")echo'<button  class="deleteEntry" data-url="http://localhost/resourcesmgmt/index.php?page=Clients&action=deleteClients&clientid='.$result['client_id'].'">Delete Client</button>';
		echo'<button  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=Clients">Back To Clients Page</a></button>
		</div>';
	}
	else
	{
		displayClientsPage($conn);
	}
}

function displayClientsPage($conn){
	echo '<div class="pageTitle"><h1>Clients';
	if(ISSET($_POST['keyphrase']))echo ': Search Results for "'.$_POST['keyphrase'].'"';
	echo'</h1><div>';
	echo '<div class="pageListings">
	<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Clients&action=Search" method="post">
				<input type="submit" name="search" value="Search Clients"><input name="keyphrase" type="text" maxlength="45">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';
	
	$results = getClients($conn);
	if(ISSET($_POST['keyphrase']))$results = clientsSearchFilter($conn,$_POST['keyphrase'],$results);
	//Addition Options for page. not relevant to any particular entry.
	echo'<div class="mgmtTools"><button type="button" id="createClient" class="buttonLink">
	<a name="createClient" href="http://localhost/resourcesmgmt/index.php?page=Clients&action=createClient">Create New Client</a></button></div>';
	
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>Client Name</th>
				<th>Main Phone</th>
				<th>Address</th>
				<th>Sales Representative(s)</th>
				<th>Status</th>
				<th>Action</th>
			</tr>
	</table></div>';	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		for($i=0;$i<count($results);$i++){
			$results[$i]['sales_rep']=getClientsSRsAsString($conn,$results[$i]['sales_rep']);
		echo'<tr>
				<td>'.$results[$i]['client_name'].'</td>
				<td>'.$results[$i]['main_phone'].'</td>
				<td>'.$results[$i]['client_address'].'</td>
				<td>'.$results[$i]['sales_rep'].'</td>
				<td>'.$results[$i]['client_status'].'</td>
				<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Clients&action=viewClient&clientid='.$results[$i]['client_id'].'">View</a>
				<a href="http://localhost/resourcesmgmt/index.php?page=Clients&action=editClient&clientid='.$results[$i]['client_id'].'">Edit</a>';
				if($_SESSION['role']=="administrator")echo' <a class="deleteEntry" href="http://localhost/resourcesmgmt/index.php?page=Clients&action=deleteClient&clientid='.$results[$i]['client_id'].'">Delete</a>';
				echo'</td>
			</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
		echo'</div>';
		
}




function displayReportsPage($conn){
	$openRequests = getOpenRequests($conn);
	$activeSows = getActiveSows($conn);
	$recentActivities = getRecentActivities($conn);
	echo '<div class="pageTitle"><h1>Reports</h1></div>';//div.pageTitle
	echo '<div class="pageListings">';
	echo '<div class="reportsPageHSection">';
	echo '<div><h2>Open Requests</h2></div>';
	echo'<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Requests&action=Search" method="post">
				<input type="submit" name="search" value="Search Requests"><input name="keyphrase" type="text" maxlength="45"><input type="hidden" name="Open" value="Yes">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';//div.searchbar
	
echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>Request Name</th>
				<th>Request Description</th>
				<th>Requester</th>
				<th>Type</th>
				<th>Budget</th>
				<th>Length</th>
				<th>Location</th>
				<th>Actions</th>
			</tr>
	</table>		
	</div>';	
			
	if($openRequests){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($openRequests as $request){
			echo'<tr>
					<td>'.$request['request_name'].'</td>
					<td>'.$request['request_desc'].'</td>
					<td>'.$request['client_name'].'</td>
					<td>'.$request['type'].'</td>
					<td>'.$request['budget'].'</td>
					<td>'.$request['length'].'</td>
					<td>'.$request['location'].'</td>
					<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=viewRequest&requestid='.$request['request_id'].'">View</a> 
					<a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=editRequest&requestid='.$request['request_id'].'">Edit</a></td>
				</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
		
	echo'</div>';//div.reportsPageHSection
	echo '<div class="reportsPageHSection">';
	echo '<div><h2>Open Sows</h2></div>';
	echo'<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Sows&action=Search" method="post">
				<input type="submit" name="search" value="Search S.O.W.s"><input name="keyphrase" type="text" maxlength="45"><input type="hidden" name="Active" value="Yes">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';//div.searchbar
		
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>S.O.W. Name</th>
				<th>Brief Summary</th>
				<th>Client</th>
				<th>Client Primary Contact</th>
				<th>S.O.W.</th>
				<th>Start Date</th>
				<th>End Date</th>
				<th>Billing Rate</th>
				<th>Subcontractor</th>
				<th>Actions</th>
			</tr>
	</table>		
	</div>';	
			
	if($activeSows){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($activeSows as $sow){
			echo'<tr>
					<td>'.$sow['sow_name'].'</td>
					<td>'.$sow['summary'].'</td>
					<td>'.$sow['client_name'].'</td>
					<td>'.$sow['primary_contact'].'</td>
					<td>';
					if($sow['sow_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$sow['sow_upload'].'" value="View S.O.W. File">';
					echo '</td>
					<td>'.$sow['start_date'].'</td>
					<td>'.$sow['end_date'].'</td>
					<td>'.$sow['billing'].'</td>
					<td>'.$sow['subcontractor_disguise'].'</td>
					<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=viewSow&sowid='.$sow['sow_id'].'">View</a> 
						<a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=editSow&sowid='.$sow['sow_id'].'">Edit</a></td>
				</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
	
	
	echo'</div>';
	echo '<div><h2>Recent Activity</h2></div>';
	echo '<div class="reportsPageHSection">';
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>Activity Description</th>
				<th>Date And Time Of Action</th>
			</tr>
	</table></div>';	
			
	if($recentActivities){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($recentActivities as $activity){
			echo'<tr>
					<td>'.$activity['activity_desc'].'</td>
					<td>'.$activity['activity_date'].'</td>
					</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
	echo '</div>';//div.reportsPageHSection
	echo '<br/></div>';//pageListings
	echo '<div class="reportsPageSection">';
	echo'<div class="entries">';
	echo '<div class="sectionTitle"><h2>Search For Resources With Specific Skill(s) And/Or Attribute(s)</h2></div>';
	$requestableSkills =  getRequestableSkills($conn);
	$numOfSkills = count($requestableSkills);
	$halfWayPoint = floor($numOfSkills/2);
	echo'<div class= "newEntryInfo-Threes"><ul>';
	for($i=0;$i<$halfWayPoint;$i++){
		echo '<li>'.$requestableSkills[$i]['rs_name'].'</li>';
	}
	echo'</ul></div>';//div.newEntryInfo-Threes
	echo'<div class= "newEntryInfo-Threes"><ul>';
	for($i=$halfWayPoint;$i<$numOfSkills;$i++){
		echo '<li>'.$requestableSkills[$i]['rs_name'].'</li>';
	}
	echo'</ul></div>';//div.newEntryInfo-Threes
			
	echo'<div class= "newEntryInfo-Threes">
	<form id="findQRes" action="http://localhost/resourcesmgmt/index.php?page=Resources&action=filterResources" method="post">
	
	<div id="skillsWanted">
	<div class="requiredSkill">
	<span>*Check the box if skill needs to be a Primary</span><br>
	Skill: <input name="wantedSkill[]" class="wantedSkill"  type="text" maxlength="45">
	<select name="conditional[]">
	<option value="Must Have">Must Have</option>
	<option value="A Plus">A Plus</option>
	</select>
	<input type="checkbox" name="is_pskill[]" class="is_pskill"></div>
	</div>
	<input type="button" id="another_skill" value="Add Skill"><br/>
	<input type="submit" value="Find Qualified Resources"><input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form></div>';
	echo '</div>';//div.newEntryInfo-Threes
	echo '</div>';//div.entries
	echo '</div>';//div.reportsPageSection
	
}

function displayCreateRequestPage($conn){
	$clients = getClients($conn);
	$resources = getResources($conn);
	$rSkills = getRequestableSkills($conn);
	$skillCount = count($rSkills);
	$maxSkillsPerColumn = ceil($skillCount/(float)4);
	$lastColStartIndex = ($maxSkillsPerColumn * 3);
	echo '<div class="request_manage"><div class="pageTitle"><h1>Add A Request</h1></div>';
	echo' <form action="http://localhost/resourcesmgmt/index.php?page=Requests&action=addRequest" method="post"
		enctype="multipart/form-data">';
		echo'<div class="entries">';
		echo '<div class="sectionTitle"><h2>Request Details</h2></div>';				
		echo'<div class= "newEntryInfo-Threes">
		<span class="input_name">Request Name:</span><br/>
		<span class="input_value"><input name="request_name" class="requiredInput" type="text" maxlength="45"></span><br/>
		<span class="input_name">Request Description And Notes:</span><br/>
		<span class="input_value"><textarea name="request_desc" rows="4" cols="40"  class="requiredInput"></textarea><br/>
		<span class="input_name">Requester(Client):</span><br/>
		<select name="client_id" class="requiredInput">';
		echo '<option value="" class="null">N/A</option>';
		foreach($clients as $client){
		echo '<option value="'.$client['client_id'].'">'.$client['client_name'].'</option>';
		}
		echo '</select><br/>
		</div>
		<div class= "newEntryInfo-Threes">
		<span class="input_name">Type:</span><br/>
		<span class="input_value"><input name="type" type="text" maxlength="45"></span><br/>
		<span class="input_name">Budget:</span><br/>
		<span class="input_value"><input name="budget" type="text" maxlength="45"></span><br/>
		<span class="input_name">Length</span><br/>
		<span class="input_value"><input name="length" type="text" maxlength="45"></span><br/>
		<span class="input_name">Location</span><br/>
		<span class="input_value"><textarea name="location" rows="4" cols="40"></textarea><br/>
		</div>
		<div class= "newEntryInfo-Threes">
		<span class="input_name">Resumes Sent:</span><br/>
		<div class="toggleCheckboxes">
		<span class="input_value">';
		
		foreach($resources as $resource){
		$resName="";
			if($resource['resource_name'])$resName= $resource['resource_name'].': ';
			$resName = $resName.$resource['resource_fname'];
			if($resource['resource_lname'])$resName  = $resName.' '.$resource['resource_lname'];
			echo '<button type="button" class="tglBtn"><input type="checkbox" name="resumes_sent[]" class="tglCB" value="'.$resource['resource_id'].'">'.$resName.'
			<input type="button" class="viewer displayRS" data-url="http://localhost/resourcesmgmt/index.php?page=Resources&action=editResource&resourceid='.$resource['resource_id'].'" value="View/Edit">
			</button><br/>';
		}
		echo'</span><br/>
		</div>';//.toggleCheckboxes
		echo'</div>';//.newEntry-Threes
		echo'</div>';//.entries
		echo'<div class="entries">';
		echo '<div class="sectionTitle"><h2>Requested Skills</h2></div>';		
		for($n = 0;$n < 3;$n++){
			echo'<div class= "newEntryInfo-Fours">';
			for($i = 0;$i < $maxSkillsPerColumn;$i++){
				echo'<label for="rs_id[]">'.$rSkills[$maxSkillsPerColumn*$n+$i]['rs_name'].'</label><input class="requestSkill" type="checkbox" name="rs_id[]" value="'.$rSkills[$maxSkillsPerColumn*$n+$i]['rs_id'].'"><br/>';
			}
			echo '</div>';
		}
		echo'<div class= "newEntryInfo-Fours">';
		for($i = $lastColStartIndex;$i < $skillCount;$i++){		
			echo'
			<label for="rs_id[]">'.$rSkills[$i]['rs_name'].'</label><input class="requestSkill" type="checkbox" name="rs_id[]" value="'.$rSkills[$i]['rs_id'].'"><br/>';
			
		}
		echo'<div class="newOptions"></div><input class="addNewOption" data-giveName="new_rs[]" type="button" value="Add One"></div>';//.newEntryInfo-Fours
		echo '</div>';//entries
		echo'<div class="submitDiv"><input type="submit" value="Save New Request"></div>
			<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
			echo'</div>';
}

function displayEditRequestPage($conn,$requestid){
	$request = getSpecificRequest($conn,$requestid);
	if($request != null){
		$clients = getClients($conn);
		$resources = getResources($conn);
		$requestedSkills = explode(',',$request['requested_skills']);
		$rSkills = skillListAndRequested($conn,$requestid);
		$skillCount = count($rSkills);
		$maxSkillsPerColumn = ceil($skillCount/(float)4);
		$lastColStartIndex = ($maxSkillsPerColumn * 3);
		echo '<div class="request_manage"><div class="pageTitle"><h1>Edit Request: '.$request['request_name'].'</h1></div>';
		echo' <form action="http://localhost/resourcesmgmt/index.php?page=Requests&action=saveRequestChanges" method="post"
			enctype="multipart/form-data">';
			echo '<div class="entries">';
			echo '<div class="sectionTitle"><h2>Request Details</h2></div>';				
			echo'<div class= "newEntryInfo-Threes">
			<span class="input_name">Request Name:</span><br/>
			<span class="input_value"><input name="request_name" class="requiredInput" type="text" maxlength="45" value="'.$request['request_name'].'"></span><br/>
			<span class="input_name">Request Description And Notes:</span><br/>
			<span class="input_value"><textarea name="request_desc" rows="4" cols="40" class="requiredInput">'.$request['request_desc'].'</textarea><br/>
			<span class="input_name">Requester(Client):</span><br/>
			<select name="client_id" class="requiredInput">';
			echo '<option value="" class="null">N/A</option>';
			foreach($clients as $client){
			echo '<option value="'.$client['client_id'].'"';
			if($client['client_id']==$request['client_id'])echo ' selected';
			echo '>'.$client['client_name'].'</option>';
			}
			echo '</select><br/>
			<span class="input_name">Status:</span><br/>
			<select name="request_status">
			<option value="Open"';
			if($request['request_status']=="Open")echo ' selected';
			echo'>Open</option>
			<option value="Closed"';
			if($request['request_status']=="Closed")echo ' selected';
			echo'>Closed</option>
			<option value="To Be Removed"';
			if($request['request_status']=="To Be Removed")echo ' selected';
			echo'>To Be Removed</option>
			</select><br/>
			</div>
			<div class= "newEntryInfo-Threes">
			<span class="input_name">Type:</span><br/>
			<span class="input_value"><input name="type" type="text" maxlength="45" value="'.$request['type'].'"></span><br/>
			<span class="input_name">Budget:</span><br/>
			<span class="input_value"><input name="budget" type="text" maxlength="45" value="'.$request['budget'].'"></span><br/>
			<span class="input_name">Length:</span><br/>
			<span class="input_value"><input name="length" type="text" maxlength="45" value="'.$request['length'].'"></span><br/>
			<span class="input_name">Location</span><br/>
			<span class="input_value"><textarea name="location" rows="4" cols="40">'.$request['location'].'</textarea><br/>
			</div>
			<div class= "newEntryInfo-Threes">
		<span class="input_name">Resumes Sent:</span><br/>
		<div class="toggleCheckboxes">
		<span class="input_value">';
		$resumesSent = explode(",",$request['resumes_sent']);
		foreach($resources as $resource){
		$resName="";
			if($resource['resource_name'])$resName= $resource['resource_name'].': ';
			$resName = $resName.$resource['resource_fname'];
			if($resource['resource_lname'])$resName  = $resName.' '.$resource['resource_lname'];
			echo '<button type="button" class="tglBtn';
			if(in_array($resource['resource_id'],$resumesSent)) echo ' checked';
			echo'"><input type="checkbox" name="resumes_sent[]" class="tglCB" value="'.$resource['resource_id'].'"';
			if(in_array($resource['resource_id'],$resumesSent)) echo ' checked';
			echo'>'.$resName.'
			<input type="button" class="viewer displayRS" data-url="http://localhost/resourcesmgmt/index.php?page=Resources&action=editResource&resourceid='.$resource['resource_id'].'" value="View/Edit">
			</button><br/>';
		}
		echo'</span><br/>
		</div>';//.toggleCheckboxes
		echo'</div>';//.newEntryInfo-Threes
		echo'</div>';//.entries
			echo'<div class="entries">';
			echo '<div class="sectionTitle"><h2>Requested Skills</h2></div>';				
			for($n = 0;$n < 3;$n++){
				echo'<div class= "newEntryInfo-Fours">';
				for($i = 0;$i < $maxSkillsPerColumn;$i++){
					$rsId = $rSkills[$maxSkillsPerColumn*$n+$i]['rs_id'];
					$rsName = $rSkills[$maxSkillsPerColumn*$n+$i]['rs_name'];
					echo'<label for="rs_id[]">'.$rsName.'</label><input class="requestSkill" type="checkbox" name="rs_id[]" value="'.$rsId.'"';
					if(in_array($rsId,$requestedSkills))echo ' checked';
					echo'><br/>';
				}
				echo '</div>';
			}
			echo'<div class= "newEntryInfo-Fours">';
			for($i = $lastColStartIndex;$i < $skillCount;$i++){		
				$rsId = $rSkills[$i]['rs_id'];
				$rsName = $rSkills[$i]['rs_name'];
				echo'
				<label for="rs_id[]">'.$rsName.'</label><input class="requestSkill" type="checkbox" name="rs_id[]" value ="'.$rsId.'" ';
					if(in_array($rsId,$requestedSkills))echo ' checked';
					echo'><br/>';
				
			}
			echo'<div class="newOptions"></div><input class="addNewOption" data-giveName="new_rs[]" type="button" value="Add One"></div>';//.newEntryInfo-Fours
			echo '</div>';//entries
			
			echo '<input type="hidden" name="request_id" value="'.$request['request_id'].'">';
			echo'<div class="submitDiv"><input type="submit" value="Save Request Changes"></div>
				<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
			echo'</div>';	
		}
		else{
			displayRequestsPage($conn);
		}
}

function displayViewRequestPage($conn,$requestid){
	$request = getSpecificDetailedRequest($conn,$requestid);
	if($request != null){
		$resources = getResources($conn);
		$requestedSkills = explode(',',$request['requested_skills']);
		$rSkills = skillListAndRequested($conn,$requestid);
		$skillCount = count($rSkills);
		$maxSkillsPerColumn = ceil($skillCount/(float)4);
		$lastColStartIndex = ($maxSkillsPerColumn * 3);
		echo '<div class="request_manage"><div class="pageTitle"><h1>Edit Request: '.$request['request_name'].'</h1></div>';
		echo' <div class="entries">';
		echo '<div class="sectionTitle"><h2>Request Details</h2></div>';				
			echo'<div class= "newEntryInfo-Threes"><ul class="viewingPageList">
			<li>Request Name: '.$request['request_name'].'</li>
			<li>Request Description: '.$request['request_desc'].'</li>
			<li>Requester(Client): '.$request['client_name'].'</li>
			<li>Status: '.$request['request_status'].'</li>
			</ul></div>
			<div class= "newEntryInfo-Threes"><ul class="viewingPageList">
			<li>Type: '.$request['type'].'</li>
			<li>Budget: '.$request['budget'].'</li>
			<li>Length: '.$request['length'].'</li>
			<li>Location: '.$request['location'].'</li>
			</ul></div>
			<div class= "newEntryInfo-Threes">Resumes Sent:<div class="viewingPageBox"><ul class="viewingPageList">';
			$resumesSent = explode(",",$request['resumes_sent']);
			//$namesOfResumesSent = namesOfResumesSent($conn,$request['resumes_sent']);
			foreach($resources as $resource){
				if(in_array($resource['resource_id'],$resumesSent)){
					$resName="";
					if($resource['resource_name'])$resName= $resource['resource_name'].': ';
					$resName = $resName.$resource['resource_fname'];
					if($resource['resource_lname'])$resName  = $resName.' '.$resource['resource_lname'];
					echo' <li> '.$resName.'
					<button class="viewer displayRS" data-url="http://localhost/resourcesmgmt/index.php?page=Resources&action=editResource&resourceid='.$resource['resource_id'].'">View/Edit
					</button></li>';
				}
			}
			
			echo'</ul></div>
		</div>';//entries
		echo '<div class="entries">';
		echo '<div class="sectionTitle"><h2>Requested Skills</h2></div>';				
		for($n = 0;$n < 3;$n++){
			echo'<div class= "newEntryInfo-Fours"><ul class ="viewingPageList">';
			for($i = 0;$i < $maxSkillsPerColumn;$i++){
				$rsId = $rSkills[$maxSkillsPerColumn*$n+$i]['rs_id'];
				$rsName = $rSkills[$maxSkillsPerColumn*$n+$i]['rs_name'];
				if(in_array($rsId,$requestedSkills))echo '<li>'.$rsName.'</li>';
			}
			echo '</ul></div>';
		}
		echo'<div class= "newEntryInfo-Fours"><ul class ="viewingPageList">';
		for($i = $lastColStartIndex;$i < $skillCount;$i++){		
			$rsId = $rSkills[$i]['rs_id'];
			$rsName = $rSkills[$i]['rs_name'];
			if(in_array($rsId,$requestedSkills))echo '<li>'.$rsName.'</li>';
		}
		echo '</ul></div>';
		echo '</div>';//entries
		echo '<div class="nextPage">
		<button  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=editRequest&requestid='.$request['request_id'].'">Edit Request</a></button>';
		if($_SESSION['role']=="administrator")echo'<button  class="deleteEntry" data-url="http://localhost/resourcesmgmt/index.php?page=Requests&action=deleteRequest&requestid='.$request['request_id'].'">Delete Request</button>';
		echo'<button  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=Requests">Back To Requests Page</a></button>
		</div>';	
		}
		else{
			displayRequestsPage($conn);
		}
}

function displayRequestsPage($conn){
	echo '<div class="pageTitle"><h1>Requests';
	if(ISSET($_POST['keyphrase']))echo ': Search Results for "'.$_POST['keyphrase'].'"';
	echo'</h1><div>';
	echo '<div class="pageListings">
	<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Requests&action=Search" method="post">
				<input type="submit" name="search" value="Search"><input name="keyphrase" type="text" maxlength="45">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';
	if(ISSET($_POST['Open']))$results = getOpenRequests($conn);	
	else $results = getRequests($conn);
	if(ISSET($_POST['keyphrase']))$results = requestsSearchFilter($conn,$_POST['keyphrase'],$results);
	//Addition Options for page. not relevant to any particular entry.
	echo'<div class="mgmtTools"><button type="button" id="createSow" style="background:none;"  class="buttonLink">
	<a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=createRequest">Create New Requests</a></button></div>';//div.mgmtTools
	
	echo '<div class="table">';
	echo'<div class="tableHead">
			<table  class="tableHead">
					<tr>
						<th>Request Name</th>
						<th>Request Description</th>
						<th>Requester</th>
						<th>Type</th>
						<th>Budget</th>
						<th>Length</th>
						<th>Location</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
			</table></div>';	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($results as $request){
			echo'<tr>
					<td>'.$request['request_name'].'</td>
					<td>'.$request['request_desc'].'</td>
					<td>'.$request['client_name'].'</td>
					<td>'.$request['type'].'</td>
					<td>'.$request['budget'].'</td>
					<td>'.$request['length'].'</td>
					<td>'.$request['location'].'</td>
					<td>'.$request['request_status'].'</td>
					<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=viewRequest&requestid='.$request['request_id'].'">View</a> 
					<a href="http://localhost/resourcesmgmt/index.php?page=Requests&action=editRequest&requestid='.$request['request_id'].'">Edit</a>';
				if($_SESSION['role']=="administrator")echo' <a class="deleteEntry" href="http://localhost/resourcesmgmt/index.php?page=Requests&action=deleteRequest&requestid='.$request['request_id'].'">Delete</a>';
				echo'</td>
				</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table

	echo'</div>';//div.pageListings
	
}


function displayCreateResourcePage($conn){
	$pskills = primarySkillsList($conn,null);
	echo '<div class="pageTitle"><h1>Add A Resource</h1><div>';
	echo'<form action="http://localhost/resourcesmgmt/index.php?page=Resources&action=addResource" method="post"
	enctype="multipart/form-data">
		<div class"entries">
		<div class="sectionTitle"><h2>Resource Details</h2></div>
		<div class= "newEntryInfo-Fours">
		<span class="input_name">Company Name:</span><br/>
		<span class="input_value"><input name="resource_name" type="text" maxlength="45"></span><br/>
		<span class="input_name">First Name(Main Contact):</span><br/>
		<span class="input_value"><input name="resource_fname" class="requiredInput" type="text" maxlength="45"></span><br/>
		<span class="input_name">Last Name:</span><br/>
		<span class="input_value"><input name="resource_lname" type="text" maxlength="45"></span><br/>
		<span class="input_name">Type:</span><br/>
		<span class="input_value"><input name="type" type="text" maxlength="45"></span><br/>
		<span class="input_name">Primary Contact #:</span><br/>
			<span class="input_value"><input name="phone1" type="text" maxlength="45"></span><br/>
			<span class="input_name">Other Contact #:</span><br/>
			<span class="input_value"><input name="phone2" type="text" maxlength="45"></span><br/>
		</div>
		<div class= "newEntryInfo-Fours">
		<span class="input_name">Address:</span><br/>
		<textarea name="resource_address" rows="3" class="contact_info"></textarea><br/>
		<span class="input_name">Email Address:</span><br/>
		<span class="input_value"><input name="email" type="text" maxlength="45"></span><br/>
		</div>
		<div class= "newEntryInfo-Fours">
		<span class="input_name">Resume-ISSI Version:</span><br/>
		<span class="input_value"><input type="file" name="resume_issi" id="file"><br/>
		<span class="input_name">Resume-Original Version:</span><br/>
		<span class="input_value"><input type="file" name="resume" id="file"><br/>
		<span class="input_name">Notes:</span><br/>
		<span class="input_value"><textarea name="resource_notes" rows="6" cols="40"></textarea><br/>
		</div>';
		echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Primary Skills</div>';
			echo '<div class="toggleCheckboxes">';
			$numpskills = count($pskills);
			for($i=0;$i<$numpskills;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="primary_skills[]" class="tglCB" value="'.$pskills[$i]['rs_id'].'">'.$pskills[$i]['rs_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_primary_skills[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours</div>';
			echo'</div>';//.entries
			$dbtypes = databaseTypesList($conn,null);
			$platforms = oSPlatformsList($conn,null);
			$skills = otherSkillsList($conn,null);
			$ac= applicationCodesList($conn,null);
			$hardwares = hardwaresList($conn,null);
			$ibmp = iBMProductsList($conn,null);
			$isvp = iSVProductsList($conn,null);
			$titles = titlesList($conn,null);
			$txns = transactionsList($conn,null);
			
			
			echo '<div class="entries">';
			echo '<div class="sectionTitle"><h2>Expertise and Skills</h2></div>';
			echo '<div class="resSkillGrouper">';
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Operating Systems</div>';
			echo '<div class="toggleCheckboxes">';
			$numplatforms = count($platforms);
			for($i=0;$i<$numplatforms;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="os_platforms[]" class="tglCB" value="'.$platforms[$i]['platform_id'].'">'.$platforms[$i]['platform_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_os_platforms[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Database Types</div>';
			echo '<div class="toggleCheckboxes">';
			$numdbtypes = count($dbtypes);
			for($i=0;$i<$numdbtypes;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="database_types[]" class="tglCB" value="'.$dbtypes[$i]['dt_id'].'">'.$dbtypes[$i]['dt_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_database_types[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Transactions</div>';
			echo '<div class="toggleCheckboxes">';
			$numtxns = count($txns);
			for($i=0;$i<$numtxns;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="transactions[]" class="tglCB" value="'.$txns[$i]['transaction_id'].'">'.$txns[$i]['transaction_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_transactions[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Application Codes</div>';
			echo '<div class="toggleCheckboxes">';
			$numac = count($ac);
			for($i=0;$i<$numac;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="application_codes[]" class="tglCB" value="'.$ac[$i]['ac_id'].'">'.$ac[$i]['ac_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_application_codes[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			
			echo'</div>';//resSkillGrouper
			
			
			echo '<div class="resSkillGrouper">';
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">ISV Products</div>';
			echo '<div class="toggleCheckboxes">';
			$numisvp = count($isvp);
			for($i=0;$i<$numisvp;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="isv_products[]" class="tglCB" value="'.$isvp[$i]['isvp_id'].'">'.$isvp[$i]['isvp_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_isv_products[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">IBM Products</div>';
			echo '<div class="toggleCheckboxes">';
			$numibmp = count($ibmp);
			for($i=0;$i<$numibmp;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="ibm_products[]" class="tglCB" value="'.$ibmp[$i]['ibmp_id'].'">'.$ibmp[$i]['ibmp_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_ibm_products[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Hardware</div>';
			echo '<div class="toggleCheckboxes">';
			$numhardwares = count($hardwares);
			for($i=0;$i<$numhardwares;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="hardwares[]" class="tglCB" value="'.$hardwares[$i]['hardware_id'].'">'.$hardwares[$i]['hardware_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_hardwares[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Titles</div>';
			echo '<div class="toggleCheckboxes">';
			$numtitles = count($titles);
			for($i=0;$i<$numtitles;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="titles[]" class="tglCB" value="'.$titles[$i]['title_id'].'">'.$titles[$i]['title'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_titles[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo'</div>';//.resSkillGrouper
			
			echo '<div class="resSkillGrouper">';
			
			echo '<div class="newEntryInfo-Fours">
			<div class="skillCategory">Other Skills</div>';
			echo '<div class="toggleCheckboxes">';
			$numskills = count($skills);
			for($i=0;$i<$numskills;$i++){
			echo '<button type="button" class="tglBtn">
			<input type="checkbox" name="other_skills[]" class="tglCB" value="'.$skills[$i]['skill_id'].'">'.$skills[$i]['skill_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_other_skills[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo'</div>';//.resSkillGrouper
			echo'</div>';//.entries
			
		echo '<div class="submitDiv"><input type="submit" value="Save New Resource"></div>
	<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
}


function displayEditResourcePage($conn,$resourceid){
	$result = getSpecificResource($conn,$resourceid);
	if($result != null){
		$pskills = primarySkillsList($conn,$result['resource_id']);
		echo '<div class="pageTitle"><h1>Edit Resource:';
		if(ISSET($result['resource_name']))echo $result['resource_name'];
		else echo $result['resource_fname'];
		echo'</h1></div>';
		echo'<form action="http://localhost/resourcesmgmt/index.php?page=Resources&action=saveResourceChanges" method="post"
			enctype="multipart/form-data">
			<div class="entries">';
			echo '<div class="sectionTitle"><h2>Resource Details</h2></div>';
			echo'<div class= "newEntryInfo-Fours">
			<span class="input_name">Company Name:</span><br/>
			<span class="input_value"><input name="resource_name" type="text" maxlength="45" value="'.$result['resource_name'].'"></span><br/>
			<span class="input_name">First Name(Main Contact):</span><br/>
			<span class="input_value"><input name="resource_fname" class="requiredInput" type="text" maxlength="45" value="'.$result['resource_fname'].'"></span><br/>
			<span class="input_name">Last Name:</span><br/>
			<span class="input_value"><input name="resource_lname" type="text" maxlength="45" value="'.$result['resource_lname'].'"></span><br/>
			<span class="input_name">Status:</span><br/>
			<span class="input_value">
			<select name="resource_status">
			<option value="Available"';
			if($result['resource_status']=="Available")echo ' selected';
			echo '>Available</option>
			<option value="Not Available"';
			if($result['resource_status']=="Not Available")echo ' selected';
			echo '>Not Available</option>
			<option value="To Be Removed"';
			if($result['resource_status']=="To Be Removed")echo ' selected';
			echo '>To Be Removed</option>
			</select>
			</span><br/>
			</div>
			<div class= "newEntryInfo-Fours">
			<span class="input_name">Primary Contact #:</span><br/>
			<span class="input_value"><input name="phone1" type="text" maxlength="45" value="'.$result['phone1'].'"></span><br/>
			<span class="input_name">Other Contact #:</span><br/>
			<span class="input_value"><input name="phone2" type="text" maxlength="45" value="'.$result['phone2'].'"></span><br/>
			<span class="input_name">Address:</span><br/>
			<textarea name="resource_address" rows="3" class="contact_info">'.$result['resource_address'].'</textarea><br/>
			<span class="input_name">Email Address:</span><br/>
			<span class="input_value"><input name="email" type="text" maxlength="45" value="'.$result['email'].'"></span><br/>
			
			
			</div>
			<div class= "newEntryInfo-Fours">
			<span class="input_name">Resume-ISSI Version:</span>';
			echo'<div class="upload_section">';
			if($result['resume_issi_upload']){
				echo'<br/><input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_issi_uploads&filename='.$result['resume_issi_upload'].'" value="View Resume ISSI"><br/>
				<span>Uploaded on: '.$result['resume_issi_uploaded_date'].'<input type="hidden" name="resume_issi_uploaded_date" value="'.$result['resume_issi_uploaded_date'].'"></span><br/>
				<label for="remove_resume_issi">Remove Upload:</label><input name="remove_resume_issi"  id="remove_resume_issi" type="checkbox">';
			}
			echo'<br/>
			<span class="input_value"><input type="file" name="resume_issi" id="file"><br/>';
			echo '</div>';
			echo'<span class="input_name">Resume-Original Version:</span>';
			echo'<div class="upload_section">';
			if($result['resume_upload']){
			echo'<br/><input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_uploads&filename='.$result['resume_upload'].'" value="View Resume"><br/>
				<span>Uploaded on: '.$result['resume_uploaded_date'].'<input type="hidden" name="resume_uploaded_date" value="'.$result['resume_uploaded_date'].'"></span><br/>
				<label for="remove_resume">Remove Upload:</label><input name="remove_resume"  id="remove_resume" type="checkbox">';
			}
			echo'<br/>
			<span class="input_value"><input type="file" name="resume" id="file"><br/>';
			echo '</div>';
			echo'<span class="input_name">Notes:</span><br/>
			<span class="input_value"><textarea name="resource_notes" rows="6" cols="40">'.$result['resource_notes'].'</textarea>
			</div>';
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Primary Skills</div>';
			echo '<div class="toggleCheckboxes">';
			$numpskills = count($pskills);
			for($i=0;$i<$numpskills;$i++){
			echo '<button type="button" class="tglBtn';
			if($pskills[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="primary_skills[]" class="tglCB" value="'.$pskills[$i]['rs_id'].'"';
			if($pskills[$i]['resource_id']!=null)echo' checked';
			echo'>'.$pskills[$i]['rs_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_primary_skills[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours</div>';
			echo'</div>';
			
			
			$dbtypes = databaseTypesList($conn,$result['resource_id']);
			$platforms = oSPlatformsList($conn,$result['resource_id']);
			$skills = otherSkillsList($conn,$result['resource_id']);
			$ac= applicationCodesList($conn,$result['resource_id']);
			$hardwares = hardwaresList($conn,$result['resource_id']);
			$ibmp = iBMProductsList($conn,$result['resource_id']);
			$isvp = iSVProductsList($conn,$result['resource_id']);
			$titles = titlesList($conn,$result['resource_id']);
			$txns = transactionsList($conn,$result['resource_id']);
			
			echo '<div class="entries">';
			echo '<div class="sectionTitle"><h2>Expertise and Skills</h2></div>';
			echo '<div class="resSkillGrouper">';
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Operating Systems</div>';
			echo '<div class="toggleCheckboxes">';
			$numplatforms = count($platforms);
			for($i=0;$i<$numplatforms;$i++){
			echo '<button type="button" class="tglBtn';
			if($platforms[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="os_platforms[]" class="tglCB" value="'.$platforms[$i]['platform_id'].'"';
			if($platforms[$i]['resource_id']!=null)echo' checked';
			echo'>'.$platforms[$i]['platform_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_os_platforms[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Database Types</div>';
			echo '<div class="toggleCheckboxes">';
			$numdbtypes = count($dbtypes);
			for($i=0;$i<$numdbtypes;$i++){
			echo '<button type="button" class="tglBtn';
			if($dbtypes[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="database_types[]" class="tglCB" value="'.$dbtypes[$i]['dt_id'].'"';
			if($dbtypes[$i]['resource_id']!=null)echo' checked';
			echo'>'.$dbtypes[$i]['dt_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_database_types[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Transactions</div>';
			echo '<div class="toggleCheckboxes">';
			$numtxns = count($txns);
			for($i=0;$i<$numtxns;$i++){
			echo '<button type="button" class="tglBtn';
			if($txns[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="transactions[]" class="tglCB" value="'.$txns[$i]['transaction_id'].'"';
			if($txns[$i]['resource_id']!=null)echo' checked';
			echo'>'.$txns[$i]['transaction_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_transactions[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Application Codes</div>';
			echo '<div class="toggleCheckboxes">';
			$numac = count($ac);
			for($i=0;$i<$numac;$i++){
			echo '<button type="button" class="tglBtn';
			if($ac[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="application_codes[]" class="tglCB" value="'.$ac[$i]['ac_id'].'"';
			if($ac[$i]['resource_id']!=null)echo' checked';
			echo'>'.$ac[$i]['ac_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_application_codes[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo'</div>';//. resSkillGrouper
			
			echo '<div class="resSkillGrouper">';
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">ISV Products</div>';
			echo '<div class="toggleCheckboxes">';
			$numisvp = count($isvp);
			for($i=0;$i<$numisvp;$i++){
			echo '<button type="button" class="tglBtn';
			if($isvp[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="isv_products[]" class="tglCB" value="'.$isvp[$i]['isvp_id'].'"';
			if($isvp[$i]['resource_id']!=null)echo' checked';
			echo'>'.$isvp[$i]['isvp_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_isv_products[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">IBM Products</div>';
			echo '<div class="toggleCheckboxes">';
			$numibmp = count($ibmp);
			for($i=0;$i<$numibmp;$i++){
			echo '<button type="button" class="tglBtn';
			if($ibmp[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="ibm_products[]" class="tglCB" value="'.$ibmp[$i]['ibmp_id'].'"';
			if($ibmp[$i]['resource_id']!=null)echo' checked';
			echo'>'.$ibmp[$i]['ibmp_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_ibm_products[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Hardware</div>';
			echo '<div class="toggleCheckboxes">';
			$numhardwares = count($hardwares);
			for($i=0;$i<$numhardwares;$i++){
			echo '<button type="button" class="tglBtn';
			if($hardwares[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="hardwares[]" class="tglCB" value="'.$hardwares[$i]['hardware_id'].'"';
			if($hardwares[$i]['resource_id']!=null)echo' checked';
			echo'>'.$hardwares[$i]['hardware_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_hardwares[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Titles</div>';
			echo '<div class="toggleCheckboxes">';
			$numtitles = count($titles);
			for($i=0;$i<$numtitles;$i++){
			echo '<button type="button" class="tglBtn';
			if($titles[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="titles[]" class="tglCB" value="'.$titles[$i]['title_id'].'"';
			if($titles[$i]['resource_id']!=null)echo' checked';
			echo'>'.$titles[$i]['title'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_titles[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			
			echo'</div>';//.resSkillGrouper
			
			echo '<div class="resSkillGrouper">';
			
			echo '<div class= "newEntryInfo-Fours">
			<div class="skillCategory">Other Skills</div>';
			echo '<div class="toggleCheckboxes">';
			$numskills = count($skills);
			for($i=0;$i<$numskills;$i++){
			echo '<button type="button" class="tglBtn';
			if($skills[$i]['resource_id']!=null) echo ' checked';
			echo'">
			<input type="checkbox" name="other_skills[]" class="tglCB" value="'.$skills[$i]['skill_id'].'"';
			if($skills[$i]['resource_id']!=null)echo' checked';
			echo'>'.$skills[$i]['skill_name'].'
			</button><br/>';
			}
			echo'</div>';//.toggleCheckboxes
			echo'<div class="skillsToAdd"></div><input class="addOption" data-giveName="new_other_skills[]" type="button" value="Add One">
			</div>';//.newEntryInfo-Fours
			echo'</div>';//.resSkillGrouper
			echo'</div>';//.entries
			
			$pskills = getPrimarySkills($conn,$result['resource_id']);
			
			$pskills = getPrimarySkills($conn,$result['resource_id']);
			$dbtypes = getDatabaseTypes($conn,$result['resource_id']);
			$platforms = getOSPlatforms($conn,$result['resource_id']);
			$skills = getOtherSkills($conn,$result['resource_id']);
			$ac= getApplicationCodes($conn,$result['resource_id']);
			$hardwares = getHardwares($conn,$result['resource_id']);
			$ibmp = getIBMProducts($conn,$result['resource_id']);
			$isvp = getISVProducts($conn,$result['resource_id']);
			$titles = getTitles($conn,$result['resource_id']);
			$txns = getTransactions($conn,$result['resource_id']);
			
			$pskills = oneColumnDBResult($pskills,"rs_id");
			$dbtypes = oneColumnDBResult($dbtypes,"dt_id");
			$platforms = oneColumnDBResult($platforms,"platform_id");
			$skills = oneColumnDBResult($skills,"skill_id");
			$ac= oneColumnDBResult($ac,"ac_id");
			$hardwares = oneColumnDBResult($hardwares,"hardware_id");
			$ibmp = oneColumnDBResult($ibmp,"ibmp_id");
			$isvp = oneColumnDBResult($isvp,"isvp_id");
			$titles = oneColumnDBResult($titles,"title_id");
			$txns = oneColumnDBResult($txns,"transaction_id");
			
			$pskillsBefore = implode(",",$pskills);
			$dbtypesBefore = implode(",",$dbtypes);
			$platformsBefore = implode(",",$platforms);
			$skillsBefore = implode(",",$skills);
			$acBefore= implode(",",$ac);
			$hardwaresBefore = implode(",",$hardwares);
			$ibmpBefore = implode(",",$ibmp);
			$isvpBefore = implode(",",$isvp);
			$titlesBefore = implode(",",$titles);
			$txnsBefore = implode(",",$txns);
			
			echo'<div  style="display:none">
			<input name="pskillsBefore" value="'.$pskillsBefore.'">
			
			<input name="platformsBefore" value="'.$platformsBefore.'">
			<input name="dbtypesBefore" value="'.$dbtypesBefore.'">
			<input name="txnsBefore" value="'.$txnsBefore.'">
			<input name="acBefore" value="'.$acBefore.'">
			<input name="ibmpBefore" value="'.$ibmpBefore.'">
			<input name="isvpBefore" value="'.$isvpBefore.'">
			<input name="hardwaresBefore" value="'.$hardwaresBefore.'">
			<input name="titlesBefore" value="'.$titlesBefore.'">
			<input name="skillsBefore" value="'.$skillsBefore.'">
			</div>';
			
  			echo'<input name="resource_id" value="'.$result['resource_id'].'" hidden>
			<input name="old_resume_name" type="hidden" value="'.$result['resume_upload'].'">
			<input name="old_resume_issi_name" type="hidden" value="'.$result['resume_issi_upload'].'">';
			
			echo' <div class="submitDiv"><input type="submit" value="Save Resource Changes"></div>
			<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
	}
	else
	{
		displayResourcesPage($conn);
	}
}

function displayViewResourcePage($conn,$resourceid){
	$result = getSpecificResource($conn,$resourceid);
	if($result != null){
		$pskills = getPrimarySkills($conn,$result['resource_id']);			
			$pskills = oneColumnDBResult($pskills,"rs_name");
			$pskills = arrayToStringSet($pskills);
			
		echo '<div class="pageTitle"><h1>View Resource:';
		if(ISSET($result['resource_name']))echo $result['resource_name'];
		else echo $result['resource_fname'];
		echo'</h1></div>';
		echo'<div class="entries">';
			echo '<div class="sectionTitle"><h2>Resource Details</h2></div>';		
			echo'<div class= "newEntryInfo-Fours"><ul class="viewingPageList">
			<li>Company Name: '.$result['resource_name'].'</li>
			<li>First Name(Main Contact): '.$result['resource_fname'].'</li>
			<li>Last Name: '.$result['resource_lname'].'</li>
			<li>Status: '.$result['resource_status'].'</li>
			</ul></div>
			<div class= "newEntryInfo-Fours"><ul class="viewingPageList">
			<li>Primary Contact #: '.$result['phone1'].'</li>
			<li>Other Contact #: '.$result['phone2'].'</li>
			<li>Address: '.$result['resource_address'].'</li>
			<li>Email Address: '.$result['email'].'</li>
			</ul></div>
			<div class= "newEntryInfo-Fours"><ul class="viewingPageList">
			<li>Resume-ISSI Version: ';
			if($result['resume_issi_upload']){
				echo'<button class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_issi_uploads&filename='.$result['resume_issi_upload'].'">View Resume ISSI</button><br/>';
				echo'<span>Uploaded on: '.$result['resume_uploaded_date'].'<input type="hidden" name="resume_uploaded_date" value="'.$result['resume_uploaded_date'].'"></span>';
				}			
			echo'</li>
			<li>Resume-Original Version: ';
			if($result['resume_upload']){
				echo'<button class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_uploads&filename='.$result['resume_upload'].'">View Resume</button><br/>';
				echo'<span>Uploaded on: '.$result['resume_uploaded_date'].'<input type="hidden" name="resume_uploaded_date" value="'.$result['resume_uploaded_date'].'"></span>';
				
				}
			echo'</li>
			<li>Resource Notes:'.$result['resource_notes'].'</li>
			</ul></div>';
			echo'<div class= "newEntryInfo-Fours"><ul class="viewingPageList">';
			echo'Primary Skills: '.$pskills;
			echo'</div>
			</div>';
			
			
			$dbtypes = getDatabaseTypes($conn,$result['resource_id']);
			$platforms = getOSPlatforms($conn,$result['resource_id']);
			$skills = getOtherSkills($conn,$result['resource_id']);
			$ac= getApplicationCodes($conn,$result['resource_id']);
			$hardwares = getHardwares($conn,$result['resource_id']);
			$ibmp = getIBMProducts($conn,$result['resource_id']);
			$isvp = getISVProducts($conn,$result['resource_id']);
			$titles = getTitles($conn,$result['resource_id']);
			$txns = getTransactions($conn,$result['resource_id']);
			
			$dbtypes = oneColumnDBResult($dbtypes,"dt_name");
			$platforms = oneColumnDBResult($platforms,"platform_name");
			$skills = oneColumnDBResult($skills,"skill_name");
			$ac= oneColumnDBResult($ac,"ac_name");
			$hardwares = oneColumnDBResult($hardwares,"hardware_name");
			$ibmp = oneColumnDBResult($ibmp,"ibmp_name");
			$isvp = oneColumnDBResult($isvp,"isvp_name");
			$titles = oneColumnDBResult($titles,"title");
			$txns = oneColumnDBResult($txns,"transaction_name");
			
			$dbtypes = arrayToStringSet($dbtypes);
			$platforms = arrayToStringSet($platforms);
			$skills = arrayToStringSet($skills);
			$ac= arrayToStringSet($ac);
			$hardwares = arrayToStringSet($hardwares);
			$ibmp = arrayToStringSet($ibmp);
			$isvp = arrayToStringSet($isvp);
			$titles = arrayToStringSet($titles);
			$txns = arrayToStringSet($txns);
			
			echo '<div class="entries">';
			echo '<div class="sectionTitle"><h2>Expertise and Skills</h2></div>';
			echo'<ul class="viewingPageList">';
			echo '<li>Operating Systems: '.$platforms.'</li>';
			echo '<li>Database Types: '.$dbtypes.'</li>';
			echo '<li>Transactions(Expertise): '.$txns.'</li>';
			echo '<li>Application Codes: '.$ac.'</li>';
			echo '<li>ISV Products(Expertise): '.$ibmp.'</li>';
			echo '<li>IBM Products(Expertise): '.$isvp.'</li>';
			echo '<li>Hardwares(Expertise): '.$hardwares.'</li>';
			echo '<li>Titles: '.$titles.'</li>';
			echo '<li>Additional Skills: '.$skills.'</li>';
			echo '</ul></div>';
			echo '<div class="nextPage">
		<button  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=editResource&resourceid='.$result['resource_id'].'">Edit Resource</a></button>';
		if($_SESSION['role']=="administrator")echo'<button  class="deleteEntry" data-url="http://localhost/resourcesmgmt/index.php?page=Resources&action=deleteResource&resourceid='.$result['resource_id'].'">Delete Resource</button>';
		echo'<button  class="buttonLink"><a href="http://localhost/resourcesmgmt/index.php?page=Resources">Back To Resources Page</a></button>
		</div>';
	}
	else
	{
		displayResourcesPage($conn);
	}
}




function displayResourcesPage($conn){
	echo '<div class="pageTitle"><h1>Resources';
	if(ISSET($_POST['keyphrase']))echo ': Search Results for "'.$_POST['keyphrase'].'"';
	echo'</h1><div>';//div.pageTitle
	echo '<div class="pageListings">
	<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Resources&action=Search" method="post">
				<input type="submit" name="search" value="Search"><input name="keyphrase" type="text" maxlength="45">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';//div.searchbar
	
	if(ISSET($_POST['wantedSkill'])){
		$resources = getAvailableResources($conn);
		$results = getQualifiedResources($conn,$_POST,$resources);
	}
	else $results = getResources($conn);
	if(ISSET($_POST['keyphrase'])){
		$resources = getResources($conn);
		$results = resourcesSearchFilter($conn,$_POST['keyphrase'],$resources);
	}
	//Addition Options for page. not relevant to any particular entry.
	echo'<div class="mgmtTools"><button type="button" id="createUser" style="background:none;"  class="buttonLink">
	<a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=createResource">Create New Resource</a></button></div>';//div.mgmtTools
	
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>Resource Name</th>
				<th>Last Name</th>
				<th>First Name</th>
				<th>Contact #s</th>
				<th>Primary Skill(s)</th>
				<th>email</th>
				<th>Address</th>
				<th>Resume</th>
				<th>ISSI Resume</th>
				<th>Status</th>
				<th>Action</th>
				</tr>
	</table>		
	</div>';	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		$numResources = count($results);
		for($i=0;$i<$numResources;$i++){
			echo'<tr>
					<td>'.$results[$i]['resource_name'].'</td>
					<td>'.$results[$i]['resource_lname'].'</td>
					<td>'.$results[$i]['resource_fname'].'</td>
					<td>';
					if($results[$i]['phone1']!=="")echo '1st: '.$results[$i]['phone1'];
					if($results[$i]['phone2']!=="")echo '<br/>2nd: '.$results[$i]['phone2'];
					echo'</td>
					<td>';
					$pskills = getPrimarySkills($conn,$results[$i]['resource_id']);			
				$pskills = oneColumnDBResult($pskills,"rs_name");
				$pskills = arrayToStringSet($pskills);
				echo $pskills;
					echo'</td>
					<td>'.$results[$i]['email'].'</td>
					<td>'.$results[$i]['resource_address'].'</td>
					<td>';
					if($results[$i]['resume_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_uploads&filename='.$results[$i]['resume_upload'].'" value="View Resume">';
					echo '</td>
					<td>';
					if($results[$i]['resume_issi_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=resume_issi_uploads&filename='.$results[$i]['resume_issi_upload'].'" value="View Resume ISSI">';
					echo '</td>
					<td>'.$results[$i]['resource_status'].'</td>
					<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=viewResource&resourceid='.$results[$i]['resource_id'].'">View</a> 
					<a href="http://localhost/resourcesmgmt/index.php?page=Resources&action=editResource&resourceid='.$results[$i]['resource_id'].'">Edit</a>';
				if($_SESSION['role']=="administrator")echo' <a class="deleteEntry" href="http://localhost/resourcesmgmt/index.php?page=Resources&action=deleteResource&resourceid='.$results[$i]['resource_id'].'">Delete</a>';
				echo'</td>
				</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
	
	echo'</div>';//div.pageListings
}


function displayCreateSowPage($conn){
	$clients = getClients($conn);
	echo '<div class="pageTitle"><h1>Add A S.O.W.</h1><div>';
	echo '<form action="http://localhost/resourcesmgmt/index.php?page=Sows&action=addSow" method="post"
		enctype="multipart/form-data">';
		echo '<div class="entries">';
		echo '<div class="sectionTitle"><h2>S.O.W. Details</h2></div>';		
		echo'<div class= "newEntryInfo-Threes">
		<span class="input_name">SOW Name:</span><br/>
		<span class="input_value"><input name="sow_name" class="requiredInput" type="text" maxlength="45"></span><br/>
		<span class="input_name">Client Name:</span><br/>
		<select name="client_id" class="requiredInput">';
		echo '<option value="" class="null">N/A</option>';
		foreach($clients as $client){
		echo '<option value="'.$client['client_id'].'">'.$client['client_name'].'</option>';
		}
		echo '</select><br/>
		<span class="input_name">Primary Contact on Client Side:</span><br/>
		<span class="input_value"><input name="primary_contact" type="text" maxlength="45"></span><br/>
		<span class="input_name">SubContractor Identity:</span><br/>
		<span class="input_value"><input name="subcontractor_disguise" type="text" maxlength="45"></span><br/>
		<span class="input_name">Billing Per Hour/Flat Rate</span><br/>
		<span class="input_value"><input name="billing" type="text" maxlength="45"></span><br/>
		</div>
		<div class= "newEntryInfo-Threes">
		<span class="input_name">SOW:</span><br/>
		<span class="input_value"><input type="file" name="sow" id="file"><br/>
		<span class="input_name">Start Date:</span><br/>
		<span class="input_value"><input name="start_date" type="text" maxlength="45" class="datepicker"></span><br/>
		<span class="input_name">End Date:</span><br/>
		<span class="input_value"><input name="end_date" type="text" maxlength="45" class="datepicker"></span><br/>
		<span class="input_name">Summary:</span><br/>
		<span class="input_value"><textarea name="summary" rows="2" cols="50"></textarea><br/>
		</div>';//.newEntryInfo-Threes
		echo'</div>';//.entries
		echo'<div class="submitDiv"><input type="submit" value="Save New SOW"></div>
			<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
}



function displayEditSowPage($conn,$sowid){
	$result = getSpecificSow($conn,$sowid);
	$clients = getClients($conn);
	if($result != null){
		echo '<div class="pageTitle"><h1>Edit S.O.W.: '.$result['sow_name'].'</h1><div>';
		echo '<form action="http://localhost/resourcesmgmt/index.php?page=Sows&action=saveSowChanges" method="post"
			enctype="multipart/form-data">';
			echo '<div class="entries">';
			echo '<div class="sectionTitle"><h2>S.O.W. Details</h2></div>';		
			echo'<div class= "newEntryInfo-Threes">
			<span class="input_name">SOW Name:</span><br/>
			<span class="input_value"><input name="sow_name" class="requiredInput" type="text" maxlength="45" value="'.$result['sow_name'].'"></span><br/>
			<span class="input_name">Client Name:</span><br/>
			<select name="client_id" class="requiredInput">';
			echo '<option value="" class="null">N/A</option>';
			if($clients){
			foreach($clients as $client){
				echo '<option value="'.$client['client_id'].'"';
				if($client['client_id'] == $result['client_id'])echo' selected';
				echo'>'.$client['client_name'].'</option>';
			}
			}
			echo '</select><br/>
			<span class="input_name">Primary Client Contact:</span><br/>
			<span class="input_value"><input name="primary_contact" type="text" maxlength="45" value="'.$result['primary_contact'].'"></span><br/>
			<span class="input_name">SubContractor Identity:</span><br/>
			<span class="input_value"><input name="subcontractor_disguise" type="text" maxlength="45" value="'.$result['subcontractor_disguise'].'"></span><br/>
			<span class="input_name">Billing Per Hour/Flat Rate:</span><br/>
			<span class="input_value"><input name="billing"type="text" maxlength="45" value="'.$result['billing'].'"></span><br/>
			
			</div>';//.newEntryInfo-Threes
			echo'<div class= "newEntryInfo-Threes">
			<span class="input_name">SOW:</span>';
			if($result['sow_upload'])echo'<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$result['sow_upload'].'" value="View S.O.W. File">';
			echo'<br/>
			<span class="input_value"><input type="file" name="sow" id="file"></span><br/>
			<span class="input_name">Start Date:</span><br/>
			<span class="input_value"><input name="start_date" type="text" maxlength="45" class="datepicker" value="'.$result['start_date'].'"></span><br/>
			<span class="input_name">End Date:</span><br/>
			<span class="input_value"><input name="end_date" type="text" maxlength="45" class="datepicker" value="'.$result['end_date'].'"></span><br/>
			<span class="input_name">Status:</span><br/>
			<span class="input_value">
			<select name="sow_status">
			<option value="Active"';
			if($result['sow_status']=="Active")echo ' selected';
			echo '>Active</option>
			<option value="Expired"';
			if($result['sow_status']=="Expired")echo ' selected';
			echo '>Expired</option>
			<option value="Completed"';
			if($result['sow_status']=="Completed")echo ' selected';
			echo '>Completed</option>
			<option value="Canceled"';
			if($result['sow_status']=="Canceled")echo ' selected';
			echo '>Canceled</option>
			<option value="Replaced"';
			if($result['sow_status']=="Replaced")echo ' selected';
			echo '>Replaced</option>
			<option value="To Be Removed"';
			if($result['sow_status']=="To Be Removed")echo ' selected';
			echo '>To Be Removed</option>
			</select>
			</span><br/>
			<span class="input_name">Summary:</span><br/>
			<span class="input_value"><textarea name="summary" rows="2" cols="50">'.$result['summary'].'</textarea><br/>
			<input name="sow_id" type="hidden" value="'.$result['sow_id'].'">
			<input name="prev_name" type="hidden" value="'.$result['sow_upload'].'">
			</div>';//.newEntryInfo-Threes
			echo'</div>';//.entries
			echo'<div class="submitDiv"><input type="submit" value="Save Changes To SOW"></div>
			<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
	}
	else
	{
		displaySowsPage($conn);
	}
}

function displayViewSowPage($conn,$sowid){
	$result = getSpecificSow($conn,$sowid);
	if($result != null){
		echo '<div class="pageTitle"><h1>S.O.W.: '.$result['sow_name'].'</h1><div>';
		echo '<div class="entries">';
			echo '<div class="sectionTitle"><h2>S.O.W. Details</h2></div>';					
			echo'<div class= "newEntryInfo-Threes"><ul class=""viewingPageList">
			<li>SOW Name: '.$result['sow_name'].'</li>
			<li>Client Name: '.$result['client_name'].'</li>
			<li>Primary Client Contact: '.$result['primary_contact'].'</li>
			<li>SubContractor Identity: '.$result['subcontractor_disguise'].'</li>
			<li>Billing Per Hour/Flat Rate: '.$result['billing'].'</li>
			</ul></div>
			<div class= "newEntryInfo-Threes"><ul class=""viewingPageList">
			<li>SOW: ';
			if($result['sow_upload'])echo'<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$result['sow_upload'].'" value="View S.O.W. File">';
			echo'
			<li>Start Date: '.$result['start_date'].'</li>
			<li>End Date: '.$result['end_date'].'</li>
			<li>Status: '.$result['sow_status'].'</li>
			<li>Summary: '.$result['summary'].'</li>';
			echo'</ul></div>';//.newEntryInfo-Threes
			echo'</div>';//.entries
			echo '<div class="nextPage">
		<button  class="buttonLink"><a href="">Edit Sow</a></button>';
		if($_SESSION['role']=="administrator")echo'<button  class="deleteEntry" data-url="http://localhost/resourcesmgmt/index.php?page=Sows&action=deleteSow&sowid='.$result['sow_id'].'">Delete Sow</button>';
		echo'<button  class="buttonLink"><a href="">Back To Sows Page</a></button>
		</div>';//.nextPage
	}
	else
	{
		displaySowsPage($conn);
	}
}

function displaySowsPage($conn){
	echo '<div class="pageTitle"><h1>S.O.W.s(Statements of Work)';
	if(ISSET($_POST['keyphrase']))echo ': Search Results for "'.$_POST['keyphrase'].'"';
	echo'</h1></div>';//div.pageTitle
	echo '<div class="pageListings">
	<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Sows&action=Search" method="post">
				<input type="submit" name="search" value="Search"><input name="keyphrase" type="text" maxlength="45">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';//div.searchbar
	if(ISSET($_POST['Active']))$results = getActiveSows($conn);	
	else $results = getSows($conn);
	if(ISSET($_POST['keyphrase']))$results = sowsSearchFilter($conn,$_POST['keyphrase'],$results);
	//Addition Options for page. not relevant to any particular entry.
	echo'<div class="mgmtTools"><button type="button" id="createSow" style="background:none;" class="buttonLink">
	<a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=createSow">Create New Sow</a></button></div>';//div.mgmtTools
	
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
					<th>S.O.W. Name</th>
					<th>Client</th>
					<th>Primary Contact</th>
					<th>Subcontractor Identity</th>
					<th>S.O.W.</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Billing Rate</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
	</table></div>';	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		foreach($results as $sow){
			echo '<tr>
				<td>'.$sow['sow_name'].'</td>
				<td>'.$sow['client_name'].'</td>
				<td>'.$sow['primary_contact'].'</td>
				<td>'.$sow['subcontractor_disguise'].'</td>
				<td>';
				if($sow['sow_upload']!=null)echo '<input type="button"class="viewer" data-url="http://localhost/resourcesmgmt/index.php?action=viewFile&directory=sow_uploads&filename='.$sow['sow_upload'].'" value="View S.O.W. File">';
				echo '</td>
				<td>'.$sow['start_date'].'</td>
				<td>'.$sow['end_date'].'</td>
				<td>'.$sow['billing'].'</td>
				<td>'.$sow['sow_status'].'</td>
				<td class="rowEntryAction"><a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=viewSow&sowid='.$sow['sow_id'].'">View</a> 
				<a href="http://localhost/resourcesmgmt/index.php?page=Sows&action=editSow&sowid='.$sow['sow_id'].'">Edit</a>';
				if($_SESSION['role']=="administrator")echo' <a class="deleteEntry" href="http://localhost/resourcesmgmt/index.php?page=Sows&action=deleteSow&sowid='.$sow['sow_id'].'">Delete</a>';
				echo'</td>
			</tr>';
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table
	echo'</div>';//div.pageListings		
}





function displayCreateUserPage($conn){
	echo '<div class="pageTitle"><h1>Add A User</h1><div>';
	echo'<form action="http://localhost/resourcesmgmt/index.php?page=Users&action=addUser" method="post">';
		echo'<div class="entries">';
		echo '<div class="sectionTitle"><h2>User Details</h2></div>';		
		echo'<div class= "newEntryInfo-Threes">
		<span class="input_name">First Name:</span><br/>
		<span class="input_value"><input name="user_fname" class="requiredInput" type="text" maxlength="45"></span><br/>
		<span class="input_name">Last Name:</span><br/>
		<span class="input_value"><input name="user_lname" class="requiredInput" type="text" maxlength="45"></span><br/>
		<span class="input_name">Email Address:</span><br/>
		<span class="input_value"><input name="email" type="text" maxlength="45"></span><br/>
		<span class="input_name">Phone Number:</span><br/>
		<span class="input_value"><input name="phone" type="text" maxlength="45"></span><br/>
		<span class="input_name">User Role:</span><br/>
		<span class="input_value"><select name="role">';
			$roles = getPossibleRoles();
			for($i=0;$i<count($roles);$i++){
			echo'<option value="'.$roles[$i].'"';
			echo'>'.$roles[$i].'</option>';
			}
			echo '</select></span>
		</div>';
		echo'<div class= "newEntryInfo-Threes">
		<span class="input_name">Username:</span>
		<span>*Must be at least 5 characters long, and not already exist<span><br/>
		<span class="input_value"><input name="username" id="username" data-ref="username" class="requiredInput ajaxCalling" type="text" maxlength="45"></span>
		<span class="ajaxResult" data-ref="username" data-url="http://localhost/resourcesmgmt/index.php?page=Ajax&action=checkUsername&username=">Invalid</span><br/>
		<!--<span class="input_name">Suggested Username:</span><br/>
		<span class="input_value"><input name="suggested_username" style="border-color:#ddd" type="text" maxlength="45" readonly></span><br/>
		--></div>';
		echo '</div>';//.entries
		echo'<div class="submitDiv"><input type="submit" value="Save New User"></div>
	<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
}

function displayEditUserPage($conn,$userid){
	$result = getSpecificUser($conn,$userid);
	if($result != null){
	echo '<div class="pageTitle"><h1>Edit User:'.$result['user_fname'].' '.$result['user_lname'].'</h1><div>';
	echo'<form action="http://localhost/resourcesmgmt/index.php?page=Users&action=saveUserChanges" method="post">';
		echo'<div class="entries">';
		echo '<div class="sectionTitle"><h2>User Details</h2></div>';		
		echo'<div class= "newEntryInfo-Threes">
		<span class="input_name">First Name:</span><br/>
		<span class="input_value"><input name="user_fname" class="requiredInput" type="text" maxlength="45" value="'.$result['user_fname'].'"></span><br/>
		<span class="input_name">Last Name:</span><br/>
		<span class="input_value"><input name="user_lname" class="requiredInput" type="text" maxlength="45" value="'.$result['user_lname'].'"></span><br/>
		<span class="input_name">Email Address:</span><br/>
		<span class="input_value"><input name="email" type="text" maxlength="45" value="'.$result['email'].'"></span><br/>
		<span class="input_name">Phone Number:</span><br/>
		<span class="input_value"><input name="phone" type="text" maxlength="45" value="'.$result['phone'].'"></span><br/>
		<span class="input_name">User Role:</span><br/>
		<span class="input_value"><select name="role">';
			$roles = getPossibleRoles();
			for($i=0;$i<count($roles);$i++){
			echo'<option value="'.$roles[$i].'"';
			if(strcasecmp($roles[$i],$result['role']) == 0) echo ' selected';
			echo'>'.$roles[$i].'</option>';
			}
			echo '</select></span><br/>
		<input type="hidden" name="user_id" value="'.$result['user_id'].'">
		</div>
		<div class= "newEntryInfo-Threes">
		<span class="input_name">Status:</span><br/>
		<span class="input_value"><select name="user_status">
		<option value="Active"';
			if($result['user_status']=="Active")echo ' selected';
			echo '>Active</option>
			<option value="Deactivated"';
			if($result['user_status']=="Deactivated")echo ' selected';
			echo '>Deactivated</option>
			<option value="To Be Removed"';
			if($result['user_status']=="To Be Removed")echo ' selected';
			echo '>To Be Removed</option>
			</select>
			</span><br/>
			</div>';//newEntryInfo-Threes
			echo '</div>';//.entries
		echo'<div class="submitDiv"><input type="submit" value="Save User Changes"></div>
	<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>';
	}
	else
	{
		displayUsersPage($conn);
	}
}

function displayViewUserPage($conn,$userid){
	$result = getSpecificUser($conn,$userid);
	if($result != null){
		echo '<div class="pageTitle"><h1>User:'.$result['user_fname'].' '.$result['user_lname'].'</h1><div>';
		echo'<div class="entries">';
		echo '<div class="sectionTitle"><h2>User Details</h2></div>';		
		echo'<div class= "newEntryInfo-Threes"><ul class="viewingPageList">
		<li>First Name:'.$result['user_fname'].'</li>
		<li>Last Name:'.$result['user_lname'].'</li>
		<li>Email Address:'.$result['email'].'</li>
		<li>Phone Number:'.$result['phone'].'</li>
		<li>User Role:'.$result['role'].'</li>
		</ul></div>
		<div class= "newEntryInfo-Threes"><ul class="viewingPageList">
		<span class="input_name">Status: '.$result['user_status'].'</li>
		</ul></div>
		</div>';//.entries
		echo '<div class="nextPage">
		<button  class="buttonLink"><a href="">Edit User</a></button>';
		if($_SESSION['role']=="administrator" && $_SESSION['userid']==1)echo'<button  class="deleteEntry" data-url="http://localhost/resourcesmgmt/index.php?page=Users&action=deleteUser&userid='.$result['user_id'].'">Delete Resource</button>';
		echo'<button  class="buttonLink"><a href="">Back To Users Page</a></button>
		</div>';//.nextPage
	}
	else
	{
		displayUsersPage($conn);
	}
}

function displayUsersPage($conn){
	echo '<div class="pageTitle"><h1>Users';
	if(ISSET($_POST['keyphrase']))echo ': Search Results for "'.$_POST['keyphrase'].'"';
	echo'</h1><div>';//div.pageTitle
	echo '<div class="pageListings">
	<div class="searchbar">
			<form action="http://localhost/resourcesmgmt/index.php?page=Users&action=Search" method="post">
				<input type="submit" name="search" value="Search"><input name="keyphrase" type="text" maxlength="45">';
			displayAdvancedSearchOption();
			echo'<input type="hidden" name="action_token" value="';echo html_escape($_SESSION['action_token']);echo'" /></form>
		</div>';//div.searchbar
	
	$results = getUsers($conn);
	if(ISSET($_POST['keyphrase']))$results = usersSearchFilter($conn,$_POST['keyphrase'],$results);
	//Addition Options for page. not relevant to any particular entry.
	echo'<div class="mgmtTools"><button type="button" id="createUser" style="background:none;"  class="buttonLink">
	<a href="http://localhost/resourcesmgmt/index.php?page=Users&action=createUser">Create New User</button></div>';//div.mgmtTools
	
	echo '<div class="table">';
	echo'<div class="tableHead">
	<table  class="tableHead">
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Phone</th>
				<th>Created By</th>
				<th>Status</th>
				<th>Action</th>
			</tr>
	</table></div>';//div.tableHead	
			
	if($results){
		echo'<div class="tableBody"><table  class="tableBody">';
		for($i=0;$i<count($results);$i++){
			$creator = getCreator($conn,$results[$i]['created_by']);
			$creator = implode(" ",$creator);
			echo'<tr>
					<td>'.$results[$i]['user_fname'].'</td>
					<td>'.$results[$i]['user_lname'].'</td>
					<td>'.$results[$i]['email'].'</td>
					<td>'.$results[$i]['phone'].'</td>
					<td>'.$creator.'</td>
					<td>'.$results[$i]['user_status'].'</td>
					<td class="rowEntryAction">
					<a href="http://localhost/resourcesmgmt/index.php?page=Users&action=viewUser&userid='.$results[$i]['user_id'].'">View</a> ';
					if($_SESSION['role']=="administrator"){
					if($_SESSION['userid']==1
					|| $results[$i]['role']!= "administrator"
					|| $results[$i]['user_id'] == $_SESSION['userid'])echo '<a href="http://localhost/resourcesmgmt/index.php?page=Users&action=editUser&userid='.$results[$i]['user_id'].'">Edit</a>';
					}
					echo'</td>
				</tr>';	
		}	
		echo '</table></div>';//div.tableBody
	}
	echo '</div>';//div.table	
	echo '</div>';//div.pageListings
}


function displayFooter(){
echo '</div>
<div id="footer"></div>
		</div>
		</body>
		</html>';

}

function testPage($conn){

$resourcenotes = "Pls see the following email.
 
Lee Sorenson is VP of Procurement for IS&GS so he is the top of the food chain there.  He may hand this off and it gets lost in the wash. If you don’t hear anything in a week or two I suggest you call and reference the mail from John to get back in the door.
 
Lee’s number is (610) 354-6029.  If you need any information or assistance don’t hesitate to contact me.";

echo 'resource notes: '.$resourcenotes.'<br/><br/>';

$testText = "Pls see the following email.
 
Lee Sorenson is VP of Procurement for IS&GS so he is the top of the food chain there.  He may hand this off and it gets lost in the wash. If you don’t hear anything in a week or two I suggest you call and reference the mail from John to get back in the door.
 
Lee’s number is (610) 354-6029.  If you need any information or assistance don’t hesitate to contact me.";
echo $testText.'<br/>';
$testArray = array("rando","another'rando",array($testText,"afterTest"),"empty",3);

echo '<br/>1<br/>';
print_r($testArray);
echo '<br/>2<br/>';


//$oldArray = array($testArray);
$oldArray = $testArray;

echo '<br/>3<br/>';
print_r($oldArray);

echo '<br/>4<br/>';

$testArray = anti_hack($testArray);

echo '<br/>5<br/>';
print_r($testArray);


echo '<br/>6<br/>';


echo $testArray == $oldArray;
echo '<br/>7<br/>';

echo 'The text after escaping is :'.html_escape($testText).'<br/>';
//$testText = cleanForHtml($testText);

$oldData = $testText;

echo '<br/>8<br/>';

echo 'testdata: '.$testText.'<br/>';
echo 'olddata: '.$oldData.'<br/>';


$sql = "UPDATE csm.resources SET resource_notes=:resource_notes WHERE resource_id = 36";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':resource_notes',$resourcenotes,PDO::PARAM_STR);
$stmt->execute();

}
	
function testRecievePage($conn,$postArray){
	
}

function displayQuarantinedPage($conn){
	echo 'You\'ve inserted suspicious data, so your account has been quarantined.  Contact your website administrator to get account re-enabled';
	quarantineUser($conn);
	}
?>