<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
    init_table_sorting();

    $(document).on('click', '.clickable-row', function(e) {
        if ($(e.target).closest('a').length) return;
        var href = $(this).data('href');
        if (href) window.location = href;
    });
});

function init_table_sorting() {
    if ($('.tablesorter tbody tr').length > 1) {
        $("#sortable_table").tablesorter({ sortList: [[2,0]], headers: { 6: { sorter: false } } });
    }
}
</script>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M12 12h.01M8 12h.01M16 12h.01"/>
        </svg>
        <?php echo $this->lang->line('modules_currency_definitions'); ?>
    </h1>
    <div class="page-actions">
        <?php
        $_SESSION['origin'] = "AS";
        include('../wrightetmathon/application/views/partial/show_buttons.php');
        ?>
    </div>
</div>

<!-- Messages -->
<?php if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0): ?>
    <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
<?php endif; ?>

<!-- Filters Bar -->
<div class="filters-bar">
    <?php echo form_open("$controller_name/search", array('id' => 'search_form', 'class' => 'filters-form')); ?>
        <div class="filter-group">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" id="search" name="search" class="form-control search-field"
                       placeholder="Recherche..." tabindex="5" value="">
            </div>
        </div>
    </form>
</div>

<!-- Table Container -->
<div class="table-container">
    <div class="table-wrapper">
        <table class="tablesorter" id="sortable_table">
            <thead>
                <tr>
                    <th><?php echo $this->lang->line('currency_definitions_denomination'); ?></th>
                    <th><?php echo $this->lang->line('currency_definitions_display_name'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('currency_definitions_display_order'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('currency_definitions_type'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('currency_definitions_cashtill'); ?></th>
                    <th style="text-align:right;"><?php echo $this->lang->line('currency_definitions_multiplier'); ?></th>
                    <th style="text-align:center;width:50px;">Actions</th>
                </tr>
            </thead>
            <tbody id="table_contents">
            <?php if ($manage_table_data && $manage_table_data->num_rows() > 0): ?>
                <?php foreach ($manage_table_data->result() as $cd): ?>
                <?php
                    $type_label = ($cd->type == 'N') ? $this->lang->line('currency_definitions_type_N') : $this->lang->line('currency_definitions_type_C');
                    $type_class = ($cd->type == 'N') ? 'badge-success' : 'badge-info';
                ?>
                <tr class="clickable-row" data-href="<?php echo site_url('currency_definitions/view/'.$cd->denomination); ?>" style="cursor:pointer;">
                    <td><?php echo htmlspecialchars($cd->denomination); ?></td>
                    <td><strong><?php echo htmlspecialchars($cd->display_name); ?></strong></td>
                    <td style="text-align:center;"><?php echo (int)$cd->display_order; ?></td>
                    <td style="text-align:center;"><span class="badge <?php echo $type_class; ?>"><?php echo $type_label; ?></span></td>
                    <td style="text-align:center;"><?php echo htmlspecialchars($cd->cashtill); ?></td>
                    <td style="text-align:right;"><?php echo htmlspecialchars($cd->multiplier); ?></td>
                    <td style="text-align:center;">
                        <a href="#" onclick="if(confirm('Confirmation de la suppression ?')){window.location='<?php echo site_url('currency_definitions/delete/'.$cd->denomination); ?>';} return false;" title="Supprimer" style="text-decoration:none;">
                            <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b;">Aucune coupure trouvée.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($links)): ?>
    <div class="table-footer">
        <div class="pagination-wrapper"><?php echo $links; ?></div>
    </div>
    <?php endif; ?>
</div>

<?php echo form_close(); ?>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php if (($_SESSION['show_dialog'] ?? 0) == 1): ?>
    <?php include('../wrightetmathon/application/views/currency_definitions/form.php'); ?>
<?php endif; ?>
