<div class="os-row">
  <div class="os-col-6">
    <?php if($default_fields_for_customer['first_name']['active']) echo OsFormHelper::text_field('customer[first_name]', __('First Name', 'latepoint'), $selected_customer->first_name); ?>
  </div>
  <div class="os-col-6">
    <?php if($default_fields_for_customer['last_name']['active']) echo OsFormHelper::text_field('customer[last_name]', __('Last Name', 'latepoint'), $selected_customer->last_name); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php echo OsFormHelper::text_field('customer[email]', __('Email Address', 'latepoint'), $selected_customer->email); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php if($default_fields_for_customer['phone']['active']) echo OsFormHelper::text_field('customer[phone]', __('Telephone Number', 'latepoint'), $selected_customer->formatted_phone, array('class' => 'os-mask-phone')); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php if($default_fields_for_customer['notes']['active']) echo OsFormHelper::textarea_field('customer[notes]', __('Customer Notes', 'latepoint'), $selected_customer->notes, ['rows' => 1]); ?>
  </div>
</div>
<div class="os-row">
  <div class="os-col-12">
    <?php echo OsFormHelper::textarea_field('customer[admin_notes]', __('Notes only visible to admins', 'latepoint'), $selected_customer->admin_notes, ['rows' => 1]); ?>
  </div>
</div>
<?php do_action('latepoint_customer_quick_edit_form_after', $selected_customer); ?>

<?php echo OsFormHelper::hidden_field('booking[customer_id]', $selected_customer->id); ?>