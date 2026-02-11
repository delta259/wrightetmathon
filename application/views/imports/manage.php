<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
    enable_row_selection();
    if ($('.tablesorter tbody tr').length > 1) {
        $("#sortable_table").tablesorter({
            sortList: [[1, 0]],
            headers: { 0: { sorter: false } }
        });
    }
});
</script>

<?php $_SESSION['line_number'] = 0; ?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12"/>
        </svg>
        <?php echo $this->lang->line('modules_imports'); ?>
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
        <?php echo $manage_table; ?>
    </div>

    <?php if (!empty($links)): ?>
    <div class="table-footer">
        <div class="pagination-wrapper"><?php echo $links; ?></div>
    </div>
    <?php endif; ?>
</div>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php if (($_SESSION['show_dialog'] ?? 0) == 1): ?>
    <?php include('../wrightetmathon/application/views/'.$_SESSION['controller_name'].'/form.php'); ?>
<?php endif; ?>
