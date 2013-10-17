<?php
/**
 * taskAssign.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */

/**
 * The tab view for assigning operations to tasks
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem.tabViews
 * @since 1.0.0
 */
?>
<?php
$criteria = new CDbCriteria();
$criteria->condition = "type=1";
$criteria->order = "name";
?>
<!-- TASKS -> OPERATIONS -->
<div class="srbac">
  <?php echo SHtml::beginForm(); ?>
  <?php echo SHtml::errorSummary($model); ?>
  <table width="100%">
    <tr><th colspan="2"><?php echo Helper::translate('srbac','Assign Operations to Tasks') ?></th>
    </tr>
    <tr>
      <th width="50%">
      <?php echo SHtml::label(Helper::translate('srbac',"Task"),'task'); ?></th>
      <td width="50%" rowspan="2">
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
        
        $this->widget('system.web.widgets.CTabView', array('tabs'=>$tabParameters,'activeTab'=>"tab2"));
    ?>      
      </td>
    </tr>
    <tr valign="top">
      <td><?php echo SHtml::activeDropDownList(Assignments::model(),'itemname',
        SHtml::listData(AuthItem::model()->findAll($criteria), 'name', 'name'),
        array('size'=>$this->module->listBoxNumberOfLines,'class'=>'dropdown',
            'ajax' => array(
                'type'=>'POST',
                'url'=>array('getOpers'),
                'beforeSend' => 'function(){
                              $("#loadMessTask,#loadMessOper").addClass("srbacLoading");
                          }',
                'complete' => 'function(){
                              $("#loadMessTask,#loadMessOper").removeClass("srbacLoading");
                          }',
                'success'=>'function(response){
                            $("#operations").html(response);
                            $.ajax({
                                type: "POST",
                                url: "'.$urlManager->createUrl($parent."srbac/authitem/getTasks").'",
                                data: {
                                   "checked":$("#cleverTask").attr("checked"),
                                   "name":$("#Assignments_itemname").val(),
                                   "nameInput":$("#cleverTaskInput").val()
                                },    
                                success: function(response){
                                  $("#tasks").html(response);
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
$urlManager = Yii::app()->getUrlManager();
$parent = $this->module->parentModule ? $this->module->parentModule->name."/" : "" ;
$url1 = $urlManager->createUrl($parent."srbac/authitem/getCleverOpers");
$url2 = $urlManager->createUrl($parent."srbac/authitem/getCleverTasks");
?>
<?php
$script1 = "jQuery('#cleverOper').click(function(){
  var checked = $('#cleverOper').attr('checked');
  var name = $('#Assignments_itemname').attr('value');
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
  var name = $('#Assignments_itemname').attr('value');
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
  var name = $('#Assignments_itemname').attr('value');
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
  var name = $('#Assignments_itemname').attr('value');
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
Yii::app()->clientScript->registerScript("cbOper",$script1,CClientScript::POS_READY);
Yii::app()->clientScript->registerScript("cbTask",$script2,CClientScript::POS_READY);
?>