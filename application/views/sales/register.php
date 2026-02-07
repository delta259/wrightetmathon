<!--
register.php view controls the display of the sales register

controller is sales.php
css can be found in pos-register.css and modern-theme.css
-->


<!-- this style info is for the sales target area -->
<?php
	// output header
	$this->load->view("partial/header");

	// get the number format -->
	$pieces =array();
	$pieces = explode("/", $this->config->item('numberformat'));


	if(($_POST['item'] != NULL) && ($this->config->item('custom2_name') != 'Y'))
	{
		if($_POST['input_qty'] == NULL)
		{
			$_POST['input_qty'] = 1;
		}
		$input = $_POST['item'];
		foreach($_SESSION['CSI']['CT'] as $key => $item)
		{
			if($item->line_quantity > 0 && $item->item_id == $input && end($_SESSION['CSI']['CT']) != $item && $item->kit_item != 'Y' && $item->DynamicKit != 'Y' && $item->line_discount == 0)
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

<?php
	// set up the mode lang line, long and short form text
	$lang_line = 'reports_'.$_SESSION['CSI']['SHV']->mode;
	$mode_long_text = $this->lang->line($lang_line);
	$this->load->helper('text');
	$mode_short_text = ellipsize($mode_long_text, 10, .5);
?>

<!-- ============================================ -->
<!-- POS LAYOUT - Modern Grid                     -->
<!-- ============================================ -->
<div class="pos-layout">

    <!-- ======================================== -->
    <!-- LEFT COLUMN (Main)                       -->
    <!-- ======================================== -->
    <div class="pos-main">

        <!-- Messages -->
        <div id="sales_register_wrapper">
        <?php
        if(isset($error))
        {
            echo "<div class='message_erreur'>".$error."</div>";
        }

        if (isset($warning))
        {
            echo "<div class='warning_message'>".$warning."</div>";
        }

        if (isset($success))
        {
            echo "<div class='success_message'>".$success."</div>";
        }

        if (!isset($_SESSION['show_dialog']))
        {
            include('../wrightetmathon/application/views/partial/show_messages.php');
        }
        ?>

        <!-- Customer Panel + Actions Toolbar -->
        <div class="pos-customer-panel">
            <div class="pos-toolbar">
                <div class="pos-toolbar-left">
                <?php
                $origin = 'SS';

                if(isset($_SESSION['CSI']['SHV']->customer_id))
                {
                    ?>
                    <div class="pos-customer-info">
                        <?php
                        $this->db->select('comments');
                        $this->db->from('people');
                        $this->db->where('person_id = "'.$_SESSION['CSI']['SHV']->customer_id.'"');
                        $report_data = $this->db->get()->result_array();

                        $comment = $report_data[0]["comments"];
                        if($comment != "0" && $comment != NULL)
                        {
                            ?>
                            <span class="pos-customer-icon">
                                <img src="<?php echo $_SESSION['url_image'];?>/info.png" title="<?php echo $comment?>" height="20px">
                            </span>
                            <?php
                        }

                        if(strlen($_SESSION['CSI']['SHV']->dob_month) == 1)
                        {
                            $_SESSION['CSI']['SHV']->dob_month = '0' . $_SESSION['CSI']['SHV']->dob_month;
                        }

                        if(strlen($_SESSION['CSI']['SHV']->dob_day) == 1)
                        {
                            $_SESSION['CSI']['SHV']->dob_day = '0' . $_SESSION['CSI']['SHV']->dob_day;
                        }

                        $dob_customer['0'] = strval($_SESSION['CSI']['SHV']->dob_year . '-' . $_SESSION['CSI']['SHV']->dob_month . '-' . $_SESSION['CSI']['SHV']->dob_day);
                        $date['plus_1']['0'] = date("Y-m-d", strtotime("+1 day"));
                        $date['plus_2']['0'] = date("Y-m-d", strtotime("+2 day"));
                        $date['moins_1']['0'] = date("Y-m-d", strtotime("-1 day"));
                        $date['moins_2']['0'] = date("Y-m-d", strtotime("-2 day"));
                        $date['new']['0'] = date("Y-m-d");
                        list($dob_customer['Y'], $dob_customer['m'], $dob_customer['d']) = explode('-', $dob_customer['0']);

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
                            <span class="pos-customer-icon">
                                <img src="<?php echo $_SESSION['url_image'];?>/anniv.png" title="Joyeux Anniversaire le <?php echo $_SESSION['CSI']['SHV']->dob_day . '/' . $_SESSION['CSI']['SHV']->dob_month;?>" height="26px">
                            </span>
                            <?php
                        }
                        ?>

                        <?php
                        // get the suspended sales
                        $_SESSION['suspended_sales'] = array();
                        $_SESSION['suspended_sales'] = $this->Sale_suspended->get_all()->result_array();
                        $suspended=false;
                        foreach ($_SESSION['suspended_sales'] as $suspendedsale)
                        {
                            if ($suspendedsale['customer_id']  == $_SESSION['CSI']['SHV']->customer_id)
                            {
                                ?>
                                <span class="pos-customer-icon">
                                    <img src="<?php echo $_SESSION['url_image']?>/panier.png" title="Vente en suspend:<?php echo $suspendedsale['comment']  ?>" height="22px">
                                </span>
                                <?php $suspended=true;
                            }
                        }
                        ?>

                        <span class="pos-customer-name" id="client">
                            <?php echo anchor(
                                'customers/view/'.$_SESSION['CSI']['SHV']->customer_id.'/'.$origin,
                                $_SESSION['CSI']['SHV']->customer_formatted
                            ); ?>
                        </span>

                        <?php
                        switch($_SESSION['customer_info_not_complete'])
                        {
                            case 1:
                                ?>
                                <span class="pos-customer-icon">
                                    <img src="<?php echo $_SESSION['url_image']?>/attention_rouge_client.png" title="Attention Fiche cliente imcompl&eacute;te" height="22px">
                                </span>
                                <?php
                                break;
                        }
                        ?>

                        <a href="<?php echo site_url('sales/customer_remove'); ?>" class="pos-customer-remove" title="<?php echo $this->lang->line('common_remove'); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </a>
                    </div>
                    <?php
                }
                else
                {
                    // Show customer search form
                    echo form_open("sales/customer_select/".'SC', array('id'=>'select_customer_form'));
                    ?>
                    <div class="pos-customer-search">
                        <?php echo form_input(array(
                            'name'        => 'customer',
                            'id'          => 'customer',
                            'size'        => '25',
                            'class'       => 'pos-search-input',
                            'placeholder' => $this->lang->line('sales_start_typing_customer_name')
                        )); ?>
                        <?php echo anchor(
                            'customers/view/-1/'.$origin,
                            $this->lang->line('sales_new_customer'),
                            'class="pos-link"'
                        ); ?>
                    </div>
                    </form>
                    <?php
                }
                ?>
                </div>

                <div class="pos-toolbar-right">
                    <?php echo anchor("sales/suspended/", '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg><span>'.$this->lang->line('sales_suspended_sales').'</span>', 'class="pos-toolbar-btn"'); ?>
                    <?php echo anchor("sales/CN_select_invoice", '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/></svg><span>'.$this->lang->line('sales_credit_note').'</span>', 'class="pos-toolbar-btn"'); ?>
                    <?php
                    if(count($_SESSION['CSI']['CT']) > 0)
                    {
                        echo anchor("sales/cancel_sale",
                            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><span>'.$this->lang->line('recvs_cancel').' '.$mode_short_text.'</span>',
                            'class="pos-toolbar-btn pos-toolbar-btn-danger"'
                        );
                    }
                    ?>
                    <a href="<?php echo site_url("sales/icone_tiroir"); ?>" class="pos-toolbar-btn pos-toolbar-btn-icon" title="Tiroir-caisse">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 14h20"/><path d="M9 14v2h6v-2"/></svg>
                    </a>
                </div>
            </div>

            <?php
            if(isset($_SESSION['CSI']['SHV']->customer_id))
            {
                echo '<div class="pos-customer-details">';
                // if customer has comments, show them via a pop-up but only if allowed to show comments
                if (!empty($_SESSION['current_sale_info']['customer_info']->customer_comments) AND $this->config->item('person_show_comments') == 'Y')
                {
                    $_SESSION['CSI']['CI']->customer_comments = str_replace(array("\r", "\n"), " ", $_SESSION['CSI']['CI']->customer_comments);
                    $customer = str_replace(array("\r", "\n"), " ", $_SESSION['CSI']['SHV']->customer_formatted);
                    $customer_comments_parm = str_replace(' ', '_', $_SESSION['CSI']['CI']->customer_comments);
                    $customer_parm = str_replace(' ', '_', $customer);
                    $parm = json_encode($customer_parm.'/'.$customer_comments_parm);
                    $popup_command = '<a href="#" onMouseOver = popUp1('.$parm.'); onmouseout="Win1.close()">'.$this->lang->line('customers_comments').'</a>';
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
                    echo ' <b class="c_couleur">'.$this->lang->line('customer_profiles_customer_profiles').': </b>'
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
            ?>
        </div>

        <!-- Item Entry -->
        <div class="pos-item-entry">
            <?php
            echo form_open("sales/add",array('id'=>'add_item_form'));

            if ($this->config->item('custom2_name') != 'Y')
            {
                echo form_input(array(
                    'name'        => 'input_qty',
                    'id'          => 'input_qty',
                    'size'        => '5',
                    'class'       => 'pos-qty-input',
                    'placeholder' => $this->lang->line('sales_quantity'),
                    'value'       => 1
                ));
            }
            else
            {
                echo form_input(array(
                    'name'        => 'input_qty',
                    'id'          => 'input_qty',
                    'size'        => '5',
                    'class'       => 'pos-qty-input',
                    'placeholder' => $this->lang->line('sales_quantity')
                ));
            }

            echo form_input(array(
                'name'        => 'item',
                'id'          => 'item',
                'size'        => '30',
                'class'       => 'pos-item-input',
                'autofocus'   => 'autofocus',
                'methode'     => 'POST',
                'placeholder' => $this->lang->line('sales_start_typing_item_name')
            ));
            echo form_close();
            ?>
        </div>

        <!-- Cart Table -->
        <div class="pos-cart">
            <div class="pos-cart-scroll">
                <table id="sales_register" class="pos-cart-table tablesorter">
                    <thead>
                        <tr>
                            <th></th>
                            <th><?php echo $this->lang->line('items_item_number'); ?></th>
                            <th><?php echo $this->lang->line('sales_item_name'); ?></th>
                            <th><?php echo $this->lang->line('items_DynamicKit'); ?></th>
                            <th><?php echo $this->lang->line('sales_in_stock')?></th>
                            <th><?php echo $this->lang->line('sales_price').$this->lang->line('sales_TTC'); ?></th>
                            <th><?php echo $this->lang->line('sales_quantity'); ?></th>
                            <th><?php echo $this->lang->line('sales_discount').$this->lang->line('common_percent'); ?></th>
                            <th><?php echo $this->lang->line('sales_line_offered').$this->lang->line('common_question'); ?></th>
                            <th><?php echo $this->lang->line('sales_total').$this->lang->line('sales_TTC'); ?></th>
                        </tr>
                    </thead>

                    <tbody id="sales_cart_contents">
                        <?php
                        if(count($_SESSION['CSI']['CT']) == 0)
                        {
                            ?>
                            <tr class="pos-cart-empty">
                                <td colspan='10'><?php echo $this->lang->line('sales_no_items_in_cart'); ?></td>
                            </tr>
                            <?php
                        }
                        else
                        {
                            $current_kit_option = '';
                            $colour = '';

                            foreach($_SESSION['CSI']['CT'] as $line => $cart_line)
                            {
                                echo form_open("sales/edit_item/$line");

                                // Test for change of kit option
                                if ($current_kit_option != $cart_line->kit_option)
                                {
                                    $current_kit_option = $cart_line->kit_option;
                                    switch ($colour)
                                    {
                                        case 'pos-kit-pink':
                                            $colour = 'pos-kit-blue';
                                            break;
                                        case 'pos-kit-blue':
                                            $colour = 'pos-kit-pink';
                                            break;
                                        default:
                                            $colour = 'pos-kit-pink';
                                            break;
                                    }
                                    ?>
                                    <tr class="pos-kit-sep">
                                        <td colspan="10"></td>
                                    </tr>
                                    <?php
                                }

                                // test for dynamic kit
                                if ($cart_line->DynamicKit == 'Y')
                                {
                                    $DynamicKit_settext = $this->lang->line('common_yes');
                                }
                                else
                                {
                                    $DynamicKit_settext = $this->lang->line('common_no');
                                }

                                // test for kit item
                                if ($cart_line->kit_item == 'N')
                                {
                                    // Normal item row
                                    if ($cart_line->last_line)
                                    {
                                        ?>
                                        <tr id="line_couleur" class="pos-cart-row pos-cart-last-item">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <tr class="pos-cart-row">
                                        <?php
                                    }
                                    ?>
                                        <td class="pos-cart-delete" title="<?php echo $this->lang->line('common_delete'); ?>">
                                            <a href="<?php echo site_url("sales/delete_item/$line");?>">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                            </a>
                                        </td>

                                        <td title="<?php echo $this->lang->line('sales_item_det'); ?>">
                                            <?php echo anchor('items/view/'.$cart_line->item_id.'/'.$origin, $cart_line->item_number); ?>
                                        </td>

                                        <td style="text-align:left;" title="<?php echo $this->lang->line('sales_remote_stock'); ?>">
                                            <?php echo $cart_line->name; ?>
                                        </td>

                                        <td><?php echo $DynamicKit_settext ?></td>
                                        <td><?php echo $cart_line->quantity;?></td>
                                        <td style="text-align:right;"><?php echo number_format($cart_line->line_priceTTC, 2); ?></td>

                                        <td class="zone_champ_saisie_1">
                                            <?php
                                            $qty_val = round(number_format($cart_line->line_quantity, 2), 0);
                                            echo form_input(array(
                                                'type'=>'number',
                                                'name'=>'line_quantity',
                                                'value'=>$qty_val,
                                                'data-orig'=>$qty_val,
                                                'class'=>'pos-auto-edit',
                                                'style'=>'text-align:right',
                                                'size'=>'4'
                                            ));
                                            ?>
                                        </td>

                                        <td class="zone_champ_saisie">
                                            <?php
                                            if ($cart_line->line_discount == 0 AND $_SESSION['CSI']['CT'][$line]->CN_line != 'Y')
                                            {
                                                echo form_input(array(
                                                    'name'=>'line_discount',
                                                    'value'=>$cart_line->line_discount,
                                                    'data-orig'=>$cart_line->line_discount,
                                                    'class'=>'pos-auto-edit',
                                                    'style'=>'text-align:right',
                                                    'size'=>'6'
                                                ));
                                            }
                                            else
                                            {
                                                echo $cart_line->line_discount;
                                            }
                                            ?>
                                        </td>

                                        <td class="zone_champ_saisie">
                                            <?php
                                            if ($_SESSION['CSI']['CT'][$line]->CN_line == 'Y')
                                            {
                                                echo $this->lang->line('sales_credit_note');
                                            }
                                            else
                                            {
                                                if ($cart_line->line_offered == 'N')
                                                {
                                                    echo form_dropdown(
                                                        'line_offered',
                                                        $_SESSION['G']->YorN_pick_list,
                                                        $cart_line->line_offered,
                                                        'class="pos-auto-edit"'
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

                                        <td class="pos-cart-edit" style="display:none">
                                            <input name="edit_item" type="image" src="<?php echo base_url().$_SESSION['url_image'].'/maj.png';?>" width="24px" height="24px"/>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                else
                                {
                                    // Kit item row
                                    if ($cart_line->last_line)
                                    {
                                        ?>
                                        <tr class="pos-kit-row pos-cart-last-item">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <tr class="pos-kit-row <?php echo $colour; ?>">
                                        <?php
                                    }
                                    ?>
                                        <td></td>
                                        <td></td>
                                        <td><?php echo $cart_line->kit_option; ?></td>
                                        <td><?php echo $cart_line->item_number; ?></td>
                                        <td><?php echo $cart_line->name;?></td>
                                        <td><?php echo $cart_line->quantity; ?></td>
                                        <?php
                                        if ($cart_line->kit_option_type == 'F')
                                        {
                                            ?>
                                            <td style="text-align:right"><?php echo number_format($cart_line->line_quantity);?></td>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <td class="zone_champ_saisie" style="text-align:right"><?php echo round(number_format($cart_line->line_quantity, 2),0); ?></td>
                                            <?php
                                        }
                                        ?>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </form>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        </div><!-- end #sales_register_wrapper -->

    </div><!-- end .pos-main -->

    <!-- ======================================== -->
    <!-- RIGHT COLUMN (Sidebar)                   -->
    <!-- ======================================== -->
    <div class="pos-sidebar">

        <!-- Till Status (closed warning) -->
        <?php if ($_SESSION['cashtill_not_open'] == 1 OR $_SESSION['cashtill_closed'] == 1) { ?>
        <div class="pos-till-badge pos-till-closed">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <span><?php echo ($_SESSION['cashtill_not_open'] == 1) ? $this->lang->line('cashtills_not_open') : $this->lang->line('cashtills_closed'); ?></span>
        </div>
        <?php } ?>

        <!-- Sales Targets (compact grid) -->
        <?php if($_SESSION['hidden'] != 1){ ?>
        <div class="pos-targets-bar" id="sales_targets">
            <?php $month = date('F'); ?>
            <div class="pos-targets-item">
                <span class="pos-targets-label"><?php echo $this->lang->line('cal_'.$month); ?></span>
                <span class="pos-targets-value"><?php echo number_format($_SESSION['CSI']['TT']->monthlyrealised, 0, $pieces[1], $pieces[2]); ?></span>
                <span class="pos-targets-sub">/&thinsp;<?php echo number_format($_SESSION['CSI']['TT']->monthlytarget, 0, $pieces[1], $pieces[2]); ?> (<?php echo number_format($_SESSION['CSI']['TT']->monthlyrealisedpercent, 0, $pieces[1], $pieces[2]); ?>%)</span>
            </div>
            <span class="pos-targets-sep"></span>
            <div class="pos-targets-item">
                <span class="pos-targets-label"><?php echo $this->lang->line('sales_daily'); ?></span>
                <?php
                if ($_SESSION['CSI']['TT']->dailytodo > $_SESSION['CSI']['TT']->dailytarget)
                {
                    ?>
                    <span class="pos-targets-value pos-amount-negative"><?php echo number_format($_SESSION['CSI']['TT']->dailydone, 0, $pieces[1], $pieces[2]); ?></span>
                    <span class="pos-targets-sub pos-amount-negative">/&thinsp;<?php echo number_format($_SESSION['CSI']['TT']->dailytodo, 0, $pieces[1], $pieces[2]); ?></span>
                    <?php
                }
                else
                {
                    ?>
                    <span class="pos-targets-value pos-amount-positive"><?php echo number_format($_SESSION['CSI']['TT']->dailydone, 0, $pieces[1], $pieces[2]); ?></span>
                    <span class="pos-targets-sub">/&thinsp;<?php echo number_format($_SESSION['CSI']['TT']->dailytodo, 0, $pieces[1], $pieces[2]); ?></span>
                    <?php
                }
                ?>
            </div>
            <span class="pos-targets-sep"></span>
            <div class="pos-targets-item">
                <span class="pos-targets-label"><?php echo $this->lang->line('reports_average_basket'); ?></span>
                <span class="pos-targets-value"><?php echo $_SESSION['CSI']['TT']->monthlybasket; ?>&thinsp;&euro;</span>
            </div>
            <span class="pos-targets-sep"></span>
            <div class="pos-targets-item">
                <span class="pos-targets-label"><?php echo $this->lang->line('reports_nb_sales'); ?></span>
                <span class="pos-targets-value"><?php echo $_SESSION['CSI']['TT']->count_invoice; ?></span>
            </div>
            <span class="pos-targets-sep"></span>
            <div class="pos-targets-item">
                <span class="pos-targets-label"><?php echo $this->lang->line('sales_todo'); ?></span>
                <span class="pos-targets-value"><?php echo number_format($_SESSION['CSI']['TT']->monthlytodo, 0, $pieces[1], $pieces[2]); ?></span>
            </div>
        </div>
        <?php } ?>

        <?php
        if(count($_SESSION['CSI']['CT']) > 0)
        {
        ?>
        <div id="overall_sale">

            <!-- Totals Card -->
            <div class="pos-totals-card">
                <div class="pos-totals-body">
                    <table class="pos-totals-table">
                        <tr>
                            <td class="pos-totals-label"><?php echo $this->lang->line('reports_subtotal'); ?></td>
                            <td class="pos-totals-value"><?php echo to_currency($_SESSION['CSI']['SHV']->header_valueAD_HT); ?></td>
                        </tr>
                        <tr>
                            <td class="pos-totals-label"><?php echo $_SESSION['CSI']['SHV']->tax_name.' '; ?></td>
                            <td class="pos-totals-value"><?php echo to_currency($_SESSION['CSI']['SHV']->header_taxAD); ?></td>
                        </tr>
                        <tr class="pos-totals-grand">
                            <td class="pos-totals-label"><?php echo $this->lang->line('reports_total'); ?></td>
                            <td class="pos-totals-value"><?php echo to_currency($_SESSION['CSI']['SHV']->header_valueAD_TTC); ?></td>
                        </tr>
                        <?php
                        if ($_SESSION['CSI']['SHV']->default_profile_flag == 0)
                        {
                            ?>
                            <tr class="pos-discount-row">
                                <td class="pos-totals-label"><?php echo $this->lang->line('customer_profiles_customer_profiles')
                                    .$this->lang->line('common_space')
                                    .$this->lang->line('common_equal')
                                    .$this->lang->line('common_space')
                                    .$_SESSION['CSI']['CPI']->profile_name;
                                ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="pos-totals-label"><?php echo
                                    $this->lang->line('customer_profiles_profile_discount')
                                    .$this->lang->line('common_space')
                                    .$this->lang->line('common_equal')
                                    .$this->lang->line('common_space')
                                    .$_SESSION['CSI']['CPI']->profile_discount
                                    .$this->lang->line('common_percent'); ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        else
                        {
                            if (isset($_SESSION['CSI']['SHV']->overall_discount))
                            {
                                ?>
                                <tr class="pos-discount-row">
                                    <td class="pos-totals-label"><?php echo $this->lang->line('sales_global')
                                        .$this->lang->line('common_space')
                                        .$_SESSION['CSI']['SHV']->overall_discount
                                        .$this->lang->line('common_percent');?></td>
                                    <td></td>
                                </tr>
                                <?php
                            }
                            else
                            {
                                echo form_open("sales/overall_discount",array('id'=>'overall_discount_form'));
                                ?>
                                <tr class="pos-discount-row">
                                    <td class="pos-totals-label"><?php echo $this->lang->line('sales_discount').$this->lang->line('common_space').$this->lang->line('sales_discount_percentage');?></td>
                                    <td class="pos-totals-value zone_champ_saisie"><?php echo form_input(array(
                                        'name'  => 'overall_discount_percentage',
                                        'id'    => 'overall_discount_percentage',
                                        'value' => $_SESSION['CSI']['SHV']->overall_discount,
                                        'style' => 'text-align:right',
                                        'size'  => '5',
                                        'class' => 'champ_saisie'
                                    )); ?></td>
                                </tr>
                                </form>
                                <?php
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>

            <!-- Amount Due / Tendered Card (visible only when payments exist) -->
            <?php if (!empty($_SESSION['CSI']['PD'])): ?>
            <div class="pos-amount-card">
                <div class="pos-amount-body">
                    <table class="pos-amount-table">
                        <tr class="<?php echo (round($_SESSION['CSI']['SHV']->header_valueAD_TTC, 2) == round($_SESSION['CSI']['SHV']->header_payments_TTC, 2)) ? 'pos-amount-balanced' : 'pos-amount-due'; ?>">
                            <td class="pos-amount-label"><?php echo $this->lang->line('sales_amount_due').':'; ?></td>
                            <td class="pos-amount-value"><?php echo to_currency($_SESSION['CSI']['SHV']->header_amount_due_TTC); ?></td>
                        </tr>
                        <tr class="pos-amount-tendered">
                            <td class="pos-amount-label"><?php echo $this->lang->line('sales_amount_tendered').':' ?></td>
                            <td class="pos-amount-value"><?php echo to_currency($_SESSION['CSI']['SHV']->header_payments_TTC); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="pos-actions" id="finish_sale">
                <?php
                if (!isset($_SESSION['CSI']['SHV']->cart_in_error))
                {
                    // Quick payment buttons (top 3 payment methods)
                    if (!empty($_SESSION['CSI']['QUICK_PM']))
                    {
                        $pm_icons = array(
                            'CARDSC' => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/><path d="M12 4v-2m-3 2v-1m6 2v-1"/></svg>',
                            'CARD'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/><path d="M6 16h4"/></svg>',
                            'CASH'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="5" width="22" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M5 8v0m14 0v0m-14 8v0m14 0v0"/></svg>',
                            'CHEQ'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M6 14h8m-8 3h5"/><path d="M17 10l2 2-2 2"/></svg>',
                            'BANK'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M3 10h18M12 3l9 7H3l9-7z"/><path d="M5 10v8m4-8v8m6-8v8m4-8v8"/></svg>',
                            'GIFT'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="10" width="18" height="11" rx="1"/><rect x="1" y="6" width="22" height="4" rx="1"/><path d="M12 6v15"/><path d="M12 6c-1-2-4-4-6-2s0 4 6 2m0 0c1-2 4-4 6-2s0 4-6 2"/></svg>',
                            'FIDE'   => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
                        );
                        $default_icon = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';

                        echo '<div class="pos-quick-pay">';
                        foreach ($_SESSION['CSI']['QUICK_PM'] as $pm)
                        {
                            $code = $pm['payment_method_code'];
                            $icon = isset($pm_icons[$code]) ? $pm_icons[$code] : $default_icon;
                            $desc = htmlspecialchars($pm['payment_method_description']);
                            $pmid = $pm['payment_method_id'];
                            echo '<button type="button" class="pos-quick-pay-btn" data-pm-id="'.$pmid.'" title="'.$desc.'">';
                            echo $icon;
                            echo '<span>'.$desc.'</span>';
                            echo '</button>';
                        }
                        // 4th button: open full payment dialog (existing behavior)
                        echo '<button type="button" class="pos-quick-pay-btn pos-quick-pay-more" id="btn_add_payment" title="'.$this->lang->line('sales_add_payment').'">';
                        echo '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8m-4-4h8"/></svg>';
                        echo '<span>'.$this->lang->line('sales_add_payment').'</span>';
                        echo '</button>';
                        echo '</div>';
                    }

                    // Hidden form for "Ajouter Paiement" (full dialog) — submitted by 4th button
                    echo form_open($_SESSION['controller_name'].'/payments', array('id'=>'finish_sale_form', 'style'=>'display:none'));
                    echo form_submit(array('name'=>'submit','id'=>'submit_payments','value'=>$this->lang->line('sales_add_payment')));
                    echo form_close();

                    // Hidden form for quick pay — submitted by top 3 buttons
                    echo form_open($_SESSION['controller_name'].'/add_payment', array('id'=>'quick_pay_form', 'style'=>'display:none'));
                    echo form_hidden('payment_method_id', '');
                    echo form_hidden('amount_tendered', '');
                    echo form_close();
                }
                ?>

                <div class="pos-bottom-row">
                    <?php
                    echo form_open($_SESSION['controller_name'].'/confirm/suspend', array('class'=>'pos-suspend-form'));
                    echo form_submit(array(
                        'name'  => 'submit',
                        'id'    => 'submit',
                        'value' => $this->lang->line('sales_suspend_sale').$mode_short_text,
                        'class' => 'big_button_suspend'
                    ));
                    echo form_close();

                    // Fidelity button: show only if customer has fidelity points > 0
                    if (isset($_SESSION['CSI']['CI']->fidelity_flag)
                        && $_SESSION['CSI']['CI']->fidelity_flag == 'Y'
                        && isset($_SESSION['CSI']['SHV']->fidelity_value)
                        && $_SESSION['CSI']['SHV']->fidelity_value > 0
                        && !isset($_SESSION['CSI']['SHV']->cart_in_error))
                    {
                        // Find fidelity payment method id from all active methods
                        $fide_pm_id = 0;
                        foreach ($this->Sale->get_payment_methods() as $pm) {
                            if ($pm['payment_method_fidelity_flag'] == 'Y') {
                                $fide_pm_id = $pm['payment_method_id'];
                                break;
                            }
                        }
                        if ($fide_pm_id > 0) {
                    ?>
                        <button type="button" class="pos-fidelity-btn" id="btn_fidelity_pay"
                                data-pm-id="<?php echo $fide_pm_id; ?>"
                                data-amount="<?php echo round($_SESSION['CSI']['SHV']->fidelity_value, 2); ?>"
                                title="<?php echo $_SESSION['CSI']['CI']->fidelity_points.' pts = '.$_SESSION['CSI']['SHV']->fidelity_value.$_SESSION['CSI']['CuI']->currency_sign; ?>">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span><?php echo $_SESSION['CSI']['SHV']->fidelity_value.$_SESSION['CSI']['CuI']->currency_sign; ?></span>
                        </button>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>

        </div>
        <?php
        }
        ?>

        <?php if (!empty($_SESSION['CSI']['TOP'])): ?>
        <!-- Top Produits Client -->
        <div class="pos-top-items-card">
            <div class="pos-top-items-header">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Produits habituels
            </div>
            <div class="pos-top-items-grid">
                <?php foreach ($_SESSION['CSI']['TOP'] as $top_item): ?>
                <a href="#" class="pos-top-item" data-item-id="<?php echo $top_item->item_id; ?>">
                    <span class="pos-top-item-name"><?php echo htmlspecialchars($top_item->name); ?></span>
                    <span class="pos-top-item-ref"><?php echo htmlspecialchars($top_item->item_number); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Image Slider -->
        <div class="pos-slider-card">
            <div id="slider">
                <?php $cache_buster = isset($_SESSION['slides_sync_time']) ? '?v=' . $_SESSION['slides_sync_time'] : ''; ?>
                <figure>
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide1.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide2.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide3.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide4.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide5.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide6.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide7.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide8.png'.$cache_buster;?>">
                    <img src="<?php echo base_url().'SLIDES_VENTES/slide9.png'.$cache_buster;?>">
                </figure>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- end .pos-sidebar -->

    <!-- ======================================== -->
    <!-- CUSTOMER HISTORY (Full Width)            -->
    <!-- ======================================== -->
    <div class="pos-history">
        <div id="table_holder">
            <table class="tablesorter report table table-striped table-bordered" id="sortable_table">
                <thead>
                    <tr>
                        <th>+</th>
                        <?php foreach ($_SESSION['CSI']['HH']['summary'] as $header) { ?>
                        <th><?php echo $header; ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $pieces = array();
                    $pieces = explode("/", $this->config->item('numberformat'));
                    ?>

                    <?php foreach ($_SESSION['CSI']['HS'] as $key=>$row) { ?>
                    <tr>
                        <td><a href="#" class="expand">+</a></td>
                        <?php foreach ($row as $cell)
                        {
                            if (is_numeric($cell))
                            {
                                $cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
                                ?>
                                <td style="text-align:right">
                                <?php
                            }
                            else
                            {
                                ?>
                                <td style="text-align:left">
                                <?php
                            }
                            echo $cell;
                            ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td colspan="15">
                        <table class="innertable tablesorter report table table-striped table-bordered" style="width: 90%;">
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
                                        if (is_numeric($cell))
                                        {
                                            $cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
                                            ?>
                                            <td style="text-align:right">
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <td style="text-align:left">
                                            <?php
                                        }
                                        echo $cell;
                                        ?>
                                        </td>
                                    <?php } ?>
                                </tr>
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

</div><!-- end .pos-layout -->


<!-- show the footer -->
<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>


<?php
// Show modals
switch ($_SESSION['show_dialog'])
{
    case 1:
        include('../wrightetmathon/application/views/sales/suspended.php');
        break;
    case 2:
        include('../wrightetmathon/application/views/sales/confirm.php');
        break;
    case 3:
        include('../wrightetmathon/application/views/sales/payments.php');
        break;
    case 4:
        include('../wrightetmathon/application/views/sales/CN_select_invoice.php');
        break;
    case 5:
        include('../wrightetmathon/application/views/sales/CN_select_invoice_items.php');
        break;
    case 6:
        include('../wrightetmathon/application/views/sales/payments_avoir.php');
        break;
    case 7:
        include('../wrightetmathon/application/views/sales/multi_connexion.php');
        break;
    default:
        break;
}
?>


<!-- div for spinner -->
<div id="spinner" class="spinner" style="display:none;">
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

$(document).on('click', '.pos-top-item', function(e) {
    e.preventDefault();
    $('#input_qty').val('1');
    $('#item').val($(this).data('item-id'));
    $('#add_item_form').submit();
});

// Quick pay: top 3 payment method buttons — pay full amount due
$(document).on('click', '.pos-quick-pay-btn:not(.pos-quick-pay-more)', function(e) {
    e.preventDefault();
    var pmId = $(this).data('pm-id');
    var amount = '<?php echo isset($_SESSION['CSI']['SHV']->header_amount_due_TTC) ? round($_SESSION['CSI']['SHV']->header_amount_due_TTC, 2) : 0; ?>';
    if (parseFloat(amount) == 0) return;
    $('#quick_pay_form input[name="payment_method_id"]').val(pmId);
    $('#quick_pay_form input[name="amount_tendered"]').val(amount);
    $('#quick_pay_form').submit();
});

// 4th button: open full payment dialog
$(document).on('click', '#btn_add_payment', function(e) {
    e.preventDefault();
    $('#finish_sale_form').submit();
});

// Fidelity button: pay with fidelity points
$(document).on('click', '#btn_fidelity_pay', function(e) {
    e.preventDefault();
    var pmId = $(this).data('pm-id');
    var amount = $(this).data('amount');
    if (parseFloat(amount) <= 0) return;
    $('#quick_pay_form input[name="payment_method_id"]').val(pmId);
    $('#quick_pay_form input[name="amount_tendered"]').val(amount);
    $('#quick_pay_form').submit();
});

// Auto-submit cart line: click the row's edit_item button to submit
// (closest('form') doesn't work because <form> inside <table> is invalid HTML
//  and the browser moves the <form> tags out of <tbody>)
var _autoEditTimer = null;
function autoEditSubmit($input) {
    var orig = $input.data('orig');
    if (typeof orig !== 'undefined' && $input.val() == orig) return;
    $input.closest('tr').find('input[name="edit_item"]').click();
}
$(document).on('change', 'input.pos-auto-edit, select.pos-auto-edit', function() {
    clearTimeout(_autoEditTimer);
    autoEditSubmit($(this));
});
// Spinner buttons (up/down) fire 'input' not 'change' — debounce 600ms
$(document).on('input', 'input[type=number].pos-auto-edit', function() {
    var $input = $(this);
    clearTimeout(_autoEditTimer);
    _autoEditTimer = setTimeout(function() { autoEditSubmit($input); }, 600);
});
$(document).on('keydown', 'input.pos-auto-edit', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        clearTimeout(_autoEditTimer);
        autoEditSubmit($(this));
    }
});

</script>


<style>
    div#slider{
        width:80%;
        max-width:1000px;
        overflow:hidden;
    }
    div#slider figure{
        position:relative;
        width:900%;
        margin:0;
        padding:0;
        font-size:0;
        left:0;
        text-align:left;
        animation: 45s slidy infinite;
    }
    div#slider figure img {
        width:11.111%;
        height:auto;
        float:left;
    }

    @keyframes slidy{
        0% {left:0%;}
        10% {left:0%;}
        11.11% {left:-100%;}
        21% {left:-100%;}
        22.22% {left:-200%;}
        32% {left:-200%;}
        33.33% {left:-300%;}
        43% {left:-300%;}
        44.44% {left:-400%;}
        54% {left:-400%;}
        55.55% {left:-500%;}
        65% {left:-500%;}
        66.66% {left:-600%;}
        76% {left:-600%;}
        77.77% {left:-700%;}
        87% {left:-700%;}
        88.88% {left:-800%;}
        99% {left:-800%;}
        100% {left:0%;}
    }
</style>
