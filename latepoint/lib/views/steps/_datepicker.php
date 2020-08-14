<div class="step-datepicker-w latepoint-step-content" data-step-name="datepicker"  data-clear-action="clear_step_datepicker">
  <div class="os-dates-w">
    <?php OsBookingHelper::generate_monthly_calendar($calendar_start_date, ['total_attendies' => $booking->total_attendies, 
                                                                            'timeshift_minutes' => OsTimeHelper::get_timezone_shift_in_minutes(OsTimeHelper::get_timezone_name_from_session()),
                                                                            'service_id' => $booking->service_id, 
                                                                            'agent_id' => $booking->agent_id, 
                                                                            'location_id' => $booking->location_id, 
                                                                            'duration' => $booking->get_total_duration()]); ?>
  </div>
  <div class="time-selector-w">
    <div class="times-header"><?php _e('Pick Appointment Time For', 'latepoint'); ?> <span></span></div>
    <div class="os-times-w">
      <div class="timeslots"></div>
    </div>
  </div>
  <?php
  echo OsFormHelper::hidden_field('booking[start_date]', $booking->start_date, [ 'class' => 'latepoint_start_date', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('booking[start_time]', $booking->start_time, [ 'class' => 'latepoint_start_time', 'skip_id' => true]);
  ?>
</div>