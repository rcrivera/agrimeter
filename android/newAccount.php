<?php
/*Code that insert a new account.*/

//array for JSON response
$response = array();

//verify the availability of parameters.
if(isset($_POST['firstName']) && isset($_POST['lastName'])&&isset($_POST['phoneNumber'])&&isset($_POST['organization'])&&
	isset($_POST['email'])&&isset($_POST['username'])&&isset($_POST['password'])){
	
	$firstName = $_POST['firstName'];
	$lastName = $_POST['lastName'];
	$phoneNumber = $_POST['phoneNumber'];
	$organization = $_POST['organization'];
	$email = $_POST['email'];
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Verify if currently exist an account with this username. 
		$stmt = $con->prepare("SELECT username FROM USERS WHERE username = ?");
		$stmt->bind_param('s',$username);
		$stmt->execute();
		$verify_query = $stmt->get_result();
		
		if(mysqli_num_rows($verify_query)>0){
			$response["success"] = 2; //Exist an account with this username.
			mysqli_close($con);
			echo json_encode($response);
		}
		else{
			//Cancel auto commit option in the database
			mysqli_autocommit($con, FALSE);
			
			//If user select "None" on the organization that is going to be part.
			if($organization=="None"){
				$stmt1 = $con->prepare("INSERT INTO USERS (username,password,phone,email)
										VALUES (?,?,?,?)");
				$stmt1->bind_param('ssss',$username,$password,$phoneNumber,$email);
				$stmt1->execute();
			
				if($stmt1){
					$stmt2 = $con->prepare("INSERT INTO SURVEYOR (firstname,lastname,isactive)
											VALUES (?,?,1)");
					$stmt2->bind_param('ss',$firstName,$lastName);
					$stmt2->execute();
					
					if($stmt2){
						$response["success"] = 3; //insert successfully
						mysqli_commit($con);
						mysqli_close($con);
						echo json_encode($response);
					}
					else{
						$response["success"] = 4; //insert not successfully
						mysqli_rollback($con);
						mysqli_close($con);
						echo json_encode($response);
					}
				}
				else{
					$response["success"] = 4; //insert not successfully
					mysqli_rollback($con);
					mysqli_close($con);
					echo json_encode($response);
				}
			}
			//User select one of the organization that is available on the system.
			else{
				$stmt3 = $con->prepare("INSERT INTO USERS (username,password,phone,email)
										VALUES (?,?,?,?)");
				$stmt3->bind_param('ssss',$username,$password,$phoneNumber,$email);
				$stmt3->execute();
		
				if($stmt3){
					$result4=$con->query("SELECT MAX(userid) as maxid FROM USERS");
					$queryresult1 ='none';
					while($row = $result4->fetch_array()){
						$queryresult1 = $row["maxid"];
					}
					
					$stmt5 = $con->prepare("SELECT userid FROM ORGANIZATION WHERE orgname = ?");
					$stmt5->bind_param('s',$organization);
					$stmt5->execute();
					$result5 = $stmt5->get_result();
		
					$queryresult2 ='none';
					while($row = $result5->fetch_array()){
						$queryresult2 = $row["userid"];
					}
					
					$stmt6 = $con->prepare("INSERT INTO SURVEYOR (userid,firstname,lastname,orgid,isactive)
											VALUES (?,?,?,?,1)");
					$stmt6->bind_param('ssss',$queryresult1,$firstName,$lastName,$queryresult2);
					$stmt6->execute();
					
					if($stmt6){
						$response["success"] = 3; //insert successfully
						mysqli_commit($con);
						mysqli_close($con);
						echo json_encode($response);
					}
					else{
						$response["success"] = 4; //insert not successfully
						mysqli_rollback($con);
						mysqli_close($con);
						echo json_encode($response);
					}
					
				}
				else{
					$response["success"] = 4; //insert not successfully
					mysqli_rollback($con);
					mysqli_close($con);
					echo json_encode($response);
				}
			}
		}
		
	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>