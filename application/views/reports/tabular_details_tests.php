<?php 

//OJB: Check if for excel export process
if($export_excel == 1){
	ob_start();
	$this->load->view("partial/header_excel");
}else{
    // output the header
    $this->load->view("partial/head");
    $this->load->view("partial/header_banner");
} 
?>

<div id="wrapper" class="wlp-bighorn-book">
    <?php $this->load->view("partial/header_menu"); ?>
    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">

                <div class="body_page" >

                    <div class="body_colonne">


                        <h2><?php echo $title; ?> </h2>

                        <div class="body_page">

<div id="page_title" style="margin-bottom:8px;">
<?php 

	if ($_SESSION['G']->modules[$_SESSION['module_id']]['show_exit_button'] == 1)
	{
		echo anchor	('common_controller/common_exit/',
					"<div class='btnew c_btcouleur' style='float:right;'><span>".$this->lang->line('common_return')."</span></div>"
					);
	}
?>
</div>



<div id="page_subtitle" style="margin-bottom:8px;"><h3><?php echo $subtitle ?></h3></div>

<div id="table_holder">
	<table class="tablesorter report table table-striped table-bordered" id="sortable_table">
		<thead>
			<tr>
				<?php foreach ($headers['details'] as $header) { ?>
				<th><?php echo $header; ?></th>
				<?php } ?>
			
			</tr>
		</thead>
		<tbody>
			<!-- -->
			<!-- get the number format -->
			<!-- -->
			<?php 
			$pieces =array();
			$pieces = explode("/", $this->config->item('numberformat'));
			?>
			
			
			<tr>
						<?php foreach ($details_data_info as $row) { ?>
						
							<tr>
								<?php foreach ($row as $cell)
								{
								?>
									<!-- -->
									<!-- Output each cell, format the number if numeric-->
									<!-- -->
									<?php 
										if (is_numeric($cell))
										{
											$cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);

											if($cell==number_format($row[3], $pieces[0], $pieces[1], $pieces[2]) || ($cell==number_format($row[4], $pieces[0], $pieces[1], $pieces[2])))
											{
												//$cell=floatval($cell);
												$cell=round(($cell),0);
											}
											?>
											<td align="right">
											<?php
										}
										else
										{
											?>
											<td align="center">
											<?php
										}
									echo $cell;
									?>
								</td>
								<!-- -->
								<!-- Get next cell -->
								<!-- -->
								<?php } ?>
							</tr>
							<!-- -->
							<!-- Get next row -->
							<!-- -->
						<?php } ?>
				</td>
			</tr>

		</tbody>
	</table>
</div>

<!-- -->
<!-- show the totals -->
<!-- -->

<div id="report_summary">

	<?php 
	foreach($overall_summary_data as $name=>$value) 
		{
		?>
			<div class="summary_row"><?php echo $this->lang->line('reports_'.$name). '    '.$value; ?></div>
		<?php 
		}
		?>
</div>
<?php 
if($export_excel == 1){
	$this->load->view("partial/footer_excel");
	$content = ob_end_flush();
	
	$filename = trim($filename);
	$filename = str_replace(array(' ', '/', '\\'), '', $title);
	$filename .= "_Export.xls";
	header('Content-type: application/ms-excel');
	header('Content-Disposition: attachment; filename='.$filename);
	echo $content;
	die();
	
}else{
    $this->load->view("partial/pre_footer");
	$this->load->view("partial/footer"); 
?>
<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$(".tablesorter a.expand").click(function(event)
	{
		$(event.target).parent().parent().next().find('.innertable').toggle();
		
		if ($(event.target).text() == '+')
		{
			$(event.target).text('-');
		}
		else
		{
			$(event.target).text('+');
		}
		return false;
	});
	
});
</script>
<?php 
} // end if not is excel export 

?>
