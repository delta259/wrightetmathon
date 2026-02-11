function get_dimensions() 
{
	var dims = {width:0,height:0};
	
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    dims.width = window.innerWidth;
    dims.height = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    dims.width = document.documentElement.clientWidth;
    dims.height = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    dims.width = document.body.clientWidth;
    dims.height = document.body.clientHeight;
  }
  
  return dims;
}

function set_feedback(text, classname, keep_displayed)
{
	if(text!='')
	{
		$('#feedback_bar').removeClass();
		$('#feedback_bar').addClass(classname);
		$('#feedback_bar').text(text);
		$('#feedback_bar').css('opacity','1');

		if(!keep_displayed)
		{
			$('#feedback_bar').fadeTo(5000, 1);
			$('#feedback_bar').fadeTo("fast",0);
		}
	}
	else
	{
		$('#feedback_bar').css('opacity','0');
	}
}

//keylisteners

$(window).jkey('f1',function(){
window.location = BASE_URL + '/customers/index';
});


$(window).jkey('f2',function(){
window.location = BASE_URL + '/items/index';
});


$(window).jkey('f3',function(){
window.location = BASE_URL + '/reports/index';
});

$(window).jkey('f4',function(){
window.location = BASE_URL + '/suppliers/index';
});

$(window).jkey('f5',function(){
window.location = BASE_URL + '/receivings/index';
});


$(window).jkey('f6',function(){
window.location = BASE_URL + '/sales/index';
});

$(window).jkey('f7',function(){
window.location = BASE_URL + '/employees/index';
});

$(window).jkey('f8',function(){
window.location = BASE_URL + '/config/index';
});

$(window).jkey('f9',function(){
window.location = BASE_URL + '/giftcards/index';
});

// ---- Modern Detail Modal: toggle switch sync ----
// Syncs checkbox state to hidden <select> for form POST compatibility
// jQuery 1.2.6 compatible — uses .click() not .on()
$('.md-toggle-input').click(function(){
    var checked = this.checked;
    var val = checked ? 'Y' : 'N';
    // Find the hidden select in the same toggle-group parent
    var group = $(this).parents('.md-toggle-group');
    group.find('.md-toggle-select').val(val);
    // Update the visible value label if present
    group.find('.md-toggle-value').text(val);
});

// Close modal on Escape key
$(document).keydown(function(e){
    if (e.keyCode === 27 && $('.md-modal-overlay').length > 0) {
        window.location = BASE_URL + '/common_controller/common_exit/';
    }
});

// ---- AJAX Tab Navigation for Modern Modal ----
// Loads tab content without reloading the full page (items table)
// Only replaces the modal body content, keeping header/tabs/footer intact
// jQuery 1.2.6 compatible

function initModalAjaxTabs() {
    $('.md-tab-ajax').unbind('click').click(function(e){
        var ajaxUrl = $(this).attr('data-ajax-url');
        if (!ajaxUrl) return true; // fallback to normal link

        e.preventDefault();

        // Don't reload if already active
        if ($(this).hasClass('md-tab-active')) return false;

        var $body = $('#md-modal-body-content');
        var $clickedTab = $(this);

        if ($body.length === 0) {
            // Fallback: if no body container, use old behavior
            window.location = $(this).attr('href');
            return false;
        }

        // Show loading state
        $body.css('opacity', '0.5');

        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            cache: false,
            success: function(html) {
                // Replace only the body content
                $body.html(html);
                $body.css('opacity', '1');

                // Update active tab
                $('.md-tab').removeClass('md-tab-active');
                $clickedTab.addClass('md-tab-active');

                // Re-initialize handlers on new content
                initModalHandlers();
            },
            error: function() {
                // On error, fallback to normal navigation
                $body.css('opacity', '1');
                window.location = $clickedTab.attr('href');
            }
        });

        return false;
    });
}

function initModalHandlers() {
    // Toggle switch sync
    $('.md-toggle-input').unbind('click').click(function(){
        var checked = this.checked;
        var val = checked ? 'Y' : 'N';
        var group = $(this).parents('.md-toggle-group');
        group.find('.md-toggle-select').val(val);
        group.find('.md-toggle-value').text(val);
    });

    // Show/hide add supplier card button
    $('#btn_show_add_supplier').unbind('click').click(function(){
        var card = $('#supplier_add_card');
        if (card.css('display') === 'none') {
            // Valeurs par defaut — toggles
            $('#add_supplier_preferred_toggle').attr('checked', true);
            $('#add_supplier_preferred').val('Y');
            $('#add_supplier_preferred_toggle').parents('.md-toggle-group').find('.md-toggle-value').text('Y');
            // Passer tous les fournisseurs existants en non-prefere (toggles + hidden selects)
            $('#supplier_update_form .md-toggle-group').each(function(){
                var sel = $(this).find('.md-toggle-select[name^="supplier_preferred_"]');
                if (sel.length) {
                    sel.val('N');
                    $(this).find('.md-toggle-input').attr('checked', false);
                }
            });
            $('#add_supplier_reorder_toggle').attr('checked', true);
            $('#add_supplier_reorder_policy').val('Y');
            $('#add_supplier_reorder_toggle').parents('.md-toggle-group').find('.md-toggle-value').text('Y');
            $('#add_supplier_reorder_pack_size').val('1');
            $('#add_supplier_min_order_qty').val('1');
            $('#add_supplier_min_stock_qty').val('1');
            // Reprendre le code barre d'un fournisseur existant
            var existingBarcode = '';
            $('input[name^="supplier_bar_code_"]').each(function(){
                var v = $(this).val();
                if (v && v !== '' && existingBarcode === '') {
                    existingBarcode = v;
                }
            });
            if (existingBarcode !== '') {
                $('#add_supplier_bar_code').val(existingBarcode);
            }
            card.slideDown(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
        } else {
            card.slideUp(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Ajouter Fournisseur');
        }
    });

    // Show/hide add pricelist card button
    $('#btn_show_add_pricelist').unbind('click').click(function(){
        var card = $('#pricelist_add_card');
        if (card.css('display') === 'none') {
            card.slideDown(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
        } else {
            card.slideUp(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Ajouter Tarif');
        }
    });

    // Show/hide add DLUO card button
    $('#btn_show_add_dluo').unbind('click').click(function(){
        var card = $('#dluo_add_card');
        if (card.css('display') === 'none') {
            card.slideDown(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
        } else {
            card.slideUp(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Ajouter DLUO');
        }
    });

    // Show/hide add kit structure card button
    $('#btn_show_add_kit_structure').unbind('click').click(function(){
        var card = $('#kit_structure_add_card');
        if (card.css('display') === 'none') {
            card.slideDown(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
        } else {
            card.slideUp(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Ajouter Structure');
        }
    });

    // Show/hide add kit detail card button
    $('#btn_show_add_kit_detail').unbind('click').click(function(){
        var card = $('#kit_detail_add_card');
        if (card.css('display') === 'none') {
            card.slideDown(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"></line></svg> Annuler');
        } else {
            card.slideUp(200);
            $(this).html('<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Ajouter Detail');
        }
    });

    // Pricelist date picker sync
    $('#pricelist_add_form').unbind('submit').submit(function(){
        var from = $('#add_valid_from_picker').val();
        if (from && from !== '') {
            var p = from.split('-');
            $('#add_valid_from').val(p[2] + '/' + p[1] + '/' + p[0]);
        } else {
            $('#add_valid_from').val('00/00/0000');
        }
        var to = $('#add_valid_to_picker').val();
        if (to && to !== '') {
            var p2 = to.split('-');
            $('#add_valid_to').val(p2[2] + '/' + p2[1] + '/' + p2[0]);
        } else {
            $('#add_valid_to').val('00/00/0000');
        }
    });

    // Fuzzy search merge
    initFuzzySearchMerge();
}

// ---- Fuzzy Search & Merge ----

// Global state for active filter (default: OFF = show deleted items)
window._fuzzyActiveOnly = false;
window._fuzzyAjaxUrl = '';

function initFuzzySearchMerge() {
    $('#btn-fuzzy-search-merge').unbind('click').click(function(e) {
        e.preventDefault();
        var $btn = $(this);
        var ajaxUrl = $btn.attr('data-ajax-url');
        var $body = $('#md-modal-body-content');

        if (!ajaxUrl || $body.length === 0) return false;

        // Store base URL for filter toggle
        window._fuzzyAjaxUrl = ajaxUrl;
        window._fuzzyActiveOnly = false; // Reset to default (OFF)

        doFuzzySearch($body);
        return false;
    });
}

function doFuzzySearch($body) {
    var ajaxUrl = window._fuzzyAjaxUrl;
    if (!ajaxUrl) return;

    // Add active_only parameter
    var separator = ajaxUrl.indexOf('?') >= 0 ? '&' : '?';
    var fullUrl = ajaxUrl + separator + 'active_only=' + (window._fuzzyActiveOnly ? '1' : '0');

    $body.html('<div class="md-fuzzy-loading">Recherche en cours...</div>');

    $.ajax({
        url: fullUrl,
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(resp) {
            if (!resp.success) {
                $body.html('<div class="md-fuzzy-no-results">' + (resp.error || 'Erreur') + '</div>');
                return;
            }

            var filterLabel = window._fuzzyActiveOnly ? 'actifs' : 'd\u00e9sactiv\u00e9s';

            if (!resp.results || resp.results.length === 0) {
                var noHtml = '<div style="padding: 20px;">'
                    + '<div class="md-fuzzy-results-header">'
                    + '<h3 style="margin:0;font-size:15px;">Recherche d\'articles ' + filterLabel + ' similaires</h3>'
                    + '<div class="md-fuzzy-header-actions">'
                    + buildActiveToggle()
                    + '<button type="button" class="md-btn md-btn-sm md-btn-secondary md-fuzzy-back-btn">Retour</button>'
                    + '</div>'
                    + '</div>'
                    + '<div class="md-fuzzy-no-results">Aucun article ' + filterLabel + ' similaire trouv\u00e9 pour "' + resp.current_item.name + '"</div>'
                    + '</div>';
                $body.html(noHtml);
                initFuzzyBackButton();
                initFuzzyActiveToggle($body);
                return;
            }

            // Store results for sorting
            window._fuzzyResults = resp.results;
            window._fuzzyCurrentName = resp.current_item.name;
            window._fuzzySortCol = 'composite_score';
            window._fuzzySortDir = 'desc';

            renderFuzzyResultsTable($body);
            initFuzzyBackButton();
            initFuzzyMergeButtons();
            initFuzzySortHeaders();
            initFuzzyActiveToggle($body);
        },
        error: function() {
            $body.html('<div class="md-fuzzy-no-results">Erreur de connexion</div>');
        }
    });
}

function buildActiveToggle() {
    var checked = window._fuzzyActiveOnly ? 'checked' : '';
    return '<label class="md-fuzzy-toggle" title="Filtrer les produits actifs">'
        + '<span class="md-fuzzy-toggle-label">Actifs</span>'
        + '<input type="checkbox" id="fuzzy-active-toggle" ' + checked + '>'
        + '<span class="md-fuzzy-toggle-slider"></span>'
        + '</label>';
}

function initFuzzyActiveToggle($body) {
    $('#fuzzy-active-toggle').unbind('change').change(function() {
        window._fuzzyActiveOnly = $(this).is(':checked');
        doFuzzySearch($body);
    });
}

function renderFuzzyResultsTable($body) {
    var results = window._fuzzyResults;
    var sortCol = window._fuzzySortCol;
    var sortDir = window._fuzzySortDir;

    // Sort results
    var sorted = [];
    for (var i = 0; i < results.length; i++) {
        sorted.push(results[i]);
    }
    sorted.sort(function(a, b) {
        var va, vb;
        if (sortCol === 'composite_score') {
            va = parseFloat(a.composite_score);
            vb = parseFloat(b.composite_score);
            return sortDir === 'asc' ? va - vb : vb - va;
        } else {
            va = (a.name || '').toLowerCase();
            vb = (b.name || '').toLowerCase();
            if (va < vb) return sortDir === 'asc' ? -1 : 1;
            if (va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        }
    });

    var scoreArrow = (sortCol === 'composite_score') ? (sortDir === 'asc' ? ' \u25b2' : ' \u25bc') : '';
    var nameArrow = (sortCol === 'name') ? (sortDir === 'asc' ? ' \u25b2' : ' \u25bc') : '';
    var filterLabel = window._fuzzyActiveOnly ? 'actifs' : 'd\u00e9sactiv\u00e9s';

    var html = '<div style="padding: 20px;">'
        + '<div class="md-fuzzy-results-header">'
        + '<h3 style="margin:0;font-size:15px;">Articles ' + filterLabel + ' similaires \u00e0 "' + window._fuzzyCurrentName + '"</h3>'
        + '<div class="md-fuzzy-header-actions">'
        + buildActiveToggle()
        + '<button type="button" class="md-btn md-btn-sm md-btn-secondary md-fuzzy-back-btn">Retour</button>'
        + '</div>'
        + '</div>'
        + '<div class="md-card" style="overflow-x:auto;">'
        + '<table class="md-table" style="width:100%;">'
        + '<thead><tr>'
        + '<th class="md-fuzzy-sort" data-sort-col="composite_score" style="cursor:pointer;">Score' + scoreArrow + '</th>'
        + '<th>R\u00e9f\u00e9rence</th>'
        + '<th class="md-fuzzy-sort" data-sort-col="name" style="cursor:pointer;">Nom' + nameArrow + '</th>'
        + '<th>Cat\u00e9gorie</th><th>Qt\u00e9</th><th>Action</th>'
        + '</tr></thead><tbody>';

    for (var i = 0; i < sorted.length; i++) {
        var r = sorted[i];
        html += '<tr>'
            + '<td><span class="md-fuzzy-score">' + r.composite_score + '</span></td>'
            + '<td>' + r.item_number + '</td>'
            + '<td>' + r.name + '</td>'
            + '<td>' + (r.category || '') + '</td>'
            + '<td>' + r.quantity + '</td>'
            + '<td><button type="button" class="md-btn md-btn-sm md-btn-primary md-fuzzy-merge-btn" data-to-id="' + r.item_id + '">Fusionner</button></td>'
            + '</tr>';
    }

    html += '</tbody></table></div></div>';
    $body.html(html);
}

function initFuzzySortHeaders() {
    $('.md-fuzzy-sort').unbind('click').click(function(e) {
        e.preventDefault();
        var col = $(this).attr('data-sort-col');
        if (window._fuzzySortCol === col) {
            window._fuzzySortDir = (window._fuzzySortDir === 'asc') ? 'desc' : 'asc';
        } else {
            window._fuzzySortCol = col;
            window._fuzzySortDir = (col === 'name') ? 'asc' : 'desc';
        }
        var $body = $('#md-modal-body-content');
        renderFuzzyResultsTable($body);
        initFuzzyBackButton();
        initFuzzyMergeButtons();
        initFuzzySortHeaders();
    });
}

function initFuzzyBackButton() {
    $('.md-fuzzy-back-btn').unbind('click').click(function(e) {
        e.preventDefault();
        var $body = $('#md-modal-body-content');
        var itemId = window.CURRENT_ITEM_ID;

        if (!itemId || itemId <= 0) {
            window.location.reload();
            return false;
        }

        $body.css('opacity', '0.5');

        $.ajax({
            url: BASE_URL + '/items/ajax_tab_article/' + itemId,
            type: 'GET',
            cache: false,
            success: function(html) {
                $body.html(html);
                $body.css('opacity', '1');
                // Restore first tab as active
                $('.md-tab').removeClass('md-tab-active');
                var $firstTab = $('.md-tab').eq(0);
                if ($firstTab.length) {
                    $firstTab.addClass('md-tab-active');
                }
                initModalHandlers();
            },
            error: function() {
                $body.css('opacity', '1');
                window.location.reload();
            }
        });

        return false;
    });
}

function initFuzzyMergeButtons() {
    $('.md-fuzzy-merge-btn').unbind('click').click(function(e) {
        e.preventDefault();
        var toId = $(this).attr('data-to-id');
        var $body = $('#md-modal-body-content');

        $body.css('opacity', '0.5');

        $.ajax({
            url: BASE_URL + '/items/ajax_fuzzy_merge_confirm/' + toId,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(resp) {
                $body.css('opacity', '1');

                if (!resp.success) {
                    alert(resp.error || 'Erreur');
                    return;
                }

                var f = resp.from;
                var t = resp.to;

                var html = '<div style="padding: 20px;">'
                    + '<div class="md-fuzzy-results-header">'
                    + '<h3 style="margin:0;font-size:15px;">Confirmer la fusion</h3>'
                    + '<button type="button" class="md-btn md-btn-sm md-btn-secondary md-fuzzy-back-btn">Annuler</button>'
                    + '</div>'
                    + '<p style="font-size:13px;color:#6b7280;margin-bottom:16px;">L\'article courant sera fusionn\u00e9 dans l\'article d\u00e9sactiv\u00e9 (qui sera r\u00e9activ\u00e9).</p>'
                    + '<div class="md-merge-comparison">';

                // FROM card
                html += '<div class="md-merge-card md-merge-card-from">'
                    + '<div class="md-merge-card-label">ORIGINE (sera d\u00e9sactiv\u00e9)</div>'
                    + '<div class="md-merge-field md-merge-field-full"><div class="md-merge-field-label">R\u00e9f\u00e9rence</div><div class="md-merge-field-value">' + f.item_number + '</div></div>'
                    + '<div class="md-merge-field md-merge-field-full"><div class="md-merge-field-label">Nom</div><div class="md-merge-field-value">' + f.name + '</div></div>'
                    + '<div class="md-merge-field md-merge-field-full"><div class="md-merge-field-label">Cat\u00e9gorie</div><div class="md-merge-field-value">' + (f.category || '-') + '</div></div>'
                    + '<div class="md-merge-fields-grid">'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">Quantit\u00e9</div><div class="md-merge-field-value">' + f.quantity + '</div></div>'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">CA HT</div><div class="md-merge-field-value">' + f.sales_ht + '</div></div>'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">Ventes Qt\u00e9</div><div class="md-merge-field-value">' + f.sales_qty + '</div></div>'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">DLUO</div><div class="md-merge-field-value">' + f.dluo_indicator + '</div></div>'
                    + '</div>'
                    + '</div>';

                // Arrow
                html += '<div class="md-merge-arrow">\u2192</div>';

                // TO card
                html += '<div class="md-merge-card md-merge-card-to">'
                    + '<div class="md-merge-card-label">DESTINATION (sera r\u00e9activ\u00e9)</div>'
                    + '<div class="md-merge-field md-merge-field-full"><div class="md-merge-field-label">R\u00e9f\u00e9rence</div><div class="md-merge-field-value">' + t.item_number + '</div></div>'
                    + '<div class="md-merge-field md-merge-field-full"><div class="md-merge-field-label">Nom</div><div class="md-merge-field-value">' + t.name + '</div></div>'
                    + '<div class="md-merge-field md-merge-field-full"><div class="md-merge-field-label">Cat\u00e9gorie</div><div class="md-merge-field-value">' + (t.category || '-') + '</div></div>'
                    + '<div class="md-merge-fields-grid">'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">Quantit\u00e9</div><div class="md-merge-field-value">' + t.quantity + ' \u2192 ' + (parseFloat(t.quantity) + parseFloat(f.quantity)) + '</div></div>'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">CA HT</div><div class="md-merge-field-value">' + t.sales_ht + ' \u2192 ' + (parseFloat(t.sales_ht) + parseFloat(f.sales_ht)).toFixed(2) + '</div></div>'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">Ventes Qt\u00e9</div><div class="md-merge-field-value">' + t.sales_qty + ' \u2192 ' + (parseFloat(t.sales_qty) + parseFloat(f.sales_qty)) + '</div></div>'
                    + '<div class="md-merge-field"><div class="md-merge-field-label">DLUO</div><div class="md-merge-field-value">' + t.dluo_indicator + '</div></div>'
                    + '</div>'
                    + '</div>';

                html += '</div>';

                // Confirm button
                html += '<div style="text-align:right;margin-top:20px;">'
                    + '<button type="button" class="md-btn md-btn-secondary md-fuzzy-back-btn" style="margin-right:8px;">Annuler</button>'
                    + '<button type="button" class="md-btn md-btn-primary" id="btn-fuzzy-merge-execute">Confirmer la fusion</button>'
                    + '</div></div>';

                $body.html(html);
                initFuzzyBackButton();
                initFuzzyMergeExecute();
            },
            error: function() {
                $body.css('opacity', '1');
                alert('Erreur de connexion');
            }
        });

        return false;
    });
}

function initFuzzyMergeExecute() {
    $('#btn-fuzzy-merge-execute').unbind('click').click(function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $body = $('#md-modal-body-content');

        $btn.css('opacity', '0.5');
        $btn.attr('disabled', 'disabled');

        $.ajax({
            url: BASE_URL + '/items/ajax_fuzzy_merge_execute',
            type: 'POST',
            dataType: 'json',
            cache: false,
            success: function(resp) {
                $btn.css('opacity', '1');

                if (resp.success) {
                    // Allow caller to handle merge completion (e.g. inventory count page)
                    if (typeof window.onMergeComplete === 'function' && resp.merged) {
                        window.onMergeComplete(resp);
                        return;
                    }
                    var html = '<div style="padding:40px;text-align:center;">'
                        + '<div style="font-size:48px;margin-bottom:16px;">\u2705</div>'
                        + '<h3 style="margin:0 0 8px 0;font-size:16px;">Fusion r\u00e9ussie</h3>'
                        + '<p style="font-size:13px;color:#6b7280;margin-bottom:20px;">' + resp.message + '</p>'
                        + '<a href="' + resp.redirect + '" class="md-btn md-btn-primary">Retour \u00e0 la liste</a>'
                        + '</div>';
                    $body.html(html);
                } else {
                    alert(resp.error || 'Erreur lors de la fusion');
                    $btn.removeAttr('disabled');
                }
            },
            error: function() {
                $btn.css('opacity', '1');
                $btn.removeAttr('disabled');
                alert('Erreur de connexion');
            }
        });

        return false;
    });
}

// ---- AJAX Toggle Active/Inactive ----
// Icône = action à effectuer (pas l'état actuel)
function initToggleActive() {
    $('#btn-toggle-active').unbind('click').click(function(e){
        e.preventDefault();
        var $btn = $(this);
        var ajaxUrl = $btn.attr('data-ajax-url');
        var currentDeleted = $btn.attr('data-deleted');

        $btn.css('opacity', '0.5');

        $.ajax({
            url: ajaxUrl,
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                $btn.css('opacity', '1');
                if (resp.success) {
                    var nowDeleted = (resp.deleted == 1);
                    $btn.attr('data-deleted', resp.deleted);

                    if (nowDeleted) {
                        // Article maintenant INACTIF: icône œil ouvert vert = cliquer pour activer
                        $btn.removeClass('md-tab-action-deactivate').addClass('md-tab-action-activate');
                        $btn.find('.icon-deactivate').hide();
                        $btn.find('.icon-activate').show();
                        $btn.find('.toggle-label-text').text('Activer');
                    } else {
                        // Article maintenant ACTIF: rafraîchir la page pour afficher les données à jour
                        window.location.reload();
                    }
                } else {
                    alert(resp.error || 'Erreur');
                }
            },
            error: function() {
                $btn.css('opacity', '1');
                alert('Erreur de connexion');
            }
        });

        return false;
    });
}

// Initialize on page load
$(document).ready(function(){
    initModalAjaxTabs();
    initToggleActive();
    initFuzzySearchMerge();
});
