<?php if(!$is_compact) echo VP_View::instance()->load('control/template_control_head', $head_info); ?>

<input type="text" name="<?php echo $name; ?>" class="vp-input slideinput vp-js-tipsy" original-title="Range between <?php echo $opt_raw['min']; ?> and <?php echo $opt_raw['max']; ?>" value="<?php echo esc_attr( $value ); ?>" />
<div class="vp-js-slider slidebar" id="<?php echo $name; ?>" data-vp-opt="<?php echo esc_attr( $opt ); ?>"></div>

<?php if(!$is_compact) echo VP_View::instance()->load('control/template_control_foot'); ?>