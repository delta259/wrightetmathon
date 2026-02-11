
<?php $this->load->view("partial/header_popup"); ?>

<?php
// Prepare data
$is_new   = (($_SESSION['new'] ?? 0) == 1);
$is_del   = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info     = $_SESSION['transaction_info'];
$customer_id = isset($info->customer_id) ? $info->customer_id : '';
$person_id   = isset($info->person_id) ? $info->person_id : '';

// Avatar initials
$first_initial = !empty($info->first_name) ? mb_strtoupper(mb_substr($info->first_name, 0, 1)) : '';
$last_initial  = !empty($info->last_name) ? mb_strtoupper(mb_substr($info->last_name, 0, 1)) : '';
$initials = $first_initial . $last_initial;
if (empty($initials)) $initials = '?';

// Full name for header
$full_name = trim(($info->last_name ?? '') . ' ' . ($info->first_name ?? ''));
if (empty($full_name)) $full_name = $this->lang->line('customers_new') ?: 'Nouveau Client';
?>

<style>
/* --- Compact customer form overrides --- */
.cust-form .md-card { padding: 0.6em 0.8em; margin-bottom: 0.5em; }
.cust-form .md-card-title { font-size: 0.82em; padding-bottom: 0.35em; margin-bottom: 0.4em; }
.cust-form .md-form-row { gap: 0.5em; margin-bottom: 0.35em; align-items: flex-end; }
.cust-form .md-form-group { margin-bottom: 0.15em; display: flex; flex-direction: column; }
.cust-form .md-form-label { font-size: 0.78em; margin-bottom: 2px; line-height: 1.2; }
.cust-form .md-form-input,
.cust-form select.md-form-select,
.cust-form .md-form-select {
    height: 32px !important;
    padding: 0 8px !important;
    font-size: 13px !important;
    line-height: 32px !important;
    box-sizing: border-box !important;
    border-radius: 6px !important;
}
.cust-form select.md-form-select,
.cust-form .md-form-select {
    padding-right: 28px !important;
}
.cust-form textarea.md-form-input {
    height: auto !important;
    padding: 6px 8px !important;
    line-height: 1.4 !important;
}
.cust-form .md-toggle-row { padding: 0.2em 0; margin-bottom: 0; }
.cust-form .md-toggle-label { font-size: 0.82em; }
.cust-form .md-form-static {
    font-size: 13px; padding: 0 8px;
    height: 32px; line-height: 32px;
    box-sizing: border-box;
}
.cust-form .md-grid-2col { gap: 0.6em; }
.cust-form .md-modal-body { padding: 0.6em 1em; }
.cust-form .cust-section-sep { border-top: 1px solid var(--border-color, #e2e8f0); margin: 0.5em 0 0.4em; padding-top: 0.4em; }
.cust-form .cust-section-sep .md-card-title { border-bottom: none; padding-bottom: 0; margin-bottom: 0.3em; }
/* --- Auto-suggestion ville par code postal --- */
.zip-city-suggestions {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0); border-top: none;
    border-radius: 0 0 6px 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    max-height: 200px; overflow-y: auto;
}
.zip-city-suggestions .zip-city-item {
    padding: 6px 10px; cursor: pointer; font-size: 0.82em;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid var(--border-color, #f0f0f0); transition: background 0.15s;
}
.zip-city-suggestions .zip-city-item:last-child { border-bottom: none; }
.zip-city-suggestions .zip-city-item:hover,
.zip-city-suggestions .zip-city-item.active { background: var(--primary, #2563eb); color: #fff; }
.zip-city-suggestions .zip-city-item .zip-city-dept { font-size: 0.8em; opacity: 0.7; margin-left: 8px; white-space: nowrap; }
.zip-city-suggestions .zip-city-item:hover .zip-city-dept,
.zip-city-suggestions .zip-city-item.active .zip-city-dept { opacity: 1; }
.zip-city-suggestions .zip-city-loading { padding: 8px 10px; text-align: center; font-size: 0.82em; color: var(--text-secondary, #64748b); }
</style>

<div class="md-modal-overlay">
<div class="md-modal cust-form" style="max-width: 1050px;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header" style="padding: 0.6em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: var(--primary, #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2em; font-weight: 700; border-radius: 50%; width: 44px; height: 44px;">
            <?php echo htmlspecialchars($initials); ?>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref"><?php echo htmlspecialchars($info->account_number ?? ''); ?></div>
            <h2 class="md-modal-name" style="font-size: 1.1em;"><?php echo htmlspecialchars($full_name); ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>

<!-- ========== MESSAGES ========== -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- ========== BODY ========== -->
<div class="md-modal-body" id="md-modal-body-content">

<?php if (!$is_del) { ?>

<?php
if (!$is_undel) {
    echo form_open('customers/save/', array('id' => 'item_form'));
}
?>

<div class="md-grid-2col">

<!-- ==================== LEFT COLUMN ==================== -->
<div class="md-main-col">

    <!-- CARD: Identite + Contact + Adresse (fusionné) -->
    <div class="md-card">
        <div class="md-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?php echo $this->lang->line('customers_basic_information') ?: 'Identit&eacute;'; ?>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required"><?php echo $this->lang->line('common_last_name'); ?></label>
                <?php echo form_input(array('name'=>'last_name','id'=>'last_name','class'=>'md-form-input required','placeholder'=>$this->lang->line('common_last_name'),'value'=>$info->last_name)); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required"><?php echo $this->lang->line('common_first_name'); ?></label>
                <?php echo form_input(array('name'=>'first_name','id'=>'first_name','class'=>'md-form-input required','placeholder'=>$this->lang->line('common_first_name'),'value'=>$info->first_name)); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 120px;">
                <label class="md-form-label">Date naiss.</label>
                <?php echo form_input(array('name'=>'dob','id'=>'dob','class'=>'md-form-input','placeholder'=>'JJ/MM/AAAA','value'=>$info->dob_day.'/'.$info->dob_month.'/'.$info->dob_year)); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 90px;">
                <label class="md-form-label required"><?php echo $this->lang->line('common_sex'); ?></label>
                <?php echo form_dropdown('sex',$_SESSION['G']->sex_pick_list,$info->sex,'class="md-form-select required"'); ?>
            </div>
        </div>

        <!-- Séparateur Contact -->
        <div class="cust-section-sep">
            <div class="md-card-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                Contact
            </div>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:2;">
                <label class="md-form-label required"><?php echo $this->lang->line('common_email'); ?></label>
                <?php echo form_input(array('name'=>'email','id'=>'email','class'=>'md-form-input required','placeholder'=>$this->lang->line('common_email'),'value'=>$info->email)); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('common_phone_number'); ?></label>
                <?php echo form_input(array('name'=>'phone_number','id'=>'phone_number','class'=>'md-form-input','placeholder'=>$this->lang->line('common_phone_number'),'value'=>$info->phone_number)); ?>
            </div>
        </div>

        <!-- Séparateur Adresse -->
        <div class="cust-section-sep">
            <div class="md-card-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
                </svg>
                Adresse
            </div>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('common_address_1'); ?></label>
                <?php echo form_input(array('name'=>'address_1','id'=>'address_1','class'=>'md-form-input','placeholder'=>$this->lang->line('common_address_1'),'value'=>$info->address_1)); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('common_address_2'); ?></label>
                <?php echo form_input(array('name'=>'address_2','id'=>'address_2','class'=>'md-form-input','placeholder'=>$this->lang->line('common_address_2'),'value'=>$info->address_2)); ?>
            </div>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:0 0 90px;">
                <label class="md-form-label required"><?php echo $this->lang->line('common_zip'); ?></label>
                <?php echo form_input(array('name'=>'zip','id'=>'zip','class'=>'md-form-input required','placeholder'=>$this->lang->line('common_zip'),'value'=>$info->zip,'maxlength'=>'10','autocomplete'=>'off')); ?>
            </div>
            <div class="md-form-group" style="flex:1; position: relative;">
                <label class="md-form-label"><?php echo $this->lang->line('common_city'); ?></label>
                <?php echo form_input(array('name'=>'city','id'=>'city','class'=>'md-form-input','placeholder'=>$this->lang->line('common_city'),'value'=>$info->city)); ?>
                <div id="city-suggestions" class="zip-city-suggestions" style="display:none;"></div>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('common_state'); ?></label>
                <?php echo form_input(array('name'=>'state','id'=>'state','class'=>'md-form-input','placeholder'=>$this->lang->line('common_state'),'value'=>$info->state)); ?>
            </div>
            <div class="md-form-group" style="flex:0 0 140px;">
                <label class="md-form-label"><?php echo $this->lang->line('common_country'); ?></label>
                <?php echo form_dropdown('country_id',$_SESSION['G']->country_pick_list,$info->country_id,'class="md-form-select" id="country_id"'); ?>
            </div>
        </div>
    </div>

    <!-- CARD: Commentaires (conditionnel) -->
    <?php if ($this->config->item('person_show_comments') == 'Y') { ?>
    <div class="md-card">
        <div class="md-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <?php echo $this->lang->line('common_comments'); ?>
        </div>
        <div class="md-form-group">
            <?php echo form_textarea(array('name'=>'comments','id'=>'comments','class'=>'md-form-input','rows'=>'2','placeholder'=>$this->lang->line('common_comments'),'value'=>$info->comments)); ?>
        </div>
    </div>
    <?php } ?>

</div><!-- /md-main-col -->

<!-- ==================== RIGHT COLUMN (SIDEBAR) ==================== -->
<div class="md-sidebar">

    <!-- CARD: Compte -->
    <div class="md-card">
        <div class="md-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            Compte
        </div>

        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('customers_account_number'); ?></label>
            <?php echo form_input(array('name'=>'account_number','id'=>'account_number','class'=>'md-form-input','placeholder'=>$this->lang->line('customers_account_number'),'value'=>$info->account_number,'readonly'=>'readonly')); ?>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required">Tarif</label>
                <?php echo form_dropdown('pricelist_id',$_SESSION['G']->pricelist_pick_list,$info->pricelist_id,'class="md-form-select required"'); ?>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label required">Profil</label>
                <?php echo form_dropdown('profile_id',$_SESSION['G']->profile_pick_list,$_SESSION['selected_profile_id'],'class="md-form-select required"'); ?>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('customer_profiles_profile_reference'); ?></label>
            <?php echo form_input(array('name'=>'profile_reference','id'=>'profile_reference','class'=>'md-form-input','placeholder'=>$this->lang->line('customer_profiles_profile_reference'),'value'=>$info->profile_reference)); ?>
        </div>

        <div class="md-toggle-row">
            <div class="md-toggle-group">
                <span class="md-toggle-label"><?php echo $this->lang->line('customers_taxable'); ?></span>
                <label class="md-toggle">
                    <input type="checkbox" class="md-toggle-input" data-target="taxable"
                        <?php echo ($_SESSION['selected_taxable'] == 'Y') ? 'checked' : ''; ?>>
                    <span class="md-toggle-slider"></span>
                </label>
                <?php echo form_dropdown('taxable',$_SESSION['G']->YorN_pick_list,$_SESSION['selected_taxable'],'class="md-toggle-select" id="taxable" style="display:none;"'); ?>
            </div>
        </div>
    </div>

    <!-- CARD: Encours + Fidélité (fusionné) -->
    <div class="md-card">
        <div class="md-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
            <?php echo $this->lang->line('customers_fidelity_flag') ?: 'Fid&eacute;lit&eacute;'; ?>
        </div>

        <div class="md-toggle-row">
            <div class="md-toggle-group">
                <span class="md-toggle-label"><?php echo $this->lang->line('customers_fidelity_flag'); ?></span>
                <label class="md-toggle">
                    <input type="checkbox" class="md-toggle-input" data-target="fidelity_flag"
                        <?php echo ($_SESSION['selected_fidelity_flag'] == 'Y') ? 'checked' : ''; ?>>
                    <span class="md-toggle-slider"></span>
                </label>
                <?php echo form_dropdown('fidelity_flag',$_SESSION['G']->YorN_pick_list,$_SESSION['selected_fidelity_flag'],'class="md-toggle-select" id="fidelity_flag" style="display:none;"'); ?>
            </div>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('customers_fidelity_points'); ?></label>
                <div class="md-form-static"><?php echo htmlspecialchars($info->fidelity_points ?? '0'); ?></div>
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('customers_fidelity_value'); ?></label>
                <div class="md-form-static"><?php echo htmlspecialchars($info->fidelity_value ?? '0'); ?></div>
            </div>
        </div>

        <!-- Séparateur Encours -->
        <div class="cust-section-sep">
            <div class="md-card-title">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                Encours
            </div>
        </div>

        <div class="md-toggle-row">
            <div class="md-toggle-group">
                <span class="md-toggle-label"><?php echo $this->lang->line('customers_on_stop_indicator'); ?></span>
                <label class="md-toggle">
                    <input type="checkbox" class="md-toggle-input" data-target="on_stop_indicator"
                        <?php echo ($_SESSION['selected_on_stop_indicator'] == 'Y') ? 'checked' : ''; ?>>
                    <span class="md-toggle-slider"></span>
                </label>
                <?php echo form_dropdown('on_stop_indicator',$_SESSION['G']->YorN_pick_list,$_SESSION['selected_on_stop_indicator'],'class="md-toggle-select" id="on_stop_indicator" style="display:none;"'); ?>
            </div>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label"><?php echo $this->lang->line('customers_on_stop_amount'); ?></label>
                <?php echo form_input(array('name'=>'on_stop_amount','id'=>'on_stop_amount','class'=>'md-form-input','placeholder'=>'Montant','value'=>$info->on_stop_amount)); ?>
            </div>
            <div class="md-form-group" style="flex:2;">
                <label class="md-form-label"><?php echo $this->lang->line('customers_on_stop_reason'); ?></label>
                <?php echo form_input(array('name'=>'on_stop_reason','id'=>'on_stop_reason','class'=>'md-form-input','placeholder'=>'Motif','value'=>$info->on_stop_reason)); ?>
            </div>
        </div>
    </div>

    <!-- CARD: VapeSelf (conditionnel) -->
    <?php
    if (($_SESSION['selected_fidelity_flag'] == 'Y') && ($this->config->item('distributeur_vapeself') == 'Y')) {
        $id = $info->person_id;
    ?>
    <div class="md-card">
        <div class="md-card-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
            VapeSelf
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label">Code carte</label>
                <input type="text" id="code_carte_distributeur_vapeself" name="code_carte_distributeur_vapeself"
                       class="md-form-input" maxlength="4" value="<?php echo htmlspecialchars($info->card_code ?? ''); ?>">
            </div>
            <div class="md-form-group" style="flex:1;">
                <label class="md-form-label">Cr&eacute;dit VS</label>
                <div class="md-form-static"><?php echo htmlspecialchars($info->vs_solde ?? '0'); ?></div>
            </div>
        </div>

        <a href="<?php echo site_url("customers/update_VS_client/$id"); ?>"
           class="md-btn md-btn-secondary" style="width: 100%; text-align: center; margin-top: 0.3em; padding: 5px 0; font-size: 0.82em;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="22 2 15 22 11 13 2 9 22 2"></polyline>
            </svg>
            Envoyer donn&eacute;es VS
        </a>
    </div>
    <?php } ?>

</div><!-- /md-sidebar -->

</div><!-- /md-grid-2col -->

<?php if (!$is_undel) { echo form_close(); } ?>

<?php } /* end if !$is_del */ ?>

</div><!-- /md-modal-body -->

<!-- ========== FOOTER ========== -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left">
        <?php
        if (!$is_new && !$is_undel && !$is_del) {
            echo form_open($_SESSION['controller_name'].'/delete/'.$customer_id, array('id'=>'customer_delete_form','style'=>'display:inline;'));
            ?>
            <button type="submit" name="delete" id="delete" class="md-btn md-btn-danger">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <?php echo $this->lang->line('customers_delete'); ?>
            </button>
            <?php echo form_close();
        }
        if ($is_undel && !empty($customer_id)) {
            echo form_open($_SESSION['controller_name'].'/undelete/'.$customer_id, array('id'=>'customer_delete_form','style'=>'display:inline;'));
            ?>
            <button type="submit" name="undelete" id="undelete" class="md-btn md-btn-success">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                </svg>
                <?php echo $this->lang->line('customers_undelete'); ?>
            </button>
            <?php echo form_close();
        }
        ?>
    </div>
    <div class="md-modal-footer-right">
        <?php if (!$is_undel && !$is_del) { ?>
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
                <?php echo $this->lang->line('common_reset'); ?>
            </a>
            <button type="submit" form="item_form" name="submit" id="submit" class="md-btn md-btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <?php echo $this->lang->line('common_submit'); ?>
            </button>
        <?php } else { ?>
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">Fermer</a>
        <?php } ?>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script src="<?php echo base_url(); ?>/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
<script src="<?php echo base_url(); ?>/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url(); ?>/jquery-ui-1.12.1.custom/my_calendar2.js"></script>

<script>
(function() {
    var FRANCE_COUNTRY_ID = '1';
    var zipTimer = null;
    var activeIdx = -1;

    function isFranceSelected() {
        var sel = $('#country_id');
        if (!sel.length) return true;
        return sel.val() == FRANCE_COUNTRY_ID;
    }

    function isFrenchZip(val) { return /^\d{5}$/.test(val); }

    function showSuggestions(items) {
        var $box = $('#city-suggestions');
        $box.empty();
        activeIdx = -1;
        if (!items || items.length === 0) { $box.hide(); return; }
        if (items.length === 1) {
            $('#city').val(items[0].nom);
            if (items[0].departement && items[0].departement.nom) $('#state').val(items[0].departement.nom);
            $box.hide();
            return;
        }
        $.each(items, function(i, item) {
            var deptLabel = item.departement ? item.departement.nom + ' (' + item.codeDepartement + ')' : item.codeDepartement || '';
            var $it = $('<div class="zip-city-item" data-index="' + i + '">' +
                '<span>' + $('<span>').text(item.nom).html() + '</span>' +
                '<span class="zip-city-dept">' + $('<span>').text(deptLabel).html() + '</span></div>');
            $it.on('click', function() {
                $('#city').val(item.nom);
                if (item.departement && item.departement.nom) $('#state').val(item.departement.nom);
                $box.hide();
                $('#city').focus();
            });
            $box.append($it);
        });
        $box.show();
    }

    function fetchCities(zip) {
        var $box = $('#city-suggestions');
        $box.html('<div class="zip-city-loading">Recherche...</div>').show();
        $.ajax({
            url: 'https://geo.api.gouv.fr/communes',
            data: { codePostal: zip, fields: 'nom,codeDepartement,departement', format: 'json' },
            dataType: 'json', timeout: 5000,
            success: function(data) {
                data.sort(function(a, b) { return a.nom.localeCompare(b.nom); });
                showSuggestions(data);
            },
            error: function() { $box.hide(); }
        });
    }

    $(document).ready(function() {
        $('#zip').on('input keyup', function() {
            clearTimeout(zipTimer);
            var val = $.trim($(this).val());
            if (!isFranceSelected() || !isFrenchZip(val)) { $('#city-suggestions').hide(); return; }
            zipTimer = setTimeout(function() { fetchCities(val); }, 300);
        });

        $('#zip, #city').on('keydown', function(e) {
            var $box = $('#city-suggestions');
            if (!$box.is(':visible')) return;
            var $items = $box.find('.zip-city-item');
            if (!$items.length) return;
            if (e.keyCode === 40) { e.preventDefault(); activeIdx = Math.min(activeIdx+1, $items.length-1); $items.removeClass('active'); $items.eq(activeIdx).addClass('active'); }
            else if (e.keyCode === 38) { e.preventDefault(); activeIdx = Math.max(activeIdx-1, 0); $items.removeClass('active'); $items.eq(activeIdx).addClass('active'); }
            else if (e.keyCode === 13 && activeIdx >= 0) { e.preventDefault(); $items.eq(activeIdx).trigger('click'); }
            else if (e.keyCode === 27) { $box.hide(); activeIdx = -1; }
        });

        $('#country_id').on('change', function() {
            var zip = $.trim($('#zip').val());
            if (isFranceSelected() && isFrenchZip(zip)) fetchCities(zip);
            else $('#city-suggestions').hide();
        });

        $(document).on('mousedown', function(e) {
            if (!$(e.target).closest('#city-suggestions, #zip, #city').length) $('#city-suggestions').hide();
        });
    });
})();
</script>
