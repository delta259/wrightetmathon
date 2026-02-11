<!-- -->
<!-- Dispay the configuration screen -->
<!-- -->

<!-- output the header -->
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>

<div id="wrapper" class="wlp-bighorn-book">

    <?php $this->load->view("partial/header_menu"); ?>

    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">


                <!--Contenu background gris-->
                <div class="body_page" id="loginPage">
                    <div>

                        <div class="body_colonne">
                            <h2><?php echo $this->lang->line('config_info'); ?></h2>
                            <div class="body_cadre_gris">

<!-- output messages -->
<?php
	if($error != '')
	{
		echo "<div class='error_message'>".$error."</div>";
	}

	if($success != '')
	{
		echo "<div class='success_message'>".$success."</div>";
	}
?>

<!-- set up the input form -->
<?php
echo form_open('config/save/', array('id'=>'config_form'));
?>

                                <style>
                                    h2,a
                                    {
                                        color: #3b9abf;
                                    }
                                    h2,a:hover
                                    {
                                        color: #024b84;
                                    }
                                </style>


                                <!-- set up error message -->
<div id="config_wrapper">

    <div class="blocformfond creationimmediate">

        <div>
	<ul id="error_message_box"></ul>
            <fieldset>
	<div id="config_info">

        <!-- output custom fields -->
        <div class="config_row clearfix">
            <?php echo form_label('Enseigne Y/S:', 'website',array('class'=>'Fwide')); ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'custom1_name',
                'id'=>'custom1_name',
                'value'=>$this->config->item('custom1_name')));?>
                <a class="btaide" id="" title="<?php echo 'Enseigne YesStore/Sonrisa ?'; ?>"></a>

			</span>
        </div>
        <br/>

		<!-- output company name -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_company').':   ', 'company',array('class'=>'Fwide required')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'company',
				'id'=>'company',
				'size'=>'60',
                'class'=>'colorobligatoire',
				'value'=>$this->config->item('company')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_company'); ?>"></a>
			</span>
		</div>
<br/>
        <!-- output Siret -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_company_registration_number').':  ', 'siret',array('class'=>'Fwide required')); ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'siret',
                'id'=>'siret',
                'size'=>'50',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('siret')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_company_registration_number'); ?>"></a>

            </span>
        </div>
        <br/>
        <!-- output tva -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_company_tva_number').':  ', 'tva',array('class'=>'Fwide required')); ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'tva',
                'id'=>'tva',
                'size'=>'50',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('tva')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_company_tva_number'); ?>"></a>

            </span>
        </div>


        <br/>
		<!-- output company address -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_address').':   ', 'address',array('class'=>'Fwide required')); ?>
			<div class='zone_champ_saisie '>
			<?php echo form_textarea(array(
				'name'=>'address',
				'id'=>'address',
                'class'=>'colorobligatoire',
				'rows'=>4,
				'cols'=>80,
				'value'=>$this->config->item('address')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_company_registration_number'); ?>"></a>

            </div>
		</div>
<br/>
		<!-- output company phone number -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_phone').':', 'phone',array('class'=>'Fwide required')); ?>
			<span class='zone_champ_saisie '>
			<?php echo form_input(array(
				'name'=>'phone',
                'class'=>'colorobligatoire',
				'id'=>'phone',
				'value'=>$this->config->item('phone')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_phone'); ?>"></a>

			</span>
		</div>

		<!--<!-- output company fax number
		<div class="config_row clearfix">
		<?php /* echo form_label($this->lang->line('config_fax').':', 'fax',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'fax',
				'id'=>'fax',
				'value'=>$this->config->item('fax')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_fax');*/ ?>"></a>

			</span>
		</div> -->
        <!-- output opening date -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_opened').':', 'branch_opened',array('class'=>'Fwide required')); ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'branch_opened',
                'id'=>'branch_opened',
                'size'=>'10',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('branch_opened'))); ?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>
			</span>
        </div>
        <br/>
<br/>
		<!-- output company email -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('common_email').':', 'email',array('class'=>'Fwide required')); ?>
			<span class='zone_champ_saisie '>
			<?php echo form_input(array(
				'name'=>'email',
				'id'=>'email',
                'class'=>'colorobligatoire',
				'size'=>'50',
				'value'=>$this->config->item('email')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

			</span>
		</div>
<br/>
		<!-- output company web site -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_website').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'website',
				'id'=>'website',
				'size'=>'50',
				'value'=>$this->config->item('website')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

			</span>
		</div>
<br/>
		<!-- output shop open hours -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_shop_open_hours').':', 'open_hours',array('class'=>'Fwide required')); ?>
			<div class='zone_champ_saisie'>
			<?php echo form_textarea(array(
				'name'=>'open_hours',
				'id'=>'open_hours',
				'rows'=>4,
                'class'=>'colorobligatoire',
				'cols'=>80,
				'value'=>$this->config->item('open_hours')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

            </div>
		</div>
    <br/>
        <!-- output flash_info_displays, ie the number of signons for which a changed flash_info is to be displayed -->


        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_flash_info_displays').':', 'flash_info_displays', array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array	(
                    'name'=>'flash_info_displays',
                    'value'=>$this->config->item('flash_info_displays')
                )
            );?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

				</span>
        </div>
    </div>
            <br/>

        <!-- output enter alarm OK code -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_Alarm_OK_code').':', 'Alarm_OK_code',array('class'=>'Fwide')) ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'Alarm_OK_code',
                'id'=>'Alarm_OK_code',
                'value'=>$this->config->item('Alarm_OK_code')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

			</span>
        </div>

        <!-- output enter alarm NOT OK code -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_Alarm_KO_code').':', 'Alarm_KO_code',array('class'=>'Fwide')) ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'Alarm_KO_code',
                'id'=>'Alarm_KO_code',
                'value'=>$this->config->item('Alarm_KO_code')));?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

			</span>
        </div>
        <br/>
        <!-- output system language -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_language').':', 'language',array('class'=>'Fwide required')); ?>
            <span class='zone_champ_saisie'>
			<?php echo form_dropdown('language', array(
                'Azerbaijan'		=> 'Azerbaijan',
                'BahasaIndonesia'	=> 'BahasaIndonesia',
                'English'			=> 'English',
                'French'			=> 'French',
                'Spanish'			=> 'Spanish',
                'Russian'			=> 'Russian'

            ),
                $this->config->item('language'),	'class="colorobligatoire"');
            ?>
                <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

			</span>
        </div>
            <!-- output currency -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('currencies_currency_name').':', 'currency', array('class'=>'Fwide required')); ?>
                <span class='zone_champ_saisie '>
			<?php echo form_dropdown	(
                'currency',
                $_SESSION['G']->currency_pick_list,
                $this->config->item('currency'),
                'style="text-align:center; font-size:16px"','class="colorobligatoire"'
            );?>
                    <a class="btaide" id="" title="<?php echo $this->lang->line('config_opened'); ?>"></a>

			</span>
            </div>
            <br/>


        <!---partie 1--->






	<!-- Messages -->

        <h2 id="message_title" style="border-bottom: solid 0px #2B84BF;"><a><?php echo 'Configuration du Ticket de caisse'; ?></a></h2>
                <div id="message_div">
		<!-- Polite message -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_polite_message').':', 'polite_message',array('class'=>'Fwide')); ?>
			<div class='zone_champ_saisie'>
			<?php echo form_textarea(array(
				'name'=>'polite_message',
				'id'=>'polite_message',
				'rows'=>'10',
				'cols'=>'30',
				'value'=>$this->config->item('polite_message')));?>
			</div>
		</div>

		<!-- Season Message -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_season_message').':', 'season_message',array('class'=>'Fwide')); ?>
			<div class='zone_champ_saisie'>
			<?php echo form_textarea(array(
				'name'=>'season_message',
				'id'=>'season_message',
				'rows'=>'10',
				'cols'=>'30',
				'value'=>$this->config->item('season_message')));?>
			</div>
		</div>

		<!-- Fidelity Message -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_fidelity_message').':', 'fidelity_message',array('class'=>'Fwide')); ?>
			<div class='zone_champ_saisie'>
			<?php echo form_textarea(array(
				'name'=>'fidelity_message',
				'id'=>'fidelity_message',
				'rows'=>'10',
				'cols'=>'30',
				'value'=>$this->config->item('fidelity_message')));?>
			</div>
		</div>

		<!-- Return Policy -->
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('common_return_policy').':', 'return_policy',array('class'=>'Fwide required')); ?>
			<div class='zone_champ_saisie '>
			<?php echo form_textarea(array(
				'name'=>'return_policy',
				'id'=>'return_policy',
				'rows'=>'10',
				'cols'=>'30',
                'class'=>'colorobligatoire',
				'value'=>$this->config->item('return_policy')));?>
			</div>
		</div>
	</div>
            <!-- cashtill configuration -->

                <h2 id="cashtill_title" style="border-bottom: solid 0px #2B84BF;">
                   <a> <?php echo 'Configuration de la caisse'; ?></a>
                </h2>
<div id="cashtill_div">
                <!-- output cashtill check total -->
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_cashtill_check_total').':', 'cashtill_check_total',array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie'>
					<?php echo form_dropdown	(
                        'cashtill_check_total',
                        $_SESSION['G']->YorN_pick_list,'class="colorobligatoire"',
                        $this->config->item('cashtill_check_total')
                    ); ?>
					</span>
                </div>

                <!-- output cashtill total -->
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_cashtill_total').':', 'cashtill_total',array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie '>
					<?php echo form_input	(	array	(
                        'name'	=>	'cashtill_total',
                        'id'	=>	'cashtill_total',
                        'style'	=>	'text-align:right',
                        'size'	=>	6,
                        'class'=>'colorobligatoire',
                        'value'	=>	$this->config->item('cashtill_total')
                    ));?>
					</span>
                </div>

                <!-- output cashtill allow correction -->
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_cashtill_allow_correction').':', 'cashtill_allow_correction',array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie '>
					<?php echo form_dropdown	(
                        'cashtill_allow_correction',
                        $_SESSION['G']->YorN_pick_list,
                        $this->config->item('cashtill_allow_correction'),'class="colorobligatoire"'
                    );?>
					</span>
                </div>
                <br/>
                <!-- output cashtill notification email -->
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_cashtill_notification_email').':', 'cashtill_notification_email',array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie '>
					<?php echo form_input	(	array	(
                        'name'	=>	'cashtill_notification_email',
                        'id'	=>	'cashtill_notification_email',
                        'style'	=>	'text-align:left',
                        'size'	=>	60,
                        'class'=>'colorobligatoire',
                        'value'	=>	$this->config->item('cashtill_notification_email')
                    ));?>
					</span>
                </div>

                <!-- output cashtill notification email -->
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_cashtill_notification_password').':', 'cashtill_notification_password',array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie' align="center">
					<?php echo form_password	(	array	(
                        'name'	=>	'cashtill_notification_password',
                        'id'	=>	'cashtill_notification_password',
                        'style'	=>	'text-align:left',
                        'size'	=>	60,
                        'class'=>'colorobligatoire',
                        'value'	=>	$this->config->item('cashtill_notification_password')
                    ));?>
					</span>
                </div>
            </div>


                <h2 id="config_fidelity_title" style="border-bottom: solid 0px #2B84BF;">
                    <a style=""><?php echo $this->lang->line('config_fidelity'); ?></a>
                </h2>
                <!-- fidelity parameters -->
                <div id="config_fidelity_div">
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_fidelity_rule_1').':', 'fidelity_rule', array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'fidelity_rule',
                'id'=>'fidelity_rule',
                'style'	=>	'text-align:right',
                'size'	=>	6,
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('fidelity_rule')));
            echo $currency_info->currency_sign.$this->lang->line('common_space').$this->lang->line('sales_TTC').$this->lang->line('common_space').$this->lang->line('config_fidelity_rule_2');?>
			</span>
                </div>
                <br/>
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_fidelity_value').':', 'fidelity_value', array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'fidelity_value',
                'id'=>'fidelity_value',
                'style'	=>	'text-align:right',
                'size'	=>	6,
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('fidelity_value')));
            echo $currency_info->currency_sign.$this->lang->line('common_space').$this->lang->line('sales_TTC');?>
			</span>
                </div>
                <br/>
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_fidelity_minimum').':', 'fidelity_minimum', array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'fidelity_minimum',
                'id'=>'fidelity_minimum',
                'style'	=>	'text-align:right',
                'size'	=>	6,
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('fidelity_minimum')));
            echo $currency_info->currency_sign.$this->lang->line('common_space').$this->lang->line('sales_TTC');?>
			</span>
                </div>
                <br/>
                <div class="config_row clearfix">
                    <?php echo form_label($this->lang->line('config_fidelity_maximum').': ', 'fidelity_maximum', array('class'=>'Fwide required')) ?>
                    <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'	=>	'fidelity_maximum',
                'id'	=>	'fidelity_maximum',
                'style'	=>	'text-align:right',
                'size'	=>	6,
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('fidelity_maximum')));
            echo $currency_info->currency_sign.$this->lang->line('common_space').$this->lang->line('sales_TTC');?>
			</span>
                </div>

</div>

            <h2 id="config_supplier_title" style="border-bottom: solid 0px #2B84BF;"><a><?php echo 'Configuration Commande fournisseur'; ?></a></h2>

<div id="config_supplier_div">
            <!-- output email for POs -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_POemail').':', 'POemail',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'POemail',
                'id'=>'POemail',
                'class'=>'colorobligatoire',
                'size'=>'60',
                'value'=>$this->config->item('POemail')));?>
			</span>
            </div>

            <!-- output password for email for POs -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_POemailpwd').':', 'POemailpwd',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'POemailpwd',
                'id'=>'POemailpwd',
                'class'=>'colorobligatoire',
                'size'=>'60',
                'value'=>$this->config->item('POemailpwd')));?>
			</span>
            </div>
            <br/>
            <!-- output message for email for POs -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_POemailmsg').':', 'POemailmsg',array('class'=>'Fwide required')) ?>
                <div class='zone_champ_saisie'>
                    <?php echo form_textarea(array(
                        'name'=>'POemailmsg',
                        'id'=>'POemailmsg',
                        'rows'=>'5',
                        'class'=>'colorobligatoire',
                        'cols'=>'70',
                        'value'=>$this->config->item('POemailmsg')));?>
                </div>
            </div>
</div>

<!-- //affichage dans un tableau pour que tout soit plus structuré -->
            <h2 style="border-bottom: solid 0px #2B84BF;" id="config_default_title"><a><?php echo 'Configuration des parametres par defaut'; ?></a></h2>
                <div id="config_default_div">
                    <style>
                    .espace
                    {
                     height : 10px;
                    }
                    </style>
        <table>    
        <tr>
            <!-- output use DLUO -->
            <div class="config_row clearfix">
                <td align="left" >
                <?php echo form_label($this->lang->line('config_use_DLUO').':', 'use_DLUO',array('class'=>'Fwide required')) ?>
                </td>
                <td align="left" >
                <span class='zone_champ_saisie ' >
			<?php echo form_dropdown	(
                'use_DLUO',
                $_SESSION['G']->YorN_pick_list,
                $this->config->item('use_DLUO'),'class="colorobligatoire"'
            );?>
            </span>
            </td>
            </div>
        </tr>
        <tr><td class="espace" ></td></tr>
      <tr>
            <!-- output price list -->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('pricelists_pricelist_name').' '.$this->lang->line('common_default').':', 'pricelist', array('class'=>'Fwide required')); ?>
            </td>
            <td align="left" >
                <span class='zone_champ_saisie '>
			<?php echo form_dropdown	(
                'pricelist_id',
                $_SESSION['G']->pricelist_pick_list,
                $this->config->item('pricelist_id'),

                'style="text-align:center; font-size:16px"','class="colorobligatoire"'
            );?>
            </span>
            </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
            <!-- output customer profile -->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('customer_profiles_profile_name').' '.$this->lang->line('common_default').':', 'profile', array('class'=>'Fwide required')); ?>
            </td>
            <td align="left" >
                <span class='zone_champ_saisie'>
			<?php echo form_dropdown	(
                'profile_id',
                $_SESSION['G']->profile_pick_list,
                $this->config->item('profile_id'),
                'style="text-align:center; font-size:16px"','class="colorobligatoire"'
            );?>
            </span>
            </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
            <!-- output default client id -->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('config_default_client_id').':', 'default_client_id',array('class'=>'Fwide required')) ?>
            </td>
            <td align="left" >
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'default_client_id',
                    'id'=>'default_client_id',
                    'size'=>'5',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('default_client_id')));?>
                </span>
            </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
            <!-- person show comments -->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('config_person_show_comments').': ', 'person_show_comments', array('class'=>'Fwide required')) ?>
            </td>
            <td align="left" >
                    <span class='zone_champ_saisie ' >
					<?php echo form_dropdown	(
                        'person_show_comments',
                        $_SESSION['G']->YorN_pick_list,
                        $this->config->item('person_show_comments'),'class="colorobligatoire"'
                    );?>
                    </span>
                </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
            <!-- output default supplier id -->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('config_default_supplier_id').':', 'default_supplier_id',array('class'=>'Fwide required')) ?>
            </td>
            <td align="left" >    
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'default_supplier_id',
                    'id'=>'default_supplier_id',
                    'size'=>'5',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('default_supplier_id')));?>
                </span>
            </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
            <!-- output no supplier id -->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('config_no_supplier_id').':', 'no_supplier_id',array('class'=>'Fwide required')) ?>
            </td>
            <td align="left" >  
                <span class='zone_champ_saisie'>
				<?php echo form_input(array(
                    'name'=>'no_supplier_id',
                    'id'=>'no_supplier_id',
                    'size'=>'5',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('no_supplier_id')));?>
                </span>
            </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
            <!-- output defaut warehouse code-->
            <div class="config_row clearfix">
            <td align="left" >
                <?php echo form_label($this->lang->line('config_default_warehouse_code').':', 'default_warehouse_code',array('class'=>'Fwide required')) ?>
            </td>
            <td align="left" >
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'default_warehouse_code',
                    'id'=>'default_warehouse_code',
                    'size'=>'5',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('default_warehouse_code')));?>
                </span>
            </td>
            </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
        <?php 
        /* $_SESSION['historique']=$this->config->item('historique');
        if(!isset($_SESSION['historique']) || ($_SESSION['historique']==""))
        { 
            $_SESSION['historique']=45;
        ?>
        <!-- Configuration historique -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_historique').':', 'config_historique',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'historique',
                'id'=>'historique',
                'class'=>'colorobligatoire',
                'value'=>'45')); echo "(en jour)" ?>
			</span>
        </div>
        <?php
        }
        else
        { //*/
            $_SESSION['historique']=$this->config->item('historique');
            ?>
        <!-- Configuration historique -->
        <div class="config_row clearfix">
        <td align="left" >
            <?php echo form_label($this->lang->line('config_historique').':', 'config_historique',array('class'=>'Fwide required')) ?>
        </td>
        <td align="left" >
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'historique',
                'id'=>'historique',
                'size' => '5', 
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('historique'))); echo "(en jour)" ?>
            </span>
        </td>
        </div>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
  <?php //} ?>
        <?php /*
        $_SESSION['nbre_jour_prevision_stock']=$this->config->item('nbre_jour_prevision_stock');        
        if(!isset($_SESSION['nbre_jour_prevision_stock']) || ($_SESSION['nbre_jour_prevision_stock']==""))
        { 
            $_SESSION['nbre_jour_prevision_stock']=20;
        ?>
        <br/>
        <!-- Configuration nombre de jour de stockage -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_nbre_jour_prevision_stock').':', 'nbre_jour_prevision_stock',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'nbre_jour_prevision_stock',
                'id'=>'nbre_jour_prevision_stock',
                'class'=>'colorobligatoire',
                'value'=>'20')); echo "(en jour)" ?>
			</span>
        </div>
        <?php
        }
        else
        { 
            $_SESSION['nbre_jour_prevision_stock']=$this->config->item('nbre_jour_prevision_stock'); //*/
            ?>
        <!-- Configuration nombre de jour de stockage -->
        <div class="config_row clearfix">
        <td align="left" >
            <?php echo form_label($this->lang->line('config_nbre_jour_prevision_stock').':', 'nbre_jour_prevision_stock',array('class'=>'Fwide required')) ?>
        </td>
        <td align="left" >
            <span class='zone_champ_saisie '>
            <?php echo form_input(array(
                'name'=>'nbre_jour_prevision_stock',
                'id'=>'nbre_jour_prevision_stock',
                'size' => '5',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('nbre_jour_prevision_stock'))); echo "(en jour)" ?>
            </span>
        </td>
        </div> 
        </tr>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
        <!-- Configuration multi vendeur -->
        <div class="config_row clearfix">
        <td align="left" >
            <?php echo form_label($this->lang->line('config_multi_vendeur').':', 'multi_vendeur',array('class'=>'Fwide required')) ?>
        </td>
        <td align="left" >
            <span class='zone_champ_saisie '>
            <?php echo form_dropdown(
                'multi_vendeur',
                $_SESSION['G']->YorN_pick_list,
                $this->config->item('multi_vendeur'),
                'class="colorobligatoire"'); ?>
            </span>
        </td>
        </div> 
        </tr>
        </tr><tr><td class="espace" ></td></tr>
        <tr>
        <!-- Configuration multi vendeur -->
        <div class="config_row clearfix">
        <td align="left" >
            <?php echo form_label($this->lang->line('config_distributeur_vapeself').':', 'distributeur_vapeself',array('class'=>'Fwide required')) ?>
        </td>
        <td align="left" >
            <span class='zone_champ_saisie '>
            <?php echo form_dropdown(
                'distributeur_vapeself',
                $_SESSION['G']->YorN_pick_list,
                $this->config->item('distributeur_vapeself'),
                'class="colorobligatoire"'); ?>
            </span>
        </td>
        </div> 
        </tr>        
    
        </tr><tr><td class="espace" ></td></tr>
        <tr>
        <!-- Configuration multi vendeur -->
        <div class="config_row clearfix">
        <td align="left" >
            <?php echo form_label($this->lang->line('config_distributeur_vapeself_code').':', 'distributeur_vapeself_code',array('class'=>'Fwide required')) ?>
        </td>
        <td align="left" >
            <span class='zone_champ_saisie '>
            <?php echo form_input(array(
                'name'=>'distributeur_vapeself_code',
                'id'=>'distributeur_vapeself_code',
                'size' => '5',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('distributeur_vapeself_code'))); ?>
            </span>
        </td>
        </div> 
        </tr>
    
  <?php //} ?>
		<!-- output should the invoice / receipt be printed after transaction is completed -->
		<!-- Note that this option is used for all transactions (not just sales) -->
        </table>            </div>
    
            <h2 id="config_rep_title" style="border-bottom: solid 0px #2B84BF;"><a><?php echo 'Configuration des repertoires de stockage '; ?></a></h2>
            <div id="config_rep_div">
            <!-- output storage path to save purchase order info to -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_POsavepath').':', 'POsavepath',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'POsavepath',
                'id'=>'POsavepath',
                'size'=>'60',
                'value'=>$this->config->item('POsavepath')));?>
			</span>
            </div>

            <!-- output storage path to save reports info to -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_RPsavepath').':', 'RPsavepath',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'RPsavepath',
                'id'=>'RPsavepath',
                'class'=>'colorobligatoire',
                'size'=>'60',
                'value'=>$this->config->item('RPsavepath')));?>
			</span></div>

            <!-- output storage path to save backups to -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_BUsavepath').':', 'BUsavepath',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'BUsavepath',
                'id'=>'BUsavepath',
                'class'=>'colorobligatoire',
                'size'=>'60',
                'value'=>$this->config->item('BUsavepath')));?>
			</span>
            </div>
                </div>

				<h2 style="border-bottom: solid 0px #2B84BF;" id="etiquette_title">
				<a>	<?php echo 'Parametres des etiquettes'; ?></a>
				</h2>
                <div id="etiquette_div">
				<div class="config_row clearfix">
					<?php echo form_label($this->lang->line('config_label_font').':', 'default_label_font',array('class'=>'Fwide required')) ?>
					<span class='zone_champ_saisie'>
							<?php echo form_input	(	array	(
																'name'=>'default_label_font',
																'id'=>'default_label_font',
																'size'=>'60',
                                                                'class'=>'colorobligatoire',
																'value'=>$this->config->item('default_label_font')));?>
					</span>
				</div>

				<div class="config_row clearfix">
					<?php echo form_label($this->lang->line('config_label_image').':', 'default_label_image',array('class'=>'Fwide required')) ?>
					<span class='zone_champ_saisie '>
							<?php echo form_input	(	array	(
																'name'=>'default_label_image',
																'id'=>'default_label_image',
																'size'=>'60',
                                                                'class'=>'colorobligatoire',
																'value'=>$this->config->item('default_label_image')));?>
					</span>
				</div>

				<div class="config_row clearfix">
					<?php echo form_label($this->lang->line('config_label_store').':', 'default_label_store',array('class'=>'Fwide required')) ?>
					<span class='zone_champ_saisie'>
							<?php echo form_input	(	array	(
																'name'=>'default_label_store',
																'id'=>'default_label_store',
																'size'=>'60',
                                                                'class'=>'colorobligatoire',
																'value'=>$this->config->item('default_label_store')));?>
					</span>
				</div>

</div>


                <!-- output catalogue information -->
		<!--	<div class="fieldset">
                <h2 style="border-bottom: solid 0px #2B84BF;">
					<?php /* echo $this->lang->line('config_catalogue_parms'); ?>
				</h2>

				<div class="config_row clearfix">
					<?php echo form_label($this->lang->line('config_catalogue_name').':', 'catalogue_name',array('class'=>'Fwide required')) ?>
					<span class='zone_champ_saisie' >
							<?php echo form_input	(	array	(
																'name'=>'catalogue_name',
																'id'=>'catalogue_name',
																'size'=>'60',
                                                                'style'=>'',
                                                                'class'=>'colorobligatoire',
																'value'=>$this->config->item('catalogue_name')));?>
					</span>
				</div>

				<div class="config_row clearfix">
					<?php echo form_label($this->lang->line('config_catalogue_path').':', 'catalogue_path',array('class'=>'Fwide required')) ?>
					<span class='zone_champ_saisie'>
							<?php echo form_input	(	array	(
																'name'=>'catalogue_path',
																'id'=>'catalogue_path',
																'size'=>'60',
                                                                'class'=>'colorobligatoire',
																'value'=>$this->config->item('catalogue_path')));?>
					</span>
				</div>

				<div class="config_row clearfix">
					<?php echo form_label($this->lang->line('config_browser_download_folder').':', 'browser_download_folder',array('class'=>'Fwide required')) ?>
					<span class='zone_champ_saisie'>
							<?php echo form_input	(	array	(
																'name'=>'browser_download_folder',
																'id'=>'browser_download_folder',
																'size'=>'60',
                                                                 'class'=>'colorobligatoire',
																'value'=>$this->config->item('browser_download_folder')));*/?>
					</span>
				</div>
			</div>-->

        <h2 style="border-bottom: solid 0px #2B84BF;" id="tech_title"><a><?php echo 'Configuration Technique'; ?></a></h2>

            <div id="tech_div">
            <!-- output ticket printer name -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_ticket_printer').':', 'ticket_printer',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'ticket_printer',
                'id'=>'ticket_printer',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('ticket_printer')));?>
			</span>
            </div>
<br/>
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_print_after_sale').':', 'print_after_sale',array('class'=>'Fwide')); ?>
                <span class='zone_champ_saisie'>
			<?php echo form_checkbox(array(
                'name'=>'print_after_sale',
                'id'=>'print_after_sale',
                'value'=>'print_after_sale',
                'checked'=>$this->config->item('print_after_sale')));?>
			</span>
                <?php echo form_label($this->lang->line('config_print_receipt_for_categories').':', 'print_receipt_categories',array('class'=>'Fwide')) ?>
                <span class='zone_champ_saisie'>
			<?php echo form_input	(	array	(
                'name'=>'print_receipt_categories',
                'id'=>'print_receipt_categories',
                'value'=>$this->config->item('print_receipt_categories'))); echo $this->lang->line('config_print_receipt_for_categories_format');?>
			</span>
            </div>


            <br/>

        <!-- output default tax name and percentage -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_default_tax_rate_1').':', 'default_tax_1_rate',array('class'=>'Fwide required')); ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'default_tax_1_name',
                'id'=>'default_tax_1_name',
                'class'=>'colorobligatoire',
                'size'=>'10',
                'value'=>$this->config->item('default_tax_1_name')!==FALSE ? $this->config->item('default_tax_1_name') : $this->lang->line('items_sales_tax_1')));?>

                <?php echo form_input(array(
                    'name'=>'default_tax_1_rate',
                    'id'=>'default_tax_1_rate',
                    'size'=>'4',
                    'value'=>$this->config->item('default_tax_1_rate')));?>%
			</span>
        </div>
<br/>
        <!-- output default tax name and percentage -->
     <!--   <div class="config_row clearfix">
            <?php /* echo form_label($this->lang->line('config_default_tax_rate_2').':', 'default_tax_1_rate',array('class'=>'Fwide')); ?>
            <span class='zone_champ_saisie'>
			<?php echo form_input(array(
                'name'=>'default_tax_2_name',
                'id'=>'default_tax_2_name',
                'size'=>'10',
                'value'=>$this->config->item('default_tax_2_name')!==FALSE ? $this->config->item('default_tax_2_name') : $this->lang->line('items_sales_tax_2')));?>

                <?php echo form_input(array(
                    'name'=>'default_tax_2_rate',
                    'id'=>'default_tax_2_rate',
                    'size'=>'4',
                    'value'=>$this->config->item('default_tax_2_rate')));*/?>%
			</span>
        </div>
        <br/>-->
        <!-- output time zone -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_timezone').':', 'timezone',array('class'=>'Fwide required')); ?>
            <span class='zone_champ_saisie '>
			<?php echo form_dropdown	(
                'timezone',
                $_SESSION['G']->timezone_pick_list,
                $this->config->item('timezone'),
                'style="text-align:center; font-size:16px"','class="colorobligatoire"'
            );?>
			</span>
        </div>
        <br/>
        <!-- output date format -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_dateformat').':', 'dateformat',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'dateformat',
                'id'=>'dateformat',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('dateformat'))); echo ($this->lang->line('config_dateformatexample'));?>
			</span>
        </div>
        <br/>
        <!-- output time format -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_timeformat').':', 'timeformat',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'timeformat',
                'id'=>'timeformat',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('timeformat'))); echo ($this->lang->line('config_timeformatexample'));?>
			</span>
        </div>
        <br/>
        <!-- output number format -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_numberformat').':', 'numberformat',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'numberformat',
                'id'=>'numberformat',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('numberformat'))); echo ($this->lang->line('config_numberformatexample'));?>
			</span>
        </div>
        <br/>
        <?php 
        /* $_SESSION['historique']=$this->config->item('historique');
        if(!isset($_SESSION['historique']) || ($_SESSION['historique']==""))
        { 
            $_SESSION['historique']=45;
        ?>
        <!-- Configuration historique -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_historique').':', 'config_historique',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'historique',
                'id'=>'historique',
                'class'=>'colorobligatoire',
                'value'=>'45')); echo "(en jour)" ?>
			</span>
        </div>
        <?php
        }
        else
        { 
            $_SESSION['historique']=$this->config->item('historique');
            ?>
        <!-- Configuration historique -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_historique').':', 'config_historique',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'historique',
                'id'=>'historique',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('historique'))); echo "(en jour)" ?>
			</span>
        </div>
  <?php //} ?>
        <?php 
        $_SESSION['nbre_jour_prevision_stock']=$this->config->item('nbre_jour_prevision_stock');        
        if(!isset($_SESSION['nbre_jour_prevision_stock']) || ($_SESSION['nbre_jour_prevision_stock']==""))
        { 
            $_SESSION['nbre_jour_prevision_stock']=20;
        ?>
        <br/>
        <!-- Configuration nombre de jour de stockage -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_nbre_jour_prevision_stock').':', 'nbre_jour_prevision_stock',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
			<?php echo form_input(array(
                'name'=>'nbre_jour_prevision_stock',
                'id'=>'nbre_jour_prevision_stock',
                'class'=>'colorobligatoire',
                'value'=>'20')); echo "(en jour)" ?>
			</span>
        </div>
        <?php
        }
        else
        { 
            $_SESSION['nbre_jour_prevision_stock']=$this->config->item('nbre_jour_prevision_stock'); 
            ?>
        <br/>
        <!-- Configuration nombre de jour de stockage -->
        <div class="config_row clearfix">
            <?php echo form_label($this->lang->line('config_nbre_jour_prevision_stock').':', 'nbre_jour_prevision_stock',array('class'=>'Fwide required')) ?>
            <span class='zone_champ_saisie '>
            <?php echo form_input(array(
                'name'=>'nbre_jour_prevision_stock',
                'id'=>'nbre_jour_prevision_stock',
                'class'=>'colorobligatoire',
                'value'=>$this->config->item('nbre_jour_prevision_stock'))); echo "(en jour)" ?>
            </span>
        </div> 
  <?php //} 
        <br/>//*/ ?>
            <!-- output stock valuation create records -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_create_stock_valuation_records').':', 'createstockvaluationrecords',array('class'=>'Fwide')) ?>
                <span class='zone_champ_saisie'>
			<?php echo form_dropdown	(
                'createstockvaluationrecords',
                $_SESSION['G']->YorN_pick_list,
                $this->config->item('createstockvaluationrecords')
            );?>
			</span>
            </div>
            <br/>
            <!-- output category create records -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_create_category_records').':', 'createcategoryrecords',array('class'=>'Fwide')) ?>
                <span class='zone_champ_saisie' >
			<?php echo form_dropdown	(
                'createcategoryrecords',
                $_SESSION['G']->YorN_pick_list,
                $this->config->item('createcategoryrecords')
            );?>
			</span>
            </div>
            <br/>
            <!-- info for managing purchase price update -- >
        <!-- output storage path to read purchase prices from -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_PPsavepath').':', 'PPsavepath',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'PPsavepath',
                    'id'=>'PPsavepath',
                    'size'=>'60',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('PPsavepath')));?>
				</span>
            </div>

            <!-- output purchase price file name -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_PPfilename').':', 'PPfilename',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'PPfilename',
                    'id'=>'PPfilename',
                    'size'=>'60',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('PPfilename')));?>
				</span>
            </div>

            <!-- info for managing sales price update -- >
                <!-- output storage path to read purchase prices from -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_SPsavepath').':', 'SPsavepath',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'SPsavepath',
                    'id'=>'SPsavepath',
                    'class'=>'colorobligatoire',
                    'size'=>'60',
                    'value'=>$this->config->item('SPsavepath')));?>
				</span>
            </div>

            <!-- output purchase price file name -->
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_SPfilename').':', 'SPfilename',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie '>
				<?php echo form_input(array(
                    'name'=>'SPfilename',
                    'id'=>'SPfilename',
                    'size'=>'60',
                    'class'=>'colorobligatoire',
                    'value'=>$this->config->item('SPfilename')));?>
				</span>
            </div>

        </div>
      <!--      <!-- output import article database
            <div class="config_row clearfix">
                <?php /* echo form_label($this->lang->line('config_import_items_database').':', 'import_items_database',array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie ' align="center">
			<?php echo form_dropdown	(
                'import_items_database',
                $_SESSION['G']->YorN_pick_list,
                $this->config->item('import_items_database'),'class="colorobligatoire"'
            );?>
			</span>
            </div>
            <br/>
            <!-- output software_update
            <div class="config_row clearfix">
                <?php echo form_label($this->lang->line('config_software_update').':', 'software_update', array('class'=>'Fwide required')) ?>
                <span class='zone_champ_saisie' align="center"><?php echo form_dropdown	(
                        'software_update',
                        $_SESSION['G']->YorN_pick_list,
                        $this->config->item('software_update'),'class="colorobligatoire"'
                    ); echo $_SESSION['software_folder_name'];*/ ?>
			</span>
            </div>-->




            <h2 id="custom_title" style="border-bottom: solid 0px #2B84BF;" >
             <a><?php echo ('Configuration Custom'); ?></a> </h2>
        <div id="custom_div" style="display:;">
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom2').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom2_name',
				'id'=>'custom2_name',
				'value'=>$this->config->item('custom2_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom3').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom3_name',
				'id'=>'custom3_name',
				'value'=>$this->config->item('custom3_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom4').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom4_name',
				'id'=>'custom4_name',
				'value'=>$this->config->item('custom4_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom5').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom5_name',
				'id'=>'custom5_name',
				'value'=>$this->config->item('custom5_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom6').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom6_name',
				'id'=>'custom6_name',
				'value'=>$this->config->item('custom6_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom7').':', 'website',array('class'=>'Fwide')); ?>
			<sapn class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom7_name',
				'id'=>'custom7_name',
				'value'=>$this->config->item('custom7_name')));?>
			</sapn>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom8').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom8_name',
				'id'=>'custom8_name',
				'value'=>$this->config->item('custom8_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom9').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom9_name',
				'id'=>'custom9_name',
				'value'=>$this->config->item('custom9_name')));?>
			</span>
		</div>
        <br/>
		<div class="config_row clearfix">
		<?php echo form_label($this->lang->line('config_custom10').':', 'website',array('class'=>'Fwide')); ?>
			<span class='zone_champ_saisie'>
			<?php echo form_input(array(
				'name'=>'custom10_name',
				'id'=>'custom10_name',
				'value'=>$this->config->item('custom10_name')));?>
			</span>
		</div>
        </div>

	</div>

        <div id="required_fields_message" class="obligatoire">
            <a class="btobligatoire" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
            <?php echo $this->lang->line('common_fields_required_message'); ?>
        </div>
        </fieldset>
    </div>
</div>
        <div class="txt_droite"><!-- output the OK button -->
            <?php
            echo form_submit	(array	(
                    'name'		=>	'submit',
                    'id'		=>	'submit',
                    'value'		=>	$this->lang->line('common_submit'),
                    'class'		=>	'btsubmit'
                )
            );
            ?>
        </div>
<?php
echo form_close();
?>
<div id="feedback_bar"></div>

                                <div id="feedback_bar"></div>
                            </div>
                        </div>

                    </div>
                </div>

            </main></div></div>
</div>

<script >
    $(document).ready(function()
    {
        $('#etiquette_div').hide();
        $('#etiquette_title').click(function()
        {
            $('#etiquette_div').toggle();
        });

        $('#custom_div').hide();
        $('#custom_title').click(function()
        {
            $('#custom_div').toggle();
        });

        $('#tech_div').hide();
        $('#tech_title').click(function()
        {
            $('#tech_div').toggle();
        });

        $('#config_rep_div').hide();
        $('#config_rep_title').click(function()
        {
            $('#config_rep_div').toggle();
        });

        $('#config_default_div').hide();
        $('#config_default_title').click(function()
        {
            $('#config_default_div').toggle();
        });

        $('#config_supplier_div').hide();
        $('#config_supplier_title').click(function()
        {
            $('#config_supplier_div').toggle();
        });

        $('#config_fidelity_div').hide();
        $('#config_fidelity_title').click(function()
        {
            $('#config_fidelity_div').toggle();
        });

        $('#cashtill_div').hide();
        $('#cashtill_title').click(function()
        {
            $('#cashtill_div').toggle();
        });
        $('#message_div').hide();
        $('#message_title').click(function()
        {
            $('#message_div').toggle();
        });
    });
</script>


<script type='text/javascript'>

//validation and submit handling
$(document).ready
				(	function()
					{
						$('#config_form').validate
						(
							{
							submitHandler:function(form)
								{
									form.submit();
								},

							errorLabelContainer: "#error_message_box",
							wrapper: "li",

							rules:
								{
									branch_code: "required",
									branch_opened: "required",
									company: "required",
									address: "required",
									phone: "required",
									default_tax_rate:
									{
										required:true,
										number:true
									},
									email:"email",
									return_policy: "required",
									dateformat: "required",
									timeformat: "required",
									numberformat: "required",
									default_client_id: "required"
								},

							messages:
								{
									branch_code: "<?php  echo $this->lang->line('config_branch_required'); ?>",
									branch_opened: "<?php echo $this->lang->line('config_opened_required'); ?>",
									company: "<?php echo $this->lang->line('config_company_required'); ?>",
									address: "<?php echo $this->lang->line('config_address_required'); ?>",
									phone: "<?php echo $this->lang->line('config_phone_required'); ?>",
									default_tax_rate:
									{
										required:"<?php echo $this->lang->line('config_default_tax_rate_required'); ?>",
										number:"<?php echo $this->lang->line('config_default_tax_rate_number'); ?>"
									},
									email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>",
									return_policy:"<?php echo $this->lang->line('config_return_policy_required'); ?>",
									dateformat:"<?php echo $this->lang->line('config_dateformat_required'); ?>",
									timeformat:"<?php echo $this->lang->line('config_timeformat_required'); ?>",
									numberformat:"<?php echo $this->lang->line('config_numberformat_required'); ?>",
									default_client_id:"<?php echo $this->lang->line('config_default_client_id_required'); ?>"
								}
							}
						);
					}
				);
</script>


<?php $this->load->view("partial/footer"); ?>


<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/my_calendar.js"></script>
