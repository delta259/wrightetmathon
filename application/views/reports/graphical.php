

<!-- -->
<!-- Dispay the configuration screen -->
<!-- -->

<!-- output the header -->
<?php $this->load->view("partial/head"); ?>
<?php $this->load->view("partial/header_banner"); ?>

<div id="wrapper" class="wlp-bighorn-book">

    <?php $this->load->view("partial/header_menu"); ?>

    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">


                <!--Contenu background gris-->
                <div class="body_page" id="loginPage">
                    <div>

                        <div class="body_colonne">
                            <h2><?php echo $title; ?></h2>
<!-- Output the titles -->
<!-- -->
	<div id="page_title" style="margin-bottom:8px;"><?php include('../wrightetmathon/application/views/partial/show_buttons.php'); ?></div>
	<div id="page_subtitle" style="margin-bottom:8px;"><h3><?php echo $subtitle ?></h3></div>

<div style="text-align: center;">

<!-- -->
<!-- Write the chart - here we can use the base-url because read only -->
<!-- -->
<div id="chartContainer1">Chart Should load here. If you see this there is a problem.</div>
<div style="text-align: left; font-size: 16px;">
<script type="text/javascript">
FusionCharts.setCurrentRenderer('javascript'); 
var myChart =
new FusionCharts
("<?php echo base_url();?><?php echo $report_type;?>",	// chart type
"myChartId", 											// Chart ID - must be unique if multiple charts on same page
"1200",													// Width either is pixels or in %
"575", 													// Height either is pixels or in %
"0", 													// debug mode 0 = no debug, 1 = debug
"1" ); 													// must always be 1
myChart.setJSONUrl("<?php echo base_url();?>FirstChart/data.json");
myChart.render("chartContainer1");
</script>

<?php
?>


<div id="report_summary">
<?php foreach($summary_data as $name=>$value) { ?>
	<div class="summary_row"><?php echo $this->lang->line('reports_'.$name). ': '.to_currency($value); ?></div>
<?php }?>
</div>
</div>
<?php
$this->load->view("partial/pre_footer");
$this->load->view("partial/footer"); 
?>
