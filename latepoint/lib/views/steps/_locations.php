<div class="step-locations-w latepoint-step-content" data-step-name="locations" data-clear-action="clear_step_locations">
  <div class="os-locations os-animated-parent os-items os-selectable-items os-as-rows">
    <?php foreach($locations as $location){ ?>
      <div class="os-animated-child os-item os-selectable-item <?php if(!empty($location->full_address)) echo 'with-description'; ?>"
          data-summary-field-name="location" 
          data-summary-value="<?php echo esc_attr($location->name); ?>" 
          data-id-holder=".latepoint_location_id"
          data-item-id="<?php echo $location->id; ?>">
        <div class="os-animated-self os-item-i">
          <div class="os-item-img-w" style="background-image: url(<?php echo $location->selection_image_url; ?>);"></div>
          <div class="os-item-name-w">
            <div class="os-item-name"><?php echo $location->name; ?></div>
            <?php if($location->full_address){ ?>
              <div class="os-item-desc"><?php echo $location->full_address; ?></div>
            <?php } ?>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
  <?php echo OsFormHelper::hidden_field('booking[location_id]', $booking->location_id, [ 'class' => 'latepoint_location_id', 'skip_id' => true]); ?>
</div>