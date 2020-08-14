<div class="os-row">
  <?php if($default_fields_for_customer['first_name']['active']) echo OsFormHelper::text_field('customer[first_name]', __('Your First Name', 'latepoint'), $customer->first_name, array('class' => $default_fields_for_customer['first_name']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['first_name']['width'])); ?>
  <?php if($default_fields_for_customer['last_name']['active'])echo OsFormHelper::text_field('customer[last_name]', __('Your Last Name', 'latepoint'), $customer->last_name, array('class' => $default_fields_for_customer['last_name']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['last_name']['width'])); ?>
  <?php if($default_fields_for_customer['phone']['active'])echo OsFormHelper::text_field('customer[phone]', __('Your Phone Number', 'latepoint'), $customer->formatted_phone, array('class' => $default_fields_for_customer['phone']['required'] ? 'required os-mask-phone ' : 'os-mask-phone'), array('class' => $default_fields_for_customer['phone']['width'].' os-col-sm-12')); ?>
  <?php echo OsFormHelper::text_field('customer[email]', __('Your Email Address', 'latepoint'), $customer->email, array('class' => 'required'), array('class' => $default_fields_for_customer['email']['width'].' os-col-sm-12')); ?>
  <?php if(($customer->is_new_record() || $customer->is_guest) && OsSettingsHelper::is_on('steps_require_setting_password')){
		echo OsFormHelper::password_field('customer[password]', __('Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-6'));
		echo OsFormHelper::password_field('customer[password_confirmation]', __('Confirm Password', 'latepoint'), '', array('class' => 'required'), array('class' => 'os-col-6'));
  } ?>
  <?php if($default_fields_for_customer['notes']['active']) echo OsFormHelper::textarea_field('customer[notes]', __('Add Comments', 'latepoint'), $customer->notes, array('class' => $default_fields_for_customer['notes']['required'] ? 'required' : ''), array('class' => $default_fields_for_customer['notes']['width'])); ?>
  <?php do_action('latepoint_booking_steps_contact_after', $customer); ?>
</div>