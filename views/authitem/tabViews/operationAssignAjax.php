<?php
/**
 * operationAssignAjax.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The assigning operations to tasks listboxes
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem.tabViews
 * @since 1.0.0
 */
 ?>
<table width="100%">
  <tr>
    <th><?php echo Helper::translate('srbac','Assigned Operations') ?></th>
    <th>&nbsp;</th>
    <th><?php echo Helper::translate('srbac','Not Assigned Operations')?></th>
  </tr>
  <tr><td width="45%">
      <?php echo SHtml::activeDropDownList($model,'name[revoke]',
      SHtml::listData(
      $data['assignedOpers'], 'name', 'name'),
      array('size'=>$this->module->listBoxNumberOfLines,'multiple'=>'multiple','class'=>'dropdown')) ?>
    </td>
    <td width="10%" align="center">
      <?php
      $ajax = array(
            'type'=>'POST',
            'update'=>'#operations',
            'beforeSend' => 'function(){
                        $("#loadMessTaskOper").addClass("srbacLoading");
                    }',
            'complete' => 'function(){
                        $("#loadMessTaskOper").removeClass("srbacLoading");
                    }');
      echo  SHtml::ajaxSubmitButton('<<',array('ajaxAssign','assignOpers'=>1),$ajax,$data['assign']);
      echo  SHtml::ajaxSubmitButton('>>',array('ajaxAssign','revokeOpers'=>1),$ajax,$data['revoke']); ?>
    </td>
    <td width="45%">
      <?php echo SHtml::activeDropDownList($model,'name[assign]',
      SHtml::listData(
      $data['notAssignedOpers'], 'name', 'name'),
      array('size'=>$this->module->listBoxNumberOfLines,'multiple'=>'multiple','class'=>'dropdown')); ?>
    </td></tr>
</table>
<div id="loadMessOper" class="message">
  <?php echo "&nbsp;".$message ?>
</div>