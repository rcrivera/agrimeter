<?php
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Registry.php';

class UserController extends Zend_Controller_Action
{
   public function survregisterAction()
 {
   $request = $this->getRequest();
   $this->view->assign('action',"../folder"); //The user will be redirected to 
 }
 
 //Page that let the user select which type of account
 public function selectaccounttypeAction()
  {	
  	$request = $this->getRequest();
	$this->view->assign('create_org_link', $request->getBaseURL()."/user/orgform"); 
	$this->view->assign('create_surv_link', $request->getBaseURL()."/user/survform");  
   	$this->view->assign('title', 'Select New Account Type');
   	$this->view->assign('org_label', 'New Organization Account');	
   	$this->view->assign('surv_label', 'New Surveyor Account');	
    
 }
 
 public function orgformAction(){
 	$request 	= $this->getRequest();
  	$this->view->assign('action', $request->getBaseURL()."/user/orgformprocess");
  	$this->view->assign('errors', $request->getParam('errors'));
  	$this->view->assign('values', $request->getParam('values'));
  	
  }
  
  public function orgformprocessAction(){
  	$request 		= $this->getRequest();
  	$username 		= $request->getParam('username');
  	$password 		= $request->getParam('password');
  	$conf_password	= $request->getParam('conf_password');
  	$address 		= $request->getParam('address');
  	$city 			= $request->getParam('city');
  	$state 			= $request->getParam('state');
  	$country 		= $request->getParam('country');
  	$zip 			= $request->getParam('zip');
    $phone 			= $request->getParam('phone');
    $email			= $request->getParam('email');
    $orgname		= $request->getParam('orgname');
    $first_name		= $request->getParam('first_name');
    $last_name		= $request->getParam('last_name');
    $website		= $request->getParam('website');
    
    $errors = $this->_helper->validatefunctions->validateorg($orgname,$first_name,$last_name,$country,$zip,$state,
    						$city,$address,$email,$website,$phone,$username,$password,$conf_password);
    
    if(!$errors){
    	$this->_helper->dbfunctions->insertOrganization($username,$password,$address,$city,$state,$country,$zip,
    							$phone,$email,$orgname,$first_name,$last_name,$website);
    	$this->_redirect('/user/loginform');	
    }
    
    else{ //There are errors that need to be corrected in the registration form
		$values = array(
						"username" 		=> $username,
						"address" 		=> $address,
						"city" 			=> $city,
						"state" 		=> $state,
						"country" 		=> $country,
						"zip" 			=> $zip,
						"phone" 		=> $phone,
						"email"			=> $email,
						"orgname"		=> $orgname,
						"first_name"	=> $first_name,
						"last_name"		=> $last_name,
						"website"		=> $website
						);
    	$this->getRequest()->setParam('errors', $errors);
    	$this->getRequest()->setParam('values', $values);
    	$this->_forward('orgform');
    }
  }
 
  public function survformAction(){
  	$request = $this->getRequest();
  	$this->view->assign('action', $request->getBaseURL()."/user/survformprocess");
  	$this->view->assign('errors', $request->getParam('errors'));
  	$this->view->assign('values', $request->getParam('values'));
  	$organizations = $this->_helper->validatefunctions->getorgslist();
  	$this->view->assign('organizations', $organizations);
  }
  
  public function survformprocessAction(){
  	$request 		= $this->getRequest();
  	$first_name		= $request->getParam('first_name');
    $last_name		= $request->getParam('last_name');
    $state 			= $request->getParam('state');
  	$country 		= $request->getParam('country');
  	$address 		= $request->getParam('address');
  	$city 			= $request->getParam('city');
  	$zip 			= $request->getParam('zip');
  	$phone 			= $request->getParam('phone');
    $email			= $request->getParam('email');
    $orgid			= $request->getParam('orgid');
  	$username 		= $request->getParam('username');
  	$password 		= $request->getParam('password');
  	$conf_password	= $request->getParam('conf_password');
  	
  	$errors = $this->_helper->validatefunctions->validatesurv($first_name,$last_name,$state,$country,
  						$address,$city,$zip,$phone,$email,$orgid,$username,$password,$conf_password);

	if(!$errors){
    	$this->_helper->dbfunctions->insertSurveyor($first_name,$last_name,$state,$country,
  						$address,$city,$zip,$phone,$email,$orgid,$username,$password,$conf_password);
    	$this->_redirect('/user/loginform');	
    }
    
     else{ //There are errors that need to be corrected in the registration form
		$values = array(
						"first_name"	=> $first_name,
						"last_name"		=> $last_name,
						"address" 		=> $address,
						"city" 			=> $city,
						"state" 		=> $state,
						"country" 		=> $country,
						"zip" 			=> $zip,
						"phone" 		=> $phone,
						"email"			=> $email,
						"orgid"			=> $orgid,
						"username" 		=> $username
						);
    	$this->getRequest()->setParam('errors', $errors);
    	$this->getRequest()->setParam('values', $values);
    	$this->_forward('survform');
    }
  }

 public function loginformAction()
  {
	$request = $this->getRequest();
	
	$ns = new Zend_Session_Namespace('UserCredentials');
	
	$this->view->assign('action', $request->getBaseURL()."/user/auth");  
   	$this->view->assign('title', 'Login Form');
   	$this->view->assign('username', 'User Name');	
   	$this->view->assign('password', 'Password');
   	$this->view->assign('newaccount_link', $request->getBaseURL()."/user/selectaccounttype");
   	$this->view->assign('newaccount_label', 'Create New Account'); 	
    
 }
 
 public function authAction()
 {
	$request 	= $this->getRequest();
   	$registry 	= Zend_Registry::getInstance();
	$auth		= Zend_Auth::getInstance(); 

	$DB = $registry['DB'];
	
   	$authAdapter = new Zend_Auth_Adapter_DbTable($DB);
   	$authAdapter->setTableName('USERS')
               ->setIdentityColumn('username')
               ->setCredentialColumn('password');    

	// Set the input credential values
	
	$uname = $request->getParam('username');
	$paswd = $request->getParam('password');
   	$authAdapter->setIdentity($uname);
   	$authAdapter->setCredential($paswd);

   	// Perform the authentication query, saving the result
   	$result = $auth->authenticate($authAdapter);

   if($result->isValid()){
   		$session = new Zend_Session_Namespace('user_info');
	   	//If valid user, we will save user data at session using
  		$data = $authAdapter->getResultRowObject(null,'password'); //password have been omitted
  		$auth->getStorage()->write($data);
  		
  		//Look the userid
  		$select = $DB->select();
		$select->from('USERS', array('userid'));
		$select->where('username = ?', $uname); 
		$userid = $DB->fetchOne($select);
		$session->userid = $userid;
		$session->username = $uname;
			
		//Set current folder attributes
		$session->current_folder = NULL;
		
		//Verify account type (org or surv)
		$select = $DB->select();
		$select->from('SURVEYOR', array('userid'));
		$select->where('userid = ?', $userid); 
		$is_surv = $DB->fetchOne($select);
	
	
		if($is_surv){
			$session->type = 'surv';
			$this->_redirect('/folder');
		}
		else{
			$session->type = 'org';
			$this->_redirect('/organization');
		}  		
		
   }
   
   //The user is not found in the DB
   else{
  	$this->_redirect('/user/loginform');
	}

	
  }

	public function logoutAction()
	{
   		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		$session = new Zend_Session_Namespace('user_info');
		$session->userid = NULL;
		$session->username = NULL;
		$this->_redirect('/user/loginform');
	}
	
	public function survprofileAction(){
		$auth = Zend_Auth::getInstance(); 
		if(!$auth->hasIdentity()){
		  $this->_redirect('/user/loginform');
		}
		$session 		= new Zend_Session_Namespace('user_info');
		$request 		= $this->getRequest(); 
		$user			= $auth->getIdentity();
		$username		= $user->username;
		$result = $this->_helper->dbfunctions->getsurvinfo($username);
		
		$this->view->assign('action', $request->getBaseURL()."/user/processupdatesurv");  
		$this->view->assign('title', 'Surveyor Profile');
		$this->view->assign('survinfo', $result);	
		$this->view->assign('errors', $request->getParam('errors'));
  		$this->view->assign('reviewvalues', $request->getParam('reviewvalues'));
			
	}
	
	public function processupdatesurvAction(){
  	$request 		= $this->getRequest();
  	$first_name		= $request->getParam('first_name');
    $last_name		= $request->getParam('last_name');
    $state 			= $request->getParam('state');
  	$country 		= $request->getParam('country');
  	$address 		= $request->getParam('address');
  	$city 			= $request->getParam('city');
  	$zip 			= $request->getParam('zip');
  	$phone 			= $request->getParam('phone');
    $email			= $request->getParam('email');
  	$username 		= $request->getParam('username');
  	$oldpassword 	= $request->getParam('oldpassword');
  	$password 		= $request->getParam('password');
  	$conf_password	= $request->getParam('conf_password');
  	
  	$errors = $this->_helper->validatefunctions->validatesurvupdates($first_name,$last_name,$state,$country,
  						$address,$city,$zip,$phone,$email,$username,$oldpassword,$password,$conf_password);

	if(!$errors){
		
		if( $oldpassword == NULL || $password == NULL || $conf_password == NULL)
			$password = NULL;
			
    	$this->_helper->dbfunctions->updateSurveyor($first_name,$last_name,$state,$country,
  													$address,$city,$zip,$phone,$email,$username,$password);

    	$this->_redirect('/folder/index');	
    }
    
     else{ //There are errors that need to be corrected in the registration form
		$values = array(
						"firstname"	=> $first_name,
						"lastname"		=> $last_name,
						"address" 		=> $address,
						"city" 			=> $city,
						"state" 		=> $state,
						"country" 		=> $country,
						"zip" 			=> $zip,
						"phone" 		=> $phone,
						"email"			=> $email,
						"username" 		=> $username
						);
    	$this->getRequest()->setParam('errors', $errors);
    	$this->getRequest()->setParam('reviewvalues', $values);
    	$this->_forward('survprofile');
    }
  }
	
 
}
?>