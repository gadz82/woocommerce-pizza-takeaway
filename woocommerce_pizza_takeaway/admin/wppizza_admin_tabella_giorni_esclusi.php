<table class="wp-list-table widefat fixed posts" id="table_giorni_esclusi" style="margin:12px 0">
	<thead>
		<th><?php _e('Data ora', $td) ?></th>
		<th style="text-align:center"><?php _e('Elimina', $td) ?></th>
	</thead>
	<tbody>
		<?php 
		
		foreach($giorni as $giorno):
			
		?>
		
		<tr class="e_row_<?php echo $giorno; ?>">
			<td><?php echo $giorno; ?></td>
			
			<td style="text-align:center"><input id="eliminagiornoescluso_<?php echo $giorno; ?>" type="button" value="<?php _e('X', $td); ?>" class="remove_row button" /></td>			
		</tr>
			
		<?php 
		
		endforeach;
		
		?>
	</tbody>
</table>
