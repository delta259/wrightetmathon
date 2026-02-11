

<tr>
    <td align="left" class="zone_champ_saisie "><?php echo form_input	(	array	(
            'name'		=>	'last_name',
            'id'		=>	'last_name',
            'style'		=>	'font-size:15px;',
            'size'		=>	30,
            'class'=>'colorobligatoire',
            'placeholder'=>$this->lang->line('common_last_name'),
            'value'		=>	$_SESSION['transaction_info']->last_name
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_last_name') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie "><?php echo form_input	(	array	(
            'name'		=>	'first_name',
            'id'		=>	'first_name',
            'style'		=>	'font-size:15px;',
            'size'		=>	30,
            'class'=>'colorobligatoire',

            'placeholder'=>$this->lang->line('common_first_name'),
            'value'		=>	$_SESSION['transaction_info']->first_name
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_first_name') ;?>"></a>
    </td>

    <td align="left" class="zone_champ_saisie "><?php 	echo form_input	(	array	(
            'name'		=>	'dob',
            'id'		=>	'dob',
            'style'		=>	'font-size:15px;',
            'size'		=>	8,
            'class'=>'colornormal',
            'placeholder'=> 'JJ/MM/AAAA',
            'value'		=>	$_SESSION['transaction_info']->dob_day.'/'.$_SESSION['transaction_info']->dob_month.'/'.$_SESSION['transaction_info']->dob_year
        )); ?>
        <a class="btaide" title="<?php echo 'Date de Naissance' ;?>"></a>
    </td>



</tr>
<!-- email -->
<tr>
    <td class="zone_champ_saisie "  ><?php /*echo $this->lang->line('common_sex');*/ echo form_dropdown	(
            'sex',
            $_SESSION['G']->sex_pick_list,
            $_SESSION['transaction_info']->sex,
            'class="colorobligatoire" style="text-align:center; font-size:15px"'
        ) ;?>
        <a class="btaide" title="<?php echo $this->lang->line('common_sex') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie "><?php 	echo form_input	(	array	(
            'name'		=>	'email',
            'id'		=>	'email',
            'style'		=>	'font-size:15px;',
            'class'=>'colorobligatoire',
            'size'		=>	30,
            'placeholder'=>$this->lang->line('common_email'),
            'value'		=>	$_SESSION['transaction_info']->email
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_email') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
            'name'		=>	'phone_number',
            'id'		=>	'phone_number',
            'class'=>'colornormal',
            'style'		=>	'font-size:15px;',
            'placeholder'=>$this->lang->line('common_phone_number'),
            'size'		=>15,
            'value'		=>	$_SESSION['transaction_info']->phone_number
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_phone_number') ;?>"></a>
    </td>
    <td>

    </td>


</tr><!-- adres-->
<tr><td align="left" class="zone_champ_saisie "><?php echo form_input	(	array	(
            'name'		=>	'address_1',
            'id'		=>	'address_1',
            'style'		=>	'font-size:15px;',
            'size'		=>	30,
            'class'=>'colornormal',
            'placeholder'=>$this->lang->line('common_address_1'),
            'value'		=>	$_SESSION['transaction_info']->address_1
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_address_1') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
            'name'		=>	'address_2',
            'id'		=>	'address_2',
            'style'		=>	'font-size:15px;',
            'class'=>'colornormal',
            'size'		=>	30,
            'placeholder'=>$this->lang->line('common_address_2'),
            'value'		=>	$_SESSION['transaction_info']->address_2
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_address_2') ;?>"></a>
    </td><td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
            'name'		=>	'zip',
            'id'		=>	'zip',
            'style'		=>	'font-size:15px;',
            'size'		=>	5,
            'class'=>'colorobligatoire',
            'placeholder'=>$this->lang->line('common_zip'),
            'value'		=>	$_SESSION['transaction_info']->zip
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_zip') ;?>"></a>
    </td></tr>


<!--	/*	// if this is a supplier udate email is mandatory
        if ($_SESSION['supplier_view'] == 1)
        {
            echo form_label($this->lang->line('common_email'), 'email', array('class'=>'required'));
        }
        else
        {
            echo form_label($this->lang->line('common_email'), 'email');
        }
        */?>-->











<tr>
    <td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
            'name'		=>	'city',
            'id'		=>	'city',
            'style'		=>	'font-size:15px;',
            'size'		=>	20,
            'class'=>'colornormal',
            'placeholder'=>$this->lang->line('common_city'),
            'value'		=>	$_SESSION['transaction_info']->city
        ));?>
        <a class="btaide" title="<?php echo $this->lang->line('common_city') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie"><?php echo form_input	(	array	(
                'name'		=>	'state',
                'id'		=>	'state',
                'style'		=>	'font-size:15px;',
                'size'		=>	20,
                'class'=>'colornormal',
                'placeholder'=>$this->lang->line('common_state'),
                'value'		=>	$_SESSION['transaction_info']->state)
        );?>
        <a class="btaide" title="<?php echo $this->lang->line('common_state') ;?>"></a>
    </td>
    <td align="left" class="zone_champ_saisie"><?php /*echo $this->lang->line('common_country');*/ echo form_dropdown	(
            'country_id',
            $_SESSION['G']->country_pick_list,
            $_SESSION['transaction_info']->country_id,
            ' style="text-align:center; font-size:15px"'
        );?>  <a class="btaide" title="<?php echo $this->lang->line('common_country'); ?>"></a>
    </td></tr>
<tr>
    <?php
    if ($this->config->item('person_show_comments') == 'Y') {
        ?>
        <td align="left" colspan=3
            class="zone_champ_saisie"><?php echo form_textarea(array(
                'name' => 'comments',
                'id' => 'comments',
                'style' => 'font-size:15px;',
                'rows' => '3',
                'cols' => '60',
                'class'=>'colornormal',
                'placeholder' => $this->lang->line('common_comments'),
                'value' => $_SESSION['transaction_info']->comments
            )); ?>
            <a class="btaide" title="<?php echo $this->lang->line('common_comments'); ?>"></a>
        </td>
        <?php
    }
    ?>
</tr>

