<?php
//Code that obtains elevation differences obtained in a project folder.

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
		//Get all elevation difference taken on a specific folder.
		$stmt = $con->prepare("SELECT CONCAT(A.pointname,',',B.pointname) AS points, A.elevationdifference
								FROM POINT as A,FOLDER,USERS,COLLABORATORS,POINT as B
								WHERE FOLDER.folderid = A.folder
								and FOLDER.foldername = ?
								and FOLDER.rootid = (SELECT FOLDER.folderid FROM FOLDER WHERE FOLDER.foldername = ?)
								and USERS.username = ?
								and USERS.userid = COLLABORATORS.userid
								and COLLABORATORS.folderid = (SELECT FOLDER.folderid FROM FOLDER WHERE FOLDER.foldername = ?)
								and A.prevpoint is not null
								and A.prevpoint = B.pointid");
		$stmt->bind_param('ssss',$folder,$project,$username,$project);
		$stmt->execute();
		$result = $stmt->get_result();
		
		$response["differences"] = array(); //array that store all distances.
		
		//loop through all results
		while($row = $result->fetch_array()){
			$difference = array();
			$difference["points"] = $row["points"];
			$difference["elevationDifference"] = $row["elevationdifference"];
		
			//push name into response array
			array_push($response["differences"],$difference);
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