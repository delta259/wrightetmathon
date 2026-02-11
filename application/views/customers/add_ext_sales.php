<?php $this->load->view("partial/header_popup"); ?>
<dialog open class="fenetre modale cadre" style=" width: 1130px;">
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
        Recherche vente(s) Client
	</span>
        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>
    </div>
    <div class="fenetre-content">
        <div class="centrepage">
            <div class="blocformfond creationimmediate">
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
		<!--
    <table class="table_center table table-bordered" >
        <thead>
            <?php /*
            foreach($summary as $head => $heads)
            {
            ?>
            <th>
                <?php
                echo $heads;
                ?>
            </th>
            <?php
            }
            ?>
        </thead>

        <tbody>

        <?php
        $_SESSION['line_count'] = 1;
        foreach($details as $key => $line)
        {
            $_SESSION['line_count'] += 1;
            $this->Common_routines->set_line_colour();
            echo '<tr style="background-color:'.$_SESSION['line_colour'].'">';
            foreach($line as $key_2 => $line_2)
            {
                echo '<td align=center >'.$line_2.'</td>';
            }
            echo '</tr>';
        }//*/
        ?>
        </tbody>
    </table><!-- -->
    
        </div>
</div>




<fieldset >
    
	<div>
<div>

<!-- show the customer history -->

<div id="table_holder">
		<table class="tablesorter report table table-striped table-bordered " id="sortable_table">
			<thead>
				<tr>
					<th>+</th>
					<?php foreach ($_SESSION['CSI']['HH']['summary'] as $header) { ?>
					<th><?php echo $header; ?></th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$pieces =array();
				$pieces = explode("/", $this->config->item('numberformat'));
				?>

				<?php foreach ($_SESSION['CSI']['HS'] as $key=>$row) { ?>
				<tr>
					<td><a href="#" class="expand">+</a></td>
					<?php foreach ($row as $cell)
					{
					?>
									<?php
									if (is_numeric($cell))
									{
										$cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
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
									echo $cell;
									?>
								</td>
					<?php } ?>
				</tr>
				<tr>
					<td colspan="15">
					<table class="innertable tablesorter report table table-striped table-bordered " style="width: 90%;">
						<thead>
							<tr>
								<?php foreach ($_SESSION['CSI']['HH']['details'] as $header) { ?>
								<th><?php echo $header; ?></th>
								<?php } ?>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($_SESSION['CSI']['HD'][$key] as $row2) { ?>

								<tr>
									<?php foreach ($row2 as $cell)
									{
									?>
										<?php
											if (is_numeric($cell))
											{
												$cell = number_format($cell, $pieces[0], $pieces[1], $pieces[2]);
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
										echo $cell;
										?>
									</td>
									<?php } ?>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
    </fieldset >
    
        </div>
</div></dialog>