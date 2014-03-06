<?php
//Code that insert a pair of points, and the distance and sea level elevation between them

//array for JSON response
$response = array();

//verify the availability of parameters.
if(isset($_POST['username']) && isset($_POST['projectName']) && isset($_POST['folder']) && isset($_POST['pointName1']) &&
	isset($_POST['latitude1']) && isset($_POST['longitude1']) && isset($_POST['elevation1']) && isset($_POST['pointName2']) &&
	isset($_POST['latitude2']) && isset($_POST['longitude2']) && isset($_POST['elevation2']) && isset($_POST['distance']) &&
	isset($_POST['distanceWithElevation']) && isset($_POST['elevationDifference']) && isset($_POST['comment'])){
	
	$username = $_POST['username'];
	$projectName = $_POST['projectName'];
	$folder = $_POST['folder'];
	$pointName1 = $_POST['pointName1'];
	$latitude1 = $_POST['latitude1'];
	$longitude1 = $_POST['longitude1'];
	$elevation1 = $_POST['elevation1'];
	$pointName2 = $_POST['pointName2'];
	$latitude2 = $_POST['latitude2'];
	$longitude2 = $_POST['longitude2'];
	$elevation2 = $_POST['elevation2'];
	$distance = $_POST['distance'];
	$distanceWithElevation = $_POST['distanceWithElevation'];
	$elevationDifference = $_POST['elevationDifference'];
	$comment = $_POST['comment'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Get folderid where the data is going to be store
		$stmt1 = $con->prepare("SELECT FOLDER.folderid
								FROM FOLDER,COLLABORATORS
								WHERE FOLDER.foldername = ?
								and FOLDER.rootid = (SELECT folderid FROM FOLDER WHERE foldername = ? and isroot = 1)
								and FOLDER.folderid = COLLABORATORS.folderid
								and COLLABORATORS.userid = (SELECT USERS.userid FROM USERS WHERE USERS.username = ?)");
		$stmt1->bind_param('sss',$folder,$projectName,$username);
		$stmt1->execute();
		$result1 = $stmt1->get_result();
		
		$queryresult1 ='none';
		while($row = $result1->fetch_array()){
			$queryresult1 = $row["folderid"];
		}
		
		//Verify if exist a point with the submitted names
		$stmt2 = $con->prepare("SELECT POINT.pointname FROM POINT WHERE POINT.pointname = ? and POINT.folder = ?");
		$stmt2->bind_param('ss',$pointName1,$queryresult1);
		$stmt2->execute();
		$verify_query2 = $stmt2->get_result();
		
		$stmt3 = $con->prepare("SELECT POINT.pointname FROM POINT WHERE POINT.pointname = ? and POINT.folder = ?");
		$stmt3->bind_param('ss',$pointName2,$queryresult1);
		$stmt3->execute();
		$verify_query3 = $stmt3->get_result();
		
		if(mysqli_num_rows($verify_query2)>0 || mysqli_num_rows($verify_query3)>0){
			$response["success"] = 2; //Exist a point with the submitted name
			mysqli_close($con);
			echo json_encode($response);
		}
		else{
			//obtaining current total distance
			$dist_res=$con->prepare("SELECT dist_total
							FROM POINT
							WHERE pointid = (SELECT MAX(pointid) FROM POINT WHERE folder = ?)");
			$dist_res->bind_param('s',$queryresult1);
			$dist_res->execute();
			$dist_res1 = $dist_res->get_result();
			
			$dist_result ='none';
			while($row = $dist_res1->fetch_array()){
				$dist_result = $row["dist_total"];
			}
			
			//obtaining current total distance with elevation
			$distWithElevation_res=$con->prepare("SELECT dist_total_e
							FROM POINT
							WHERE pointid = (SELECT MAX(pointid) FROM POINT WHERE folder = ?)");
			$distWithElevation_res->bind_param('s',$queryresult1);
			$distWithElevation_res->execute();
			$dist_res2 = $distWithElevation_res->get_result();
			
			$distWithElevation_result ='none';
			while($row = $dist_res2->fetch_array()){
				$distWithElevation_result = $row["dist_total_e"];
			}
			
			if($dist_result=='none' && $distWithElevation_result=='none'){
				//Cancel auto commit option in the database
				mysqli_autocommit($con, FALSE);
			
				//Insert first point
				$stmt4 = $con->prepare("INSERT INTO POINT (pointname,latitude,longitude,elevation,folder)
									VALUES (?,?,?,?,?)");
				$stmt4->bind_param('sssss',$pointName1,$latitude1,$longitude1,$elevation1,$queryresult1);
				$stmt4->execute();
			
				if($stmt4){
					$result5=$con->query("SELECT MAX(pointid) as max_pointid FROM POINT");
					
					$queryresult5 ='none';
					while($row = $result5->fetch_array()){
						$queryresult5 = $row["max_pointid"];
					}
					
					//Insert the second point
					$stmt6 = $con->prepare("INSERT INTO POINT (pointname,latitude,longitude,elevation,folder,prevpoint,dist_relative,dist_relative_e,elevationdifference)
											VALUES (?,?,?,?,?,?,?,?,?)");
					$stmt6->bind_param('sssssssss',$pointName2,$latitude2,$longitude2,$elevation2,$queryresult1,$queryresult5,$distance,$distanceWithElevation,$elevationDifference);
					$stmt6->execute();
					
					if($stmt6){
						$stmt7 = $con->prepare("SELECT FOLDER.comment FROM FOLDER WHERE FOLDER.folderid = ?");
						$stmt7->bind_param('s',$queryresult1);
						$stmt7->execute();
						$verify_query7 = $stmt7->get_result();
						
						$queryresult7 ='none';
						while($row = $verify_query7->fetch_array()){
							$queryresult7 = $row["comment"];
						}
						$commentResult = $queryresult7." ".$comment;
						
						//Insert Comment
						$stmt8 = $con->prepare("UPDATE FOLDER SET comment = ? WHERE folderid = ?");
						$stmt8->bind_param('ss',$commentResult,$queryresult1);
						$stmt8->execute();
						
						if($stmt8){
							$response["success"] = 4; //insert successfully
							mysqli_commit($con);
							mysqli_close($con);
							echo json_encode($response);
						}
						else{
							$response["success"] = 3; //insert not successfully
							mysqli_rollback($con);
							mysqli_close($con);
							echo json_encode($response);
						}
						
						
					}
					else{
						$response["success"] = 3; //insert not successfully
						mysqli_rollback($con);
						mysqli_close($con);
						echo json_encode($response);	
					}
					
				}
				else{
					$response["success"] = 3; //insert not successfully
					mysqli_rollback($con);
					mysqli_close($con);
					echo json_encode($response);
				}
			}//finish if($dist_result=='none')
			else{
				//Cancel auto commit option in the database
				mysqli_autocommit($con, FALSE);
			
				//Insert first point
				$stmt4 = $con->prepare("INSERT INTO POINT (pointname,latitude,longitude,elevation,folder)
									VALUES (?,?,?,?,?)");
				$stmt4->bind_param('sssss',$pointName1,$latitude1,$longitude1,$elevation1,$queryresult1);
				$stmt4->execute();
			
				if($stmt4){
					$result5=$con->query("SELECT MAX(pointid) as max_pointid FROM POINT");
					
					$queryresult5 ='none';
					while($row = $result5->fetch_array()){
						$queryresult5 = $row["max_pointid"];
					}
					//obtaining total distance and total distance with elevation
					$total_distance = $dist_result + $distance;
					$total_distanceWithElevation = $distWithElevation_result + $distanceWithElevation;
					//Insert the second point
					$stmt6 = $con->prepare("INSERT INTO POINT (pointname,latitude,longitude,elevation,folder,prevpoint,dist_total,dist_relative,dist_total_e,dist_relative_e,elevationdifference)
											VALUES (?,?,?,?,?,?,?,?,?,?,?)");
					$stmt6->bind_param('sssssssssss',$pointName2,$latitude2,$longitude2,$elevation2,$queryresult1,$queryresult5,$total_distance,$distance,$total_distanceWithElevation,$distanceWithElevation,$elevationDifference);
					$stmt6->execute();
					
					if($stmt6){
						$stmt7 = $con->prepare("SELECT FOLDER.comment FROM FOLDER WHERE FOLDER.folderid = ?");
						$stmt7->bind_param('s',$queryresult1);
						$stmt7->execute();
						$verify_query7 = $stmt7->get_result();
						
						$queryresult7 ='none';
						while($row = $verify_query7->fetch_array()){
							$queryresult7 = $row["comment"];
						}
						$commentResult = $queryresult7." ".$comment;
						
						//Insert Comment
						$stmt8 = $con->prepare("UPDATE FOLDER SET comment = ? WHERE folderid = ?");
						$stmt8->bind_param('ss',$commentResult,$queryresult1);
						$stmt8->execute();
						
						if($stmt8){
							$response["success"] = 4; //insert successfully
							mysqli_commit($con);
							mysqli_close($con);
							echo json_encode($response);
						}
						else{
							$response["success"] = 3; //insert not successfully
							mysqli_rollback($con);
							mysqli_close($con);
							echo json_encode($response);
						}
						
						
					}
					else{
						$response["success"] = 3; //insert not successfully
						mysqli_rollback($con);
						mysqli_close($con);
						echo json_encode($response);	
					}
					
				}
				else{
					$response["success"] = 3; //insert not successfully
					mysqli_rollback($con);
					mysqli_close($con);
					echo json_encode($response);
				}
			}//finish else for if($dist_result=='none')
		
			
		}
	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>