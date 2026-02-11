<?php
// Check if loaded in modal mode
$is_modal_mode = isset($_GET['modal']) && $_GET['modal'] == '1';
?>

<?php if ($is_modal_mode): ?>
<!-- Modal Mode: Close wrapper only -->
</div><!-- /.modal-page-wrapper -->
</body>
</html>
<?php else: ?>
<!-- Normal Mode: Full footer -->
<footer class="unified-footer" role="contentinfo">
    <div class="footer-content">
        <span class="footer-version"><?php echo $this->config->item('application_version'); ?></span>
        <span class="footer-copyright">&copy; POS W &amp; M. <?php echo date('Y'); ?></span>
        <span class="footer-date"><?php echo date('d/m/Y'); ?></span>
    </div>
</footer>

</body>
</html>
<?php endif; ?>
