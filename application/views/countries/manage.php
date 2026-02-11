<?php $this->load->view("partial/header"); ?>

<script type="text/javascript">
$(document).ready(function() {
    // Autocomplete search
    $("#search").autocomplete(
        '<?php echo site_url($_SESSION['controller_name']."/suggest"); ?>',
        {
            minChars: 2,
            max: 25,
            selectOnly: true,
            delay: 1,
            formatItem: function(row) {
                return row[0];
            }
        }
    );

    $("#search").result(function(event, data, formatted) {
        $("#search_form").submit();
    });

    // Enable row selection
    enable_row_selection();

    // Client-side table sorting
    if ($('.tablesorter tbody tr').length > 1) {
        $("#sortable_table").tablesorter({
            sortList: [[3, 0]],
            headers: { 0: { sorter: false } }
        });
    }

    // Focus search field
    document.getElementById("search").focus();
});
</script>

<?php
// Reset line number
$_SESSION['line_number'] = 0;
?>

<!-- Page Header - YesAppro Style -->
<div class="page-header">
    <h1 class="page-title">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/>
            <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <?php echo $this->lang->line('modules_' . $_SESSION['controller_name']); ?>
    </h1>
    <div class="page-actions">
        <?php
        $_SESSION['origin'] = "AS";
        include('../wrightetmathon/application/views/partial/show_buttons.php');
        ?>

        <span class="badge badge-info">
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
            </svg>
            <span><?php echo $count; ?> <?php echo $this->lang->line('modules_' . $_SESSION['controller_name']); ?></span>
        </span>
    </div>
</div>

<!-- Messages -->
<?php if (!isset($_SESSION['show_dialog']) || $_SESSION['show_dialog'] == 0): ?>
    <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>
<?php endif; ?>

<!-- Filters Bar -->
<div class="filters-bar">
    <?php echo form_open($_SESSION['controller_name'].'/search', array('id' => 'search_form', 'class' => 'filters-form')); ?>
        <div class="filter-group">
            <div class="search-input-wrapper">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" id="search" name="search" class="form-control search-field"
                       placeholder="Nom, description..."
                       value="<?php echo $_SESSION['filtre_recherche'] ?? '' ?>" tabindex="5">
            </div>
        </div>
    </form>
</div>

<!-- Table Container -->
<div class="table-container">
    <div class="table-wrapper">
        <table class="data-table tablesorter" id="sortable_table">
            <colgroup>
                <col style="width: 32px;"><!-- Edit -->
                <col style="width: 80px;"><!-- ID -->
                <col><!-- Nom (auto) -->
                <col><!-- Description (auto) -->
                <col style="width: 100px;"><!-- Ordre -->
            </colgroup>
            <thead>
                <tr>
                    <th class="col-action"></th>
                    <th class="col-number"><?php echo $this->lang->line('countries_country_id'); ?></th>
                    <th class="col-name"><?php echo $this->lang->line('countries_country_name'); ?></th>
                    <th class="col-name"><?php echo $this->lang->line('countries_country_description'); ?></th>
                    <th class="col-number"><?php echo $this->lang->line('countries_country_display_order'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($countries->result() as $country):
                    $_SESSION['line_number'] += 1;
                    $this->Common_routines->set_line_colour();
                ?>
                <tr>
                    <!-- Edit -->
                    <td class="cell-action">
                        <?php echo anchor($_SESSION['controller_name'].'/view/'.$country->country_id,
                            '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
                            'class="btn-icon" title="'.$this->lang->line('common_edit').'"'); ?>
                    </td>
                    <!-- ID -->
                    <td class="cell-id">
                        <span class="badge-ref"><?php echo $country->country_id; ?></span>
                    </td>
                    <!-- Nom -->
                    <td class="cell-name"><?php echo htmlspecialchars($country->country_name); ?></td>
                    <!-- Description -->
                    <td class="cell-name"><?php echo htmlspecialchars($country->country_description); ?></td>
                    <!-- Ordre -->
                    <td class="cell-number"><?php echo $country->country_display_order; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Table Footer -->
    <div class="table-footer">
        <div class="table-info">
            <span class="item-count"><?php echo $_SESSION['line_number']; ?> <?php echo $this->lang->line('modules_' . $_SESSION['controller_name']); ?></span>
        </div>
        <?php if (isset($links)): ?>
            <div class="pagination-wrapper">
                <?php echo $links; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Modal dialog for edit/create
if (($_SESSION['show_dialog'] ?? 0) == 1)
{
    include('../wrightetmathon/application/views/'.$_SESSION['controller_name'].'/form.php');
}
?>
