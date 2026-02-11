<?php
/**
 * Modern modal footer partial for item detail
 * Adapts based on $_SESSION['show_dialog']:
 *   - Article tab (1, 18): Delete/Undelete + Cancel/Save
 *   - Suppliers tab (9): Close only
 *   - Pricelists tab (11): Close only
 *
 * Expects: $_SESSION['new'], $_SESSION['undel'], $_SESSION['del'], $_SESSION['show_dialog']
 */

$active_dialog = isset($_SESSION['show_dialog']) ? $_SESSION['show_dialog'] : 1;
$is_article_tab = ($active_dialog == 1 || $active_dialog == 18);
?>
<div class="md-modal-footer">
    <div class="md-modal-footer-left">
    </div>
    <div class="md-modal-footer-right">
        <?php if ($is_article_tab && $_SESSION['undel'] == NULL && $_SESSION['del'] == NULL) { ?>
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
                <?php echo $this->lang->line('common_reset'); ?>
            </a>
            <button type="submit" form="item_form" name="submit" id="submit" class="md-btn md-btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <?php echo $this->lang->line('common_submit'); ?>
            </button>
        <?php } else { ?>
            <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
                Fermer
            </a>
        <?php } ?>
    </div>
</div>
