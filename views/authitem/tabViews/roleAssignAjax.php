<?php
/**
 * roleAssignAjax.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The assigning roles to users listboxes
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem.tabViews
 * @since 1.0.0
 */
 ?>
<table width="100%">
  <tr>
    <th><?php echo Helper::translate('srbac','Assigned Roles') ?></th>
    <th>&nbsp;</th>
    <th><?php echo Helper::translate('srbac','Not Assigned Roles') ?></th>
  </tr>
  <tr><td width="45%">
      <?php echo SHtml::activeDropDownList($model,'name[revoke]',
      SHtml::listData(
      $data['assignedRoles'], 'name', 'name'),
      array('size'=>$this->module->listBoxNumberOfLines,'multiple'=>'multiple','class'=>'dropdown')) ?>
    </td>
    <td width="10%" align="center">
      <?php
      $ajax = array(
            'type'=>'POST',
            'update'=>'#roles',
            'beforeSend' => 'function(){
                        $("#loadMessRole").addClass("srbacLoading");
                    }',
            'complete' => 'function(){
                        $("#loadMessRole").removeClass("srbacLoading");
                    }');
      echo  SHtml::ajaxSubmitButton('<<',array('ajaxAssign','assignRoles'=>1),$ajax,$data['assign']);
      echo  SHtml::ajaxSubmitButton('>>',array('ajaxAssign','revokeRoles'=>1),$ajax,$data['revoke']); ?>
    </td>
    <td width="45%">
      <?php echo SHtml::activeDropDownList($model,'name[assign]',
      SHtml::listData(
      $data['notAssignedRoles'], 'name', 'name'),
      array('size'=>$this->module->listBoxNumberOfLines,'multiple'=>'multiple','class'=>'dropdown')); ?>
    </td></tr>
</table>
<div id="loadMessRole" class="message" >
  <?php echo "&nbsp;".$message ?>
</div>
