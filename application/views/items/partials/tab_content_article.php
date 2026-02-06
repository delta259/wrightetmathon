<?php
/**
 * Tab content partial: Article
 * Contains only the body content for the Article tab
 * Used by modal_wrapper.php and AJAX tab loading
 */
?>

<!-- Messages -->
<?php include(APPPATH . 'views/partial/show_messages.php'); ?>

<?php
// Only show form if item is NOT deleted
if ($_SESSION['del'] == NULL && $_SESSION['undel'] == NULL)
{
    // Form open — same action as before
    echo form_open(
        $_SESSION['controller_name'] . '/save/' . $_SESSION['transaction_info']->item_id . '/' . $_SESSION['origin'],
        array('id' => 'item_form')
    );
?>

    <div class="md-grid-2col">

        <!-- ===== LEFT COLUMN ===== -->
        <div>

            <!-- CARD: Identification -->
            <div class="md-card">
                <div class="md-card-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Identification
                </div>

                <div class="md-form-row">
                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label required"><?php echo $this->lang->line('items_item_number'); ?></label>
                        <?php echo form_input(array(
                            'name'        => 'item_number',
                            'id'          => 'item_number',
                            'class'       => 'md-form-input required',
                            'placeholder' => $this->lang->line('items_item_number'),
                            'value'       => $_SESSION['transaction_info']->item_number
                        )); ?>
                    </div>
                    <div class="md-form-group" style="flex:2;">
                        <label class="md-form-label required"><?php echo $this->lang->line('items_name'); ?></label>
                        <?php echo form_input(array(
                            'name'        => 'name',
                            'id'          => 'name',
                            'class'       => 'md-form-input required',
                            'placeholder' => $this->lang->line('items_name'),
                            'value'       => $_SESSION['transaction_info']->name
                        )); ?>
                    </div>
                </div>

                <div class="md-form-group">
                    <label class="md-form-label required"><?php echo $this->lang->line('items_category'); ?></label>
                    <?php echo form_dropdown(
                        'category_id',
                        $_SESSION['category_pick_list'],
                        $_SESSION['selected_category'],
                        'class="md-form-select required" id="category_id"'
                    ); ?>
                </div>

                <div class="md-form-row">
                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label required"><?php echo $this->lang->line('items_volume'); ?></label>
                        <div class="md-input-with-suffix">
                            <?php echo form_input(array(
                                'name'        => 'volume',
                                'id'          => 'volume',
                                'class'       => 'md-form-input required',
                                'style'       => 'text-align:right;',
                                'placeholder' => '0',
                                'value'       => $_SESSION['transaction_info']->volume
                            )); ?>
                            <span class="md-input-suffix">Ml</span>
                        </div>
                    </div>
                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label required"><?php echo $this->lang->line('items_nicotine'); ?></label>
                        <div class="md-input-with-suffix">
                            <?php echo form_input(array(
                                'name'        => 'nicotine',
                                'id'          => 'nicotine',
                                'class'       => 'md-form-input required',
                                'style'       => 'text-align:right;',
                                'placeholder' => '0',
                                'value'       => $_SESSION['transaction_info']->nicotine
                            )); ?>
                            <span class="md-input-suffix">Mg/Ml</span>
                        </div>
                    </div>
                </div>
            </div><!-- /card Identification -->


            <!-- CARD: Indicateurs -->
            <div class="md-card">
                <div class="md-card-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    Indicateurs
                </div>

                <div class="md-toggle-row">
                    <!-- DLUO toggle -->
                    <div class="md-toggle-group">
                        <span class="md-toggle-label"><?php echo $this->lang->line('items_dluo_indicator'); ?></span>
                        <label class="md-toggle">
                            <input type="checkbox" class="md-toggle-input" data-target="dluo_indicator"
                                <?php echo ($_SESSION['selected_dluo_indicator'] == 'Y') ? 'checked' : ''; ?>>
                            <span class="md-toggle-slider"></span>
                        </label>
                        <?php echo form_dropdown(
                            'dluo_indicator',
                            $_SESSION['G']->YorN_pick_list,
                            $_SESSION['selected_dluo_indicator'],
                            'class="md-toggle-select" id="dluo_indicator" style="display:none;"'
                        ); ?>
                    </div>

                    <!-- Giftcard toggle -->
                    <div class="md-toggle-group">
                        <span class="md-toggle-label"><?php echo $this->lang->line('items_giftcard_indicator'); ?></span>
                        <label class="md-toggle">
                            <input type="checkbox" class="md-toggle-input" data-target="giftcard_indicator"
                                <?php echo ($_SESSION['selected_giftcard_indicator'] == 'Y') ? 'checked' : ''; ?>>
                            <span class="md-toggle-slider"></span>
                        </label>
                        <?php echo form_dropdown(
                            'giftcard_indicator',
                            $_SESSION['G']->YorN_pick_list,
                            $_SESSION['selected_giftcard_indicator'],
                            'class="md-toggle-select" id="giftcard_indicator" style="display:none;"'
                        ); ?>
                    </div>

                    <!-- Offer toggle -->
                    <div class="md-toggle-group">
                        <span class="md-toggle-label"><?php echo $this->lang->line('items_offer_indicator'); ?></span>
                        <label class="md-toggle">
                            <input type="checkbox" class="md-toggle-input" data-target="offer_indicator"
                                <?php echo ($_SESSION['selected_offer_indicator'] == 'Y') ? 'checked' : ''; ?>>
                            <span class="md-toggle-slider"></span>
                        </label>
                        <?php echo form_dropdown(
                            'offer_indicator',
                            $_SESSION['G']->YorN_pick_list,
                            $_SESSION['selected_offer_indicator'],
                            'class="md-toggle-select" id="offer_indicator" style="display:none;"'
                        ); ?>
                    </div>
                </div>

                <!-- Valeur offre & TVA -->
                <div class="md-form-row" style="margin-top:16px;">
                    <?php if ($_SESSION['G']->login_employee_info->admin == 1) { ?>
                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label required"><?php echo $this->lang->line('items_offer_value'); ?></label>
                        <div class="md-input-with-suffix">
                            <?php echo form_input(array(
                                'name'  => 'offer_value',
                                'id'    => 'offer_value',
                                'class' => 'md-form-input required',
                                'style' => 'text-align:right;',
                                'value' => isset($_SESSION['offer_value']) ? floatval($_SESSION['offer_value']) : 0
                            )); ?>
                            <span class="md-input-suffix">&euro;</span>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label required"><?php echo $this->lang->line('items_tax_1'); ?></label>
                        <div class="md-input-with-suffix">
                            <?php echo form_input(array(
                                'name'  => 'tax_percent_1',
                                'id'    => 'tax_percent_1',
                                'class' => 'md-form-input required',
                                'style' => 'text-align:right;',
                                'value' => isset($_SESSION['item_tax_info'][0]['percent']) ? round($_SESSION['item_tax_info'][0]['percent'], 2) : $this->config->item('default_tax_1_rate')
                            )); ?>
                            <span class="md-input-suffix">%</span>
                        </div>
                    </div>
                </div>
            </div><!-- /card Indicateurs -->


            <?php
            // ===== CARD: VapeSelf (conditionnel) =====
            if ($this->config->item('distributeur_vapeself') == 'Y')
            {
                $item_id = $_SESSION['transaction_info']->item_id;
            ?>
            <div class="md-card">
                <div class="md-card-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    Distributeur VapeSelf
                </div>

                <div class="md-vs-quantity">
                    Quantite disponible dans le distributeur : <strong><?php echo isset($_SESSION['vs_quantity'][$item_id]) ? $_SESSION['vs_quantity'][$item_id] : '0'; ?></strong>
                </div>

                <div class="md-grid-2eq">
                    <div>
                        <div class="md-form-group">
                            <label class="md-form-label">Nom</label>
                            <input type="text" id="vs_nom" name="vs_nom" class="md-form-input"
                                value="<?php echo htmlspecialchars($_SESSION['transaction_info']->vs_nom); ?>">
                        </div>
                        <div class="md-form-group">
                            <label class="md-form-label">Marque</label>
                            <input type="text" id="vs_marque" name="vs_marque" class="md-form-input"
                                value="<?php echo htmlspecialchars($_SESSION['transaction_info']->vs_marque); ?>">
                        </div>
                        <div class="md-form-group">
                            <label class="md-form-label">Type</label>
                            <?php
                            $_SESSION['selected_vs_category_type'] = $_SESSION['G']->vs_category_all[$_SESSION['transaction_info']->vs_category]['type'];
                            echo form_dropdown(
                                'vs_category_type',
                                $_SESSION['G']->vs_category_type,
                                $_SESSION['selected_vs_category_type'],
                                'class="md-form-select" id="vs_category_type"'
                            );
                            ?>
                        </div>
                        <div class="md-form-group">
                            <label class="md-form-label required">Category</label>
                            <?php
                            $_SESSION['selected_vs_category'] = $_SESSION['transaction_info']->vs_category;
                            echo form_dropdown(
                                'vs_category',
                                $_SESSION['G']->vs_category,
                                $_SESSION['selected_vs_category'],
                                'class="md-form-select required" id="vs_category"'
                            );
                            ?>
                        </div>
                    </div>

                    <div>
                        <div class="md-form-group">
                            <label class="md-form-label">Param1 <span style="color:var(--text-muted);font-weight:400;text-transform:none;">(Nicotine, couleur)</span></label>
                            <input type="text" id="vs_param_1" name="vs_param_1" class="md-form-input"
                                value="<?php echo htmlspecialchars($_SESSION['transaction_info']->vs_param_1); ?>">
                        </div>
                        <div class="md-form-group">
                            <label class="md-form-label">Param2 <span style="color:var(--text-muted);font-weight:400;text-transform:none;">(Volume)</span></label>
                            <input type="text" id="vs_param_2" name="vs_param_2" class="md-form-input"
                                value="<?php echo htmlspecialchars($_SESSION['transaction_info']->vs_param_2); ?>">
                        </div>
                        <div class="md-form-group">
                            <label class="md-form-label">Param3 <span style="color:var(--text-muted);font-weight:400;text-transform:none;">(PG / VG)</span></label>
                            <input type="text" id="vs_param_3" name="vs_param_3" class="md-form-input"
                                value="<?php echo htmlspecialchars($_SESSION['transaction_info']->vs_param_3); ?>">
                        </div>
                    </div>
                </div>

                <div class="md-form-row">
                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label">Emplacement</label>
                        <?php echo form_dropdown(
                            'emplacement',
                            $_SESSION['G']->distributor,
                            $_SESSION['transaction_info']->emplacement,
                            'class="md-form-select" id="emplacement"'
                        ); ?>
                    </div>
                    <div class="md-form-group" style="flex:1;">
                        <label class="md-form-label">Nom Image</label>
                        <?php
                        $nom_image = (!isset($_SESSION['transaction_info']->vs_nom_image) || ($_SESSION['transaction_info']->vs_nom_image == ''))
                            ? $_SESSION['transaction_info']->item_number . '.jpg'
                            : $_SESSION['transaction_info']->vs_nom_image;
                        ?>
                        <input type="text" id="vs_nom_image" name="vs_nom_image" class="md-form-input"
                            value="<?php echo htmlspecialchars($nom_image); ?>">
                    </div>
                </div>

                <div class="md-vs-actions">
                    <a href="<?php echo site_url("items/update_VS_item/$item_id"); ?>" class="md-btn md-btn-secondary md-btn-sm">
                        <?php echo $this->lang->line('items_maj_item'); ?>
                    </a>
                    <a href="<?php echo site_url("items/verify_info_article_vapeself/$item_id"); ?>" class="md-btn md-btn-secondary md-btn-sm">
                        <?php echo $this->lang->line('items_vs_verify'); ?>
                    </a>
                    <a href="<?php echo site_url("items/integration_item_to/$item_id"); ?>" class="md-btn md-btn-secondary md-btn-sm">
                        <?php echo $this->lang->line('items_vs_integration'); ?>
                    </a>
                </div>
            </div><!-- /card VapeSelf -->
            <?php } /* end VapeSelf */ ?>

            <!-- Required fields note -->
            <div class="md-required-note">
                <span>*</span> <?php echo $this->lang->line('common_fields_required_message'); ?>
            </div>

        </div><!-- /left column -->


        <!-- ===== RIGHT COLUMN (sidebar) ===== -->
        <div class="md-sidebar">

            <!-- Product Image -->
            <div class="md-card" style="text-align:center;">
                <?php
                // Load product image
                $product_image_url = base_url() . 'SLIDES_VENTES/cadre.png';
                $current_image_file_name = '';
                if ($_SESSION['show_image'] != 'N' && isset($_SESSION['transaction_info']->item_id)) {
                    $img_data = $this->Item->get_info($_SESSION['transaction_info']->item_id);
                    if (!empty($img_data->image_file_name)) {
                        $current_image_file_name = $img_data->image_file_name;
                        // Vérifier si c'est une URL valide (contient un domaine avec au moins un point)
                        if (preg_match('/^[a-zA-Z0-9][-a-zA-Z0-9]*\.[a-zA-Z]/', $img_data->image_file_name)) {
                            $product_image_url = 'http://' . $img_data->image_file_name;
                        }
                    }
                }
                ?>
                <div class="md-product-image-wrapper" id="product-image-wrapper"
                     style="cursor: pointer;"
                     title="Cliquer pour modifier l'URL de l'image"
                     onclick="editProductImage(this); return false;"
                     data-item-id="<?php echo $_SESSION['transaction_info']->item_id ?? ''; ?>"
                     data-current-url="<?php echo htmlspecialchars($current_image_file_name); ?>">
                    <img id="product-image" src="<?php echo $product_image_url; ?>" alt="<?php echo htmlspecialchars($_SESSION['transaction_info']->name ?? ''); ?>"
                         onerror="this.src='<?php echo base_url(); ?>SLIDES_VENTES/cadre.png'">
                    <div class="image-edit-overlay">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Price summary -->
            <div class="md-price-card">
                <div class="md-price-card-title">Prix</div>

                <div class="md-price-row">
                    <span class="md-price-label"><?php echo $this->lang->line('items_unit_price'); ?></span>
                    <span class="md-price-value"><?php echo $_SESSION['transaction_info']->unit_price; ?> &euro;</span>
                </div>

                <div class="md-price-row">
                    <span class="md-price-label"><?php echo $this->lang->line('items_cost_price'); ?></span>
                    <span class="md-price-value"><?php echo $_SESSION['preferred_supplier_cost_price']; ?> &euro;</span>
                </div>

                <div class="md-price-divider"></div>

                <div class="md-price-row">
                    <span class="md-price-label"><?php echo $this->lang->line('items_margin'); ?></span>
                    <span class="md-price-margin"><?php echo $_SESSION['percentage_profit']; ?> %</span>
                </div>
            </div>

        </div><!-- /right column -->

    </div><!-- /md-grid-2col -->

<?php
    echo form_close();
} /* end if undel==NULL && del==NULL */
?>

<!-- Image URL Edit Styles -->
<style>
.md-product-image-wrapper {
    position: relative;
    display: inline-block;
}
.md-product-image-wrapper:hover .image-edit-overlay {
    opacity: 1;
}
.image-edit-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
    border-radius: 8px;
}
.image-edit-overlay svg {
    color: white;
    width: 32px;
    height: 32px;
}
</style>

<!-- Image URL Edit Script (compatible AJAX loading) -->
<script type="text/javascript">
function editProductImage(element) {
    var wrapper = $(element);
    var itemId = wrapper.attr('data-item-id');
    var currentUrl = wrapper.attr('data-current-url') || '';

    if (!itemId || itemId == '' || itemId == '-1') {
        alert('Veuillez d\'abord enregistrer l\'article avant de définir une image.');
        return false;
    }

    var newUrl = prompt('Entrez l\'URL de l\'image produit :\n(sans le préfixe http://)', currentUrl);

    if (newUrl === null) {
        return false; // Annulé
    }

    // Nettoyer l'URL (retirer http:// ou https:// si présent)
    newUrl = newUrl.replace(/^https?:\/\//, '').trim();

    $.ajax({
        url: '<?php echo site_url("items/ajax_save_image_url"); ?>',
        type: 'POST',
        data: {
            item_id: itemId,
            image_url: newUrl
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Mettre à jour l'image affichée
                var isValidUrl = newUrl && /^[a-zA-Z0-9][-a-zA-Z0-9]*\.[a-zA-Z]/.test(newUrl);
                var imgSrc = isValidUrl ? 'http://' + newUrl : '<?php echo base_url(); ?>SLIDES_VENTES/cadre.png';
                $('#product-image').attr('src', imgSrc);
                // Rafraîchir aussi la vignette du header
                $('#header-avatar-image').attr('src', imgSrc);
                wrapper.attr('data-current-url', newUrl);
                alert('Image mise à jour avec succès');
            } else {
                alert('Erreur: ' + (response.error || 'Impossible de sauvegarder'));
            }
        },
        error: function() {
            alert('Erreur de connexion');
        }
    });
    return false;
}
</script>
