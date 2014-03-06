<?php
/*Code that request all Organization available at the database*/

//array for JSON response
$response = array();

//Generating data base connection.
$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 0; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//get all organization names
		$result = $con->query("SELECT DISTINCT orgname FROM ORGANIZATION");
		
		$response["organization"] = array();
		
		//loop through all results
		while($row = $result->fetch_array()){
			$organization = array();
			$organization["name"] = $row["orgname"];
			
			//push name into response array
			array_push($response["organization"],$organization);
		}
		
		$response["success"] = 1;//successfully obtained all organization name.
		mysqli_close($con);
		echo json_encode($response);
		
	}
?>