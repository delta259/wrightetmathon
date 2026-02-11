<?php $this->load->view("partial/header_popup"); ?>

<div open class="fenetre modale modal-lg">
    <!-- Header -->
    <div class="fenetre-header">
        <span class="fenetre-title">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Ajouter un article - <?php echo htmlspecialchars($_SESSION['transaction_info']->name ?? ''); ?>
        </span>
        <?php include('../wrightetmathon/application/views/partial/show_exit.php'); ?>
    </div>

    <!-- Content -->
    <div class="fenetre-content">
        <?php include('../wrightetmathon/application/views/partial/show_messages.php'); ?>

        <fieldset class="fieldset">
            <legend>Rechercher un article</legend>

            <div class="form-grid">
                <div class="form-row">
                    <div class="form-group" style="flex: 3;">
                        <?php echo form_open('item_kits/search_item/'.$_SESSION['transaction_info']->item_kit_id, array('id'=>'item_kit_form_item_kit')); ?>
                        <div class="search-input-wrapper-modal">
                            <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <input type="text" id="search_item_kit" name="search_item_kit" class="form-control search-field-modal"
                                   placeholder="Rechercher par code ou nom d'article..."
                                   value="<?php echo isset($_SESSION['item_kit_item']['item_number']) ? htmlspecialchars($_SESSION['item_kit_item']['item_number']) : ''; ?>"
                                   autofocus="autofocus">
                        </div>
                        </form>
                    </div>
                </div>

                <?php if (isset($_SESSION['item_kit_item']['item_number']) && $_SESSION['item_kit_item']['item_number'] != ''): ?>
                <div class="selected-item-box">
                    <div class="selected-item-label">Article sélectionné :</div>
                    <div class="selected-item-details">
                        <span class="badge-ref"><?php echo htmlspecialchars($_SESSION['item_kit_item']['item_number']); ?></span>
                        <span class="selected-item-name"><?php echo htmlspecialchars($_SESSION['item_kit_item']['name'] ?? ''); ?></span>
                    </div>
                </div>

                <?php echo form_open('item_kits/add_item/' . $_SESSION['transaction_info']->item_kit_id, array('id'=>'item_kit_form')); ?>
                <div class="form-row" style="align-items: flex-end;">
                    <div class="form-group" style="flex: 0 0 150px;">
                        <label class="form-label required"><?php echo $this->lang->line('items_quantity'); ?></label>
                        <input type="number" step="1" id="quantity" name="quantity" class="form-control colorobligatoire"
                               value="1" min="1" style="text-align: center;">
                    </div>
                    <div class="form-group">
                        <?php echo form_submit(array(
                            'name' => 'submit',
                            'id' => 'submit_add',
                            'value' => 'Ajouter au kit',
                            'class' => 'btn-add-to-kit'
                        )); ?>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        </fieldset>

        <?php if (!empty($_SESSION['transaction_items_info'])): ?>
        <fieldset class="fieldset">
            <legend><?php echo $this->lang->line('item_kits_items'); ?> (<?php echo count($_SESSION['transaction_items_info']); ?>)</legend>

            <div class="kit-items-container">
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
            </div>
        </fieldset>
        <?php endif; ?>

        <div class="txt_milieu">
            <?php echo anchor('item_kits/view/'.$_SESSION['transaction_info']->item_kit_id, '<div class="btretour btlien">Retour au kit</div>', 'target="_self"'); ?>
        </div>
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

.search-input-wrapper-modal {
    position: relative;
    display: flex;
    align-items: center;
}
.search-input-wrapper-modal .search-icon {
    position: absolute;
    left: 12px;
    color: var(--text-secondary, #64748b);
}
.search-field-modal {
    padding-left: 40px !important;
    width: 100%;
}

.selected-item-box {
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 8px;
    padding: 12px 16px;
}
.selected-item-label {
    font-size: 0.8rem;
    color: #166534;
    margin-bottom: 6px;
}
.selected-item-details {
    display: flex;
    align-items: center;
    gap: 12px;
}
.selected-item-name {
    font-weight: 500;
    color: var(--text-primary, #1e293b);
}

.btn-add-to-kit {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    background: #22c55e;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-add-to-kit:hover {
    background: #16a34a;
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

.cell-action {
    text-align: center;
}
</style>
