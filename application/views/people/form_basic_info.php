
<!-- Person basic info — shared include (customers, suppliers, employees) -->
<div class="md-form-row">
    <div class="md-form-group" style="flex:1">
        <label class="md-form-label required"><?php echo $this->lang->line('common_last_name'); ?></label>
        <?php echo form_input(array(
            'name'=>'last_name','id'=>'last_name',
            'class'=>'md-form-input required',
            'placeholder'=>$this->lang->line('common_last_name'),
            'value'=>$_SESSION['transaction_info']->last_name
        )); ?>
    </div>
    <div class="md-form-group" style="flex:1">
        <label class="md-form-label required"><?php echo $this->lang->line('common_first_name'); ?></label>
        <?php echo form_input(array(
            'name'=>'first_name','id'=>'first_name',
            'class'=>'md-form-input required',
            'placeholder'=>$this->lang->line('common_first_name'),
            'value'=>$_SESSION['transaction_info']->first_name
        )); ?>
    </div>
    <div class="md-form-group" style="flex:0 0 140px;">
        <label class="md-form-label">Date de naissance</label>
        <?php echo form_input(array(
            'name'=>'dob','id'=>'dob',
            'class'=>'md-form-input',
            'placeholder'=>'JJ/MM/AAAA',
            'value'=>$_SESSION['transaction_info']->dob_day.'/'.$_SESSION['transaction_info']->dob_month.'/'.$_SESSION['transaction_info']->dob_year
        )); ?>
    </div>
</div>

<div class="md-form-row">
    <div class="md-form-group" style="flex:0 0 120px;">
        <label class="md-form-label required"><?php echo $this->lang->line('common_sex'); ?></label>
        <?php echo form_dropdown('sex', $_SESSION['G']->sex_pick_list, $_SESSION['transaction_info']->sex, 'class="md-form-select"'); ?>
    </div>
    <div class="md-form-group" style="flex:1">
        <label class="md-form-label required"><?php echo $this->lang->line('common_email'); ?></label>
        <?php echo form_input(array(
            'name'=>'email','id'=>'email',
            'class'=>'md-form-input required',
            'placeholder'=>$this->lang->line('common_email'),
            'value'=>$_SESSION['transaction_info']->email
        )); ?>
    </div>
    <div class="md-form-group" style="flex:0 0 180px;">
        <label class="md-form-label"><?php echo $this->lang->line('common_phone_number'); ?></label>
        <?php echo form_input(array(
            'name'=>'phone_number','id'=>'phone_number',
            'class'=>'md-form-input',
            'placeholder'=>$this->lang->line('common_phone_number'),
            'value'=>$_SESSION['transaction_info']->phone_number
        )); ?>
    </div>
</div>

<div class="md-form-row">
    <div class="md-form-group" style="flex:2">
        <label class="md-form-label"><?php echo $this->lang->line('common_address_1'); ?></label>
        <?php echo form_input(array(
            'name'=>'address_1','id'=>'address_1',
            'class'=>'md-form-input',
            'placeholder'=>$this->lang->line('common_address_1'),
            'value'=>$_SESSION['transaction_info']->address_1
        )); ?>
    </div>
    <div class="md-form-group" style="flex:2">
        <label class="md-form-label"><?php echo $this->lang->line('common_address_2'); ?></label>
        <?php echo form_input(array(
            'name'=>'address_2','id'=>'address_2',
            'class'=>'md-form-input',
            'placeholder'=>$this->lang->line('common_address_2'),
            'value'=>$_SESSION['transaction_info']->address_2
        )); ?>
    </div>
    <div class="md-form-group" style="flex:0 0 100px;">
        <label class="md-form-label required"><?php echo $this->lang->line('common_zip'); ?></label>
        <?php echo form_input(array(
            'name'=>'zip','id'=>'zip',
            'class'=>'md-form-input required',
            'placeholder'=>$this->lang->line('common_zip'),
            'value'=>$_SESSION['transaction_info']->zip
        )); ?>
    </div>
</div>

<div class="md-form-row">
    <div class="md-form-group" style="flex:1">
        <label class="md-form-label"><?php echo $this->lang->line('common_city'); ?></label>
        <?php echo form_input(array(
            'name'=>'city','id'=>'city',
            'class'=>'md-form-input',
            'placeholder'=>$this->lang->line('common_city'),
            'value'=>$_SESSION['transaction_info']->city
        )); ?>
    </div>
    <div class="md-form-group" style="flex:1">
        <label class="md-form-label"><?php echo $this->lang->line('common_state'); ?></label>
        <?php echo form_input(array(
            'name'=>'state','id'=>'state',
            'class'=>'md-form-input',
            'placeholder'=>$this->lang->line('common_state'),
            'value'=>$_SESSION['transaction_info']->state
        )); ?>
    </div>
    <div class="md-form-group" style="flex:1">
        <label class="md-form-label"><?php echo $this->lang->line('common_country'); ?></label>
        <?php echo form_dropdown('country_id', $_SESSION['G']->country_pick_list, $_SESSION['transaction_info']->country_id, 'class="md-form-select"'); ?>
    </div>
</div>

<?php if ($this->config->item('person_show_comments') == 'Y') { ?>
<div class="md-form-group">
    <label class="md-form-label"><?php echo $this->lang->line('common_comments'); ?></label>
    <?php echo form_textarea(array(
        'name'=>'comments','id'=>'comments',
        'class'=>'md-form-input',
        'rows'=>'3',
        'placeholder'=>$this->lang->line('common_comments'),
        'value'=>$_SESSION['transaction_info']->comments
    )); ?>
</div>
<?php } ?>
