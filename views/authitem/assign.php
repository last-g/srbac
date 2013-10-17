<?php
/**
 * assign.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The Assign tabview view
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem
 * @since 1.0.0
 */
?>
<?php $this->breadcrumbs = array(
    'Srbac Assign'
)
?>
<?php if($this->module->getMessage() != ""){ ?>
<div id="srbacError">
  <?php echo $this->module->getMessage();?>
</div>
<?php } ?>
<?php if($this->module->getShowHeader()) {
  $this->renderPartial($this->module->header);
}
?>
<div>
  <?php

  $this->renderPartial("frontpage");
  ?>
    <div id="wizardButton" style="text-align:left" class="controlPanel marginBottom">
    <?php echo SHtml::ajaxLink(Helper::translate('srbac','Users'),
                array('ajaxAssign','view'=>'userAssign','type'=>'Users'),
                array(
                    'type'=>'POST',
                    'update'=>'#wizard',
                    'beforeSend' => 'function(){
                                      $("#wizard").addClass("srbacLoading");
                                  }',
                    'complete' => 'function(){
                                      $("#wizard").removeClass("srbacLoading");
                                  }',
                ),
                array(
                    'name'=>'usersAssign',
                    'onclick'=>"$(this).css('font-weight', 'bold');$(this).siblings().css('font-weight', 'normal');",
                )
            );
    ?>
    <?php echo SHtml::ajaxLink(Helper::translate('srbac','Groups'),
                array('ajaxAssign','view'=>'groupAssign','type'=>'Groups'),
                array(
                    'type'=>'POST',
                    'update'=>'#wizard',
                    'beforeSend' => 'function(){
                                      $("#wizard").addClass("srbacLoading");
                                  }',
                    'complete' => 'function(){
                                      $("#wizard").removeClass("srbacLoading");
                                  }',
                ),
                array(
                    'name'=>'buttonAuto',
                    'onclick'=>"$(this).css('font-weight', 'bold');$(this).siblings().css('font-weight', 'normal');",
                )
            );
    ?>
    <?php echo SHtml::ajaxLink(Helper::translate('srbac','Roles'),
                array('ajaxAssign','view'=>'roleAssign','type'=>CAuthItem::TYPE_ROLE),
                array(
                    'type'=>'POST',
                    'update'=>'#wizard',
                    'beforeSend' => 'function(){
                                      $("#wizard").addClass("srbacLoading");
                                  }',
                    'complete' => 'function(){
                                      $("#wizard").removeClass("srbacLoading");
                                  }',
                ),
                array(
                    'name'=>'buttonAllowed',
                    'onclick'=>"$(this).css('font-weight', 'bold');$(this).siblings().css('font-weight', 'normal');",
                )
            );
    ?>
    <?php echo SHtml::ajaxLink(Helper::translate('srbac','Tasks'),
                array('ajaxAssign','view'=>'taskAssign','type'=>CAuthItem::TYPE_TASK),
                array(
                    'type'=>'POST',
                    'update'=>'#wizard',
                    'beforeSend' => 'function(){
                                      $("#wizard").addClass("srbacLoading");
                                  }',
                    'complete' => 'function(){
                                      $("#wizard").removeClass("srbacLoading");
                                  }',
                ),
                array(
                    'name'=>'buttonClear',
                    'onclick'=>"$(this).css('font-weight', 'bold');$(this).siblings().css('font-weight', 'normal');",
                )
            );
    ?>
    </div>
    <div id="wizard"></div>
</div>
<?php if($this->module->getShowFooter()) {
  $this->renderPartial($this->module->footer);
}
?>
