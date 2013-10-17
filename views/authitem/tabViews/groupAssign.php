<?php
/**
 * groupAssign.php
 */
 ?>
<!-- GROUP -> ROLES -->
<div class="srbac">
  <?php echo SHtml::beginForm(); ?>
  <?php echo SHtml::errorSummary($model); ?>
  <table width="100%">
    <tr><th colspan="2"><?php echo Helper::translate('srbac','Assign Roles to Groups')?></th></tr>
    <tr>
      <th width="50%">
      <?php echo SHtml::label(Helper::translate('srbac',"Group"),'group'); ?></th>
      <td width="50%" rowspan="2">
        <div id="roles">
          <?php
          $this->renderPartial(
            'tabViews/roleAssignAjax',
            array('model'=>$model,'data'=>$data,'message'=>$message)
          );
          ?>
        </div>
      </td>
    </tr>
    <tr valign="top">
      <td><?php
          $criteria = new CDbCriteria();
          $criteria->order = $this->module->groupname;
          echo SHtml::activeDropDownList($this->module->getGroupModel(),$this->module->groupid,
        SHtml::listData($this->module->getGroupModel()->findAll($criteria), $this->module->groupid, $this->module->groupname),
        array('size'=>$this->module->listBoxNumberOfLines,'class'=>'dropdown','ajax' => array(
        'type'=>'POST',
        'url'=>array('getRoles'),
        'update'=>'#roles',
        'beforeSend' => 'function(){
                      $("#loadMess").addClass("srbacLoading");
                  }',
        'complete' => 'function(){
                      $("#loadMess").removeClass("srbacLoading");
                  }'
        ),
        )); ?>
      </td>
    </tr>
  </table>
  <br/>
  <?php echo SHtml::endForm(); ?>
</div>
