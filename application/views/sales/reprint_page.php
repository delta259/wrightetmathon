<?php
// Lightweight reprint page: header + modal only (no receipt rendering)
// This avoids loading the heavy receipt.php view which requires many data variables
// and can cause client-side redirect issues.
// Note: Do NOT override $_SESSION['controller_name'] or $_SESSION['origin'] here —
// they are already set correctly by the calling context (reports controller etc.)
?>
<?php $this->load->view("partial/header"); ?>

<!-- Hide all page content, only the modal overlay will be visible -->
<style>
.body_cadre_gris, .body_page, .body_colonne,
.wlp-bighorn-book-content h2,
.pre_footer, #footer, .unified-footer { display: none !important; }
</style>

<?php $this->load->view("partial/pre_footer"); ?>
<?php $this->load->view("partial/footer"); ?>

<?php
// Render the reprint modal overlay (position:fixed, above everything)
include('../wrightetmathon/application/views/sales/reprint.php');
?>
