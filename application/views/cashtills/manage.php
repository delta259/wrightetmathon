<?php $this->load->view("partial/header"); ?>

<?php
// State flags
$till_open   = ($_SESSION['cash_till_open'] ?? 0);
$till_closed = ($_SESSION['cash_till_closed'] ?? 0);
$till_final  = ($_SESSION['cash_till_final'] ?? 0);
$is_vapeself = ($this->config->item('distributeur_vapeself') == 'Y');

// Status label
if ($till_final == 1) {
    $status_label = 'Cl&ocirc;tur&eacute;e'; $status_class = 'ct-status-closed';
} elseif ($till_closed == 1) {
    $status_label = 'Ferm&eacute;e (avant versement)'; $status_class = 'ct-status-pending';
} elseif ($till_open == 1) {
    $status_label = 'Ouverte'; $status_class = 'ct-status-open';
} else {
    $status_label = 'Non ouverte'; $status_class = 'ct-status-idle';
}
?>

<style>
/* --- Cashtills page --- */
.ct-page { max-width: 800px; margin: 0 auto; }
.ct-card {
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
}
.ct-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.8em 1.2em;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap; gap: 0.6em;
}
.ct-header-left { display: flex; align-items: center; gap: 0.6em; }
.ct-header-left svg { color: var(--primary, #2563eb); flex-shrink: 0; }
.ct-header-left h2 { font-size: 1.1em; font-weight: 700; margin: 0; color: var(--text-primary, #1e293b); }

/* Status badge */
.ct-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 20px; font-size: 0.78em; font-weight: 600;
}
.ct-status-dot { width: 8px; height: 8px; border-radius: 50%; }
.ct-status-open { background: rgba(34,197,94,0.12); color: #16a34a; }
.ct-status-open .ct-status-dot { background: #22c55e; }
.ct-status-pending { background: rgba(245,158,11,0.12); color: #d97706; }
.ct-status-pending .ct-status-dot { background: #f59e0b; }
.ct-status-closed { background: rgba(239,68,68,0.12); color: #dc2626; }
.ct-status-closed .ct-status-dot { background: #ef4444; }
.ct-status-idle { background: rgba(100,116,139,0.12); color: #64748b; }
.ct-status-idle .ct-status-dot { background: #94a3b8; }

/* Date bar */
.ct-date-bar {
    display: flex; align-items: center; gap: 0.6em; padding: 0.5em 1.2em;
    background: var(--bg-card, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.82em; color: var(--text-secondary, #64748b);
}
.ct-date-bar svg { width: 14px; height: 14px; flex-shrink: 0; }
.ct-date-bar b { color: var(--text-primary, #1e293b); }

/* Action grid */
.ct-actions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.8em;
    padding: 1.2em;
}
.ct-action {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.5em; padding: 1.2em 0.8em;
    background: var(--bg-card, #f8fafc);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    text-decoration: none;
    color: var(--text-primary, #1e293b);
    transition: all 0.15s;
    cursor: pointer;
    min-height: 100px;
}
.ct-action:hover {
    border-color: var(--primary, #2563eb);
    box-shadow: 0 4px 12px rgba(37,99,235,0.12);
    transform: translateY(-2px);
    background: var(--bg-container, #fff);
}
.ct-action-icon {
    width: 44px; height: 44px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.ct-action-icon svg { width: 24px; height: 24px; }
.ct-action-label { font-size: 0.82em; font-weight: 600; text-align: center; line-height: 1.3; }

/* Icon colors */
.ct-icon-open { background: rgba(34,197,94,0.12); color: #16a34a; }
.ct-icon-close { background: rgba(239,68,68,0.12); color: #dc2626; }
.ct-icon-setaside { background: rgba(245,158,11,0.12); color: #d97706; }
.ct-icon-bank { background: rgba(37,99,235,0.12); color: #2563eb; }
.ct-icon-change { background: rgba(139,92,246,0.12); color: #7c3aed; }
.ct-icon-status { background: rgba(100,116,139,0.12); color: #475569; }
.ct-icon-vapeself { background: rgba(6,182,212,0.12); color: #0891b2; }

/* Disabled state */
.ct-action-disabled {
    opacity: 0.35;
    pointer-events: none;
    cursor: default;
}

/* Footer */
.ct-footer {
    padding: 0.6em 1.2em; border-top: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.78em; color: var(--text-secondary, #94a3b8); text-align: center;
}

/* Extra icon colors */
.ct-icon-history { background: rgba(14,165,233,0.12); color: #0ea5e9; }
.ct-icon-movements { background: rgba(139,92,246,0.12); color: #7c3aed; }
</style>

<div class="ct-page">
<div class="ct-card">

<!-- Header -->
<div class="ct-header">
    <div class="ct-header-left">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
            <line x1="2" y1="10" x2="22" y2="10"></line>
        </svg>
        <h2>Caisse</h2>
    </div>
    <span class="ct-status <?php echo $status_class; ?>">
        <span class="ct-status-dot"></span>
        <?php echo $status_label; ?>
    </span>
</div>

<!-- Date -->
<div class="ct-date-bar">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
    </svg>
    <b><?php echo date('d/m/Y'); ?></b>
</div>

<!-- output messages -->
<?php
if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0) {
    include('../wrightetmathon/application/views/partial/show_messages.php');
}
?>

<!-- Action Grid -->
<div class="ct-actions">

    <?php // VapeSelf
    if ($is_vapeself) { ?>
    <a href="<?php echo site_url('sales/synchronisation_vs'); ?>" class="ct-action">
        <div class="ct-action-icon ct-icon-vapeself">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>
        </div>
        <span class="ct-action-label">Distributeur</span>
    </a>
    <?php } ?>

    <?php // Open
    if ($till_open == 0) { ?>
    <a href="<?php echo site_url('cashtills/open'); ?>" class="ct-action">
        <div class="ct-action-icon ct-icon-open">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>
        </div>
        <span class="ct-action-label"><?php echo $this->lang->line('cashtills_open') ?: 'Ouverture'; ?></span>
    </a>
    <?php } ?>

    <?php // Close
    if ($till_closed == 0) { ?>
    <a href="<?php echo site_url('cashtills/close'); ?>" class="ct-action<?php echo ($till_open == 0) ? ' ct-action-disabled' : ''; ?>">
        <div class="ct-action-icon ct-icon-close">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <span class="ct-action-label"><?php echo $this->lang->line('cashtills_close') ?: 'Fermer caisse'; ?></span>
    </a>
    <?php } ?>

    <!-- Set Aside (Versement) -->
    <a href="<?php echo site_url('cashtills/set_aside'); ?>" class="ct-action<?php echo ($till_closed == 0) ? ' ct-action-disabled' : ''; ?>">
        <div class="ct-action-icon ct-icon-setaside">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
        </div>
        <span class="ct-action-label"><?php echo $this->lang->line('cashtills_set_aside') ?: 'Versement'; ?></span>
    </a>

    <!-- Bank Deposit -->
    <a href="<?php echo site_url('cashtills/bank'); ?>" class="ct-action">
        <div class="ct-action-icon ct-icon-bank">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 21h18"></path><path d="M3 10h18"></path>
                <path d="M12 3l9 7H3l9-7z"></path>
                <path d="M5 10v11"></path><path d="M19 10v11"></path>
                <path d="M9 10v11"></path><path d="M15 10v11"></path>
            </svg>
        </div>
        <span class="ct-action-label"><?php echo $this->lang->line('cashtills_bank') ?: 'Banque'; ?></span>
    </a>

    <?php // Change Employee
    if ($till_final == 0) { ?>
    <a href="<?php echo site_url('cashtills/change'); ?>" class="ct-action<?php echo ($till_open == 0) ? ' ct-action-disabled' : ''; ?>">
        <div class="ct-action-icon ct-icon-change">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <span class="ct-action-label">Changement employ&eacute;</span>
    </a>
    <?php } ?>

    <!-- Status -->
    <a href="<?php echo site_url('cashtills/status'); ?>" class="ct-action">
        <div class="ct-action-icon ct-icon-status">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <span class="ct-action-label"><?php echo $this->lang->line('cashtills_status') ?: 'Situation'; ?></span>
    </a>

    <!-- Bank History -->
    <a href="<?php echo site_url('cashtills/bank_history'); ?>" class="ct-action">
        <div class="ct-action-icon ct-icon-history">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <span class="ct-action-label">Historique d&eacute;p&ocirc;ts</span>
    </a>

    <!-- Mouvements -->
    <a href="<?php echo site_url('cashtills/mouvements'); ?>" class="ct-action">
        <div class="ct-action-icon ct-icon-movements">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
        </div>
        <span class="ct-action-label">Mouvements</span>
    </a>

</div><!-- /ct-actions -->

<!-- Footer -->
<div class="ct-footer">
    Caisse du <?php echo date('d/m/Y'); ?>
</div>

</div><!-- /ct-card -->
</div><!-- /ct-page -->

<!-- Modal dialogs -->
<?php
switch ($_SESSION['show_dialog'] ?? 0)
{
    case 1:
        include('../wrightetmathon/application/views/cashtills/form_open.php');
        break;
    case 2:
        include('../wrightetmathon/application/views/cashtills/form_close.php');
        break;
    case 3:
        include('../wrightetmathon/application/views/cashtills/form_set_aside.php');
        break;
    case 4:
        include('../wrightetmathon/application/views/cashtills/form_status.php');
        break;
    case 5:
        include('../wrightetmathon/application/views/cashtills/form_bank.php');
        break;
    case 6:
        include('../wrightetmathon/application/views/cashtills/form_change.php');
        break;
    case 7:
        include('../wrightetmathon/application/views/cashtills/form_bank_history.php');
        break;
    case 8:
        include('../wrightetmathon/application/views/cashtills/form_mouvements.php');
        break;
}
?>

<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
