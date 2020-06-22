<?php
/**
 * Actions required
 */

wp_enqueue_style( 'plugin-install' );
wp_enqueue_script( 'plugin-install' );
wp_enqueue_script( 'updates' );
wp_localize_script( 'updates', '_wpUpdatesItemCounts', array(
		'totals'  => wp_get_update_data(),
	) );
?>

<div class="feature-section action-required demo-import-boxed" id="plugin-filter">

	<?php
	global $illdy_required_actions, $illdy_recommended_plugins;

	if ( ! empty( $illdy_required_actions ) ):

		/* illdy_show_required_actions is an array of true/false for each required action that was dismissed */
		$illdy_show_required_actions = get_option( "illdy_show_required_actions" );
		$hooray = true;

		$nr_actions_required = 0;
		$nr_action_dismissed = 0;
		foreach ( $illdy_required_actions as $illdy_required_action_key => $illdy_required_action_value ):
			$hidden = false;
			if ( @$illdy_show_required_actions[ $illdy_required_action_value['id'] ] === false ) {
				$hidden = true;
			}
			if ( @$illdy_required_action_value['check'] ) {
				continue;
			}

			$nr_actions_required ++;
			if ( $hidden ) {
				$nr_action_dismissed ++;
			}
			?>
			<div class="illdy-action-required-box">
				<?php if ( ! $hidden ): ?>
					<span data-action="dismiss" class="dashicons dashicons-visibility illdy-required-action-button"
					      id="<?php echo $illdy_required_action_value['id']; ?>"></span>
				<?php else: ?>
					<span data-action="add" class="dashicons dashicons-hidden illdy-required-action-button" id="<?php echo $illdy_required_action_value['id']; ?>"></span>
				<?php endif; ?>
				<h3><?php if ( ! empty( $illdy_required_action_value['title'] ) ): echo $illdy_required_action_value['title']; endif; ?></h3>
				<p>
					<?php if ( ! empty( $illdy_required_action_value['description'] ) ): echo $illdy_required_action_value['description']; endif; ?>
					<?php if ( ! empty( $illdy_required_action_value['help'] ) ): echo '<br/>' . $illdy_required_action_value['help']; endif; ?>
				</p>
				<?php
				if ( ! empty( $illdy_required_action_value['plugin_slug'] ) ) {
					$active = $this->check_active( $illdy_required_action_value['plugin_slug'] );
					if ( !isset($active['plugin_path']) ) {
						$active['plugin_path'] = '';
					}

					if ( $active['needs'] == 'deactivate' && !MT_Notify_System::check_plugin_update( $illdy_required_action_value['plugin_slug'] ) ) {
						$active['needs'] = 'update';
					}

					$url    = $this->create_action_link( $active['needs'], $illdy_required_action_value['plugin_slug'], $active['plugin_path'] );
					$label  = '';

					switch ( $active['needs'] ) {
						case 'install':
							$class = 'install-now button';
							$label = __( 'Install', 'illdy' );
							break;
						case 'activate':
							$class = 'activate-now button button-primary';
							$label = __( 'Activate', 'illdy' );
							break;
						case 'update':
							$class = 'update-now button button-primary';
							$label = __( 'Update', 'illdy' );
							break;
						case 'deactivate':
							$class = 'deactivate-now button';
							$label = __( 'Deactivate', 'illdy' );
							break;
					}

					?>
					<p class="plugin-card-<?php echo esc_attr( $illdy_required_action_value['plugin_slug'] ) ?> action_button <?php echo ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ?>">
						<a data-slug="<?php echo esc_attr( $illdy_required_action_value['plugin_slug'] ) ?>"
							data-plugin = "<?php echo esc_attr( $active['plugin_path'] ) ?>"
						   class="<?php echo $class; ?>"
						   href="<?php echo esc_url( $url ) ?>"> <?php echo $label ?> </a>
					</p>
					<?php
				};
				?>
			</div>
			<?php
			$hooray = false;
		endforeach;
	endif;

	$nr_recommended_plugins = 0;
	if ( $nr_actions_required == 0 || $nr_actions_required == $nr_action_dismissed ):

		$illdy_show_recommended_plugins = get_option( "illdy_show_recommended_plugins" );
		foreach ( $illdy_recommended_plugins as $slug => $plugin_opt ) {
			
			if ( !$plugin_opt['recommended'] ) {
				continue;
			}

			if ( MT_Notify_System::has_import_plugin( $slug ) ) {
				continue;
			}
			if ( $nr_recommended_plugins == 0 ) {
				echo '<h3 class="hooray">' . __( 'Hooray! There are no required actions for you right now. But you can make your theme more powerful with next actions: ', 'illdy' ) . '</h3>';
			}

			$nr_recommended_plugins ++;
			echo '<div class="illdy-action-required-box">';

			if ( isset($illdy_show_recommended_plugins[$slug]) && $illdy_show_recommended_plugins[$slug] ): ?>
				<span data-action="add" class="dashicons dashicons-hidden illdy-recommended-plugin-button"
				      id="<?php echo esc_attr( $slug ); ?>"></span>
			<?php else: ?>
				<span data-action="dismiss" class="dashicons dashicons-visibility illdy-recommended-plugin-button"
				      id="<?php echo esc_attr( $slug ); ?>"></span>
			<?php endif;

			$active = $this->check_active( $slug );
			$url    = $this->create_action_link( $active['needs'], $slug );
			$info   = $this->call_plugin_api( $slug );
			$label  = '';
			$class = '';
			switch ( $active['needs'] ) {
				case 'install':
					$class = 'install-now button';
					$label = __( 'Install', 'illdy' );
					break;
				case 'activate':
					$class = 'activate-now button button-primary';
					$label = __( 'Activate', 'illdy' );
					break;
				case 'deactivate':
					$class = 'deactivate-now button';
					$label = __( 'Deactivate', 'illdy' );
					break;
			}
			?>
			<h3><?php echo $label .': '.$info->name ?></h3>
			<p>
				<?php echo $info->short_description ?>
			</p>
			<p class="plugin-card-<?php echo esc_attr( $slug ) ?> action_button <?php echo ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ?>">
				<a data-slug="<?php echo esc_attr( $slug ) ?>"
				   class="<?php echo $class; ?>"
				   href="<?php echo esc_url( $url ) ?>"> <?php echo $label ?> </a>
			</p>
			<?php

			echo '</div>';

		}

	endif;

	if ( $hooray && $nr_recommended_plugins == 0 ):
		echo '<span class="hooray">' . __( 'Hooray! There are no required actions for you right now.', 'illdy' ) . '</span>';
	endif;
	?>

</div>
