<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready	(
						function()
						{
							enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
						}
					);
</script>

<!-- table header -->

<div class="submenu">
    <ul>
        <li class="search" >
            <?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
            <input id='search'  placeholder="Recherche " tabindex="5" size="18" name='search' type='text' class="champ_search" title="rechercher" value="" >
            <img src="<?php echo $_SESSION['url_image'];?>/search.png" class="img_search"    style=" margin-bottom: -16.5px;margin-left: -46px;"/>
            </form>
        </li>

		
		
        <span class="btnewc">
		<a href="<?php echo site_url("customers/search_ext_customers"); ?>" >
		<div class='btnew c_btcouleur' style='float: left;'>Recherche client</div>
	</a><span>
		
        <span class="btnewc">
                    <?php
                   
                    include('../wrightetmathon/application/views/partial/show_buttons.php');
                    ?>

                </span>
    </ul>
</div>

<!-- output messages if not modal -->
<?php
if (!isset($_SESSION['show_dialog']))
	{
		include('../wrightetmathon/application/views/partial/show_messages.php');
	}
?>


<!-- Show the table -->
<?php
	
	echo $manage_table;
	
?>
<div><!-- Output Links -->
    <?php echo $links;?></div>
<div>
	
	<?php
       echo $customer_number;
    ?>
</div>	
</div>
<!-- Close the form -->

<?php
	echo form_close();
?>

<?php
	// this is the modal dialog output when updating an existing record or adding a new one.
	// show dialog depending on show dialog
	// 1 = customer data entry
	// 5 = merge

	switch ($_SESSION['show_dialog'])
	{
		// show item data entry
		case	1:
				include('../wrightetmathon/application/views/customers/form.php');
		break;

		// show merge
		case	5:
				include('../wrightetmathon/application/views/customers/merge_form.php');
		break;

		case    6:
				include('../wrightetmathon/application/views/customers/form_solde.php');
		break;

		//panel for search customers in other shops
		case 7:
			include('../wrightetmathon/application/views/customers/search_ext_customers.php');
		break;
		
		//panel for add customers
		case 8:
			include('../wrightetmathon/application/views/customers/add_ext_customers.php');
		break;

		//add fidelity_points
		case 9:
			include('../wrightetmathon/application/views/customers/add_ext_customers_fidelity_points.php');
		break;

		case 10:
			include('../wrightetmathon/application/views/customers/add_ext_sales.php');
		break;
	}

?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<script type="text/javascript" language="javascript">
//to 'plier/déplier' details of sales
$(document).ready(
				function()
				{
					$(".tablesorter a.expand").click(
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
</script>