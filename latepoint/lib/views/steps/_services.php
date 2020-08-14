<div class="step-services-w latepoint-step-content" data-step-name="services"  data-clear-action="clear_step_services">
  <div class="latepoint-step-content-text-centered">
    <h4><?php _e('Select Service Duration', 'latepoint'); ?></h4>
    <div><?php _e('You need to select service duration, the price of your service will depend on duration.', 'latepoint'); ?></div>
  </div>
  <div class="select-total-attendies-w style-centered">
    <div class="select-total-attendies-label">
      <h4><?php _e('How Many People?', 'latepoint'); ?></h4>
      <div class="sta-sub-label"><?php _e('Maximum capacity is', 'latepoint'); ?> <span>1</span></div>
    </div>
    <div class="total-attendies-selector-w">
      <div class="total-attendies-selector total-attendies-selector-minus"><i class="latepoint-icon latepoint-icon-minus"></i></div>
      <input type="text" name="booking[total_attendies]" class="total-attendies-selector-input latepoint_total_attendies" value="<?php echo $booking->total_attendies; ?>" placeholder="<?php _e('Qty', 'latepoint'); ?>">
      <div class="total-attendies-selector total-attendies-selector-plus"><i class="latepoint-icon latepoint-icon-plus"></i></div>
    </div>
  </div>
  <?php 
  if(OsSettingsHelper::steps_show_service_categories()){

    // Generate categorized services list
    OsBookingHelper::generate_services_and_categories_list(false, $show_service_categories_arr, $show_services_arr, $preselected_category);
  }else{
    OsBookingHelper::generate_services_list($services);
  } ?>
  <?php 
    echo OsFormHelper::hidden_field('booking[service_id]', $booking->service_id, [ 'class' => 'latepoint_service_id', 'skip_id' => true]);
    echo OsFormHelper::hidden_field('booking[duration]', $booking->duration, [ 'class' => 'latepoint_duration', 'skip_id' => true]);
  ?>
</div>