<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="      position: absolute;
   left: 50%;
    right: 50%;
    top: 3%;
    transform: translate(-50%,50%);
    width: 1000px;
    z-index: 101;">

    <!--HEADER-->
    <div class="fenetre-header">
  <span id="page_title" class="fenetre-title">
		<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).'  '.$_SESSION['$title']; ?>
  </span>

        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>


    <!---CONTENT-->
    <div class="fenetre-content">



        <div class="centrepage">
            <div class="blocformfond creationimmediate">

	
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
    <fieldset class="fieldset">
		<table class="tablesorter report table table-striped table-bordered " width="100%">
			<thead>
							<th><?php echo form_label($this->lang->line('branches_branch_description')); ?></th>
							<th><?php echo form_label($this->lang->line('items_quantity')); ?></th>
							<th><?php echo form_label($this->lang->line('common_comments')); ?></th>
            </thead>
            <tbody>
						<?php
						foreach($_SESSION['remote_quantities'] as $key => $branch)
						{
							?>
							<tr>
								<td><?php echo $key;?></td>
								<td align="right"><?php echo $branch['qty'];?></td>
								<td><?php echo $branch['des'];?></td>
							</tr>
							<?php
							}
							?>


			</tbody>
		</table>
		</fieldset>
		
	<?php
	?>
            </div></div></div></div>