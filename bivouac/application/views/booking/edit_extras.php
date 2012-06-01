<?php 
$data['title'] = "Add/Edit extras on your booking!";
$this->load->view("_includes/frontend/head", $data); 
?>
<div id="container" class="clearfix">
	<?php if ($this->session->userdata('is_logged_in') === TRUE): ?>
		<p class="user-logout"> 
			Hello <b><?php echo $this->session->userdata('screen_name'); ?></b> | <?php echo anchor('account/logout', 'Log out'); ?>
		</p>
	<?php endif; ?>
	<?php echo anchor('/account/bookings', 'My Account', array('title' => 'Login to your account', 'class' =>'login-tab')); ?>
	
	<?php $this->load->view("_includes/frontend/header"); ?>
	
	<div id="main" role="main">
		<!-- Site Details -->
		<div class="site-details clearfix">
			<div class="telephone-number">
				<h1><span class="telephone-icon"></span>01765 53 50 20</h1>
			</div>
			<div class="social-media">
				<ul>
					<li>
						<a href="http://www.facebook.com/wearethebivouac" title="See what we're up to on Facebook"><span class="facebook-icon"></span> Facebook</a>
					</li>
					<li>
						<a href="https://twitter.com/#!/thebivouac" title="Folllow us on Twitter"><span class="twitter-icon"></span> Twitter</a>
					</li>
				</ul>
			</div>
		</div>
	
		<section>
			<ol class="booking-flow-indicators clearfix">
				<li>When &amp; where</li>
				<li class="active">Holiday Extras</li>
				<li>Contact Details</li>
				<li>Booking Overview</li>
				<li>Payment</li>
				<li>Confirmation</li>
			</ol>
			
			<h1>Extras</h1>
			<p>Choose any extras you wish to add to your booking. The total price of your booking will refresh as you make your choices.</p>
		
			<div class="content" id="extras">
				<div class="price-container">
					<p><strong>Extras Total Price:</strong> &pound;<span class="extras-price"><?php echo $extras_price; ?></span></p>
					<p><strong>Total Booking Price:</strong> &pound;<span class="total-price"><?php echo $total_price; ?></span></p>
				</div>
				
				<?php echo form_open('booking/edit_extras/' . $booking_id, array('id' => 'extras-booking-form', 'class' => 'booking-form')); ?>
					<input type="hidden" name="booking_id" id="booking_id" value="<?php echo $booking_id; ?>" />
					<input type="hidden" name="total_price" id="price" value="<?php echo $total_price; ?>" />
					
					<?php foreach ($extra_types as $type): ?>
						<?php if ($type['extras_num'] > 0): ?>
							<h2><?php echo $type['name']; ?></h2>
						<?php endif; ?>
						
						<ul class="extra-list">
							<?php 
								$extras = $type['extras']; 	
								if ($type['extras_num'] > 0): 
							?>
								<?php if ($type['id'] != 6 && $type['id'] != 4 && $type['id'] != 2): ?>
									<?php foreach ($extras->result() as $extra): ?>
										<?php
											// Timestamps from d-m-Y formats i.e. without hours mins and seconds
											$cut_off_date = strtotime($extra->cut_off_date);
											$today = strtotime(date('d-m-Y'));
											
											$in_date = false;
											
											if ($cut_off_date === 0 || empty($cut_off_date) || $today <= $cut_off_date)
											{
												$in_date = true;
											}
										?>
								
										<?php if ($extra->status === "open" && $in_date): // Only show items that are set to open and have cut off dates today or later ?>
								
										<?php
											// Loop through all previously selected extras. If the ID of the selected extra is equal to the ID
											// of this extra then set a prevVal data attribute with the quantity if a select, or set to checked
					
											// set defaults
											$prev_val_quantity = 0;
											$prev_val_nights = 0;
											$sExtraID = 0;
											$prev_val_date = 0;
										
											if ($selected_extras->num_rows() > 0)
											{		
												foreach ($selected_extras->result() as $sExtra)
												{	
													if ($sExtra->extra_id === $extra->id)
													{
														$sExtraID = $sExtra->extra_id;
														$prev_val_quantity = $sExtra->quantity;
														$prev_val_nights = $sExtra->nights;
														$prev_val_date = $sExtra->date;
													}
												}
											}
										?>
								
										<li>
											<h3><?php echo $extra->name; ?></h3>
											<div class="extra-information clearfix">
											<?php if (!empty($extra->photo_1)): ?>
												<img src="<?php echo base_url() . 'images/extras/' . $extra->photo_1; ?>" width="100" />
											<?php endif; ?>
											<p class="extra-description">
												<?php echo $extra->description; ?>
												<?php if (strtotime($extra->start_date) > 0): ?>
												<br /><strong>Start Date/Time: </strong> <?php echo date('d-m-Y H:i', strtotime($extra->start_date)); ?>
												<?php endif; ?>
												<?php if (strtotime($extra->end_date) > 0): ?>
												<br /><strong>End Date/Time: </strong> <?php echo date('d-m-Y H:i', strtotime($extra->end_date)); ?>
												<?php endif; ?>	
											</p>
											<p class="price extra-price">&pound; <?php echo $extra->price; ?></p>
											<div class="extra-preferences">
												<input type="hidden" class="extra_price" name="extra_price_<?php echo $extra->id; ?>" value="<?php echo $extra->price; ?>" />
											
												<?php if ($extra->extra_type == 1): // Single Item ?>
													<label for="extra_checkbox_<?php echo $extra->id; ?>">Tick to add extra</label>
													<input type="checkbox" class="extra_quantity" id="extra_checkbox_<?php echo $extra->id; ?>" <?php if($prev_val_quantity == 1){ echo "checked='checked'"; } ?> name="extra_quantity_<?php echo $extra->id; ?>" value="1" />
													
												<?php elseif ($extra->extra_type == 3): // Multi Purchase Items ?>	
													<label for="extra_number_<?php echo $extra->id; ?>">How many?</label>
													<select class="extra_quantity" id="extra_number_<?php echo $extra->id; ?>" name="extra_quantity_<?php echo $extra->id; ?>" <?php if($prev_val_quantity > 0){ echo "data-prevVal=" . $prev_val_quantity; } ?>>
														<?php for($i=0; $i<=8; $i++): ?>
														<option value="<?php echo $i; ?>" <?php if ($prev_val_quantity == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php endfor; ?>
													</select>
													
												<?php else: // Baby Provision ?>
													<label for="extra_number_<?php echo $extra->id; ?>">How many?</label>
													<select class="extra_quantity" id="extra_number_<?php echo $extra->id; ?>" name="extra_quantity_<?php echo $extra->id; ?>" <?php if($prev_val_quantity > 0){ echo "data-prevVal=" . $prev_val_quantity; } ?>>
														<?php for($i=0; $i<=$babies; $i++): ?>
														<option value="<?php echo $i; ?>" <?php if ($prev_val_quantity == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php endfor; ?>
													</select>
									
													<label for="extra_nights_<?php echo $extra->id; ?>">How many days/nights?</label>
													<select class="extra_nights" id="extra_nights_<?php echo $extra->id; ?>" name="extra_nights_<?php echo $extra->id; ?>" <?php if($prev_val_nights > 0){ echo "data-prevVal=" . $prev_val_nights; } ?>>
														<?php for($i=1; $i<=$duration; $i++): ?>
														<option value="<?php echo $i; ?>" <?php if ($prev_val_nights == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php endfor; ?>
													</select>
												<?php endif; ?>
											</div>
										</li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php else: ?>
									<?php
										// Type ID equals 6 or 4 (Specific Dates Type or and Event) 
										foreach ($extras as $extra): 
									?>			
																	
										<?php
											// Timestamps from d-m-Y formats i.e. without hours mins and seconds
											$cut_off_date = strtotime($extra->cut_off_date);
											$today = strtotime(date('d-m-Y'));
											
											$in_date = false;
											
											if ($cut_off_date === 0 || empty($cut_off_date) || $today <= $cut_off_date)
											{
												$in_date = true;
											}
										?>
										<?php if ($extra->status === "open" && $in_date): // Only show items that are set to open and have cut off dates today or later ?>
										
										<?php
											// Loop through all previously selected extras. If the ID of the selected extra is equal to the ID
											// of this extra then set a prevVal data attribute with the quantity if a select, or set to checked
					
											// set defaults
											$prev_val_quantity = 0;
											$prev_val_nights = 0;
											$sExtraID = 0;
											$prev_val_date = 0;
										
											if ($selected_extras->num_rows() > 0)
											{		
												foreach ($selected_extras->result() as $sExtra)
												{	
													if ($sExtra->extra_id === $extra->id)
													{
														$sExtraID = $sExtra->extra_id;
														$prev_val_quantity = $sExtra->quantity;
														$prev_val_nights = $sExtra->nights;
														$prev_val_date = $sExtra->date;
													}
												}
											}
										?>
											<li>
												<h3><?php echo $extra->name; ?></h3>
												<div class="extra-information clearfix">
												<?php if (!empty($extra->photo_1)): ?>
													<img src="<?php echo base_url() . 'images/extras/' . $extra->photo_1; ?>" width="100" />
												<?php endif; ?>
												<p class="extra-description">
													<?php echo $extra->description; ?>
													<?php if (strtotime($extra->start_date) > 0): ?>
													<br /><strong>Start Date/Time: </strong> <?php echo date('d-m-Y H:i', strtotime($extra->start_date)); ?>
													<?php endif; ?>
													<?php if (strtotime($extra->end_date) > 0): ?>
													<br /><strong>End Date/Time: </strong> <?php echo date('d-m-Y H:i', strtotime($extra->end_date)); ?>
													<?php endif; ?>	
												</p>
												<p class="price extra-price">&pound; <?php echo $extra->price; ?></p>
												<div class="extra-preferences <?php if ($extra->extra_type == 6) { echo 'specific-dates-extras'; } ?>">
													<input type="hidden" class="extra_price" name="extra_price_<?php echo $extra->id; ?>" value="<?php echo $extra->price; ?>" />
												
												<?php if ($extra->extra_type == 4): // Event ?>	
													<label for="extra_number_<?php echo $extra->id; ?>">How many places?</label>
													<select class="extra_quantity" id="extra_number_<?php echo $extra->id; ?>" name="extra_quantity_<?php echo $extra->id; ?>" <?php if($prev_val_quantity > 0){ echo "data-prevVal=" . $prev_val_quantity; } ?>>
														<?php for ($i=0; $i<=$extra->number_still_available; $i++): ?>
														<option value="<?php echo $i; ?>" <?php if ($prev_val_quantity == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php endfor; ?>
													</select>
												
												<?php elseif ($extra->extra_type == 2): // Day/Night Hire ?>
													<label for="extra_number_<?php echo $extra->id; ?>">How many?</label>
													<select class="extra_quantity" id="extra_number_<?php echo $extra->id; ?>" name="extra_quantity_<?php echo $extra->id; ?>" <?php if($prev_val_quantity > 0){ echo "data-prevVal=" . $prev_val_quantity; } ?>>
														<?php 
															if ($extra->id == 14):  // If this is for dogs - id 14 then limit to 2
																for ($i=0; $i<=2; $i++): 
														?>
																<option value="<?php echo $i; ?>" <?php if ($prev_val_quantity == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php 
																endfor; 
															else:
																for ($i=0; $i<=8; $i++):	
														?>
																<option value="<?php echo $i; ?>" <?php if ($prev_val_quantity == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php
																endfor;
															endif;
														?>
													</select>
									
													<label for="extra_nights_<?php echo $extra->id; ?>">How many days/nights?</label>
													<select class="extra_nights" id="extra_nights_<?php echo $extra->id; ?>" name="extra_nights_<?php echo $extra->id; ?>" <?php if($prev_val_nights > 0){ echo "data-prevVal=" . $prev_val_nights; } ?>>
														<?php for($i=0; $i<=$duration; $i++): ?>
														<option value="<?php echo $i; ?>" <?php if ($prev_val_nights == $i){ echo "selected"; } ?>><?php echo $i; ?></option>
														<?php endfor; ?>
													</select>
													
												<?php else: ?>
												
													<label for="extra_number_<?php echo $extra->id; ?>">Which dates do you want to book this for?</label>
													<ul>
														<?php foreach ($extra->selectable_dates as $date): ?>
														<li><input type="checkbox" class="date-option" name="extra_date_<?php echo $extra->id; ?>[]" value="<?php echo date('d-m-Y', $date); ?>" <?php if($prev_val_date == date('d-m-Y', $date)){ echo "checked='checked'"; } ?>><?php echo date('l jS M Y', $date); ?></li>
														<?php endforeach; ?>
													</ul>
												<?php endif; ?>
												</div>
											</li>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							<?php endif; ?>
						</ul>
					<?php endforeach; ?>
					
					<div class="price-container">
						<p><strong>Extras Total Price:</strong> &pound;<span class="extras-price"><?php echo $extras_price; ?></span></p>
						<p><strong>Total Booking Price:</strong> &pound;<span class="total-price"><?php echo $total_price; ?></span></p>
					</div>
					
					<input type="submit" id="submit" name="submit" value="Update extras on your booking" />
				</form>
			</div>	
		</section>
	</div>
	
	<?php $this->load->view("_includes/frontend/footer"); ?>
</div>
<?php $this->load->view("_includes/frontend/js"); ?>