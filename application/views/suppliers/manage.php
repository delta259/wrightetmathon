<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function() {
    init_table_sorting();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest"); ?>','<?php echo $this->lang->line("common_confirm_search"); ?>');
    enable_email('<?php echo site_url("$controller_name/mailto"); ?>');

    // Clickable rows
    $(document).on('click', '.supplier-row', function(e) {
        if ($(e.target).closest('a').length) return;
        var href = $(this).data('href');
        if (href) window.location = href;
    });
});

function init_table_sorting() {
    if ($('.tablesorter tbody tr').length > 1) {
        $("#sortable_table").tablesorter({ sortList: [[1,0]], headers: { 0: { sorter: false } } });
    }
}
</script>

<!-- Page header -->
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;margin-bottom:12px;">
    <div class="page-title" style="display:flex;align-items:center;gap:10px;font-size:1.15em;font-weight:700;color:var(--text-primary,#1e293b);">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        <?php echo $this->lang->line('common_list_of').' '.$this->lang->line('modules_suppliers'); ?>
    </div>
    <div class="page-actions" style="display:flex;align-items:center;gap:10px;">
        <?php
        include('../wrightetmathon/application/views/partial/show_buttons.php');
        ?>
    </div>
</div>

<!-- Search bar -->
<div style="margin-bottom:12px;">
    <?php echo form_open("$controller_name/search", array('id'=>'search_form')); ?>
    <input type="text" id="search" name="search" class="md-form-input" placeholder="Recherche fournisseur..." style="max-width:300px;">
    </form>
</div>

<!-- Messages -->
<?php
if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0) {
    include('../wrightetmathon/application/views/partial/show_messages.php');
}
?>

<!-- Table -->
<style>
.sup-table-wrap { background:var(--bg-container,#fff); border:1px solid var(--border-color,#e2e8f0); border-radius:8px; overflow:hidden; }
.sup-table { width:100%; border-collapse:collapse; }
.sup-table th { font-size:0.75em; font-weight:600; text-transform:uppercase; letter-spacing:0.03em; color:#fff; background:#4386a1cc; padding:8px 10px; text-align:left; white-space:nowrap; }
.sup-table th:first-child { border-radius:8px 0 0 0; }
.sup-table th:last-child { border-radius:0 8px 0 0; }
.sup-table td { padding:6px 10px; font-size:0.88em; border-bottom:1px solid var(--border-color,#e2e8f0); color:var(--text-primary,#1e293b); }
.sup-table tbody tr:hover td { background:color-mix(in srgb, var(--primary,#2563eb) 4%, transparent); }
.sup-table .supplier-row { cursor:pointer; }
[data-theme="dark"] .sup-table th { background:#4386a1; }
[data-theme="dark"] .sup-table td { border-bottom-color:rgba(255,255,255,0.06); }
</style>

<div class="sup-table-wrap">
<table class="sup-table tablesorter" id="sortable_table">
    <thead>
        <tr>
            <th style="width:30px;text-align:center;"><input type="checkbox" id="select_all"></th>
            <th><?php echo $this->lang->line('suppliers_company_name'); ?></th>
            <th><?php echo $this->lang->line('common_last_name'); ?></th>
            <th><?php echo $this->lang->line('common_first_name'); ?></th>
            <th style="text-align:center;"><?php echo $this->lang->line('suppliers_account_number'); ?></th>
            <th><?php echo $this->lang->line('common_email'); ?></th>
            <th style="text-align:center;"><?php echo $this->lang->line('common_phone_number'); ?></th>
            <th style="text-align:center;width:50px;">Actions</th>
        </tr>
    </thead>
    <tbody id="table_contents">
    <?php
    if ($manage_table_data && $manage_table_data->num_rows() > 0) {
        foreach ($manage_table_data->result() as $supplier) {
    ?>
        <tr class="supplier-row" data-href="<?php echo site_url('suppliers/view/'.$supplier->person_id); ?>">
            <td style="text-align:center;"><input type="checkbox" id="<?php echo $supplier->person_id; ?>" value="<?php echo $supplier->person_id; ?>"></td>
            <td><strong><?php echo htmlspecialchars($supplier->company_name); ?></strong></td>
            <td><?php echo htmlspecialchars($supplier->last_name); ?></td>
            <td><?php echo htmlspecialchars($supplier->first_name); ?></td>
            <td style="text-align:center;"><span style="background:#eff6ff;color:#1e40af;border:1px solid #3b82f6;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;"><?php echo htmlspecialchars($supplier->account_number); ?></span></td>
            <td><?php echo htmlspecialchars($supplier->email); ?></td>
            <td style="text-align:center;"><?php echo htmlspecialchars($supplier->phone_number); ?></td>
            <td style="text-align:center;">
                <a href="#" onclick="if(confirm('<?php echo addslashes($this->lang->line('suppliers_confirm_delete')); ?>')){window.location='<?php echo site_url('suppliers/delete/'.$supplier->person_id); ?>';} return false;" title="<?php echo $this->lang->line('suppliers_delete'); ?>" style="text-decoration:none;">
                    <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </a>
            </td>
        </tr>
    <?php
        }
    } else {
    ?>
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b;">
            <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 8px;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
            <?php echo $this->lang->line('common_no_persons_to_display'); ?>
        </td></tr>
    <?php } ?>
    </tbody>
</table>
</div>

<!-- Pagination -->
<div style="margin-top:10px;"><?php echo $links; ?></div>

<?php echo form_close(); ?>

<?php
if (($_SESSION['show_dialog'] ?? 0) == 1) {
    include('../wrightetmathon/application/views/suppliers/form.php');
}
?>

<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
