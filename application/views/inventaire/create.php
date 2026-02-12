<?php $this->load->view("partial/header"); ?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Modal overlay (after footer, same pattern as items/modal_wrapper)
$this->load->view("partial/header_popup");
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width:700px;">

<!-- ========== MODAL HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background:var(--bg-card, #f0f9ff);">
            <svg width="28" height="28" fill="none" stroke="var(--modal-primary, #0A6184)" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                <line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref">Inventaire</div>
            <h2 class="md-modal-name"><?php echo $this->lang->line('inventaire_create_session'); ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('inventaire'); ?>" class="md-modal-close" title="Annuler">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
    </div>
</div>

<!-- ========== MODAL BODY ========== -->
<div class="md-modal-body" id="md-modal-body-content" style="padding:24px;">

    <?php echo form_open('inventaire/save_session', array('id' => 'create_session_form')); ?>

    <!-- Session Type -->
    <fieldset class="fieldset" style="margin-bottom:20px;">
        <legend style="font-weight:600;padding:0 8px;color:var(--modal-text, #1e293b);"><?php echo $this->lang->line('inventaire_select_type'); ?></legend>
        <div style="padding:12px;">
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="session_type" value="full" checked>
                <strong><?php echo $this->lang->line('inventaire_type_full'); ?></strong>
                <span style="color:var(--modal-text-muted, #64748b);font-size:0.85rem;"> - Tous les articles actifs</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="session_type" value="rolling">
                <strong><?php echo $this->lang->line('inventaire_type_rolling'); ?></strong>
                <span style="color:var(--modal-text-muted, #64748b);font-size:0.85rem;"> - Articles non encore inventori&eacute;s (indicateur tournant = 0)</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="session_type" value="partial">
                <strong><?php echo $this->lang->line('inventaire_type_partial'); ?></strong>
                <span style="color:var(--modal-text-muted, #64748b);font-size:0.85rem;"> - Par famille ou par date</span>
            </label>
        </div>
    </fieldset>

    <!-- Partial Options (hidden by default) -->
    <fieldset id="partial_options" class="fieldset" style="margin-bottom:20px;display:none;">
        <legend style="font-weight:600;padding:0 8px;color:var(--modal-text, #1e293b);">Options partiel</legend>
        <div style="padding:12px;">
            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="partial_mode" value="category" checked>
                <strong><?php echo $this->lang->line('inventaire_select_category'); ?></strong>
            </label>
            <div id="partial_category_group" style="margin-left:24px;margin-bottom:16px;">
                <select name="category_id" id="category_id" style="width:100%;padding:8px 12px;border:1px solid var(--modal-input-border, #cbd5e1);border-radius:6px;font-size:0.9rem;background:var(--modal-input-bg, #fff);color:var(--modal-text, #1e293b);">
                    <option value="">-- <?php echo $this->lang->line('inventaire_select_category'); ?> --</option>
                    <?php foreach ($categories->result() as $cat): ?>
                    <?php if (trim($cat->category_name) !== ''): ?>
                    <option value="<?php echo $cat->category_id; ?>"><?php echo htmlspecialchars($cat->category_name); ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="partial_mode" value="supplier">
                <strong>Par fournisseur</strong>
            </label>
            <div id="partial_supplier_group" style="margin-left:24px;margin-bottom:16px;display:none;">
                <select name="supplier_id" id="supplier_id" style="width:100%;padding:8px 12px;border:1px solid var(--modal-input-border, #cbd5e1);border-radius:6px;font-size:0.9rem;">
                    <option value="">-- Sélectionner un fournisseur --</option>
                    <?php foreach ($suppliers->result() as $sup): ?>
                    <?php if (trim($sup->company_name) !== ''): ?>
                    <option value="<?php echo $sup->person_id; ?>"><?php echo htmlspecialchars(strtoupper($sup->company_name)); ?></option>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="partial_mode" value="search">
                <strong>Par recherche</strong>
                <span style="color:var(--modal-text-muted, #64748b);font-size:0.85rem;"> - Nom ou r&eacute;f&eacute;rence</span>
            </label>
            <div id="partial_search_group" style="margin-left:24px;margin-bottom:16px;display:none;">
                <input type="text" name="search_term" id="search_term" autocomplete="off" placeholder="Ex: ACCU 18500, ELIQ MENTHE..." style="width:100%;padding:8px 12px;border:1px solid var(--modal-input-border, #cbd5e1);border-radius:6px;font-size:0.9rem;background:var(--modal-input-bg, #fff);color:var(--modal-text, #1e293b);">
                <div id="search_hint" style="margin-top:4px;font-size:0.8rem;color:var(--modal-text-muted, #64748b);">Plusieurs mots = tous doivent correspondre</div>
            </div>

            <label style="display:flex;align-items:center;gap:8px;margin-bottom:12px;cursor:pointer;font-size:0.95rem;color:var(--modal-text, #1e293b);">
                <input type="radio" name="partial_mode" value="date">
                <strong><?php echo $this->lang->line('inventaire_cutoff_date'); ?></strong>
            </label>
            <div id="partial_date_group" style="margin-left:24px;display:none;">
                <input type="date" name="cutoff_date" id="cutoff_date" style="width:100%;padding:8px 12px;border:1px solid var(--modal-input-border, #cbd5e1);border-radius:6px;font-size:0.9rem;background:var(--modal-input-bg, #fff);color:var(--modal-text, #1e293b);">
            </div>
        </div>
    </fieldset>

    <!-- Preview -->
    <div id="preview_box" style="background:var(--bg-card, #f0f9ff);border:1px solid var(--modal-primary, #0A6184);border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <svg width="18" height="18" fill="none" stroke="var(--modal-primary, #0A6184)" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <span style="font-size:0.9rem;color:var(--modal-text, #1e293b);">
            Articles concern&eacute;s : <strong id="preview_count" style="font-size:1.1rem;">-</strong>
        </span>
        <span id="preview_loading" style="display:none;font-size:0.8rem;color:var(--modal-text-muted, #64748b);">chargement...</span>
    </div>

    <!-- Notes -->
    <fieldset class="fieldset" style="margin-bottom:20px;">
        <legend style="font-weight:600;padding:0 8px;color:var(--modal-text, #1e293b);"><?php echo $this->lang->line('inventaire_notes'); ?></legend>
        <div style="padding:12px;">
            <textarea name="notes" rows="3" style="width:100%;padding:8px 12px;border:1px solid var(--modal-input-border, #cbd5e1);border-radius:6px;font-size:0.9rem;resize:vertical;background:var(--modal-input-bg, #fff);color:var(--modal-text, #1e293b);" placeholder="Notes optionnelles..."></textarea>
        </div>
    </fieldset>

    <?php echo form_close(); ?>

</div><!-- /md-modal-body -->

<!-- ========== MODAL FOOTER ========== -->
<div class="md-modal-footer">
    <div class="md-modal-footer-left">
        <a href="<?php echo site_url('inventaire'); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:6px;font-size:0.85rem;font-weight:500;text-decoration:none;background:var(--modal-bg, #fff);color:var(--modal-text, #1e293b);border:1px solid var(--modal-border, #e2e8f0);">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Annuler
        </a>
    </div>
    <div class="md-modal-footer-right">
        <button type="submit" form="create_session_form" style="display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border-radius:6px;font-size:0.85rem;font-weight:600;cursor:pointer;background:var(--modal-primary, #0A6184);color:#fff;border:none;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo $this->lang->line('inventaire_create_session'); ?>
        </button>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script type="text/javascript">
$(document).ready(function() {
    // Show/hide partial fields based on radio selection
    $('input[name="session_type"]').on('change', function() {
        var val = $(this).val();
        if (val === 'partial') {
            $('#partial_options').show();
        } else {
            $('#partial_options').hide();
        }
        updatePreview();
    });

    // Show/hide sub-options within partial
    $('input[name="partial_mode"]').on('change', function() {
        var mode = $(this).val();
        $('#partial_category_group').toggle(mode === 'category');
        $('#partial_supplier_group').toggle(mode === 'supplier');
        $('#partial_search_group').toggle(mode === 'search');
        $('#partial_date_group').toggle(mode === 'date');
        // Reset non-active fields
        if (mode !== 'category') $('#category_id').val('');
        if (mode !== 'supplier') { $('#supplier_id').val(''); }
        if (mode !== 'search') $('#search_term').val('');
        if (mode !== 'date') $('#cutoff_date').val('');
        updatePreview();
    });

    // Update preview on category, supplier, search or date change
    $('#category_id').on('change', function() { updatePreview(); });
    $('#supplier_id').on('change', function() { updatePreview(); });
    $('#cutoff_date').on('change', function() { updatePreview(); });

    // Search term: debounced preview on typing
    var searchTimer = null;
    $('#search_term').on('input', function() {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(updatePreview, 500);
    });

    // Preview item count
    var previewTimer = null;
    function updatePreview() {
        if (previewTimer) clearTimeout(previewTimer);
        previewTimer = setTimeout(doPreview, 300);
    }

    function doPreview() {
        var type = $('input[name="session_type"]:checked').val();
        var catId = '', cutoff = '', suppId = '', searchTerm = '';

        if (type === 'partial') {
            var mode = $('input[name="partial_mode"]:checked').val();
            if (mode === 'category') {
                catId = $('#category_id').val();
                if (!catId) { $('#preview_count').text('-'); return; }
            } else if (mode === 'supplier') {
                suppId = $('#supplier_id').val();
                if (!suppId) { $('#preview_count').text('-'); return; }
            } else if (mode === 'search') {
                searchTerm = $.trim($('#search_term').val());
                if (!searchTerm) { $('#preview_count').text('-'); return; }
            } else {
                cutoff = $('#cutoff_date').val();
                if (!cutoff) { $('#preview_count').text('-'); return; }
            }
        }

        $('#preview_loading').show();
        $.ajax({
            url: '<?php echo site_url("inventaire/preview_count"); ?>',
            type: 'POST',
            dataType: 'json',
            data: { session_type: type, category_id: catId, cutoff_date: cutoff, supplier_id: suppId, search_term: searchTerm },
            success: function(r) {
                $('#preview_count').text(r.count);
                $('#preview_loading').hide();
            },
            error: function() {
                $('#preview_count').text('?');
                $('#preview_loading').hide();
            }
        });
    }

    // Initial preview
    updatePreview();
});
</script>
