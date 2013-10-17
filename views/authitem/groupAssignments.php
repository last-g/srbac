<?php
/**
 * groupAssignments.php
 */
 ?>
<br />
<h1>Assignments of group : '<?php echo $groupname?>'</h1>
<table class="srbacDataGrid" width="100%">
  <tr>
    <th class="groups"><?php echo Helper::translate('srbac','Groups')?></th>  
    <th class="roles"><?php echo Helper::translate('srbac','Roles')?></th>
    <th class="tasks"><?php echo Helper::translate('srbac','Tasks')?></th>
    <th class="operations"><?php echo Helper::translate('srbac','Operations')?></th>
  </tr>
  <tr>
    <td valign="top" colspan="4">
        <table class="groups">
            <?php foreach ($data as $k=>$groups) { ?>
            <tr>
                <td><b><?php echo $k; ?></b>         
                  <?php foreach ($groups as $i=>$roles) { ?>
                  <table class="roles">
                    <tr>
                      <td><b><?php echo $i; ?></b>
                          <?php foreach ($roles as $j=>$tasks) { ?>
                        <table class="tasks">
                          <tr>
                            <td valign="top">
                                  <?php echo $j; ?>
                              <table class="operations">
                                <tr>
                                  <td valign="top">
                                  <?php foreach ($tasks as $opers) { ?>
                                          <?php echo $opers."<br />";  ?>
                                  <?php } ?>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                          <?php }?>
                      </td>
                    </tr>
                  </table>
            <?php } ?>  
            </td>
          </tr>
          <?php } ?> 
        <tr>
            <td>
              <?php echo Helper::translate('srbac','Unique operations')?>:
              <?php echo $uniqOper; ?>
            </td>  
        </tr>
      </table>
    </td>
  </tr>
</table>