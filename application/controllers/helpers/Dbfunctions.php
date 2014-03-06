<?php

class Zend_Controller_Action_Helper_Dbfunctions extends Zend_Controller_Action_Helper_Abstract
{
	//Return a list of the children of the current folder ($parentfolder)
    function listfolders($currentfolder){
    	//Get instance for DB connection
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');

		//If $currentfolder==NULL, you are in the root folder
		if($currentfolder==NULL){
			$select = $DB->select();
			$select	->from('FOLDER', array('folderid','foldername'));
			$select	->join('COLLABORATORS', 'COLLABORATORS.folderid = FOLDER.folderid', array());
			$select	->where('COLLABORATORS.userid = ?', $session->userid);
			$select	->where('FOLDER.parent IS NULL',array());
			$folderlist = $DB->fetchAll($select);
		}

		else{
			$select = $DB->select();
			$select->from('FOLDER', array('folderid','foldername','parent'));
			$select->where('parent = ?', $currentfolder);
			$folderlist = $DB->fetchAll($select);
		}
	
		return $folderlist;
    }
    
    function listpoints($currentfolder){
    	//Get instance for DB connection
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];

		$select = $DB->select();
		$select	->from('POINT', array('pointid','pointname','latitude','longitude','elevation','prevpoint','dist_total','dist_relative','dist_total_e','dist_relative_e','elevationdifference'));
		$select	->where('folder = ?', $currentfolder);
		$select ->order(array('pointid ASC'));
		$pointlist = $DB->fetchAll($select);
		
		return $pointlist;
    }
    
    
    function forp($currentfolder){
    	if($currentfolder == NULL){
    		return 0;
    	}
    	
    	//Get instance for DB connection
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
		//Check if $current folder is a data folder
		$select = $DB->select();
		$select	->from('FOLDER', array('isdatafolder'));
		$select->where('folderid = ?', $currentfolder);
		$result  = $DB->fetchOne($select);
		return $result;
    }
    
    
   
    function insertOrganization($username,$password,$address,$city,$state,$country,$zip,
    							$phone,$email,$orgname,$first_name,$last_name,$website)
    {
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		// begin the transaction
		$DB->beginTransaction();
		try{
		  $data = array('username'	=> $username,
						'password'	=> $password,
						'address'	=> $address,
						'city'		=> $city,
						'state'		=> $state,
						'country'	=> $country,
						'zip'		=> $zip,
						'phone'		=> $phone,
						'email'		=> $email);
		  $result = $DB->insert('USERS', $data);
		  
		  if ($result) {
			  // grab the userid from the last insert
			  $userid = $DB->lastInsertId('USERS','userid');
			  $data = array(  'userid'			=> $userid,
							  'orgname'			=> $orgname,
							  'creatorfname'	=> $first_name,
							  'creatorlname'	=> $last_name,
							  'website'			=> $website);
			  $result = $DB->insert('ORGANIZATION', $data);
			  // on success, commit transaction and return the user_id
			  if ($result) {
				  $DB->commit();
			  }
		  }
		}
		catch (Exception $e) {
			$DB->rollBack();
			throw $e;
		}
	}
	
	function insertSurveyor($first_name,$last_name,$state,$country,$address,$city,$zip,
							$phone,$email,$orgid,$username,$password,$conf_password){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$DB->beginTransaction();
		try{
		  $data = array('username'=> $username,
						'password'=> $password,
						'address'	=> $address,
						'city'	=> $city,
						'state'	=> $state,
						'country'	=> $country,
						'zip'		=> $zip,
						'phone'	=> $phone,
						'email'	=> $email);
		  $result = $DB->insert('USERS', $data);
		  
		  if ($result) {
			  // grab the userid from the last insert
			  $userid = $DB->lastInsertId('USERS','userid');
			  
			  if($orgid =='NULL')
					$orgid = NULL;
			  else
					$orgid = (int)$orgid;
			  
			  $data = array(  'userid'		=> $userid,
							  'firstname'	=> $first_name,
							  'lastname'	=> $last_name,
							  'orgid'		=> $orgid,
							  'isactive'	=> 0);
			  $result = $DB->insert('SURVEYOR', $data);
			  // on success, commit transaction and return the user_id
			  if ($result) {
				  $DB->commit();
			  }
		  }
		}
		catch (Exception $e) {
			$dbWrite->rollBack();
			throw $e;
		}					
	}

	
	function insertfolder($foldername,$comment,$isdatafolder){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');
		$userid = $session->userid;
		$currentfolder = $session->current_folder;
		
		//Set variables to be used either adding rootfolder or not
		$isdata = 0;
		$isroot = null;
		$rootid = null;
		$parent = null;
 			
		if(!is_int($currentfolder)){// You are adding a root folder
			$isroot = 1;	
		  		
	  	}
		else{ //You are adding an inner folder
			//Need to find the rootid of current folder
		  	$select = $DB->select();
			$select	->from('FOLDER', array('rootid'));
			$select	->where('FOLDER.folderid = ?',$currentfolder);
			$rootid = $DB->fetchOne($select);
			$parent = $currentfolder;
			$isroot = 0;
		}

		// begin the transaction
		$DB->beginTransaction();
		try{		
			$data = array('foldername'=> $foldername,
						  'comment'	=> $comment,
						  'isroot'	=> $isroot,
						  'rootid'	=> $rootid,
						  'isdatafolder'	=> $isdatafolder,
						  'parent'	=> $currentfolder);
			$result = $DB->insert('FOLDER', $data);
			
			if(!is_int($currentfolder)){// Make root id the folder itself
				// grab the folderid from the last insert
				$folderid = $DB->lastInsertId('FOLDER','folderid');
				if($isdatafolder == 1)
					$session->current_folder = $folderid;
				
				

				$data = array(  'rootid'		=> $folderid);
				$where = array('folderid = ?' => $folderid);
			  	$result = $DB->update('FOLDER', $data, $where);
			  	
			  	//Insert user - folder relation
				$data = array('userid'		=> $userid,
						  	'folderid'	=> $folderid,
						  	'iscreator'	=> 1);
				$result = $DB->insert('COLLABORATORS', $data);
				
	  		}
	  		else if($isdatafolder == 1)
	  			$session->current_folder = $folderid;
	  		
			// on success, commit transaction and return the user_id
			$DB->commit();
			
		}
		catch(Exception $e){
			$DB->rollBack();
    		throw $e;
		}
		
	}
	
	function insertpoints($points){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');
		$currentfolder = $session->current_folder;
		$dist_total 	= 0;
		$dist_total_e 	= 0;
		$prevpointid	= null;
				
		for($i=0;$i<count($points);$i++){
			$pointname 	= $points[$i]['pname'];
			$latitude 	= $points[$i]['lat'];
			$longitude	= $points[$i]['long'];
			$elevation	= $points[$i]['alt'];
			$folder 	= $currentfolder;
			if($i == 0){
				$prevpoint			= null;
				$dist_total			= 0;
				$dist_relative		= 0;
				$dist_total_e		= 0;
				$dist_relative_e	= 0;
				$elevationdifference= 0;
			}
			else{
				$prevpoint = $points[$i-1];
				$d_rel = $this->distance($prevpoint['lat'],$prevpoint['long'],$prevpoint['alt'],$latitude,$longitude,$elevation);
				$dist_relative 		= $d_rel[0];
				$dist_relative_e	= $d_rel[1];
				$dist_total			= $dist_total + $dist_relative;
				$dist_total_e		= $dist_total_e + $dist_relative_e;
				$elevationdifference= $points[$i]['alt'] - $prevpoint['alt'];
			}
			
			// Insert point into DB
			$DB->beginTransaction();
			try{	
				$data = array('pointname'=> $pointname,
							  'latitude'	=> $latitude,
							  'longitude'	=> $longitude,
							  'elevation'	=> $elevation,
							  'folder'	=> $folder,
							  'prevpoint'	=> $prevpointid,
							  'dist_total'	=> $dist_total,
							  'dist_total_e'	=> $dist_total_e,
							  'dist_relative'	=> $dist_relative,
							  'dist_relative_e'	=> $dist_relative_e,
							  
							  'elevationdifference'	=> $elevationdifference);
	
				$result = $DB->insert('POINT', $data);
				
				$prevpointid = $DB->lastInsertId('POINT','pointid');
			
				// on success, commit transaction and return the user_id
				$DB->commit();
			}
			catch(Exception $e){
				$DB->rollBack();
				throw $e;
			}
	
 		}
 		
 		
	}
	
	
	function backfolder($currentfolder){
    	if($currentfolder == NULL){
    		return NULL;
    	}
    	else{
    		$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			$select = $DB->select();
		  	$select	->from('FOLDER', array('parent'));
		  	$select	->where('FOLDER.folderid = ?',$currentfolder);
		  	return $DB->fetchOne($select);
    	}
    }
    
	function distance($lat1,$long1,$elev1,$lat2,$long2,$elev2){
	
		$lat1 = deg2rad($lat1);
		$long1 = deg2rad($long1);
		$lat2 = deg2rad($lat2);
		$long2 = deg2rad($long2);
	
		//Major semi axe of Earth ellipsoid for GRS80
		$a = 6378137;
		//Minor semi axe of Earth ellipsoid for GRS80
		$b = 6356752.314140;
		//Flattening of ellipsoid for GRS80
		$f = 1/(298.257222101);

		$L = $long2-$long1; //longitude difference
		$u1 = atan((1-$f)*(tan($lat1))); //Reduced latitude1
		$u2 = atan((1-$f)*(tan($lat2))); //Reduced latitude2
		$sin_u1 = sin($u1);
		$cos_u1 = cos($u1);
		$sin_u2 = sin($u2);
		$cos_u2 = cos($u2);
		$lamda = $L; //first approximation
		$lamda_PI = 2*M_PI;
	
		$sin_sigma = 0;
		$cos_sigma = 0;
		$sigma = 0 ;
		$cos_Sq_Alpha = 0;
		$cos2sigma_m = 0;
	
		while (abs($lamda-$lamda_PI) >= 1E-12){
			$sin_lamda = sin($lamda);
			$cos_lamda = cos($lamda);
			$sin_sigma = sqrt((pow(($cos_u2*$sin_lamda), 2))+
					(pow(($cos_u1*$sin_u2-$sin_u1*$cos_u2*$cos_lamda), 2)));
			$cos_sigma = $sin_u1*$sin_u2+ $cos_u1*$cos_u2*$cos_lamda;
			$sigma = atan2($sin_sigma, $cos_sigma);
			$sin_alpha = $cos_u1*$cos_u2*$sin_lamda/$sin_sigma;
			//Trigonometric identity
			$cos_Sq_Alpha = 1 - pow($sin_alpha, 2);
			//-----------------
			$cos2sigma_m = $cos_sigma - 2*$sin_u1*$sin_u2/$cos_Sq_Alpha;
		
			if (is_nan($cos2sigma_m)){
				$cos2sigma_m = 0;
			}
		
			$C = $f/16*$cos_Sq_Alpha*(4+$f*(4-3*$cos_Sq_Alpha));
			$lamda_PI = $lamda;
			$lamda = $L+(1-$C)*$f*$sin_alpha*($sigma+$C*$sin_sigma*($cos2sigma_m+$C*$cos_sigma*(2*pow($cos2sigma_m, 2)-1)));
		}
	
		$uSq = $cos_Sq_Alpha*(pow($a, 2)-pow($b, 2))/pow($b, 2);
		$A = 1+$uSq/16384*(4096+$uSq*($uSq*(320-175*$uSq)-768));
		$B = $uSq/1024*(256+$uSq*($uSq*(74-47*$uSq)-128));
		$delta_sigma = $B*$sin_sigma*($cos2sigma_m+$B/4*($cos_sigma*(2*pow($cos2sigma_m, 2)-1)-$B/6*$cos2sigma_m*(4*pow($sin_sigma, 2)-3)*(4*pow($cos2sigma_m, 2)-3)));
	
		$z = array();
		$z[0] = abs($b*$A*($sigma-$delta_sigma));
	
		//pythagoras theorem
		$x = $b*$A*($sigma-$delta_sigma);
		$y = $elev2 - $elev1;
		$z[1] = sqrt(pow($x,2)+pow($y,2));

		return $z;
	}
	
	function collaboratorcandidates($username,$folderid){
		//Get instance for DB connection
		$registry = Zend_Registry::getInstance();
		$DB = $registry['DB'];
		  		
		//Verify if the user is on the root, you can't share root  		
		if(!isset($folderid)){
			return -1;
		}
		
		//Veryfy that the user belongs to an organization and if he is an active member 
		$select = $DB->select();
		$select	->from('SURVEYOR', array('orgid','isactive'));
		$select	->join('USERS', 'USERS.userid = SURVEYOR.userid', array());
		$select	->where('USERS.username = ?',$username);
		$result = $DB->fetchRow($select);
		
		$orgid = $result["orgid"];
		$isactive =$result["isactive"];
		
		if(!isset($orgid) || !isset($isactive) || $isactive ==0){
			return -1;
		}
		  
		//look rootid
		$select = $DB->select();
		$select	->from('FOLDER', array('rootid'));
		$select	->where('FOLDER.folderid = ?',$folderid);
		$root = $DB->fetchOne($select);
		
		//Look for organization that the user belongs
		$select = $DB->select();
		$select	->from('SURVEYOR', array('orgid'));
		$select	->join('USERS', 'USERS.userid = SURVEYOR.userid', array());
		$select	->where('USERS.username = ?',$username);
		$orgid = $DB->fetchOne($select);
		 		
		$stmt = $DB->query(
            	"SELECT B.username, SURVEYOR.userid
				FROM USERS U
				JOIN COLLABORATORS ON U.userid = COLLABORATORS.userid
				AND COLLABORATORS.folderid = ?
				RIGHT JOIN SURVEYOR ON COLLABORATORS.userid = SURVEYOR.userid
				JOIN USERS B ON B.userid = SURVEYOR.userid
				WHERE COLLABORATORS.userid IS NULL
				AND SURVEYOR.orgid = ?",
            	array($root, $orgid)
        );
        
        $result = $stmt->fetchAll();
        		
		return $result;
		
    }
    function addcollaborator($userid,$folderid){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
		//look rootid
		$select = $DB->select();
		$select	->from('FOLDER', array('rootid'));
		$select	->where('FOLDER.folderid = ?',$folderid);
		$root = $DB->fetchOne($select);
		
				
		$data = array(	'userid'	=> $userid,
						'folderid'	=> $root,
						'iscreator'	=> 0);
		$result = $DB->insert('COLLABORATORS', $data);
		return $result;
    }
    
    function removecandidates($username,$folderid){
		//Get instance for DB connection
		$registry = Zend_Registry::getInstance();
		$DB = $registry['DB'];
		  		
		//Verify if the user is on the root, you can't share root  		
		if($folderid == NULL){
			return null;
		}
		//Look for userid
		$select = $DB->select();
		$select	->from('USERS', array('userid'));
		$select	->where('USERS.username = ?',$username);
		$userid = $DB->fetchOne($select);
		
		//Check if the user is the creator of the project
		$select = $DB->select();
		$select	->from('COLLABORATORS', array('iscreator'));
		$select	->where('COLLABORATORS.userid = ?',$userid);
		$iscreator = $DB->fetchOne($select);

		if($iscreator == 0){
			return null;
		}
		//look rootid
		$select = $DB->select();
		$select	->from('FOLDER', array('rootid'));
		$select	->where('FOLDER.folderid = ?',$folderid);
		$root = $DB->fetchOne($select);
		
		//Look for Surveyors that share this project and are not creators
		$stmt = $DB->query(
            	"SELECT USERS.username, USERS.userid
				FROM COLLABORATORS
				JOIN USERS ON COLLABORATORS.userid = USERS.userid
				WHERE COLLABORATORS.folderid = ?
				AND COLLABORATORS.iscreator = 0",
            	array($root)
        );
        
        $result = $stmt->fetchAll();
        		
		return $result;
		
    }
    
    function removecollaborator($userid,$folderid){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
		//look rootid
		$select = $DB->select();
		$select	->from('FOLDER', array('rootid'));
		$select	->where('FOLDER.folderid = ?',$folderid);
		$root = $DB->fetchOne($select);
		
		$stmt = $DB->query(
            	"DELETE
				FROM COLLABORATORS
				WHERE COLLABORATORS.folderid = ?
				AND COLLABORATORS.userid = ?",
            	array($root,$userid)
        );
        
        $result = $stmt->execute();
		return $result;
		
    }
    
    function activemembers($orgname){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
		$session = new Zend_Session_Namespace('user_info');
		$orgid = $session->userid;
		
		//Get active members		
		$select = $DB->select();
		$select	->from('SURVEYOR', array('USERS.userid','firstname','lastname','USERS.username'));
		$select	->join('USERS', 'USERS.userid = SURVEYOR.userid', array());
		$select	->where('SURVEYOR.orgid = ?',$orgid);
		$select	->where('SURVEYOR.isactive = 1',array());
		$active = $DB->fetchAll($select);		
		return $active;
    }
    
    function requestsmembers($orgname){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
		/*
		//Get orgid
		$select = $DB->select();
		$select	->from('ORGANIZATION', array('userid'));
		$select	->where('ORGANIZATION.orgname = ?',$orgname);
		$orgid = $DB->fetchOne($select);
		*/
		$session = new Zend_Session_Namespace('user_info');
		$orgid = $session->userid;
		
		//Get active members		
		$select = $DB->select();
		$select	->from('SURVEYOR', array('USERS.userid','firstname','lastname','USERS.username'));
		$select	->join('USERS', 'USERS.userid = SURVEYOR.userid', array());
		$select	->where('SURVEYOR.orgid = ?',$orgid);
		$select	->where('SURVEYOR.isactive != 1',array());
		$requests = $DB->fetchAll($select);		
		return $requests;
    }
    
    function removeactive($removeUserid){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];		
		
		$DB->beginTransaction();
		try{
			$data = array(	'orgid' => NULL,
							'isactive' => 0);
			$where = array('userid = ?' => $removeUserid);
			$DB->update('SURVEYOR', $data, $where);	
			
			$stmt = $DB->query(
								"DELETE
								FROM COLLABORATORS
								WHERE COLLABORATORS.userid = ?",
								array($removeUserid)
			);
	
			$stmt->execute();
			$DB->commit();
			
		}
		catch(Exception $e){
			$DB->rollBack();
    		throw $e;
		}		
    }
    
    function allowrequest($userid,$orgname){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
		/*
		//Get orgid
		$select = $DB->select();
		$select	->from('ORGANIZATION', array('userid'));
		$select	->where('ORGANIZATION.orgname = ?',$orgname);
		$orgid = $DB->fetchOne($select);
		*/
		$session = new Zend_Session_Namespace('user_info');
		$orgid = $session->userid;
		
		$data = array(
		   	'orgid' => $orgid,
        	'isactive' => 1);
		$where = array('userid = ?' => $userid);
		$DB->update('SURVEYOR', $data, $where);
    }
    
    function denyrequest($userid){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];	
		$data = array(
		   	'orgid' => NULL,
        	'isactive' => 0);
		$where = array('userid = ?' => $userid);
		$DB->update('SURVEYOR', $data, $where);
    }
    
    
    function isdata($folderid){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$select = $DB->select();
		$select	->from('FOLDER', array('isdatafolder'));
		$select	->where('FOLDER.folderid = ?',$folderid);
		$isdata = $DB->fetchOne($select);
		return $isdata;	
    } 

    function getfoldernamecomment($folderid){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$select = $DB->select();
		$select	->from('FOLDER', array('folderid','foldername','comment'));
		$select	->where('FOLDER.folderid = ?',$folderid);
		$result = $DB->fetchRow($select);
		return $result;	
    }
    
    function updatefolder($folderid,$foldername,$comment){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$data = array(  'foldername' => $foldername,'comment' => $comment);
		$where = array('folderid = ?' => $folderid);
		$result = $DB->update('FOLDER', $data, $where);
		return $result;	
    }
      
    function deletefolder($folderid){    
		while($this->haschild($folderid)){
			$childfolderid = $this->getchild($folderid);
			$this->deletefolder($childfolderid);
		}	
		$this->erasefolder($folderid);
    }
    
    function erasefolder($folderid){
		$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];		
		
		$DB->beginTransaction();
		try{	
			$stmt = $DB->query(
								"DELETE
								FROM COLLABORATORS
								WHERE folderid = ?",
								array($folderid)
			);
			$stmt->execute();
			
			$stmt = $DB->query(
								"DELETE
								FROM POINT
								WHERE folder = ?",
								array($folderid)
			);
			$stmt->execute();
			
			$stmt = $DB->query(
								"DELETE
								FROM FOLDER
								WHERE folderid = ?",
								array($folderid)
			);
			$stmt->execute();
			
			$DB->commit();
		}
		catch(Exception $e){
			$DB->rollBack();
    		throw $e;
		}
			
    }

    function haschild($folderid){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
    	$select = $DB->select();
		$select	->from('FOLDER', array('folderid'));
		$select	->where('FOLDER.parent = ?',$folderid);
		$result = $DB->fetchAll($select);
		if(!empty($result))
			return TRUE;
		else
			return FALSE;
    }
    
    function getchild($folderid){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
    	$select = $DB->select();
		$select	->from('FOLDER', array('folderid'));
		$select	->where('FOLDER.parent = ?',$folderid);
		$result = $DB->fetchOne($select);
		return $result;
    }
    
    function getsurvinfo($username){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
    	$select = $DB->select();
		$select	->from('SURVEYOR', array('SURVEYOR.firstname','SURVEYOR.lastname','SURVEYOR.orgid','USERS.username','USERS.password','USERS.address','USERS.city','USERS.state','USERS.country','USERS.zip','USERS.phone','USERS.email'));
		$select	->join('USERS', 'USERS.userid = SURVEYOR.userid', array());
		$select	->where('USERS.username = ?',$username);
		$result = $DB->fetchRow($select);
		return $result;	
    }
    
     function updateSurveyor($first_name,$last_name,$state,$country,$address,$city,$zip,$phone,$email,$username,$password){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');
		$userid = $session->userid;			
		
		$DB->beginTransaction();
		try{	
			$data = array(  'firstname' => $first_name,
							'lastname' => $last_name);
			$where = array('userid = ?' => $userid);
			$DB->update('SURVEYOR', $data, $where);
			
			$data = array(  'state' => $state,
							'country' => $country,
							'address' => $address,
							'city' => $city,
							'zip' => $zip,
							'phone' => $phone,
							'email' => $email);
			$where = array('userid = ?' => $userid);
			$DB->update('USERS', $data, $where);
			
			
			if($password != NULL){
				$data = array(  'password' => $password);
				$where = array('userid = ?' => $userid);
				$DB->update('USERS', $data, $where);
			}

			$DB->commit();
		}
		catch(Exception $e){
			$DB->rollBack();
    		throw $e;
		}
    }
    
    function getorginfo($username){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		
    	$select = $DB->select();
		$select	->from('ORGANIZATION', array('ORGANIZATION.orgname','ORGANIZATION.website','USERS.username','USERS.password','USERS.address','USERS.city','USERS.state','USERS.country','USERS.zip','USERS.phone','USERS.email'));
		$select	->join('USERS', 'USERS.userid = ORGANIZATION.userid', array());
		$select	->where('USERS.username = ?',$username);
		$result = $DB->fetchRow($select);
		return $result;	
    }
    
  	 function updateOrganization($orgname,$website,$state,$country,$address,$city,$zip,$phone,$email,$username,$password){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');
		$userid = $session->userid;		
		
		$DB->beginTransaction();
		try{	
			$data = array(  'orgname' => $orgname,
							'website' => $website);
			$where = array('userid = ?' => $userid);
			$DB->update('ORGANIZATION', $data, $where);
			
			$data = array(  'state' => $state,
							'country' => $country,
							'address' => $address,
							'city' => $city,
							'zip' => $zip,
							'phone' => $phone,
							'email' => $email);
			$where = array('userid = ?' => $userid);
			$DB->update('USERS', $data, $where);
			
			
			if($password != NULL){
				$data = array(  'password' => $password);
				$where = array('userid = ?' => $userid);
				$DB->update('USERS', $data, $where);
			}
			$DB->commit();
			
		}
		catch(Exception $e){
			$DB->rollBack();
    		throw $e;
		}
	}
	function getpath($folderid){
		$result = array();
		if($folderid == NULL){
			$result[0] = array('parent' => NULL, 'foldername' =>'HOME', 'folderid' => NULL, 'comment' => NULL);
		}
		else{
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			$i=0;
			//look data of Current folder
			$select = $DB->select();
			$select	->from('FOLDER', array('parent','foldername','folderid','comment'));
			$select	->where('FOLDER.folderid = ?',$folderid);
			$parentfolder = $DB->fetchRow($select);
			$result[$i] = $parentfolder;
			$i++;
			
			while($folderid != NULL){
				//look parent id of current folder
				$select = $DB->select();
				$select	->from('FOLDER', array('parent'));
				$select	->where('FOLDER.folderid = ?',$folderid);
				$parentid = $DB->fetchOne($select);
				
				//Look data of the parent
				if($parentid != NULL){
					$select = $DB->select();
					$select	->from('FOLDER', array('parent','foldername','folderid','comment'));
					$select	->where('FOLDER.folderid = ?',$parentid);
					$parentfolder = $DB->fetchRow($select);
					$result[$i] = $parentfolder;
					$folderid = $parentfolder['folderid'];
				}
				else{
					$result[$i] = array('parent' => NULL, 'foldername' =>'HOME', 'folderid' => NULL,'comment' => NULL);
					$folderid = NULL;
				}
				$i++;
			}
		}
		return $result;
	}
}
	
	
?>