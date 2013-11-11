<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SDbAuthManager
 *
 * @author ssoldatos
 */
class SDbAuthManager extends CDbAuthManager {

    /**
     * @var string the name of the table storing authorization item assignments. Defaults to 'AuthAssignmentGroup'.
     */
    public $assignmentTableGroup='AuthAssignmentGroup';
    
  /**
   * Performs access check for the specified user.
   * Use Yii::app()->user->checkAccess('operation') or Yii::app()->getAuthManager()->checkAccess("operation",userId)
   * @param string the name of the operation that need access check
   * @param mixed the user ID. This should can be either an integer and a string representing
   * the unique identifier of a user. See {@link IWebUser::getId}.
   * @param array name-value pairs that would be passed to biz rules associated
   * with the tasks and roles assigned to the user.
   * @return boolean whether the operations can be performed by the user.
   */
  public function regularCheckAccess($itemName, $userId, $params=array()){
    if (!empty($this->defaultRoles) && in_array($itemName,$this->defaultRoles)) {
      return 1;
    }
    $sql = "SELECT name, type, description, t1.bizrule, t1.data, t2.bizrule AS bizrule2, t2.data AS data2 FROM {$this->itemTable} t1, {$this->assignmentTable} t2 WHERE name=itemname AND user_id=:userid";
    $command = $this->db->createCommand($sql);
    $command->bindValue(':userid', $userId);

    // check directly assigned items
    $names = array();
    foreach ($command->queryAll() as $row) {
       Yii::trace('Checking permission "' . $row['name'] . '"', 'system.web.auth.CDbAuthManager');
      if ($this->executeBizRule($row['bizrule2'], $params, unserialize($row['data2']))
        && $this->executeBizRule($row['bizrule'], $params, unserialize($row['data']))) {
        if (strtolower($row['name']) === strtolower($itemName)) {
          return 1;
        }
        $names[] = $row['name'];
      }
    }
    //checkSuperAdmin
    if($this->checkSuperAdmin($names)) return 1;
    
    //get user groups assigned roles
    $groups=Helper::getArrayGroupsUser($userId);   
    $groupNames=Helper::getGroupAssignedRoles($groups);
    foreach ($groupNames as $groupName) {
        if (strtolower($groupName['name']) === strtolower($itemName)) {
          return 1;
        }
        if(!in_array($groupName['name'],$names)) $names[]=$groupName['name'];
    }
    //checkSuperAdmin
    if($this->checkSuperAdmin($names)) return 1;
    
    // check all descendant items
    while ($names !== array()) {
      $items = $this->getItemChildren($names);
      $names = array();
      foreach ($items as $item) {
        Yii::trace('Checking permission "' . $item->getName() . '"', 'system.web.auth.CDbAuthManager');
        if ($this->executeBizRule($item->getBizRule(), $params, $item->getData())) {
          if (strtolower($item->getName()) === strtolower($itemName)) {
            return 1;
          }
          $names[] = $item->getName();
        }
      }
    }
    return 0;
  }
  
  public function checkAccess($itemName, $userId, $params = array()) {
        $checkAccessResult_id = "checkAccessResult_" . $itemName . '_' . $userId . '_' . (!empty($params) ? md5(serialize($params)) : 0);
        $checkAccessResult = Yii::app()->cache->get($checkAccessResult_id);
        if ($checkAccessResult === false) {
            $checkAccessResult = $this->regularCheckAccess($itemName, $userId, $params);
            Yii::app()->cache->set($checkAccessResult_id, $checkAccessResult, Yii::app()->params['checkAccessCacheDuration']);
        }
        return $checkAccessResult ? true : false;
    }


  /**
   * Return true if $name have superUser
   * @param array $names
   * @return boolean
   */
  public function checkSuperAdmin($names){
      if(in_array(Yii::app()->getModule('srbac')->superUser,$names)) return true;
      return false;
  }
  
  //
    public function assign($itemName,$userId,$bizRule=null,$data=null)
    {
            if($this->usingSqlite() && $this->getAuthItem($itemName)===null)
                    throw new CException(Yii::t('yii','The item "{name}" does not exist.',array('{name}'=>$itemName)));

            $this->db->createCommand()
                    ->insert($this->assignmentTable, array(
                            'itemname'=>$itemName,
                            'user_id'=>$userId,
                            'bizrule'=>$bizRule,
                            'data'=>serialize($data)
                    ));
            return new CAuthAssignment($this,$itemName,$userId,$bizRule,$data);
    }
    public function revoke($itemName,$userId)
    {
            return $this->db->createCommand()
                    ->delete($this->assignmentTable, 'itemname=:itemname AND user_id=:userid', array(
                            ':itemname'=>$itemName,
                            ':userid'=>$userId
                    )) > 0;
    }
  	public function isAssigned($itemName,$userId)
	{
		return $this->db->createCommand()
			->select('itemname')
			->from($this->assignmentTable)
			->where('itemname=:itemname AND user_id=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId))
			->queryScalar() !== false;
	}
	public function getAuthAssignment($itemName,$userId)
	{
		$row=$this->db->createCommand()
			->select()
			->from($this->assignmentTable)
			->where('itemname=:itemname AND user_id=:userid', array(
				':itemname'=>$itemName,
				':userid'=>$userId))
			->queryRow();
		if($row!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthAssignment($this,$row['itemname'],$row['user_id'],$row['bizrule'],$data);
		}
		else
			return null;
	}
	public function getAuthAssignments($userId)
	{
		$rows=$this->db->createCommand()
			->select()
			->from($this->assignmentTable)
			->where('user_id=:userid', array(':userid'=>$userId))
			->queryAll();
		$assignments=array();
		foreach($rows as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$assignments[$row['itemname']]=new CAuthAssignment($this,$row['itemname'],$row['user_id'],$row['bizrule'],$data);
		}
		return $assignments;
	}
	public function saveAuthAssignment($assignment)
	{
		$this->db->createCommand()
			->update($this->assignmentTable, array(
				'bizrule'=>$assignment->getBizRule(),
				'data'=>serialize($assignment->getData()),
			), 'itemname=:itemname AND user_id=:userid', array(
				'itemname'=>$assignment->getItemName(),
				'user_id'=>$assignment->getUserId()
			));
	}
	public function getAuthItems($type=null,$userId=null)
	{
		if($type===null && $userId===null)
		{
			$command=$this->db->createCommand()
				->select()
				->from($this->itemTable);
		}
		else if($userId===null)
		{
			$command=$this->db->createCommand()
				->select()
				->from($this->itemTable)
				->where('type=:type', array(':type'=>$type));
		}
		else if($type===null)
		{
			$command=$this->db->createCommand()
				->select('name,type,description,t1.bizrule,t1.data')
				->from(array(
					$this->itemTable.' t1',
					$this->assignmentTable.' t2'
				))
				->where('name=itemname AND user_id=:userid', array(':userid'=>$userId));
		}
		else
		{
			$command=$this->db->createCommand()
				->select('name,type,description,t1.bizrule,t1.data')
				->from(array(
					$this->itemTable.' t1',
					$this->assignmentTable.' t2'
				))
				->where('name=itemname AND type=:type AND user_id=:userid', array(
					':type'=>$type,
					':userid'=>$userId
				));
		}
		$items=array();
		foreach($command->queryAll() as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$items[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $items;
	}
  //
  
    /**
     * Assigns an authorization item to a group.
     * @param string $itemName the item name
     * @param mixed $groupId the group ID
     * @param string $bizRule the business rule to be executed when {@link checkAccess} is called
     * for this particular authorization item.
     * @param mixed $data additional data associated with this assignment
     * @return CAuthAssignment the authorization assignment information.
     * @throws CException if the item does not exist or if the item has already been assigned to the group
     */
    public function assignGroup($itemName,$groupId,$bizRule=null,$data=null)
    {   
            /*
            if($this->usingSqlite() && $this->getAuthItem($itemName)===null)
                    throw new CException(Yii::t('yii','The item "{name}" does not exist.',array('{name}'=>$itemName)));
            */
            $this->db->createCommand()
                    ->insert($this->assignmentTableGroup, array(
                            'itemname'=>$itemName,
                            'group_id'=>$groupId,
                            'bizrule'=>$bizRule,
                            'data'=>serialize($data)
                    ));
            //return new CAuthAssignment($this,$itemName,$userId,$bizRule,$data);
    }
    /**
     * Revokes an authorization assignment from a group.
     * @param string $itemName the item name
     * @param mixed $groupId the group ID 
     * @return boolean whether removal is successful
     */
    public function revokeGroup($itemName,$groupId)
    {
            return $this->db->createCommand()
                    ->delete($this->assignmentTableGroup, 'itemname=:itemname AND group_id=:groupid', array(
                            ':itemname'=>$itemName,
                            ':groupid'=>$groupId
                    )) > 0;
    }
    /**
     * NOT USE
     * Returns a value indicating whether the item has been assigned to the group.
     * @param string $itemName the item name
     * @param mixed $groupId the group ID 
     * @return boolean whether the item has been assigned to the group.
     */
    /*public function isGroupAssigned($itemName,$groupId)
    {
            return $this->db->createCommand()
                    ->select('itemname')
                    ->from($this->assignmentTableGroup)
                    ->where('itemname=:itemname AND groupid=:groupid', array(
                            ':itemname'=>$itemName,
                            ':groupid'=>$groupId))
                    ->queryScalar() !== false;
    }*/
    /**
     * NOT USE
     * Returns the item assignment information.
     * @param string $itemName the item name
     * @param mixed $groupId the group ID 
     * @return CAuthAssignment the item assignment information. Null is returned if
     * the item is not assigned to the group.
     */
    /*public function getAuthGroupAssignment($itemName,$groupId)
    {
            $row=$this->db->createCommand()
                    ->select()
                    ->from($this->assignmentTableGroup)
                    ->where('itemname=:itemname AND groupid=:groupid', array(
                            ':itemname'=>$itemName,
                            ':groupid'=>$groupId))
                    ->queryRow();
            if($row!==false)
            {
                    if(($data=@unserialize($row['data']))===false)
                            $data=null;
                    return new CAuthAssignment($this,$row['itemname'],$row['groupid'],$row['bizrule'],$data);
            }
            else
                    return null;
    }*/
    /**
     * NOT USE
     * Returns the item assignments for the specified user.
     * @param mixed $userId the user ID (see {@link IWebUser::getId})
     * @return array the item assignment information for the user. An empty array will be
     * returned if there is no item assigned to the user.
     */
    /*public function getAuthGroupAssignments($groupId)
    {
            $rows=$this->db->createCommand()
                    ->select()
                    ->from($this->assignmentTableGroup)
                    ->where('groupid=:groupid', array(':groupid'=>$groupId))
                    ->queryAll();
            $assignments=array();
            foreach($rows as $row)
            {
                    if(($data=@unserialize($row['data']))===false)
                            $data=null;
                    $assignments[$row['itemname']]=new CAuthAssignment($this,$row['itemname'],$row['groupid'],$row['bizrule'],$data);
            }
            return $assignments;
    }*/

    /**
     * NOT USE
     * Saves the changes to an authorization assignment.
     * @param CAuthAssignment $assignment the assignment that has been changed.
     */
    /*public function saveAuthGroupAssignment($assignment)
    {
            $this->db->createCommand()
                    ->update($this->assignmentTableGroup, array(
                            'bizrule'=>$assignment->getBizRule(),
                            'data'=>serialize($assignment->getData()),
                    ), 'itemname=:itemname AND groupid=:groupid', array(
                            'itemname'=>$assignment->getItemName(),
                            'groupid'=>$assignment->getGroupId()
                    ));
    }*/
    /**
     * NOT USE
     * @return mixed group ID
     */
    /*public function getGroupId()
    {
            return $this->groupId;
    }*/
    /**
     * Returns the authorization items of the specific type and group.
     * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
     * meaning returning all items regardless of their type.
     * @param mixed $groupId the group ID. Defaults to null, meaning returning all items even if
     * they are not assigned to a group.
     * @return array the authorization items of the specific type.
     */
    public function getAuthGroupItems($type=null,$groupId=null)
    {
            if($type===null && $groupId===null)
            {
                    $command=$this->db->createCommand()
                            ->select()
                            ->from($this->itemTable);
            }
            else if($groupId===null)
            {
                    $command=$this->db->createCommand()
                            ->select()
                            ->from($this->itemTable)
                            ->where('type=:type', array(':type'=>$type));
            }
            else if($type===null)
            {
                    $command=$this->db->createCommand()
                            ->select('name,type,description,t1.bizrule,t1.data')
                            ->from(array(
                                    $this->itemTable.' t1',
                                    $this->assignmentTableGroup.' t2'
                            ))
                            ->where('name=itemname AND group_id=:groupid', array(':groupid'=>$groupId));
            }
            else
            {
                    $command=$this->db->createCommand()
                            ->select('name,type,description,t1.bizrule,t1.data')
                            ->from(array(
                                    $this->itemTable.' t1',
                                    $this->assignmentTableGroup.' t2'
                            ))
                            ->where('name=itemname AND type=:type AND group_id=:groupid', array(
                                    ':type'=>$type,
                                    ':groupid'=>$groupId
                            ));
            }
            $items=array();
            foreach($command->queryAll() as $row)
            {
                    if(($data=@unserialize($row['data']))===false)
                            $data=null;
                    $items[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
            }
            return $items;
    }
    /**
     * Returns the children of the specified item.
     * @param mixed $names the parent item name. This can be either a string or an array.
     * The latter represents a list of item names.
     * @return array all child items of the parent
     */
    public function getItemChildrenGroup($names)
    {
            if(is_string($names))
                    $condition='parent='.$this->db->quoteValue($names);
            else if(is_array($names) && $names!==array())
            {
                    foreach($names as &$name)
                            $name=$this->db->quoteValue($name);
                    $condition='parent IN ('.implode(', ',$names).')';
            }

            $rows=$this->db->createCommand()
                    ->select('name, type, description, bizrule, data')
                    ->from(array(
                            $this->itemTable,
                            $this->itemChildTable
                    ))
                    ->where($condition.' AND name=child')
                    ->queryAll();

            $children=array();
            foreach($rows as $row)
            {
                    if(($data=@unserialize($row['data']))===false)
                            $data=null;
                    $children[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
            }
            return $children;
    }
    	/**
         * NOT USE
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	/*public function removeAuthItemGroup($name)
	{
		if($this->usingSqlite())
		{
			$this->db->createCommand()
				->delete($this->itemChildTable, 'parent=:name1 OR child=:name2', array(
					':name1'=>$name,
					':name2'=>$name
			));
			$this->db->createCommand()
				->delete($this->assignmentTableGroup, 'itemname=:name', array(
					':name'=>$name,
			));
		}

		return $this->db->createCommand()
			->delete($this->itemTable, 'name=:name', array(
				':name'=>$name
			)) > 0;
	}*/
        /**
         * NOT USE
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	/*public function saveAuthItemGroup($item,$oldName=null)
	{
		if($this->usingSqlite() && $oldName!==null && $item->getName()!==$oldName)
		{
			$this->db->createCommand()
				->update($this->itemChildTable, array(
					'parent'=>$item->getName(),
				), 'parent=:whereName', array(
					':whereName'=>$oldName,
				));
			$this->db->createCommand()
				->update($this->itemChildTable, array(
					'child'=>$item->getName(),
				), 'child=:whereName', array(
					':whereName'=>$oldName,
				));
			$this->db->createCommand()
				->update($this->assignmentTableGroup, array(
					'itemname'=>$item->getName(),
				), 'itemname=:whereName', array(
					':whereName'=>$oldName,
				));
		}

		$this->db->createCommand()
			->update($this->itemTable, array(
				'name'=>$item->getName(),
				'type'=>$item->getType(),
				'description'=>$item->getDescription(),
				'bizrule'=>$item->getBizRule(),
				'data'=>serialize($item->getData()),
			), 'name=:whereName', array(
				':whereName'=>$oldName===null?$item->getName():$oldName,
			));
	}*/
        /**
         * NOT USE
	 * Removes all authorization assignments.
	 */
	/*public function clearAuthGroupAssignments()
	{
		$this->db->createCommand()->delete($this->assignmentTableGroup);
	}*/
}
?>
