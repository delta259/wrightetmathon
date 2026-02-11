<?php
/**
 * Sales tab content partial
 * Shows sales statistics and chart for the item
 *
 * Expects: $_SESSION['transaction_info'], $_SESSION['sales_stats']
 */

$item = $_SESSION['transaction_info'];
$stats = isset($_SESSION['sales_stats']) ? $_SESSION['sales_stats'] : new stdClass();

// Default values if stats not set
if (!isset($stats->total_revenue)) $stats->total_revenue = 0;
if (!isset($stats->total_margin)) $stats->total_margin = 0;
if (!isset($stats->margin_percent)) $stats->margin_percent = 0;
if (!isset($stats->total_qty)) $stats->total_qty = 0;
if (!isset($stats->total_sales)) $stats->total_sales = 0;
if (!isset($stats->unique_customers)) $stats->unique_customers = 0;
if (!isset($stats->avg_qty_per_sale)) $stats->avg_qty_per_sale = 0;
if (!isset($stats->start_date)) $stats->start_date = date('Y-m-d', strtotime('-3 months'));
if (!isset($stats->end_date)) $stats->end_date = date('Y-m-d');
if (!isset($stats->months)) $stats->months = 3;
if (!isset($stats->daily_data)) $stats->daily_data = array();
if (!isset($stats->monthly_data)) $stats->monthly_data = array();

// Number formatting
$pieces = explode("/", $this->config->item('numberformat'));
$decimals = $pieces[0];
$dec_point = $pieces[1];
$thousands_sep = $pieces[2];

// Prepare chart data with full dates for time scale
$chart_data_revenue = array();
$chart_data_qty = array();

if (is_array($stats->daily_data)) {
    foreach ($stats->daily_data as $day) {
        $date_str = $day['sale_date']; // Format: Y-m-d
        $chart_data_revenue[] = array('x' => $date_str, 'y' => floatval($day['daily_revenue']));
        $chart_data_qty[] = array('x' => $date_str, 'y' => floatval($day['daily_qty']));
    }
}
$has_chart_data = !empty($chart_data_revenue);
?>

<style>
/* Sales Tab Styles - Compact Dark Mode Compatible */
.sales-stats-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
    margin-bottom: 12px;
}
.sales-stat-card {
    background: var(--bg-card, #f8f9fa);
    border-radius: 8px;
    padding: 10px 8px;
    text-align: center;
    border: 1px solid var(--border-color, #dee2e6);
}
.sales-stat-value {
    font-size: 18px;
    font-weight: bold;
    color: var(--primary, #0A6184);
    margin-bottom: 2px;
    line-height: 1.2;
}
.sales-stat-label {
    font-size: 9px;
    color: var(--text-secondary, #6c757d);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.sales-stat-card.highlight {
    background: linear-gradient(135deg, var(--primary, #0A6184) 0%, #106587 100%);
}
.sales-stat-card.highlight .sales-stat-value,
.sales-stat-card.highlight .sales-stat-label {
    color: white;
}
.sales-stat-card.success {
    background: linear-gradient(135deg, var(--success, #28a745) 0%, #20c997 100%);
}
.sales-stat-card.success .sales-stat-value,
.sales-stat-card.success .sales-stat-label {
    color: white;
}
.sales-content-row {
    display: flex;
    gap: 12px;
    margin-bottom: 10px;
}
.sales-chart-container {
    background: var(--bg-card, white);
    border-radius: 8px;
    padding: 12px;
    border: 1px solid var(--border-color, #dee2e6);
    flex: 1;
}
.sales-chart-container.chart-area {
    flex: 2;
}
.sales-chart-container.table-area {
    flex: 1;
    min-width: 280px;
}
.sales-chart-title {
    font-size: 12px;
    font-weight: bold;
    color: var(--text-primary, #333);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.sales-chart-title svg {
    width: 14px;
    height: 14px;
    stroke: var(--primary, #0A6184);
}
.sales-monthly-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
.sales-monthly-table th,
.sales-monthly-table td {
    padding: 5px 4px;
    text-align: right;
    border-bottom: 1px solid var(--border-color, #dee2e6);
    color: var(--text-primary, #333);
}
.sales-monthly-table th {
    background: var(--bg-body, #f8f9fa);
    font-weight: bold;
    text-align: center;
    font-size: 10px;
}
.sales-monthly-table td:first-child {
    text-align: left;
    font-weight: 500;
}
.sales-monthly-table tbody tr:hover {
    background: var(--bg-hover, rgba(0,0,0,0.03));
}
.sales-period-info {
    font-size: 11px;
    color: var(--text-secondary, #6c757d);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.sales-period-info svg {
    width: 12px;
    height: 12px;
}
.sales-no-data {
    text-align: center;
    padding: 20px;
    color: var(--text-secondary, #6c757d);
    font-size: 12px;
}
.sales-no-data svg {
    width: 32px;
    height: 32px;
    margin-bottom: 6px;
    opacity: 0.5;
}

/* Dark mode adjustments */
[data-theme="dark"] .sales-stat-card:not(.highlight):not(.success) {
    background: var(--bg-card, #334155);
}
[data-theme="dark"] .sales-chart-container {
    background: var(--bg-card, #334155);
}
[data-theme="dark"] .sales-monthly-table th {
    background: var(--bg-body, #1e293b);
}

/* Responsive: stack on small screens */
@media (max-width: 800px) {
    .sales-stats-grid { grid-template-columns: repeat(3, 1fr); }
    .sales-content-row { flex-direction: column; }
}
</style>

<div class="sales-period-info">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
    </svg>
    Période : <?php echo date('d/m/Y', strtotime($stats->start_date)); ?> - <?php echo date('d/m/Y', strtotime($stats->end_date)); ?> (<?php echo $stats->months; ?> mois)
</div>

<!-- Summary Stats Cards -->
<div class="sales-stats-grid">
    <div class="sales-stat-card highlight">
        <div class="sales-stat-value"><?php echo number_format($stats->total_revenue, $decimals, $dec_point, $thousands_sep); ?> €</div>
        <div class="sales-stat-label">Chiffre d'affaires</div>
    </div>
    <div class="sales-stat-card success">
        <div class="sales-stat-value"><?php echo number_format($stats->total_margin, $decimals, $dec_point, $thousands_sep); ?> €</div>
        <div class="sales-stat-label">Marge (<?php echo $stats->margin_percent; ?>%)</div>
    </div>
    <div class="sales-stat-card">
        <div class="sales-stat-value"><?php echo number_format($stats->total_qty, $decimals, $dec_point, $thousands_sep); ?></div>
        <div class="sales-stat-label">Quantité vendue</div>
    </div>
    <div class="sales-stat-card">
        <div class="sales-stat-value"><?php echo $stats->total_sales; ?></div>
        <div class="sales-stat-label">Nombre de ventes</div>
    </div>
    <div class="sales-stat-card">
        <div class="sales-stat-value"><?php echo $stats->unique_customers; ?></div>
        <div class="sales-stat-label">Clients uniques</div>
    </div>
    <div class="sales-stat-card">
        <div class="sales-stat-value"><?php echo $stats->avg_qty_per_sale; ?></div>
        <div class="sales-stat-label">Qté moy./vente</div>
    </div>
</div>

<!-- Chart and Table side by side -->
<div class="sales-content-row">
    <!-- Sales Chart -->
    <div class="sales-chart-container chart-area">
        <div class="sales-chart-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
            Évolution (<?php echo $stats->months; ?> mois)
        </div>
        <div id="salesChartWrapper" style="height: 180px;">
            <?php if (!$has_chart_data): ?>
            <div class="sales-no-data">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M3 3v18h18"></path>
                    <path d="M18 9l-5 5-4-4-6 6"></path>
                </svg>
                <div>Aucune vente</div>
            </div>
            <?php else: ?>
            <canvas id="salesChart"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Monthly Breakdown Table -->
    <?php if (!empty($stats->monthly_data)): ?>
    <div class="sales-chart-container table-area">
        <div class="sales-chart-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="3" y1="9" x2="21" y2="9"></line>
                <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
            Par mois
        </div>
        <table class="sales-monthly-table">
            <thead>
                <tr>
                    <th>Mois</th>
                    <th>Qté</th>
                    <th>CA</th>
                    <th>Marge</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats->monthly_data as $month):
                    $month_margin = floatval($month['monthly_revenue']) - floatval($month['monthly_cost']);
                ?>
                <tr>
                    <td><?php echo date('M y', strtotime($month['sale_month'] . '-01')); ?></td>
                    <td><?php echo number_format($month['monthly_qty'], 0, $dec_point, $thousands_sep); ?></td>
                    <td><?php echo number_format($month['monthly_revenue'], 0, $dec_point, $thousands_sep); ?>€</td>
                    <td><?php echo number_format($month_margin, 0, $dec_point, $thousands_sep); ?>€</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($has_chart_data): ?>
<script>
(function() {
    var chartJsLoaded = false;
    var adapterLoaded = false;

    function loadScript(src, callback) {
        var script = document.createElement('script');
        script.src = src;
        script.onload = callback;
        document.head.appendChild(script);
    }

    function initChart() {
        var ctx = document.getElementById('salesChart');
        if (!ctx) return;

        var revenueData = <?php echo json_encode($chart_data_revenue); ?>;
        var qtyData = <?php echo json_encode($chart_data_qty); ?>;
        var startDate = '<?php echo $stats->start_date; ?>';
        var endDate = '<?php echo $stats->end_date; ?>';

        // Detect dark mode
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark' ||
                     document.body.classList.contains('dark-mode');

        var textColor = isDark ? '#e2e8f0' : '#333';
        var gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

        new Chart(ctx, {
            type: 'bar',
            data: {
                datasets: [
                    {
                        label: 'CA (€)',
                        data: revenueData,
                        backgroundColor: isDark ? 'rgba(59, 130, 246, 0.7)' : 'rgba(10, 97, 132, 0.7)',
                        borderColor: isDark ? 'rgba(59, 130, 246, 1)' : 'rgba(10, 97, 132, 1)',
                        borderWidth: 1,
                        yAxisID: 'y',
                        order: 2,
                        barThickness: 8,
                        maxBarThickness: 12
                    },
                    {
                        label: 'Quantité',
                        data: qtyData,
                        type: 'line',
                        borderColor: isDark ? 'rgba(52, 211, 153, 1)' : 'rgba(40, 167, 69, 1)',
                        backgroundColor: isDark ? 'rgba(52, 211, 153, 0.1)' : 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y1',
                        order: 1,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 5, bottom: 5 } },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: textColor,
                            usePointStyle: true,
                            padding: 8,
                            font: { size: 10 }
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDark ? '#475569' : '#dee2e6',
                        borderWidth: 1,
                        callbacks: {
                            title: function(items) {
                                if (items.length > 0) {
                                    var d = new Date(items[0].parsed.x);
                                    return d.toLocaleDateString('fr-FR');
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            displayFormats: {
                                day: 'dd/MM'
                            },
                            tooltipFormat: 'dd/MM/yyyy'
                        },
                        min: startDate,
                        max: endDate,
                        ticks: {
                            color: textColor,
                            font: { size: 9 },
                            maxRotation: 45,
                            autoSkip: true,
                            maxTicksLimit: 15
                        },
                        grid: { color: gridColor }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'CA €',
                            color: textColor,
                            font: { size: 9 }
                        },
                        ticks: { color: textColor, font: { size: 9 } },
                        grid: { color: gridColor }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Qté',
                            color: textColor,
                            font: { size: 9 }
                        },
                        ticks: { color: textColor, font: { size: 9 } },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // Load scripts in sequence: Chart.js first, then adapter, then init
    function startLoading() {
        if (typeof Chart !== 'undefined' && Chart._adapters) {
            // Already loaded (maybe from another tab)
            initChart();
            return;
        }

        if (typeof Chart !== 'undefined') {
            // Chart loaded but no adapter yet
            loadScript('https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js', initChart);
        } else {
            // Load Chart.js first
            loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', function() {
                // Then load adapter
                loadScript('https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js', initChart);
            });
        }
    }

    // Start
    if (document.readyState === 'complete') {
        startLoading();
    } else {
        window.addEventListener('load', startLoading);
    }
})();
</script>
<?php endif; ?>
