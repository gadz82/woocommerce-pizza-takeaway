
<div class="wrap nosubsub">
<h2><?php _e('Pizza Manager', $td); ?></h2>
<div id="ajax-response"></div>
<br class="clear">
<div id="col-container">
	<div id="col-right">
		<div class="col-wrap">
			<div>
				<form id="nuovo_orario">
					<label for="nuovo_orario"><strong><?php _e('Inserisci un nuovo orario:',$td); ?></strong> </label>
					<input type="text" name="nuovo_orario" id="wp_pizza_nuovo_orario" />
					<input type="hidden" name="wppizza_action" value="wppizza_add_orario" />
					<input type="button" id="button_add_orario" value="<?php _e('Aggiungi', $td); ?>" class="button action" />
				</form>
			
			</div>
			<form id="tabella_orari">
				<div>
					<?php do_action('wppizza_render_table_orari', $orari); ?>
				</div>
				<p class="submit">
				<input type="hidden" name="wppizza_action" value="wppizza_salva_orario" />
				<input type="button" class="button button-primary button-hero" id="wppizza_salva_orari" value="<?php _e('Salva Orari', $td); ?>"/>
				</p>
			</form>
		</div>
		<hr />
		<br />
		<div class="col-wrap">
			<div>
				<form id="escludi_orario">
					<label for="escludi_orario"><strong><?php _e('Escludi un orario:',$td); ?></strong> </label>
					<input type="text" name="escludi_orario" id="wp_pizza_escludi_orario" />
					<input type="hidden" name="wppizza_action" value="wppizza_escludi_orario" />
					<input type="button" id="button_escludi_orario" value="<?php _e('Escludi', $td); ?>" class="button action" />
				</form>
			</div>
			<form id="lista_giorni_esclusi">
				<div>
					<?php do_action('wppizza_render_giorni_esclusi', $giorni_esclusi); ?>
				</div>
			</form>
		</div>
	</div>
	<div id="col-left">
		<div class="col-wrap">
			<div class="form-wrap">
				<h3><?php _e('Gestione forza lavoro', $td); ?></h3>
				<p><?php _e('Imposta il numero di pizze che possono essere prodotte in un intervallo di tempo e quanti ragazzi si hanno a disposizione per le consegne.'); ?></p>
				<form id="gestione_pizzeria">
					<table>
						<tr>
							<td width="75%"><label for="produzione_slot"><strong><?php _e('Numero pizze per intervallo',$td); ?></strong></label></td> 
							<td><input type="number" name="produzione_slot" id="produzione_slot" style="width:50%" value="<?php echo $dispo['produzione_slot']; ?>" /></td>
						</tr>
						<tr>
							<td width="75%"><label for="scooter"><strong><?php _e('Consegne max per intervallo',$td); ?></strong> </label></td>
							<td><input type="number" name="scooter" id="scooter" style="width:50%" value="<?php echo $dispo['scooter']; ?>" /></td>
						</tr>
					</table>
					<input type="hidden" name="wppizza_action" value="wppizza_salva_dispo" />
					<input type="button" class="button button-primary" id="wppizza_salva_dispo" value="<?php _e('Salva Impostazioni', $td); ?>"/>
				</form>
			</div>
			<br />
			<hr />
			<br />
			<div class="form-wrap">
				<h3><?php _e('Giorni di Apertura', $td); ?></h3>
				<p><?php _e('Imposta i giorni in cui la tua attivit&agrave; accetta ordini online.'); ?></p>
				<?php 
				$days = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
				
				$dtranslations = array(
							'Mon' => __('Lun', $td),
							'Tue' => __('Mar', $td),
							'Wed' => __('Mer', $td),
							'Thu' => __('Gio', $td),
							'Fri' => __('Ven', $td),
							'Sat' => __('Sab', $td),
							'Sun' => __('Dom', $td),
						); 
				?>
				<form id="giorni_apertura_pizzeria">
					<table width="100%">
						<?php foreach($days as $giorno ): ?>
							<tr style="border-bottom: 1px solid">
								<td width="50%"><strong><?php echo $dtranslations[$giorno]; ?></strong></td>
								<td><input type="checkbox" name="<?php echo $giorno; ?>_apertura" id="<?php echo $giorno; ?>_apertura" value="1" <?php if(in_array($giorno, $giorni_apertura))echo 'checked'; ?> /></td>
							</tr>
							
							
						<?php endforeach; ?>
					</table>
					<br />
					<input type="hidden" name="wppizza_action" value="wppizza_salva_giorni_apertura" />
					<input type="button" class="button button-primary" id="wppizza_salva_giorni_apertura" value="<?php _e('Salva Giorni', $td); ?>"/>
				</form>
			</div>
			<br />
			<hr />
			<br />
			<div class="form-wrap">
				<h3><?php _e('Importo Minimo Ordine', $td); ?></h3>
				<p><?php _e('Imposta l&apos;importo minimo dell&apos;ordine. Se un utente effettua un ordine per un importo inferiore, il prezzo nel carrello sar&agrave; automaticamente aumentato all&apos;importo sottostante'); ?></p>
				<form id="gestione_importo">
					<table>
						<tr>
							<td width="75%"><label for="wppizza_importo_minimo"><strong><?php _e('Importo espresso in euro',$td); ?></strong></label></td> 
							<td><input type="number" name="wppizza_importo_minimo" id="wppizza_importo_minimo" style="width:70%" value="<?php echo $importo_minimo; ?>" /> &euro;</td>
						</tr>
					</table>
					<input type="hidden" name="wppizza_action" value="wppizza_salva_importo" />
					<input type="button" class="button button-primary" id="wppizza_salva_importo" value="<?php _e('Salva Importo', $td); ?>"/>
				</form>
			</div>
			<br />
			<hr />
			<br />
			<div class="form-wrap">
			
				<h3><?php _e('Sconto di Benvenuto', $td); ?></h3>
				<p><?php _e('Inserisci il codice dello sconto di benvenuto per il primo ordine di ciascun utente. Per impostare uno sconto di benvenuto crea un nuovo buono sconto.'); ?></p>
				<form id="gestione_sconto">
					<table>
						<tr>
							<td width="50%"><label for="wppizza_welcome_discount"><strong><?php _e('Codice Coupon',$td); ?></strong></label></td> 
							<td><select name="wppizza_welcome_discount" id="wppizza_welcome_discount" style="width:100%" ><?php echo $options_coupon; ?></select> </td>
						</tr>
					</table>
					<input type="hidden" name="wppizza_action" value="wppizza_salva_sconto" />
					<input type="button" class="button button-primary" id="wppizza_salva_sconto" value="<?php _e('Salva codice', $td); ?>"/>
				</form>
			</div>
			<br />
			<hr />
			<br />
			<div class="form-wrap">
				<h3><?php _e('Gestione Ingredienti', $td); ?></h3>
				<p><?php _e('Per Gestire gli ingredienti sono disponibili due differenti attributi prodotti da configurare manualmente. Al momento della creazione degli ingredienti aggiuntivi, verr&agrave; chiesto di inserire un prezzo che si andr&agrave; ad aggiungere a quello standard della pizza.'); ?></p>
			</div>
		</div>
	</div>
</div>
</div>