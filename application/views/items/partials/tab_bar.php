<?php
/**
 * Modern tab bar partial for item detail modal
 * Highlights the active tab based on $_SESSION['show_dialog']
 * Uses AJAX for tab switching to avoid reloading the items table
 *
 * Expects: $_SESSION['show_dialog'], $_SESSION['transaction_info'], $_SESSION['new']
 */

$active_dialog = isset($_SESSION['show_dialog']) ? $_SESSION['show_dialog'] : 1;
$item_id = isset($_SESSION['transaction_info']->item_id) ? $_SESSION['transaction_info']->item_id : -1;
$is_new = (($_SESSION['new'] ?? 0) == 1);
?>
<div class="md-tab-bar">
    <!-- Article tab -->
    <a href="<?php echo $is_new ? '#' : site_url('items/view/' . $item_id . '/' . $_SESSION['origin']); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 1 || $active_dialog == 18) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_article'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        Article
    </a>

    <?php if (!$is_new) { ?>
    <!-- Fournisseurs tab -->
    <a href="<?php echo site_url('items/view_suppliers'); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 9) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_suppliers'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
        Fournisseurs
    </a>

    <!-- Tarifs tab -->
    <a href="<?php echo site_url('items/view_pricelists'); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 11) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_pricelists'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
        Tarifs
    </a>

    <!-- Stock tab -->
    <a href="<?php echo site_url('items/inventory/' . $item_id); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 3 || $active_dialog == 4 || $active_dialog == 17) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_stock'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
            <polyline points="17 6 23 6 23 12"></polyline>
        </svg>
        Stock
    </a>

    <!-- Ventes tab -->
    <a href="<?php echo site_url('items/view_sales/' . $item_id); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 19) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_sales'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="20" x2="18" y2="10"></line>
            <line x1="12" y1="20" x2="12" y2="4"></line>
            <line x1="6" y1="20" x2="6" y2="14"></line>
        </svg>
        Ventes
    </a>

    <?php if (isset($_SESSION['selected_dluo_indicator']) && $_SESSION['selected_dluo_indicator'] == 'Y') { ?>
    <!-- DLUO tab -->
    <a href="<?php echo site_url('items/dluo_form/' . $item_id); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 6) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_dluo'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        DLUO
    </a>
    <?php } ?>

    <?php if (isset($_SESSION['transaction_info']->DynamicKit) && $_SESSION['transaction_info']->DynamicKit == 'Y') { ?>
    <!-- Kit tab -->
    <a href="<?php echo site_url('items/kit/' . $item_id); ?>"
       class="md-tab md-tab-ajax <?php echo ($active_dialog == 15 || $active_dialog == 16) ? 'md-tab-active' : ''; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_tab_kit'); ?>">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
        </svg>
        Kit
    </a>
    <?php } ?>

    <?php } /* end if !$is_new */ ?>

    <?php if (!$is_new) {
        $_tab_item_info = $this->Item->get_info($item_id);
        $is_deleted = (isset($_tab_item_info->deleted) && $_tab_item_info->deleted == '1');
    ?>
    <!-- Icône = action à effectuer (pas l'état actuel) -->
    <a href="#" id="btn-toggle-active"
       class="md-tab-action <?php echo $is_deleted ? 'md-tab-action-activate' : 'md-tab-action-deactivate'; ?>"
       data-item-id="<?php echo $item_id; ?>"
       data-deleted="<?php echo $is_deleted ? '1' : '0'; ?>"
       data-ajax-url="<?php echo site_url('items/ajax_toggle_active/' . $item_id); ?>">
        <?php if (!$is_deleted) { ?>
            <!-- Article ACTIF: icône œil barré = cliquer pour désactiver -->
            <svg class="icon-deactivate" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
            <svg class="icon-activate" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <span class="toggle-label-text">Désactiver</span>
        <?php } else { ?>
            <!-- Article INACTIF: icône œil ouvert = cliquer pour activer -->
            <svg class="icon-deactivate" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
            <svg class="icon-activate" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <span class="toggle-label-text">Activer</span>
        <?php } ?>
    </a>
    <?php } ?>
</div>
