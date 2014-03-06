<?php
/*Code that insert a new project.*/

//array for JSON response
$response = array();

if(isset($_POST['username'])&&isset($_POST['projectName']) && isset($_POST['projectDescription'])&&isset($_POST['projectFolderName'])){
	$username = $_POST['username'];
	$projectName = $_POST['projectName'];
	$projectDescription = $_POST['projectDescription'];
	$projectFolderName = $_POST['projectFolderName'];
	
	$con = mysqli_connect("localhost","root","agrimeterdb13","agrimeterDB");
	
	if(mysqli_connect_errno()){
		$response["success"] = 1; //error trying to connect to database
		echo json_encode($response);
	}
	else{
		//Verify if currently exist an project with this name under the user account. 
		$stmt1 = $con->prepare("SELECT FOLDER.foldername
								FROM USERS,COLLABORATORS,FOLDER
								WHERE USERS.username = ?
								and USERS.userid = COLLABORATORS.userid
								and COLLABORATORS.folderid = FOLDER.folderid
								and FOLDER.isroot = 1
								and FOLDER.foldername = ?");
		$stmt1->bind_param('ss',$username,$projectName);
		$stmt1->execute();
		$verify_query = $stmt1->get_result();
		
		if(mysqli_num_rows($verify_query)>0){
			$response["success"] = 2; //Exist a project with this name.
			mysqli_close($con);
			echo json_encode($response);
		}
		else{
			//Cancel auto commit option in the database
			mysqli_autocommit($con, FALSE);
			
			//Insert new project
			$stmt2 = $con->prepare("INSERT INTO FOLDER (foldername,comment,isroot,isdatafolder)
									VALUES (?,?,1,0)");
			$stmt2->bind_param('ss',$projectName,$projectDescription);
			$stmt2->execute();
			
			if($stmt2){
				$stmt3 = $con->prepare("SELECT USERS.userid
										FROM USERS
										WHERE USERS.username = ?");
				$stmt3->bind_param('s',$username);
				$stmt3->execute();
				$result3 = $stmt3->get_result();
				
				$queryresult3 ='none';
				while($row = $result3->fetch_array()){
					$queryresult3 = $row["userid"];
				}
				
				$result4=$con->query("SELECT MAX(folderid) as max_folderid FROM FOLDER");
				$queryresult4 ='none';
				while($row = $result4->fetch_array()){
					$queryresult4 = $row["max_folderid"];
				}
				
				$stmt5 = $con->prepare("INSERT INTO COLLABORATORS (userid,folderid,iscreator)
										VALUES (?,?,1)");
				$stmt5->bind_param('ss',$queryresult3,$queryresult4);
				$stmt5->execute();
				
				if($stmt5){
					//Insert folder under the project created
					$result6=$con->query("SELECT MAX(folderid) as max_folderid FROM FOLDER");
					$queryresult6 ='none';
					while($row = $result6->fetch_array()){
						$queryresult6 = $row["max_folderid"];
					}
					
					$stmt7 = $con->prepare("INSERT INTO FOLDER (foldername,isroot,rootid,isdatafolder,parent)
									VALUES (?,0,?,1,?)");
					$stmt7->bind_param('sss',$projectFolderName,$queryresult6,$queryresult6);
					$stmt7->execute();
					
					if($stmt7){
						$result8=$con->query("SELECT MAX(folderid) as max_folderid FROM FOLDER");
						$queryresult8 ='none';
						while($row = $result8->fetch_array()){
							$queryresult8 = $row["max_folderid"];
						}
						
						$stmt9 = $con->prepare("INSERT INTO COLLABORATORS (userid,folderid,iscreator)
										VALUES (?,?,1)");
						$stmt9->bind_param('ss',$queryresult3,$queryresult8);
						$stmt9->execute();
						
						if($stmt9){
							$response["success"] = 7; //insert successfully
							mysqli_commit($con);
							mysqli_close($con);
							echo json_encode($response);
						}
						else{
							$response["success"] = 6; //insert not successfully
							mysqli_rollback($con);
							mysqli_close($con);
							echo json_encode($response);
						}
						
					}
					else{
						$response["success"] = 5; //insert not successfully
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
			else{
				$response["success"] = 3; //insert not successfully
				mysqli_rollback($con);
				mysqli_close($con);
				echo json_encode($response);
			}
		}
	}
}
else{
	$response["success"] = 0; //problem with the submitted parameters.
	echo json_encode($response);
}
?>