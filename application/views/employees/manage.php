<?php $this->load->view("partial/header"); ?>

<?php
// Collect data rows
$emp_rows = array();
if (isset($manage_table_data) && is_object($manage_table_data) && $manage_table_data->num_rows() > 0) {
    foreach ($manage_table_data->result() as $row) {
        $emp_rows[] = $row;
    }
}
?>

<style>
/* --- Employees page --- */
.emp-page { max-width: 1100px; margin: 0 auto; }
.emp-card {
    background: var(--bg-container, #fff);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
}
.emp-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.8em 1.2em;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap; gap: 0.6em;
}
.emp-header-left { display: flex; align-items: center; gap: 0.6em; }
.emp-header-left svg { color: var(--primary, #2563eb); flex-shrink: 0; }
.emp-header-left h2 { font-size: 1.1em; font-weight: 700; margin: 0; color: var(--text-primary, #1e293b); }
.emp-header-right { display: flex; align-items: center; gap: 0.5em; flex-wrap: wrap; }

/* Search */
.emp-search {
    height: 30px; padding: 0 10px; border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px; font-size: 0.85em; background: var(--bg-input, #fff);
    color: var(--text-primary, #1e293b); width: 200px;
}
.emp-search:focus { outline: none; border-color: var(--primary, #2563eb); box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }

/* Summary bar */
.emp-summary {
    display: flex; gap: 1em; padding: 0.5em 1.2em;
    background: var(--bg-card, #f8fafc); border-bottom: 1px solid var(--border-color, #e2e8f0);
    flex-wrap: wrap;
}
.emp-chip { font-size: 0.8em; color: var(--text-secondary, #64748b); }
.emp-chip b { color: var(--text-primary, #1e293b); margin-left: 4px; }

/* Table */
.emp-table { width: 100%; border-collapse: collapse; }
.emp-table th {
    padding: 8px 12px; font-size: 0.78em; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--text-secondary, #64748b);
    border-bottom: 2px solid var(--border-color, #e2e8f0);
    text-align: left; cursor: pointer; user-select: none; white-space: nowrap;
}
.emp-table th:hover { color: var(--primary, #2563eb); }
.emp-table th svg { vertical-align: middle; margin-left: 3px; opacity: 0.3; }
.emp-table th.sort-asc svg, .emp-table th.sort-desc svg { opacity: 1; color: var(--primary, #2563eb); }
.emp-table td {
    padding: 8px 12px; font-size: 0.88em;
    border-bottom: 1px solid var(--border-color, #f0f0f0);
    color: var(--text-primary, #1e293b);
}
.emp-table tbody tr { cursor: pointer; transition: background 0.12s; }
.emp-table tbody tr:hover { background: rgba(37,99,235,0.06); }
.emp-table .col-id { width: 50px; text-align: center; color: var(--text-secondary, #94a3b8); font-size: 0.8em; }
.emp-table .col-name { font-weight: 600; }
.emp-table .col-num { text-align: right; font-variant-numeric: tabular-nums; }
.emp-table .col-email { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.emp-table .col-email a { color: var(--primary, #2563eb); text-decoration: none; }
.emp-table .col-email a:hover { text-decoration: underline; }

/* Avatar */
.emp-avatar-sm {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--primary, #2563eb); color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.7em; font-weight: 700; margin-right: 6px; vertical-align: middle;
    flex-shrink: 0;
}

/* Footer */
.emp-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.6em 1.2em; border-top: 1px solid var(--border-color, #e2e8f0);
    font-size: 0.82em; color: var(--text-secondary, #64748b);
}
.emp-empty { padding: 2em; text-align: center; color: var(--text-secondary, #94a3b8); font-size: 0.9em; }

/* Buttons in header */
.emp-header .btn-group-modern { display: flex; gap: 0.4em; }
</style>

<div class="emp-page">
<div class="emp-card">

<!-- Header -->
<div class="emp-header">
    <div class="emp-header-left">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <h2><?php echo $this->lang->line('employees_manage') ?: 'Employ&eacute;s'; ?></h2>
    </div>
    <div class="emp-header-right">
        <input id="search" name="search" type="text" class="emp-search" placeholder="Rechercher un employ&eacute;..." autocomplete="off" value="">
        <span class="btn-group-modern">
        <?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?>
        </span>
    </div>
</div>

<!-- Summary -->
<?php if (count($emp_rows) > 0) { ?>
<div class="emp-summary">
    <span class="emp-chip"><?php echo count($emp_rows); ?> employ&eacute;<?php echo count($emp_rows) > 1 ? 's' : ''; ?></span>
</div>
<?php } ?>

<!-- Table -->
<table class="emp-table" id="emp-table">
    <thead>
        <tr>
            <th class="col-id" data-col="0">ID
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th data-col="1"><?php echo $this->lang->line('common_last_name') ?: 'Nom'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th data-col="2"><?php echo $this->lang->line('common_first_name') ?: 'Pr&eacute;nom'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th data-col="3"><?php echo $this->lang->line('employees_username') ?: 'Identifiant'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th data-col="4"><?php echo $this->lang->line('common_email') ?: 'Email'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
            <th data-col="5"><?php echo $this->lang->line('common_phone_number') ?: 'T&eacute;l&eacute;phone'; ?>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
            </th>
        </tr>
    </thead>
    <tbody id="emp-tbody">
    <?php
    if (count($emp_rows) > 0) {
        foreach ($emp_rows as $e) {
            $initials = strtoupper(mb_substr($e->first_name ?? '', 0, 1) . mb_substr($e->last_name ?? '', 0, 1));
            ?>
            <tr data-href="<?php echo site_url('employees/view/'.$e->person_id); ?>">
                <td class="col-id"><?php echo $e->person_id; ?></td>
                <td class="col-name">
                    <span class="emp-avatar-sm"><?php echo $initials; ?></span>
                    <?php echo strtoupper($e->last_name); ?>
                </td>
                <td><?php echo ucfirst(strtolower($e->first_name)); ?></td>
                <td><?php echo htmlspecialchars($e->username ?? ''); ?></td>
                <td class="col-email"><?php if (!empty($e->email)) { ?><a href="mailto:<?php echo $e->email; ?>"><?php echo $e->email; ?></a><?php } ?></td>
                <td><?php echo htmlspecialchars($e->phone_number ?? ''); ?></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr><td colspan="6" class="emp-empty">Aucun employ&eacute; &agrave; afficher</td></tr>
        <?php
    }
    ?>
    </tbody>
</table>

<!-- Footer -->
<div class="emp-footer">
    <span><?php echo count($emp_rows); ?> enregistrement<?php echo count($emp_rows) > 1 ? 's' : ''; ?></span>
</div>

</div><!-- /emp-card -->
</div><!-- /emp-page -->

<!-- output messages if not modal -->
<?php
if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0) {
    include('../wrightetmathon/application/views/partial/show_messages.php');
}
?>

<!-- Modal dialog for create/edit -->
<?php
switch ($_SESSION['show_dialog'] ?? 0)
{
    case 1:
        include('../wrightetmathon/application/views/employees/form.php');
    break;
    case 2:
        include('../wrightetmathon/application/views/employees/form_permissions.php');
    break;
}
?>

<script>
$(document).ready(function() {
    // Row click navigation
    $('#emp-tbody').on('click', 'tr[data-href]', function() {
        window.location.href = $(this).data('href');
    });

    // Live search filter
    $('#search').on('input', function() {
        var val = $.trim($(this).val()).toLowerCase();
        $('#emp-tbody tr[data-href]').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(val) > -1);
        });
        // Update count
        var visible = $('#emp-tbody tr[data-href]:visible').length;
        $('.emp-footer span:first').text(visible + ' enregistrement' + (visible > 1 ? 's' : ''));
    });

    // Sortable columns
    var sortCol = -1, sortDir = 0;
    $('#emp-table thead th[data-col]').on('click', function() {
        var col = parseInt($(this).data('col'));
        if (sortCol === col) { sortDir = sortDir === 1 ? -1 : (sortDir === -1 ? 0 : 1); }
        else { sortCol = col; sortDir = 1; }

        $('#emp-table thead th').removeClass('sort-asc sort-desc');
        if (sortDir === 1) $(this).addClass('sort-asc');
        else if (sortDir === -1) $(this).addClass('sort-desc');

        var $tbody = $('#emp-tbody');
        var $rows = $tbody.find('tr[data-href]');
        if (sortDir === 0) return;

        $rows.sort(function(a, b) {
            var va = $(a).find('td').eq(col).text().trim().toLowerCase();
            var vb = $(b).find('td').eq(col).text().trim().toLowerCase();
            // Try numeric sort first
            var na = parseFloat(va.replace(/[^\d,-]/g,'').replace(',','.'));
            var nb = parseFloat(vb.replace(/[^\d,-]/g,'').replace(',','.'));
            if (!isNaN(na) && !isNaN(nb)) return sortDir * (na - nb);
            // Fallback to string sort
            return sortDir * va.localeCompare(vb, 'fr');
        });
        $tbody.append($rows);
    });
});
</script>

<?php $this->load->view("partial/pre_footer"); $this->load->view("partial/footer"); ?>
