<?php
    require_once 'Zend/Loader/Autoloader.php';

class Zend_Controller_Action_Helper_Validatefunctions extends Zend_Controller_Action_Helper_Abstract
{
	//Validates input data
    function validateorg($orgname,$first_name,$last_name,$country,$zip,$state,
    						$city,$address,$email,$website,$phone,$username,$password,$conf_password){
		Zend_Loader_Autoloader::getInstance();
		$errors = array();
		try{
			/*
			$val = new Zend_Validate_Alnum();
			if(!$val->isValid($orgname)){
				$errors['orgname'] = 'Invalid: Alphanumeric characters only';
			}
			*/
		
			$val = new Zend_Validate_Regex('/^[a-z]+[-\'a-z ]+$/i');//One letter followed by one more letter,hyphen, apostrophe, or space
			if(!$val->isValid($orgname)){
				$errors['orgname'] = 'Invalid: Alphanumeric characters only';
			}
			
			if(!$val->isValid($first_name)){
				$errors['first_name'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
			}
		
			if(!$val->isValid($last_name)){
				$errors['last_name'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
			}
		
			if(!$val->isValid($country)){
				$errors['country'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
			}
		
			if(!$val->isValid($state)){
				$errors['state'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
			}
		
			if(!$val->isValid($city)){
				$errors['city'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
			}
		
			$val = new Zend_Validate_Regex('/^[a-z]+[-\'\.\#\t 0-9 a-z ]+$/i');
			if(!$val->isValid($address)){
				$errors['address'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
			}
		
			$val = new Zend_Validate_EmailAddress();
			if (!$val->isValid($email)){
				$errors['email'] = 'Use a valid email address';	
			}
		
			$val = new Zend_Validate_Hostname();
			if (!$val->isValid($website)){
				$errors['website'] = 'Not a valid website';	
			}
		
		
			$val = new Zend_Validate();
			$length = new Zend_Validate_StringLength(5,5);
			$val->addValidator($length);
			$val->addValidator(new Zend_Validate_Int());
			if (!$val->isValid($zip)){
				$errors['zip'] = 'Not a valid Zip code';	
			}
	
/*			
			$val = new Zend_Validate();
			$length = new Zend_Validate_StringLength(10,10);
			$val->addValidator($length);
			$val->addValidator(new Zend_Validate_Int());
			if(!$val->isValid($phone)){
				$errors['phone']	= '10 digits required';
			}
*/		
			$val = new Zend_Validate();
			$length = new Zend_Validate_StringLength(6,30);
			$val->addValidator($length);
			$val->addValidator(new Zend_Validate_Alnum());
			if(!$val->isValid($username)){
				$errors['username']	= 'Use 6-15 letters or numbers only';
			}
			else{
				//check database for duplicate username
				$registry = Zend_Registry::getInstance();  
				$DB = $registry['DB'];
				
				$sql = $DB->quoteInto('SELECT username FROM USERS WHERE username =?',$username);
				$result	= $DB->fetchAll($sql);
				if($result){
					$errors['username']	= $username . ' is already in use';
				}
			}
		
			$length->setMin(8);
			$val = new Zend_Validate();
			$val->addValidator($length);
			$val->addValidator(new Zend_Validate_Alnum());
			if(!$val->isValid($password)){
				$errors['password']	= 'Use 8-15 letters or numbers only';
			}
		
			$val = new Zend_Validate_Identical($password);
			if(!$val->isValid($conf_password)){
				$errors['conf_password']	= "Passwords don't match";
			}
		}
		catch(Exception $e) {
			echo $e->getMessage();
		}
		
		return $errors;
    }
    
    //Get all organizations registered in the system
    function getorgslist(){
    	$registry = Zend_Registry::getInstance();  
		$DB = $registry['DB'];
		$organizations = $DB->fetchAssoc('SELECT orgname,userid FROM ORGANIZATION');
		return $organizations;
    }
    
    
    function validatesurv($first_name,$last_name,$state,$country,
  						$address,$city,$zip,$phone,$email,$orgid,$username,$password,$conf_password){
		Zend_Loader_Autoloader::getInstance();
		$errors = array();
		$val = new Zend_Validate_Regex('/^[a-z]+[-\'a-z ]+$/i');//One letter followed by one more letter,hyphen, apostrophe, or space
		if(!$val->isValid($first_name)){
			$errors['first_name'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($last_name)){
			$errors['last_name'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($country)){
			$errors['country'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($state)){
			$errors['state'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($city)){
			$errors['city'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		$val = new Zend_Validate_Regex('/^[a-z]+[-\'\.\#\t 0-9 a-z ]+$/i');
		if(!$val->isValid($address)){
			$errors['address'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		$val = new Zend_Validate_EmailAddress();
		if (!$val->isValid($email)){
			$errors['email'] = 'Use a valid email address';	
		}
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(5,5);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Int());
		if (!$val->isValid($zip)){
			$errors['zip'] = 'Not a valid Zip code';	
		}
		
		/*
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(10,10);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Int());
		if(!$val->isValid($phone)){
			$errors['phone']	= '10 digits required';
		}	
		*/
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(6,30);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Alnum());
		if(!$val->isValid($username)){
			$errors['username']	= 'Use 6-15 letters or numbers only';
		}
		else{
			//check database for duplicate username
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			
			$sql = $DB->quoteInto('SELECT username FROM USERS WHERE username =?',$username);
			$result	= $DB->fetchAll($sql);
			if($result){
				$errors['username']	= $username . ' is already in use';
			}
		}
		
		$length->setMin(8);
		$val = new Zend_Validate();
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Alnum());
		if(!$val->isValid($password)){
			$errors['password']	= 'Use 8-15 letters or numbers only';
		}
		
		$val = new Zend_Validate_Identical($_POST['password']);
		if(!$val->isValid($conf_password)){
			$errors['conf_password']	= "Passwords don't match";
		}
		return $errors;
    }
    
    function validatefolder($foldername,$comment){
		Zend_Loader_Autoloader::getInstance();
		$errors = array();
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(1,35);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Alnum());
		

		if(!$val->isValid($foldername)){
			$errors['foldername'] = 'Invalid Name: Alphanumeric characters only';
		}
		
		else{
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			$session = new Zend_Session_Namespace('user_info');
			$currentfolder = $session->current_folder;
			//Set variables for global scope
			$select = NULL;
			$parent = NULL;
			
			//Look for parent
			if(is_int($currentfolder)){
				$select = $DB->select();
				$select	->from('FOLDER', array('parent'));
				$select->where('folderid = ?', $currentfolder);
				$parent = $DB->fetchOne($select);
				
				$select = $DB->select();
		  		$select	->from('FOLDER', array('foldername'));
				$select	->where('FOLDER.parent = ?',$parent);
				$select->where('foldername = ?', $foldername);
			}
			else{//You will be adding a root folder
				$select = $DB->select();
		  		$select	->from('FOLDER', array('foldername'));
		  		$select	->join('COLLABORATORS', 'COLLABORATORS.folderid = FOLDER.rootid', array());
		  		$select	->where('COLLABORATORS.userid = ?', $session->userid);
		  		$select	->where('FOLDER.parent IS NULL',array());
		  		$select->where('foldername = ?', $foldername);
			}
		
			//check database for duplicate folder in same folder
		  	
		  	$result = $DB->fetchOne($select);	
			
			if($result){
				$errors['foldername']	= $result . ' is already in use in same directory';
			}
		}
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(0,150);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Regex('/^[a-z]+[-\'\.\#\t 0-9 a-z ]+$/i'));
		if(!$val->isValid($comment)){
			$errors['comment'] = 'Up to 150 characters. Only letters, hyphens, apostrophes and spaces';
		}
		return $errors;
    }
  
    function validateuploadfile($filepath){
		$dataread = array();
		$fpoint = -1; //folder pointer
		$ppoint	= 0; //point pointer
		$lastfolder = ''; //lastfolder name
		$fp = fopen($filepath, "r" );
		while (( $data = fgetcsv ( $fp , 0 , "," )) !== FALSE ) { // While more lines to read
		//$line[0]foldername [1]pointname [2]lat. [3]lat. dir. [4]long [5]long. dir [6]alt.
		//$dataread[$i] = ('foldername'=>$line[0],array('pointname'=>$line[1],array('lat'=>$line[2],'long'=>$line[4],'alt'=>$line[6])));
			if($data[0]==$lastfolder){ //point belong to same previous folder
				$ppoint++;
			}
			else{ // new folder
				$ppoint = 0;
				$fpoint++;
				$dataread[$fpoint]['fname'] 				= $data[0];// set new foldername
				$lastfolder =  $data[0];
				//echo $dataread[$fpoint]['fname'] . "<br>";
			}
				$dataread[$fpoint][1][$ppoint]['pname']	= $data[1];// set new point name
		
				$lat = substr($data[2], 0,2) +substr($data[2], 2,8)/60;
				if(strcmp($data[3], 'S') == 0)
					$lat = $lat * (-1);
				$dataread[$fpoint][1][$ppoint]['lat']	= $lat;//set latitude
		
				$long = substr($data[4], 0,3) + substr($data[4], 3,9)/60;
				if(strcmp($data[5], 'W') == 0)
					$long = $long * (-1);
				$dataread[$fpoint][1][$ppoint]['long']	= $long;//set longitude
		
				$dataread[$fpoint][1][$ppoint]['alt'] 	= $data[6];//set altitude
		}
		fclose ($fp);
		return $dataread;

		
    }
    
    private function distance($lat1,$long1,$elev1,$lat2,$long2,$elev2){
		
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
		$lamda_PI = 2*PI;
		
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
		
		//pythagoras theorem
		$x = $b*$A*($sigma-$delta_sigma);
		$y = $elev2 - $elev1;
		$z = sqrt(pow($x,2)+pow($y,2));

		return $z;
	}
	
	function validateupdatefolder($folderid,$foldername,$comment){
		Zend_Loader_Autoloader::getInstance();
		$errors = array();
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(1,35);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Alnum());
		
		
		

		if(!$val->isValid($foldername)){
			$errors['foldername'] = 'Invalid Name: Alphanumeric characters only';
		}
		
		else{
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			$session = new Zend_Session_Namespace('user_info');
			$currentfolder = $session->current_folder;

			//Set variables for global scope
			$select = NULL;
			$parent = NULL;
			
			//Look for parent
			$select = $DB->select();
			$select	->from('FOLDER', array('parent'));
			$select->where('folderid = ?', $currentfolder);
			$parent = $DB->fetchOne($select);
			
			if($parent == NULL){
				$select = $DB->select();
				$select	->from('FOLDER', array('folderid'));
				$select	->join('COLLABORATORS', 'COLLABORATORS.folderid = FOLDER.rootid', array());
				$select	->where('COLLABORATORS.userid = ?', $session->userid);
				$select	->where('FOLDER.parent IS NULL',array());
				$select->where('foldername = ?', $foldername);
			}
			
			else{
				$select = $DB->select();
				$select	->from('FOLDER', array('folderid'));
				$select	->where('FOLDER.parent = ?',$parent);
				$select->where('foldername = ?', $foldername);
			}
		
			//check database for duplicate folder in same folder
		  	
		  	$result = $DB->fetchOne($select);

			if($result){
				if($result != $currentfolder){
					$errors['foldername']	= $foldername . ' is already in use in same directory';
				}
			}
		}
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(0,150);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Regex('/^[a-z]+[-\'\.\#\t 0-9 a-z ]+$/i'));
		if(!$val->isValid($comment)){
			$errors['comment'] = 'Up to 150 characters. Only letters, hyphens, apostrophes and spaces';
		}
		return $errors;
    }
    
    function validatesurvupdates($first_name,$last_name,$state,$country,
  						$address,$city,$zip,$phone,$email,$username,$oldpassword,$password,$conf_password){
  		$registry = Zend_Registry::getInstance();  				
  		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');
		$userid = $session->userid;
  						
		Zend_Loader_Autoloader::getInstance();
		$errors = array();
		$val = new Zend_Validate_Regex('/^[a-z]+[-\'a-z ]+$/i');//One letter followed by one more letter,hyphen, apostrophe, or space
		if(!$val->isValid($first_name)){
			$errors['first_name'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($last_name)){
			$errors['last_name'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($country)){
			$errors['country'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($state)){
			$errors['state'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($city)){
			$errors['city'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		$val = new Zend_Validate_Regex('/^[a-z]+[-\'\.\#\t 0-9 a-z ]+$/i');
		if(!$val->isValid($address)){
			$errors['address'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		$val = new Zend_Validate_EmailAddress();
		if (!$val->isValid($email)){
			$errors['email'] = 'Use a valid email address';	
		}
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(5,5);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Int());
		if (!$val->isValid($zip)){
			$errors['zip'] = 'Not a valid Zip code';	
		}
		
		/*
		$length = new Zend_Validate_StringLength(array('min' => 10, 'max' => 10));
		if (!$length->isValid($phone)){
			$errors['phone']	= '10 digits required';	
		}
		
		$val = new Zend_Validate_Int();
		if (!$val->isValid($phone)){
			$errors['phone']	= '10 digits required';	
		}
		*/
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(6,30);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Alnum());
		if(!$val->isValid($username)){
			$errors['username']	= 'Use 6-15 letters or numbers only';
		}
		else{
			//check database for duplicate username
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			
			$sql = $DB->quoteInto('SELECT userid FROM USERS WHERE username =?',$username);
			$result	= $DB->fetchOne($sql);
			if($result){
				if($result != $userid)
					$errors['username']	= $username . ' is already in use';
			}
		}
		//$oldpassword,$password,$conf_password
		
		if($oldpassword!= NULL){
			//Validate old password
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			$select = $DB->select();
			$select	->from('USERS', array('password'));
			$select->where('userid = ?', $userid);
			$result  = $DB->fetchOne($select);
			if($result){
				if(strcmp($result,$oldpassword)){
					$errors['oldpassword']	= 'Invalid old password';
				}
			}
	
			$length->setMin(8);
			$val = new Zend_Validate();
			$val->addValidator($length);
			$val->addValidator(new Zend_Validate_Alnum());
			if(!$val->isValid($password)){
				$errors['password']	= 'Use 8-15 letters or numbers only';
			}
		
			$val = new Zend_Validate_Identical($_POST['password']);
			if(!$val->isValid($conf_password)){
				$errors['conf_password']	= "Passwords don't match";
			}
		}
		return $errors;
    }
 
    function validateorgupdates($orgname,$website,$state,$country,
  						$address,$city,$zip,$phone,$email,$username,$oldpassword,$password,$conf_password){
  		$registry = Zend_Registry::getInstance();  				
  		$DB = $registry['DB'];
		$session = new Zend_Session_Namespace('user_info');
		$userid = $session->userid;
  						
		Zend_Loader_Autoloader::getInstance();
		$errors = array();
		
		$val = new Zend_Validate_Hostname();
		if (!$val->isValid($website)){
			$errors['website'] = 'Not a valid website';	
		}
		
		$val = new Zend_Validate_Regex('/^[a-z]+[-\'a-z ]+$/i');//One letter followed by one more letter,hyphen, apostrophe, or space
		if(!$val->isValid($orgname)){
			$errors['orgname'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($country)){
			$errors['country'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($state)){
			$errors['state'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		if(!$val->isValid($city)){
			$errors['city'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		$val = new Zend_Validate_Regex('/^[a-z]+[-\'\.\#\t 0-9 a-z ]+$/i');
		if(!$val->isValid($address)){
			$errors['address'] = 'Required field. Only letters, hyphens, apostrophes and spaces';
		}
		
		$val = new Zend_Validate_EmailAddress();
		if (!$val->isValid($email)){
			$errors['email'] = 'Use a valid email address';	
		}
		
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(5,5);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Int());
		if (!$val->isValid($zip)){
			$errors['zip'] = 'Not a valid Zip code';	
		}

		/*
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(10,10);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Int());
		if(!$val->isValid($phone)){
			$errors['phone']	= '10 digits required';
		}	
		*/
		$val = new Zend_Validate();
		$length = new Zend_Validate_StringLength(6,30);
		$val->addValidator($length);
		$val->addValidator(new Zend_Validate_Alnum());
		if(!$val->isValid($username)){
			$errors['username']	= 'Use 6-15 letters or numbers only';
		}
		else{
			//check database for duplicate username
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			
			$sql = $DB->quoteInto('SELECT userid FROM USERS WHERE username =?',$username);
			$result	= $DB->fetchOne($sql);
			if($result){
				if($result != $userid)
					$errors['username']	= $username . ' is already in use';
			}
		}
		//$oldpassword,$password,$conf_password
		
		if($oldpassword!= NULL){
			//Validate old password
			$registry = Zend_Registry::getInstance();  
			$DB = $registry['DB'];
			$select = $DB->select();
			$select	->from('USERS', array('password'));
			$select->where('userid = ?', $userid);
			$result  = $DB->fetchOne($select);
			if($result){
				if(strcmp($result,$oldpassword)){
					$errors['oldpassword']	= 'Invalid old password';
				}
			}
	
			$length->setMin(8);
			$val = new Zend_Validate();
			$val->addValidator($length);
			$val->addValidator(new Zend_Validate_Alnum());
			if(!$val->isValid($password)){
				$errors['password']	= 'Use 8-15 letters or numbers only';
			}
		
			$val = new Zend_Validate_Identical($_POST['password']);
			if(!$val->isValid($conf_password)){
				$errors['conf_password']	= "Passwords don't match";
			}
		}
		return $errors;
    }
    
    
    
}
?>