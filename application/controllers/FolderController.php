<?php
require_once 'Zend/Auth.php';
require_once 'Zend/Auth/Adapter/DbTable.php';
require_once 'Zend/Db/Adapter/Pdo/Mysql.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Loader/Autoloader.php';

class FolderController extends Zend_Controller_Action
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
	$username		= $user->username;
	$logoutUrl  	= $request->getBaseURL().'/user/logout';
	$backUrl		= $request->getBaseURL().'/folder/back';
	$forwardUrl 	= $request->getBaseURL().'/folder/forward';
	$createfolderUrl= $request->getBaseURL().'/folder/createfolderform';
	$uploadUrl		= $request->getBaseURL().'/folder/upload';
	$addcolabUrl	= $request->getBaseURL().'/folder/addcollaborator';
	$rmvcolabUrl	= $request->getBaseURL().'/folder/removecollaborator';
	$editfolderUrl	= $request->getBaseURL().'/folder/edit';
	$deletefolderUrl= $request->getBaseURL().'/folder/deletefolder';
	$foldershomeUrl = $request->getBaseURL().'/folder/index';
	$editsurvUrl	= $request->getBaseURL().'/user/survprofile';
	$pathUrl		= $request->getBaseURL().'/folder/path';
	

	$this->view->assign('username', $username);
	$this->view->assign('urllogout',$logoutUrl);
	$this->view->assign('urlback',$backUrl); 
	$this->view->assign('urlforward',$forwardUrl);
	$this->view->assign('createfolderUrl',$createfolderUrl);
	$this->view->assign('uploadUrl',$uploadUrl);
	$this->view->assign('addcolabUrl',$addcolabUrl);
	$this->view->assign('rmvcolabUrl',$rmvcolabUrl);
	$this->view->assign('editfolderUrl',$editfolderUrl);
	$this->view->assign('deletefolderUrl',$deletefolderUrl);
	
	$this->view->assign('editsurvUrl',$editsurvUrl);
	$this->view->assign('foldershomeUrl',$foldershomeUrl);
	$this->view->assign('pathUrl',$pathUrl);

  	$this->view->assign('title', 'Current Folder: '. $request->getParam('currentfolder'));
	$this->view->assign('welcome',$session->current_folder);
	
	//Check if current view is to show points or folders, 1 = poinrs , 0 = folders
	$p_or_f = $this->_helper->dbfunctions->forp($session->current_folder);
	
	if($p_or_f ==1){//List of points
		$pointlist = $this->_helper->dbfunctions->listpoints($session->current_folder);
		$this->view->assign('pointlist',$pointlist);
	}
	else{//List of folders	
		$folderlist = $this->_helper->dbfunctions->listfolders($session->current_folder);
		$this->view->assign('folderlist',$folderlist);
	}
	
	$path = $this->_helper->dbfunctions->getpath($session->current_folder);
	$this->view->assign('path',$path);
	
	
	
 }
 
   public function createfolderformAction()
 { 
 	$request = $this->getRequest();
  	$this->view->assign('title', 'Create Folder');
	$this->view->assign('action', $request->getBaseURL()."/folder/createfolderprocess");
	$this->view->assign('errors', $request->getParam('errors'));
  	$this->view->assign('values', $request->getParam('values'));
  	$this->view->assign('urllogout',$request->getBaseURL().'/user/logout');	
 }
 
    public function createfolderprocessAction()
 { 
 	$session = new Zend_Session_Namespace('user_info');
 	$request 		= $this->getRequest();
  	$foldername 	= $request->getParam('foldername');
  	$comment 		= $request->getParam('comment');
  	
  	
	$errors = $this->_helper->validatefunctions->validatefolder($foldername,$comment);
	
	
	if(!$errors){
		$this->_helper->dbfunctions->insertfolder($foldername,$comment,0);
		$this->_redirect('folder/index');	
    }
    
	else{ //There are errors that need to be corrected in the form
		$values = array(
						"foldername" 	=> $foldername,
						"comment" 		=> $comment
						);
    	$this->getRequest()->setParam('errors', $errors);
    	$this->getRequest()->setParam('values', $values);
    	$this->_forward('createfolderform');
    }
    
    //$this->_redirect('/user/loginform');
 }
 
   public function editAction()
 { 
  	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
  	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$username		= $user->username;
	
	$folderid = $session->current_folder;
	if($folderid == NULL){
		$this->_redirect('folder/index');
	}
	else{
		$foldernamecomment = $this->_helper->dbfunctions->getfoldernamecomment($folderid);
		$this->view->assign('foldernamecomment', $foldernamecomment);
		$this->view->assign('updatefolderaction', $request->getBaseURL()."/folder/updatefolder");
		
		$folders_selected = $request->getParam('folder_group');

		$this->view->assign('errors', $request->getParam('errors'));
		$this->view->assign('values',$request->getParam('values'));
	}
 }

  public function updatefolderAction()
 { 
  	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
  	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$username		= $user->username;
	$folderid = $session->current_folder;
	$foldername = $request->getParam('foldername');
	$comment = $request->getParam('comment');
	
	$errors = $this->_helper->validatefunctions->validateupdatefolder($folderid,$foldername,$comment);

	if(!$errors){
		$this->_helper->dbfunctions->updatefolder($folderid,$foldername,$comment);
		$this->_redirect('folder/index');	
    }
    
	else{ //There are errors that need to be corrected in the form
		$values = array(
						"foldername" 	=> $foldername,
						"comment" 		=> $comment
						);
    	$this->getRequest()->setParam('errors', $errors);
    	$this->getRequest()->setParam('values', $values);
    	$this->_forward('edit');
    }
   
 }
 
   public function addcollaboratorAction()
 { 
 	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
  	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$username		= $user->username;
	
	$folderid = $session->current_folder;
 	$candidates = $this->_helper->dbfunctions->collaboratorcandidates($username,$folderid);
 	
 	if($candidates == -1){
 		$this->_redirect('folder/index');
 	}
 	$request = $this->getRequest();
	$this->view->assign('candidates', $candidates);
  	$this->view->assign('title', 'Add Collaborator');
	$this->view->assign('action', $request->getBaseURL()."/folder/addcollaboratorprocess");	
 }
 
 public function addcollaboratorprocessAction()
 { 
 	$session = new Zend_Session_Namespace('user_info');
 	$currentfolder = $session->current_folder;
  	$request = $this->getRequest();
  	$selected_group = $request->getParam('selected_group');
  	if (!isset($selected_group)) {
    	$this->_redirect('folder/addcollaborator');
    }
    else{
    	for($i=0;$i<count($selected_group);$i++){
    		//Add member to Collaborator
    		$this->_helper->dbfunctions->addcollaborator($selected_group[$i],$currentfolder);
    	}
    $this->_redirect('folder/index');
    }	
 }
 
    public function removecollaboratorAction()
 { 	
 	$auth = Zend_Auth::getInstance(); 
	if(!$auth->hasIdentity()){
	  $this->_redirect('/user/loginform');
	}
  	$session 		= new Zend_Session_Namespace('user_info');
    $request 		= $this->getRequest(); 
	$user			= $auth->getIdentity();
	$username		= $user->username;
	
	$folderid = $session->current_folder;
 	$candidates = $this->_helper->dbfunctions->removecandidates($username,$folderid);
 	
 	if($candidates == NULL){
 		$this->_redirect('folder/index');
 	}
 	$request = $this->getRequest();
	$this->view->assign('candidates', $candidates);
  	$this->view->assign('title', 'Remove Collaborator');
	$this->view->assign('action', $request->getBaseURL()."/folder/removecollaboratorprocess");	
 }
 
 	public function removecollaboratorprocessAction()
 { 
 	$session = new Zend_Session_Namespace('user_info');
 	$currentfolder = $session->current_folder;
  	$request = $this->getRequest();
  	$selected_group = $request->getParam('selected_group');
  	if (!isset($selected_group)) {
    	$this->_redirect('folder/removecollaborator');
    }
    else{
    	for($i=0;$i<count($selected_group);$i++){
    		//Remove Collaborator
    		$this->_helper->dbfunctions->removecollaborator($selected_group[$i],$currentfolder);
    	}
    $this->_redirect('folder/index');
    }	
 }
 
    public function uploadAction()
 { 
 	$request = $this->getRequest();
  	$this->view->assign('title', 'Upload Measurements');
	$this->view->assign('welcome','uploadAction');
	$this->view->assign('browseaction', $request->getBaseURL()."/folder/uploadbrowse");
	$this->view->assign('folders', $request->getParam('folders'));
	$this->view->assign('uploadaction', $request->getBaseURL()."/folder/uploadprocess");
 }
 
    public function uploadbrowseAction()
 { 
  	$request = $this->getRequest();
  	if($_FILES['filename']['error'] != 4){
		//This portion of code uploads a file into the server   	
		/*	
		$target_path = "/Users/macrobio/Sites/agrimeter/application/controllers/uploads/";
		$target_path = $target_path . basename($_FILES['filename']['name']); 

		if(move_uploaded_file($_FILES['filename']['tmp_name'], $target_path)) {
			echo "The file ".  basename( $_FILES['filename']['name']). 
			" has been uploaded";
		} else{
			echo "There was an error uploading the file, please try again!";
		}
		*/
		$file = $_FILES['filename']['tmp_name'];
	
		$dataread = $this->_helper->validatefunctions->validateuploadfile($file);
		$this->getRequest()->setParam('folders', $dataread);
  	}
  	$this->_forward('upload');
 }
 
     public function uploadprocessAction()
 { 
 	$session = new Zend_Session_Namespace('user_info');
 	$currentfolder = $session->current_folder;
  	$request = $this->getRequest();
  	$folders = unserialize(base64_decode($request->getParam('folders')));
  	$folders_selected = $request->getParam('folder_group');
  	if (!isset($folders_selected)) {
    	$this->_redirect('folder/upload');
    }
    
    
    else{
    	for($i=0;$i<count($folders_selected);$i++){
    		//Create Folder for measurements
    		$this->_helper->dbfunctions->insertfolder($folders[$folders_selected[$i]]['fname'],null,1);
    		$this->_helper->dbfunctions->insertpoints($folders[$folders_selected[$i]][1]);
    		$session->current_folder = $this->_helper->dbfunctions->backfolder($session->current_folder);
    	}
    	$this->view->assign('folders_selected', $folders_selected);
    	$this->view->assign('folders', $folders);
    	$this->view->assign('count', count($folders_selected));
    		
		$logoutUrl  	= $request->getBaseURL().'/user/logout';
		$this->view->assign('urllogout',$logoutUrl);
		$this->_redirect('folder/index');
    }
 }

    public function backAction()
 { 
 	$session = new Zend_Session_Namespace('user_info');
 	$currentfolder = $session->current_folder;
 	$newfolder = $this->_helper->dbfunctions->backfolder($currentfolder);
 	$session->current_folder = $newfolder;
	$this->_redirect('folder/index');
 }
 
 //navigate into a folder
     public function forwardAction()
 { 
 	$request = $this->getRequest();
 	$session = new Zend_Session_Namespace('user_info');
 	$session->current_folder = $request->getParam('folder_select');
	$this->_redirect('folder/index');
	
 }
 
  public function deletefolderAction()
 { 
 	$request = $this->getRequest();
  	$session = new Zend_Session_Namespace('user_info');
	$folderid = $session->current_folder;
	if($folderid != NULL){
		$newfolder = $this->_helper->dbfunctions->backfolder($folderid);
		$result = $this->_helper->dbfunctions->deletefolder($folderid);
		$session->current_folder = $newfolder;
	}
	$this->_redirect('folder/index');
 }
 	
 	public function pathAction()
 { 
 	$request = $this->getRequest();
 	$newfolderpath = $request->getParam('folderpath');
  	$session = new Zend_Session_Namespace('user_info');
	$session->current_folder = $newfolderpath;
	$this->_redirect('folder/index');
 }
 
 
}
?>
