<?php
$mvt_year    = $_SESSION['mvt_year'] ?? date('Y');
$mvt_month   = intval($_SESSION['mvt_month'] ?? 0);
$mvt_mode    = $_SESSION['mvt_mode'] ?? 'monthly';
$labels      = $_SESSION['mvt_labels'] ?? [];
$data_sales  = $_SESSION['mvt_sales'] ?? [];
$data_sa     = $_SESSION['mvt_setaside'] ?? [];
$data_bk     = $_SESSION['mvt_bank'] ?? [];

$total_sales = array_sum($data_sales);
$total_sa    = array_sum($data_sa);
$total_bk    = array_sum($data_bk);

$months_list = array(
    0 => 'Toute l\'ann&eacute;e',
    1 => 'Janvier', 2 => 'F&eacute;vrier', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Ao&ucirc;t',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'D&eacute;cembre'
);
?>

<style>
/* --- Mouvements modal --- */
.mvt-filters {
    display: flex; align-items: end; gap: 0.6em; flex-wrap: wrap;
    padding: 0.5em 0.8em; margin-bottom: 0.6em;
    background: var(--bg-card, #f8fafc); border-radius: 8px;
    border: 1px solid var(--border-color, #e2e8f0);
}
.mvt-filter-group { display: flex; flex-direction: column; gap: 0.15em; }
.mvt-filter-group label {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    color: var(--text-secondary, #64748b);
}
.mvt-filter-group select {
    padding: 0.35em 0.5em; font-size: 0.92em;
    border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    background: var(--bg-container, #fff); color: var(--text-primary, #1e293b);
}
.mvt-filter-group select:focus {
    outline: none; border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.mvt-filter-btn {
    padding: 0.35em 0.8em; font-size: 0.85em; font-weight: 600;
    color: #fff; background: var(--primary, #2563eb);
    border: none; border-radius: 6px; cursor: pointer; transition: background 0.15s;
}
.mvt-filter-btn:hover { background: #1d4ed8; }

.mvt-chart-wrap {
    position: relative; width: 100%; height: 280px;
    padding: 0.3em 0;
}
.mvt-summary {
    display: flex; gap: 0.5em; flex-wrap: wrap; margin-top: 0.5em;
}
.mvt-summary-card {
    flex: 1; min-width: 120px;
    padding: 0.5em 0.7em; border-radius: 8px;
    border: 1px solid var(--border-color, #e2e8f0);
    background: var(--bg-card, #f8fafc);
}
.mvt-summary-label {
    font-size: 0.68em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;
    display: flex; align-items: center; gap: 0.3em;
}
.mvt-summary-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.mvt-summary-value {
    font-size: 1.1em; font-weight: 700; font-variant-numeric: tabular-nums;
    color: var(--text-primary, #1e293b); margin-top: 0.15em;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<div class="md-modal-overlay" style="z-index: 2000;">
<div class="md-modal" style="max-width: 720px;">

<!-- Header -->
<div class="md-modal-header" style="padding: 0.5em 1em;">
    <div class="md-modal-header-left">
        <div class="md-modal-avatar" style="background: rgba(139,92,246,0.12); color: #7c3aed;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
        </div>
        <div class="md-modal-header-info">
            <h2 class="md-modal-name" style="font-size: 1.05em;">Mouvements de caisse</h2>
            <span class="md-modal-ref"><?php echo $mvt_year; ?><?php echo $mvt_month > 0 ? ' &mdash; '.$months_list[$mvt_month] : ''; ?> &mdash; <?php echo $mvt_mode == 'daily' ? 'Vue journali&egrave;re' : 'Vue mensuelle'; ?></span>
        </div>
    </div>
    <div class="md-modal-header-actions">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-modal-close" title="Fermer">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </a>
    </div>
</div>

<!-- Body -->
<div class="md-modal-body" style="padding: 0.6em 1em; max-height: 85vh; overflow-y: auto;">

<!-- Filters -->
<?php echo form_open('cashtills/mouvements', array('id' => 'mvt_filter_form')); ?>
<div class="mvt-filters">
    <div class="mvt-filter-group">
        <label>Ann&eacute;e</label>
        <select name="mvt_year">
            <?php for ($y = intval(date('Y')); $y >= intval(date('Y')) - 4; $y--) { ?>
            <option value="<?php echo $y; ?>"<?php echo ($y == $mvt_year) ? ' selected' : ''; ?>><?php echo $y; ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="mvt-filter-group">
        <label>P&eacute;riode</label>
        <select name="mvt_month">
            <?php foreach ($months_list as $k => $v) { ?>
            <option value="<?php echo $k; ?>"<?php echo ($k == $mvt_month) ? ' selected' : ''; ?>><?php echo $v; ?></option>
            <?php } ?>
        </select>
    </div>
    <button type="submit" class="mvt-filter-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: -2px;">
            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        Afficher
    </button>
</div>
<?php echo form_close(); ?>

<!-- Chart -->
<div class="mvt-chart-wrap">
    <canvas id="mvtChart"></canvas>
</div>

<!-- Summary cards -->
<div class="mvt-summary">
    <div class="mvt-summary-card">
        <div class="mvt-summary-label" style="color: #22c55e;">
            <span class="mvt-summary-dot" style="background: #22c55e;"></span>
            Ventes
        </div>
        <div class="mvt-summary-value"><?php echo number_format($total_sales, 2, ',', ' '); ?> &euro;</div>
    </div>
    <div class="mvt-summary-card">
        <div class="mvt-summary-label" style="color: #f59e0b;">
            <span class="mvt-summary-dot" style="background: #f59e0b;"></span>
            Versements
        </div>
        <div class="mvt-summary-value"><?php echo number_format($total_sa, 2, ',', ' '); ?> &euro;</div>
    </div>
    <div class="mvt-summary-card">
        <div class="mvt-summary-label" style="color: #3b82f6;">
            <span class="mvt-summary-dot" style="background: #3b82f6;"></span>
            D&eacute;p&ocirc;ts banque
        </div>
        <div class="mvt-summary-value"><?php echo number_format($total_bk, 2, ',', ' '); ?> &euro;</div>
    </div>
</div>

</div><!-- /md-modal-body -->

<!-- Footer -->
<div class="md-modal-footer" style="padding: 0.5em 1em;">
    <div class="md-modal-footer-left"></div>
    <div class="md-modal-footer-right">
        <a href="<?php echo site_url('common_controller/common_exit/'); ?>" class="md-btn md-btn-secondary">
            Fermer
        </a>
    </div>
</div>

</div><!-- /md-modal -->
</div><!-- /md-modal-overlay -->

<script>
(function() {
    var labels = <?php echo json_encode($labels); ?>;
    var sales  = <?php echo json_encode($data_sales); ?>;
    var sa     = <?php echo json_encode($data_sa); ?>;
    var bk     = <?php echo json_encode($data_bk); ?>;
    var mode   = '<?php echo $mvt_mode; ?>';

    // Detect dark mode
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark' ||
                 (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches &&
                  !document.documentElement.getAttribute('data-theme'));
    var gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
    var textColor = isDark ? '#94a3b8' : '#64748b';

    var ctx = document.getElementById('mvtChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ventes',
                    data: sales,
                    backgroundColor: 'rgba(34,197,94,0.7)',
                    borderColor: '#22c55e',
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 3
                },
                {
                    label: 'Versements',
                    data: sa,
                    backgroundColor: 'rgba(245,158,11,0.7)',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 2
                },
                {
                    label: 'Dépôts banque',
                    data: bk,
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 3,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        boxWidth: 12, boxHeight: 12, padding: 12,
                        font: { size: 11, weight: '600' },
                        color: textColor,
                        usePointStyle: true, pointStyle: 'rectRounded'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#f1f5f9' : '#1e293b',
                    bodyColor: isDark ? '#cbd5e1' : '#475569',
                    borderColor: isDark ? '#475569' : '#e2e8f0',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10,
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ' : ' +
                                ctx.parsed.y.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' \u20ac';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        callback: function(val) {
                            return val.toLocaleString('fr-FR') + ' \u20ac';
                        }
                    }
                }
            }
        }
    });
})();
</script>
