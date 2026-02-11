<?php
/**
 * Main modal wrapper for item detail
 * Contains the shared modal chrome (header, tabs, footer)
 * Tab content is loaded into #md-modal-body-content
 *
 * Expects: $_SESSION['transaction_info'], $_SESSION['show_dialog'], $_SESSION['new']
 */

// Only include header_popup for non-AJAX requests
if (!$this->input->is_ajax_request()) {
    $this->load->view("partial/header_popup");
}

// Determine which tab content to load based on show_dialog
$active_dialog = isset($_SESSION['show_dialog']) ? $_SESSION['show_dialog'] : 1;
$item_id = isset($_SESSION['transaction_info']->item_id) ? $_SESSION['transaction_info']->item_id : -1;
$is_new = (($_SESSION['new'] ?? 0) == 1);

// Map show_dialog to tab content partial
$tab_content_map = array(
    1  => 'items/partials/tab_content_article',
    18 => 'items/partials/tab_content_article',
    9  => 'items/partials/tab_content_suppliers',
    11 => 'items/partials/tab_content_pricelists',
    3  => 'items/partials/tab_content_stock',
    4  => 'items/partials/tab_content_stock',
    17 => 'items/partials/tab_content_stock',
    6  => 'items/partials/tab_content_dluo',
    15 => 'items/partials/tab_content_kit',
    16 => 'items/partials/tab_content_kit',
    19 => 'items/partials/tab_content_sales',
);

$tab_content_view = isset($tab_content_map[$active_dialog]) ? $tab_content_map[$active_dialog] : 'items/partials/tab_content_article';
?>

<div class="md-modal-overlay">
<div class="md-modal">

<!-- ========== HEADER ========== -->
<?php include(APPPATH . 'views/items/partials/modal_header.php'); ?>

<!-- ========== TAB BAR ========== -->
<?php include(APPPATH . 'views/items/partials/tab_bar.php'); ?>

<!-- ========== BODY ========== -->
<div class="md-modal-body" id="md-modal-body-content">
    <?php $this->load->view($tab_content_view); ?>
</div>

<!-- ========== FOOTER ========== -->
<?php include(APPPATH . 'views/items/partials/modal_footer.php'); ?>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<?php if (!$this->input->is_ajax_request()) { ?>
<script type="text/javascript">
// Re-initialize modal handlers after content load
$(document).ready(function(){
    initModalAjaxTabs();
    initModalHandlers();
    initToggleActive();
});
</script>
<?php } ?>
