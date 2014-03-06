<?php
//Code that obtains raw data (point name, longitude, latitude, elevation) from a point.

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
		//Get all points under the specific folder, project, and username.
		$stmt = $con->prepare("SELECT POINT.pointname,POINT.longitude,POINT.latitude,POINT.elevation
								FROM POINT,FOLDER,USERS,COLLABORATORS
								WHERE FOLDER.folderid = POINT.folder
								and FOLDER.foldername = ?
								and FOLDER.rootid = (SELECT FOLDER.folderid FROM FOLDER WHERE FOLDER.foldername = ?)
								and USERS.username = ?
								and USERS.userid = COLLABORATORS.userid
								and COLLABORATORS.folderid = (SELECT FOLDER.folderid FROM FOLDER WHERE FOLDER.foldername = ?)");
		$stmt->bind_param('ssss',$folder,$project,$username,$project);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$response["points"] = array(); //array that store all points.
		
		//loop through all results
		while($row = $result->fetch_array()){
			$point = array();
			$point["name"] = $row["pointname"];
			$point["longitude"] = $row["longitude"];
			$point["latitude"] = $row["latitude"];
			$point["elevation"] = $row["elevation"];
			
			//push information into response array
			array_push($response["points"],$point);
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