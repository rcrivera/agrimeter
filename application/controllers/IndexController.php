 <?php
require_once 'Zend/Controller/Action.php';

class IndexController extends Zend_Controller_Action
{
  public function indexAction()
  {
    $this->view->assign('title', 'Agrimeter');
	$this->view->assign('welcome','Welcome to Agrimeter.');
  }
  
}
?>
