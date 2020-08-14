<div class="latepoint-booking-params-w">
	<?php 
	$add_string_to_id = '_'.OsUtilHelper::random_text('hexdec', 8);

	echo '<div class="latepoint-restrictions-params">';
		echo OsFormHelper::hidden_field('restrictions[show_locations]', $restrictions['show_locations'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[show_agents]', $restrictions['show_agents'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[show_services]', $restrictions['show_services'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[show_service_categories]', $restrictions['show_service_categories'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[selected_service]', $restrictions['selected_service'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[selected_service_category]', $restrictions['selected_service_category'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[selected_location]', $restrictions['selected_location'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[selected_agent]', $restrictions['selected_agent'], ['skip_id' => true]);
		echo OsFormHelper::hidden_field('restrictions[calendar_start_date]', $restrictions['calendar_start_date'], ['skip_id' => true]);
	echo '</div>';

	echo OsFormHelper::hidden_field('booking[generated_form_id]', $booking->generated_form_id, [ 'class' => 'latepoint_agent_id', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('current_step', $current_step, ['class' => 'latepoint_current_step', 'skip_id' => true]);
	echo OsFormHelper::hidden_field('step_direction', 'next', ['class' => 'latepoint_step_direction', 'skip_id' => true]); 

	do_action('latepoint_booking_params', $booking, $restrictions, $current_step, $add_string_to_id);
	?>
</div>