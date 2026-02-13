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
        $("#sortable_table").tablesorter({ sortList: [[2,0]], headers: {} });
    }
}
</script>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
        </svg>
        <?php echo $this->lang->line('modules_paymethods'); ?>
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
                    <th><?php echo $this->lang->line('paymethods_paymethod_code'); ?></th>
                    <th><?php echo $this->lang->line('paymethods_paymethod_description'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('paymethods_paymethod_display_order'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('paymethods_paymethod_include'); ?></th>
                </tr>
            </thead>
            <tbody id="table_contents">
            <?php if ($manage_table_data && $manage_table_data->num_rows() > 0): ?>
                <?php foreach ($manage_table_data->result() as $pm): ?>
                <?php
                    $include_label = ($pm->payment_method_include == 'Y') ? 'Oui' : 'Non';
                    $include_class = ($pm->payment_method_include == 'Y') ? 'badge-success' : 'badge-danger';
                ?>
                <tr class="clickable-row" data-href="<?php echo site_url('paymethods/view/'.$pm->payment_method_id); ?>" style="cursor:pointer;">
                    <td><?php echo htmlspecialchars($pm->payment_method_code); ?></td>
                    <td><strong><?php echo htmlspecialchars($pm->payment_method_description); ?></strong></td>
                    <td style="text-align:center;"><?php echo (int)$pm->payment_method_display_order; ?></td>
                    <td style="text-align:center;"><span class="badge <?php echo $include_class; ?>"><?php echo $include_label; ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b;">Aucune méthode de paiement trouvée.</td></tr>
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
    <?php include('../wrightetmathon/application/views/paymethods/form.php'); ?>
<?php endif; ?>
