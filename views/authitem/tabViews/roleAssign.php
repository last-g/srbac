<?php
/**
 * roleAssign.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The tab view for assigning tasks to roles
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem.tabViews
 * @since 1.0.0
 */
 ?>
<!-- ROLES -> TASKS -->
<?php
$criteria = new CDbCriteria();
$criteria->condition = "type=2";
$criteria->order = "name";
?>
<div class="srbac">
  <?php echo SHtml::beginForm(); ?>
  <?php echo SHtml::errorSummary($model); ?>
  <table width="100%">
    <tr><th colspan="2"><?php echo Helper::translate('srbac','Assign Tasks to Roles') ?></th>
    </tr>
    <tr>
      <th width="50%">
      <?php echo SHtml::label(Helper::translate('srbac',"Role"),'role'); ?></th>
      <td width="50%" rowspan="2">
    <?php $this->beginWidget('system.web.widgets.CClipWidget', array('id'=>Helper::translate('srbac','Roles'))); ?>
        <div>
            <?php echo Helper::translate("srbac","Clever Assigning"); ?>:
            <?php echo SHtml::checkBox("cleverRole",  Yii::app()->getGlobalState("cleverAssigningRole")); ?>
            <?php echo SHtml::textField("cleverRoleInput"); ?>
        </div>  
        <div id="roles">
          <?php
          $this->renderPartial('tabViews/roleAssignAjax',
              array('model'=>$model,'data'=>$data,'message'=>$message));
          ?>
        </div>
    <?php $this->endWidget(); ?>    
    <?php $this->beginWidget('system.web.widgets.CClipWidget', array('id'=>Helper::translate('srbac','Tasks'))); ?>
        <div>
            <?php echo Helper::translate("srbac","Clever Assigning"); ?>:
            <?php echo SHtml::checkBox("cleverTask",  Yii::app()->getGlobalState("cleverAssigningTask")); ?>
            <?php echo SHtml::textField("cleverTaskInput"); ?>
        </div>  
        <div id="tasks">
          <?php                   
          $this->renderPartial('tabViews/taskAssignAjax',
              array('model'=>$model,'data'=>$data,'message'=>$message));
          ?>
        </div>
    <?php $this->endWidget(); ?>
    <?php $this->beginWidget('system.web.widgets.CClipWidget', array('id'=>Helper::translate('srbac','Operations'))); ?>
        <div>
            <?php echo Helper::translate("srbac","Clever Assigning"); ?>:
            <?php echo SHtml::checkBox("cleverOper",  Yii::app()->getGlobalState("cleverAssigning")); ?>
            <?php echo SHtml::textField("cleverOperInput"); ?>
        </div>  
        <div id="operations">
          <?php
          $this->renderPartial('tabViews/operationAssignAjax',
              array('model'=>$model,'data'=>$data,'message'=>$message));
          ?>
        </div>
    <?php $this->endWidget(); ?>    
    <?php
    $tabParameters = array();
    foreach($this->clips as $key=>$clip)
        $tabParameters['tab'.(count($tabParameters)+1)] = array('title'=>$key, 'content'=>$clip);
    $urlManager = Yii::app()->getUrlManager();
    $parent = $this->module->parentModule ? $this->module->parentModule->name."/" : "" ;
    
    $this->widget('system.web.widgets.CTabView', array('tabs'=>$tabParameters,'activeTab'=>"tab2")); ?> 
      </td>
    </tr>
    <tr valign="top">
      <td><?php echo SHtml::activeDropDownList(AuthItem::model(),'name[0]',
        SHtml::listData(AuthItem::model()->findAll($criteria), 'name', 'name'),
        array('size'=>$this->module->listBoxNumberOfLines,
            'class'=>'dropdown',
            'ajax' => array(
                'type'=>'POST',
                'url'=>array('getTasks'),
                'beforeSend' => 'function(){
                              $("#loadMessRole,#loadMessTask,#loadMessOper").addClass("srbacLoading");
                          }',
                'complete' => 'function(){
                              $("#loadMessRole,#loadMessTask,#loadMessOper").removeClass("srbacLoading");
                }',              
                'success'=>'function(response){
                            $("#tasks").html(response);
                            $.ajax({
                                type: "POST",
                                url: "'.$urlManager->createUrl($parent."srbac/authitem/getOpers").'",
                                data: {
                                   "checked":$("#cleverOper").attr("checked"),
                                   "name":$("#AuthItem_name_0").val(),
                                   "nameInput":$("#cleverOperInput").val()
                                },    
                                success: function(response){
                                  $("#operations").html(response);
                                }
                           });
                           $.ajax({
                                type: "POST",
                                url: "'.$urlManager->createUrl($parent."srbac/authitem/getRoles").'",
                                data: {
                                   "checked":$("#cleverRole").attr("checked"),
                                   "name":$("#AuthItem_name_0").val(),
                                   "nameInput":$("#cleverRoleInput").val()
                                },    
                                success: function(response){
                                  $("#roles").html(response);
                                }
                           });
                }'
            ),
        )); ?>
      </td>
    </tr>
  </table>
  <br />
  <?php echo SHtml::endForm(); ?>
</div>
<?php
$url1 = $urlManager->createUrl($parent."srbac/authitem/getCleverOpers");
$url2 = $urlManager->createUrl($parent."srbac/authitem/getCleverTasks");
$url3 = $urlManager->createUrl($parent."srbac/authitem/getCleverRoles");
?>
<?php
$script1 = "jQuery('#cleverOper').click(function(){
  var checked = $('#cleverOper').attr('checked');
  var name = $('#AuthItem_name_0').attr('value');
  var nameInput='';
  if(checked=='checked')
    nameInput=$('#cleverOperInput').val();
    
  $.ajax({
   type: 'POST',
   url: '{$url1}',
   data: 'checked='+checked+'&name='+name+'&nameInput='+nameInput,
   beforeSend: function(){
     $('#loadMessOper').addClass('srbacLoading');
   },
   complete: function(){
     $('#loadMessOper').removeClass('srbacLoading');
   },
  success: function(data){
     $('#operations').html(data);
   }
 });

});
jQuery('#cleverOperInput').keyup(function(){ 
  var checked = $('#cleverOper').attr('checked');
  var name = $('#AuthItem_name_0').attr('value');
  var nameInput=$(this).val();
  if($(this).val()!='')
  {
    checked='checked';
    $('#cleverOper').attr('checked',true); 
  }
  $.ajax({
   type: 'POST',
   url: '{$url1}',
   data: 'checked='+checked+'&name='+name+'&nameInput='+nameInput,
   beforeSend: function(){
     $('#loadMessOper').addClass('srbacLoading');
   },
   complete: function(){
     $('#loadMessOper').removeClass('srbacLoading');
   },
  success: function(data){
     $('#operations').html(data);
   }
 });

});";
$script2 = "jQuery('#cleverTask').click(function(){
  var checked = $('#cleverTask').attr('checked');
  var name = $('#AuthItem_name_0').attr('value');
  var nameInput='';
  if(checked=='checked')
    nameInput=$('#cleverTaskInput').val();
  $.ajax({
   type: 'POST',
   url: '{$url2}',
   data: 'checked='+checked+'&name='+name+'&nameInput='+nameInput,
   beforeSend: function(){
     $('#loadMessTask').addClass('srbacLoading');
   },
   complete: function(){
     $('#loadMessTask').removeClass('srbacLoading');
   },
  success: function(data){
     $('#tasks').html(data);
   }
 });

});
jQuery('#cleverTaskInput').keyup(function(){  
  var checked = $('#cleverTask').attr('checked'); 
  var name = $('#AuthItem_name_0').attr('value');
  var nameInput=$(this).val();
  if($(this).val()!='')
  {
    checked='checked';
    $('#cleverTask').attr('checked',true);
  }
  $.ajax({
   type: 'POST',
   url: '{$url2}',
   data: 'checked='+checked+'&name='+name+'&nameInput='+nameInput,
   beforeSend: function(){
     $('#loadMessTask').addClass('srbacLoading');
   },
   complete: function(){
     $('#loadMessTask').removeClass('srbacLoading');
   },
  success: function(data){
     $('#tasks').html(data);
   }
 });

});";
$script3 = "jQuery('#cleverRole').click(function(){
  var checked = $('#cleverRole').attr('checked');
  var name = $('#AuthItem_name_0').attr('value');
  var nameInput='';
  if(checked=='checked')
    nameInput=$('#cleverRoleInput').val();
  $.ajax({
   type: 'POST',
   url: '{$url3}',
   data: 'checked='+checked+'&name='+name+'&nameInput='+nameInput,
   beforeSend: function(){
     $('#loadMessRole').addClass('srbacLoading');
   },
   complete: function(){
     $('#loadMessRole').removeClass('srbacLoading');
   },
  success: function(data){
     $('#roles').html(data);
   }
 });

});
jQuery('#cleverRoleInput').keyup(function(){  
  var checked = $('#cleverRole').attr('checked'); 
  var name = $('#AuthItem_name_0').attr('value');
  var nameInput=$(this).val();
  if($(this).val()!='')
  {
    checked='checked';
    $('#cleverRole').attr('checked',true);
  }
  $.ajax({
   type: 'POST',
   url: '{$url3}',
   data: 'checked='+checked+'&name='+name+'&nameInput='+nameInput,
   beforeSend: function(){
     $('#loadMessRole').addClass('srbacLoading');
   },
   complete: function(){
     $('#loadMessRole').removeClass('srbacLoading');
   },
  success: function(data){
     $('#roles').html(data);
   }
 });

});";
Yii::app()->clientScript->registerScript("cbOper",$script1,CClientScript::POS_READY);
Yii::app()->clientScript->registerScript("cbTask",$script2,CClientScript::POS_READY);
Yii::app()->clientScript->registerScript("cbRole",$script3,CClientScript::POS_READY);
?>