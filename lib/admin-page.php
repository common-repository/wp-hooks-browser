<div class="wrap">
	<h2 class="hb-page-heading"><?php echo __('Hooks Browser', 'hb'); ?></h2>

	<form method="post" action="<?php echo admin_url('admin.php?page=hb-hooks-browser'); ?>">
	
		<?php settings_fields( 'hooks-browser-settings' ); ?>
		
		<div class="form-table">

			<div class="hb-field-wrap">
				<label class="hb-label">
					<?php echo __('Theme or Plugin ?', 'hb'); ?>
				</label>
				<div class="hb-single-field">
					<select type="select" name="hb_where_to_look" id="hb_where_to_look">
						<option value="both" <?php selected( $_POST['hb_where_to_look'], 'both' ); ?> ><?php echo __('Everywhere', 'hb'); ?></option>
						<option value="plugins"  <?php selected( $_POST['hb_where_to_look'], 'plugins' ); ?>><?php echo __('Plugin', 'hb'); ?></option>
						<option value="themes" <?php selected( $_POST['hb_where_to_look'], 'themes' ); ?>><?php echo __('Theme', 'hb'); ?></option>
					</select>
				</div>
			</div>

			<div class="hb-field-wrap">
				<label class="hb-label">
					<?php echo __('Choose Theme ..', 'hb'); ?>
				</label>
				<div class="hb-single-field">
					<select type="select" name="hb_which_theme" id="hb_which_theme">
						<?php
							 foreach( wp_get_themes( $args ) as $hb_theme_slug	=>	$hb_theme_object) {
							 	 echo '<option value="'.$hb_theme_slug.'" '.selected( $_POST['hb_which_theme'], $hb_theme_slug,false ).'>'.$hb_theme_object->display('Name').'</option>';
							 }
						?>
					</select>
				</div>
			</div>

			<div class="hb-field-wrap">
				<label class="hb-label">
					<?php echo __('Choose Plugin ..', 'hb'); ?>
				</label>
				<div class="hb-single-field">
					<select type="select" name="hb_which_plugin" id="hb_which_plugin">
						<?php
							 foreach( get_plugins() as $plugin_file	=>	$plugin_data) {
							 	 echo '<option value="'.explode('/',$plugin_file)[0].'" '.selected( $_POST['hb_which_plugin'], explode('/',$plugin_file)[0],false ).'>'.$plugin_data['Name'].'</option>';
							 }
						?>
					</select>
				</div>
			</div>

			<div class="hb-field-wrap">
				<label class="hb-label">
					<?php echo __('Look For ..', 'hb'); ?>
				</label>
				<div class="hb-single-field">
					<select type="select" name="hb_actions_or_filters" id="hb_actions_or_filters">
						<option value="all" <?php selected( $_POST['hb_actions_or_filters'], 'all' ); ?> ><?php echo __('All', 'hb'); ?></option>
						<option value="do_action" <?php selected( $_POST['hb_actions_or_filters'], 'do_action' ); ?>><?php echo __('Defined Actions', 'hb'); ?></option>
						<option value="apply_filters" <?php selected( $_POST['hb_actions_or_filters'], 'apply_filters' ); ?>><?php echo __('Defined Filters', 'hb'); ?></option>
						<option value="add_action" <?php selected( $_POST['hb_actions_or_filters'], 'add_action' ); ?>><?php echo __('Used Actions', 'hb'); ?></option>
						<option value="add_filter" <?php selected( $_POST['hb_actions_or_filters'], 'do_filter' ); ?>><?php echo __('Used Filters', 'hb'); ?></option>
						<option value="string" <?php selected( $_POST['hb_actions_or_filters'], 'string' ); ?>><?php echo __('String', 'hb'); ?></option>
					</select>
				</div>
			</div>

			<div class="hb-field-wrap">
				<label class="hb-label">
					<?php echo __('String', 'hb'); ?>
				</label>
				<div class="hb-single-field">
					<input type="text" class="hb-string-val" name="hb_string_val" />
				</div>
			</div>

		</div>
		<div class="hb-field-wrap">
			<?php submit_button('Browse'); ?>
		</div>
	</form>
	<div class="hb-hooks-list">
		<?php
			WPHB()->list_hooks();
		?>
	</div>
</div>
