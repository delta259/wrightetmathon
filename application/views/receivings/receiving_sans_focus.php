<?php
// load the header
$this																->	load->view("partial/head");
$this																->	load->view("partial/header_banner");
/*
<!--/var/www/html/wrightetmathon/application/views/items/inventory.php-->//*/
	// set up short form text
	$mode_short_text													=	ellipsize($_SESSION['title'], 10, .5);
?>

<!-- //Focus sur la barre de recherche
<script type="text/javascript">
$(document).ready(function()
{
   document.getElementById("item").focus();
});
</script>
<!-- -->
<?php
switch ($_SESSION['stock_action_id_stock_choix_liste'])
		{
      //Approvisionner
			case	10:
/*		  	?>
		  	<script type="text/javascript">
		  	$(document).ready(function()
			  {
		  		 document.getElementById("item").focus();
		  	});
			  </script>
			  <?php    //*/
				break;
			//Receptionner
/*			case	20:
			  ?>
				<script type="text/javascript">
				$(document).ready(function()
				{
					 document.getElementById("item").focus();
				});
				</script>
				<?php
				break;    //*/
			//Mouvement de stock divers
/*			case	30:
				?>
				<script type="text/javascript">
				$(document).ready(function()
				{
					 document.getElementById("item").focus();
				});
				</script>
				<?php
				break;    //*/
			//Gestion des bons de commande en attente
/*			case	40:
			  ?>
				<script type="text/javascript">
				$(document).ready(function()
				{
					 document.getElementById("item").focus();
				});
				</script>
				<?php
				break;    //*/
			//Création automatique d’un bon de commande
/*			case	50:
				?>
				<script type="text/javascript">
				$(document).ready(function()
				{
				   document.getElementById("item").focus();
				});
				</script>
				<?php
				break;    //*/
			//Gestion des réceptions en attente
/*			case	60:
				?>
				<script type="text/javascript">
				$(document).ready(function()
				{
					 document.getElementById("item").focus();
				});
				</script>
				<?php
				break;    //*/
		}
	//unset($_SESSION['stock_action_id_stock_choix_liste']); //Attention: si la variable est supprimée alors il se passe des trucs bizarres
?>


<div id="wrapper" class="wlp-bighorn-book">

		<?php $this->load->view("partial/header_menu"); ?>

		<div class="wlp-bighorn-book">

				<div class="wlp-bighorn-book-content">

						<main id="login_page" class="wlp-bighorn-page-unconnect" role="main">

								<div class="body_page" >

										<div class="body_colonne">

											<h2><?php // show title
													echo $_SESSION['title']; ?> </h2>



													<div class="body_page">

													    <?php	include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

													    <!-- table header -->
													    <div class="submenu">
													        <ul>
													            <li class="search" >
																				<?php echo form_open("receivings/add",array('id'=>'add_item_form')); ?>
<!--																				<input id='item' name='item' placeholder="Recherche" width=" 250px;"tabindex="5" size="18"  type='text' class="champ_search" title="rechercher" value="" > <!-- -->
																				<input id='item' name='item' placeholder="Recherche" width=" 250px;"tabindex="5" size="18"  type='text' class="champ_search" title="rechercher" value="" autofocus="autofocus" > <!-- -->
																				<img src="<?php echo $_SESSION['url_image'];?>/search.png" class="img_search"    style=" margin-bottom: -16.5px;margin-left: -46px;"/>

																				</form>
																			</li>


<span class="btnewc">

	<!-- Show stock actions button -->
	<?php
		if (empty($cart))
		{
			echo anchor	($_SESSION['controller_name'].'/stock_actions_1/',
						"<div class='btnew c_btcouleur' style='float: right;'><span>".$this->lang->line('receivings_stock_actions')."</span></div>"
						);
		}
	?>
</span>
</ul>
</div>

<?php
if(isset($error))
{
	echo "<div class='error_message'>".$error."</div>";
}
?>

<!-- output messages if not modal -->
<?php
if (!isset($_SESSION['show_dialog']))
	{
		include('../wrightetmathon/application/views/partial/show_messages.php');
	}
?>

<div class="body_colonneG" style="width:75%">
<div class="register_wrapper">



	<!-- Receiving Items List -->
	<!--<div style=" overflow-y:scroll; overflow-x:hidden;margin-top: 0px; font-size:14px;max-height:460px; width: 100%;border:#f5f5f5 1px solid;">-->

	<table style ="font-size: 16px;" width="100%" class="table table-striped table-bordered table-hover tablesorter" id="sortable_table">
		<thead>
			<tr>
				<th style="text-align:center;">	</th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('DynamicKit'); ?></i></span></th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('items_category'); ?></i></span></th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('items_item_number'); ?></i></span></th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('sales_item_name'); ?></i></span></th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('recvs_cost'); ?></i></span></th>
				<th align="center"><?php	echo $this->lang->line('sales_edit'); ?></th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo "Stock"; ?></i></span></th>
				<th align="center">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('sales_quantity'); ?></i></span></th>
				<?php
				if ($mode != 'purchaseorder')
				{
					?>
					<th style="text-align:center;">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('items_dluo'); ?></i></span></th>
					<?php
				}
				?>
				<th style="text-align:center;">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('sales_discount'); ?></i></span></th>
				<th style="text-align:center;">	<span onclick="myfunction();"><i class="fa fa-sort"><?php	echo $this->lang->line('sales_total'); ?></i></span></th>
				<th style="text-align:center;">	<?php echo "Désactiver" ?> </th>
			</tr>
			<!-- Tri lié à la fléche de trie fa fa-sort -->
			<script>
      function myfunction()
      {
	       $(document).ready(function()
	       {
		         $("#sortable_table").tablesorter();
	       }
      );
      };
      </script>
			
		</thead>

		<tbody id="cart_contents">
	<?php
		$newcart	=	'N';
		if(count($cart)==0)
		{
			$newcart =	'Y';
	?>
			<tr><td colspan='11'>
			<div class='warning_message' style='padding:7px;'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></div>
			</tr></tr>
	<?php
		}
		else
		{
			// if entire receipt do not reorder the cart; otherwise show in reverse order so that newly added items come out on top
			switch ($data['entire_receipt'])
			{
				case	'Y':
					$foreach	=	$cart;
					break;
				case	'N':
				default:
					$foreach 	=	array_reverse($cart, true);
			}

			// read cart
			foreach ($foreach as $line=>$item)
			{

			// get item info
				$cur_item_info = $this->Item->get_info($item['item_id']);
			// point out that this is a dynamic kit constructed at run time. Let the user enter the parts
			// making up the kit and thier price
				$DynamicKit_settext = ' ';
				if ($cur_item_info->DynamicKit == 1)
					{
						$DynamicKit_settext = 'OUI';
					}
				else
					{
						$DynamicKit_settext = ' ';
					}

				echo form_open("receivings/edit_item/$line");

				// colour the line I just processed.
				if ($_SESSION['line'] == $line)
				{
				?>
					<tr id="line_couleur">
				<?php
				}
				else
				{
				?>
					<tr >
				<?php
				}
				?>

				<?php
				// colour line red if not available with this supplier
				?>

				<!-- Output delete button -->
				<td style="text-align:center;" width="10px" ><a href="<?php echo site_url("receivings/delete_item/$line");?>" title="<?php	echo $this->lang->line('common_delete'); ?>"><img src="<?php echo base_url().'images2/del.png';?>" width="25px" height="25px" alt="Suppression"></a>
		</td>

				<!-- Output Dynamic Kit indicator -->
				<td style="align:center;font-weight:bold;color:#161FDA" width="5px" ><?php echo $DynamicKit_settext ?></td>

				<!-- Output category -->
				<td style="text-align:center;" width="100px" ><?php echo $item['category']; ?></td>

				<!-- Output item number -->

				<td style="text-align:center;" width="100px" ><a href="<?php echo site_url("receivings/view/$cur_item_info->item_id");?>" ><?php echo $item['item_number']; ?></a></td>
				
				<!-- Output decription and qty in stock -->
				<td style="text-align:left;" width="300px"><?php echo $item['name']; ?></td>

				<?php if ($items_module_allowed)
				{
				?>
					<td style="text-align: right" width="1000px" >
						<?php echo $item['price'];?>
					</td>
				<?php
				}
				else
				{
				?>
					<td style="text-align: right" width="80px" ><?php echo $item['price']; ?></td>
					<?php echo form_hidden('price',$item['price']); ?>
				<?php
				}
				?>

				<!-- output update item box -->
				<td style="text-align: center">
					<input name="edit_item" type="image" src="<?php echo base_url().$_SESSION['url_image'].'/maj.png';?>"  width="30px" height="30px"/>
				</td>
				<td><a href="<?php echo site_url("receivings/inventory/$cur_item_info->item_id");?>"> <?php echo $cur_item_info->quantity; ?></a></td>
			  
				<!-- Output quantity -->
				<?php
				// colour the line I just processed.
				if ($_SESSION['line'] == $line)
				{
				?>
					<td style="text-align: right">
					<?php echo form_input(array(
												'name'					=>	'quantity',
												'value'					=>	number_format($item['quantity'], 2),
												'style'					=>	'text-align:right;',
												'size'					=>	'3',
												'class'                 =>  'md-form-input',
												'autofocus'				=>	'autofocus'
												));
					?>
					</td>
				<?php
				}
				else
				{
				?>
					<td style="text-align: right">
					<?php echo form_input(array(
												'name'=>'quantity',
												'value'=>number_format($item['quantity'], 2),
												'style'=>'text-align:right',
												'size'=>'3'));
					?>
					</td>
				<?php
				}
				?>


				<!-- Output DLUO -->
				<?php
				if ($mode != 'purchaseorder')
				{
					?>
					<td style="text-align: center;">
					<?php
						if ($cur_item_info->dluo_indicator == 'Y')
						{
							echo anchor	(
										'items/dluo_form/'.$cur_item_info->item_id.'/DR/'.$line,
										$this->lang->line('items_dluo_x')
										);
						}
						else
						{
							echo ' ';
						}
					?>
					</td>
					<?php
				}
				?>

				<!-- output discount field -->
				<td style="text-align: right">
					<?php echo form_input(array(
												'name'=>'discount',
												'value'=>$item['discount'],
												'style'=>'text-align:right',
												'size'=>'3'));
					?>
				</td>

				<!-- output price field without tax -->
				<td style="text-align: right"><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
			  	<?php
				$tout=(string)$cur_item_info->item_id . ':' . (string)$line . ":" . 'receivings';
				?>	
				<td style="text-align:center;"><a href="<?php echo site_url("receivings/desactive/$tout");/* $tout  $cur_item_info->item_id:$line  %$line   $cur_item_info->item_id?$line    item_id=$cur_item_info->item_id&line=$line*/ ?>"><img src="<?php echo base_url().$_SESSION['url_image'].'/desactive.png';?>" width="25px" height="25px" alt="Desactive" ></a></td>
						  
			</tr>
				<!--<tr style="height:3px">
							<td colspan=10 style="background-color:#EEFFFF"> </td>
						</tr>-->
				</form>
			<?php
			}
		}
	?>
	</tbody>
	</table>
<!--</div>-->

</div>
</div>

<div class="body_colonneD" style="width:25%">
<!-- Overall Receiving -->

<div id="overall_sale">
<!-- -->
<!-- Show supplier if selected else show supplier selection dropdown-->
<!-- -->
  <div class="" style="float:left; width:100%">
	<?php
	if(isset($supplier))
	{
		echo '<b>'.$supplier. '</b>';
?>
<a href="<?php echo site_url('receivings/delete_supplier"');?>">
	<img  id="img_sup" src="images/change_fournisseur.png" class="img_search c_bgcouleur"  title="<?php echo $this->lang->line('common_change').' de '.$this->lang->line('suppliers_supplier');?>"   style="
margin-top: -11px;
float: right;
width: 35px;
height: 35px;
margin-bottom: 10px;

"/></a>
</div>
<?php

		/*echo anchor("receivings/delete_supplier",''.$this->lang->line('common_delete').' '.$this->lang->line('suppliers_supplier').'');*/
	}
	else
	{
		echo form_open("receivings/select_supplier",array('id'=>'select_supplier_form')); ?>
		<label id="supplier_label" for="supplier"><?php echo $this->lang->line('recvs_supplier'); ?></label>
		<?php
			//
			echo form_dropdown	(
									'supplier_id',
									$_SESSION['G']->supplier_pick_list,
									0,
									'onchange="$(\'#select_supplier_form\').submit();"'
									); ?>
		</form>



		<?php
	}
	?>
<!-- -->

	<div id='sale_details' style="margin-top: 10px;">
		<div class="" style='width:65%;font-size:15px;font-weight:bold;color:#100909'><?php echo 'Total '; echo ' HT'; ?>:</div>
		  <div class="bttotal" style="width: 40%;
	    float: right;
	    text-align: right;
	    /* font-size: 13pt; */
	    font-weight: bold;"><?php echo to_currency($total); ?></div></div>
	</div>
	<?php
	if(count($cart) > 0)
	{
		?>
		<fieldset style="margin-top:80px; border:1px solid #0A6184;  background:none;">
			<div id="finish_sale" class="btnp" style="width:231px;">

				<?php
				// open the form
				echo form_open("receivings/complete",array('id'=>'finish_sale_form'));
				?>

				<!-- Show comments -->
				<label id="comment_label" for="comment"><?php echo $this->lang->line('common_comments'); ?>:</label>
				<?php echo form_textarea(array('name'=>'comment', 'id' => 'comment', 'value'=>$comment,'rows'=>'3','cols'=>'20','class'=>'md-form-input'));?>

				<!-- show complete button -->
				<div class='' style=''><span>
				<a href=<?php echo 'index.php/'.$_SESSION['controller_name'].'/confirm/stocktransaction'?>>
					<img style="margin-left: 5px;
                            margin-bottom: -15px;
                            "  width="40px" height ="40px" src="images/valider.png">
														<?php echo $this->lang->line('recvs_complete_receiving'); ?>
				</a>
				</span>
				</div>

				<!-- Show cancel transaction button -->
				<div class='' style=''><span>
				<a href=<?php echo 'index.php/'.$_SESSION['controller_name'].'/confirm/canceltransaction'?>>
					<img style="margin-left: 5px;
														margin-bottom: -15px;
														"  width="40px" height ="40px" src="images/annuler.png">
				<?php echo $this->lang->line('recvs_cancel'); ?>
				</a>
				</span>
				</div>

				<!-- Show suspend transaction button -->
				<?php
					switch ($_SESSION['stock_action_id'])
					{
						// show suspend for PO
						case	10:
								?>
								<div class='' style=''><span>
								<a href=<?php echo 'index.php/'.$_SESSION['controller_name'].'/confirm/suspendtransaction'?>>
									<img style="margin-left: 5px;
					                            margin-bottom: -15px;
					                            "  width="40px" height ="40px" src="images/en_attente.png">
								<?php echo $this->lang->line('receivings_suspend'); ?>
								</a>
								</span>
								</div>
								<?php
						break;

						// show suspend for reception
						case	20:
								?>
								<div class='' style=''><span>
								<a href=<?php echo 'index.php/'.$_SESSION['controller_name'].'/confirm/suspendreception'?>>
									<img style="margin-left: 5px;
					                            margin-bottom: -15px;
					                            "  width="40px" height ="40px" src="images/en_attente.png">
								<?php echo $this->lang->line('receivings_suspend'); ?>
								</a>
								</span>
								</div>
								<?php
						break;
					}
				?>
			</div>
		</fieldset>
		</form>
	<?php
	}
	?>

</div>
</div>
</div>

<div class="clearfix" style="margin-bottom:30px;">&nbsp;</div>


<?php
	// show dialog depending on show dialog
	switch ($_SESSION['show_dialog'])
	{
		// show stock action select
		case	1:
				include('../wrightetmathon/application/views/receivings/form_stock_actions.php');
		break;

		// show confirmation
		case	2:
				include('../wrightetmathon/application/views/sales/confirm.php');
		break;

		// show suspended POs
		case	3:
				include('../wrightetmathon/application/views/receivings/suspended.php');
		break;

		// show suspended receptions
		case	4:
				include('../wrightetmathon/application/views/receivings/suspended_receptions.php');
		break;

		// show suspended for merge selection
		case	5:
				include('../wrightetmathon/application/views/receivings/suspended_merge.php');
		break;

		// show inventory low
		case	6:
				include('../wrightetmathon/application/views/receivings/inventory_low.php');
		break;
	}
?>
<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<script>
$( "register_wrapper" ).scrollTop(<?php echo $_SESSION['line'] ?>);
</script>


<script type="text/javascript" language="javascript">
$(document).ready(function()
{
    $("#item").autocomplete('<?php echo site_url("receivings/item_search"); ?>',
    {
    	minChars:0,
    	max:100,
       	delay:10,
       	selectFirst: true,
    	formatItem: function(row) {
			return row[1];
		}
    });

    $("#item").result(function(event, data, formatted)
    {
		$("#add_item_form").submit();
    });



	$('#item').blur(function()
    {
    	$(this).attr('value',"<?php echo $this->lang->line('sales_start_typing_item_name'); ?>");
    });

	$('#item,#supplier').click(function()
    {
    	$(this).attr('value','');
    });

    $("#supplier").autocomplete('<?php echo site_url("receivings/supplier_search"); ?>',
    {
    	minChars:0,
    	delay:10,
    	max:100,
    	formatItem: function(row) {
			return row[1];
		}
    });

    $("#supplier").result(function(event, data, formatted)
    {
		$("#select_supplier_form").submit();
    });

    $('#supplier').blur(function()
    {
    	$(this).attr('value',"<?php echo $this->lang->line('recvs_start_typing_supplier_name'); ?>");
    });

    $('#comment').change				(
											function()
											{
												$.post('<?php echo site_url("receivings/set_comment");?>', {comment: $('#comment').val()});
											}
										);

});

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
		$("#supplier").attr("value",response.person_id);
		$("#select_supplier_form").submit();
	}
}

</script>

