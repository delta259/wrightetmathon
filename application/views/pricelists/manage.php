<?php $this->load->view("partial/header"); ?>
<script type="text/javascript">
$(document).ready(function()
{
    init_table_sorting();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
});

function init_table_sorting()
{
    if($('.tablesorter tbody tr').length > 1)
    {
        $("#sortable_table").tablesorter(
        {
            sortList: [[0,0]],
            headers:
            {
                4: { sorter: false}
            }
        });
    }
}
</script>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
            <line x1="7" y1="7" x2="7.01" y2="7"/>
        </svg>
        <?php echo $this->lang->line('modules_pricelists'); ?>
    </h1>
    <div class="page-actions">
        <a href="<?php echo site_url('pricelists/view/-1'); ?>" class="btn-action btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <?php echo $this->lang->line('pricelists_new'); ?>
        </a>
    </div>
</div>

<!-- Search -->
<div style="margin-bottom:16px;">
    <?php echo form_open("$controller_name/search", array('id'=>'search_form')); ?>
    <input id="search" name="search" type="text" placeholder="Nom, description..." tabindex="5"
           class="md-form-input" style="max-width:300px;padding:8px 12px;font-size:0.85rem;">
    </form>
</div>

<!-- Messages -->
<?php
if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0)
{
    include('../wrightetmathon/application/views/partial/show_messages.php');
}
?>

<!-- Table -->
<div class="table-container">
    <div class="table-wrapper">
        <?php
        $data_var = $pricelist_data ?? null;
        $has_rows = ($data_var && $data_var->num_rows() > 0);
        ?>
        <?php if ($has_rows): ?>
        <table class="tablesorter" id="sortable_table">
            <thead>
                <tr>
                    <th><?php echo $this->lang->line('pricelists_pricelist_name'); ?></th>
                    <th><?php echo $this->lang->line('pricelists_pricelist_description'); ?></th>
                    <th><?php echo $this->lang->line('pricelists_pricelist_currency'); ?></th>
                    <th><?php echo $this->lang->line('pricelists_pricelist_default'); ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_var->result() as $pl):
                    $currency_name = '';
                    if (isset($_SESSION['G']->currency_pick_list[$pl->pricelist_currency])) {
                        $currency_name = $_SESSION['G']->currency_pick_list[$pl->pricelist_currency];
                    } else {
                        $currency_name = $pl->pricelist_currency;
                    }
                ?>
                <tr class="pl-row" data-href="<?php echo site_url('pricelists/view/' . $pl->pricelist_id); ?>" style="cursor:pointer;">
                    <td><?php echo anchor('pricelists/view/' . $pl->pricelist_id, htmlspecialchars($pl->pricelist_name)); ?></td>
                    <td><?php echo htmlspecialchars($pl->pricelist_description); ?></td>
                    <td style="text-align:center;"><?php echo htmlspecialchars($currency_name); ?></td>
                    <td style="text-align:center;">
                        <?php if ($pl->pricelist_default === 'Y'): ?>
                        <span style="background:#dcfce7;color:#166534;border:1px solid #22c55e;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Oui</span>
                        <?php else: ?>
                        <span style="background:#fef2f2;color:#991b1b;border:1px solid #ef4444;padding:2px 8px;border-radius:12px;font-size:0.8rem;font-weight:500;">Non</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;white-space:nowrap;">
                        <a href="#" onclick="if(confirm('Supprimer cette liste de prix ?')){window.location='<?php echo site_url('pricelists/delete/' . $pl->pricelist_id); ?>';} return false;" title="Supprimer" style="text-decoration:none;">
                            <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center;padding:40px 20px;color:#64748b;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:12px;opacity:0.5;">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                <line x1="7" y1="7" x2="7.01" y2="7"/>
            </svg>
            <p><?php echo $this->lang->line('common_no_persons_to_display'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Pagination -->
<div style="margin-top:12px;"><?php echo $links; ?></div>

<style>
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s;
    cursor: pointer;
    border: none;
}
.btn-primary {
    background: var(--primary, #2563eb);
    color: #fff;
}
.btn-primary:hover {
    background: #1d4ed8;
    color: #fff;
}
#sortable_table {
    width: 100%;
    border-collapse: collapse;
}
#sortable_table thead th {
    background: #4386a1cc;
    color: #fff;
    padding: 8px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 0.85rem;
}
#sortable_table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.85rem;
}
#sortable_table tbody tr:hover {
    background: var(--bg-card, #f1f5f9);
}
#sortable_table tbody td a {
    color: var(--primary, #2563eb);
    text-decoration: none;
    font-weight: 500;
}
#sortable_table tbody td a:hover {
    text-decoration: underline;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    // Clickable rows (except Actions column)
    $('#sortable_table').on('click', 'tr.pl-row td:not(:last-child)', function() {
        window.location = $(this).closest('tr').data('href');
    });
});
</script>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Modal dialog for add/edit (after footer, like branches/manage.php)
if (($_SESSION['show_dialog'] ?? 0) == 1)
{
    include('../wrightetmathon/application/views/pricelists/form.php');
    $_SESSION['show_dialog'] = 0;
}
?>
