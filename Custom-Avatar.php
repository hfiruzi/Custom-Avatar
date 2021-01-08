<?php
/*
Plugin Name: Custom Avatar
Plugin URI: heycode.ir
Description: Custom Avatar For Wp
Author: Hamid Firuzi
Author URI: https://heycode.ir
Version: 1.0.0
*/

if ( !defined('ABSPATH') ) {
    exit;
}


/**
 * Custom Avatar Plugin PLs Read Comment
 */

add_action( "admin_enqueue_scripts", "heycode_enqueue" );
function heycode_enqueue( $hook ){
	// Load scripts only on the profile page.
	if( $hook === 'profile.php' || $hook === 'user-edit.php' ){
		add_thickbox();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_media();
	}
}
// 2. Scripts for Media Uploader.
function heycode_admin_media_scripts() {
	?>
	<script>
		jQuery(document).ready(function ($) {
			$(document).on('click', '.avatar-image-upload', function (e) {
				e.preventDefault();
				var $button = $(this);
				var file_frame = wp.media.frames.file_frame = wp.media({
					title: 'Select or Upload an Custom Avatar',
					library: {
						type: 'image' // mime type
					},
					button: {
						text: 'Select Avatar'
					},
					multiple: false
				});
				file_frame.on('select', function() {
					var attachment = file_frame.state().get('selection').first().toJSON();
					$button.siblings('#heycode-custom-avatar').val( attachment.sizes.thumbnail.url );
					$button.siblings('.custom-avatar-preview').attr( 'src', attachment.sizes.thumbnail.url );
				});
				file_frame.open();
			});
		});
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts-profile.php', 'heycode_admin_media_scripts' );
add_action( 'admin_print_footer_scripts-user-edit.php', 'heycode_admin_media_scripts' );




// 3. Adding the Custom Image section for avatar.
function custom_user_profile_fields( $profileuser ) {
	?>
	<h3><?php _e('Custom Local Avatar', 'heycode'); ?></h3>
	<table class="form-table heycode-avatar-upload-options">
		<tr>
			<th>
				<label for="image"><?php _e('Custom Local Avatar', 'heycode'); ?></label>
			</th>
			<td>
				<?php
				// Check whether we saved the custom avatar, else return the default avatar.
				$custom_avatar = get_the_author_meta( 'heycode-custom-avatar', $profileuser->ID );
				if ( $custom_avatar == '' ){
					$custom_avatar = get_avatar_url( $profileuser->ID );
				}else{
					$custom_avatar = esc_url_raw( $custom_avatar );
				}
				?>
				<img style="width: 96px; height: 96px; display: block; margin-bottom: 15px;" class="custom-avatar-preview" src="<?php echo $custom_avatar; ?>">
				<input type="text" name="heycode-custom-avatar" id="heycode-custom-avatar" value="<?php echo esc_attr( esc_url_raw( get_the_author_meta( 'heycode-custom-avatar', $profileuser->ID ) ) ); ?>" class="regular-text" />
				<input type='button' class="avatar-image-upload button-primary" value="<?php esc_attr_e("Upload Image","heycode");?>" id="uploadimage"/><br />
				<span class="description">
					<?php _e('Please upload a custom avatar for your profile, to remove the avatar simple delete the URL and click update.', 'heycode'); ?>
				</span>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'custom_user_profile_fields', 10, 1 );
add_action( 'edit_user_profile', 'custom_user_profile_fields', 10, 1 );



// 4. Saving the values.
add_action( 'personal_options_update', 'heycode_save_local_avatar_fields' );
add_action( 'edit_user_profile_update', 'heycode_save_local_avatar_fields' );
function ayecode_save_local_avatar_fields( $user_id ) {
	if ( current_user_can( 'edit_user', $user_id ) ) {
		if( isset($_POST[ 'heycode-custom-avatar' ]) ){
			$avatar = esc_url_raw( $_POST[ 'heycode-custom-avatar' ] );
			update_user_meta( $user_id, 'heycode-custom-avatar', $avatar );
		}
	}
}



// 5. Set the uploaded image as default gravatar.
add_filter( 'get_avatar_url', 'heycode_get_avatar_url', 10, 3 );
function heycode_get_avatar_url( $url, $id_or_email, $args ) {
	$id = '';
	if ( is_numeric( $id_or_email ) ) {
		$id = (int) $id_or_email;
	} elseif ( is_object( $id_or_email ) ) {
		if ( ! empty( $id_or_email->user_id ) ) {
			$id = (int) $id_or_email->user_id;
		}
	} else {
		$user = get_user_by( 'email', $id_or_email );
		$id = !empty( $user ) ?  $user->data->ID : '';
	}
	//Preparing for the launch.
	$custom_url = $id ?  get_user_meta( $id, 'heycode-custom-avatar', true ) : '';

	// If there is no custom avatar set, return the normal one.
	if( $custom_url == '' || !empty($args['force_default'])) {
		return esc_url_raw( 'https://switchgearcontent.com/wp-content/uploads/2021/01/avatar.jpg' );
	}else{
		return esc_url_raw($custom_url);
	}
}
