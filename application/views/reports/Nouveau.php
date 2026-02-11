<?php 
?>

<?php include('../wrightetmathon/application/views/report/listing.php');
?>

<?php $this->load->view("partial/header_popup"); ?>


<div open class="fenetre modale" style="
        position: absolute;
    left: 50%;
    right: 50%;
    top: 5%;
    transform: translate(-50%,50%);
    width: 700px;
    z-index: 101;">

    <!-- Header fenetre -->
    <div class="fenetre-header">
	<span id="confirm_title" class="fenetre-title">
		<?php echo $this->lang->line('modules_'.$_SESSION['controller_name']).' Surveillance '.$this->lang->line('choix_nombre_de_mois'); ?>
    </span>
        <?php
        include('../wrightetmathon/application/views/partial/show_exit.php');
        ?>


    </div>



    <!-- Contenu de la fenetre-->
    <div class="fenetre-content">



        <div class="centrepage">


            <div class="blocformfond creationimmediate">
	<?php
		include('../wrightetmathon/application/views/partial/show_messages.php');
	?>
	
	<fieldset >
		<!-- show enter invoice -->
		<div>
			<?php 
				//echo form_open("sales/CN_select_invoice_item");
				//echo form_open("reports/tabular");
//				echo form_open("reports/dluo_future_date");
				
				?>
				<form action="<?php echo site_url("/reports/dluo_future_date")?>" method="post" accept-chaset="utf-8" >
					<table class="table_center">
						<thead>
							<tr>
								<th><?php 
								
								echo $this->lang->line('choix_nombre_de_mois');
								
								?></th>
							</tr>
						</thead>
						
						<tbody>
							<tr>									
								<td style='text-align:center'><?php 
																	echo form_input	(array	(
																		'name'=>'choix_mois',
																		'id'=>'choix_mois',
																		'value'=>'',
																		'style'=>'text-align:center;font-size:20pt',
																		'size'=>'10','autofocus'		=>	'autofocus',));
														
																	?>
																	
										
																
																</td>
							</tr>
							<tr>	

							</tr>
						</tbody>	
					</table>
<?php 
//$_SESSION['choix_mois']=$_POST['CN_original_invoice'];
$_SESSION['choix_mois']=$_POST['choix_mois'];
?>
        </div>
    </fieldset>
            </div>
                <div class="txt_milieu">
                    <?php
                    $target	=	'target="_self"';
                    echo anchor			(
                        'common_controller/common_exit/','<div class="btretour btlien">'.$this->lang->line('common_reset').'</div>',
                        $target
					);
					
					echo form_submit					(array	(
						'name'	=>	'submit',
						'id'	=>	'submit',
						'value'	=>	$this->lang->line('common_submit'), // 'Valider',
						'class'	=>	'btsubmit'
					));
 ?>

</div>
				<?php
				echo form_close();
			?>
		</div>
        </div>
        </div>
		
<script type="text/javascript" language="javascript">
$(document).ready(function()
{
	$("#show_spinner").click(function()
	{		
		$('#spinner_on_bar').show();
	});	
});
</script>
