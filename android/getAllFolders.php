<?php
//Code that get all folders under an specific project for a user.

//array for JSON response
$response = array();

if(isset($_POST['projectName'])){
	$project = $_POST['projectName'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Get Project folders that contains data.
		$stmt1 = $con->prepare("SELECT FOLDER.foldername
								FROM FOLDER
								WHERE FOLDER.rootid = (SELECT folderid FROM FOLDER WHERE foldername = ?)
								and FOLDER.isdatafolder = 1");
		$stmt1->bind_param('s',$project);
		$stmt1->execute();
		$result = $stmt1->get_result();
		
		$response["folders"] = array(); //Array that store project folders.
		
		//loop through all results
		while($row = $result->fetch_array()){
			$projectFolder = array();
			$projectFolder["folder"] = $row["foldername"];
			
			//push project folders into response array
			array_push($response["folders"],$projectFolder);
		}
		
		$response["success"] = 2; //successfully obtained all project information.
		mysqli_close($con);
		echo json_encode($response);
	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>