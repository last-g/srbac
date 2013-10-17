<?php

/**
 * This is the model class for table "auth_assignment_group".
 *
 * The followings are the available columns in table 'auth_assignment_group':
 * @property string $itemname
 * @property string $group_id
 * @property string $bizrule
 * @property string $data
 */
class AssignmentsGroup extends CActiveRecord
{
/**
 * Returns the static model of the specified AR class.
 * @return CActiveRecord the static model class
 */
  public static function model($className=__CLASS__) {
    return parent::model($className);
  }

  public function getDbConnection() {
    return Yii::app()->authManager->db;
  }

  /**
   * @return string the associated database table name
   */
  public function tableName() {
    return Yii::app()->authManager->assignmentTableGroup;
  }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('itemname, group_id', 'required'),
			array('itemname', 'length', 'max'=>64),
			array('group_id', 'length', 'max'=>46),
			array('bizrule, data', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('itemname, group_id, bizrule, data', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'itemname' => 'Itemname',
			'group_id' => 'Groupid',
			'bizrule' => 'Bizrule',
			'data' => 'Data',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('itemname',$this->itemname,true);
		$criteria->compare('group_id',$this->group_id,true);
		$criteria->compare('bizrule',$this->bizrule,true);
		$criteria->compare('data',$this->data,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}