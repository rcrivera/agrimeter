<?php
//Code that obtains comments in a project folder.

//array for JSON response
$response = array();
if(isset($_POST['username'])&&isset($_POST["projectName"])&&isset($_POST['folder'])){
	$username = $_POST['username'];
	$project = $_POST["projectName"];
	$folder = $_POST['folder'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Get folder comments
		$stmt = $con->prepare("SELECT FOLDER.comment
								FROM FOLDER,USERS,COLLABORATORS
								WHERE FOLDER.foldername = ?
								and FOLDER.rootid = (SELECT FOLDER.folderid FROM FOLDER WHERE FOLDER.foldername = ?)
								and USERS.username = ?
								and USERS.userid = COLLABORATORS.userid
								and COLLABORATORS.folderid = FOLDER.folderid");
		$stmt->bind_param('sss',$folder,$project,$username);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$response["comments"] = array(); //array that store comments.
		
		//loop through all results
		while($row = $result->fetch_array()){
			$comment = array();
			$comment["comment"] = $row["comment"];
			
			//push query response into response array
			array_push($response["comments"],$comment);
		}
		
		$response["success"] = 2; //successfully obtained all information.
		mysqli_close($con);
		echo json_encode($response);
	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>