<?php
//Code to obtains all projects created by a user.

//array for JSON response
$response = array();

if(isset($_POST['username'])){
	$username = $_POST['username'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Get all project names under the specific user.
		$stmt = $con->prepare("SELECT FOLDER.foldername
								FROM USERS,COLLABORATORS,FOLDER
								WHERE USERS.username = ?
								and USERS.userid = COLLABORATORS.userid
								and COLLABORATORS.folderid = FOLDER.folderid
								and FOLDER.isroot = 1");
		$stmt->bind_param('s',$username);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$response["projects"] = array(); //array that store all project names.
		
		//loop through all results
		while($row = $result->fetch_array()){
			$projects = array();
			$projects["MyProject"] = $row["foldername"];
			
			//push name into response array
			array_push($response["projects"],$projects);
		}
		
		$response["success"] = 2; //successfully obtained all organization name.
		mysqli_close($con);
		echo json_encode($response);
	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>