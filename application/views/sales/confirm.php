<?php
// Only include header_popup for non-AJAX requests
if (!$this->input->is_ajax_request()) {
    $this->load->view("partial/header_popup");
}

// Determine modal configuration based on confirm_what
$confirm_what = $_SESSION['confirm_what'];

switch ($confirm_what) {
    case 'invoice':
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
        $modal_gradient = '#22c55e, #16a34a';
        $modal_title = $this->lang->line('sales_confirm_finish_sale');
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = 'index.php/'.$_SESSION['controller_name'].'/complete';
        $has_comment = false;
        break;

    case 'suspend':
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
        $modal_gradient = '#f59e0b, #d97706';
        $modal_title = $this->lang->line('sales_confirm_suspend_sale');
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = null; // uses form submit
        $has_comment = true;
        break;

    case 'stocktransaction':
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
        $modal_gradient = '#22c55e, #16a34a';
        $modal_title = $this->lang->line('recvs_confirm_finish_receiving');
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = 'index.php/'.$_SESSION['controller_name'].'/complete';
        $has_comment = false;
        break;

    case 'canceltransaction':
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        $modal_gradient = '#ef4444, #dc2626';
        $modal_title = $this->lang->line('recvs_confirm_cancel_receiving');
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = 'index.php/'.$_SESSION['controller_name'].'/cancel_receiving';
        $has_comment = false;
        break;

    case 'suspendtransaction':
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
        $modal_gradient = '#f59e0b, #d97706';
        $modal_title = $this->lang->line('receivings_confirm_suspend_receiving');
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = 'index.php/'.$_SESSION['controller_name'].'/suspend_CMDE';
        $has_comment = false;
        break;

    case 'suspendreception':
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
        $modal_gradient = '#f59e0b, #d97706';
        $modal_title = $this->lang->line('receivings_confirm_suspend_reception');
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = 'index.php/'.$_SESSION['controller_name'].'/suspend_RCPT';
        $has_comment = false;
        break;

    case 'partialreceive':
        $pr_checked = $_SESSION['partial_receive_checked'] ?? 0;
        $pr_total = $_SESSION['partial_receive_total'] ?? 0;
        $pr_reliquat = $pr_total - $pr_checked;
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
        $modal_gradient = '#8b5cf6, #6d28d9';
        $modal_title = 'Réceptionner ' . $pr_checked . ' article(s) sur ' . $pr_total . ' ?<br><span style="font-size:0.85em;font-weight:400;color:var(--text-secondary,#64748b);">' . $pr_reliquat . ' article(s) seront mis en attente</span>';
        $modal_subtitle = $this->lang->line('modules_'.$_SESSION['controller_name']);
        $confirm_url = 'index.php/'.$_SESSION['controller_name'].'/partial_receive';
        $has_comment = false;
        break;

    default:
        $modal_icon = '<svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        $modal_gradient = '#64748b, #475569';
        $modal_title = 'Confirmation';
        $modal_subtitle = '';
        $confirm_url = null;
        $has_comment = false;
        break;
}

// Button style based on type
$is_danger = ($confirm_what === 'canceltransaction');
$is_warning = in_array($confirm_what, array('suspend', 'suspendtransaction', 'suspendreception'));
$is_partial = ($confirm_what === 'partialreceive');
$btn_class = $is_danger ? 'md-btn md-btn-danger' : ($is_warning ? 'md-btn confirm-btn-warning' : ($is_partial ? 'md-btn confirm-btn-partial' : 'md-btn md-btn-success'));
?>

<div class="md-modal-overlay">
<div class="md-modal" style="max-width: 520px;">

<!-- ========== HEADER ========== -->
<div class="md-modal-header">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: linear-gradient(135deg, <?php echo $modal_gradient; ?>);">
            <?php echo $modal_icon; ?>
        </div>
        <div class="md-modal-header-info">
            <div class="md-modal-name"><?php echo $modal_subtitle; ?></div>
            <div class="md-modal-ref"><?php echo ucfirst($confirm_what); ?></div>
        </div>
    </div>
    <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </a>
</div>

<!-- ========== BODY ========== -->
<div class="md-modal-body">

    <!-- Messages -->
    <?php include(APPPATH . 'views/partial/show_messages.php'); ?>

    <!-- Confirmation message -->
    <div class="confirm-message">
        <div class="confirm-icon-ring" style="border-color: <?php echo explode(',', $modal_gradient)[0]; ?>;">
            <?php
            // Large icon matching type
            $icon_color = explode(',', $modal_gradient)[0];
            switch ($confirm_what) {
                case 'invoice':
                case 'stocktransaction':
                    echo '<svg width="36" height="36" fill="none" stroke="'.$icon_color.'" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
                    break;
                case 'suspend':
                case 'suspendtransaction':
                case 'suspendreception':
                    echo '<svg width="36" height="36" fill="none" stroke="'.$icon_color.'" stroke-width="2" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
                    break;
                case 'canceltransaction':
                    echo '<svg width="36" height="36" fill="none" stroke="'.$icon_color.'" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                    break;
                case 'partialreceive':
                    echo '<svg width="36" height="36" fill="none" stroke="'.$icon_color.'" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                    break;
                default:
                    echo '<svg width="36" height="36" fill="none" stroke="'.$icon_color.'" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
            }
            ?>
        </div>
        <p class="confirm-text"><?php echo $modal_title; ?></p>
    </div>

    <!-- Comment textarea for suspend -->
    <?php if ($has_comment): ?>
    <?php echo form_open($_SESSION['controller_name'].'/suspend', array('id' => 'confirm_suspend_form')); ?>
    <div class="confirm-comment">
        <label class="md-form-label" for="comment"><?php echo $this->lang->line('common_comments'); ?></label>
        <?php echo form_textarea(array(
            'name'        => 'comment',
            'id'          => 'comment',
            'value'       => $_SESSION['CSI']['SHV']->comment,
            'rows'        => '3',
            'class'       => 'md-form-input confirm-textarea',
            'placeholder' => 'Ajouter un commentaire (optionnel)...',
        )); ?>
    </div>
    <?php endif; ?>

</div>

<!-- ========== FOOTER ========== -->
<div class="md-modal-footer">
    <div class="md-modal-footer-left">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <?php echo $this->lang->line('common_reset'); ?>
        </a>
    </div>
    <div class="md-modal-footer-right">
        <?php if ($has_comment): ?>
            <?php echo form_submit(array(
                'name'  => 'submit',
                'id'    => 'submit_confirm',
                'value' => $this->lang->line('common_confirm'),
                'class' => $btn_class.' confirm-submit-btn',
                'form'  => 'confirm_suspend_form',
            )); ?>
            </form>
        <?php else: ?>
            <a href="<?php echo $confirm_url; ?>" class="<?php echo $btn_class; ?>" id="show_spinner">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <?php echo $this->lang->line('common_confirm'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>

</div><!-- end .md-modal -->
</div><!-- end .md-modal-overlay -->

<style>
/* Confirmation message */
.confirm-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 30px 24px 10px;
}
.confirm-icon-ring {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 3px solid;
    background: var(--bg-card, #f8fafc);
}
.confirm-text {
    font-size: 1.05em;
    font-weight: 600;
    color: var(--text-primary, #1e293b);
    text-align: center;
    margin: 0;
    line-height: 1.5;
}

/* Comment section */
.confirm-comment {
    padding: 0 4px;
    margin-top: 8px;
}
.confirm-comment .md-form-label {
    margin-bottom: 6px;
}
.confirm-textarea {
    width: 100%;
    resize: vertical;
    min-height: 70px;
    font-family: inherit;
    box-sizing: border-box;
}

/* Warning button (orange) */
.confirm-btn-warning {
    background: var(--warning, #f59e0b) !important;
    color: #fff !important;
    border-color: var(--warning, #f59e0b) !important;
    font-weight: 700;
    cursor: pointer;
}
.confirm-btn-warning:hover {
    background: #d97706 !important;
    border-color: #d97706 !important;
}

/* Success button */
.md-btn-success {
    background: var(--success, #22c55e) !important;
    color: #fff !important;
    border-color: var(--success, #22c55e) !important;
    font-weight: 700;
}
.md-btn-success:hover {
    background: #16a34a !important;
    border-color: #16a34a !important;
}

/* Partial button (violet) */
.confirm-btn-partial {
    background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important;
    color: #fff !important;
    border-color: #8b5cf6 !important;
    font-weight: 700;
}
.confirm-btn-partial:hover {
    background: linear-gradient(135deg, #7c3aed, #5b21b6) !important;
    border-color: #7c3aed !important;
}

/* Submit button in footer */
.confirm-submit-btn {
    border: none;
    font-size: 0.875em;
    padding: 8px 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 6px;
    cursor: pointer;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    // Spinner on confirm click
    $("#show_spinner, #submit_confirm").click(function() {
        $('#spinner_on_bar').show();
    });
});
</script>
