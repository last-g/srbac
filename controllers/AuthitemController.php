<?php

/**
 * AuthitemController class file.
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * AuthitemController is the main controller for all of the srbac actions
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.controllers
 * @since 1.0.0
 */
class AuthitemController extends SBaseController {

  /**
   * @var string specifies the default action to be 'list'.
   */
  public $defaultAction = 'frontpage';
  /**
   *  @var $breadcrumbs
   */
  public $breadcrumbs;
  /**
   * @var CActiveRecord the currently loaded data model instance.
   */
  private $_model;

  /** 
  * ДОСТУП
  * @return array action filters
  */
  public function filters()
  {
      return array(
          'accessControl', // perform access control for CRUD operations
          'ajaxOnly + ajaxAssign',
      );
  }
    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
            return array(
                    array('allow', // allow admin user to perform 'admin' and 'delete' actions
                            'users'=>array('last-g', 'durnev', 'lda'),
                    ),
                    array('deny',  // deny all users
                            'users'=>array('*'),
                            'message'=>'К сожалению, вам Access Denied.',
                    ),
            );
    }
        
  public function init() {
    parent::init();
  }

  /**
   * Checks if the user has the authority role
   * @param String $action The current action
   * @return Boolean true if user has the authority role
   */
  protected function beforeAction($action) {
    
    if (!$this->module->isInstalled() && $action->id != "install") {
      $this->redirect(array("install"));
      return false;
    }

    if ($this->module->debug) {
      return true;
    }
    if (Yii::app()->user->checkAccess(Helper::findModule('srbac')->superUser) ) {
      return true;
    } else {
      parent::beforeAction($action);
    }
  }
  
  public function actionGetUsers($term){
    $list = Helper::getAllusers($term);
    echo CJSON::encode($list);
  }
  public function actionGetGroups($term){
    $list = Helper::getAllgroups($term);
    echo CJSON::encode($list);
  }

  /**
   * Assigns roles to a user
   *
   * @param int $userid The user's id
   * @param String $roles The roles to assign
   * @param String $bizRules Not used yet
   * @param String $data Not used yet
   */
  private function _assignUser($userid, $roles, $bizRules, $data) {
    if ($userid) {
      $auth = Yii::app()->authManager;
      /* @var $auth CDbAuthManager */
      foreach ($roles as $role) {
        $auth->assign($role, $userid, $bizRules, $data);
      }
    }
  }

  /**
   * Revokes roles from a user
   * @param int $userid The user's id
   * @param String $roles The roles to revoke
   */
  private function _revokeUser($userid, $roles) {
    if ($userid) {
      $auth = Yii::app()->authManager;
      /* @var $auth CDbAuthManager */
      foreach ($roles as $role) {
        if ($role == $this->module->superUser) {
          $count = Assignments::model()->count("itemname='" . $role . "'");
          if ($count == 1) {
            return false;
          }
        }
        $auth->revoke($role, $userid);
        return true;
      }
    }
  }

    /**
   * Assigns roles to a group
   *
   * @param int $groupid The group's id
   * @param String $roles The roles to assign
   * @param String $bizRules Not used yet
   * @param String $data Not used yet
   */
  private function _assignGroup($groupid, $roles, $bizRules, $data) {
    if ($groupid) {
      $auth = Yii::app()->authManager;
      /* @var $auth SDbAuthManager */
      foreach ($roles as $role) {
        $auth->assignGroup($role, $groupid, $bizRules, $data);
      }
    }
  }

  /**
   * Revokes roles from a group
   * @param int $groupid The group's id
   * @param String $roles The roles to revoke
   */
  private function _revokeGroup($groupid, $roles) {
    if ($groupid) {
      $auth = Yii::app()->authManager;
      /* @var $auth SDbAuthManager */
      foreach ($roles as $role) {
        /*if ($role == $this->module->superUser) {
          $count = AssignmentsGroup::model()->count("itemname='" . $role . "'");
          if ($count == 1) {
            return false;
          }
        }*/
        $auth->revokeGroup($role, $groupid);
        return true;
      }
    }
  }
  
  /**
   * Assigns child items to a parent item
   * @param String $parent The parent item
   * @param String $children The child items
   */
  private function _assignChild($parent, $children) {
    if ($parent) {
      $auth = Yii::app()->authManager;
      /* @var $auth CDbAuthManager */
      foreach ($children as $child) {
        $auth->addItemChild($parent, $child);
      }
    }
  }

  /**
   * Revokes child items from a parent item
   * @param String $parent The parent item
   * @param String $children The child items
   */
  private function _revokeChild($parent, $children) {
    if ($parent) {
      $auth = Yii::app()->authManager;
      /* @var $auth CDbAuthManager */
      foreach ($children as $child) {
        $auth->removeItemChild($parent, $child);
      }
    }
  }

  /**
   * The assignment action
   * First checks if the user is authorized to perform this action
   * Then initializes the needed variables for the assign view.
   * If there's a post back it performs the assign action
   */
  public function actionAssign() {
      $this->render('assign');
  }

  /**
   * Used by Ajax to get the roles of a user when he is selected in the Assign
   * roles to user,group,role tab
   */
  public function actionGetRoles() {
    $this->_setMessage("");
    $this->_getTheRoles();
  }

  /**
   * Gets the assigned and not assigned roles of the selected user,group,role
   */
  private function _getTheRoles() {
    $type=Yii::app()->getGlobalState("type");  
    $model = new AuthItem();
    $data['assignedRoles'] =array();
    $data['notAssignedRoles'] =array();
    if($type=="Users")
    {    
        $userid = $_POST[Helper::findModule('srbac')->userclass][$this->module->userid];
        $data['assignedRoles'] = Helper::getUserAssignedRoles($userid);
        $data['notAssignedRoles'] = Helper::getUserNotAssignedRoles($userid);
    }    
    if($type=="Groups")
    {    
        $groupid = $_POST[Helper::findModule('srbac')->groupclass][$this->module->groupid];
        $data['assignedRoles'] = Helper::getGroupAssignedRoles($groupid);
        $data['notAssignedRoles'] = Helper::getGroupNotAssignedRoles($groupid);
    }
    if($type==CAuthItem::TYPE_ROLE)
    {    
        $name = isset($_POST["name"])?$_POST["name"]:(isset($_POST["AuthItem"]["name"][0])?$_POST["AuthItem"]["name"][0]:Yii::app()->getGlobalState("cleverNameRole"));
        $nameInput=Yii::app()->getGlobalState("cleverAssigningInputRole");
        if (Yii::app()->getGlobalState("cleverAssigningRole") && $name) {
          $data['assignedRoles'] = Helper::getAssignedRoles($name, true,$nameInput,$type);
          $data['notAssignedRoles'] = Helper::getNotAssignedRoles($name, true,$nameInput,$type);
        } else if ($name) {
          $data['assignedRoles'] = Helper::getAssignedRoles($name, false,null,$type);
          $data['notAssignedRoles'] = Helper::getNotAssignedRoles($name, false,null,$type);
        }
    }
    if ($data['assignedRoles'] == array()) {
      $data['revoke'] = array("name" => "revokeRole", "disabled" => true);
    } else {
      $data['revoke'] = array("name" => "revokeRole");
    }
    if ($data['notAssignedRoles'] == array()) {
      $data['assign'] = array("name" => "assignRole", "disabled" => true);
    } else {
      $data['assign'] = array("name" => "assignRole");
    }
    $this->renderPartial('tabViews/roleAssignAjax',
      array('model' => $model, 'data' => $data, 'message' => $this->_getMessage()),
      false, true);
  }
  
  /**
   * Used by Ajax to get the tasks of a role when it is selected in the Assign
   * tasks to roles tab
   */
  public function actionGetTasks() {
    $this->_setMessage("");
    $this->_getTheTasks();
  }

  /**
   * Gets the assigned and not assigned tasks of the selected user
   */
  private function _getTheTasks() {
    $type=Yii::app()->getGlobalState("type");  
    $model = new AuthItem();
    $data['assignedTasks'] = array();
    $data['notAssignedTasks'] = array();
    if($type==CAuthItem::TYPE_TASK)
        $name = isset($_POST["name"])?$_POST["name"]:(isset($_POST["Assignments"]["itemname"])?$_POST["Assignments"]["itemname"]:Yii::app()->getGlobalState("cleverNameTask"));
    if($type==CAuthItem::TYPE_ROLE)
        $name = isset($_POST["AuthItem"]["name"][0])?$_POST["AuthItem"]["name"][0]:Yii::app()->getGlobalState("cleverNameTask");
    $nameInput=Yii::app()->getGlobalState("cleverAssigningInputTask");

    if (Yii::app()->getGlobalState("cleverAssigningTask") && $name) {
        $data['assignedTasks'] = Helper::getAssignedTasks($name,true,$nameInput,$type);
        $data['notAssignedTasks'] = Helper::getNotAssignedTasks($name,true,$nameInput,$type); 
    }
    else if ($name) {
        $data['assignedTasks'] = Helper::getAssignedTasks($name,false,null,$type);
        $data['notAssignedTasks'] = Helper::getNotAssignedTasks($name,false,null,$type);
    }   
    if ($data['assignedTasks'] == array()) {
      $data['revoke'] = array("name" => "revokeTask", "disabled" => true);
    } else {
      $data['revoke'] = array("name" => "revokeTask");
    }
    if ($data['notAssignedTasks'] == array()) {
      $data['assign'] = array("name" => "assignTasks", "disabled" => true);
    } else {
      $data['assign'] = array("name" => "assignTasks");
    }
    $this->renderPartial('tabViews/taskAssignAjax',
      array('model' => $model, 'name' => $name, 'data' => $data, 'message' => $this->_getMessage()), false, true);
  }

  /**
   * Used by Ajax to get the operations of a task when he is selected in the Assign
   * operations to tasks tab
   */
  public function actionGetOpers() {
    $this->_setMessage("");
    $this->_getTheOpers();
  }

  /**
   * Gets the assigned and not assigned operations of the selected user
   */
  private function _getTheOpers() {
    $type=Yii::app()->getGlobalState("type");
    $model = new AuthItem();    
    $data['assignedOpers'] = array();
    $data['notAssignedOpers'] = array();
    if($type==CAuthItem::TYPE_TASK)
        $name = isset($_POST["Assignments"]["itemname"])?$_POST["Assignments"]["itemname"]:Yii::app()->getGlobalState("cleverName");
    if($type==CAuthItem::TYPE_ROLE)
        $name = isset($_POST["name"])?$_POST["name"]:(isset($_POST["AuthItem"]["name"][0])?$_POST["AuthItem"]["name"][0]:Yii::app()->getGlobalState("cleverName"));
    $nameInput=Yii::app()->getGlobalState("cleverAssigningInput");

    if (Yii::app()->getGlobalState("cleverAssigning") && $name) {
      $data['assignedOpers'] = Helper::getAssignedOpers($name, true,$nameInput,$type);
      $data['notAssignedOpers'] = Helper::getNotAssignedOpers($name, true,$nameInput,$type);
    } else if ($name) {
      $data['assignedOpers'] = Helper::getAssignedOpers($name, false,null,$type);
      $data['notAssignedOpers'] = Helper::getNotAssignedOpers($name, false,null,$type);
    }
    if ($data['assignedOpers'] == array()) {
      $data['revoke'] = array("name" => "revokeOpers", "disabled" => true);
    } else {
      $data['revoke'] = array("name" => "revokeOpers");
    }
    if ($data['notAssignedOpers'] == array()) {
      $data['assign'] = array("name" => "assignOpers", "disabled" => true);
    } else {
      $data['assign'] = array("name" => "assignOpers");
    }
    $this->renderPartial('tabViews/operationAssignAjax',
      array('model' => $model, 'name' => $name, 'data' => $data, 'message' => $this->_getMessage()), false, true);
  }

  /**
   * Shows a particular model.
   */
  public function actionShow() {
    $deleted = Yii::app()->request->getParam('deleted', false);
    $delete = Yii::app()->request->getParam('delete', false);
    $model = $this->loadAuthItem();
    $this->renderPartial('manage/show', array('model' => $model,
        'deleted' => $deleted,
        'updateList' => false,
        'delete' => $delete));
  }

  /**
   * Creates a new model.
   * If creation is successful, the browser will be redirected to the 'show' page.
   */
  public function actionCreate() {
    $model = new AuthItem;
    if (isset($_POST['AuthItem'])) {
      $model->attributes = $_POST['AuthItem'];
      try {
        if ($model->save()) {

          Yii::app()->user->setFlash('updateSuccess',
            "'" . $model->name . "' " .
            Helper::translate('srbac', 'created successfully'));
          $model->data = unserialize($model->data);
          $this->renderPartial('manage/update', array('model' => $model));
        } else {
          $this->renderPartial('manage/create', array('model' => $model));
        }
      } catch (CDbException $exc) {
        Yii::app()->user->setFlash('updateError',
          Helper::translate('srbac', 'Error while creating')
          . ' ' . $model->name . "<br />" .
          Helper::translate('srbac', 'Possible there\'s already an item with the same name'));
        $this->renderPartial('manage/create', array('model' => $model));
      }
    } else {
      $this->renderPartial('manage/create', array('model' => $model));
    }
  }

  /**
   * Updates a particular model.
   * If update is successful, the browser will be redirected to the 'show' page.
   */
  public function actionUpdate() {
    $model = $this->loadAuthItem();
    $message = "";
    if (isset($_POST['AuthItem'])) {
      $model->oldName = isset($_POST["oldName"]) ? $_POST["oldName"] : $_POST["name"];
      $model->attributes = $_POST['AuthItem'];

      if ($model->save()) {
        Yii::app()->user->setFlash('updateSuccess',
          "'" . $model->name . "' " .
          Helper::translate('srbac', 'updated successfully'));
      } else {

      }
    }
    $this->renderPartial('manage/update', array('model' => $model));
  }

  /**
   * Deletes a particular model.
   * If deletion is successful, the browser will be redirected to the 'list' page.
   */
  public function actionDelete() {
    if (Yii::app()->request->isAjaxRequest) {

      $this->loadAuthItem()->delete();
      //$this->processAdminCommand();
      //$criteria = new CDbCriteria;
      //$pages = new CPagination(AuthItem::model()->count($criteria));
      //$pages->pageSize = $this->module->pageSize;
      //$pages->applyLimit($criteria);
      //$sort = new CSort('AuthItem');
      //$sort->applyOrder($criteria);
      //$models = AuthItem::model()->findAll($criteria);

      Yii::app()->user->setFlash('updateName',
        Helper::translate('srbac', 'Updating list'));
      $this->renderPartial('manage/show', array(
          //'models' => $models,
          //'pages' => $pages,
          //'sort' => $sort,
          'updateList' => true,
        ), false, false);
    } else {
      throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
    }
  }

  /**
   * Show the confirmation view for deleting auth items
   */
  public function actionConfirm() {
    $this->renderPartial('manage/show',
      array('model' => $this->loadAuthItem(), 'updateList' => false, 'delete' => true),
      false, true);
  }

  /**
   * Lists all models.
   */
  public function actionList() {
    // Get selected type
    $selectedType =
      Yii::app()->request->getParam('selectedType',
        Yii::app()->user->getState("selectedType"));
    Yii::app()->user->setState("selectedType", $selectedType);

    //Get selected name
    $selectedName =
      Yii::app()->request->getParam('name',
        Yii::app()->user->getState("selectedName"));
    Yii::app()->user->setState("selectedName", $selectedName);

    if (!Yii::app()->request->isAjaxRequest) {
      Yii::app()->user->setState("currentPage", Yii::app()->request->getParam('page', 0) - 1);
    }
    $criteria = new CDbCriteria;
    $criteria->condition = "1=1";    
    if ($selectedName != "") {
      $criteria->condition .= " AND name LIKE '%" . $selectedName . "%'";
    }
    if ($selectedType != "") {
      $criteria->condition .= " AND type = " . $selectedType;
    }
    $criteria->order="name";
    $pages = new CPagination(AuthItem::model()->count($criteria));
    $pages->pageSize = $this->module->pageSize;
    $pages->applyLimit($criteria);
    $pages->route = "manage";
    $pages->setCurrentPage(Yii::app()->user->getState("currentPage"));
    $models = AuthItem::model()->findAll($criteria);
    $this->renderPartial('manage/list', array(
        'models' => $models,
        'pages' => $pages,
      ), false, true);
  }

  /**
   * Installs srbac (only in debug mode)
   */
  public function actionInstall() {
    if ($this->module->debug) {
      $action = Yii::app()->getRequest()->getParam("action", "");
      $demo = Yii::app()->getRequest()->getParam("demo", 0);
      if ($action) {
        $error = Helper::install($action, $demo);
        if ($error == 1) {
          $this->render('install/overwrite', array("demo" => $demo));
        } else if ($error == 0) {
          $this->render('install/success', array("demo" => $demo));
        } else if ($error == 2) {
          $error = Helper::translate("srbac", "Error while installing srbac.<br />Please check your database and try again");
          $this->render('install/error', array("demo" => $demo, "error" => $error));
        }
      } else {
        $this->render('install/install');
      }
    } else {
      $error = Helper::translate("srbac", "srbac must be in debug mode");
      $this->render("install/error", array("error" => $error));
    }
  }

  /**
   * Displayes the authitem manage page
   */
  public function actionManage() {
    $this->processAdminCommand();
    $page = Yii::app()->getRequest()->getParam("page", "");
    if (Yii::app()->request->isAjaxRequest || $page != "") {
      $selectedType = Yii::app()->request->getParam('selectedType', Yii::app()->user->getState("selectedType"));
    } else {
      $selectedType = "";
    }
    Yii::app()->user->setState("selectedType", $selectedType);
    $criteria = new CDbCriteria;
    if ($selectedType != "") {
      $criteria->condition = "type = " . $selectedType;
    }
    $criteria->order="name";
    
    if (!Yii::app()->request->isAjaxRequest) {
      Yii::app()->user->setState("currentPage", Yii::app()->request->getParam('page', 0) - 1);
    }    

    $pages = new CPagination(AuthItem::model()->count($criteria));
    $pages->route = "manage";
    $pages->pageSize = $this->module->pageSize;
    $pages->applyLimit($criteria);
    $pages->setCurrentPage(Yii::app()->user->getState('currentPage'));

    $sort = new CSort('AuthItem');
    $sort->applyOrder($criteria);

    $models = AuthItem::model()->findAll($criteria);
    $full = Yii::app()->request->getParam("full");
    if (Yii::app()->request->isAjaxRequest && !$full) {
      $this->renderPartial('manage/list', array(
          'models' => $models,
          'pages' => $pages,
          'sort' => $sort,
          'full' => $full,
        ), false, true);
    } else if (Yii::app()->request->isAjaxRequest && $full) {
      $this->renderPartial('manage/manage', array(
          'models' => $models,
          'pages' => $pages,
          'sort' => $sort,
          'full' => $full
        ), false, true);
    } else {
      $this->render('manage/manage', array(
          'models' => $models,
          'pages' => $pages,
          'sort' => $sort,
          'full' => $full,
      ));
    }
  }

  /**
   * Gets the authitems for the CAutocomplete textbox
   */
  public function actionAutocomplete() {
    $criteria = new CDbCriteria();
    $criteria->condition = "name LIKE :name";
    $criteria->params = array(":name" => "%" . Yii::app()->request->getParam('q') . "%");
    $items = AuthItem::model()->findAll($criteria);
    foreach ($items as $item) {
      $valuesArray[] = $item->name;
    }
    echo join("\n", $valuesArray);
  }

  /**
   * Returns the data model based on the primary key given in the GET variable.
   * If the data model is not found, an HTTP exception will be raised.
   * @param integer the primary key value. Defaults to null, meaning using the 'id' GET variable
   */
  public function loadAuthItem($id=null) {
    if ($this->_model === null) {
      $r_id = urldecode(Yii::app()->getRequest()->getParam("id", ""));
      if ($id !== null || $r_id != "")
        $this->_model = AuthItem::model()->findbyPk($id !== null ? $id : $r_id);
      if ($this->_model === null)
        throw new CHttpException(404, 'The requested page does not exist.');
    }
    return $this->_model;
  }

  /**
   * Executes any command triggered on the admin page.
   */
  protected function processAdminCommand() {
    if (isset($_POST['command'], $_POST['id']) && $_POST['command'] === 'delete') {
      // $this->loadAuthItem($_POST['id'])->delete();
      // reload the current page to avoid duplicated delete actions
      //$this->refresh();
    }
  }

  //TODO These messages should be replaced by flash messages

  /**
   * Sets the message that is displayed to the user
   * @param String $mess  The message to show
   */
  private function _setMessage($mess) {
    Yii::app()->user->setState("message", $mess);
  }

  /**
   *
   * @return String Gets the message that will be displayed to the user
   */
  private function _getMessage() {
    return Yii::app()->user->getState("message");
  }

  /**
   * Displayes the assignments page with no user selected
   */
  public function actionAssignments() {
    $this->render('assignments', array("id" => 0));
  }

  /**
   * Show a user's assignments.The user is passed by $_GET
   */
  public function actionShowAssignments() {
    $userid = Yii::app()->request->getParam('id');
    $user = $this->module->getUserModel()->findByPk($userid);
    $username = $user->fullName;//{$this->module->username}
    $r = array("" => array(0 => array(0 => array())));
    Yii::app()->setGlobalState("uniqOper", array());
    if ($userid > 0) {
      $auth = Yii::app()->authManager;
      /* @var $auth CDbAuthManager */
      $ass = $auth->getAuthItems(2, $userid);
      $r[""] = array();
      foreach ($ass as $i => $role) {
          $r=array_merge($r,$this->bildRole("Users",0,$role->name,$r,"",$i));
      }    
      
      // Add always allowed opers
      $r[""]["AlwaysAllowed"][""] = $this->module->getAlwaysAllowed();
      
      //Add groups for user
      $groups=Helper::getArrayGroupsUser($userid);
      foreach ($groups as $key=>$groupid) {
          $r=array_merge($r,$this->getGroupAssignments($groupid,$key));
      }
      $this->renderPartial('userAssignments', array('data' => $r, 'username' => $username, "uniqOper"=>implode(", ",Yii::app()->getGlobalState("uniqOper"))));
    }   
  }

  public function bildRole($type,$n,$name,$arr,$g,$r){
        $arr[$g][$r] = $name;
        if($type=="Users")
            $children = Yii::app()->authManager->getItemChildren($name);
        if($type=="Groups")
            $grandchildren = Yii::app()->authManager->getItemChildrenGroup($name);
        $arr[$g][$r] = array();
        foreach ($children as $j => $task) {
            if($task->type==CAuthItem::TYPE_ROLE)
            {
                $arr=array_merge($arr,$this->bildRole($type,$n+1,$task->name,$arr,$g,$this->separator($n+1).$task->name));
            }
            if($task->type==CAuthItem::TYPE_TASK)
            {
                $arr=array_merge($arr,$this->bildTask($type,0,$task->name,$arr,$g,$r,$j));
            }
            if($task->type==CAuthItem::TYPE_OPERATION)
            {
                $count=count(isset($arr[$g][$r][""])?$arr[$g][$r][""]:array())+1;
                $arr=array_merge($arr,$this->bildOper($task->name,$arr,$g,$r,"",$count));
            }
        }
        return $arr;
  }
  public function bildTask($type,$n,$name,$arr,$g,$r,$t){
        $arr[$g][$r][$t] = $name;
        if($type=="Users")
            $grandchildren = Yii::app()->authManager->getItemChildren($name);
        if($type=="Groups")
            $grandchildren = Yii::app()->authManager->getItemChildrenGroup($name);
        $arr[$g][$r][$t] = array();
        foreach ($grandchildren as $k => $oper) {
            if($oper->type==CAuthItem::TYPE_TASK)
            {
                $arr=array_merge($arr,$this->bildTask($type,$n+1,$oper->name,$arr,$g,$r,$this->separator($n+1).$oper->name));
            }
            if($oper->type==CAuthItem::TYPE_OPERATION)
            {
                $arr=array_merge($arr,$this->bildOper($oper->name,$arr,$g,$r,$t,$k));
            }                        
        }
        return $arr;
  }
  public function bildOper($name,$arr,$g,$r,$t,$o){
      $arr[$g][$r][$t][$o] = $name;
      $this->uniqOper($name); 
      return $arr;
  }
  public function uniqOper($name){
      $uniqOper=Yii::app()->getGlobalState("uniqOper");
      if(!in_array($name,$uniqOper)) 
          $uniqOper[]=$name;
      Yii::app()->setGlobalState("uniqOper",$uniqOper);
  }
  public function separator($n){
      $separator="";
      for ($index = 0; $index < $n; $index++) {
          $separator.="-> ";
      }
      return $separator;
  }

  /**
   * Show a group's assignments.The group is passed by $_GET
   */
  public function actionShowGroupAssignments() {
        $groupid =Yii::app()->request->getParam('id');//$_POST[Helper::findModule('srbac')->groupclass][$this->module->groupid]
        Yii::app()->setGlobalState("uniqOper", array());     
        $group = $this->module->getGroupModel()->findByPk($groupid);
        $groupname = $group->{$this->module->groupname};
        if ($groupid > 0) {
          $r=$this->getGroupAssignments($groupid,$groupname);  
          // Add always allowed opers
          $r[$groupname]["AlwaysAllowed"][""] = $this->module->getAlwaysAllowed();
          $this->renderPartial('groupAssignments', array('data' => $r, 'groupname' => $groupname, "uniqOper"=>implode(", ",Yii::app()->getGlobalState("uniqOper"))));
        }
  }
  
  public function getGroupAssignments($groupid,$groupname){
        $auth = Yii::app()->authManager;
        /* @var $auth CDbAuthManager */
        $ass = $auth->getAuthGroupItems(2, $groupid);
        $r[$groupname] = array();
        foreach ($ass as $i => $role) {
            $r=array_merge($r,$this->bildRole("Users",0,$role->name,$r,$groupname,$i));
        }  
        return $r;
  }

    /**
   * Scans applications controllers and find the actions for autocreating of
   * authItems
   */
  public function actionScan() {
    if (Yii::app()->request->getParam('module') != '') {
      $controller = Yii::app()->request->getParam('module') .
        Helper::findModule('srbac')->delimeter
        . Yii::app()->request->getParam('controller');
    } else {
      $controller = Yii::app()->request->getParam('controller');
    }
    $controllerInfo = $this->_getControllerInfo($controller);
    $this->renderPartial("manage/createItems",
      array("actions" => $controllerInfo[0],
          "controller" => $controller,
          "delete" => $controllerInfo[2],
          "task" => $controllerInfo[3],
          "taskViewingExists" => $controllerInfo[4],
          "taskAdministratingExists" => $controllerInfo[5],
          "allowed" => $controllerInfo[1]),
      false, true);
  }

  /**
   * Getting a controllers actions and also th actions that are always allowed
   * return array
   * */
  private function _getControllerInfo($controller, $getAll = false) {
    $del = Helper::findModule('srbac')->delimeter;
    $actions = array();
    $allowed = array();
    $auth = Yii::app()->authManager;

    //Check if it's a module controller
    if (substr_count($controller,$del )) {
      $c = explode($del, $controller);
      $controller = $c[1];
      $module = $c[0] .$del;
      $contPath = Yii::app()->getModule($c[0])->getControllerPath();
      $control = $contPath . DIRECTORY_SEPARATOR . str_replace(".", DIRECTORY_SEPARATOR, $controller) . ".php";
    } else {
      $module = "";
      $contPath = Yii::app()->getControllerPath();
      $control = $contPath . DIRECTORY_SEPARATOR . str_replace(".", DIRECTORY_SEPARATOR, $controller) . ".php";
    }

    $task = $module . str_replace("Controller", "", $controller);

    $taskViewingExists = $auth->getAuthItem($task . "Viewing") !== null ? true : false;
    $taskAdministratingExists = $auth->getAuthItem($task . "Administrating") !== null ? true : false;
    $delete = Yii::app()->request->getParam('delete');

    $h = file($control);
    for ($i = 0; $i < count($h); $i++) {
      $line = trim($h[$i]);
      if (preg_match("/^(.+)function( +)action*/", $line)) {
        $posAct = strpos(trim($line), "action");
        $posPar = strpos(trim($line), "(");
        $action = trim(substr(trim($line),$posAct, $posPar-$posAct));
        $patterns[0] = '/\s*/m';
        $patterns[1] = '#\((.*)\)#';
        $patterns[2] = '/\{/m';
        $replacements[2] = '';
        $replacements[1] = '';
        $replacements[0] = '';
        $action = preg_replace($patterns, $replacements, trim($action));
        $itemId = $module . str_replace("Controller", "", $controller) .
        preg_replace("/action/", "", $action,1);
        if ($action != "actions") {
          if ($getAll) {
            $actions[$module . $action] = $itemId;
            if (in_array($itemId, $this->allowedAccess())) {
              $allowed[] = $itemId;
            }
          } else {
            if (in_array($itemId, $this->allowedAccess())) {
              $allowed[] = $itemId;
            } else {
              if ($auth->getAuthItem($itemId) === null && !$delete) {
                if (!in_array($itemId, $this->allowedAccess())) {
                  $actions[$module . $action] = $itemId;
                }
              } else if ($auth->getAuthItem($itemId) !== null && $delete) {
                if (!in_array($itemId, $this->allowedAccess())) {
                  $actions[$module . $action] = $itemId;
                }
              }
            }
          }
        } else {
          //load controller
          if (!class_exists($controller, false)) {
            require($control);
          }
          $tmp = array();
          $controller_obj = new $controller($controller, $module);
          //Get actions
          $controller_actions = $controller_obj->actions();
          foreach ($controller_actions as $cAction => $value) {
            $itemId = $module . str_replace("Controller", "", $controller) . ucfirst($cAction);
            if ($getAll) {
              $actions[$cAction] = $itemId;
              if (in_array($itemId, $this->allowedAccess())) {

                $allowed[] = $itemId;
              }
            } else {
              if (in_array($itemId, $this->allowedAccess())) {
                $allowed[] = $itemId;
              } else {
                if ($auth->getAuthItem($itemId) === null && !$delete) {
                  if (!in_array($itemId, $this->allowedAccess())) {
                    $actions[$cAction] = $itemId;
                  }
                } else if ($auth->getAuthItem($itemId) !== null && $delete) {
                  if (!in_array($itemId, $this->allowedAccess())) {
                    $actions[$cAction] = $itemId;
                  }
                }
              }
            }
          }
        }
      }
    }
    return array($actions, $allowed, $delete, $task, $taskViewingExists, $taskAdministratingExists);
  }

  /**
   * Deletes autocreated authItems
   */
  public function actionAutoDeleteItems() {
    $del = Helper::findModule('srbac')->delimeter;
    $cont = str_replace("Controller", "", $_POST["controller"]);

    //Check for module controller
    $controllerArr = explode($del, $cont);
    $controller = $controllerArr[sizeof($controllerArr) - 1];


    $actions = isset($_POST["actions"]) ? $_POST["actions"] : array();
    $deleteTasks = isset($_POST["createTasks"]) ? $_POST["createTasks"] : 0;
    $tasks = isset($_POST["tasks"]) ? $_POST["tasks"] : array();
    $message = "<div style='font-weight:bold'>" . Helper::translate('srbac', 'Delete operations') . "</div>";
    foreach ($actions as $key => $action) {
      if (substr_count($action, "action") > 0) {
        //controller action
        $action = trim(preg_replace("/action/", $controller, $action,1));
      } else {
        // actions actionstr_replace
        $action = $controller . ucfirst($action);
      }
      $auth = AuthItem::model()->findByPk($action);
      if ($auth !== null) {
        $auth->delete();
        $message .= "<div>" . $action . " " . Helper::translate('srbac', 'deleted') . "</div>";
      } else {
        $message .= "<div style='color:red;font-weight:bold'>" . Helper::translate('srbac',
            'Error while deleting')
          . ' ' . $action . "</div>";
      }
    }

    if ($deleteTasks) {
      $message .= "<div style='font-weight:bold'>" . Helper::translate('srbac', 'Delete tasks') . "</div>";
      foreach ($tasks as $key => $taskname) {
        $auth = AuthItem::model()->findByPk($taskname);
        if ($auth !== null) {
          $auth->delete();
          $message .= "<div>" . $taskname . " " . Helper::translate('srbac', 'deleted') . "</div>";
        } else {
          $message .= "<div style='color:red;font-weight:bold'>" . Helper::translate('srbac',
              'Error while deleting')
            . ' ' . $taskname . "</div>";
        }
      }
    }
    echo $message;
  }

  /**
   * Autocreating of authItems
   */
  public function actionAutoCreateItems() {
    $controller = str_replace("Controller", "", $_POST["controller"]);
    $actions = isset($_POST["actions"]) ? $_POST["actions"] : array();
    $message = "";
    $createTasks = isset($_POST["createTasks"]) ? $_POST["createTasks"] : 0;
    $tasks = isset($_POST["tasks"]) ? $_POST["tasks"] : array("");

    if ($createTasks == "1") {
      $message = "<div style='font-weight:bold'>" . Helper::translate('srbac', 'Creating tasks') . "</div>";
      foreach ($tasks as $key => $taskname) {
        $auth = new AuthItem();
        $auth->name = $taskname;
        $auth->type = 1;
        try {
          if ($auth->save()) {
            $message .= "'" . $auth->name . "' " .
              Helper::translate('srbac', 'created successfully') . "<br />";
          } else {
            $message .= "<div style='color:red;font-weight:bold'>" . Helper::translate('srbac',
                'Error while creating')
              . ' ' . $auth->name . '<br />' .
              Helper::translate('srbac', 'Possible there\'s already an item with the same name') . "</div><br />";
          }
        } catch (Exception $e) {
          $message .= "<div style='color:red;font-weight:bold'>" . Helper::translate('srbac',
              'Error while creating')
            . ' ' . $auth->name . '<br />' .
            Helper::translate('srbac', 'Possible there\'s already an item with the same name') . "</div><br />";
        }
      }
    }
    $message .= "<div style='font-weight:bold'>" . Helper::translate('srbac', 'Creating operations') . "</div>";
    foreach ($actions as $action) {
      $act = explode("action", $action,2);
      $a = trim($controller . (count($act) > 1 ? $act[1] : ucfirst($act[0])));
      $auth = new AuthItem();
      $auth->name = $a;
      $auth->type = 0;
      try {
        if ($auth->save()) {
          $message .= "'" . $auth->name . "' " .
            Helper::translate('srbac', 'created successfully') . "<br />";
          if ($createTasks == "1") {
            if ($this->_isUserOperation($auth->name)) {
              $this->_assignChild($tasks["user"], array($auth->name));
            }
            $this->_assignChild($tasks["admin"], array($auth->name));
          }
        } else {
          $message .= "<div style='color:red;font-weight:bold'>" . Helper::translate('srbac',
              'Error while creating')
            . ' ' . $auth->name . '<br />' .
            Helper::translate('srbac', 'Possible there\'s already an item with the same name') . "</div><br />";
        }
      } catch (Exception $e) {
        $message .= "<div style='color:red;font-weight:bold'>" . Helper::translate('srbac',
            'Error while creating')
          . ' ' . $auth->name . '<br />' .
          Helper::translate('srbac', 'Possible there\'s already an item with the same name') . "</div><br />";
      }
    }
    echo $message;
  }

  /**
   * Gets the controllers and the modules' controllers for the autocreating of
   * authItems
   */
  public function actionAuto() {
    $controllers = $this->_getControllers();
    $this->renderPartial("manage/wizard", array(
        'controllers' => $controllers), false, true);
  }
  public function actionAjaxAssign(){
    $type=Yii::app()->request->getParam('type',null); 
    if(!is_null($type)) Yii::app()->setGlobalState("type", $type);
    $type=Yii::app()->getGlobalState("type");
    $view=Yii::app()->request->getParam('view',null);  
      
    //CVarDumper::dump($_POST, 5, true);
    $userid = isset($_POST[Helper::findModule('srbac')->userclass][$this->module->userid])?$_POST[Helper::findModule('srbac')->userclass][$this->module->userid]:"";
    $groupid = isset($_POST[Helper::findModule('srbac')->groupclass][$this->module->groupid])?$_POST[Helper::findModule('srbac')->groupclass][$this->module->groupid]:"";

    //Init values
    $model = AuthItem::model();
    $data['assignedRoles'] = array();
    $data['notAssignedRoles'] = array();
    $data['assignedTasks'] = array();
    $data['notAssignedTasks'] = array();
    $data['assignedOpers'] = array();
    $data['notAssignedOpers'] = array();
    $data["assign"] = array("disabled" => true);
    $data["revoke"] = array("disabled" => true);
    $this->_setMessage("");

    $auth = Yii::app()->authManager;
    /* @var $auth CDbAuthManager */
    $authItemAssignName = isset($_POST['AuthItem']['name']['assign'])?$_POST['AuthItem']['name']['assign']:"";

    $assBizRule = isset($_POST['Assignments']['bizrule'])?$_POST['Assignments']['bizrule']:"";
    $assData = isset($_POST['Assignments']['data'])?$_POST['Assignments']['data']:"";

    $authItemRevokeName = isset($_POST['AuthItem']['name']['revoke'])?$_POST['AuthItem']['name']['revoke']:"";

    if (isset($_POST['AuthItem']['name'])) {
      if (isset($_POST['AuthItem']['name'][0])) {
        $authItemName = $_POST['AuthItem']['name'][0];
      } else {
        $authItemName = $_POST['AuthItem']['name'];
      }
    }
    $assItemName = isset($_POST['Assignments']['itemname'])?$_POST['Assignments']['itemname']:"";

    $assignRoles = Yii::app()->request->getParam('assignRoles', 0);
    $revokeRoles = Yii::app()->request->getParam('revokeRoles', 0);   
    $assignTasks = Yii::app()->request->getParam('assignTasks', 0);
    $revokeTasks = Yii::app()->request->getParam('revokeTasks', 0);
    $assignOpers = Yii::app()->request->getParam('assignOpers', 0);
    $revokeOpers = Yii::app()->request->getParam('revokeOpers', 0);
    try 
    {
        if($assignRoles && is_array($authItemAssignName)){
            if($type=="Users")
                $this->_assignUser($userid, $authItemAssignName, $assBizRule, $assData);
            if($type=="Groups")
                $this->_assignGroup($groupid, $authItemAssignName, $assBizRule, $assData);
            if($type==CAuthItem::TYPE_ROLE)
                $this->_assignChild($authItemName, $authItemAssignName);
            $this->_setMessage(Helper::translate('srbac', 'Role(s) Assigned'));
        }elseif($revokeRoles && is_array($authItemRevokeName)){
            $revoke=true;
            if($type=="Users")
                $revoke = $this->_revokeUser($userid, $authItemRevokeName);
            if($type=="Groups")
                $revoke = $this->_revokeGroup($groupid, $authItemRevokeName);
            if($type==CAuthItem::TYPE_ROLE)
                $this->_revokeChild($authItemName, $authItemRevokeName);
            if($revoke){
              $this->_setMessage(Helper::translate('srbac', 'Role(s) Revoked'));
            }else{
              $this->_setMessage(Helper::translate('srbac', 'Can\'t revoke this role'));
            }
        }elseif($assignTasks && is_array($authItemAssignName)){
            if($type==CAuthItem::TYPE_TASK)
                $this->_assignChild($assItemName, $authItemAssignName);
            if($type==CAuthItem::TYPE_ROLE)
                $this->_assignChild($authItemName, $authItemAssignName);        
            $this->_setMessage(Helper::translate('srbac', 'Task(s) Assigned'));
        }elseif($revokeTasks && is_array($authItemRevokeName)){
            if($type==CAuthItem::TYPE_TASK)
                $this->_revokeChild($assItemName, $authItemRevokeName);
            if($type==CAuthItem::TYPE_ROLE)
                $this->_revokeChild($authItemName, $authItemRevokeName);
            $this->_setMessage(Helper::translate('srbac', 'Task(s) Revoked'));
        }elseif($assignOpers && is_array($authItemAssignName)){
            if($type==CAuthItem::TYPE_TASK)
                $this->_assignChild($assItemName, $authItemAssignName);
            if($type==CAuthItem::TYPE_ROLE)
                $this->_assignChild($authItemName, $authItemAssignName);
            $this->_setMessage(Helper::translate('srbac', 'Operation(s) Assigned'));
        }elseif($revokeOpers && is_array($authItemRevokeName)){
            if($type==CAuthItem::TYPE_TASK)
                $this->_revokeChild($assItemName, $authItemRevokeName);
            if($type==CAuthItem::TYPE_ROLE)
                $this->_revokeChild($authItemName, $authItemRevokeName);
            $this->_setMessage(Helper::translate('srbac', 'Operation(s) Revoked'));
        }    
    }    
    catch(Exception $e)
    {
        $this->_setMessage($e->getMessage());
    }
    // assign to user show the user tab
    if($userid != "" || $groupid != "" || $assignRoles != 0 || $revokeRoles != 0){
      $this->_getTheRoles();
    }elseif($assignTasks != 0 || $revokeTasks != 0){
      $this->_getTheTasks();
    }elseif($assignOpers != 0 || $revokeOpers != 0){
      $this->_getTheOpers();
    }  
    if(!is_null($view))
        $this->renderPartial("tabViews/".$view, array('model'=>$model,'message'=>"",'data'=>$data), false, true);
  }

  /**
   * Geting all the application's and  modules controllers
   * @return array The application's and modules controllers
   */
  private function _getControllers() {
    $contPath = Yii::app()->getControllerPath();

    $controllers = $this->_scanDir($contPath);
    
        
    //Scan modules
    $modules = Yii::app()->getModules();
    $modControllers = array();
    foreach ($modules as $mod_id => $mod) {
      $moduleControllersPath = Yii::app()->getModule($mod_id)->controllerPath;
      $modControllers = $this->_scanDir($moduleControllersPath, $mod_id, "", $modControllers);
    }
    return array_merge($controllers, $modControllers);
  }

  private function _scanDir($contPath, $module="", $subdir="", $controllers = array()) {
    $handle = opendir($contPath);
    $del = Helper::findModule('srbac')->delimeter;
    while (($file = readdir($handle)) !== false) { 
      $filePath = $contPath . DIRECTORY_SEPARATOR . $file; 
      if (is_file($filePath)) {
        if (preg_match("/^(.+)Controller.php$/", basename($file))) {
          //if ($this->_extendsSBaseController($filePath)) {
            $controllers[] = (($module) ? $module . $del : "") .
              (($subdir) ? $subdir . "." : "") .
              str_replace(".php", "", $file);
          //}
        }
      } else if (is_dir($filePath) && $file != "." && $file != "..") {
        $controllers = $this->_scanDir($filePath, $module, $file, $controllers);
      }
    }
    return $controllers;
  }

  private function _extendsSBaseController($controller) {
    $c = basename(str_replace(".php", "", $controller));
    if (!class_exists($c, false)) {
       include_once $controller;
    } else {

    }
    $cont = new $c($c);

    if ($cont instanceof SBaseController) {
      return true;
    }
    return false;
  }

  public function actionGetCleverOpers() {
    $cleverAssigning = Yii::app()->getRequest()->getParam("checked")=="checked" ? 1 : 0;
    $cleverName = Yii::app()->getRequest()->getParam("name");
    $nameInput = Yii::app()->getRequest()->getParam("nameInput");
    Yii::app()->setGlobalState("cleverAssigning", $cleverAssigning);
    Yii::app()->setGlobalState("cleverName", $cleverName);
    Yii::app()->setGlobalState("cleverAssigningInput", $nameInput);
    $this->_getTheOpers();
  }
  public function actionGetCleverTasks() {
    $cleverAssigning = Yii::app()->getRequest()->getParam("checked")=="checked" ? 1 : 0;
    $cleverName = Yii::app()->getRequest()->getParam("name");
    $nameInput = Yii::app()->getRequest()->getParam("nameInput");
    Yii::app()->setGlobalState("cleverAssigningTask", $cleverAssigning);
    Yii::app()->setGlobalState("cleverNameTask", $cleverName);
    Yii::app()->setGlobalState("cleverAssigningInputTask", $nameInput);echo 123;
    $this->_getTheTasks();
  }
  public function actionGetCleverRoles() {
    $cleverAssigning = Yii::app()->getRequest()->getParam("checked")=="checked" ? 1 : 0;
    $cleverName = Yii::app()->getRequest()->getParam("name");
    $nameInput = Yii::app()->getRequest()->getParam("nameInput");
    Yii::app()->setGlobalState("cleverAssigningRole", $cleverAssigning);
    Yii::app()->setGlobalState("cleverNameRole", $cleverName);
    Yii::app()->setGlobalState("cleverAssigningInputRole", $nameInput);
    $this->_getTheRoles();
  }

  /**
   *
   * @param <type> $operation
   * @return <type> Checks if an operations should be assigned to using task or not
   */
  function _isUserOperation($operation) {
    foreach ($this->module->userActions as $oper) {
      if (strpos(strtolower($operation), strtolower($oper)) > -1) {
        return true;
      }
    }
    return false;
  }

  /**
   * Displays srbac frontpage
   */
  public function actionFrontPage() {
    $this->render('frontpage', array());
  }

  /**
   * Displays the editor for the alwaysAllowed items
   */
  public function actionEditAllowed() {
    if (!Helper::isAlwaysAllowedFileWritable()) {
      echo Helper::translate("srbac", "The always allowed file is not writeable by the server") . "<br />";
      echo "File : " . $this->module->getAlwaysAllowedFile();
      return;
    }
    $controllers = $this->_getControllers();
    foreach ($controllers as $n => $controller) {
      $info = $this->_getControllerInfo($controller, true);
      $c[$n]["title"] = $controller;
      $c[$n]["actions"] = $info[0];
      $c[$n]["allowed"] = $info[1];
    }
    $this->renderPartial('allowed', array('controllers' => $c), false, true);
  }

  public function actionSaveAllowed() {
    if (!Helper::isAlwaysAllowedFileWritable()) {
      echo Helper::translate("srbac", "The always allowed file is not writable by the server") . "<br />";
      echo "File : " . $this->module->getAlwaysAllowedFile();
      return;
    }
    $allowed = array();
    foreach ($_POST as $controller) {
      foreach ($controller as $action) {
        //Delete items
        $auth = AuthItem::model()->findByPk($action);
        if ($auth !== null) {
          $auth->delete();
        }
        $allowed[] = $action;
      }
    }

    $handle = fopen($this->module->getAlwaysAllowedFile(), "wb");
    fwrite($handle, "<?php \n return array(\n\t'" . implode("',\n\t'", $allowed) . "'\n);\n?>");
    fclose($handle);
    $this->renderPartial("saveAllowed", array("allowed" => $allowed));
  }

  public function actionClearObsolete() {
    $obsolete = array();
    $controllers = $this->_getControllers();
    $controllers = array_map(array($this, "replace"), $controllers);
    /* @var $auth CDbAuthManager */
    $auth = Yii::app()->authManager;
    $items = array_merge($auth->tasks, $auth->operations);
    foreach ($controllers as $contId => $cont) {
      foreach ($items as $item => $val) {
        $length = strlen($cont);
        $contItem = substr($item, 0, $length);
        if ($cont == $contItem) {
          unset($items[$item]);
        }
      }
    }
    foreach ($items as $key => $value) {
      $obsolete[$key] = $key;
    }
    $this->renderPartial("manage/clearObsolete", array("items" => $obsolete), false, true);
  }

  private function replace($value) {
    return str_replace("Controller", "", $value);
  }

  public function actionDeleteObsolete() {
    $removed = array();
    $notRemoved = array();
    if (isset($_POST["items"])) {
      $auth = Yii::app()->authManager;
      foreach ($_POST["items"] as $item) {
        if ($auth->removeAuthItem($item)) {
          $removed[] = $item;
        } else {
          $notRemoved[] = $item;
        }
      }
    }
    $this->renderPartial("manage/obsoleteRemoved", array("removed" => $removed, "notRemoved" => $notRemoved));
  }

}

