<?php $this->load->view("partial/header_popup"); ?>
<dialog open class="fenetre modale cadre" style=" width: 1130px;">
    <div class="fenetre-header">
	<span id="page_title" class="fenetre-title">
        Recherche Client
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
    <fieldset >
    <table class="table table-bordered" >
        <thead>
            <?php    
            foreach($headers as $head => $heads)
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
        foreach($data_list_customers as $key => $line)
        {
            $_SESSION['line_count'] += 1;
            $this->Common_routines->set_line_colour();
            echo '<tr style="background-color:'.$_SESSION['line_colour'].'">';
            foreach($line as $key_2 => $line_2)
            {
                echo '<td align=center >'.$line_2.'</td>';
            }
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
    </fieldset >
    
        </div>
</div></dialog>