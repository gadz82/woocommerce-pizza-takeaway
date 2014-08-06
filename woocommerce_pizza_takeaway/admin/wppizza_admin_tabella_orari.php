<table class="wp-list-table widefat fixed posts" id="table_orari" style="margin:12px 0">
	<thead>
		<th><?php _e('Orario', $td) ?></th>
		<th style="text-align:center"><?php _e('Aperto', $td) ?></th>
		<th style="text-align:center"><?php _e('Chiuso', $td) ?></th>
		<th style="text-align:center"><?php _e('Nr.Pizze', $td) ?></th>
		<th style="text-align:center"><?php _e('Nr.Consegne', $td) ?></th>
		<th style="text-align:center"><?php _e('Elimina', $td) ?></th>
	</thead>
	<tbody>
		<?php 
		
		foreach($orari as $orario => $aperto):
			
			$flag_aperto = $aperto == '1' ? true : false;
			$flag_chiuso = $aperto == '0' ? true : false;
		?>
		
		<tr class="row_<?php echo $orario; ?>">
			<td><?php echo $orario; ?></td>
			
			<td style="text-align:center"><input id="orarioaperto_<?php echo $orario; ?>" type="checkbox" name="aperto[<?php echo $orario; ?>]" <?php if($flag_aperto) echo 'checked' ?>></td>
			
			<td style="text-align:center"><input id="orariochiuso_<?php echo $orario; ?>" type="checkbox" name="chiuso[<?php echo $orario; ?>]" <?php if($flag_chiuso) echo 'checked' ?> ></td>
			
			<td style="text-align:center"><input id="dispopizze_<?php echo $orario; ?>" type="number" name="dispopizze[<?php echo $orario; ?>]" style="width:80px"></td>
			
			<td style="text-align:center"><input id="dispoconsegne_<?php echo $orario; ?>" type="number" name="dispoconsegne[<?php echo $orario; ?>]" style="width:80px"></td>
			
			<td style="text-align:center"><input id="eliminaorario_<?php echo $orario; ?>" type="button" value="<?php _e('Elimina', $td); ?>" class="remove_row button" /></td>			
		</tr>
			
		<?php 
		
		endforeach;
		
		?>
	</tbody>
</table>
