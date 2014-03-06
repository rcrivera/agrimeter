<?php
/*Code that verify sign in credentials from android device.*/

//array for JSON response
$response = array();

//verify the availability of username and password parameters.
if(isset($_POST['username']) && isset($_POST['password'])){
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//query for verify user credentials
		//$stmt = $mysqli->stmt_init();
		$stmt = $con->prepare("SELECT SURVEYOR.firstname
								FROM USERS,SURVEYOR
								WHERE USERS.userid = SURVEYOR.userid
								and SURVEYOR.isactive = 1
								and USERS.username = ?
								and USERS.password = ?");
		//$stmt = $con->prepare("SELECT userid FROM USERS WHERE username = ?");
		$stmt->bind_param('ss', $username, $password);
		//$stmt->bind_param('s',$username);
		

		$stmt->execute();
		

		$result = $stmt->get_result();
	
		//verify query response
		if(mysqli_num_rows($result)==1){
			$response["success"] = 2; //match was found successfully
			mysqli_close($con);
			echo json_encode($response);
		}
		else{
			$response["success"] = 3; //not match credentials
			mysqli_close($con);
			echo json_encode($response);
		}

	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>