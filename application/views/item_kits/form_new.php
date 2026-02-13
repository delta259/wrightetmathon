<?php $this->load->view("partial/header_popup"); ?>

<div open class="fenetre modale modal-lg">
    <!-- Header -->
    <div class="fenetre-header">
        <span class="fenetre-title">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <?php echo ($_SESSION['new'] ?? 0) == 1 ? $this->lang->line('item_kits_new') : $this->lang->line('common_edit') . ' - ' . htmlspecialchars($_SESSION['transaction_info']->name ?? ''); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content">
        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <?php echo form_open('item_kits/save_item_kit/'.($_SESSION['transaction_info']->item_kit_id ?? ''), array('id'=>'item_kit_form')); ?>

        <fieldset class="fieldset">
            <legend><?php echo $this->lang->line('item_kits_info'); ?></legend>

            <div class="form-grid">
                <!-- Row: Name & Description -->
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label required"><?php echo $this->lang->line('item_kits_name'); ?></label>
                        <?php echo form_input(array(
                            'name' => 'name',
                            'id' => 'name',
                            'class' => 'form-control',
                            'placeholder' => $this->lang->line('item_kits_name'),
                            'value' => $_SESSION['transaction_info']->name ?? ''
                        )); ?>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label required"><?php echo $this->lang->line('item_kits_description'); ?></label>
                        <?php echo form_input(array(
                            'name' => 'description',
                            'id' => 'description',
                            'class' => 'form-control',
                            'placeholder' => $this->lang->line('item_kits_description'),
                            'value' => $_SESSION['transaction_info']->description ?? ''
                        )); ?>
                    </div>
                </div>

                <!-- Row: Price, Category, Barcode -->
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label required"><?php echo $this->lang->line('reports_item_unit_price'); ?></label>
                        <?php echo form_input(array(
                            'name' => 'unit_price_with_tax',
                            'id' => 'unit_price_with_tax',
                            'type' => 'number',
                            'step' => '0.01',
                            'class' => 'form-control',
                            'style' => 'text-align: right;',
                            'value' => isset($_SESSION['transaction_info_pricelist'][0]['unit_price_with_tax']) ? $_SESSION['transaction_info_pricelist'][0]['unit_price_with_tax'] : 0
                        )); ?>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label required"><?php echo $this->lang->line('items_category'); ?></label>
                        <?php echo form_dropdown(
                            'category_id',
                            $_SESSION['category_pick_list'] ?? array(),
                            $_SESSION['selected_category'] ?? '',
                            'class="form-control"'
                        ); ?>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label"><?php echo $this->lang->line('item_kits_code_bar'); ?></label>
                        <?php echo form_input(array(
                            'name' => 'code_bar',
                            'id' => 'code_bar',
                            'class' => 'form-control',
                            'placeholder' => 'Code barre',
                            'value' => $_SESSION['transaction_info']->barcode ?? ''
                        )); ?>
                    </div>
                </div>
            </div>
        </fieldset>

        <?php if (isset($_SESSION['transaction_info']->item_kit_id) && $_SESSION['transaction_info']->item_kit_id > 0): ?>
        <fieldset class="fieldset">
            <legend>
                <?php echo $this->lang->line('item_kits_items'); ?>
                <a href="<?php echo site_url("item_kits/view_add_item/" . $_SESSION['transaction_info']->item_kit_id); ?>" class="btn-add-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Ajouter un article
                </a>
            </legend>

            <div class="kit-items-container">
                <?php if (!empty($_SESSION['transaction_items_info'])): ?>
                <table class="kit-items-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"></th>
                            <th><?php echo $this->lang->line('items_item_number'); ?></th>
                            <th><?php echo $this->lang->line('items_libelle'); ?></th>
                            <th style="width: 100px; text-align: center;"><?php echo $this->lang->line('item_kits_quantity'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['transaction_items_info'] as $key_line => $row): ?>
                        <tr>
                            <td class="cell-action">
                                <a href="<?php echo site_url('item_kits/delete/' . $_SESSION['transaction_info']->item_kit_id . '/' . $_SESSION['transaction_items_info_items'][$key_line]->item_id . '/' . $key_line); ?>"
                                   class="btn-delete-item" title="<?php echo $this->lang->line('common_delete'); ?>">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </a>
                            </td>
                            <td><span class="badge-ref"><?php echo htmlspecialchars($_SESSION['transaction_items_info_items'][$key_line]->item_number ?? ''); ?></span></td>
                            <td><?php echo htmlspecialchars($_SESSION['transaction_items_info_items'][$key_line]->name ?? ''); ?></td>
                            <td style="text-align: center;">
                                <span class="badge-qty"><?php echo $_SESSION['transaction_items_info'][$key_line]['quantity'] ?? 0; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="empty-message">Aucun article dans ce kit. Cliquez sur "Ajouter un article" pour commencer.</p>
                <?php endif; ?>
            </div>
        </fieldset>
        <?php endif; ?>

        <div class="txt_milieu">
            <?php echo form_submit(array(
                'name' => 'submit',
                'id' => 'submit',
                'value' => $this->lang->line('common_submit'),
                'class' => 'btsubmit'
            )); ?>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<style>
.modal-lg { width: 900px; max-width: 95vw; }
.form-grid { display: flex; flex-direction: column; gap: 16px; }
.form-row { display: flex; gap: 16px; flex-wrap: wrap; }
.form-group { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.form-label { font-size: 0.85rem; font-weight: 500; color: var(--text-secondary, #64748b); }
.form-label.required::after { content: ' *'; color: var(--danger, #ef4444); }
.form-control {
    padding: 10px 12px;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    font-size: 0.9rem;
    background: var(--bg-input, #fff);
    color: var(--text-primary, #1e293b);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--primary, #0A6184);
    box-shadow: 0 0 0 3px rgba(10, 97, 132, 0.15);
}

.fieldset legend {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.btn-add-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 0.8rem;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-add-item:hover {
    background: #1d4ed8;
    color: #fff;
}

.kit-items-container {
    margin-top: 12px;
}

.kit-items-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.kit-items-table th {
    background: var(--bg-card, #f8fafc);
    padding: 10px 12px;
    text-align: left;
    font-weight: 600;
    color: var(--text-secondary, #64748b);
    border-bottom: 2px solid var(--border-color, #e2e8f0);
}
.kit-items-table td {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border-color, #e2e8f0);
}
.kit-items-table tbody tr:hover {
    background: var(--bg-card, #f8fafc);
}

.btn-delete-item {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: #fee2e2;
    color: #dc2626;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-delete-item:hover {
    background: #fecaca;
}

.badge-ref {
    display: inline-block;
    padding: 2px 8px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-qty {
    display: inline-block;
    padding: 4px 12px;
    background: #f0fdf4;
    color: #166534;
    border-radius: 4px;
    font-weight: 600;
}

.empty-message {
    text-align: center;
    color: var(--text-secondary, #64748b);
    padding: 30px;
    font-style: italic;
}

.cell-action {
    text-align: center;
}
</style>
