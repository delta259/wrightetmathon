<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    enable_search('<?php echo site_url("$controller_name/suggest")?>','<?php echo $this->lang->line("common_confirm_search")?>');
    init_table_sorting();
});
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
        <?php echo $manage_table; ?>
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
