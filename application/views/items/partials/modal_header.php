<?php
/**
 * Modern modal header partial for item detail
 * Displays product avatar, reference, name, and close button
 *
 * Expects: $_SESSION['transaction_info'], $_SESSION['show_image']
 */

// Expose item_id to JavaScript for AJAX tab navigation fallback
$item_id_for_js = isset($_SESSION['transaction_info']->item_id) ? $_SESSION['transaction_info']->item_id : -1;
?>
<script type="text/javascript">
window.CURRENT_ITEM_ID = <?php echo (int)$item_id_for_js; ?>;
</script>
<?php

// Load product image
$header_image_url = base_url() . 'SLIDES_VENTES/cadre.png';
if ($_SESSION['show_image'] != 'N' && isset($_SESSION['transaction_info']->item_id)) {
    $header_img_data = $this->Item->get_info($_SESSION['transaction_info']->item_id);
    if (!empty($header_img_data->image_file_name)) {
        // Vérifier si c'est une URL valide (contient un domaine avec au moins un point)
        if (preg_match('/^[a-zA-Z0-9][-a-zA-Z0-9]*\.[a-zA-Z]/', $header_img_data->image_file_name)) {
            $header_image_url = 'http://' . $header_img_data->image_file_name;
        }
    }
}
?>
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar">
            <img id="header-avatar-image" src="<?php echo $header_image_url; ?>" alt="" onerror="this.src='<?php echo base_url(); ?>SLIDES_VENTES/cadre.png'">
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-ref"><?php echo htmlspecialchars($_SESSION['transaction_info']->item_number); ?></div>
            <h2 class="md-modal-name"><?php echo htmlspecialchars($_SESSION['transaction_info']->name); ?></h2>
        </div>
    </div>
    <div class="md-modal-header-actions">
<?php
$is_new = (($_SESSION['new'] ?? 0) == 1);
$show_fuzzy = (!$is_new
    && !empty($_SESSION['transaction_info']->item_number)
    && substr($_SESSION['transaction_info']->item_number, 0, 2) !== 'SO'
    && substr($_SESSION['transaction_info']->item_number, 0, 2) !== 'PK'
    && (!isset($_SESSION['transaction_info']->deleted) || $_SESSION['transaction_info']->deleted != 1)
);
if ($show_fuzzy): ?>
        <button type="button" class="md-modal-search" id="btn-fuzzy-search-merge"
                data-ajax-url="<?php echo site_url('items/ajax_fuzzy_search_merge'); ?>"
                data-item-id="<?php echo (int)$_SESSION['transaction_info']->item_id; ?>"
                title="Rechercher un article d&eacute;sactiv&eacute; similaire">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
<?php endif; ?>
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>
