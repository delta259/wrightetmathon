<!-- Configuration screen with 6 tabs -->

<!-- output the header -->
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>

<div id="wrapper" class="wlp-bighorn-book">

    <?php $this->load->view("partial/header_menu"); ?>

    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">

                <div class="body_page" id="loginPage">
                    <div>

                        <div class="body_colonne">
                            <h2><?php echo $this->lang->line('config_info'); ?></h2>
                            <div class="body_cadre_gris">

<!-- set up the input form -->
<?php echo form_open('config/save/', array('id'=>'config_form')); ?>

<div id="config_wrapper">
    <div class="blocformfond creationimmediate">
        <div>
            <ul id="error_message_box"></ul>
            <fieldset>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB BAR + inline toast message                                     -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div style="display:flex;align-items:center;gap:0;">
<div class="md-tab-bar" id="config-tab-bar" style="flex:1;">
    <a class="md-tab md-tab-active" data-tab="tab-magasin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <?php echo $this->lang->line('config_tab_magasin'); ?>
    </a>
    <a class="md-tab" data-tab="tab-systeme">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        <?php echo $this->lang->line('config_tab_systeme'); ?>
    </a>
    <a class="md-tab" data-tab="tab-ticket">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <?php echo $this->lang->line('config_tab_ticket'); ?>
    </a>
    <a class="md-tab" data-tab="tab-caisse">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <?php echo $this->lang->line('config_tab_caisse'); ?>
    </a>
    <a class="md-tab" data-tab="tab-parametres">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
        <?php echo $this->lang->line('config_tab_parametres'); ?>
    </a>
    <a class="md-tab" data-tab="tab-technique">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        <?php echo $this->lang->line('config_tab_technique'); ?>
    </a>
</div>
<?php if (!empty($success)): ?>
<div id="config-toast" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;margin-left:8px;background:var(--success-bg,#dcfce7);color:var(--success-text,#166534);border:1px solid var(--success,#22c55e);border-radius:6px;font-size:0.8rem;font-weight:500;white-space:nowrap;opacity:1;transition:opacity 0.5s;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    <?php echo $success; ?>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;margin-left:8px;background:var(--danger-bg,#fef2f2);color:var(--danger-text,#991b1b);border:1px solid var(--danger,#ef4444);border-radius:6px;font-size:0.8rem;font-weight:500;white-space:nowrap;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    <?php echo $error; ?>
</div>
<?php endif; ?>
</div><!-- /flex wrapper tab-bar + toast -->

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 1 — MAGASIN                                                    -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-magasin" class="config-tab-panel" style="display:block">

    <div class="md-grid-2eq">

    <!-- ── Card : Identite du magasin ─────────────────────────────── -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Identite du magasin
        </div>

        <div class="md-form-row">
            <div class="md-form-group">
                <label class="md-form-label">Enseigne Y/S</label>
                <?php echo form_input(array(
                    'name'=>'custom1_name',
                    'id'=>'custom1_name',
                    'class'=>'md-form-input',
                    'placeholder'=>'Y = YesStore, S = Sonrisa',
                    'value'=>$this->config->item('custom1_name'))); ?>
            </div>
            <div class="md-form-group">
                <label class="md-form-label"><?php echo $this->lang->line('config_item_reference_prefix'); ?></label>
                <?php echo form_input(array(
                    'name'=>'item_reference_prefix',
                    'id'=>'item_reference_prefix',
                    'class'=>'md-form-input',
                    'maxlength'=>'2',
                    'size'=>'4',
                    'style'=>'width:80px;text-transform:uppercase;letter-spacing:2px;font-weight:600;',
                    'placeholder'=>'YS',
                    'value'=>$this->config->item('item_reference_prefix'))); ?>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_company'); ?></label>
            <?php echo form_input(array(
                'name'=>'company',
                'id'=>'company',
                'class'=>'md-form-input required',
                'value'=>$this->config->item('company'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_company_registration_number'); ?></label>
            <?php echo form_input(array(
                'name'=>'siret',
                'id'=>'siret',
                'class'=>'md-form-input required',
                'placeholder'=>'SIRET',
                'value'=>$this->config->item('siret'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_company_tva_number'); ?></label>
            <?php echo form_input(array(
                'name'=>'tva',
                'id'=>'tva',
                'class'=>'md-form-input required',
                'placeholder'=>'FR...',
                'value'=>$this->config->item('tva'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_opened'); ?></label>
            <?php echo form_input(array(
                'name'=>'branch_opened',
                'id'=>'branch_opened',
                'class'=>'md-form-input required',
                'style'=>'max-width:200px',
                'value'=>$this->config->item('branch_opened'))); ?>
        </div>
    </div>

    <!-- ── Card : Coordonnees ─────────────────────────────────────── -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Coordonnees
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_address'); ?></label>
            <?php echo form_textarea(array(
                'name'=>'address',
                'id'=>'address',
                'class'=>'md-form-input required',
                'rows'=>3,
                'value'=>$this->config->item('address'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_phone'); ?></label>
            <?php echo form_input(array(
                'name'=>'phone',
                'id'=>'phone',
                'class'=>'md-form-input required',
                'value'=>$this->config->item('phone'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('common_email'); ?></label>
            <?php echo form_input(array(
                'name'=>'email',
                'id'=>'email',
                'class'=>'md-form-input required',
                'value'=>$this->config->item('email'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('config_website'); ?></label>
            <?php echo form_input(array(
                'name'=>'website',
                'id'=>'website',
                'class'=>'md-form-input',
                'placeholder'=>'https://',
                'value'=>$this->config->item('website'))); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_shop_open_hours'); ?></label>
            <?php echo form_textarea(array(
                'name'=>'open_hours',
                'id'=>'open_hours',
                'class'=>'md-form-input required',
                'rows'=>3,
                'value'=>$this->config->item('open_hours'))); ?>
        </div>
    </div>

    </div><!-- /md-grid-2eq -->

    <!-- ── Card : Securite & Divers ───────────────────────────────── -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Securite &amp; Divers
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('config_Alarm_OK_code'); ?></label>
                <?php echo form_input(array(
                    'name'=>'Alarm_OK_code',
                    'id'=>'Alarm_OK_code',
                    'class'=>'md-form-input',
                    'value'=>$this->config->item('Alarm_OK_code'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('config_Alarm_KO_code'); ?></label>
                <?php echo form_input(array(
                    'name'=>'Alarm_KO_code',
                    'id'=>'Alarm_KO_code',
                    'class'=>'md-form-input',
                    'value'=>$this->config->item('Alarm_KO_code'))); ?>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_flash_info_displays'); ?></label>
            <?php echo form_input(array(
                'name'=>'flash_info_displays',
                'id'=>'flash_info_displays',
                'class'=>'md-form-input required',
                'style'=>'max-width:120px',
                'value'=>$this->config->item('flash_info_displays'))); ?>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 2 — SYSTEME                                                    -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-systeme" class="config-tab-panel" style="display:none">

    <div class="md-grid-2eq">

    <!-- Card : Localisation -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Localisation
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_language'); ?></label>
            <?php echo form_dropdown('language', array(
                'Azerbaijan'=>'Azerbaijan','BahasaIndonesia'=>'BahasaIndonesia',
                'English'=>'English','French'=>'French','Spanish'=>'Spanish','Russian'=>'Russian'
            ), $this->config->item('language'), 'class="md-form-select"'); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('currencies_currency_name'); ?></label>
            <?php echo form_dropdown('currency', $_SESSION['G']->currency_pick_list, $this->config->item('currency'), 'class="md-form-select"'); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_timezone'); ?></label>
            <?php echo form_dropdown('timezone', $_SESSION['G']->timezone_pick_list, $this->config->item('timezone'), 'class="md-form-select"'); ?>
        </div>
    </div>

    <!-- Card : Formats -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Formats
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_dateformat'); ?></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php echo form_input(array('name'=>'dateformat','id'=>'dateformat','class'=>'md-form-input required','style'=>'flex:1;','value'=>$this->config->item('dateformat'))); ?>
                <span style="font-size:0.82em;color:var(--text-secondary,#64748b);white-space:nowrap;"><?php echo $this->lang->line('config_dateformatexample'); ?></span>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_timeformat'); ?></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php echo form_input(array('name'=>'timeformat','id'=>'timeformat','class'=>'md-form-input required','style'=>'flex:1;','value'=>$this->config->item('timeformat'))); ?>
                <span style="font-size:0.82em;color:var(--text-secondary,#64748b);white-space:nowrap;"><?php echo $this->lang->line('config_timeformatexample'); ?></span>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_numberformat'); ?></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php echo form_input(array('name'=>'numberformat','id'=>'numberformat','class'=>'md-form-input required','style'=>'flex:1;','value'=>$this->config->item('numberformat'))); ?>
                <span style="font-size:0.82em;color:var(--text-secondary,#64748b);white-space:nowrap;"><?php echo $this->lang->line('config_numberformatexample'); ?></span>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('config_touchscreen'); ?></label>
            <div class="md-toggle-group">
                <label class="md-toggle">
                    <input type="hidden" name="touchscreen" value="0">
                    <input type="checkbox" name="touchscreen" value="1" class="md-toggle-input"
                        <?php if ($this->config->item('touchscreen') == '1') echo 'checked'; ?>>
                    <span class="md-toggle-slider"></span>
                </label>
                <span class="md-toggle-value"><?php echo ($this->config->item('touchscreen') == '1') ? 'ON' : 'OFF'; ?></span>
            </div>
        </div>
    </div>

    </div><!-- /md-grid-2eq -->

</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 3 — TICKET DE CAISSE                                          -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-ticket" class="config-tab-panel" style="display:none">

    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Messages du ticket
        </div>

        <div class="md-grid-2eq">
            <div class="md-form-group">
                <label class="md-form-label"><?php echo $this->lang->line('config_polite_message'); ?></label>
                <?php echo form_textarea(array('name'=>'polite_message','id'=>'polite_message','rows'=>'6','class'=>'md-form-input','value'=>$this->config->item('polite_message'))); ?>
            </div>
            <div class="md-form-group">
                <label class="md-form-label"><?php echo $this->lang->line('config_season_message'); ?></label>
                <?php echo form_textarea(array('name'=>'season_message','id'=>'season_message','rows'=>'6','class'=>'md-form-input','value'=>$this->config->item('season_message'))); ?>
            </div>
            <div class="md-form-group">
                <label class="md-form-label"><?php echo $this->lang->line('config_fidelity_message'); ?></label>
                <?php echo form_textarea(array('name'=>'fidelity_message','id'=>'fidelity_message','rows'=>'6','class'=>'md-form-input','value'=>$this->config->item('fidelity_message'))); ?>
            </div>
            <div class="md-form-group">
                <label class="md-form-label required"><?php echo $this->lang->line('common_return_policy'); ?></label>
                <?php echo form_textarea(array('name'=>'return_policy','id'=>'return_policy','rows'=>'6','class'=>'md-form-input required','value'=>$this->config->item('return_policy'))); ?>
            </div>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 4 — CAISSE & FIDELITE                                         -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-caisse" class="config-tab-panel" style="display:none">

    <div class="md-grid-2eq">

    <!-- Card : Configuration Caisse -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
            <?php echo $this->lang->line('config_cashtill_configuration'); ?>
        </div>

        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_cashtill_check_total'); ?></label>
                <?php echo form_dropdown('cashtill_check_total', $_SESSION['G']->YorN_pick_list, $this->config->item('cashtill_check_total'), 'class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_cashtill_total'); ?></label>
                <?php echo form_input(array('name'=>'cashtill_total','id'=>'cashtill_total','class'=>'md-form-input required','style'=>'text-align:right;max-width:120px;','value'=>$this->config->item('cashtill_total'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_cashtill_allow_correction'); ?></label>
                <?php echo form_dropdown('cashtill_allow_correction', $_SESSION['G']->YorN_pick_list, $this->config->item('cashtill_allow_correction'), 'class="md-form-select"'); ?>
            </div>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_cashtill_notification_email'); ?></label>
            <?php echo form_input(array('name'=>'cashtill_notification_email','id'=>'cashtill_notification_email','class'=>'md-form-input required','value'=>$this->config->item('cashtill_notification_email'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_cashtill_notification_password'); ?></label>
            <?php echo form_password(array('name'=>'cashtill_notification_password','id'=>'cashtill_notification_password','class'=>'md-form-input required','value'=>$this->config->item('cashtill_notification_password'))); ?>
        </div>
    </div>

    <!-- Card : Fidelite -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <?php echo $this->lang->line('config_fidelity'); ?>
        </div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_fidelity_rule_1'); ?></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php echo form_input(array('name'=>'fidelity_rule','id'=>'fidelity_rule','class'=>'md-form-input required','style'=>'text-align:right;max-width:120px;','value'=>$this->config->item('fidelity_rule'))); ?>
                <span style="font-size:0.82em;color:var(--text-secondary,#64748b);white-space:nowrap;"><?php echo $currency_info->currency_sign.' '.$this->lang->line('sales_TTC').' '.$this->lang->line('config_fidelity_rule_2'); ?></span>
            </div>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_fidelity_value'); ?></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php echo form_input(array('name'=>'fidelity_value','id'=>'fidelity_value','class'=>'md-form-input required','style'=>'text-align:right;max-width:120px;','value'=>$this->config->item('fidelity_value'))); ?>
                <span style="font-size:0.82em;color:var(--text-secondary,#64748b);"><?php echo $currency_info->currency_sign.' '.$this->lang->line('sales_TTC'); ?></span>
            </div>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_fidelity_minimum'); ?></label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <?php echo form_input(array('name'=>'fidelity_minimum','id'=>'fidelity_minimum','class'=>'md-form-input required','style'=>'text-align:right;max-width:100px;','value'=>$this->config->item('fidelity_minimum'))); ?>
                    <span style="font-size:0.82em;color:var(--text-secondary,#64748b);"><?php echo $currency_info->currency_sign; ?></span>
                </div>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_fidelity_maximum'); ?></label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <?php echo form_input(array('name'=>'fidelity_maximum','id'=>'fidelity_maximum','class'=>'md-form-input required','style'=>'text-align:right;max-width:100px;','value'=>$this->config->item('fidelity_maximum'))); ?>
                    <span style="font-size:0.82em;color:var(--text-secondary,#64748b);"><?php echo $currency_info->currency_sign; ?></span>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /md-grid-2eq -->

</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 5 — PARAMETRES PAR DEFAUT                                      -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-parametres" class="config-tab-panel" style="display:none">

    <div class="md-grid-2eq">

    <!-- Card : Valeurs par defaut -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/></svg>
            Valeurs par defaut
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_use_DLUO'); ?></label>
                <?php echo form_dropdown('use_DLUO', $_SESSION['G']->YorN_pick_list, $this->config->item('use_DLUO'), 'class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_person_show_comments'); ?></label>
                <?php echo form_dropdown('person_show_comments', $_SESSION['G']->YorN_pick_list, $this->config->item('person_show_comments'), 'class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_multi_vendeur'); ?></label>
                <?php echo form_dropdown('multi_vendeur', $_SESSION['G']->YorN_pick_list, $this->config->item('multi_vendeur'), 'class="md-form-select"'); ?>
            </div>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('pricelists_pricelist_name').' '.$this->lang->line('common_default'); ?></label>
                <?php echo form_dropdown('pricelist_id', $_SESSION['G']->pricelist_pick_list, $this->config->item('pricelist_id'), 'class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('customer_profiles_profile_name').' '.$this->lang->line('common_default'); ?></label>
                <?php echo form_dropdown('profile_id', $_SESSION['G']->profile_pick_list, $this->config->item('profile_id'), 'class="md-form-select"'); ?>
            </div>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_default_client_id'); ?></label>
                <?php echo form_input(array('name'=>'default_client_id','id'=>'default_client_id','class'=>'md-form-input required','style'=>'max-width:100px;','value'=>$this->config->item('default_client_id'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_default_supplier_id'); ?></label>
                <?php echo form_input(array('name'=>'default_supplier_id','id'=>'default_supplier_id','class'=>'md-form-input required','style'=>'max-width:100px;','value'=>$this->config->item('default_supplier_id'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_no_supplier_id'); ?></label>
                <?php echo form_input(array('name'=>'no_supplier_id','id'=>'no_supplier_id','class'=>'md-form-input required','style'=>'max-width:100px;','value'=>$this->config->item('no_supplier_id'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_default_warehouse_code'); ?></label>
                <?php echo form_input(array('name'=>'default_warehouse_code','id'=>'default_warehouse_code','class'=>'md-form-input required','style'=>'max-width:100px;','value'=>$this->config->item('default_warehouse_code'))); ?>
            </div>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_historique'); ?></label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <?php echo form_input(array('name'=>'historique','id'=>'historique','class'=>'md-form-input required','style'=>'text-align:right;max-width:80px;','value'=>$this->config->item('historique'))); ?>
                    <span style="font-size:0.82em;color:var(--text-secondary,#64748b);">jours</span>
                </div>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_nbre_jour_prevision_stock'); ?></label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <?php echo form_input(array('name'=>'nbre_jour_prevision_stock','id'=>'nbre_jour_prevision_stock','class'=>'md-form-input required','style'=>'text-align:right;max-width:80px;','value'=>$this->config->item('nbre_jour_prevision_stock'))); ?>
                    <span style="font-size:0.82em;color:var(--text-secondary,#64748b);">jours</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card : VapeSelf & Commandes -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            VapeSelf &amp; Commandes fournisseur
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_distributeur_vapeself'); ?></label>
                <?php echo form_dropdown('distributeur_vapeself', $_SESSION['G']->YorN_pick_list, $this->config->item('distributeur_vapeself'), 'class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_distributeur_vapeself_code'); ?></label>
                <?php echo form_input(array('name'=>'distributeur_vapeself_code','id'=>'distributeur_vapeself_code','class'=>'md-form-input required','style'=>'max-width:100px;','value'=>$this->config->item('distributeur_vapeself_code'))); ?>
            </div>
        </div>

        <div style="border-top:1px solid var(--border-color,#e2e8f0);margin:12px 0;"></div>

        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_POemail'); ?></label>
            <?php echo form_input(array('name'=>'POemail','id'=>'POemail','class'=>'md-form-input required','value'=>$this->config->item('POemail'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_POemailpwd'); ?></label>
            <?php echo form_input(array('name'=>'POemailpwd','id'=>'POemailpwd','class'=>'md-form-input required','value'=>$this->config->item('POemailpwd'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_POemailmsg'); ?></label>
            <?php echo form_textarea(array('name'=>'POemailmsg','id'=>'POemailmsg','rows'=>'4','class'=>'md-form-input required','value'=>$this->config->item('POemailmsg'))); ?>
        </div>
    </div>

    </div><!-- /md-grid-2eq -->

</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB 6 — TECHNIQUE                                                  -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<div id="tab-technique" class="config-tab-panel" style="display:none">

    <div class="md-grid-2eq">

    <!-- Card : Impression -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Impression
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_ticket_printer'); ?></label>
            <?php echo form_input(array('name'=>'ticket_printer','id'=>'ticket_printer','class'=>'md-form-input required','value'=>$this->config->item('ticket_printer'))); ?>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:0 0 auto;">
                <label class="md-form-label"><?php echo $this->lang->line('config_print_after_sale'); ?></label>
                <div class="md-toggle-group">
                    <label class="md-toggle">
                        <input type="hidden" name="print_after_sale" value="0">
                        <?php echo form_checkbox(array('name'=>'print_after_sale','id'=>'print_after_sale','value'=>'print_after_sale','checked'=>$this->config->item('print_after_sale'),'class'=>'md-toggle-input')); ?>
                        <span class="md-toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('config_print_receipt_for_categories'); ?></label>
                <div style="display:flex;align-items:center;gap:6px;">
                    <?php echo form_input(array('name'=>'print_receipt_categories','id'=>'print_receipt_categories','class'=>'md-form-input','value'=>$this->config->item('print_receipt_categories'))); ?>
                    <span style="font-size:0.78em;color:var(--text-secondary,#64748b);white-space:nowrap;"><?php echo $this->lang->line('config_print_receipt_for_categories_format'); ?></span>
                </div>
            </div>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_default_tax_rate_1'); ?></label>
            <div style="display:flex;align-items:center;gap:8px;">
                <?php echo form_input(array('name'=>'default_tax_1_name','id'=>'default_tax_1_name','class'=>'md-form-input required','style'=>'max-width:150px;','value'=>$this->config->item('default_tax_1_name')!==FALSE ? $this->config->item('default_tax_1_name') : $this->lang->line('items_sales_tax_1'))); ?>
                <?php echo form_input(array('name'=>'default_tax_1_rate','id'=>'default_tax_1_rate','class'=>'md-form-input','style'=>'max-width:80px;text-align:right;','value'=>$this->config->item('default_tax_1_rate'))); ?>
                <span style="font-size:0.9em;font-weight:600;">%</span>
            </div>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('config_create_stock_valuation_records'); ?></label>
                <?php echo form_dropdown('createstockvaluationrecords', $_SESSION['G']->YorN_pick_list, $this->config->item('createstockvaluationrecords'), 'class="md-form-select"'); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label"><?php echo $this->lang->line('config_create_category_records'); ?></label>
                <?php echo form_dropdown('createcategoryrecords', $_SESSION['G']->YorN_pick_list, $this->config->item('createcategoryrecords'), 'class="md-form-select"'); ?>
            </div>
        </div>
    </div>

    <!-- Card : Repertoires -->
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Repertoires de stockage
        </div>
        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('config_POsavepath'); ?></label>
            <?php echo form_input(array('name'=>'POsavepath','id'=>'POsavepath','class'=>'md-form-input','value'=>$this->config->item('POsavepath'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_RPsavepath'); ?></label>
            <?php echo form_input(array('name'=>'RPsavepath','id'=>'RPsavepath','class'=>'md-form-input required','value'=>$this->config->item('RPsavepath'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_BUsavepath'); ?></label>
            <?php echo form_input(array('name'=>'BUsavepath','id'=>'BUsavepath','class'=>'md-form-input required','value'=>$this->config->item('BUsavepath'))); ?>
        </div>

        <div style="border-top:1px solid var(--border-color,#e2e8f0);margin:12px 0;"></div>
        <div style="font-size:0.78em;font-weight:600;text-transform:uppercase;color:var(--text-secondary,#64748b);margin-bottom:8px;">MaJ Prix Achat/Vente</div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:2">
                <label class="md-form-label required"><?php echo $this->lang->line('config_PPsavepath'); ?></label>
                <?php echo form_input(array('name'=>'PPsavepath','id'=>'PPsavepath','class'=>'md-form-input required','value'=>$this->config->item('PPsavepath'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_PPfilename'); ?></label>
                <?php echo form_input(array('name'=>'PPfilename','id'=>'PPfilename','class'=>'md-form-input required','value'=>$this->config->item('PPfilename'))); ?>
            </div>
        </div>
        <div class="md-form-row">
            <div class="md-form-group" style="flex:2">
                <label class="md-form-label required"><?php echo $this->lang->line('config_SPsavepath'); ?></label>
                <?php echo form_input(array('name'=>'SPsavepath','id'=>'SPsavepath','class'=>'md-form-input required','value'=>$this->config->item('SPsavepath'))); ?>
            </div>
            <div class="md-form-group" style="flex:1">
                <label class="md-form-label required"><?php echo $this->lang->line('config_SPfilename'); ?></label>
                <?php echo form_input(array('name'=>'SPfilename','id'=>'SPfilename','class'=>'md-form-input required','value'=>$this->config->item('SPfilename'))); ?>
            </div>
        </div>
    </div>

    </div><!-- /md-grid-2eq -->

    <!-- Card : Etiquettes + Custom (full width) -->
    <div class="md-grid-2eq" style="margin-top:12px;">
    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Etiquettes
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_label_font'); ?></label>
            <?php echo form_input(array('name'=>'default_label_font','id'=>'default_label_font','class'=>'md-form-input required','value'=>$this->config->item('default_label_font'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_label_image'); ?></label>
            <?php echo form_input(array('name'=>'default_label_image','id'=>'default_label_image','class'=>'md-form-input required','value'=>$this->config->item('default_label_image'))); ?>
        </div>
        <div class="md-form-group">
            <label class="md-form-label required"><?php echo $this->lang->line('config_label_store'); ?></label>
            <?php echo form_input(array('name'=>'default_label_store','id'=>'default_label_store','class'=>'md-form-input required','value'=>$this->config->item('default_label_store'))); ?>
        </div>
    </div>

    <div class="md-card">
        <div class="md-card-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Configuration Custom
        </div>
        <?php for ($i = 2; $i <= 10; $i++) { ?>
        <div class="md-form-group">
            <label class="md-form-label"><?php echo $this->lang->line('config_custom'.$i); ?></label>
            <?php echo form_input(array('name'=>'custom'.$i.'_name','id'=>'custom'.$i.'_name','class'=>'md-form-input','value'=>$this->config->item('custom'.$i.'_name'))); ?>
        </div>
        <?php } ?>
    </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- END TABS                                                           -->
<!-- ═══════════════════════════════════════════════════════════════════ -->

            <div id="required_fields_message" class="obligatoire">
                <a class="btobligatoire" title="<?php $this->lang->line('common_fields_required_message')?>"></a>
                <?php echo $this->lang->line('common_fields_required_message'); ?>
            </div>
            </fieldset>
        </div>
    </div>
</div>

        <div class="txt_droite">
            <?php
            echo form_submit(array(
                    'name'      => 'submit',
                    'id'        => 'submit',
                    'value'     => $this->lang->line('common_submit'),
                    'class'     => 'btsubmit'
                )
            );
            ?>
        </div>
<?php echo form_close(); ?>

<div id="feedback_bar"></div>

                            </div>
                        </div>

                    </div>
                </div>

            </main></div></div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════ -->
<!-- TAB SWITCHING + VALIDATION                                         -->
<!-- ═══════════════════════════════════════════════════════════════════ -->
<script>
$(document).ready(function() {

    // ─── Auto-hide success toast after 4s ────────────────────────────
    var $toast = $('#config-toast');
    if ($toast.length) {
        setTimeout(function() {
            $toast.css('opacity', '0');
            setTimeout(function() { $toast.remove(); }, 500);
        }, 4000);
    }

    // ─── Tab navigation ──────────────────────────────────────────────
    $('#config-tab-bar .md-tab').on('click', function(e) {
        e.preventDefault();
        var tabId = $(this).data('tab');
        $('#config-tab-bar .md-tab').removeClass('md-tab-active');
        $(this).addClass('md-tab-active');
        $('.config-tab-panel').hide();
        $('#' + tabId).show();
        localStorage.setItem('wm-config-active-tab', tabId);
    });

    // Restore active tab from localStorage
    var saved = localStorage.getItem('wm-config-active-tab');
    if (saved && $('#' + saved).length) {
        $('#config-tab-bar .md-tab').removeClass('md-tab-active');
        $('#config-tab-bar .md-tab[data-tab="' + saved + '"]').addClass('md-tab-active');
        $('.config-tab-panel').hide();
        $('#' + saved).show();
    }

    // ─── Touchscreen toggle visual feedback ─────────────────────────
    $('.md-toggle-input[name="touchscreen"]').on('change', function() {
        $(this).closest('.md-toggle-group').find('.md-toggle-value').text(this.checked ? 'ON' : 'OFF');
    });

    // ─── Form validation ─────────────────────────────────────────────
    $('#config_form').validate({
        ignore: [],  // Validate ALL fields, even in hidden tabs
        submitHandler: function(form) {
            form.submit();
        },
        invalidHandler: function(event, validator) {
            // Auto-switch to the tab containing the first error
            var el = validator.errorList[0].element;
            var panel = $(el).closest('.config-tab-panel');
            if (panel.length && !panel.is(':visible')) {
                var tabId = panel.attr('id');
                $('#config-tab-bar .md-tab').removeClass('md-tab-active');
                $('#config-tab-bar .md-tab[data-tab="' + tabId + '"]').addClass('md-tab-active');
                $('.config-tab-panel').hide();
                panel.show();
                localStorage.setItem('wm-config-active-tab', tabId);
            }
        },
        errorLabelContainer: "#error_message_box",
        wrapper: "li",
        rules: {
            branch_code: "required",
            branch_opened: "required",
            company: "required",
            address: "required",
            phone: "required",
            default_tax_rate: {
                required: true,
                number: true
            },
            email: "email",
            return_policy: "required",
            dateformat: "required",
            timeformat: "required",
            numberformat: "required",
            default_client_id: "required"
        },
        messages: {
            branch_code: "<?php echo $this->lang->line('config_branch_required'); ?>",
            branch_opened: "<?php echo $this->lang->line('config_opened_required'); ?>",
            company: "<?php echo $this->lang->line('config_company_required'); ?>",
            address: "<?php echo $this->lang->line('config_address_required'); ?>",
            phone: "<?php echo $this->lang->line('config_phone_required'); ?>",
            default_tax_rate: {
                required: "<?php echo $this->lang->line('config_default_tax_rate_required'); ?>",
                number: "<?php echo $this->lang->line('config_default_tax_rate_number'); ?>"
            },
            email: "<?php echo $this->lang->line('common_email_invalid_format'); ?>",
            return_policy: "<?php echo $this->lang->line('config_return_policy_required'); ?>",
            dateformat: "<?php echo $this->lang->line('config_dateformat_required'); ?>",
            timeformat: "<?php echo $this->lang->line('config_timeformat_required'); ?>",
            numberformat: "<?php echo $this->lang->line('config_numberformat_required'); ?>",
            default_client_id: "<?php echo $this->lang->line('config_default_client_id_required'); ?>"
        }
    });

});
</script>

<?php $this->load->view("partial/footer"); ?>

<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url();?>/jquery-ui-1.12.1.custom/my_calendar.js"></script>
