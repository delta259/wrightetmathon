<?php
$is_new   = (($_SESSION['new'] ?? 0) == 1);
$is_del   = (($_SESSION['del'] ?? 0) == 1);
$is_undel = (($_SESSION['undel'] ?? 0) == 1);
$info     = $_SESSION['transaction_info'];

$title = $is_new ? 'Nouvel employ&eacute;' : 'Modifier employ&eacute;';
$initials = '';
if (!$is_new) {
    $fn = $info->first_name ?? '';
    $ln = $info->last_name ?? '';
    $title .= ' &mdash; ' . ucfirst(strtolower($fn)) . ' ' . strtoupper($ln);
    $initials = strtoupper(mb_substr($fn, 0, 1) . mb_substr($ln, 0, 1));
}

// DOB value
$dob_val = '';
if (!empty($info->dob_day) && !empty($info->dob_month) && !empty($info->dob_year)) {
    $dob_val = $info->dob_day . '/' . $info->dob_month . '/' . $info->dob_year;
}

// Password required only for new employees
$pwd_required = $is_new ? 'required' : '';
?>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 680px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.7em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: var(--primary, #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1em; font-weight: 700; border-radius: 50%; width: 40px; height: 40px;">
            <?php if ($initials) { echo $initials; } else { ?>
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <?php } ?>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1.05em;"><?php echo $title; ?></h2>
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

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.8em 1.2em;">

<?php if (!$is_del) { ?>
<?php if (!$is_undel) { echo form_open($_SESSION['controller_name'].'/save/', array('id' => 'item_form')); } ?>

<!-- Card: Identit&eacute; + Contact + Adresse -->
<div class="md-card" style="padding: 0.7em 0.9em;">
    <div class="md-card-title" style="font-size: 0.82em;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
        Identit&eacute; &amp; Contact
    </div>

    <!-- Nom, Pr&eacute;nom, Date naiss., Sexe -->
    <div class="md-form-row" style="gap: 0.5em; margin-bottom: 0.4em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_last_name') ?: 'Nom'; ?></label>
            <?php echo form_input(array('name'=>'last_name','id'=>'last_name','class'=>'md-form-input required',
                'value'=>$info->last_name ?? '','placeholder'=>'Nom')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_first_name') ?: 'Pr&eacute;nom'; ?></label>
            <?php echo form_input(array('name'=>'first_name','id'=>'first_name','class'=>'md-form-input required',
                'value'=>$info->first_name ?? '','placeholder'=>'Pr&eacute;nom')); ?>
        </div>
        <div class="md-form-group" style="flex: 0 0 110px;">
            <label class="md-form-label"><?php echo 'Date naiss.'; ?></label>
            <?php echo form_input(array('name'=>'dob','id'=>'dob','class'=>'md-form-input',
                'value'=>$dob_val,'placeholder'=>'JJ/MM/AAAA','style'=>'text-align:center;')); ?>
        </div>
        <div class="md-form-group" style="flex: 0 0 90px;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_sex') ?: 'Sexe'; ?></label>
            <?php echo form_dropdown('sex', $_SESSION['G']->sex_pick_list ?? array(), $info->sex ?? '',
                'class="md-form-select required" id="sex"'); ?>
        </div>
    </div>

    <!-- Email, T&eacute;l&eacute;phone -->
    <div class="md-form-row" style="gap: 0.5em; margin-bottom: 0.4em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 2;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_email') ?: 'Email'; ?></label>
            <?php echo form_input(array('name'=>'email','id'=>'email','class'=>'md-form-input required',
                'value'=>$info->email ?? '','placeholder'=>'email@exemple.com','type'=>'email')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label"><?php echo $this->lang->line('common_phone_number') ?: 'T&eacute;l&eacute;phone'; ?></label>
            <?php echo form_input(array('name'=>'phone_number','id'=>'phone_number','class'=>'md-form-input',
                'value'=>$info->phone_number ?? '','placeholder'=>'T&eacute;l&eacute;phone')); ?>
        </div>
    </div>

    <!-- Separator -->
    <div style="border-top: 1px solid var(--border-color, #e2e8f0); margin: 0.5em 0;"></div>

    <!-- Adresse 1, Adresse 2 -->
    <div class="md-form-row" style="gap: 0.5em; margin-bottom: 0.4em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label"><?php echo $this->lang->line('common_address_1') ?: 'Adresse 1'; ?></label>
            <?php echo form_input(array('name'=>'address_1','id'=>'address_1','class'=>'md-form-input',
                'value'=>$info->address_1 ?? '','placeholder'=>'Adresse')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label"><?php echo $this->lang->line('common_address_2') ?: 'Adresse 2'; ?></label>
            <?php echo form_input(array('name'=>'address_2','id'=>'address_2','class'=>'md-form-input',
                'value'=>$info->address_2 ?? '','placeholder'=>'Compl&eacute;ment')); ?>
        </div>
    </div>

    <!-- CP, Ville, R&eacute;gion, Pays -->
    <div class="md-form-row" style="gap: 0.5em; margin-bottom: 0.4em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 0 0 80px;">
            <label class="md-form-label required"><?php echo $this->lang->line('common_zip') ?: 'CP'; ?></label>
            <?php echo form_input(array('name'=>'zip','id'=>'zip','class'=>'md-form-input required',
                'value'=>$info->zip ?? '','placeholder'=>'CP','style'=>'text-align:center;')); ?>
        </div>
        <div class="md-form-group" style="flex: 1; position: relative;">
            <label class="md-form-label"><?php echo $this->lang->line('common_city') ?: 'Ville'; ?></label>
            <?php echo form_input(array('name'=>'city','id'=>'city','class'=>'md-form-input',
                'value'=>$info->city ?? '','placeholder'=>'Ville')); ?>
            <div id="city-suggestions" class="zip-city-suggestions" style="display:none;"></div>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label"><?php echo $this->lang->line('common_state') ?: 'R&eacute;gion'; ?></label>
            <?php echo form_input(array('name'=>'state','id'=>'state','class'=>'md-form-input',
                'value'=>$info->state ?? '','placeholder'=>'R&eacute;gion')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label"><?php echo $this->lang->line('common_country') ?: 'Pays'; ?></label>
            <?php echo form_dropdown('country_id', $_SESSION['G']->country_pick_list ?? array(), $info->country_id ?? '',
                'class="md-form-select" id="country_id"'); ?>
        </div>
    </div>

    <?php if ($this->config->item('person_show_comments') == 'Y') { ?>
    <div class="md-form-row" style="gap: 0.5em; margin-bottom: 0.2em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label"><?php echo $this->lang->line('common_comments') ?: 'Commentaires'; ?></label>
            <?php echo form_textarea(array('name'=>'comments','id'=>'comments','class'=>'md-form-input',
                'value'=>$info->comments ?? '','placeholder'=>'Commentaires','rows'=>2,'style'=>'height:auto;')); ?>
        </div>
    </div>
    <?php } ?>
</div>

<!-- Card: Identifiants -->
<div class="md-card" style="padding: 0.7em 0.9em; margin-top: 0.5em;">
    <div class="md-card-title" style="font-size: 0.82em;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        Identifiants
    </div>

    <div class="md-form-row" style="gap: 0.5em; margin-bottom: 0.2em; align-items: flex-end;">
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label required"><?php echo $this->lang->line('employees_username') ?: 'Identifiant'; ?></label>
            <?php echo form_input(array('name'=>'username','id'=>'username','class'=>'md-form-input required',
                'value'=>$info->username ?? '','placeholder'=>'Identifiant (min. 5 car.)','autocomplete'=>'off')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label <?php echo $pwd_required; ?>"><?php echo $this->lang->line('employees_password') ?: 'Mot de passe'; ?></label>
            <?php echo form_input(array('name'=>'password','id'=>'password','type'=>'password',
                'class'=>'md-form-input '.$pwd_required,'value'=>'','placeholder'=>'Mot de passe (min. 8 car.)','autocomplete'=>'new-password')); ?>
        </div>
        <div class="md-form-group" style="flex: 1;">
            <label class="md-form-label <?php echo $pwd_required; ?>"><?php echo $this->lang->line('employees_repeat_password') ?: 'Confirmer'; ?></label>
            <?php echo form_input(array('name'=>'repeat_password','id'=>'repeat_password','type'=>'password',
                'class'=>'md-form-input '.$pwd_required,'value'=>'','placeholder'=>'Confirmer mot de passe','autocomplete'=>'new-password')); ?>
        </div>
    </div>

    <?php if (!$is_new) { ?>
    <div style="font-size: 0.75em; color: var(--text-secondary, #64748b); margin-top: 0.3em;">
        Laissez les champs mot de passe vides pour conserver le mot de passe actuel.
    </div>
    <?php } ?>
</div>

<div class="md-required-note" style="font-size: 0.75em; color: var(--text-secondary, #94a3b8); margin-top: 0.4em;">
    <span style="color: var(--danger, #ef4444);">*</span> Champs obligatoires
</div>

<?php if (!$is_undel) { echo form_close(); } ?>
<?php } ?>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            <?php echo $this->lang->line('common_reset') ?: 'Annuler'; ?>
        </a>
        <?php if (!$is_undel && !$is_del) { ?>
        <button type="submit" form="item_form" name="submit" id="submit" class="md-btn md-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            <?php echo $this->lang->line('common_submit') ?: 'Enregistrer'; ?>
        </button>
        <?php } ?>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<style>
/* City suggestions dropdown */
.zip-city-suggestions {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 2100;
    background: var(--bg-container, #fff); border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-height: 180px; overflow-y: auto;
}
.zip-city-suggestions .zip-city-item {
    padding: 6px 10px; cursor: pointer; font-size: 0.85em;
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid var(--border-color, #f0f0f0);
    color: var(--text-primary, #1e293b);
}
.zip-city-suggestions .zip-city-item:last-child { border-bottom: none; }
.zip-city-suggestions .zip-city-item:hover,
.zip-city-suggestions .zip-city-item.active { background: var(--primary, #2563eb); color: #fff; }
.zip-city-suggestions .zip-city-item .zip-city-dept { font-size: 0.8em; opacity: 0.7; margin-left: 8px; white-space: nowrap; }
.zip-city-suggestions .zip-city-item:hover .zip-city-dept,
.zip-city-suggestions .zip-city-item.active .zip-city-dept { opacity: 1; }
.zip-city-suggestions .zip-city-loading { padding: 8px 10px; text-align: center; font-size: 0.82em; color: var(--text-secondary, #64748b); }
</style>

<script src="<?php echo base_url(); ?>/jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
<script src="<?php echo base_url(); ?>/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="<?php echo base_url(); ?>/jquery-ui-1.12.1.custom/my_calendar2.js"></script>

<script>
$(document).ready(function() {
    // Focus on first field
    <?php if ($is_new) { ?>
    $('#last_name').focus();
    <?php } ?>

    // --- Datepicker on DOB field ---
    $('#dob').datepicker({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '1930:<?php echo date("Y"); ?>',
        maxDate: '-18y',
        defaultDate: '-30y',
        monthNames: ['Janvier','F\u00e9vrier','Mars','Avril','Mai','Juin','Juillet','Ao\u00fbt','Septembre','Octobre','Novembre','D\u00e9cembre'],
        monthNamesShort: ['Jan','F\u00e9v','Mar','Avr','Mai','Jun','Jul','Ao\u00fb','Sep','Oct','Nov','D\u00e9c'],
        dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
        dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
        dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
        firstDay: 1
    });
});

// --- City auto-suggestion from postal code ---
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
