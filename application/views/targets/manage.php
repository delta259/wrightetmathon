<?php $this->load->view("partial/header"); ?>

<?php
// Month names for display
$month_names = array(1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
    7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre');

// Collect data rows
$targets_rows = array();
if (isset($manage_table_data) && is_object($manage_table_data) && $manage_table_data->num_rows() > 0) {
    foreach ($manage_table_data->result() as $row) {
        $targets_rows[] = $row;
    }
}

// Compute annual totals
$total_open_days = 0;
$total_turnover  = 0;
foreach ($targets_rows as $r) {
    $total_open_days += $r->target_shop_open_days;
    $total_turnover  += $r->target_shop_turnover;
}
?>

<style>
/* --- Targets page --- */
.tgt-page { max-width: 900px; margin: 0 auto; }
.tgt-card {
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
}
.tgt-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.8em 1.2em;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap; gap: 0.6em;
}
.tgt-header-left { display: flex; align-items: center; gap: 0.6em; }
.tgt-header-left svg { color: var(--primary, #2563eb); flex-shrink: 0; }
.tgt-header-left h2 { font-size: 1.1em; font-weight: 700; margin: 0; color: var(--text-primary, #1e293b); }
.tgt-header-right { display: flex; align-items: center; gap: 0.5em; flex-wrap: wrap; }

/* Year selector */
.tgt-year-nav { display: flex; align-items: center; gap: 0.3em; }
.tgt-year-btn {
    background: none; border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px;
    width: 30px; height: 30px; cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: var(--text-secondary, #64748b); transition: all 0.15s;
}
.tgt-year-btn:hover { background: var(--primary, #2563eb); color: #fff; border-color: var(--primary, #2563eb); }
.tgt-year-current {
    font-weight: 700; font-size: 1.1em; min-width: 60px; text-align: center;
    color: var(--text-primary, #1e293b);
}

/* Search */
.tgt-search {
    height: 30px; padding: 0 10px; border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px; font-size: 0.85em; background: var(--bg-input, #fff);
    color: var(--text-primary, #1e293b); width: 140px;
}
.tgt-search:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }

/* Summary bar */
.tgt-summary {
    display: flex; gap: 1em; padding: 0.5em 1.2em;
    background: var(--bg-card, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap;
}
.tgt-chip { font-size: 0.8em; color: var(--text-secondary, #64748b); }
.tgt-chip b { color: var(--text-primary, #1e293b); margin-left: 4px; }

/* Table */
.tgt-table { width: 100%; border-collapse: collapse; }
.tgt-table th {
    padding: 8px 12px; font-size: 0.78em; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-secondary, #64748b);
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    text-align: left; cursor: pointer; user-select: none; white-space: nowrap;
}
.tgt-table th:hover { color: var(--primary, #2563eb); }
.tgt-table th svg { vertical-align: middle; margin-left: 3px; opacity: 0.3; }
.tgt-table th.sort-asc svg, .tgt-table th.sort-desc svg { opacity: 1; color: var(--primary, #2563eb); }
.tgt-table td {
    padding: 8px 12px; font-size: 0.88em;
    border-bottom: 1px solid var(--border-color, #f0f0f0);
    color: var(--text-primary, #1e293b);
}
.tgt-table tbody tr { cursor: pointer; transition: background 0.12s; }
.tgt-table tbody tr:hover { background: rgba(37,99,235,0.06); }
.tgt-table .col-id { width: 50px; text-align: center; color: var(--text-secondary, #94a3b8); font-size: 0.8em; }
.tgt-table .col-month { font-weight: 600; }
.tgt-table .col-num { text-align: right; font-variant-numeric: tabular-nums; }
.tgt-table .col-turnover { font-weight: 600; }
.tgt-table .col-daily { color: var(--text-secondary, #64748b); font-size: 0.82em; }

/* Footer / Pagination */
.tgt-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.6em 1.2em; border-top: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.82em; color: var(--text-secondary, #64748b);
}
.tgt-footer .pagination { margin: 0; }
.tgt-empty { padding: 2em; text-align: center; color: var(--text-secondary, #94a3b8); font-size: 0.9em; }

/* Buttons in header */
.tgt-header .btn-group-modern { display: flex; gap: 0.4em; }
</style>

<div class="tgt-page">
<div class="tgt-card">

<!-- Header -->
<div class="tgt-header">
    <div class="tgt-header-left">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
            <polyline points="13 2 13 9 20 9"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        <h2>Objectifs de vente</h2>
        <div class="tgt-year-nav">
            <a href="<?php echo site_url('targets/index/'.($display_year - 1)); ?>" class="tgt-year-btn" title="Ann&eacute;e pr&eacute;c&eacute;dente">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <span class="tgt-year-current"><?php echo $display_year; ?></span>
            <a href="<?php echo site_url('targets/index/'.($display_year + 1)); ?>" class="tgt-year-btn" title="Ann&eacute;e suivante">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
    </div>
    <div class="tgt-header-right">
        <input id="search" name="search" type="text" class="tgt-search" placeholder="Ann&eacute;e..." autocomplete="off" value="<?php echo $display_year; ?>">
        <span class="btn-group-modern">
        <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>
        </span>
    </div>
</div>

<!-- Summary -->
<?php if (count($targets_rows) > 0) { ?>
<div class="tgt-summary">
    <span class="tgt-chip"><?php echo count($targets_rows); ?> mois d&eacute;fini<?php echo count($targets_rows) > 1 ? 's' : ''; ?></span>
    <span class="tgt-chip">Jours ouverts : <b><?php echo $total_open_days; ?></b></span>
    <span class="tgt-chip">CA annuel : <b><?php echo number_format($total_turnover, 0, ',', ' '); ?> &euro;</b></span>
    <?php if ($total_open_days > 0) { ?>
    <span class="tgt-chip">Moy. jour : <b><?php echo number_format($total_turnover / $total_open_days, 0, ',', ' '); ?> &euro;</b></span>
    <?php } ?>
</div>
<?php } ?>

<!-- Table -->
<table class="tgt-table" id="tgt-table">
    <thead>
        <tr>
            <th class="col-id" data-col="0">ID</th>
            <th data-col="1">Mois
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th class="col-num" data-col="2"><?php echo $this->lang->line('config_averagenumberopendays') ?: 'Jours ouverts'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th class="col-num" data-col="3"><?php echo $this->lang->line('config_monthlysalestarget') ?: 'Objectif CA'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th class="col-num col-daily" data-col="4">CA / jour
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
        </tr>
    </thead>
    <tbody id="tgt-tbody">
    <?php
    if (count($targets_rows) > 0) {
        foreach ($targets_rows as $t) {
            $m = intval($t->target_month);
            $m_name = $month_names[$m] ?? $m;
            $daily = ($t->target_shop_open_days > 0) ? round($t->target_shop_turnover / $t->target_shop_open_days) : 0;
            ?>
            <tr data-href="<?php echo site_url('targets/view/'.$t->target_id); ?>">
                <td class="col-id"><?php echo $t->target_id; ?></td>
                <td class="col-month"><?php echo $m_name; ?> <span style="opacity:0.5; font-weight:400; font-size:0.85em;">(<?php echo str_pad($m,2,'0',STR_PAD_LEFT); ?>)</span></td>
                <td class="col-num"><?php echo $t->target_shop_open_days; ?></td>
                <td class="col-num col-turnover"><?php echo number_format($t->target_shop_turnover, 0, ',', ' '); ?> &euro;</td>
                <td class="col-num col-daily"><?php echo number_format($daily, 0, ',', ' '); ?> &euro;</td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr><td colspan="5" class="tgt-empty">Aucun objectif d&eacute;fini pour cette ann&eacute;e</td></tr>
        <?php
    }
    ?>
    </tbody>
</table>

<!-- Footer -->
<div class="tgt-footer">
    <span><?php echo count($targets_rows); ?> enregistrement<?php echo count($targets_rows) > 1 ? 's' : ''; ?></span>
</div>

</div><!-- /tgt-card -->
</div><!-- /tgt-page -->

<!-- Messages -->
<?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

<script>
$(document).ready(function() {
    // Row click navigation
    $('#tgt-tbody').on('click', 'tr[data-href]', function() {
        window.location.href = $(this).data('href');
    });

    // Search: submit on Enter
    $('#search').on('keydown', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            var val = $.trim($(this).val());
            if (val && /^\d{4}$/.test(val)) {
                window.location.href = '<?php echo site_url("targets/index/"); ?>' + val;
            }
        }
    });

    // Sortable columns
    var sortCol = -1, sortDir = 0;
    $('#tgt-table thead th[data-col]').on('click', function() {
        var col = parseInt($(this).data('col'));
        if (sortCol === col) { sortDir = sortDir === 1 ? -1 : (sortDir === -1 ? 0 : 1); }
        else { sortCol = col; sortDir = 1; }

        $('#tgt-table thead th').removeClass('sort-asc sort-desc');
        if (sortDir === 1) $(this).addClass('sort-asc');
        else if (sortDir === -1) $(this).addClass('sort-desc');

        var $tbody = $('#tgt-tbody');
        var $rows = $tbody.find('tr[data-href]');
        if (sortDir === 0) return;

        $rows.sort(function(a, b) {
            var va = $(a).find('td').eq(col).text().replace(/[^\d,-]/g,'').replace(',','.');
            var vb = $(b).find('td').eq(col).text().replace(/[^\d,-]/g,'').replace(',','.');
            var na = parseFloat(va) || 0, nb = parseFloat(vb) || 0;
            return sortDir * (na - nb);
        });
        $tbody.append($rows);
    });
});
</script>

<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
