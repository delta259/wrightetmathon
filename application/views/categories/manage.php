<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    init_table_sorting();
    enable_row_selection();
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');

    // Add "Voir" button to each row
    $('#sortable_table tbody tr').each(function() {
        var firstTd = $(this).find('td:first');
        var categoryId = $.trim(firstTd.text());
        if (categoryId && !isNaN(categoryId)) {
            $(this).find('td:last').after('<td align="center"><button type="button" class="btn-view-items" data-category-id="' + categoryId + '">Voir</button></td>');
        }
    });

    // Add header for the new column
    $('#sortable_table thead tr').append('<th>Produits</th>');

    // Load items when clicking "Voir" button
    $('.btn-view-items').click(function() {
        var categoryId = $(this).attr('data-category-id');
        if (categoryId) {
            // Highlight selected row
            $('#sortable_table tbody tr').removeClass('category-selected');
            $(this).parents('tr:first').addClass('category-selected');
            // Load items
            loadCategoryItems(categoryId);
        }
    });
});

function init_table_sorting() {
    if($('.tablesorter tbody tr').length > 1) {
        $("#sortable_table").tablesorter({
            sortList: [[1,0]],
            headers: { 0: { sorter: false } }
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
        <?php echo $manage_table; ?>
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
