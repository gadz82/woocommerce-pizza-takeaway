<?php
/*
Plugin Name: Woocommerce Pizza Takeaway
Plugin URI: http://webglow.it
Description: Estensione Woocommerce per la gestione delle ordinazioni di pizze
Version: 0.0.1
Author: Francesco Marchesini
Author URI: http://www.webglow.it
*/

/**
 * @todo : set an option to manage the lenght of temporary slots
 * @todo : refactor the code and organize it
 * @todo : create/extend an exposed api
 * @todo : set multilingual support
 * @todo : add street view window in checkout page to allow the user to set where is the delivery (saving an object with pitch/zoom/heading informations)
 */

if(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	if (!class_exists( 'woocommerce_pizza_takeaway' ) ) {
		
		/**
		 * Classe unica plugin
		 * @author Francesco
		 *
		 */
		class woocommerce_pizza_takeaway{
			
			/**
			 * Text domaine
			 * @var string
			 */
			private static $plugin_text_domain = 'woocommerce_pizza_takeaway';
			
			/**
			 * Slot
			 * @var string
			 */
			private static $slot = '20'; 
	
			/**
			 * Costruttore
			 */
			public function __construct(){
				register_activation_hook( __FILE__, array($this,'install_var_wppizza' ));
				register_deactivation_hook( __FILE__, array($this,'uninstall_var_wppizza' ));
				//admin
				add_action('admin_menu', array($this,'add_wppizza_admin_menu'));
				add_action('admin_init', array($this,'wppizza_admin_init'));
				add_action('admin_enqueue_scripts', array($this,'wppizza_admin_css_js'));
				add_action('wppizza_render_table_orari', array($this, 'render_tabella_orari'),10,1);
				add_action('wppizza_render_giorni_esclusi', array($this, 'render_giorni_esclusi'),10,1);
				add_action('wppizza_add_orario', array($this, 'wppizza_add_orario'), 10, 1);
				add_action('wppizza_elimina_orario', array($this, 'wppizza_elimina_orario'), 10, 1);
				add_action('wppizza_salva_orario', array($this, 'wppizza_salva_orario'), 10, 1);
				add_action('wppizza_salva_dispo', array($this, 'wppizza_salva_dispo'), 10, 1);
				add_action('wppizza_salva_importo', array($this, 'wppizza_salva_importo'), 10, 1);
				add_action('wppizza_salva_giorni_apertura', array($this, 'wppizza_salva_giorni_apertura'), 10, 1);
				add_action('wppizza_salva_sconto', array($this, 'wppizza_salva_sconto'), 10, 1);
				add_action('wppizza_escludi_orario', array($this, 'wppizza_escludi_orario'), 10, 1);
				add_action('wppizza_elimina_giorno_escluso', array($this, 'wppizza_elimina_giorno_escluso'), 10, 1);
				add_action('pa_ingredienti-extra_add_form_fields', array($this,'wppizza_ingredienti_fields'), 10, 2);
				add_action('pa_ingredienti-extra_edit_form_fields', array($this,'wppizza_ingredienti_fields_edit'), 10, 2);
				add_action('edited_pa_ingredienti-extra', array($this,'save_ingredienti_prezzo_callback'), 10, 2);
				add_action('created_pa_ingredienti-extra',array($this,'save_ingredienti_prezzo_callback'), 10, 2);
				add_filter('filter_orari_esclusi', array($this, 'fiter_orari_esclusi'), 10, 1 );
				//shop
				add_action('init', array($this,'wppizza_init'));
				add_action('woocommerce_after_add_to_cart_form', array($this, 'wppizza_tpl_custom_pizza'),10);
				add_action('enqueue_wppizza_js', array($this, 'enqueue_wppizza_js'));
				add_action('enqueue_wppizza_css', array($this, 'enqueue_wppizza_css'));
				add_action('render_wppizza_boxes', array($this, 'render_wppizza_boxes'), 10, 2);
				add_action('wppizza_ingrediente_extra_add', array($this, 'wppizza_ingrediente_extra_add'), 10, 1);
				add_action('wppizza_ingrediente_extra_remove', array($this, 'wppizza_ingrediente_extra_remove'), 10, 1);
				add_action('wppizza_ingrediente_base_add', array($this, 'wppizza_ingrediente_base_add'), 10, 1);
				add_action('wppizza_ingrediente_base_remove', array($this, 'wppizza_ingrediente_base_remove'), 10, 1);
				add_filter( 'woocommerce_add_cart_item_data', array($this,'wppizza_add_to_cart_im'),100, 2 );
				add_action( 'woocommerce_add_to_cart', array($this,'wppizza_add_to_cart_key'),10, 3 );
				add_action( 'woocommerce_before_calculate_totals', array($this,'wppizza_custom_price'),20,1 );
				add_action( 'woocommerce_cart_emptied', array($this,'wppizza_empty_session_carrello'),10 );
				add_action( 'woocommerce_before_cart_item_quantity_zero', array($this,'wppizza_check_removed_product'),10,1 );
				add_action( 'woocommerce_after_order_notes', array($this,'wppizza_orario_field'));
				add_action( 'woocommerce_before_cart_totals', array($this, 'wppizza_check_importo'),10 );
				add_action( 'woocommerce_checkout_process', array($this,'wppizza_orario_checkout_field_process'));
				add_action( 'woocommerce_checkout_update_order_meta', array($this, 'wppizza_orario_field_update_order_meta'));
				add_filter( 'woocommerce_cart_item_name', array($this, 'wppizza_cart_title'), 10,2 );
				add_filter( 'woocommerce_checkout_product_title', array($this, 'wppizza_checkout_title'), 10,2 );
				add_filter( 'woocommerce_before_checkout_registration_form', array($this, 'wppizza_welcome_discount'), 10,1 );
				add_filter('recupera_ingredienti_extra', array($this, 'recupera_ingredienti_extra'), 10, 1);
				add_filter('woocommerce_email_order_meta_keys', array($this,'wppizza_email_order_meta_keys'));
				
				add_action('woocommerce_add_order_item_meta', array($this,'wppizza_order_observer'), 10, 2);
				add_action('wp_login', array($this,'wppizza_clear_discount'), 10);
				
				add_action('woocommerce_order_status_completed', array($this,'wppizza_order_status_observer'), 10, 1);
				add_action('woocommerce_order_status_on-hold', array($this,'wppizza_order_status_observer'), 10, 1);
				add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'wppizza_custom_checkout_field_display_admin'), 10, 1 );
				
			}
			
			/**
			 * Show delivery informations on admin order page
			 */
			public function wppizza_custom_checkout_field_display_admin($order){
				if(isset($order->wppizza_orario)){
					$output.= '<p><strong>'.__('Delivery time').':</strong> ' . $order->wppizza_orario. '</p>';
				}
				
				if(isset($order->citofono)){
					$output.= '<p><strong>'.__('Name on the intercom').':</strong> ' . $order->citofono.'</p>';
				}
					
				if(isset($order->billing_address_3)){
					$output.= '<p><strong>'.__('Additional info on the delivery place').':</strong> ' . $order->billing_address_3. '</p>';
				}
				echo $output;
			}
			
			/**
			 * Add delivery extra infos on email
			 */
			public function wppizza_email_order_meta_keys( $keys ) {
				$keys['Data e ora di consegna'] = '_wppizza_orario';
				$keys['Scala, Piano, Interno'] = 'billing_address_3';
				$keys['Cognome sul Citofono'] = 'citofono';
				return $keys;
			}
			
			/**
			 * Time slot availability management observer
			 */
			public function wppizza_order_status_observer($id_order){
				
				$order = new WC_Order($id_order);
				$orario = $order->wppizza_orario;
				$items = $order->get_items();
				$qty = 0;
				foreach($items as $prod){
					$qty+= $prod['qty'];
				}
				
				$disp = json_decode(unserialize(get_option('wppizza_agenda_disponibilita')), true);
				
				if($order->status == 'completed' || $order->status == 'on-hold'){

					if(isset($disp[$orario]) && !in_array($id_order, $disp[$orario]['ordini'])){
						$disp[$orario]['ordini'][] = $id_order;
						$disp[$orario]['pizze'] = $disp[$orario]['pizze']+$qty;
						$disp[$orario]['ordini'] = $disp[$orario]['ordini']+1;
					} elseif(!in_array($id_order, $disp[$orario]['ordini'])) {
						$disp[$orario]['ordini'][] = $id_order;
						$disp[$orario]['pizze'] = $qty;
						$disp[$orario]['ordini'] = 1;
					}
					
				}
				
				if($order->status == 'refunded' || $order->status == 'failed' || $order->status == 'cancelled'){
					if(isset($disp[$orario])){
						$disp[$orario]['pizze'] = $disp[$orario]['pizze']-$qty;
						$disp[$orario]['ordini'] = $disp[$orario]['ordini']-1;
					}
				}
				$disp = $this->filter_disp($disp);
				update_option('wppizza_agenda_disponibilita', serialize(json_encode($disp)) );
								
			}
			
			/**
			 * Filter available times
			 */
			private function filter_disp($disp){
				$now = date('d/m/y H:i');
				foreach($disp as $k => $val){
					if($k < $now){
						unset($disp[$k]);
					}
				}
				return $disp;
			}
			
			/**
			 * Clear a discount added to the order resume
			 */
			public function wppizza_clear_discount(){
				global $woocommerce;
				
				$woocommerce->cart->remove_coupons();
			}
			
			/**
			 * Add order items meta about food extra options
			 */
			public function wppizza_order_observer($item_id, $values){
				
				if(isset($values['data']->pizza['extra'])){
					$ingrx = array();
					foreach($values['data']->pizza['extra'] as $ing){
						$ingrx[] = $ing['desc_ingrediente'];
					}
					woocommerce_add_order_item_meta( $item_id, 'ingredienti_extra', implode(',',$ingrx) );
				}
				
				if(isset($values['data']->pizza['ingredienti_excl'])){
					woocommerce_add_order_item_meta( $item_id, 'ingredienti_esclusi', implode(',',$values['data']->pizza['ingredienti_excl']) );
				}

			}
			
			public function wppizza_init(){
				load_plugin_textdomain(self::$plugin_text_domain, false, dirname(plugin_basename(__FILE__)).'/languages');
				
				wp_enqueue_script( 'wppizza_chk_js_tpck', plugin_dir_url( __FILE__ ) . '/admin/js/jquery.datetimepicker.js',array('jquery'), '1.0.0.', false  );
				wp_enqueue_style('wppizza_chk_css_tpck',  plugin_dir_url( __FILE__ ) . '/admin/css/jquery.datetimepicker.css' );
				
				if(!isset($_SESSION))session_start();
				if(isset($_POST['wppizza_operation'])){
					$post = self::filter_post_vars($_POST);
					do_action($post['wppizza_operation'], $post);
				}
			}
			
			public function wppizza_orario_checkout_field_process(){
				global $woocommerce;
				if (!$_POST['wppizza_orario']){
					$woocommerce->add_error( __('E&apos; necessario indicare una data e un orario di consegna.') );
				}
			}
			
			public function wppizza_orario_field_update_order_meta($order_id){
				if ($_POST['wppizza_orario']) update_post_meta( $order_id, '_wppizza_orario', esc_attr($_POST['wppizza_orario']));
			}
			
			public function wppizza_welcome_discount($checkout){
				global $woocommerce;

			    $coupon_code = get_option('wp_pizza_welcome_discount'); // your coupon code here
				
			    if ( $woocommerce->cart->has_discount( $coupon_code ) ) return;
				
			    $coupon = new WC_Coupon($coupon_code);
			    if(!empty($coupon->code)){
			    	$woocommerce->cart->add_discount( $coupon_code );
			    	$woocommerce->clear_messages();
			    	$woocommerce->add_message(__('Hai diritto a uno sconto di benvenuto'), self::$plugin_text_domain);
			    	$woocommerce->show_messages();
			    }
				
			}
			
			public function wppizza_orario_field(){
				global $woocommerce;
				$options = array();
				$orari = json_decode(unserialize(get_option('wp_pizza_orari')), true);
				$limit = date('Y-m-d H:i', strtotime(current_time('mysql')."+ ".self::$slot." MINUTES"));
				foreach($orari as $orario => $aperto){
					$ora = date('Y-m-d').' '.$orario;
						
					if($ora > $limit && $aperto){
						$options[] = $orario;
					}
				}
				
				$giorni_esclusi = apply_filters('filter_orari_esclusi', json_decode(unserialize(get_option('wppizza_orari_esclusi')), true));
				
				//imp disponibilita
				$sett = json_decode(unserialize(get_option('wp_pizza_manager')), true);
				
				//disp orari sistema
				
				$disp = $this->filter_disp(json_decode(unserialize(get_option('wppizza_agenda_disponibilita')), true));
								
				$nr_pizze = 0;
				foreach($woocommerce->cart->cart_contents as $k => $product){
					$nr_pizze+= $product['quantity'];
				}
				
				//orari da scartare
				$excl = array();
				foreach($disp as $dt => $val){
					if(($val['ordini']+1) > $sett['scooter'] || ($val['pizze']+$nr_pizze) > $sett['produzione_slot']){
						$excl[] = $dt;
					}
				}
				
				$giorni_esclusi = array_merge($excl, $giorni_esclusi);
				
				$times = str_replace(array('{', '}'), array('[',']'), json_encode(array_filter(array_values($options))));
				
				$limit = date('Y-m-d H:i', strtotime(current_time('mysql')."+ ".self::$slot." MINUTES"));
				
				$giorni_apertura = json_decode(unserialize(get_option('wp_pizza_giorni_open')), true);
				
					echo '<div id="wppizza_orario_div"><h3>'.__('Orario di consegna').'</h3>';
					woocommerce_form_field('wppizza_orario', array(
						'type' 			=> 'text',
						'class' 		=> array('wppizza_orario'),
						'label' 		=> __('Orario di consegna'),
						'clear' 		=> false,
						'required'      => true
					));
					echo '</div>';?>
					
					<script type="text/javascript">
							var $ = jQuery;
							var days = ["Sun", "Mon","Tue","Wed","Thu","Fri","Sat"];
						
							var excl_days = <?php echo stripslashes(json_encode($giorni_esclusi)); ?>;
							var w_days = <?php echo stripslashes(json_encode($giorni_apertura)); ?>;
							
							var logic_days = function(){
								jQuery('.xdsoft_date').each(function(){
									var date = new Date();
									date.setFullYear($(this).attr('data-year'));
									date.setMonth($(this).attr('data-month'));
									date.setDate($(this).attr('data-date'));
									var cur = days[ date.getDay() ];
									if($.inArray(cur, w_days) < 0){
										var y = date.getFullYear();
										var m = date.getMonth();
										var d = date.getDate();

										jQuery('td[data-year="'+y+'"][data-month="'+m+'"][data-date="'+d+'"]').addClass('xdsoft_disabled');

										var oggi = new Date();
										if(days[oggi.getDay()] == cur){
											var Y,m,d,H,i,ival,hval;
											Y = oggi.getFullYear();
											m = pad((parseInt(oggi.getMonth())+1));
											d = oggi.getDate();
											H = oggi.getHours();

											$('#wppizza_orario').val((d+1)+'/'+m+'/'+Y+' <?php echo $options[0]; ?>');
											
										}
										
									}	
								});
								
							};

							var logic_esclusioni = function(date){
								var cur_date = date.getDate()+'/'+pad((parseInt(date.getMonth())+1))+'/'+date.getFullYear();
								for(var i = 0; i < excl_days.length; i++){
									var dtx = excl_days[i].split(' ');
									if(dtx[0] == cur_date){
										time = dtx[1].split(':');
										if(time[1] == '00')time[1] = '0';
										$('div[class^="xdsoft_time"][data-minute="'+time[1]+'"][data-hour="'+time[0]+'"]').addClass('xdsoft_disabled');
									}
								}
							};
						
							jQuery('#wppizza_orario').datetimepicker({
								 format:'d/m/Y H:i',
								 lang : 'it',
								 step: '<?php echo self::$slot; ?>',
								 minDate : '<?php echo date('Y-m-d', strtotime($limit)); ?>',
								 minTime : '<?php echo $options[0]; ?>',
								 allowTimes : '<?php echo $times; ?>',
								 value : '<?php echo date('d/m/Y', strtotime($limit)).' '.$options[0]; ?>',
						 		 onGenerate: logic_days,
							     onChangeDateTime: logic_esclusioni,
							 	 onShow:logic_esclusioni
							});
							function pad(n){return n<10 ? '0'+n : n}	 		
					 		
						</script>
					
					<?php 
				
				
			}
			
			public function wppizza_check_importo(){
				global $woocommerce;
				$minimo = get_option('wp_pizza_importo_minimo');
				
				if($woocommerce->cart->total < $minimo){
					
					$msg = __('Importo minimo ordine &euro; '. $minimo);
					?>
					
					<script>
						jQuery(document).ready(function($){
							$('input[name="proceed"]').hide().after("<strong><?php echo $msg; ?></strong>");
						});
					</script>
					
					<?php 
				}
			}
			
			public function wppizza_custom_price( $cart_object ) {
	
				if(!isset($_SESSION))session_start();
				global $woocommerce;
				
				if(is_cart()){
					$woocommerce->cart->remove_coupons();
				}
				
				foreach ( $cart_object->cart_contents as $key => $value ) {
					$product_id = $value['product_id'];
					if(isset($_SESSION['wppizza']['cart']['keys'][$key])){
						$value['data']->pizza = $_SESSION['wppizza']['cart']['keys'][$key]['data'];
					}
					if(isset($_SESSION['wppizza']['cart']['keys'][$key]['prezzo']) && !empty($_SESSION['wppizza']['cart']['keys'][$key]['prezzo'])){
						$value['data']->price = $_SESSION['wppizza']['cart']['keys'][$key]['prezzo'];
					}
					
				}
				
				
			}
			
			/**
			 * Renderizzazione del nome prodotto all'interno del carrello
			 * @param string $title
			 * @param array $item
			 * @return string
			 */
			public function wppizza_cart_title($title, $item){
				$title = '<strong>'.$title.'</strong><br />';
				if(isset($item['data']->pizza) && !empty($item['data']->pizza)){
					$pizza = $item['data']->pizza;
					
					if(isset($pizza['extra']) && !empty($pizza['extra'])){
						$title.= __('<strong>+ Ingredienti Extra</strong>: ', self::$plugin_text_domain);
						$ex = array();
						foreach($pizza['extra'] as $extra){
							$ex[] = $extra['desc_ingrediente'];
						}
						$title.= implode(', ', $ex).'</br>';
					}
					
					if(isset($pizza['ingredienti_excl']) && !empty($pizza['ingredienti_excl'])){
						$title.= __('<strong>- Senza</strong>: ', self::$plugin_text_domain);
						$ex = array();
						foreach($pizza['ingredienti_excl'] as $extra){
							$ex[] = $extra;
						}
						$title.= implode(', ', $ex);
					}
						
				}
				return $title;
			}
			
			public function wppizza_checkout_title($title, $product){
				$title = '<strong>'.$title.'</strong><br />';
				if(isset($product->pizza) && !empty($product->pizza)){
					$pizza = $product->pizza;
					if(isset($pizza['extra']) && !empty($pizza['extra'])){
						$title.= __('<strong>+ Ingredienti Extra</strong>: ', self::$plugin_text_domain);
						$ex = array();
						foreach($pizza['extra'] as $extra){
							$ex[] = $extra['desc_ingrediente'];
						}
						$title.= implode(', ', $ex).'</br>';
					}
						
					if(isset($pizza['ingredienti_excl']) && !empty($pizza['ingredienti_excl'])){
						$title.= __('<strong>- Senza</strong>: ', self::$plugin_text_domain);
						$ex = array();
						foreach($pizza['ingredienti_excl'] as $extra){
							$ex[] = $extra;
						}
						$title.= implode(', ', $ex);
					}
				}
				return $title;
			}
			
			public function wppizza_add_to_cart_key($k,$product_id){
				if(isset($_SESSION['wppizza'][$product_id])){
					$_SESSION['wppizza']['cart']['keys'][$k] = array('prezzo' => $_SESSION['wppizza'][$product_id]['prezzo'], 'product_id' => $product_id, 'data' => $_SESSION['wppizza'][$product_id]);
					unset($_SESSION['wppizza'][$product_id]);
				}
			}
			
			public function wppizza_add_to_cart_im($cart_item_data, $product_id, $variation_id= '' ){
				session_start();
				if(isset($_SESSION['wppizza'][$product_id])){
					return array('wppizza_data' => $_SESSION['wppizza'][$product_id]);
				}
			}
			
			public function wppizza_ingrediente_extra_add($post){
				if(isset($post['desc_ingrediente']) && isset($post['id_attributo']) && isset($post['id_prodotto']) && isset($post['nuovo_prezzo']) && isset($post['prezzo_ingrediente'])){
					$_SESSION['wppizza'][$post['id_prodotto']]['prezzo'] = str_replace('&euro;', '', $post['nuovo_prezzo']);
					$_SESSION['wppizza'][$post['id_prodotto']]['extra'][$post['id_attributo']] = $post;
					echo 'ok';exit();
				} else {
					echo 'ko';exit();
				}
			}

			public function wppizza_ingrediente_extra_remove($post){
				if(isset($post['desc_ingrediente']) && isset($post['id_attributo']) && isset($post['id_prodotto']) && isset($post['nuovo_prezzo']) && isset($post['prezzo_ingrediente'])){
					$_SESSION['wppizza'][$post['id_prodotto']]['prezzo'] = str_replace('&euro;', '', $post['nuovo_prezzo']);
					unset($_SESSION['wppizza'][$post['id_prodotto']]['extra'][$post['id_attributo']]);
					if(empty($_SESSION['wppizza'][$post['id_prodotto']]['extra']))unset($_SESSION['wppizza'][$post['id_prodotto']]['extra']);
					echo 'ok';exit();
				} else {
					echo 'ko';exit();
				}
			}
			
			public function wppizza_ingrediente_base_add($post){
				if(isset($post['id_prodotto']) && isset($post['ingrediente'])){
					$key = array_search($post['ingrediente'], $_SESSION['wppizza'][$post['id_prodotto']]['ingredienti_excl']);
					if (false !== $key) {
						unset($_SESSION['wppizza'][$post['id_prodotto']]['ingredienti_excl'][$key]);
					}
					
					if(empty($_SESSION['wppizza'][$post['id_prodotto']]['ingredienti_excl']))unset($_SESSION['wppizza'][$post['id_prodotto']]['ingredienti_excl']);
					echo 'ok';exit();
				} else {
					echo 'ko';exit();
				}
			}
			
			public function wppizza_ingrediente_base_remove($post){
				if(isset($post['id_prodotto']) && isset($post['ingrediente'])){
					$_SESSION['wppizza'][$post['id_prodotto']]['ingredienti_excl'][] = $post['ingrediente'];
					echo 'ok';exit();
				} else {
					echo 'ko';exit();
				}
			}
			
			public function wppizza_empty_session_carrello(){
				if(isset($_SESSION['wppizza'])){
					unset($_SESSION['wppizza']);
				}
				return true;
			}
			
			public function wppizza_check_removed_product($cart_key){
				if(!isset($_SESSION))session_start();
				if(isset($_SESSION['wppizza']['cart']['keys'][$cart_key])){
					$pid = $_SESSION['wppizza']['cart']['keys'][$cart_key]['product_id'];
					unset($_SESSION['wppizza']['cart']['keys'][$cart_key]);
				}
			}
			
			
			public function wppizza_tpl_custom_pizza(){
				/**
				 * @var WC_Product
				 */
				global $product;
				
				$ibase = array_values(woocommerce_get_product_terms($product->id, 'pa_ingredienti-base', 'names'));
				$iextra = apply_filters('recupera_ingredienti_extra', array_values(woocommerce_get_product_terms($product->id, 'pa_ingredienti-extra', 'all')));
								
				do_action('enqueue_wppizza_js');
				do_action('enqueue_wppizza_css');
				do_action('render_wppizza_boxes', $ibase, $iextra);
				
			}
			
			public function enqueue_wppizza_js(){
				wp_enqueue_script('wppizza_shop_base_js', plugins_url('', __FILE__).'/shop/js/wppizza.js', array(), false, true);
			}
			
			public function enqueue_wppizza_css(){
				wp_enqueue_style('wppizza_shop_base_css', plugin_dir_url( __FILE__ ) . '/shop/css/wppizza.css' );
			}
				
			public function recupera_ingredienti_extra($ingredienti){
				
				$nr = count($ingredienti);
				$ri = array();
				for($i = 0; $i < $nr; $i++){
					$ri[$i] = $ingredienti[$i];
					$prezzo = get_option("taxonomy_".$ingredienti[$i]->term_id);
					$ri[$i]->prezzo = $prezzo['prezzo'];
				}
				
				return $ri;
			}
			
			public function render_wppizza_boxes($ibase, $iextra = array()){
				if(!empty($ibase)){
					$td = self::$plugin_text_domain;
					include(__DIR__.'/shop/wppizza_boxes.php');
				}
			}

			public function wppizza_admin_init(){
				load_plugin_textdomain(self::$plugin_text_domain, false, dirname(plugin_basename(__FILE__)).'/languages');
				if(isset($_POST['wppizza_action'])){
					do_action(esc_attr($_POST['wppizza_action']), self::filter_post_vars($_POST));
				}
				global $pagenow;
			}
			
			public function wppizza_ingredienti_fields($tag){
				//check for existing taxonomy meta for term ID
				$t_id = $tag->term_id;
				$term_meta = get_option( "taxonomy_$t_id");
				
				?>

				<tr class="form-field">
				<th scope="row" valign="top"><label for="prezzo_ingrediente"><?php _e('Prezzo Ingrediente Aggiuntivo'); ?></label></th>
				<td>
					<input type="number" name="term_meta[prezzo]" id="term_meta[prezzo]" size="25" style="width:75%;" value="<?php echo $term_meta['prezzo'] ? $term_meta['prezzo'] : ''; ?>"> &euro;<br />
		            <span class="description"><?php _e('Prezzo Ingrediente Aggiuntivo espresso in euro'); ?></span>
		        </td>
				</tr>
				<tr class="form-field"><td></td></tr>
				<?php
			}
			
			public function wppizza_ingredienti_fields_edit($tag){
				//check for existing taxonomy meta for term ID
				$t_id = $tag->term_id;
				$term_meta = get_option( "taxonomy_$t_id");
				
				?>

				<tr class="form-field">
				<th scope="row" valign="top"><label for="prezzo_ingrediente"><?php _e('Prezzo Ingrediente Aggiuntivo'); ?></label></th>
				<td>
					<input type="text" name="term_meta[prezzo]" id="numeric_only" size="25" style="width:75%;" value="<?php echo $term_meta['prezzo'] ? $term_meta['prezzo'] : ''; ?>"> &euro;<br />
		            <span class="description"><?php _e('Prezzo Ingrediente Aggiuntivo espresso in euro'); ?></span>
		        </td>
				</tr>
				<tr class="form-field"><td></td></tr>
				<?php
			}
			
			public function save_ingredienti_prezzo_callback($term_id){
				if ( isset( $_POST['term_meta'] ) ) {
					$t_id = $term_id;
					$term_meta = get_option( "taxonomy_$t_id");
					$cat_keys = array_keys($_POST['term_meta']);
					foreach ($cat_keys as $key){
						if (isset($_POST['term_meta'][$key])){
							$term_meta[$key] = $_POST['term_meta'][$key];
						}
					}
					//save the option array
					update_option( "taxonomy_$t_id", $term_meta );
				}
			}
			
			public function wppizza_add_orario($post){
				
				if(isset($post['nuovo_orario'])){
					$orari = json_decode(unserialize(get_option('wp_pizza_orari')), true);
					if(!array_key_exists($post['nuovo_orario'], $orari)){
						$orari[$post['nuovo_orario']] = '1';
						ksort($orari);
						$opt = serialize(json_encode($orari));
						update_option('wp_pizza_orari', $opt);
						ob_start();
						do_action('wppizza_render_table_orari', $orari);
						$content = ob_get_contents();
						ob_end_clean();
						echo json_encode(array('success' => 'true', 'tpl' => $content));
						exit();
					} else {
						echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Orario gi&agrave; presente in elenco', self::$plugin_text_domain).'</p></div>'));
					}
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Inserisci un orario', self::$plugin_text_domain).'</p></div>'));
				}
			}
			
			public function wppizza_elimina_orario($post){
				if(isset($post['orario'])){
					$orari = json_decode(unserialize(get_option('wp_pizza_orari')), true);
					if(array_key_exists($post['orario'], $orari)){
						unset($orari[$post['orario']]);
						ksort($orari);
						$opt = serialize(json_encode($orari));
						update_option('wp_pizza_orari', $opt);
						echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Orario eliminato dall&apos;elenco!', self::$plugin_text_domain).'</p></div>'));
						exit();
					} else {
						echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Orario non presente in elenco', self::$plugin_text_domain).'</p></div>'));
					}
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Inserisci un orario', self::$plugin_text_domain).'</p></div>'));
				}
			}

			public function wppizza_salva_orario($post){
				
				if(isset($post['aperto']) || isset($post['chiuso'])){
					$orari = array();
					
					if(isset($post['aperto'])){
						$orari_a = array_keys($post['aperto']);
						
						foreach($orari_a as $orario){
							$orari[$orario] = '1';
						}
					}
					if(isset($post['chiuso'])){
						$orari_c = array_keys($post['chiuso']);
						foreach($orari_c as $orario){
							$orari[$orario] = '0';
						}
					}
					ksort($orari);
					fb($orari);exit();
					$opt = serialize(json_encode($orari));
					//update_option('wp_pizza_orari', $opt);
					ob_start();
					do_action('wppizza_render_table_orari', $orari);
					$content = ob_get_contents();
					ob_end_clean();
					echo json_encode(array('success' => 'true', 'tbl' => $content, 'tpl' => '<div class="updated below-h2"><p>'.__('Orari aggiornati!', self::$plugin_text_domain).'</p></div>'));
					exit();
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Errore nel salvataggio', self::$plugin_text_domain).'</p></div>'));
				}
			}
			
			public function wppizza_salva_dispo($post){
				if(!empty($post['scooter']) && is_numeric($post['scooter']) && !empty($post['produzione_slot']) && is_numeric($post['produzione_slot'])){
					update_option('wp_pizza_manager', serialize(json_encode(array(
						'produzione_slot' => $post['produzione_slot'],
						'scooter' => $post['scooter'] 
					))));
					echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Impostazioni disponibilit&agrave; aggiornate!', self::$plugin_text_domain).'</p></div>'));
					exit();
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Campi non compilati correttamente', self::$plugin_text_domain).'</p></div>'));
				}
			}

			public function wppizza_salva_importo($post){
				if(!empty($post['wppizza_importo_minimo']) && is_numeric($post['wppizza_importo_minimo'])){
					update_option('wp_pizza_importo_minimo',$post['wppizza_importo_minimo']);
					echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Impostazioni importo minimo aggiornate!', self::$plugin_text_domain).'</p></div>'));
					exit();
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Campi non compilati correttamente', self::$plugin_text_domain).'</p></div>'));
				}
			}
			
			public function wppizza_salva_giorni_apertura($post){
				unset($post['wppizza_action']);
				if(!empty($post)){
					$all = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
					$values = array();
					foreach($post as $key => $val){
						$day = explode('_', $key);
						if(in_array($day[0], $all))
							$values[] = $day[0];
					}
					
					if(!empty($val)){
						update_option('wp_pizza_giorni_open', serialize(json_encode($values)));
						echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Impostazioni giorni apertura modificate con successo!', self::$plugin_text_domain).'</p></div>'));
					} else {
						echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Si egrave; verificato un errore', self::$plugin_text_domain).'</p></div>'));
					}
					
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Seleziona almeno un giorno', self::$plugin_text_domain).'</p></div>'));
				}
			}
			
			public function wppizza_salva_sconto($post){
				if(isset($post['wppizza_welcome_discount'])){
					update_option('wp_pizza_welcome_discount', $post['wppizza_welcome_discount']);
					echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Impostazioni sconto di benvenuto aggiornate!', self::$plugin_text_domain).'</p></div>'));
					exit();
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Campi non compilati correttamente', self::$plugin_text_domain).'</p></div>'));
				}
			}
		
			public function wppizza_escludi_orario($post){
				if(isset($post['escludi_orario']) && !empty($post['escludi_orario'])){
					$giorni_esclusi = apply_filters('filter_orari_esclusi', json_decode(unserialize(get_option('wppizza_orari_esclusi')), true));
					$giorni_esclusi[] = $post['escludi_orario'];
					sort($giorni_esclusi);
					$giorni_esclusi = array_unique($giorni_esclusi);
					update_option('wppizza_orari_esclusi', serialize(json_encode($giorni_esclusi)));
					
					ob_start();
					do_action('wppizza_render_giorni_esclusi', $giorni_esclusi);
					$content = ob_get_contents();
					ob_end_clean();
					
					echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Giorno Escluso!', self::$plugin_text_domain).'</p></div>', 'tbl' => $content));
					exit();
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Campi non compilati correttamente', self::$plugin_text_domain).'</p></div>'));
				}
			}
			
			public function wppizza_elimina_giorno_escluso($post){
				if(isset($post['orario'])){
					$orari = json_decode(unserialize(get_option('wppizza_orari_esclusi')), true);
					if(in_array($post['orario'], $orari)){
						if(($key = array_search($post['orario'], $orari)) !== false) {
							unset($orari[$key]);
						}
						sort($orari);
						$opt = serialize(json_encode($orari));
						update_option('wppizza_orari_esclusi', $opt);
						echo json_encode(array('success' => 'true', 'tpl' => '<div class="updated below-h2"><p>'.__('Orario eliminato dall&apos;elenco!', self::$plugin_text_domain).'</p></div>'));
						exit();
					} else {
						echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Orario non presente in elenco', self::$plugin_text_domain).'</p></div>'));
					}
				} else {
					echo json_encode(array('success' => 'false', 'tpl' => '<div class="error below-h2"><p>'.__('Inserisci un orario', self::$plugin_text_domain).'</p></div>'));
				}
			}
				
		
			public function wppizza_settings_page(){
				
				$orari = json_decode(unserialize(get_option('wp_pizza_orari')), false);
				$dispo = json_decode(unserialize(get_option('wp_pizza_manager')), true);
				$welcome_discount = get_option('wp_pizza_welcome_discount');
				$giorni_apertura = json_decode(unserialize(get_option('wp_pizza_giorni_open')), true);
				$importo_minimo = number_format((float)round(get_option('wp_pizza_importo_minimo')), 2, '.', '');
				$giorni_esclusi = apply_filters('filter_orari_esclusi', json_decode(unserialize(get_option('wppizza_orari_esclusi')), true));
				
				/**
				 * @var wpdb
				 */
				global $wpdb;
					
				$coupons = $wpdb->get_results("SELECT
								p.post_title
							FROM
								".$wpdb->prefix."posts p
							WHERE
								p.post_type = 'shop_coupon'
							AND
								p.post_status = 'publish'", ARRAY_A);
				
				$options_coupon = '<option value=" "> </option>';

				foreach($coupons as $key => $coupon){
					$selected = $coupon['post_title'] == $welcome_discount ? 'selected' : '';
					$options_coupon.= '<option value="'.$coupon['post_title'].'" '.$selected.'>'.$coupon['post_title'].'</option>';
				}

				$td = self::$plugin_text_domain;
				
				include(__DIR__.'/admin/wppizza_admin.php');
			}

			public function wppizza_admin_css_js(){
				wp_enqueue_script( 'wppizza_admin_js_tpck', plugin_dir_url( __FILE__ ) . '/admin/js/jquery.datetimepicker.js' );
				wp_enqueue_script( 'wppizza_admin_js', plugin_dir_url( __FILE__ ) . '/admin/js/wppizza_admin.js' );
				wp_enqueue_style('wppizza_admin_css_tpck',  plugin_dir_url( __FILE__ ) . '/admin/css/jquery.datetimepicker.css' );
			}
			
			public function add_wppizza_admin_menu(){
				add_submenu_page( 'woocommerce', __( 'Pizza Manager', 'wppizza' ), __( 'Pizza Manager', 'wppizza' ), 'delete_posts', 'wppizza',  array($this,'wppizza_settings_page'));
			}
			
			public function render_tabella_orari($orari){
				$td = self::$plugin_text_domain;
				include(__DIR__.'/admin/wppizza_admin_tabella_orari.php');
			}
			
			public function render_giorni_esclusi($giorni){
				$td = self::$plugin_text_domain;
				include(__DIR__.'/admin/wppizza_admin_tabella_giorni_esclusi.php');
			}
			
			public function fiter_orari_esclusi($orari){
				$return = array();
				$nr = count($orari);
				$now = date('d-m-Y H:i:s');
				for($i = 0; $i < $nr; $i++){
					if($orari[$i] >= $now){
						$return[] = $orari[$i];
					}
				}
				return $return;
			}
						
			public function install_var_wppizza(){
				add_option('wp_pizza_orari', serialize(json_encode(array(
						'12:00' => '1',
						'12:20' => '1',
						'12:40' => '1',
						'13:00' => '1',
						'13:20' => '1',
						'13:40' => '1',
						'14:00' => '1',
						'14:20' => '1',
						'14:40' => '1',
						'15:00' => '1',
						'19:00' => '1',
						'19:20' => '1',
						'19:40' => '1',
						'20:00' => '1',
						'20:20' => '1',
						'20:40' => '1',
						'21:00' => '1',
						'21:20' => '1',
						'21:40' => '1',
						'22:00' => '1'
					)
				)));
				add_option('wp_pizza_manager', serialize(json_encode(array(
					'produzione_slot' => 20,
					'scooter' => 5 
				))));
				add_option('wp_pizza_giorni_open', serialize(json_encode(array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'))));
				add_option('wp_pizza_importo_minimo', '10.00');
				add_option('wp_pizza_welcome_discount', '');
				add_option('wppizza_orari_esclusi', '');
				add_option('wppizza_agenda_disponibilita', serialize(json_encode(array())));
				/**
				 * @var wpdb
				 */
				global $wpdb;
				
				$ib = $wpdb->get_var("SELECT
									attribute_id
								FROM
									".$wpdb->prefix."woocommerce_attribute_taxonomies
								WHERE
									attribute_name = 'ingredienti-base'
								");
				
				if(empty($ib)){
					$wpdb->query("
						INSERT INTO ".$wpdb->prefix."woocommerce_attribute_taxonomies (attribute_name, attribute_label, attribute_type, attribute_orderby) VALUES ('ingredienti-base', 'Ingredienti Base', 'select', 'menu_order')
					");
				}
				
				$ie = $wpdb->get_var("SELECT
									attribute_id
								FROM
									".$wpdb->prefix."woocommerce_attribute_taxonomies
								WHERE
									attribute_name = 'ingredienti-extra'
								");

				if(empty($ie)){
					$wpdb->query("
						INSERT INTO ".$wpdb->prefix."woocommerce_attribute_taxonomies (attribute_name, attribute_label, attribute_type, attribute_orderby) VALUES ('ingredienti-extra', 'Ingredienti Extra', 'select', 'menu_order')
					");
				}				
			}
			
			public function uninstall_var_wppizza(){
				delete_option('wp_pizza_orari');
				delete_option('wp_pizza_manager');
				delete_option('wp_pizza_importo_minimo');
				delete_option('wp_pizza_giorni_open');
				delete_option('wp_pizza_welcome_discount');
				delete_option('wppizza_orari_esclusi');
				delete_option('wppizza_agenda_disponibilita');
				
			}
			
			public static function filter_post_vars($vars){
				$return = array();
				if(is_array($vars)){
					foreach($vars as $k => $v){
						if(!is_array($v)){
							if(!empty($v)){
								$return[$k] = esc_attr($v);
							}	
						} else {
							return $vars;
						}	
					}
					return $return;
				} else {
					return esc_attr($vars);
				}
			}
		}
		
	}
	
	new woocommerce_pizza_takeaway();
}