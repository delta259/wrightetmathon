<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    init_table_sorting();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');

    // Clickable rows
    $(document).on('click', '.clickable-row', function(e) {
        if ($(e.target).closest('a').length || $(e.target).closest('.btn-view-items').length) return;
        var href = $(this).data('href');
        if (href) window.location = href;
    });

    // Load items when clicking "Voir" button
    $(document).on('click', '.btn-view-items', function(e) {
        e.stopPropagation();
        var categoryId = $(this).attr('data-category-id');
        if (categoryId) {
            $('#sortable_table tbody tr').removeClass('category-selected');
            $(this).parents('tr:first').addClass('category-selected');
            loadCategoryItems(categoryId);
        }
    });
});

function init_table_sorting() {
    if($('.tablesorter tbody tr').length > 1) {
        $("#sortable_table").tablesorter({
            sortList: [[0,0]],
            headers: { 6: { sorter: false }, 7: { sorter: false } }
        });
    }
}

function loadCategoryItems(categoryId) {
    $('#category-items-container').html('<p style="text-align:center;padding:20px;">Chargement...</p>');
    $.get('<?php echo site_url("categories/get_items"); ?>/' + categoryId, function(data) {
        $('#category-items-container').html(data);
    });
}
</script>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
        </svg>
        <?php echo $this->lang->line('modules_categories'); ?>
    </h1>
    <div class="page-actions">
        <?php
        $_SESSION['origin'] = "CG";
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
                    <th><?php echo $this->lang->line('common_last_name'); ?></th>
                    <th><?php echo $this->lang->line('categories_category_desc'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('categories_update_sales_price'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('categories_defect_indicator'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('items_reorder_pack_size'); ?></th>
                    <th style="text-align:center;"><?php echo $this->lang->line('categories_min_order_qty'); ?></th>
                    <th style="text-align:center;width:50px;">Actions</th>
                    <th style="text-align:center;width:60px;">Produits</th>
                </tr>
            </thead>
            <tbody id="table_contents">
            <?php if (!empty($manage_table_data)): ?>
                <?php foreach ($manage_table_data as $cat): ?>
                <?php
                    $update_label = ($cat['category_update_sales_price'] == 'Y') ? 'Oui' : 'Non';
                    $update_class = ($cat['category_update_sales_price'] == 'Y') ? 'badge-success' : 'badge-danger';
                    $defect_label = ($cat['category_defect_indicator'] == 'Y') ? 'Oui' : 'Non';
                    $defect_class = ($cat['category_defect_indicator'] == 'Y') ? 'badge-danger' : 'badge-success';
                ?>
                <tr class="clickable-row" data-href="<?php echo site_url('categories/view/'.$cat['category_id']); ?>" style="cursor:pointer;">
                    <td><strong><?php echo htmlspecialchars($cat['category_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($cat['category_desc'] ?? ''); ?></td>
                    <td style="text-align:center;"><span class="badge <?php echo $update_class; ?>"><?php echo $update_label; ?></span></td>
                    <td style="text-align:center;"><span class="badge <?php echo $defect_class; ?>"><?php echo $defect_label; ?></span></td>
                    <td style="text-align:center;"><?php echo (int)($cat['category_pack_size'] ?? 0); ?></td>
                    <td style="text-align:center;"><?php echo (int)($cat['category_min_order_qty'] ?? 0); ?></td>
                    <td style="text-align:center;">
                        <a href="#" onclick="if(confirm('<?php echo addslashes($this->lang->line('categories_confirm_delete')); ?>')){window.location='<?php echo site_url('categories/delete/'.$cat['category_id']); ?>';} return false;" title="Supprimer" style="text-decoration:none;">
                            <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </a>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="btn-view-items" data-category-id="<?php echo $cat['category_id']; ?>">Voir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b;">Aucune famille trouvée.</td></tr>
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

<!-- Items Container -->
<div class="table-container" style="margin-top: 20px;">
    <div class="section-header">
        <h2 class="section-title">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Produits associés
        </h2>
    </div>
    <div id="category-items-container" class="items-container">
        <p style="text-align:center;color:#64748b;padding:20px;">Cliquez sur une famille pour voir les produits associés.</p>
    </div>
</div>

<style>
.category-selected {
    background-color: #dbeafe !important;
}
.category-selected:hover {
    background-color: #bfdbfe !important;
}
.btn-view-items {
    padding: 4px 12px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}
.btn-view-items:hover {
    background: #1d4ed8;
}
.section-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
}
.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
}
.items-container {
    padding: 0;
    min-height: 100px;
}
.items-container table {
    margin: 0;
}
.items-header {
    padding: 12px 16px;
    background: var(--bg-card, #f8fafc);
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.9rem;
    color: var(--text-primary, #1e293b);
}
.item-row {
    cursor: pointer;
}
.item-row:hover {
    background-color: #f1f5f9 !important;
}
#sortable_table tbody tr {
    cursor: pointer;
}
</style>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php if (($_SESSION['show_dialog'] ?? 0) == 1): ?>
    <?php include('../wrightetmathon/application/views/categories/form.php'); ?>
<?php endif; ?>
