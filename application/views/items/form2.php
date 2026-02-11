<!--FORMULAIRE D'AJOUT article--->
</div>
<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="

    position: absolute;
    left: 50%;
    right: 50%;
    top: 0%;
    transform: translate(-50%,50%);
    width: 870px;
    z-index: 101;
">

<!--HEADER-->
<div class="fenetre-header">
  <span id="page_title" class="fenetre-title">
    <?php echo $_SESSION['$title']; /*$this->lang->line('modules_'.$_SESSION['controller_name']).' '*/?>
  </span>

  <?php
  include('../wrightetmathon/application/views/partial/show_exit.php');
  ?>


</div>


<!---CONTENT-->
<div class="fenetre-content">
  <div class="centrepage">



      <!-- show next actions link -->
      <?php
            // show delete button and barcode button - only if item not undeleting
            if ($_SESSION['new'] != 1)
            {
?>
<div class="txt_milieu">
              <div class="btnp">
          <a href="<?php echo site_url("items/view_pricelists");?>" class="" style="width: 180px; margin:0; height:38px; text-align: left;"><img style="margin-left: 5px;
                                  margin-bottom: -15px;
                                  "  width="35px" height ="35px" src="images/prix.png"> <?php echo $this->lang->line('pricelists_pricelist') ;?> </a>
                  <a href="<?php echo site_url("items/view_suppliers");?>" style="width: 180px; margin:0; height:38px; text-align: left;" class=""><img style="margin-left: 5px;
                                  margin-bottom: -15px;
                                  "  width="35px" height ="35px" src="images/fournisseur.png"> <?php echo $this->lang->line('items_supplier') ;?> </a>
                  <a href="<?php echo site_url("items/view_warehouses");?>" style="width: 180px; margin:0; height:38px; text-align: left;" disabled="disabled"><img style="margin-left: 5px;
                                  margin-bottom: -15px;
                                  "  width="35px" height ="35px" src="images/entrepot.png"> <?php echo $this->lang->line('warehouses_warehouse') ;?> </a>
              </div>
   <?php  }?>

</div>

    <div class="blocformfond creationimmediate">

      <?php include('../wrightetmathon/application/views/partial/show_messages.php');?>






    <?php
    // show delete button - only if item exists already
    if ($_SESSION['new'] != 1)
    {
        ?>



        <?php
// show undel button - only if undeleting
        if (($_SESSION['undel'] ?? 0) == 1)
        {
            echo form_open			(
                'items/undelete/',
                array('id'=>'item_undelete_form')
            );

            echo form_submit		(	array	(
                    'name'	=>	'undelete',
                    'id'	=>	'undelete',
                    'value'	=>	$this->lang->line('items_undelete'),
                    'class'	=>	'btmodification txt_gauche'
                )
            );
            echo form_close();
        }
        ?>

        <?php
    }
    ?>

      <?php
      // show data entry but not if item is already deleted
      if ($_SESSION['del'] == NULL)
      {
        ?>
        <?php
        // show enter button - only if item not undeleting
        if ($_SESSION['undel'] == NULL)
        {
          // when clicked use the controller, items, selecting method save and passing it the item ID.
          echo form_open($_SESSION['controller_name'].'/save/'.$_SESSION['transaction_info']->item_id.'/'.$_SESSION['origin'], array('id'=>'item_form'));
          ?>
          <fieldset>
            <table   style="

            border-spacing: 15px;
            border-collapse: separate;">
              <tbody>
                <tr>
                      <td  class="zone_champ_obligatoire"><?php echo form_input(array	(
                        'name'		=>	'item_number',
                        'id'		=>	'item_number',
                        'style'		=>	'font-size:15px;',
                        'size'=>9,

                        'placeholder'=>  $this->lang->line('items_item_number'),
                        'value'		=>	$_SESSION['transaction_info']->item_number
                      ));?>
                      <a class="btaide" id="" title="<?php echo $this->lang->line('items_item_number');?>"></a>
                    </td>

                    <td colspan="3" class="zone_champ_obligatoire" ><?php echo form_input(array	(
                      'name'		=>	'name',
                      'id'		=>	'name',
                      'style'		=>	'font-size:15px;',
                      'placeholder'=>  $this->lang->line('items_name'),
                      'size'		=>	35,
                      'value'		=>	$_SESSION['transaction_info']->name
                    ));?>
                    <a class="btaide" id="" title="<?php echo $this->lang->line('items_name');?>"></a>
                  </td>


      </tr>

      <tr>

            <td class="zone_champ_obligatoire" ><?php echo form_label($this->lang->line('items_category'), 'category_id',array('class'=>'required wide')); ?>

            <?php echo form_dropdown	(
              'category_id',
              $_SESSION['category_pick_list'],
              $_SESSION['selected_category'],
              'style="margin-left:4px; margin-bottom:10px;font-size:15px"',
              'class="select_obligatoire"'
            );?>
              <a class="btaide" id="" title="<?php echo $this->lang->line('items_category');?>"></a>
          </td>
          <td  class="zone_champ_saisie"><?php echo form_input(array	(
            'name'		=>	'volume',
            'id'		=>	'volume',
            'style'		=>	'text-align:right; font-size:15px;',
            'size'		=>	4,
            'placeholder'=>  $this->lang->line('items_volume'),
            'title' => $this->lang->line('items_volume'),
            'value'		=>	$_SESSION['transaction_info']->volume
          )); echo 'MG/ML'; ?>
          <a class="btaide" id="" title="<?php echo $this->lang->line('items_volume');?>"></a>
        </td>
        <td class="zone_champ_saisie"><?php echo form_input(array	(
          'name'		=>	'nicotine',
          'id'		=>	'nicotine',
          'style'		=>	'text-align:right; font-size:15px;',
          'size'		=>	4,
          'placeholder'=>  $this->lang->line('items_nicotine'),
          'title' =>$this->lang->line('items_nicotine'),
          'value'		=>	$_SESSION['transaction_info']->nicotine
        )); echo 'ML';?>
        <a class="btaide" id="" title="<?php echo $this->lang->line('items_nicotine');?>"></a>
        </td>


</tr>
<tr>
  <td ><?php echo form_label($this->lang->line('items_dluo_indicator').$this->lang->line('common_question'), 'dluo_indicator', array('class'=>'required wide')); ?>
<?php echo form_dropdown	(
    'dluo_indicator',
    $_SESSION['G']->YorN_pick_list,
    $_SESSION['selected_dluo_indicator'],
    'style="font-size:15px"','class="select_obligatoire"'
  );?>
  <a class="btaide" id="" title="<?php echo $this->lang->line('items_dluo_indicator');?>"></a>
  </td>
  <td ><?php echo form_label($this->lang->line('items_offer_indicator').$this->lang->line('common_question'), 'offer_indicator', array('class'=>'required wide')); ?>

  <?php echo form_dropdown	(
  'offer_indicator',
  $_SESSION['G']->YorN_pick_list,
  $_SESSION['selected_offer_indicator'],
  'style="font-size:15px"','class="select_obligatoire"'
  );?>
    <a class="btaide" id="" title="<?php echo $this->lang->line('items_offer_indicator');?>"></a>
  </td>
      <td class="zone_champ_obligatoire"><?php echo $this->lang->line('items_tax_1'); ?>
      <?php echo form_input(array	(
        'name'		=>	'tax_percent_1',
        'id'		=>	'tax_percent_1',
        'size'		=>	5,
        'placeholder'=> '%',
        'style'		=>	'text-align:right; font-size:15px;',
        'value'		=>	isset($_SESSION['item_tax_info[0]']->percent) ? $_SESSION['item_tax_info[0]']->percent : $this->config->item('default_tax_1_rate')
      ));?>
      <a class="btaide" id="" title="<?php echo $this->lang->line('items_tax_1');?>"></a>
    </td>


</tr>

</tbody></table>

</fieldset>
<fieldset>
<table  style="
width: 100%;
/*border-spacing: 4px;*/
border-collapse: separate;">
<tr>


          <td colspan="5" align="center"><?php echo $this->lang->line('items_export').'?';?></td>
            <tr>
          <td ><?php echo form_label($this->lang->line('items_export_to_franchise'), 'export_f',array('class'=>'required wide')); ?></td>


          <td ><?php echo form_dropdown	(
            'export_to_franchise',
            $_SESSION['G']->YorN_pick_list,
            $_SESSION['selected_export_to_franchise'],
            'style="font-size:15px"','class="select_obligatoire"'
          );?>
          <a class="btaide" id="" title="<?php echo $this->lang->line('items_tax_1');?>"></a>
        </td>
        <td ><?php echo form_label($this->lang->line('items_export_to_integrated'), 'export_i',array('class'=>'required wide')); ?></td>

        <td ><?php echo form_dropdown	(
          'export_to_integrated',
          $_SESSION['G']->YorN_pick_list,
          $_SESSION['selected_export_to_integrated'],
          'style="font-size:15px"'
        );?>
        <a class="btaide" id="" title="<?php echo $this->lang->line('items_export_to_integrated');?>"></a>
      </td>

      <td align="left"><?php echo form_label($this->lang->line('items_export_to_other'), 'export_o',array('class'=>'required wide')); ?></td>

      <td align="left" class="zone_champ_obligatoire"><?php echo form_dropdown	(
        'export_to_other',
        $_SESSION['G']->YorN_pick_list,
        $_SESSION['selected_export_to_other'],
        'style="font-size:15px"','class="select_obligatoire"'
      );?>
      <a class="btaide" id="" title="<?php echo $this->lang->line('items_export_to_other');?>"></a>
    </td>
    <td  class="zone_champ_saisie" align="right"><?php echo form_input(array	(
      'name'		=>	'image_file_name',
      'id'		=>	'image_file_name',
      'style'		=>	'   margin-bottom: 3px;
    text-align: right;
    font-size: 15px;',
      'size'		=>	15,
      'placeholder'=> $this->lang->line('items_image_file_name'),
      'value'		=>	$_SESSION['transaction_info']->image_file_name
    ));?>
    <a class="btaide" id="" title="<?php echo $this->lang->line('items_image_file_name');?>"></a>
  </td>

</tr></table>




  <table class="table_center" style="      border: solid 2px #000000;
    padding: 10px;
    margin-top: 15px;
    margin-bottom: 15px;

    border-spacing: 15px;
    border-collapse: separate;">



  <tr>
    <td align="center"><?php echo $this->lang->line('items_margin');?>
    </td>

    <td align="left"><?php	echo	$_SESSION['transaction_info']->unit_price;
    echo	$this->lang->line('common_1_asterisk');
    echo	' > ';
    echo	$_SESSION['preferred_supplier_cost_price'];
    echo	$this->lang->line('common_2_asterisk');
    echo	' > ';
    echo	$_SESSION['percentage_profit'];
    echo '%';
    ?>
  </td>
</tr>
<tr></tr>
      <tr><td colspan="2">
          <div id="required_fields_message" style="">
              <?php
              echo $this->lang->line('common_1_asterisk');
              echo $this->lang->line('items_unit_price');
              echo ' ';
              echo $this->lang->line('pricelists_pricelist_default');
              echo ' ';
              ?>
          </div>
          </td>
      </tr>
      <tr><td colspan="2">
          <div id="required_fields_message" style="">
              <?php

              echo ' ';
              echo $this->lang->line('common_2_asterisk');
              echo $this->lang->line('items_cost_price');
              echo ' ';
              echo $this->lang->line('items_supplier_preferred');
              ?>
          </div>
          </td>
      </tr>



</tbody>
</table>


</fieldset>

<div id="required_fields_message" class="obligatoire">
  <a class="btobligatoire" id="" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
  <?php echo $this->lang->line('common_fields_required_message'); ?>
</div>


    </div>












</div>

<div class="txt_milieu">

                <?php
                $target	=	'target="_self"';
                echo anchor			(
                    'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                    $target
                );
                ?>

            <?php
            echo form_submit					(	array	(
              'name'	=>	'submit',
              'id'	=>	'submit',
              'value'	=>	$this->lang->line('common_submit'),
              'class'	=>	'btsubmit'
            )
            );
            ?>

                <?php
                echo form_close();
                ?>




                <?php
                echo form_close();
                }
                ?>





                <?php
                }
                ?>


</div>


</div>
</div>
</div>
</div>
