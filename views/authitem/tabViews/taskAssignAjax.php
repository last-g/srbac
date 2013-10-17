<?php
/**
 * taskAssignAjax.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The assigning task to roles listboxes
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem.tabViews
 * @since 1.0.0
 */
 ?>
<table width="100%">
  <tr>
    <th><?php echo Helper::translate('srbac','Assigned Tasks')?></th>
    <th>&nbsp;</th>
    <th><?php echo Helper::translate('srbac','Not Assigned Tasks')?></th>
  </tr>
  <tr><td width="45%">
      <?php echo SHtml::activeDropDownList($model,'name[revoke]',
      SHtml::listData(
      $data["assignedTasks"], 'name', 'name'),
      array('size'=>$this->module->listBoxNumberOfLines,'multiple'=>'multiple','class'=>'dropdown')) ?>
    </td>
    <td width="10%" align="center">
      <?php
      $ajax = array(
            'type'=>'POST',
            'update'=>'#tasks',
            'beforeSend' => 'function(){
                      $("#loadMessTask").addClass("srbacLoading");
                  }',
            'complete' => 'function(){
                      $("#loadMessTask").removeClass("srbacLoading");
                  }');
      echo  SHtml::ajaxSubmitButton('<<',array('ajaxAssign','assignTasks'=>1),$ajax,$data['assign']);
      echo  SHtml::ajaxSubmitButton('>>',array('ajaxAssign','revokeTasks'=>1),$ajax,$data['revoke']); ?>
    </td>
    <td width="45%">
      <?php echo SHtml::activeDropDownList($model,'name[assign]',
      SHtml::listData(
      $data["notAssignedTasks"], 'name', 'name'),
      array('size'=>$this->module->listBoxNumberOfLines,'multiple'=>'multiple','class'=>'dropdown')); ?>
    </td></tr>
</table>
<div id="loadMessTask" class="message">
  <?php echo "&nbsp;".$message ?>
</div>
