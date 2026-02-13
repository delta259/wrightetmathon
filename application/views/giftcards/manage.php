<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function() {
    init_table_sorting();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest"); ?>','<?php echo $this->lang->line("common_confirm_search"); ?>');

    // Clickable rows
    $(document).on('click', '.gc-row', function(e) {
        if ($(e.target).closest('a,input').length) return;
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
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <?php echo $this->lang->line('common_list_of').' '.$this->lang->line('modules_giftcards'); ?>
    </div>
    <div class="page-actions" style="display:flex;align-items:center;gap:10px;">
        <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>
    </div>
</div>

<!-- Search bar -->
<div style="margin-bottom:12px;">
    <?php echo form_open("$controller_name/search", array('id'=>'search_form')); ?>
    <input type="text" id="search" name="search" class="md-form-input" placeholder="Recherche carte cadeau..." style="max-width:300px;">
    </form>
</div>

<!-- Messages -->
<?php
if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0) {
    include('../wrightetmathon/application/views/partial/show_messages.php');
}
// Flash messages
if (!empty($success_or_failure)) {
    $msg_class = ($success_or_failure == 'S') ? 'success_message' : 'error_message';
    echo '<div class="'.$msg_class.'" style="padding:8px;margin-bottom:10px;border-radius:6px;text-align:center;">'.htmlspecialchars($message ?? '').'</div>';
}
?>

<!-- Table -->
<style>
.gc-table-wrap { background:var(--bg-container,#fff); border:1px solid var(--border-color,#e2e8f0); border-radius:8px; overflow:hidden; }
.gc-table { width:100%; border-collapse:collapse; }
.gc-table th { font-size:0.75em; font-weight:600; text-transform:uppercase; letter-spacing:0.03em; color:#fff; background:#4386a1cc; padding:8px 10px; text-align:left; white-space:nowrap; }
.gc-table th:first-child { border-radius:8px 0 0 0; }
.gc-table th:last-child { border-radius:0 8px 0 0; }
.gc-table td { padding:6px 10px; font-size:0.88em; border-bottom:1px solid var(--border-color,#e2e8f0); color:var(--text-primary,#1e293b); }
.gc-table tbody tr:hover td { background:color-mix(in srgb, var(--primary,#2563eb) 4%, transparent); }
.gc-table .gc-row { cursor:pointer; }
.gc-badge-green { background:#dcfce7; color:#166534; border:1px solid #22c55e; padding:2px 8px; border-radius:12px; font-size:0.8rem; font-weight:500; }
.gc-badge-red { background:#fef2f2; color:#991b1b; border:1px solid #ef4444; padding:2px 8px; border-radius:12px; font-size:0.8rem; font-weight:500; }
.gc-badge-blue { background:#eff6ff; color:#1e40af; border:1px solid #3b82f6; padding:2px 8px; border-radius:12px; font-size:0.8rem; font-weight:500; }
[data-theme="dark"] .gc-table th { background:#4386a1; }
[data-theme="dark"] .gc-table td { border-bottom-color:rgba(255,255,255,0.06); }
[data-theme="dark"] .gc-badge-green { background:rgba(34,197,94,0.15); color:#86efac; border-color:rgba(34,197,94,0.3); }
[data-theme="dark"] .gc-badge-red { background:rgba(239,68,68,0.15); color:#fca5a5; border-color:rgba(239,68,68,0.3); }
[data-theme="dark"] .gc-badge-blue { background:rgba(59,130,246,0.15); color:#93c5fd; border-color:rgba(59,130,246,0.3); }
</style>

<div class="gc-table-wrap">
<table class="gc-table tablesorter" id="sortable_table">
    <thead>
        <tr>
            <th style="width:30px;text-align:center;"><input type="checkbox" id="select_all"></th>
            <th><?php echo $this->lang->line('giftcards_giftcard_number'); ?></th>
            <th><?php echo $this->lang->line('common_last_name'); ?></th>
            <th><?php echo $this->lang->line('common_first_name'); ?></th>
            <th style="text-align:right;"><?php echo $this->lang->line('giftcards_card_value'); ?></th>
            <th style="text-align:right;"><?php echo $this->lang->line('giftcards_card_value_used'); ?></th>
            <th style="text-align:right;"><?php echo $this->lang->line('giftcards_card_value_balance'); ?></th>
            <th style="text-align:center;"><?php echo $this->lang->line('sales_id'); ?></th>
            <th style="text-align:center;"><?php echo $this->lang->line('giftcards_sale_date'); ?></th>
            <th style="text-align:center;width:50px;">Actions</th>
        </tr>
    </thead>
    <tbody id="table_contents">
    <?php
    if ($manage_table_data && $manage_table_data->num_rows() > 0) {
        foreach ($manage_table_data->result() as $gc) {
            $balance = $gc->value - $gc->value_used;
            $bal_class = ($balance > 0) ? 'gc-badge-green' : 'gc-badge-red';
    ?>
        <tr class="gc-row" data-href="<?php echo site_url('giftcards/view/'.$gc->giftcard_id); ?>">
            <td style="text-align:center;"><input type="checkbox" id="giftcard_<?php echo $gc->giftcard_id; ?>" value="<?php echo $gc->giftcard_id; ?>"></td>
            <td><strong><?php echo htmlspecialchars($gc->giftcard_number); ?></strong></td>
            <td><?php echo htmlspecialchars($gc->last_name); ?></td>
            <td><?php echo htmlspecialchars($gc->first_name); ?></td>
            <td style="text-align:right;"><span class="gc-badge-blue"><?php echo to_currency($gc->value); ?></span></td>
            <td style="text-align:right;"><?php echo to_currency($gc->value_used); ?></td>
            <td style="text-align:right;"><span class="<?php echo $bal_class; ?>"><?php echo to_currency($balance); ?></span></td>
            <td style="text-align:center;"><?php if ($gc->sale_id > 0) { echo '<a href="'.site_url('sales/receipt/'.$gc->sale_id).'" style="color:var(--primary,#2563eb);">'.$gc->sale_id.'</a>'; } ?></td>
            <td style="text-align:center;"><?php echo htmlspecialchars($gc->sale_date); ?></td>
            <td style="text-align:center;">
                <a href="#" onclick="if(confirm('<?php echo addslashes($this->lang->line('giftcards_confirm_delete')); ?>')){window.location='<?php echo site_url('giftcards/delete_single/'.$gc->giftcard_id); ?>';} return false;" title="Supprimer" style="text-decoration:none;">
                    <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </a>
            </td>
        </tr>
    <?php
        }
    } else {
    ?>
        <tr><td colspan="10" style="text-align:center;padding:20px;color:#64748b;">
            <svg width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 8px;"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <?php echo $this->lang->line('common_no_persons_to_display'); ?>
        </td></tr>
    <?php } ?>
    </tbody>
</table>
</div>

<!-- Pagination -->
<div style="margin-top:10px;"><?php echo $this->pagination->create_links(); ?></div>

<?php
if (($_SESSION['show_dialog'] ?? 0) == 1) {
    include('../wrightetmathon/application/views/giftcards/form.php');
}
?>

<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
