<div class="box_ingredienti">
	<div class="label_ingredienti">
		<div id="label_ingredienti_base" class="label_selected"><?php _e('Rimuovi Ingredienti', $td); ?></div>
		<div id="label_ingredienti_extra"><?php _e('Aggiungi Extra', $td); ?></div>
	</div>
	<?php if(!empty($iextra)): ?>
	<div id="container_iextra" class="container_ingredienti">
		<ul>
			<?php

			global $product;
			
			$nr = count($iextra);
						
			for($i = 0; $i < $nr; $i++):
				$prezzo = str_replace(',', '.', $iextra[$i]->prezzo);
				$checked_extra = isset($_SESSION['wppizza'][$product->id]['extra'][$iextra[$i]->term_id]) ? 'checked' : '';
			?>
			<li>
				<input type="hidden" name="extraprice_<?php echo $iextra[$i]->term_id; ?>" id="extraprice_<?php echo $iextra[$i]->term_id; ?>" value="<?php echo $prezzo; ?>" />
				<input type="checkbox" name="iextra_<?php echo $iextra[$i]->term_id; ?>" value="1" id="iextra_<?php echo $iextra[$i]->term_id; ?>" <?php echo $checked_extra; ?>/>
				<label for="iextra_<?php echo $iextra[$i]->term_id; ?>">
					<?php echo $iextra[$i]->name; ?> (+ <?php echo $prezzo; ?>&euro;)
				</label>
			</li>
			<?php endfor; ?>
		</ul>
	</div>
	<?php endif; ?>
	<div id="container_ibase" class="container_ingredienti">
		<ul>
			<?php 
			$nr = count($ibase);
			
			for($i = 0; $i < $nr; $i++):
				if(isset($_SESSION['wppizza'][$product->id]['ingredienti_excl'])){
					$checked_base = !in_array($ibase[$i], $_SESSION['wppizza'][$product->id]['ingredienti_excl']) ? 'checked' : '';
				} else {
					$checked_base = 'checked';
				}
				
			?>
			<li>
				<input type="checkbox" name="ibase_<?php echo $ibase[$i]; ?>" value="1" id="ibase_<?php echo $ibase[$i]; ?>" <?php echo $checked_base; ?> />
				<label for="ibase_<?php echo $ibase[$i]; ?>">
					<?php echo $ibase[$i]; ?>
				</label>
			</li>
			<?php endfor; ?>
		</ul>
	</div>
	<?php global $product; ?>
	<input type="hidden" name="wppizza_pid" id="wppizza_pid" value="<?php echo $product->id; ?>" /> 
</div>