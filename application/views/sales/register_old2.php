<!--
register.php view controls the display of the sales register

controller is sales.php
css can be found in register.css and general.css
-->


<!-- this style info is for the sales target area -->
<?php
	// output header
	$this->load->view("partial/header");

	// get the number format -->
	$pieces =array();
	$pieces = explode("/", $this->config->item('numberformat'));


	if($_POST['item'] != NULL)
	{
		if($_POST['input_qty'] == NULL)
		{
			$_POST['input_qty'] = 1;
		}
		$input = $_POST['item'];
		foreach($_SESSION['CSI']['CT'] as $key => $item)
		{
			if($item->line_quantity > 0 && $item->item_id == $input && end($_SESSION['CSI']['CT']) != $item && $item->DynamicKit != 'Y' && $item->line_discount == 0)
			{
				$item->line_quantity += $_POST['input_qty'];
				$item->line_valueAD_TTC = $item->line_quantity * $item->line_priceTTC;
				$item->line_valueAD_HT = $item->line_quantity * $item->line_priceHT;
				$item->line_taxAD = $item->line_quantity * $item->line_taxAD;

				foreach($_SESSION['CSI']['CT'] as $key2 => $item2)
				{
					if($item2->line_quantity == $_POST['input_qty'] && $item2->item_id == $input)
					{
						unset($_SESSION['CSI']['CT'][$key2]);
					}
				}
				break ;
			}
		}	
	}

?>

<div class="body_cadre_gris">

    <!--------------------------->
    <!--- COLONNE GAUCHE----------------------->
    <!--  ------------------------------------------>

    <div class="body_colonneG">
<!-- set up the sales target area -->
<?php if($_SESSION['hidden'] != 1){ ?>
<div id="sales_targets" class="targets_sale" style=" float:right;">

    <h2 id="targets_sale_title" style="text-align: center; color:#000000;" ><?php echo $this->lang->line('sales_sales_targets')?></h2>

    <table width= "100%">
        <tr style="    border-bottom: 0.1px solid;
        "> <th class="cell" style="text-align: center">	<?php
                $month = date('F');
                echo $this->lang->line('cal_'.$month);
                ?>
            </th>

            <th class="cell" style="text-align: center"><?php echo $this->lang->line('sales_monthly')?></th>
            <th class="cell" style="text-align: center"><?php echo $this->lang->line('sales_daily')?></th>
        </tr>


        <tr><td class="cell" style="text-align: left" ><?php echo $this->lang->line('sales_target')?></td>
            <td class="cell" style="text-align: center"><?php echo number_format($_SESSION['CSI']['TT']->monthlytarget, 0, $pieces[1], $pieces[2]) ?></td>
            <td class="cell" style="text-align: center"><?php echo number_format($_SESSION['CSI']['TT']->dailytarget, 0, $pieces[1], $pieces[2]) ?></td>

        </tr>
        <tr><td class="cell" style="text-align: left"><?php echo $this->lang->line('sales_realised')?></td>
            <td class="cell" style="text-align: center"><?php echo number_format($_SESSION['CSI']['TT']->monthlyrealised, 0, $pieces[1], $pieces[2]).' / '.number_format($_SESSION['CSI']['TT']->monthlyrealisedpercent, 0, $pieces[1], $pieces[2]).'%' ?> </td>

            <td class="cell" style="text-align: center"><?php echo number_format($_SESSION['CSI']['TT']->dailyrealised, 0, $pieces[1], $pieces[2]) ?></td>
        </tr>

        <tr><td class="cell" style="text-align: left"><?php echo $this->lang->line('sales_todo')?></td>
            <td class="cell" style="text-align: center"><?php echo number_format($_SESSION['CSI']['TT']->monthlytodo, 0, $pieces[1], $pieces[2]) ?></td>
            <td class="cell" style="text-align: center ;font-size: 20px;">
                <?php
                if ($_SESSION['CSI']['TT']->dailytodo > $_SESSION['CSI']['TT']->dailytarget)
                {
                    ?>
                    <FONT size="3" COLOR="#DA162B" STYLE="font-weight: bold;"><?php echo number_format($_SESSION['CSI']['TT']->dailydone, 0, $pieces[1], $pieces[2]).' / '.number_format($_SESSION['CSI']['TT']->dailytodo, 0, $pieces[1], $pieces[2]); ?></FONT>
                    <?php
                }
                else
                {
                    ?>
                    <FONT size="3" COLOR="#08602E" STYLE="font-weight: bold;"><?php echo $_SESSION['CSI']['TT']->dailydone.' / '.$_SESSION['CSI']['TT']->dailytodo ?></FONT>
                    <?php
                }
                ?>
            </td>
        </tr>
    </table>
</div>
<?php } ?>
        <!------------------------------>
        <!-- ---------------------->
        <!--  -------------------------->

<?php
	// set up the mode lang line, long and short form text
	$lang_line = 'reports_'.$_SESSION['CSI']['SHV']->mode;
	$mode_long_text = $this->lang->line($lang_line);
	$this->load->helper('text');
	$mode_short_text = ellipsize($mode_long_text, 10, .5);
?>


  <div >

<!-- output page title -->
<?php
if ($_SESSION['cashtill_not_open'] == 1 OR $_SESSION['cashtill_closed'] == 1)
{
	if ($_SESSION['cashtill_not_open'] == 1)
	{
?>
		<div class="title_sale" style="text-align: center;" ><?php echo  $this->lang->line('cashtills_not_open'); ?>
<?php
	}
	else
	{
		if ($_SESSION['cashtill_closed'] == 1)
		{
?>
			<div class="title_sale" style="text-align: center;" ><?php echo $this->lang->line('cashtills_closed'); ?>
<?php
		}
	}
}
else
{
        if ($on_stop_indicator != 'Y')
        {
        // test for telephone number
        if (empty($phone_number) AND !empty($customer))
        {
        ?>
        <div class="title_sale"><?php echo $customer . ' <br/> ' . $this->lang->line('customers_enter_phone_number'); ?>
            <?php
            }
            else
            {
            ?>
            <div class="title_sale"><?php echo $customer; ?>
                <?php
                }
                ?>
                <?php
                }
        else
        {
        ?>
        <div class="title_sale"><?php echo $customer . '  ' . $on_stop_amount . '€  ' . $on_stop_reason; ?>
            <?php
            }
?>
<!--<div class="title_sale_green"><!--  -->
        <center>
		   <?php echo $this->lang->line('cashtills_open'); ?>
		</center>
	<!--	</div><!--  -->
		<?php
}
	?>

</div> <!-- div id title-->
  <!--  </div></div></div></div>-->
</div>


<!--------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------------------->


<!-- output register wrapper -->


<!--------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------------------->

<div id="sales_register_wrapper">

	<!-- output error messages -->
	<?php
	if(isset($error))
	{
		echo "<div class='message_erreur' style= 'width:68%' >".$error."</div>";
	}

	if (isset($warning))
	{
		echo "<div class='warning_message' style= 'width:68%' >".$warning."</div>";
	}

	if (isset($success))
	{
		echo "<div class='success_message' style= 'width:68%' >".$success."</div>";
	}

	if (!isset($_SESSION['show_dialog']))
	{

	    echo '<div style="width: 68%;">';
		include('../wrightetmathon/application/views/partial/show_messages.php');
		echo '</div>';
	}
	?>

<!--------------------------------------------------------------------------------------------------------->
<!-- Section 1 - Manage customer																		 -->
<!--------------------------------------------------------------------------------------------------------->
<span>
  <div class="submenu">
	<div class="btnmodif" style="    margin-top: 40px;">
	<?php
	// set origin for return to sales screen after client creation or update
	$origin = 'SS';

	if(isset($_SESSION['CSI']['SHV']->customer_id))
	{
		?><table><tr><td><table>
			
		<?php
		$this							->	db->select('comments');
		$this							->	db->from('people');
		$this							->	db->where('person_id = "'.$_SESSION['CSI']['SHV']->customer_id.'"');
		$report_data					= 	$this->db->get()->result_array();

		$comment = $report_data[0]["comments"];
		if($comment != "0" && $comment != NULL)
		{
			?>
			<td>
			<img src="<?php echo $_SESSION['url_image'];?>/info.png" title="<?php echo $comment?>" height="20px">
			</td>
			<?php
		}
	// if customer is set show details

        if(strlen($_SESSION['CSI']['SHV']->dob_month) == 1)
        {
        	$_SESSION['CSI']['SHV']->dob_month = '0' . $_SESSION['CSI']['SHV']->dob_month;
        }
        
        if(strlen($_SESSION['CSI']['SHV']->dob_day) == 1)
        {
        	$_SESSION['CSI']['SHV']->dob_day = '0' . $_SESSION['CSI']['SHV']->dob_day;
        }

        //date de naissance du client YYYY-mm-dd
        $dob_customer['0'] = strval($_SESSION['CSI']['SHV']->dob_year . '-' . $_SESSION['CSI']['SHV']->dob_month . '-' . $_SESSION['CSI']['SHV']->dob_day);
        $date['plus_1']['0'] = date("Y-m-d", strtotime("+1 day"));
        $date['plus_2']['0'] = date("Y-m-d", strtotime("+2 day"));
        $date['moins_1']['0'] = date("Y-m-d", strtotime("-1 day"));
        $date['moins_2']['0'] = date("Y-m-d", strtotime("-2 day"));
        $date['new']['0'] = date("Y-m-d");
        list($dob_customer['Y'], $dob_customer['m'], $dob_customer['d']) = explode('-', $dob_customer['0']);

        //vérification que la date de naissance ne soit pas la date par défaut
        if($dob_customer['0'] != "1970-01-01")
        {
            foreach($date as $key => $date_)
            {
        		list($date_['Y'], $date_['m'], $date_['d']) = explode('-', $date_['0']);
        		if(
        			($date_['m']==$dob_customer['m'] && $date_['d']==$dob_customer['d'])
        		)
        		{
        			$gateau=1;
        		}
            }
        }
			if($gateau == 1)
			{
				$gateau = 0;

				?>
				<td>
				<img src="<?php echo $_SESSION['url_image'];?>/anniv.png" title="Joyeux Anniversaire le <?php echo $_SESSION['CSI']['SHV']->dob_day . '/' . $_SESSION['CSI']['SHV']->dob_month;?>" height="30px">
				</td>
				<?php
			}
			?>

            <td>
			<?php
		// Show customer and allow change
		echo '<span id="client"  >'.anchor	(
							'customers/view/'.$_SESSION['CSI']['SHV']->customer_id.'/'.$origin,
							$_SESSION['CSI']['SHV']->customer_formatted,'class="c_couleur"'
							)
							.'</span>';

			  // show remove client button
		
		//Attention, (!isset(mail)||!isset(tel)) && fidelity_point==OUI
		//(email LIKE "%@%") || (phone_number != "") (people)
		//fidelity_flag=='Y' (customers)
		?>
		</td><td >
		<?php
		switch($_SESSION['customer_info_not_complete'])
		{
			case 1:
		?>
		<img src="<?php echo $_SESSION['url_image']?>/attention_rouge_client.png" title="Attention Fiche cliente imcompléte" height="25px;">
		</td><td> <?php
			break;
	    }
		echo '  ';
        echo anchor("sales/customer_remove",$this->lang->line('common_remove'),'class="colorwhite"');
?>
</td>
</table></td>
    <td width="10%" align="right">
	<table style="margin-left:0%; margin-right:0%;" >
		<?php
if($this->config->item('multi_vendeur') == 'Y')
{
		//Si le bouton n'est pas encore affilié à un vendeur alors il est possible de se connecter
		if($_SESSION['last_click'] == 1)
		{
			?>
            <td>
            <a href="<?php echo site_url() . '/sales/load_data_set_vendeur/1' ?>" style="color:black;background:white;" >
		    <?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['1']; ?></a>
		    </td>
            <?php
		}
		elseif($_SESSION['numero_button_vendeur']['person_id_vendeur']['1'] != '')
		{                                     //Bouton Vendeur1
            ?>
            <td>
            <a href="<?php echo site_url() . '/sales/load_data_set_vendeur/1' ?>" style="color:black;background:#B0B4A8;" >
		    <?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['1']; ?></a>
		    </td>
            <?php
		}
		else    //Sinon, le bouton est déjà affilié à un vendeur alors les données du vendeur sont chargés pour la vente
		{
			?>
            <td>
            <a href="<?php echo site_url() . '/sales/set_vendeur/1' ?>" class="btnew c_btcouleur" >
		    <?php echo 'Vendeur1'; ?></a>
		    </td>
			<?php
		}
        		
        //Si le bouton n'est pas encore affilié à un vendeur alors il est possible de se connecter
		if($_SESSION['last_click'] == 2)
		{
			?>
            <td>
            <a href="<?php echo site_url() . '/sales/load_data_set_vendeur/2' ?>" style="color:black;background:white;" >
		    <?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['2']; ?></a>
		    </td>
            <?php
		}
		elseif(($_SESSION['numero_button_vendeur']['person_id_vendeur']['2'] != ''))
        {                                     //Bouton Vendeur2
        	?>
        	<td>
        	<a href="<?php echo site_url() . '/sales/load_data_set_vendeur/2' ?>" style="color:black;background:#B0B4A8;" >
        	<?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['2']; ?></a>
        	</td>
        	<?php
        }
        else    //Sinon, le bouton est déjà affilié à un vendeur alors les données du vendeur sont chargés pour la vente
        {
        	?>
        	<td>
        	<a href="<?php echo site_url() . '/sales/set_vendeur/2' ?>" class="btnew c_btcouleur" >
        	<?php echo 'Vendeur2'; ?></a>
        	</td>
        	<?php
		}
		
        //Si le bouton n'est pas encore affilié à un vendeur alors il est possible de se connecter
		if($_SESSION['last_click'] == 3)
		{
			?>
            <td>
            <a href="<?php echo site_url() . '/sales/load_data_set_vendeur/3' ?>" style="color:black;background:white;" >
		    <?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['3']; ?></a>
		    </td>
            <?php
		}
		elseif(($_SESSION['numero_button_vendeur']['person_id_vendeur']['3'] != ''))
        {                                     //Bouton Vendeur3
        	?>
        	<td>
        	<a href="<?php echo site_url() . '/sales/load_data_set_vendeur/3' ?>" style="color:black;background:#B0B4A8;" ><!-- class="btsubmit" > <!--  -->
        	<?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['3']; ?></a>
        	</td>
        	<?php
        }
        else    //Sinon, le bouton est déjà affilié à un vendeur alors les données du vendeur sont chargés pour la vente
        {
        	?>
        	<td>
        	<a href="<?php echo site_url() . '/sales/set_vendeur/3' ?>" class="btnew c_btcouleur" >
        	<?php echo 'Vendeur3'; ?></a>
        	</td>
        	<?php
        }
        //Si le bouton n'est pas encore affilié à un vendeur alors il est possible de se connecter
		if($_SESSION['last_click'] == 4)
		{
			?>
            <td>
            <a href="<?php echo site_url() . '/sales/load_data_set_vendeur/4' ?>" style="color:black;background:white;" >
		    <?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['4']; ?></a>
		    </td>
            <?php
		}
		elseif(($_SESSION['numero_button_vendeur']['person_id_vendeur']['4'] != ''))
        {                                     //Bouton Vendeur4
        	?>
        	<td>
        	<a href="<?php echo site_url() . '/sales/load_data_set_vendeur/4' ?>" style="color:black;background:#B0B4A8;" >
        	<?php echo $_SESSION['numero_button_vendeur']['person_id_vendeur']['4']; ?></a>
        	</td>
        	<?php
        }
        else    //Sinon, le bouton est déjà affilié à un vendeur alors les données du vendeur sont chargés pour la vente
        {
        	?>
        	<td>
        	<a href="<?php echo site_url() . '/sales/set_vendeur/4' ?>" class="btnew c_btcouleur" >
        	<?php echo 'Vendeur4'; ?></a>
        	</td>
        	<?php
        }
}       
		?>
    </table>
    </td>
    </tr>
</table>
<?php
echo '';
       /////////avoirrrrrrr

          		echo '<div class="info_client">';
		// if customer has comments, show them via a pop-up but only if allowed to show comments
		if (!empty($_SESSION['current_sale_info']['customer_info']->customer_comments) AND $this->config->item('person_show_comments') == 'Y')
		{
			$_SESSION['CSI']['CI']->customer_comments			=	str_replace(array("\r", "\n"), " ", $_SESSION['CSI']['CI']->customer_comments);
			$customer					=	str_replace(array("\r", "\n"), " ", $_SESSION['CSI']['SHV']->customer_formatted);
			$customer_comments_parm		=	str_replace(' ', '_', $_SESSION['CSI']['CI']->customer_comments);
			$customer_parm				=	str_replace(' ', '_', $customer);
			$parm						=	json_encode($customer_parm.'/'.$customer_comments_parm);
			$popup_command				=	'<a href="#" onMouseOver = popUp1('.$parm.'); onmouseout="Win1.close()">'.$this->lang->line('customers_comments').'</a>';
			echo '<b>'.$popup_command.'</b>';
		}

		// show the average basket
		echo '<b class="c_couleur"> '.$this->lang->line('sales_average_basket').' : </b> '.$_SESSION['CSI']['CI']->sales_ht.' / '.$_SESSION['CSI']['CI']->sales_number_of.' = '.$_SESSION['CSI']['SHV']->client_average_basket;

		// show pricelist attached to this client
		echo '<b class="c_couleur"> '.$this->lang->line('pricelists_pricelist').$this->lang->line('common_space').' </b>'.$this->lang->line('common_equal').$this->lang->line('common_space').$_SESSION['CSI']['PI']->pricelist_name;

		// show fidelity if fidelity applied to this client
		if ($_SESSION['CSI']['CI']->fidelity_flag == 'Y')
		{
			echo ' <b class="c_couleur"> '.$this->lang->line('customers_fidelity').' : </b>'
							.$this->lang->line('common_space')
							.$this->lang->line('common_space')
							.$_SESSION['CSI']['CI']->fidelity_points
							.$this->lang->line('common_space')
							.$this->lang->line('customers_points')
							.$this->lang->line('common_space')
							.$this->lang->line('common_equal')
							.$this->lang->line('common_space')
							.$_SESSION['CSI']['SHV']->fidelity_value
							.$_SESSION['CSI']['CuI']->currency_sign
							.$this->lang->line('common_space')
							.$this->lang->line('sales_TTC');
		}
echo '<br/>';
		// show profile if not default
		if ($_SESSION['CSI']['SHV']->default_profile_flag == 0)
		{
			echo ' <b class="c_couleur">'	.$this->lang->line('customer_profiles_customer_profiles').': </b>'
							.$this->lang->line('common_space')

							.$this->lang->line('common_space').
							$_SESSION['CSI']['CPI']->profile_name
							.$this->lang->line('common_space')
							.' <b class="c_couleur">'.$this->lang->line('customer_profiles_profile_discount').' </b>'
							.$this->lang->line('common_space')
							.$this->lang->line('common_equal')
							.$this->lang->line('common_space')
							.$_SESSION['CSI']['CPI']->profile_discount
							.$this->lang->line('common_percent');
		}

        echo '</div>';
	}

	// otherwise show customer entry box
	else
	{
		echo form_open("sales/customer_select/".'SC', array('id'=>'select_customer_form')); ?>
		<?php echo form_input	(	array	(
											'name'			=>	'customer',
											'id'			=>	'customer',
											'size'			=>	'25',
                                          'style'			=>	'text-align:left;width:321px;',
                                            'class'         =>  'champ_search',
											'placeholder'	=>	$this->lang->line('sales_start_typing_customer_name')
											)
								);
		?>
    <div class="btncaisse" style="display: inline-block">
      <img  src="<?php echo $_SESSION['url_image'];?>/search_client.png" class="img_search"    style=" margin-bottom: -16.25px;
    height: 35px;
    margin-left: -43px;
   "/>
    </div>

		<!-- show new customer data entry-->
		<?php
		echo '  ';
		echo anchor		(
						'customers/view/-1/'.$origin,
						''.$this->lang->line('sales_new_customer').'','class="colorwhite"'
						);
		?>
		</form>

		<?php
	}
	?>

  </div>
  
  </div></span>

<!--------------------------------------------------------------------------------------------------------->
<!-- Show item entry																					 -->
<!--------------------------------------------------------------------------------------------------------->

	<!-- show item entry form -->
  <span>
    <div class="submenu">
      <div class="btnmodif">

			<?php
				echo form_open("sales/add",array('id'=>'add_item_form'));
					echo form_input		(array	(
												'name'			=>	'input_qty',
												'id'			=>	'input_qty',
												'size'			=>	'5',
												'style'			=>	'text-align:left; width: 95px; margin-right:10px;',
                                                'class'         =>  'champ_search',
												'placeholder'	=>	$this->lang->line('sales_quantity'),
												'value'			=>  1
												)
										);
					echo form_input		(array	(
												'name'			=>	'item',
												'id'			=>	'item',
												'size'			=>	'30',
												'style'			=>	'text-align:left; width: 395px;',
                                                'class'         =>  'champ_search',
												'autofocus'		=>	'autofocus',
												'methode'		=> 	'POST',
												'placeholder'	=>	$this->lang->line('sales_start_typing_item_name')
												)
										);

					// suspended sales button
					echo '  ';
					echo anchor			(
										"sales/suspended/",
										$this->lang->line('sales_suspended_sales'),'class="colorwhite"'
										);
                    // show credit note (CN) button
                    echo ' ';
                    echo anchor("sales/CN_select_invoice",$this->lang->line('sales_credit_note'),'class="colorwhite"');

					// show cancel sale button if cart has at least one item in it
					if(count($_SESSION['CSI']['CT']) > 0)
					{
						echo '  ';
						echo anchor("sales/cancel_sale",
						$this->lang->line('recvs_cancel').' '.$mode_short_text,'class="colorwhite"'
						);
					}
				echo form_close();
				
			?>
		       <a href="<?php echo site_url("sales/icone_tiroir/$tout");/* $tout  $cur_item_info->item_id:$line  %$line   $cur_item_info->item_id?$line    item_id=$cur_item_info->item_id&line=$line*/ ?>"><img src="<?php echo base_url().$_SESSION['url_image'].'/tiroir.png';?>" width="33px" height="19px" alt="icone_tiroir" ></a>
				
			</div></div></span>
			
	<!-- set up text for item table headers -->
  <div style=" overflow-y:scroll; overflow-x:hidden;margin-top: 0px; font-size:14px;max-height:450px;
    min-height: 350px;border:#f5f5f5 1px solid;">
	
    <table id="sales_register" class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th style="text-align:center;">	</th>
                    <th style="text-align:center;">	<?php echo $this->lang->line('items_item_number'); ?></th>
                    <th style="text-align:center;">	<?php echo $this->lang->line('sales_item_name'); ?></th>
                    <th style="text-align:center;">	<?php echo $this->lang->line('items_category'); ?></th>
					<th style="text-align:center;">	<?php echo $this->lang->line('items_DynamicKit'); ?></th>


                    <th style="text-align:center;">	<?php echo $this->lang->line('sales_in_stock')?></th>
					<th style="text-align:center;">	<?php echo $this->lang->line('sales_price').$this->lang->line('sales_TTC'); ?></th>
					<th style="text-align:center;">    <?php echo $this->lang->line('sales_quantity'); ?></th>
					<th style="text-align:center;">    <?php echo $this->lang->line('sales_discount').$this->lang->line('common_percent'); ?></th>
					<th style="text-align:center;">	<?php echo $this->lang->line('sales_line_offered').$this->lang->line('common_question'); ?></th>
					<th style="text-align:center;">	<?php echo $this->lang->line('sales_total').$this->lang->line('sales_TTC');	?></th>
					<th style="text-align:center;"><?php echo $this->lang->line('sales_edit'); ?></th>

				</tr>
			</thead>

			<!-- fill table row -->
			<tbody id="sales_cart_contents">

				<!-- if cart empty show empty cart message -->
					<?php
					if(count($_SESSION['CSI']['CT']) == 0)
					{
						?>
						<tr>
                            <td colspan='12' style='padding:0px;'> <?php echo $this->lang->line('sales_no_items_in_cart'); ?></td>
						</tr>
						<?php
					}
					else
					{
						// set first record indicator and initial colour
						$current_kit_option			=	'';
						$colour						=	'';

						// get each cart entry
						foreach($_SESSION['CSI']['CT'] as $line => $cart_line)
						{
							// allow edits to line, so open the form
							echo form_open("sales/edit_item/$line");

							// Test for change of kit option
							if ($current_kit_option !=	$cart_line->kit_option)
							{
								$current_kit_option						=	$cart_line->kit_option;
								switch ($colour)
								{
									case 'Pink':
										$colour							=	'PowderBlue';
										break;
									case 'PowderBlue':
										$colour							=	'Pink';
										break;
									default:
										$colour							=	'Pink';
										break;
								}
								?>
								<tr style="height:1px">
									<td colspan=11 style="background-color:#106587"> </td>
								</tr>
								<?php
							}

							//	test for dynamic kit
							if ($cart_line->DynamicKit == 'Y')
								{
									$DynamicKit_settext 				= $this->lang->line('common_yes');
								}
							else
								{
									$DynamicKit_settext 				= $this->lang->line('common_no');
								}

							// test for kit item
							if ($cart_line->kit_item == 'N')
							{
								// this is a normal line
								?>
								<!-- output separator line -->
							<!--	<tr style="height:1px">
									<td colspan=11 style="background-color:#106587"> </td>
								</tr>-->

								<!-- output table row -->
								<?php
								if ($cart_line->last_line)
								{ /* background-color:<?php echo $cart_line->colour ?>; */
									?>
								<!--	<tr style="background-color:<?php /*echo $cart_line->colour*/ ?>;font-weight:bold;text-align:center">-->

                                    <tr id="line_couleur" style="text-align:center">
									<?php
								}
								else
								{
									?>
									<tr style="background-color:#EBF4F8;text-align:center">
									<?php
								}
								?>

									<!-- Output table data -->
									<!--<td style="text-align:left;"><?php /*echo anchor("sales/delete_item/$line",'['.$this->lang->line('common_delete_short').']');*/?></td>-->
                  <td style="text-align:center;" title="<?php echo $this->lang->line('common_delete'); ?>"> <a
                    href="<?php echo site_url("sales/delete_item/$line");?>">
                    <img src="<?php echo base_url().'images2/del.png';?>" width="25px" height="25px" alt="Suppression"></a></td>


                                <td title="<?php echo $this->lang->line('sales_item_det'); ?>"><?php /*echo $cart_line->item_number; */ ?><?php echo anchor(
                                        'items/view/'.$cart_line->item_id.'/'.$origin,$cart_line->item_number

                                    ); ?></td>

                                <td style="text-align:left;" title="<?php echo $this->lang->line('sales_remote_stock'); ?>"><?php
								 //   echo anchor('items/remote_stock/'.$cart_line->item_id.'/'.$origin, $cart_line->name,'class="sablier"');
								 echo $cart_line->name;
								 ?>
                                      
                                    <?php	/*if ($_SESSION['CSI']['CT'][$line]->CN_line == 'Y')
                                    {
                                        echo $cart_line->description;
                                    }
                                    else
                                    {
                                        //echo '['.$cart_line->quantity.' '.$this->lang->line('sales_in_stock').']';
                                    } */
                                    ?>


                                </td>
                                <td ><?php echo $cart_line->category; ?></td>
									<td style=""><?php echo $DynamicKit_settext ?></td>



                                <td><?php echo $cart_line->quantity;?></td>
									<td style="text-align:right;"><?php echo number_format($cart_line->line_priceTTC, 2); ?></td>
									<td style="text-align:right;" class="zone_champ_saisie_1" ><?php	
									//if ($cart_line->DynamicKit == 1 OR $cart_line->line_offered == 'Y')
									//									{
									//										echo number_format($cart_line->line_quantity, 2);
									//									}
									//									else
									//									{
											echo form_input	(array	(
												'type'=>'number', 
												'name'=>'line_quantity',
												'value'=>round(number_format($cart_line->line_quantity, 2),0),
												'style'=>'text-align:right',
												'size'=>'4'
												)
											);

																							
									//									}
										?>
									</td>
									
									<!-- allow discount entry only if it hasn't been entered already and this is not a CN-->
									<td style="text-align:right" class="zone_champ_saisie">
										<?php
											if ($cart_line->line_discount == 0 AND $_SESSION['CSI']['CT'][$line]->CN_line != 'Y')
											{
												echo form_input	(array	(
																		'name'=>'line_discount',
																		'value'=>$cart_line->line_discount,
																		'style'=>'text-align:right',
																		'size'=>'6'
																		)
																);
											}
											else
											{
												echo $cart_line->line_discount;
											}
										?>
									</td>
									

									<!-- show line offered -->
									<td align="center" class="zone_champ_saisie">
										<?php
											if ($_SESSION['CSI']['CT'][$line]->CN_line == 'Y')
											{
												echo $this->lang->line('sales_credit_note');
											}
											else
											{
												if ($cart_line->line_offered == 'N')
												{
												echo form_dropdown		(
																		'line_offered',
																		$_SESSION['G']->YorN_pick_list,
																		$cart_line->line_offered,
																		'style=""'
																		);
												}
												else
												{
												echo $this->lang->line('common_yes');
												}
											}
										?>		
									</td>
									<td style="text-align:right"><?php echo number_format($cart_line->line_valueAD_TTC, 2); ?></td>
									<td><?php /*echo form_submit("edit_item", $this->lang->line('sales_edit_item'));*/?>
                                    <input name="edit_item" type="image" src="<?php echo base_url().$_SESSION['url_image'].'/maj.png';?>"  width="30px" height="30px"/>
                  </td>
								</tr>

								<?php
							// end of normal item == N
							}
							else // so this is a kit item line
							{
								if ($cart_line->last_line)
								{
									?>
									<tr style="background-color:<?php echo $cart_line->colour ?>;font-weight:bold;font-size:15px;text-align:center">
									<?php
								}
								else
								{
									?>
									<tr style="background-color:<?php echo $colour; ?>;font-weight:bold;font-size:15px;text-align:center">
									<?php
								}
								?>
									<td> <?php echo ' ';?></td>										<!-- No delete button -->
									<td> <?php echo ' ' ?></td>										<!-- No Dynamic Kit indicator -->
									<td> <?php echo $cart_line->kit_option; ?></td>					<!-- kit option in category column -->
									<td> <?php echo $cart_line->item_number; ?></td>				<!-- item number -->
									<td> <?php echo $cart_line->name;?>		</td>					<!-- item name -->
									<td><?php echo ''.$cart_line->quantity; ?></td>
									<td> <?php echo ' ';?></td>										<!-- No price -->
									<?php
									if ($cart_line->kit_option_type == 'F')
									{
										?>
										<td style="text-align:right"><?php	echo number_format($cart_line->line_quantity);?></td> <!-- item qty -->
										<?php
									}
									else
									{
										?>
										<td style="text-align:right" class="zone_champ_saisie"><?php	/*echo form_input	(array	(
																									'name'=>'line_quantity',
																									'value'=>number_format($cart_line->line_quantity, 2),
																									'style'=>'text-align:right',
																									'size'=>'4'
																									)//*/
																								echo round(number_format($cart_line->line_quantity, 2),0)							
																							;      // user must enter qty for multi choice option
									}
									?>
									<td> <?php	echo ' ';?></td>									<!-- No discount -->
									<td> <?php	echo ' ';?></td>									<!-- No offered flag -->
									<td> <?php	echo ' ';?></td>									<!-- No line total -->
									<?php
									if ($cart_line->kit_option_type == 'F')
									{
										?>
										<td><?php echo ' ';?></td>									<!-- No update item box if 'F' -->
										<?php
									}
									else
									{
										?>
										<td><?php /*echo form_submit("edit_item", $this->lang->line('sales_edit_item'));*/?><!--
    <input name="edit_item" type="image" src="<?php echo base_url().'images2/maj.png';?>"  width="25px" height="25px"/><!--  -->

                    </td>

                                        <!-- update item box if 'O' -->
										<?php
									}
									?>
								</tr>

								<?php
							// end of kit item == N
							}
							?>
						</form>
						<?php
						// end of foreach loop
					}
				// end of if data in cart or not
				}
				?>
			</tbody>
		</table>
  </div>

  </div>

            </div>

  <!---------------------------->
  <!-- COLONNE DROITE------------------------>
  <!------------------------------------------>
  <div class="body_colonneD">

<!--------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------------------->
<!-- Overall sale																						 -->
<!--------------------------------------------------------------------------------------------------------->
<!--------------------------------------------------------------------------------------------------------->

<?php
if(count($_SESSION['CSI']['CT']) > 0)
{
?>
	<div id="overall_sale">


<!--------------------------------------------------------------------------------------------------------->
<!-- Section 2 - Total sales 																			 -->
<!--------------------------------------------------------------------------------------------------------->


		<div id='sale_details'>
			<fieldset style="border:1px solid #0A6184; background:  none; padding:7px; top:-10px; width:100%">
				<table width="100%" style="border-collapse: separate;    border-spacing: 4px; ">
					<tr>
						<td style="text-align:left;font-size:16px"><?php echo $this->lang->line('reports_subtotal'); ?></td>
						<td style="text-align:right;font-weight:bold;font-size:16px"><?php echo to_currency($_SESSION['CSI']['SHV']->header_valueAD_HT); ?></td>
					</tr>

					<tr>
						<td style='text-align:left;font-size:16px'><?php echo $_SESSION['CSI']['SHV']->tax_name.' '; ?></td>
						<td style="text-align:right;font-weight:bold;font-size:16px"><?php echo to_currency($_SESSION['CSI']['SHV']->header_taxAD); ?></td>
					</tr>

					<tr>
						<td style='text-align:left;font-size:16px;font-weight:bold;color:#100909'><?php echo $this->lang->line('reports_total_only').$this->lang->line('reports_TTC'); ?></td>
						<td style="text-align:right;font-size:18px;font-weight:bold;" class="bttotal"><?php echo to_currency($_SESSION['CSI']['SHV']->header_valueAD_TTC); ?></td>
					</tr>

					<?php
					if ($_SESSION['CSI']['SHV']->default_profile_flag == 0)
					{
						?>
							<tr>
								<!-- output customer profile discount % -->
								<td style='font-size:14px' ><?php echo $this->lang->line('customer_profiles_customer_profiles')
																						.$this->lang->line('common_space')
																						.$this->lang->line('common_equal')
																						.$this->lang->line('common_space')
																						.$_SESSION['CSI']['CPI']->profile_name;
																						?></td></tr>

                        <tr> <td  style='font-size:14px'><?php echo
                                $this->lang->line('customer_profiles_profile_discount')
                                .$this->lang->line('common_space')
                                .$this->lang->line('common_equal')
                                .$this->lang->line('common_space')
                                .$_SESSION['CSI']['CPI']->profile_discount
                                .$this->lang->line('common_percent'); ?></td></tr>

						<?php
					}
					else
					{
						if (isset($_SESSION['CSI']['SHV']->overall_discount))
						{
							?>
								<tr>
									<!-- output overall discount % -->
									<td style='text-align:left;font-size:14px' ><?php 	echo $this->lang->line('sales_global')
																						.$this->lang->line('common_space')
																						.$_SESSION['CSI']['SHV']->overall_discount
																						.$this->lang->line('common_percent');?></td>
								</tr>
							<?php
						}
						else
						{
							echo form_open("sales/overall_discount",array('id'=>'overall_discount_form'));
							?>
								<tr>
									<!-- output overall discount % -->
									<td style='text-align:left;font-size:16px' ><?php 	echo $this->lang->line('sales_discount').$this->lang->line('common_space').$this->lang->line('sales_discount_percentage');?></td>
									<td style='text-align:right;font-size:16px' class="zone_champ_saisie"  ><?php 	echo form_input	(array	(
																												'name'	=>	'overall_discount_percentage',
																												'id'	=>	'overall_discount_percentage',
																												'value'	=>	$_SESSION['CSI']['SHV']->overall_discount,
																												'style'	=>	'text-align:right;font-size:16px',
																												'size'	=>	'5',
                                                                                                                'class'=>'champ_saisie'
																												)
																										);
																				?></td>
								</tr>
							</form>
							<?php
						}
					}
					?>
				</table>


	</div>


        <div>
            <!--------------------------------------------------------------------------------------------------------->
            <!-- Section 4 - Amount due and amount tendered 				section 3 deleted						 -->
            <!--------------------------------------------------------------------------------------------------------->

            <!-- Output amount due -->
            <fieldset  style="border:1px solid #0A6184; background:  none; top:-15px; padding:7px; width:100%">

                <table width="100%">
                    <tr>
                        <?php
                        if (round($_SESSION['CSI']['SHV']->header_valueAD_TTC, 2) == round($_SESSION['CSI']['SHV']->header_payments_TTC, 2))
                        {
                            echo "<td style='text-align:left;font-size:16px;color:#27DA16'>".$this->lang->line('sales_amount_due').':'."</td>";
                            echo "<td style='text-align:right;font-weight:bold;font-size:16px;color:#27DA16'>".to_currency($_SESSION['CSI']['SHV']->header_amount_due_TTC)."</td>";
                        }
                        else
                        {
                            echo "<td style='text-align:left;font-size:16px;color:#DA162B'>".$this->lang->line('sales_amount_due').':'."</td>";
                            echo "<td style='text-align:right;font-weight:bold;font-size:16px;color:#DA162B'>".to_currency($_SESSION['CSI']['SHV']->header_amount_due_TTC)."</td>";
                        }
                        ?>
                    </tr>

                    <!-- Output amount tendered -->
                    <tr>
                        <td style="text-align:left;font-size:16px"><?php echo $this->lang->line('sales_amount_tendered').':' ?></td>
                        <td style="text-align:right;font-size:16px"><?php echo to_currency($_SESSION['CSI']['SHV']->header_payments_TTC); ?></td>
                    </tr>
                </table>

                <!--------------------------------------------------------------------------------------------------------->
                <!-- Section 5 - Payments and finish sale					 														 -->
                <!--------------------------------------------------------------------------------------------------------->



                <div id="finish_sale">

                    <?php
                    // only show finish button if no errors
                    if (!isset($_SESSION['CSI']['SHV']->cart_in_error))
                    {
                        // open the finish sale form
                        echo form_open($_SESSION['controller_name'].'/payments', array('id'=>'finish_sale_form'));
                        ?>


                        <?php echo form_submit(	array	(
                            'name'	=>	'submit',
                            'id'	=>	'submit',
                            'value'	=>	$this->lang->line('sales_add_payment'),
                            'class'	=>	'btsubmit'
                        )
                    );?>
                        <?php
                    }
                    ?>

                    <!-- Close the finish sale button -->
                    </form>

                    <?php
                    // open the suspend form
                    echo form_open($_SESSION['controller_name'].'/confirm/suspend');
                    ?>

                    <!-- Show suspend sale button -->

                    <?php echo form_submit(	array	(
                            'name'	=>	'submit',
                            'id'	=>	'submit',
                            'value'	=>	$this->lang->line('sales_suspend_sale').$mode_short_text,
                            'class'	=>	'btsubmit'
                        )
                    );?>
                    <!-- Close the finish sale button -->
                    </form>
                </div>
            </fieldset>
        </div>
    </div>
<?php
}
?>
      <div>


          <fieldset style="padding:0px;">

              <div id="slider" style="width:300px; height:300px;">
                  <figure>
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide1.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide2.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide3.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide4.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide5.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide6.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide7.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide8.png';?>">
                      <img src="<?php echo base_url().'SLIDES_VENTES/slide9.png';?>">

                  </figure>
              </div>

          </fieldset></div>

  </div>


<div>
	<!-- show the customer history -->

	<div id="table_holder">
		<table class="tablesorter report table table-striped table-bordered " id="sortable_table">

			<thead>
				<tr>
					<th>+</th>
					<?php foreach ($_SESSION['CSI']['HH']['summary'] as $header) { ?>
					<th><?php echo $header; ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<!-- -->
				<!-- get the number format -->
				<!-- -->
				<?php
				$pieces =array();
				$pieces = explode("/", $this->config->item('numberformat'));
				?>

				<?php foreach ($_SESSION['CSI']['HS'] as $key=>$row) { ?>
				<tr>
					<td><a href="#" class="expand">+</a></td>
					<?php foreach ($row as $cell)
					{
					?>
								<!-- -->
								<!-- Output each cell, format the number if numeric-->
								<!-- -->
									<?php
									if (is_numeric($cell))
									{
										$cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
										?>
										<td align="right">
										<?php
									}
									else
									{
										?>
										<td align="left">
										<?php
									}
									echo $cell;
									?>
								</td>
					<!-- -->
					<!-- Get next cell -->
					<!-- -->
					<?php } ?>
				</tr>
				<tr>
					<td colspan="15">
					<table class="innertable tablesorter report table table-striped table-bordered " style="width: 90%;">
						<thead>
							<tr>
								<?php foreach ($_SESSION['CSI']['HH']['details'] as $header) { ?>
								<th><?php echo $header; ?></th>
								<?php } ?>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($_SESSION['CSI']['HD'][$key] as $row2) { ?>

								<tr>
									<?php foreach ($row2 as $cell)
									{
									?>
										<!-- -->
										<!-- Output each cell, format the number if numeric-->
										<!-- -->
										<?php
											if (is_numeric($cell))
											{
												$cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
												?>
												<td align="right">
												<?php
											}
											else
											{
												?>
												<td align="left">
												<?php
											}
										echo $cell;
										?>
									</td>
									<!-- -->
									<!-- Get next cell -->
									<!-- -->
									<?php } ?>
								</tr>
								<!-- -->
								<!-- Get next row -->
								<!-- -->
							<?php } ?>
						</tbody>
					</table>

					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

	</div>
</div>




<!-- show the footer -->
<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>


      <?php
      // Show modals
      switch ($_SESSION['show_dialog'])
      {
          case 1:
              // this is the modal dialog output when suspending.
              include('../wrightetmathon/application/views/sales/suspended.php');
              break;
          case 2:
              // this is the modal dialog output when confirming.
              include('../wrightetmathon/application/views/sales/confirm.php');
              break;
          case 3:
              // this is the modal dialog output adding payments.
              include('../wrightetmathon/application/views/sales/payments.php');
              break;
          case 4:
              // this is the modal dialog CN select invoice.
              include('../wrightetmathon/application/views/sales/CN_select_invoice.php');
              break;
          case 5:
              // this is the modal dialog CN select invoice items.
              include('../wrightetmathon/application/views/sales/CN_select_invoice_items.php');
			  break;
		  case 6:
			  //payment avoir
			  include('../wrightetmathon/application/views/sales/payments_avoir.php');
			  break;
			  
			//inclusion de la page multi connexion  
		  case 7:
			  //include multi_connexion for multi salers
			  include('../wrightetmathon/application/views/sales/multi_connexion.php');
			  break;	
			    
          default:
              break;
      }
      ?>


                <!-- div for spinner -->
                <div id="spinner" class="spinner" style="display:none;">
                    <!--  <img id="img-spinner" src="<?php /*echo base_url();*/?>images/M&Wloader.gif" alt="Loading"/>-->

                    <div id="floatingCirclesG">
                        <div class="f_circleG" id="frotateG_01"></div>
                        <div class="f_circleG" id="frotateG_02"></div>
                        <div class="f_circleG" id="frotateG_03"></div>
                        <div class="f_circleG" id="frotateG_04"></div>
                        <div class="f_circleG" id="frotateG_05"></div>
                        <div class="f_circleG" id="frotateG_06"></div>
                        <div class="f_circleG" id="frotateG_07"></div>
                        <div class="f_circleG" id="frotateG_08"></div>
                    </div>
                </div>

                <!-- script for spinner -->
                <script type="text/javascript">
                    $(document).ready(function()
                    {
                        $('.sablier').click(function()
                        {
                            $('#spinner').show();
                        });
                    });
                </script>


<script type="text/javascript" language="javascript">

$(document).ready	(
						function()
						{
							$("#item").autocomplete				(
																	'<?php echo site_url("sales/item_search"); ?>',
																	{
																		minChars:0,
																		max:100,
																		selectFirst: true,
																		delay:10,
																		formatItem: function(row) 
																		{
																			return row[1];
																		}
																	}
																).focus();

							$("#item").result					(
																	function(event, data, formatted)
																	{
																		$("#add_item_form").submit();
																	}
																);
							

							$('#item,#customer').click			(
																	function()
																	{
																		$(this).attr('value','');
																	}
																);
																

							$("#customer").autocomplete			(
																	'<?php echo site_url("sales/customer_search"); ?>',
																	{
																		minChars:0,
																		delay:10,
																		max:100,
																		formatItem: function(row) 
																		{
																			return row[1];
																		}
																	}
																);

							$("#customer").result				(
																	function(event, data, formatted)
																	{
																		$("#select_customer_form").submit();
																	}
																);
		
							$('#comment').change				(
																	function() 
																	{
																		$.post('<?php echo site_url("sales/set_comment");?>', {comment: $('#comment').val()});
																	}
																);
		
							$("#overall_discount_button").click	(
																	function()
																	{
																	   $('#overall_discount_form').submit();
																	}
																);
		
							$("#force_price_button").click		(
																	function()
																	{
																	   $('#force_price_form').submit();
																	}
																);

							$("#add_payment_button").click		(
																	function()
																	{
																	   $('#add_payment_form').submit();
																	}
																);

							//$("#payment_types").change			(checkPaymentTypeGiftcard).ready(checkPaymentTypeGiftcard)
							
							$(".tablesorter a.expand").click	(
																	function(event)
																	{
																		$(event.target).parent().parent().next().find('.innertable').toggle();
																		
																		if ($(event.target).text() == '+')
																			{
																				$(event.target).text('-');
																			}
																		else
																			{
																				$(event.target).text('+');
																			}
																		return false;
																	}
																);
						}
					);

function post_item_form_submit(response)
{
	if(response.success)
	{
		$("#item").attr("value",response.item_id);
		$("#add_item_form").submit();
	}
}

function post_person_form_submit(response)
{
	if(response.success)
	{
		$("#customer").attr("value",response.person_id);
		$("#select_customer_form").submit();
	}
}

function checkPaymentTypeGiftcard()
{
	if ($("#payment_types").val() == "<?php echo $this->lang->line('sales_giftcard'); ?>")
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_giftcard_number'); ?>");
		$("#amount_tendered").val('');
		$("#amount_tendered").focus();
	}
	else
	{
		$("#amount_tendered_label").html("<?php echo $this->lang->line('sales_amount_tendered'); ?>");		
	}
}

function popUp1(parm)
{
		Win1 	=	window.open	(
								"application/views/sales/customer_comment_popbox.php?id="+parm,
								"",
								"width=450,height=400,top=280,left=35,status,menubar=no"
								)
}

</script>




                <style>

                    div#slider{
                        width:80%;
						max-width:1000px;


                    }
                    div#slider figure{

                        position:relative;
                        width:500%;
                        margin:0; padding:0; font-size:0; text-align:left}

                    div#slider figure img {
                        width:20%;height:auto;float:left;}
                    div#slider{width:80%; max-width:1000px; overflow:hidden;}



                    @keyframes slidy{
                        0% {left:0%;}
                        20% {left:0%;}
                        25% {left:-100%;}
                        45% {left:-100%;}
                        50% {left:-200%;}
                        70% {left:-200%;}
                        75% {left:-300%;}
                        95% {left:-300%;}
                        100% {left:-400%;}
                    }
                    div#slider figure{
                        position:relative;
                        width:500%;
                        margin:0;
                        padding:0;
                        font-size:0;
                        left:0;
                        text-align :left;
                        animation :30s slidy infinite;
                    }
                </style>

