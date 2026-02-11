<!-- show item details -->

<table class="tablesorter report table table-striped table-bordered " border=2>

		<!-- Output Item number -->
		<thead >
			<th>
				<?php echo $this->lang->line('items_item_number'); ?>
			</th>
            <th>
                <?php echo $this->lang->line('items_name'); ?>
            </th>
            <th>
                <?php echo $this->lang->line('items_category'); ?>
            </th>
            <th>
                <?php echo $this->lang->line('items_current_quantity'); ?>
            </th>

		</thead>

		<!-- Output Item description -->
		<tbody>
        <tr>
            <td  style="text-align: center;">
                <?php echo $_SESSION['transaction_info']->item_number; ?>
            </td>
			<td>
				<?php echo $_SESSION['transaction_info']->name; ?>
			</td>

		<!-- Output Item category -->

			<td  style="text-align: center;">
				<?php echo $_SESSION['transaction_info']->category; ?>
			</td>


		<!-- Output current qty on file -->


			<td  style="text-align: right;">
				<?php echo $_SESSION['transaction_info']->quantity; ?>
			</td>
        </tr>
		</tbody>
</table>
