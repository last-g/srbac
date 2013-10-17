<?php

/**
 * Helper class file.
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */
/**
 * Helper is a class providing static methods that are used across srbac.
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.components
 * @since 1.0.0
 */
class Helper {
  const SUCCESS = 0;
  const OVERWRITE = 1;
  const ERROR = 2;

  /**
   * Return the roles assigned to a user or all the roles if no userid is provided
   * @param string $userid The id of the user
   * @return array An array of roles(AuthItems) assigned to the user
   */
  public static function getUserAssignedRoles($userid) {
    $criteria = new CDbCriteria();
    $criteria->join = 'LEFT JOIN ' . Assignments::model()->tableName() . ' a ON name = a.itemname';
    $criteria->condition = "type = ". CAuthItem::TYPE_ROLE;
    if ($userid) {
      $criteria->condition.= " AND user_id= '" . $userid . "'";
    }
    $criteria->order = "name ASC";
    $assigned = AuthItem::model()->findAll($criteria);
    return ($assigned === null) ? array(): $assigned;
  }

  /**
   * Gets the roles that are not assigned to the user by getting all the roles and
   * removes those assigned to the user, or all the roles if no user id is provided
   * @param String $userid The user's id
   * @return array An array of roles(AuthItems) not assigned to the user
   */
  public static function getUserNotAssignedRoles($userid) {
    $criteria = new CDbCriteria();
    $criteria->condition = "type=". CAuthItem::TYPE_ROLE;
    $criteria->order = "name ASC";
    $final = array();
    if ($userid) {
      $na = AuthItem::model()->findAll($criteria);
    } else {
      return AuthItem::model()->findAll($criteria);
    }
    $as = self::getUserAssignedRoles($userid);
    foreach ($na as $n) {
      $exists = false;
      foreach ($as as $a) {
        if ($a['name'] == $n['name']) {
          $exists = true;
        }
      }
      if (!$exists) {
        $final[] = $n;
      }
    }
    return ($final === null) ? array(): $final;
  }
     
  /**
   * Return the roles assigned to a group or all the roles if no groupid is provided
   * @param array $groups The id of the groups
   * @return array An array of roles(AuthItems) assigned to the group
   */
  public static function getGroupAssignedRoles($groups) {
    if($groups==array()) return array();  
    $criteria = new CDbCriteria();
    $criteria->join = 'LEFT JOIN ' . AssignmentsGroup::model()->tableName() . ' a ON name = a.itemname';
    $criteria->condition = "type = ". CAuthItem::TYPE_ROLE;
    if (is_array($groups)) {
      $criteria->condition.= " AND group_id IN (".implode(', ',$groups).")";
    }else{
      $criteria->condition.= " AND group_id =".$groups."";
    }
    $criteria->order = "name ASC";
    $assigned = AuthItem::model()->findAll($criteria);
    return ($assigned === null) ? array(): $assigned;
  }

  /**
   * Gets the roles that are not assigned to the group by getting all the roles and
   * removes those assigned to the group, or all the roles if no group id is provided
   * @param String $groupid The group's id
   * @return array An array of roles(AuthItems) not assigned to the group
   */
  public static function getGroupNotAssignedRoles($groupid) {
    $criteria = new CDbCriteria();
    $criteria->condition = "type=". CAuthItem::TYPE_ROLE;
    $criteria->order = "name ASC";
    $final = array();
    if ($groupid) {
      $na = AuthItem::model()->findAll($criteria);
    } else {
      return AuthItem::model()->findAll($criteria);
    }
    $as = self::getGroupAssignedRoles($groupid);
    foreach ($na as $n) {
      $exists = false;
      foreach ($as as $a) {
        if ($a['name'] == $n['name']) {
          $exists = true;
        }
      }
      if (!$exists) {
        $final[] = $n;
      }
    }
    return ($final === null) ? array(): $final;
  }
  /**
   * Return array groups $userid
   * @param int $userid
   * @return array 
   */
  public static function getArrayGroupsUser($userid){
    return Group::getArrayGroupsUser($userid);
  }
  
    /**
   * Return the roles assigned to a role or all the roles if no role is provided
   * @param string $name The name of the role
   * @param boolean $clever Use clever Assigning
   * @return array An array of tasks(AuthItems) assigned to the role
   */
  public static function getAssignedRoles($name, $clever = false, $nameInput='',$type) {
    $criteria = new CDbCriteria();
    if ($name) {
      $criteria->condition = "type=". CAuthItem::TYPE_ROLE." AND parent ='" . $name . "'";
      $criteria->join = 'left join ' . Yii::app()->authManager->itemChildTable . ' on name = child';
      if ($clever) {
        $p=array("/User/","/Administrator/");
        $r=array("","");
        $cleverName = preg_replace($p, $r, $name);
        $len = strlen($cleverName);
        if(empty($nameInput)) $criteria->condition.=" AND SUBSTR(child,1," . $len . ") = '" . $cleverName . "'";
        else $criteria->condition.=" AND child LIKE('%".$nameInput."%')";
      }
    } else {
      $criteria->condition = "type=". CAuthItem::TYPE_ROLE;
    }
    $criteria->order = "name ASC";
    $assigned = AuthItem::model()->findAll($criteria);

    return ($assigned === null) ? array(): $assigned;
  }

  /**
   * Return the roles not assigned to a role by getting all the roles and
   * removing those assigned to the role, or all the tasks if no role is provided
   * @param string $name The name of the role
   * @param boolean $clever Use clever Assigning
   * @return array An array of tasks(AuthItems) not assigned to the role
   */
  public static function getNotAssignedRoles($name, $clever = false, $nameInput='',$type) {
    $criteria = new CDbCriteria();
    $criteria->condition = "type=". CAuthItem::TYPE_ROLE;
    if($type==CAuthItem::TYPE_ROLE) 
        $criteria->condition.=" AND name!='".$name."'";
    if ($clever) {
      $p=array("/User/","/Administrator/");
      $r=array("","");
      $cleverName = preg_replace($p, $r, $name);
      $len = strlen($cleverName);
      //$tasks->condition.=" AND SUBSTR(name,1," . $len . ") = '" . $cleverName . "'";
      if(empty($nameInput)) $criteria->condition.=" AND SUBSTR(name,1," . $len . ") = '" . $cleverName . "'";
      else $criteria->condition.=" AND name LIKE('%".$nameInput."%')";
    }
    $criteria->order = "name ASC";
    $final = array();
    if ($name) {
      $na = AuthItem::model()->findAll($criteria);
    } else {
      return AuthItem::model()->findAll($criteria);
    }
    $as = self::getAssignedRoles($name, $clever, $nameInput,$type);
    foreach ($na as $n) {
      $exists = false;
      foreach ($as as $a) {
        if ($a['name'] == $n['name']) {
          $exists = true;
        }
      }
      if (!$exists) {
        $final[] = $n;
      }
    }
    return ($final === null) ? array(): $final;
  }
  
  /**
   * Return the tasks assigned to a role or all the tasks if no role is provided
   * @param string $name The name of the role
   * @param boolean $clever Use clever Assigning
   * @return array An array of tasks(AuthItems) assigned to the role
   */
  public static function getAssignedTasks($name, $clever = false, $nameInput='',$type) {
    $criteria = new CDbCriteria();
    if ($name) {
      $criteria->condition = "type=". CAuthItem::TYPE_TASK." AND parent ='" . $name . "'";
      $criteria->join = 'left join ' . Yii::app()->authManager->itemChildTable . ' on name = child';
      if ($clever) {
        if($type==CAuthItem::TYPE_OPERATION)
        {     
            $p=array("/User/","/Administrator/");
            $r=array("","");
        }    
        if($type==CAuthItem::TYPE_TASK)
        {   
            $p=array("/Viewing/","/Administrating/");
            $r=array("","");
        }
        $cleverName = preg_replace($p, $r, $name);
        $len = strlen($cleverName);
        if(empty($nameInput)) $criteria->condition.=" AND SUBSTR(child,1," . $len . ") = '" . $cleverName . "'";
        else $criteria->condition.=" AND child LIKE('%".$nameInput."%')";
      }
    } else {
      $criteria->condition = "type=". CAuthItem::TYPE_TASK;
    }
    $criteria->order = "name ASC";
    $assigned = AuthItem::model()->findAll($criteria);

    return ($assigned === null) ? array(): $assigned;
  }

  /**
   * Return the tasks not assigned to a role by getting all the tasks and
   * removing those assigned to the role, or all the tasks if no role is provided
   * @param string $name The name of the role
   * @param boolean $clever Use clever Assigning
   * @return array An array of tasks(AuthItems) not assigned to the role
   */
  public static function getNotAssignedTasks($name, $clever = false, $nameInput='',$type) {
    $criteria = new CDbCriteria();
    $criteria->condition = "type=". CAuthItem::TYPE_TASK;
    if($type==CAuthItem::TYPE_TASK) 
        $criteria->condition.=" AND name!='".$name."'";
    if ($clever) {
        if($type==CAuthItem::TYPE_OPERATION)
        {     
            $p=array("/User/","/Administrator/");
            $r=array("","");
        } 
        if($type==CAuthItem::TYPE_TASK)
        {   
            $p=array("/Viewing/","/Administrating/");
            $r=array("","");
        }
        $cleverName = preg_replace($p, $r, $name);
        $len = strlen($cleverName);
        //$tasks->condition.=" AND SUBSTR(name,1," . $len . ") = '" . $cleverName . "'";
        if(empty($nameInput)) $criteria->condition.=" AND SUBSTR(name,1," . $len . ") = '" . $cleverName . "'";
        else $criteria->condition.=" AND name LIKE('%".$nameInput."%')";
    }
    $criteria->order = "name ASC";
    $final = array();
    if ($name) {
      $na = AuthItem::model()->findAll($criteria);
    } else {
      return AuthItem::model()->findAll($criteria);
    }
    $as = self::getAssignedTasks($name, $clever, $nameInput,$type);
    foreach ($na as $n) {
      $exists = false;
      foreach ($as as $a) {
        if ($a['name'] == $n['name']) {
          $exists = true;
        }
      }
      if (!$exists) {
        $final[] = $n;
      }
    }
    return ($final === null) ? array(): $final;
  }

  /**
   * Return the operations assigned to a task or all the operations if no task
   * is provided
   * @param string $name The name of the task
   * @param boolean $clever Use clever Assigning
   * @return array An array of operations(AuthItems) assigned to the task
   */
  public static function getAssignedOpers($name, $clever = false, $nameInput='',$type) {
    $criteria = new CDbCriteria();
    if ($name) {
      $criteria->condition = "type=". CAuthItem::TYPE_OPERATION." AND parent ='" . $name . "'";
      $criteria->join = 'left join ' . Yii::app()->authManager->itemChildTable . ' on name = child';
      if ($clever) {
        if($type==CAuthItem::TYPE_TASK)
        {    
            $p=array("/Viewing/","/Administrating/");
            $r=array("","");
        }
        if($type==CAuthItem::TYPE_ROLE)
        {
            $p=array("/User/","/Administrator/");
            $r=array("","");
        }
        $cleverName = preg_replace($p, $r, $name);//echo $cleverName;
        $len = strlen($cleverName);
        //$tasks->condition.=" AND SUBSTR(child,1," . $len . ") = '" . is_null($nameInput)?$cleverName:$nameInput . "'";
        //$tasks->addCondition("SUBSTR(child,0,3) = SUBSTR('" . $name . "',0,3)");
        if(empty($nameInput)) $criteria->condition.=" AND SUBSTR(child,1," . $len . ") = '" . $cleverName . "'";
        else $criteria->condition.=" AND child LIKE('%".$nameInput."%')";
      }
    } else {
      $criteria->condition = "type=". CAuthItem::TYPE_OPERATION;
    }
    $criteria->order = "name ASC";
    $assigned = AuthItem::model()->findAll($criteria);    
    return ($assigned === null) ? array(): $assigned;
  }

  /**
   * Return the operations not assigned to a task by getting all the operations
   * and removing those assigned to the task, or all the operations if no task
   * is provided
   * @param string $name The name of the task
   * @param boolean $clever Use clever Assigning
   * @return array An array of operations(AuthItems) not assigned to the task
   */
  public static function getNotAssignedOpers($name, $clever = false, $nameInput='',$type) {
    $criteria = new CDbCriteria();
    $criteria->condition = "type=". CAuthItem::TYPE_OPERATION;
    if ($clever) {
        if($type==CAuthItem::TYPE_TASK)
        {    
            $p=array("/Viewing/","/Administrating/");
            $r=array("","");
        }
        if($type==CAuthItem::TYPE_ROLE)
        {
            $p=array("/User/","/Administrator/");
            $r=array("","");
        }
      
      $cleverName = preg_replace($p, $r, $name);
      $len = strlen($cleverName);
      //$tasks->condition.=" AND SUBSTR(name,1," . $len . ") = '" . is_null($nameInput)?$cleverName:$nameInput . "'";
      //$tasks->addCondition("SUBSTR(name,0,3) = SUBSTR('" . $name . "',0,3)");
      if(empty($nameInput)) $criteria->condition.=" AND SUBSTR(name,1," . $len . ") = '" . $cleverName . "'";
      else $criteria->condition.=" AND name LIKE('%".$nameInput."%')";
    }
    $criteria->order = "name ASC";
    $final = array();
    if ($name) {
      $na = AuthItem::model()->findAll($criteria);
    } else {
      return AuthItem::model()->findAll($criteria);
    }
    $as = self::getAssignedOpers($name, $clever, $nameInput,$type);
    foreach ($na as $n) {
      $exists = false;
      foreach ($as as $a) {
        if ($a['name'] == $n['name']) {
          $exists = true;
        }
      }
      if (!$exists) {
        $final[] = $n;
      }
    }
    return ($final === null) ? array(): $final;
  }

  /**
   * Marking words / phrases that are missing translation by adding a red * after
   * the word / phrase
   * @param CMissingTranslationEvent $event
   */
  public static function markWords($event) {
    if (self::findModule('srbac')->debug) {
      $event->message .= "*";
    }
  }

  /**
   * Check if authorizer is assigned to a user.
   * Until Authorizer is assigned to a user all users have access to srbac
   * administration. Also all users have access to srbac admin if srbac debug
   * attribute is true
   * @return true if authorizer is assigned to a user
   */
  public static function isAuthorizer() {
    if (self::findModule('srbac')->debug) {
      return false;
    }
    $criteria = new CDbCriteria();
    $criteria->condition = "itemname = '" . self::findModule('srbac')->superUser . "'";
    $authorizer = Assignments::model()->find($criteria);
    if ($authorizer !== null) {
      return true;
    }
    return false;
  }

  /**
   * If action is "install" checks for previous installations and if there's
   * one asks for ovewrite. If action is "ovewrite" or there's not a previous
   * installation performs the installation and returns the status of the
   * installation
   * @param String action
   * @param int demo
   * @return int status (0:Success, 1:Ovewrite, 2: Error)
   */
  public static function install($action, $demo) {
    $db = Yii::app()->authManager->db;
    /* @var $db CDbConnection */
    $auth = Yii::app()->authManager;
    /* @var $auth CDbAuthManager */
    $itemTable = $auth->itemTable;
    if ($action == "Install") {
      if (self::findModule("srbac")->isInstalled()) {
        return self::OVERWRITE; // Already installed
      } else {
        return  self::_install($demo);
      }
    } else {
      return self::_install($demo);
    }
  }

  /**
   * Performs the installation and returns the status
   * @param int demo
   * @return int status (0:Success, 1:Ovewrite, 2: Error)
   */
  private static function _install($demo) {
    $db = Yii::app()->authManager->db;
    /* @var $db CDbConnection */
    $auth = Yii::app()->authManager;
    /* @var $auth CDbAuthManager */
    $transaction = $db->beginTransaction();
    $itemTable = $auth->itemTable;
    $assignmentTable = $auth->assignmentTable;
    $assignmentTableGroup = $auth->assignmentTableGroup;
    $itemChildTable = $auth->itemChildTable;
    try {
      // Drop tables
      $db->createCommand("drop table if exists " . $assignmentTable . ";")->execute();
      $db->createCommand("drop table if exists " . $assignmentTableGroup . ";")->execute();
      $db->createCommand("drop table if exists " . $itemChildTable . ";")->execute();
      $db->createCommand("drop table if exists " . $itemTable . ";")->execute();

      //create tables
      $sql = "create table " . $itemTable . " (name varchar(64) not null,
                                     type integer not null,
                                     description text,
                                     bizrule text,
                                     data text,
                                     primary key (name));";
      $db->createCommand($sql)->execute();
      $sql = "create table " . $itemChildTable . " (parent varchar(64) not null,
                                              child varchar(64) not null,
                                              primary key (parent,child),
                                              foreign key (parent) references " . $itemTable . " (name) on delete cascade on update cascade,
                                              foreign key (child) references " . $itemTable . " (name) on delete cascade on update cascade
                                              );";
      $db->createCommand($sql)->execute();
      $sql = "create table " . $assignmentTable . "(itemname varchar(64) not null,
                                                user_id varchar(64) not null,
                                                bizrule text,
                                                data text,
                                                primary key (itemname,user_id),
                                                foreign key (itemname) references " . $itemTable . " (name) on delete cascade on update cascade
                                              );";
      $db->createCommand($sql)->execute();
      $sql = "create table " . $assignmentTableGroup . "(itemname varchar(64) not null,
                                                group_id varchar(64) not null,
                                                bizrule text,
                                                data text,
                                                primary key (itemname,group_id),
                                                foreign key (itemname) references " . $itemTable . " (name) on delete cascade on update cascade
                                              );";
      $db->createCommand($sql)->execute();
      //Insert Authorizer
      $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('" . self::findModule('srbac')->superUser . "',2)";
      $db->createCommand($sql)->execute();
      if ($demo == 1) {
        //Insert Demo Data
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Administrator',". CAuthItem::TYPE_ROLE.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('User',". CAuthItem::TYPE_ROLE.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Post Manager',". CAuthItem::TYPE_TASK.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('User Manager',". CAuthItem::TYPE_TASK.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Delete Post',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Create Post',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Edit Post',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('View Post',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Delete User',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Create User',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('Edit User',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
        $sql = "INSERT INTO " . $itemTable . " (name, type) VALUES ('View User',". CAuthItem::TYPE_OPERATION.")";
        $db->createCommand($sql)->execute();
      }
      $transaction->commit();
    } catch (CDbException $ex) {
      $transaction->rollback();
      return self::ERROR; //Error
    }
    return self::SUCCESS;
    //Success
  }

  /**
   * Find a module searching in application modules and if it's not found there
   * looks in modules' modules
   * @param String $moduleID The model to find
   * @return The module, if it's found else null
   */
  public static function findModule($moduleID) {
    if (Yii::app()->getModule($moduleID)) {
      return Yii::app()->getModule($moduleID);
    }
    $modules = Yii::app()->getModules();
    foreach ($modules as $mod=>$conf) {
      if (Yii::app()->getModule($mod)) {
        return self::findInModule(Yii::app()->getModule($mod), $moduleID);
      }
    }
    return null;
  }

  /**
   * Search for a child module
   * @param String $parent The parent module
   * @param String $moduleID The module to find
   * @return The module, if it's not found returns null
   */
  private static function findInModule($parent, $moduleID) {
    if ($parent->getModule($moduleID)) {
      return $parent->getModule($moduleID);
    } else {
      $modules = $parent->getModules();
      foreach ($modules as $mod => $conf) {
        return Helper::findInModule($parent->getModule($mod), $moduleID);
      }
    }
    return null;
  }

  /**
   * Translates texts based on Yii version
   * @param String $source The messages source
   * @param String $text The text to transalte
   * @return String The translated text
   */
  public static function translate($source, $text, $lang = null) {
    return self::findModule("srbac")->tr->translate($source, $text, $lang);
  }

  /**
   * Checks if a given version is supported by the current running Yii version
   * @param String $checkVersion
   * @return boolean True if the given version is supportedby the running Yii
   * version
   */
  public static function checkYiiVersion($checkVersion) {
    //remove dev builds
    $version = preg_replace("/[a-z]/", "", Yii::getVersion());
    $yiiVersionNoBuilds = explode("-", $version);
    $checkVersion = explode(".", $checkVersion);
    $yiiVersion = explode(".", $yiiVersionNoBuilds[0]);
    $yiiVersion[2] = isset($yiiVersion[2]) ? $yiiVersion[2]  : "0";
    if ($yiiVersion[0] > $checkVersion[0]) {
      return true;
    } else if ($yiiVersion[0] < $checkVersion[0]) {
      return false;
    } else {
      if ($yiiVersion[1] > $checkVersion[1]) {
        return true;
      } else if ($yiiVersion[1] < $checkVersion[1]) {
        return false;
      } else {
        if ($yiiVersion[2] > $checkVersion[2]) {
          return true;
        } else if ($yiiVersion[2] == $checkVersion[2]) {
          return true;
        } else {
          return false;
        }
      }
    }
    return false;
  }
  
  public static function checkInstall($key, $value) {
    if (in_array($key, explode(",", SrbacModule::PRIVATE_ATTRIBUTES))) {
      return;
    }
    $class = "";
    $out = array("", "");
    switch ($key) {
      case ($key == "userid" || $key == "username"):
        $class = "installNoError";
        $u = self::findModule("srbac")->getUserModel();
        $user = new $u;
        if (!$user->hasAttribute($value)) {
          $class = "installError";
          $out[1] = self::ERROR;
        }
        break;
      case "css":
        $class = "installNoError";
        $cssPublished = self::findModule("srbac")->isCssPublished();
        if (!$cssPublished) {
          $class = "installError";
          $out[1] = self::ERROR;
        }
        break;
      case (($key == "layout" && $value != "main" ) || $key == "notAuthorizedView" || $key == "imagesPath"
        || $key == "header" || $key == "footer"):
        $class = "installNoError";
        $file = Yii::getPathOfAlias($value) . ".php";
        $path = Yii::getPathOfAlias($value);
        if (!file_exists($file) && !is_dir($path)) {
          $class = "installError";
          $out[1] = self::ERROR;
        }
        break;
      case ($key == "imagesPack"):
        $class = "installNoError";
        if (!in_array($value, explode(",", SrbacModule::ICON_PACKS))) {
          $class = "installError";
          $out[1] = self::ERROR;
        }
        break;
      case "debug":

        break;

    }
    $out[0] = "<tr><td valign='top'>" . (substr($key, 0, 1) == "_" ? substr($key, 1) : $key) . "</td>";
    $out[0] .= "<td><div class='$class'>";
    $out[0] .= (!is_array($value)) ? $value : implode(", ", $value);
    $out[0] .= "</div><div class='$class'></div></td>";
    return $out;
  }

  /**
   * Publishes srbac cssfile
   * @return boolean If css published or not
   */
  public static function publishCss($css, $forcePublish = false) {
    if (Yii::app()->request->isAjaxRequest && !$forcePublish) {
      return true;
    }
    //Search in default Yii css directory
    $cssFile = Yii::getPathOfAlias("webroot.css") . DIRECTORY_SEPARATOR . $css;
    if (is_file($cssFile) && !Yii::app()->clientScript->isCssFileRegistered(Yii::app()->request->baseUrl . "/css/" . $css)) {
      $cssUrl = Yii::app()->request->baseUrl . "/css/" . $css;
      Yii::app()->clientScript->registerCssFile($cssUrl);
      self::findModule("srbac")->setCssUrl($cssUrl);
      return true;
    } else {
      // Search in srbac css dir

      $cssFile = Yii::getPathOfAlias("srbac.css") . DIRECTORY_SEPARATOR . $css;
      $cssDir = Yii::getPathOfAlias("srbac.css");
      if (is_file($cssFile)) {
        $published = Yii::app()->assetManager->publish($cssDir);
        $cssFile = $published . "/" . $css;
        if (!Yii::app()->clientScript->isCssFileRegistered($cssFile)) {
          Yii::app()->clientScript->registerCssFile($cssFile);
        }
        return true;
      } else {
        return false;
      }
    }
  }

  /**
   * Publish srbac images
   * @param String $imagesPath The path to the images
   * @param String $imagesPack The icons pack to use
   */
  public static function publishImages($imagesPath, $imagesPack) {
    $path = Yii::getPathOfAlias($imagesPath) . DIRECTORY_SEPARATOR . $imagesPack;
    if (is_dir($path)) {
      return Yii::app()->assetManager->publish($path);
    } else {
      return "";
    }
  }

  /**
   * Checks if the always allowed file is writeable
   * @return boolean true if always allowed file is writeable or false otherwise
   */
  public static function isAlwaysAllowedFileWritable() {
    if (!($f = @fopen(self::findModule("srbac")->getAlwaysAllowedFile(), 'r+'))) {
      return false;
    }
    fclose($f);
    return true;
  }
  
  /**
   * Gets all users array for autocomplete textbox
   */
  public static function getAllusers($term){
    $mod = Helper::findModule("srbac");
    $cr = new CDbCriteria();
    $cr->compare($mod->username,$term,true);
    $users = Helper::findModule('srbac')->getUserModel()->findAll($cr);
    foreach ($users as $key => $user) {
      $list[] = array(
          "label"=>$user->fullName,//{$mod->username}
          "value"=>$user->fullName,
          "id"=>$user->{$mod->userid},
          );
    }
    return $list;
  }
  /**
   * Gets all groups array for autocomplete textbox
   */
  public static function getAllgroups($term){
    $mod = Helper::findModule("srbac");
    $cr = new CDbCriteria();
    $cr->compare($mod->groupname,$term,true);
    $groups = Helper::findModule('srbac')->getGroupModel()->findAll($cr);
    foreach ($groups as $key => $group) {
      $list[] = array(
          "label"=>$group->{$mod->groupname},
          "value"=>$group->{$mod->groupname},
          "id"=>$group->{$mod->groupid},
          );
    }
    return $list;
  }
}