<?php $this->load->view("partial/header"); ?>
<link rel="stylesheet" href="<?php echo base_url(); ?>css/admin.css">

<div class="admin-container">
    <!-- Header -->
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
            <?php echo $this->lang->line('modules_admin_sys'); ?>
        </h1>
    </div>

    <!-- Configuration Generale -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2 class="admin-section-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                Configuration Generale
            </h2>
        </div>
        <div class="admin-cards-grid">
            <?php
            $config_modules = array(
                array('countries', 'lg', 'blue', 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM2 12h20M12 2c2.21 3.17 3.5 6.39 3.5 10s-1.29 6.83-3.5 10c-2.21-3.17-3.5-6.39-3.5-10s1.29-6.83 3.5-10'),
                array('timezones', 'md', 'cyan', 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 0v10l4 2'),
                array('trackers', 'lg', 'red', 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 6v4m0 4h.01'),
                array('imports', 'lg', 'green', 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m14-7l-5-5-5 5m5-5v12'),
            );
            foreach ($config_modules as $m): ?>
            <div class="admin-card" data-module="<?php echo $m[0]; ?>" data-size="<?php echo $m[1]; ?>" data-title="<?php echo $this->lang->line('modules_'.$m[0]); ?>" data-color="<?php echo $m[2]; ?>">
                <div class="admin-card-icon <?php echo $m[2]; ?>"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="<?php echo $m[3]; ?>"/></svg></div>
                <div class="admin-card-content">
                    <div class="admin-card-title"><?php echo $this->lang->line('modules_'.$m[0]); ?></div>
                    <div class="admin-card-desc"><?php echo $this->lang->line('modules_'.$m[0].'_desc'); ?></div>
                </div>
                <div class="admin-card-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Finances & Tarification -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2 class="admin-section-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Finances & Tarification
            </h2>
        </div>
        <div class="admin-cards-grid">
            <?php
            $finance_modules = array(
                array('currencies', 'md', 'yellow', 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 6v8m-4-4h8'),
                array('currency_definitions', 'lg', 'orange', 'M2 7h20v14H2V7zm0 0V5a2 2 0 012-2h4a2 2 0 012 2v2'),
                array('paymethods', 'lg', 'purple', 'M1 4h22v16H1V4zm0 6h22'),
                array('pricelists', 'lg', 'green', 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'),
            );
            foreach ($finance_modules as $m): ?>
            <div class="admin-card" data-module="<?php echo $m[0]; ?>" data-size="<?php echo $m[1]; ?>" data-title="<?php echo $this->lang->line('modules_'.$m[0]); ?>" data-color="<?php echo $m[2]; ?>">
                <div class="admin-card-icon <?php echo $m[2]; ?>"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="<?php echo $m[3]; ?>"/></svg></div>
                <div class="admin-card-content">
                    <div class="admin-card-title"><?php echo $this->lang->line('modules_'.$m[0]); ?></div>
                    <div class="admin-card-desc"><?php echo $this->lang->line('modules_'.$m[0].'_desc'); ?></div>
                </div>
                <div class="admin-card-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Organisation -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2 class="admin-section-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                Organisation
            </h2>
        </div>
        <div class="admin-cards-grid">
            <?php
            $org_modules = array(
                array('branches', 'lg', 'blue', 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9zm6 13V12h6v10'),
                array('modules', 'xl', 'purple', 'M3 3h7v7H3V3zm11 0h7v7h-7V3zm0 11h7v7h-7v-7zm-11 0h7v7H3v-7z'),
                array('customer_profiles', 'md', 'pink', 'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m8-10a4 4 0 100-8 4 4 0 000 8'),
                array('targets', 'lg', 'orange', 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4a6 6 0 110 12 6 6 0 010-12zm0 2a2 2 0 100 4 2 2 0 000-4'),
            );
            foreach ($org_modules as $m): ?>
            <div class="admin-card" data-module="<?php echo $m[0]; ?>" data-size="<?php echo $m[1]; ?>" data-title="<?php echo $this->lang->line('modules_'.$m[0]); ?>" data-color="<?php echo $m[2]; ?>">
                <div class="admin-card-icon <?php echo $m[2]; ?>"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="<?php echo $m[3]; ?>"/></svg></div>
                <div class="admin-card-content">
                    <div class="admin-card-title"><?php echo $this->lang->line('modules_'.$m[0]); ?></div>
                    <div class="admin-card-desc"><?php echo $this->lang->line('modules_'.$m[0].'_desc'); ?></div>
                </div>
                <div class="admin-card-arrow"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Maintenance -->
    <div class="admin-section">
        <div class="admin-section-header">
            <h2 class="admin-section-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                <?php echo $this->lang->line('modules_maintenance_items'); ?>
            </h2>
        </div>
        <div class="admin-maintenance-card">
            <div class="admin-maintenance-header">
                <div class="admin-maintenance-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></div>
                <div class="admin-maintenance-title"><?php echo $this->lang->line('modules_deactivate_inactive_items'); ?></div>
            </div>
            <div class="admin-maintenance-desc"><?php echo $this->lang->line('modules_deactivate_items_desc'); ?></div>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="admin-alert admin-alert-success">
                    <div class="admin-alert-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                    <div class="admin-alert-content"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                </div>
            <?php elseif (isset($_SESSION['deactivate_items_count'])): ?>
                <div class="admin-alert admin-alert-warning">
                    <div class="admin-alert-icon"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
                    <div class="admin-alert-content">
                        <div class="admin-alert-title">Confirmation requise</div>
                        <?php echo sprintf($this->lang->line('modules_deactivate_items_confirm'), $_SESSION['deactivate_items_count']); ?>
                        <div class="admin-alert-actions">
                            <form method="post" action="<?php echo site_url('home/deactivate_inactive_items'); ?>" style="display:inline;">
                                <input type="hidden" name="confirm_deactivate" value="yes">
                                <button type="submit" class="admin-btn admin-btn-danger"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><?php echo $this->lang->line('common_yes'); ?></button>
                            </form>
                            <a href="<?php echo site_url('home/admin'); ?>" class="admin-btn admin-btn-secondary"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><?php echo $this->lang->line('common_no'); ?></a>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['deactivate_items_count']); ?>
            <?php else: ?>
                <form method="post" action="<?php echo site_url('home/deactivate_inactive_items'); ?>">
                    <button type="submit" class="admin-btn admin-btn-primary"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg><?php echo $this->lang->line('modules_deactivate_inactive_items'); ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="moduleModal">
    <div class="modal-container size-lg" id="modalContainer">
        <button class="modal-close-float" onclick="closeModal()" title="Fermer"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        <div class="modal-body" id="modalBody">
            <div class="modal-loading" id="modalLoading">Chargement...</div>
            <iframe id="modalIframe" style="display:none;"></iframe>
        </div>
    </div>
</div>

<script>
(function() {
    var baseUrl = '<?php echo rtrim(site_url(), "/"); ?>';
    var modal = document.getElementById('moduleModal');
    var container = document.getElementById('modalContainer');
    var iframe = document.getElementById('modalIframe');
    var loading = document.getElementById('modalLoading');

    function adjustIframeHeight() {
        try {
            var doc = iframe.contentWindow.document;
            var body = doc.body;
            var html = doc.documentElement;
            var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
            var maxHeight = window.innerHeight - 80;
            iframe.style.height = Math.min(height + 20, maxHeight) + 'px';
        } catch(e) {
            iframe.style.height = '400px';
        }
    }

    function openModal(module, size) {
        container.className = 'modal-container size-' + size;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(function() { modal.classList.add('visible'); }, 10);
        loading.style.display = 'flex';
        iframe.style.display = 'none';
        iframe.style.height = 'auto';
        var theme = document.documentElement.getAttribute('data-theme') || 'light';
        iframe.src = baseUrl + '/' + module + '?modal=1&theme=' + theme;
        iframe.onload = function() {
            loading.style.display = 'none';
            iframe.style.display = 'block';
            try { iframe.contentWindow.document.documentElement.setAttribute('data-theme', theme); } catch(e) {}
            adjustIframeHeight();
            // Re-adjust after a short delay for dynamic content
            setTimeout(adjustIframeHeight, 100);
            setTimeout(adjustIframeHeight, 300);
        };
    }

    window.closeModal = function() {
        modal.classList.remove('visible');
        setTimeout(function() { modal.classList.remove('active'); document.body.style.overflow = ''; iframe.src = 'about:blank'; }, 300);
    };

    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && modal.classList.contains('active')) closeModal(); });
    document.querySelectorAll('.admin-card[data-module]').forEach(function(card) {
        card.addEventListener('click', function() { openModal(this.dataset.module, this.dataset.size || 'lg'); });
    });
})();
</script>

<?php $this->load->view("partial/footer"); ?>
