<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function()
{
    enable_select_all();
    enable_checkboxes();
    enable_row_selection();
    enable_search('<?php echo site_url($_SESSION['controller_name'].'/suggest')?>','<?php echo $this->lang->line("common_confirm_search")?>');
    //enable_delete('<?php echo $this->lang->line($controller_name."_confirm_delete")?>','<?php echo $this->lang->line($controller_name."_none_selected")?>');
    enable_bulk_edit('<?php echo $this->lang->line($_SESSION['controller_name']."_none_selected")?>');

    $('#generate_barcodes').click(function()
    {
    	var selected = get_selected_values();
    	if (selected.length == 0)
    	{
    		alert('<?php echo $this->lang->line('items_must_select_item_for_barcode'); ?>');
    		return false;
    	}

    	$(this).attr('href','index.php/items/generate_barcodes/'+selected.join(':'));
    });

    $("#low_inventory").click(function()
    {
    	$('#items_filter_form').submit();
    });

    $("#is_serialized").click(function()
    {
    	$('#items_filter_form').submit();
    });

// Wright added 18/01/2014
    $("#DynamicKit").click(function()
    {
    	$('#items_filter_form').submit();
    });
// end Wright added

    $("#no_description").click(function()
    {
    	$('#items_filter_form').submit();
    });

//GARRISON ADDED 4/21/2013
    $("#search_custom").click(function()
    	    {
    	    	$('#items_filter_form').submit();
    	    });
//END GARRISON ADDED

});

function post_item_form_submit(response)
{
	if(!response.success)
	{
		// set_feedback has three parms (found in common.js)
		// 1) the text for the message
		// 2) the class to use (found in general.css)
		// 3) whether to keep displaying the message or fade it, true == keep it, false = fade it
		set_feedback(response.message,'error_message',false);

		// highlight the row in error (found in manage_tables.js)
		// two parameters
		// 1) the row id to highlight
		// 2) success or failure (changes colour of the line)
		success_or_failure = 'failure'
		hightlight_row(response.item_id, success_or_failure);
	}
	else
	{
		//This is an update, just update one row
		if(jQuery.inArray(response.item_id, get_visible_checkbox_ids()) != -1)
		{
			update_row(response.item_id,'<?php echo site_url($_SESSION['controller_name'].'/get_row')?>');
			set_feedback(response.message,'success_message',false);

		}
		else //refresh entire table
		{
			do_search(true,function()
			{
				//highlight new row
				hightlight_row(response.item_id, success_or_failure);
				set_feedback(response.message,'success_message',false);
			});
		}
	}
}

function post_bulk_form_submit(response)
{
	if(!response.success)
	{
		set_feedback(response.message,'error_message',true);
	}
	else
	{
		var selected_item_ids=get_selected_values();
		for(k=0;k<selected_item_ids.length;k++)
		{
			update_row(selected_item_ids[k],'<?php echo site_url($_SESSION['controller_name'].'/get_row')?>');
		}
		set_feedback(response.message,'success_message',false);
	}
}

function show_hide_search_filter(search_filter_section, switchImgTag) {
        var ele = document.getElementById(search_filter_section);
        var imageEle = document.getElementById(switchImgTag);
        var elesearchstate = document.getElementById('search_section_state');
        if(ele.style.display == "block")
        {
                ele.style.display = "none";
				imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/plus.png" style="border:2;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
                elesearchstate.value="none";
        }
        else
        {
                ele.style.display = "block";
                imageEle.innerHTML = '<img src=" <?php echo base_url()?>images/minus.png" style="border:0;outline:none;padding:0px;margin:0px;position:relative;top:-5px;" >';
                elesearchstate.value="block";
        }
}

</script>

<!-- output messages if not modal -->
<?php
if (!isset($_SESSION['show_dialog']))
	{
		include('../wrightetmathon/application/views/partial/show_messages.php');
	}
?>

<!-- table header -->

<div class="submenu">
    <ul>
        <li class="search" >
            <?php echo form_open("$controller_name/search",array('id'=>'search_form')); ?>
            <input id='search' maxlength="13" placeholder="Recherche" width=" 250px;"tabindex="5" size="18" name='search' type='text' class="champ_search" title="rechercher" value="" >
            <img src="images/search.png" class="img_search"    style=" margin-bottom: -16.5px;margin-left: -46px;"/>
        </li>

        <span class="btnewc">
                    <?php
                    include('../wrightetmathon/application/views/partial/show_buttons.php');
                    ?>

        </span>
    </ul>
</div>


<!-- show the table -->
<div class="table table-striped table-bordered table-hover">
<?php
	// $manage_table is an array setup via table_helper.php in the controller items.php
	echo $manage_table;
    ?>
    <div>
        <br>
    <?php
	echo $links;

?></div>
</div>



<?php
	// this is the modal dialog output when updating an existing record or adding a new one.
	// show dialog depending on show dialog
	switch ($_SESSION['show_dialog'])
	{
		// show item data entry
		case	1:
				include('../wrightetmathon/application/views/items/form.php');
		break;

		// show clone
		case	2:
				include('../wrightetmathon/application/views/items/clone_form.php');
		break;

		// show inventory
		case	3:
				include('../wrightetmathon/application/views/items/inventory.php');
		break;

		// show count details
		case	4:
				include('../wrightetmathon/application/views/items/count_details.php');
		break;

		// show merge
		case	5:
				include('../wrightetmathon/application/views/items/merge_form.php');
		break;

		// show DLUO
		case	6:
				include('../wrightetmathon/application/views/items/dluo_form.php');
		break;

		// show price label
		case	7:
				include('../wrightetmathon/application/views/items/label_form.php');
		break;

		// show remote stock
		case	8:
				include('../wrightetmathon/application/views/items/form_remote_stock.php');
		break;

		// show supplier entry
		case	9:
				include('../wrightetmathon/application/views/items/form_item_supplier.php');
		break;

		// show warehouse entry
		case	10:
				include('../wrightetmathon/application/views/items/form_item_warehouse.php');
		break;

		// show pricelist entry
		case	11:
				include('../wrightetmathon/application/views/items/form_item_pricelist.php');
		break;

		// show bulk actions
		case	12:
				include('../wrightetmathon/application/views/items/form_bulk_actions.php');
		break;

		// show bulk actions, enter SET data and WHERE data
		case	13:
				include('../wrightetmathon/application/views/items/form_bulk_actions_select.php');
		break;

		// show bulk actions, confirm the sql
		case	14:
				include('../wrightetmathon/application/views/items/form_bulk_actions_confirm.php');
		break;
	}
?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>
