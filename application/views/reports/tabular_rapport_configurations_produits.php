<!-- -->
<!-- ..views/tabular*.php
<!-- set up the tabular style report
<!-- -->
<?php

	// get the number format -->
	$pieces	=	array();
	$pieces	= 	explode("/", $this->config->item('numberformat'));

	// ------------------------------------------------------------------
	// output to screen if required
	// ------------------------------------------------------------------

	// output the header
$this->load->view("partial/head");
	$this->load->view("partial/header_banner");
?>
<div id="wrapper" class="wlp-bighorn-book">
    <?php $this->load->view("partial/header_menu"); ?>
    <div class="wlp-bighorn-book">

        <div class="wlp-bighorn-book-content">

            <main id="login_page" class="wlp-bighorn-page-unconnect" role="main">

                <div class="body_page" >

                    <div class="body_colonne">
                        <h2 id="magasin"><?php echo $title; ?> </h2>

    <div> <?php
        include('../wrightetmathon/application/views/partial/show_messages.php');
        ?>
    </div>
	<!-- Output titles -->
<div id="title_bar">
<div class="span3" style ='margin="0px 0px"'>
        <input  value ="Imprimer" type="submit" style ="    float: right;" title="impression" class="btsubmit" OnClick="Printer.print(document.getElementById('sortable_table').innerHTML);"> </input>
    </div>
	<div id="page_title" class="float_left"></div> 
    <div id="page_subtitle" class="float_left"><h3><?php echo $subtitle; ?></h3></div>
	<?php
?>
</div>

	<!-- Output tables headers -->
	<div id="table_holder">
      <div style=" overflow-y:scroll; overflow-x:hidden;margin-top: 0px;    max-height: 600px;     min-height: 340px; width: 100%;border:#f5f5f5 1px solid;">
            <table style ="font-size: 16px;" width="100%" class="table table-striped table-bordered table-hover tablesorter" id="sortable_table">
		<!--<table class="tablesorter report" id="sortable_table">-->
			<thead>
				<tr>
					<?php foreach ($headers as $header)
							{ ?>
                                <th><i class="fa fa-sort"><?php echo $header; ?></i></th>
							<?php } ?>
				</tr>
			</thead>

			<tbody>
				<!-- -->
				<!-- Select each row of the data array -->
				<!-- -->
				<?php foreach ($data as $line=>$row)
				{
				// colour the line I just processed.
                ?>
					<tr >


					<?php foreach ($row as $cell)
						{ ?>
								<!-- -->
								<!-- Output each cell, format the number if numeric-->
								<!-- -->
								<?php
									if (is_numeric($cell))
									{
										//Si la valeur de la variable est un entier alors la variable est convertit en entier
										if(is_int($cell))
										{
											$cell = intval($cell);
										}
										//Si la valeur de la variable est un flotant alors la variable est convertit en flotant avec le format officiel
										if(is_float($cell))
										{
											$cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
											$cell = floatval($cell);
										}

										?>
										<td align="right">
										<?php
									}
									else
									{
										?>
										<td align="left">
										<?php
									}

									// make first cell button in order to position cursor to line selected
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
			</tbody>
		</table>
      </div>

			<script type="text/javascript">
					var Printer= new Object();
					Printer.print=function (HTML) {
							var win = window.open(location,null,null);
							win.blur();
							window.focus();
                            magasin=document.getElementById('magasin').innerHTML;
							title=document.getElementById('page_subtitle').innerHTML;
                            resume=document.getElementById('report_summary').innerHTML;
                            win.document.title="Aperçu de l'impression en cours";
                            win.document.write("<html><head><title>"+document.title+"</title></head><body><h2>"+magasin+"</h2><h3>"+title+"</h3><table border=2 width='100%' class='printtable' style='font-size:11.5px; border-collapse:collapse;'>" + HTML +"</table><br/><br/>"+resume+"</body></html>");
                            win.print();
							win.close();
					};
					window.top.Printer=Printer;
			</script>
	</div>
	<div id="report_summary">
	</div>
</div>

</div>
                </div></main></div></div></div>
</div>

<script>
    $(document).ready(function()
        {
            $("#sortable_table").tablesorter();
        }
    );
</script>

<?php
unset($_SESSION['oeil_desactivation']);
unset($_SESSION['reactivation']);
unset($_SESSION['tabular_articles_yes']);

// 	Output the footer
$this->load->view("partial/footer");
?>
