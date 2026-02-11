<nav class="wlp-bighorn-menu wlp-bighorn-menu-multi" role="navigation">

    <div class="wlp-bighorn-menu-menu-panel" >
        <ul style="margin-top: 2%;">


            <!--      <?php /*
        foreach($_SESSION['G']->login_employee_allowed_modules as $module)
        {
        if ($module->show_in_header == 'Y')
        {
        // initialise
        $url			=	'';
        $target			=	'';

        /*	switch ($module->module_name)
        {
        case 'sonrisa':
        $url 	=	prep_url($this->config->item('website'));
        $target	=	'target="_blank"';
        break;

        case 'drive':
        $url 	=	prep_url('drive.google.com/drive/#my-drive');
        $target	=	'target="_blank"';
        break;

        case 'print':
        $url	=	'';
        $target	=	'onClick="window.print()"';
        break;

        case 'catalogue':
        $url	=	prep_url('https://drive.google.com/open?id=0B2l4-1fUyaoKMmpiSHV2R2ota2s');
        $target	=	'target="_blank"';
        break;

        case 'copyight':
        $url	=	'';
        $target	=	'onClick="window.print()"';
        break;

        case 'security':
        $url	=	prep_url('drive.google.com/folderview?id=0B1DlcJN8YXtnZW9LUUdNaHoxdFU&usp=sharing');
        $target	=	'target="_blank"';
        break;

        default:
        $url 	=	$module->module_name;
        $target	=	'target="_self"';
        break;
      }

      // create image array
      /*$img	=	array	(
      'src'	=>	base_url().'images/menubar/'.$module->module_name.'.png',
      'border'=>	'0',
      'alt'	=>	$module->module_name
    );
    // create the anchor

    echo	"<li>"
    .anchor ($url, $this->lang->line($module->name_lang_key), $target)
    ."</li>";
  }
}*/
            ?>-->
            <li class="" style="width:10%">
                <a href="<?php echo site_url('reports/inventory_rolling');?>"><?php echo $this->lang->line('modules_home');?></a>

            </li>
            <li class="" style="width:10%" >
                <a>Produits</a>

                <?php /*echo anchor('home/user',Produits);*/?>
                <ul style="text-align: left">
                    <li class=""><a href="<?php echo site_url("categories");?>"><?php echo $this->lang->line('modules_categories');?></a>
                    </li>
                    <li class=""><a href="<?php echo site_url("items");?>"><?php echo $this->lang->line('modules_items');?></a>
                    </li>
                    <li class=""><a href="<?php echo site_url("item_kits");?>"><?php echo $this->lang->line('modules_item_kits');?></a>
                    </li>
                    <!-- <li class=""><a href="<?php echo site_url("warehouses");?>"><?php echo $this->lang->line('modules_warehouses');?></a>
                    </li> -->
                    <li class=""><a href="<?php echo site_url("suppliers");?>"><?php echo $this->lang->line('modules_suppliers');?></a>

                    </li>
                </ul>
            </li>

            <li class="" style="width:10%"><a href="<?php echo site_url("customers");?>"><?php echo $this->lang->line('modules_customers');?></a>
            </li>


            <li class="" style="width:10%"><a>Boutique</a>
                <ul style="text-align: left;">
                    <li class=""><a href="<?php echo site_url("cashtills");?>"><?php echo $this->lang->line('modules_cashtills');?></a></li>
                    <li class=""><a href="<?php echo site_url("sales");?>"><?php echo $this->lang->line('modules_sales');?></a>
                    </li>
                    <li class=""><a href="<?php echo site_url("receivings");?>"><?php echo $this->lang->line('modules_receivings');?></a>
                    </li>
                    <li><a href="<?php echo site_url("targets");?>"><?php echo $this->lang->line('modules_targets');?> </a></li>
                </ul>
            </li>

            <?php if(isset($_SESSION['G']->login_employee_id)){
                                            if ($_SESSION['G']->login_employee_info->admin == 1){

                                            ?>
            <li class="" style="width:10%"><a href="<?php echo site_url("employees");?>"><?php echo $this->lang->line('modules_employees');?></a>

            </li>
            <?php
                                            }

            }?>
            <li class="" style="width:10%"><a href="<?php echo site_url("reports");?>"><?php echo $this->lang->line('modules_reports');?></a>
            </li>

            <li class="" style="width:10%"><a target="_blank"  href="https://docs.google.com/spreadsheets/d/1KxVo0t5rct8eRoH-yu56IqVGBK_WAES8IZy5iWLjYG4/edit#gid=0" ><?php echo $this->lang->line('modules_carteKDO');?></a></li><!--<?php /*echo site_url("catalogue");*/?>-->


        </ul>
    </div>

</nav>
