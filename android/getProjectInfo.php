<?php
//Code that get all project information from specific user.

//array for JSON response
$response = array();

if(isset($_POST['username'])&&isset($_POST['projectName'])){
	$username = $_POST['username'];
	$project = $_POST['projectName'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Get project date and project description.
		$stmt1 = $con->prepare("SELECT FOLDER.datecreated,FOLDER.comment
								FROM FOLDER,COLLABORATORS,USERS
								WHERE USERS.username = ?
								and FOLDER.foldername = ?
								and FOLDER.folderid = COLLABORATORS.folderid
								and COLLABORATORS.userid = USERS.userid");
		$stmt1->bind_param('ss',$username,$project);
		$stmt1->execute();
		$result1 = $stmt1->get_result();
		
		$response["dateAndDescription"] = array(); //Array that store project date and description
		
		//loop through all results
		while($row = $result1->fetch_array()){
			$projectInfo = array();
			$projectInfo["date"] = $row["datecreated"];
			$projectInfo["description"] = $row["comment"];
			
			//push project information into response array
			array_push($response["dateAndDescription"],$projectInfo);
		}
		
		//Get project owners.
		$stmt2 = $con->prepare("SELECT DISTINCT CONCAT(SURVEYOR.firstname, ' ', SURVEYOR.lastname) AS project_owner
								FROM SURVEYOR,USERS,COLLABORATORS,FOLDER
								WHERE FOLDER.foldername = ?
								and FOLDER.folderid = COLLABORATORS.folderid
								and COLLABORATORS.userid = USERS.userid
								and USERS.userid = SURVEYOR.userid");
		$stmt2->bind_param('s',$project);
		$stmt2->execute();
		$result2 = $stmt2->get_result();
		
		$response["owners"] = array(); //Array that store project owners.
		
		//loop through all results
		while($row = $result2->fetch_array()){
			$projectOwner = array();
			$projectOwner["owner"] = $row["project_owner"];
			
			//push project owners into response array
			array_push($response["owners"],$projectOwner);
		}
		
		//Get Project folders that contains data.
		$stmt3 = $con->prepare("SELECT FOLDER.foldername
								FROM FOLDER
								WHERE FOLDER.rootid = (SELECT folderid FROM FOLDER WHERE foldername = ?)
								and FOLDER.isdatafolder = 1");
		$stmt3->bind_param('s',$project);
		$stmt3->execute();
		$result3 = $stmt3->get_result();
		
		$response["folders"] = array(); //Array that store project folders.
		
		//loop through all results
		while($row = $result3->fetch_array()){
			$projectFolder = array();
			$projectFolder["folder"] = $row["foldername"];
			
			//push project folders into response array
			array_push($response["folders"],$projectFolder);
		}
		
		$response["success"] = 2; //successfully obtained all project information.
		mysqli_close($con);
		echo json_encode($response);
	}
}else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>