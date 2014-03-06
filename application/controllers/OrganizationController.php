<?php
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Loader/Autoloader.php';

class OrganizationController extends Zend_Controller_Action
{
  public function indexAction()
 { 
	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
  	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$orgname		= $user->username;

	$rmvactiveUrl  	= $request->getBaseURL().'/organization/removeactive';	
	$requestallowUrl  = $request->getBaseURL().'/organization/requestallow';
	$requestdenyUrl  = $request->getBaseURL().'/organization/requestdeny';


	$active = $this->_helper->dbfunctions->activemembers($orgname);
	$requests = $this->_helper->dbfunctions->requestsmembers($orgname);
	
	$this->view->assign('activelist', $active);
	$this->view->assign('requestslist',$requests);
	$this->view->assign('rmvactiveUrl',$rmvactiveUrl);
	$this->view->assign('requestallowUrl',$requestallowUrl);
	$this->view->assign('requestdenyUrl',$requestdenyUrl);
 }
 
   public function removeactiveAction()
 { 
	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$orgname		= $user->username;
	
  	$request = $this->getRequest();
  	$removeMembers = $request->getParam('active_remove');
  	if (!isset($removeMembers)) {
    	$this->_redirect('organization/index');
    }
    else{
    	for($i=0;$i<count($removeMembers);$i++){
    		$this->_helper->dbfunctions->removeactive($removeMembers[$i]);
    	}
    	$this->_redirect('organization/index');
    }    
 }
 
    public function requestallowAction()
 { 
	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$orgname		= $user->username;
	
  	$request = $this->getRequest();
  	$requests = $request->getParam('requestsToManage');
  	if (!isset($requests)) {
    	$this->_redirect('organization/index');
    }
    else{
    	for($i=0;$i<count($requests);$i++){
    		$this->_helper->dbfunctions->allowrequest($requests[$i],$orgname);
    	}
    	$this->_redirect('organization/index');
    }    
 }
 
 
  public function requestdenyAction()
 { 
	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$orgname		= $user->username;
	
  	$request = $this->getRequest();
  	$requests = $request->getParam('requestsToManage');
  	if (!isset($requests)) {
    	$this->_redirect('organization/index');
    }
    else{
    	for($i=0;$i<count($requests);$i++){
    		$this->_helper->dbfunctions->denyrequest($requests[$i]);
    	}
    	$this->_redirect('organization/index');
    }    
 }
 
 public function orgprofileAction(){
		
		$auth = Zend_Auth::getInstance(); 
		if(!$auth->hasIdentity()){
		  $this->_redirect('/user/loginform');
		}
		
		$session 		= new Zend_Session_Namespace('user_info');
		$request 		= $this->getRequest(); 
		$user			= $auth->getIdentity();
		$username		= $user->username;
		$result = $this->_helper->dbfunctions->getorginfo($username);
		
		$this->view->assign('action', $request->getBaseURL()."/organization/processupdateorg");  
		$this->view->assign('title', 'Organization Profile');
		$this->view->assign('orginfo', $result);	
		$this->view->assign('errors', $request->getParam('errors'));
  		$this->view->assign('reviewvalues', $request->getParam('reviewvalues'));
  		
			
	}
	
	public function processupdateorgAction(){
  	$request 		= $this->getRequest();
  	$orgname		= $request->getParam('orgname');
    $website		= $request->getParam('website');
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
  	
  	
  	$errors = $this->_helper->validatefunctions->validateorgupdates($orgname,$website,$state,$country,
  						$address,$city,$zip,$phone,$email,$username,$oldpassword,$password,$conf_password);
	
	if(!$errors){
		
		if( $oldpassword == NULL || $password == NULL || $conf_password == NULL)
			$password = NULL;
			
    	$this->_helper->dbfunctions->updateOrganization($orgname,$website,$state,$country,
  													$address,$city,$zip,$phone,$email,$username,$password);
    	$this->_redirect('/organization/index');
    }
    
     else{ //There are errors that need to be corrected in the registration form
		$values = array(
						"orgname"		=> $orgname,
						"website"		=> $website,
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
    	$this->_forward('orgprofile');
    }
  }
 
 
 
}
?>
