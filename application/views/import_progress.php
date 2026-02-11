<?php $this->load->view("partial/head"); ?>
<body>
<link rel="stylesheet" rev="stylesheet" href="<?php echo base_url();?>css/login.css"/>

<?php
if($this->config->item('custom1_name')=='Y') {
    $lien = 'www.yesstore.fr';
    $url_image = 'images_yes';
} else {
    $lien = 'www.sonrisa-smile.com';
    $url_image = 'images_sonrisa';
}
?>

<div id="container">
    <div id="top">
        <a href="http://<?php echo $lien; ?>" target="_blank" title="Acc&eacute;der au site web" style="text-decoration:none;">
            <img src="<?php echo base_url() . $url_image; ?>/logo.png" alt="Logo" style="max-width:180px;height:auto;margin-bottom:8px;">
        </a>
        <div style="font-size:13px;opacity:0.8;font-weight:400;">
            <?php echo $this->config->item('company'); ?>
        </div>
    </div>

    <div id="login_form">
        <div class="import-progress-title">Mise &agrave; jour en cours</div>

        <!-- Steps list -->
        <div class="import-steps">
            <div class="import-step" id="step-backup">
                <span class="import-step-icon">&#9675;</span>
                <span class="import-step-text">Sauvegarde de la base de donn&eacute;es</span>
            </div>
            <div class="import-step" id="step-import_articles">
                <span class="import-step-icon">&#9675;</span>
                <span class="import-step-text">Import BASE_ARTICLE</span>
            </div>
            <div class="import-step" id="step-import_kits">
                <span class="import-step-icon">&#9675;</span>
                <span class="import-step-text">Import BASE_KIT</span>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="import-progress-bar-container">
            <div class="import-progress-bar" id="progressBar" style="width:0%"></div>
        </div>
        <div class="import-progress-percent" id="progressPercent">0%</div>
        <div class="import-progress-detail" id="progressDetail">D&eacute;marrage...</div>

        <!-- Error state (hidden by default) -->
        <div id="importError" style="display:none;">
            <div class="import-error-message" id="errorMessage"></div>
            <div id="submit_button" style="margin-top:16px;">
                <button onclick="window.location='<?php echo site_url('login/rolling'); ?>'" class="btsubmit">
                    Continuer vers le tableau de bord
                </button>
            </div>
        </div>
    </div>

    <div class="version_info">
        <?php echo $this->lang->line('login_version') . ' ' . $this->config->item('application_version'); ?>
    </div>
</div>

<script type="text/javascript">
var PROGRESS_BASE = '<?php echo rtrim(site_url(), "/"); ?>/';
var progressId = '<?php echo isset($_SESSION["import_progress_id"]) ? $_SESSION["import_progress_id"] : ""; ?>';
var importItems = <?php echo !empty($_SESSION['pending_import_items']) ? '1' : '0'; ?>;
var importKits = <?php echo !empty($_SESSION['pending_import_kits']) ? '1' : '0'; ?>;
var flashInfoShow = <?php echo (isset($_SESSION['flash_info_show']) && $_SESSION['flash_info_show'] == 1) ? '1' : '0'; ?>;
var pollTimer = null;
var importStarted = false;
var importDone = false;
var stepOrder = ['backup', 'import_articles', 'import_kits', 'done'];

function startImport() {
    if (importStarted) return;
    importStarted = true;

    $.ajax({
        url: PROGRESS_BASE + 'updates/run_import',
        type: 'POST',
        data: {
            progress_id: progressId,
            import_items: importItems,
            import_kits: importKits
        },
        dataType: 'json',
        success: function(data) {
            importDone = true;
            if (data && data.status == 'done') {
                finishImport();
            } else if (data && data.status == 'error') {
                showError(data.message || 'Erreur inconnue');
            }
        },
        error: function(xhr, status, err) {
            importDone = true;
            var errorMsg = 'Erreur: ' + (err || status);
            if (xhr && xhr.responseText) {
                errorMsg += ' - ' + xhr.responseText.substring(0, 200);
            }
            showError(errorMsg);
        }
    });
}

function pollProgress() {
    $.ajax({
        url: PROGRESS_BASE + 'updates/get_progress?id=' + progressId,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data) {
                updateUI(data);
            }
            if (!importDone) {
                pollTimer = setTimeout(pollProgress, 1000);
            }
        },
        error: function() {
            if (!importDone) {
                pollTimer = setTimeout(pollProgress, 2000);
            }
        }
    });
}

function updateUI(data) {
    var pct = data.percent || 0;
    var step = data.step || 'waiting';

    // find step index
    var stepIdx = -1;
    for (var i = 0; i < stepOrder.length; i++) {
        if (stepOrder[i] == step) { stepIdx = i; break; }
    }

    // calculate overall percent
    var overallPct = 0;
    if (step == 'done') {
        overallPct = 100;
    } else if (step == 'error') {
        showError(data.message || 'Erreur inconnue');
        return;
    } else if (step == 'init' || step == 'waiting') {
        overallPct = 0;
    } else if (step == 'backup') {
        overallPct = Math.round(pct * 0.05);
    } else if (step == 'import_articles') {
        overallPct = 5 + Math.round(pct * 0.80);
    } else if (step == 'import_kits') {
        overallPct = 85 + Math.round(pct * 0.15);
    }

    document.getElementById('progressBar').style.width = overallPct + '%';
    document.getElementById('progressPercent').innerHTML = overallPct + '%';
    document.getElementById('progressDetail').innerHTML = data.message || '';

    // update step icons
    for (var i = 0; i < stepOrder.length - 1; i++) {
        var stepEl = document.getElementById('step-' + stepOrder[i]);
        if (!stepEl) continue;
        var icon = stepEl.getElementsByTagName('span')[0];
        if (i < stepIdx) {
            icon.innerHTML = '&#10003;';
            stepEl.className = 'import-step import-step-done';
        } else if (i == stepIdx) {
            icon.innerHTML = '&#8987;';
            stepEl.className = 'import-step import-step-active';
        } else {
            icon.innerHTML = '&#9675;';
            stepEl.className = 'import-step';
        }
    }
}

function finishImport() {
    // update to 100%
    document.getElementById('progressBar').style.width = '100%';
    document.getElementById('progressPercent').innerHTML = '100%';
    document.getElementById('progressDetail').innerHTML = 'Import termin\u00e9 !';

    // mark all steps done
    for (var i = 0; i < stepOrder.length - 1; i++) {
        var stepEl = document.getElementById('step-' + stepOrder[i]);
        if (!stepEl) continue;
        var icon = stepEl.getElementsByTagName('span')[0];
        icon.innerHTML = '&#10003;';
        stepEl.className = 'import-step import-step-done';
    }

    // redirect after short delay
    setTimeout(function() {
        if (flashInfoShow == 1) {
            window.location = PROGRESS_BASE + 'login/show_flash_info';
        } else {
            window.location = PROGRESS_BASE + 'login/rolling';
        }
    }, 1500);
}

function showError(msg) {
    if (pollTimer) { clearTimeout(pollTimer); pollTimer = null; }
    document.getElementById('progressDetail').innerHTML = 'Erreur';
    document.getElementById('errorMessage').innerHTML = msg;
    document.getElementById('importError').style.display = 'block';
}

// start after page load
$(document).ready(function() {
    setTimeout(function() {
        pollProgress();
        startImport();
    }, 500);
});
</script>

</body>
</html>
