<?php
/**
 * assignments.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */
/**
 * The view of the users assignments
 * If no user id is passed a drop down with all users is shown
 * Else the user's assignments are shown.
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem
 * @since 1.0.1
 */
?>
<?php
$this->breadcrumbs = array(
    'Srbac Assignments'
  )
?>
<?php 
$parent = $this->module->parentModule ? $this->module->parentModule->name."/" : "" ;
if ($this->module->getMessage() != "") { ?>
  <div id="srbacError">
    <?php echo $this->module->getMessage(); ?>
  </div>
<?php } ?>
<?php
if (!$id) {
  if ($this->module->getShowHeader()) {
    $this->renderPartial($this->module->header);
  }
  ?>
  <div class="simple">
    <?php
    $this->renderPartial("frontpage");
    ?>
    <?php echo SHtml::beginForm(); ?>
    user:
    <?php
    $this->widget('application.widgets.callider.EmployeeDropDownListWidget.EmployeeDropDownListWidget',
        array(
            "userId"=>"",
            "htmlOptions"=>array(
                'ajax' =>array(
                        'data' => array('id' => 'js:this.value'),
                        'type'   => 'GET',
                        'url'    =>  $parent.Yii::app()->urlManager->createUrl("srbac/authitem/showAssignments"),
                        'update' => '#assignments'
                    ),
                )
        )
    );
    ?> group:
    <?php
    $this->widget('application.widgets.callider.GroupDropDownListWidget.GroupDropDownListWidget',
        array(
            "groupId"=>"",
            "htmlOptions"=>array(
                    'ajax' =>array(
                        'data' => array('id' => 'js:this.value'),
                        'type'   => 'GET',
                        'url'    =>  $parent.Yii::app()->urlManager->createUrl("srbac/authitem/showGroupAssignments"),
                        'update' => '#assignments'
                    ),
                )
        )
    );
    ?>
  <?php echo SHtml::endForm(); ?>
  </div>
<?php }?>
<div id="assignments"></div>
<?php if (!$id) { ?>
  <?php
  if ($this->module->getShowFooter()) {
    $this->renderPartial($this->module->footer);
  }
  ?>
<?php
}?>