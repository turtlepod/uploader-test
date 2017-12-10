<?php
$gallery = get_option( 'mycut', array() );
$gallery = is_array( $gallery ) ? $gallery : array();
?>

<div class="gallery-images">
	<?php foreach ( $gallery as $attachment_id ) : ?>

		<?php $img = wp_get_attachment_image( $attachment_id ); ?>

		<?php if ( $img ) : ?>
			<div class="gallery-image">
				<?php echo $img; ?>
				<input type="hidden" name="ids[]" value="<?php echo esc_attr( $attachment_id ); ?>">
				<a class="image-remove" href="#"><span class="dashicons dashicons-no-alt"></span></a>
			</div><!-- .review-gallery-image -->
		<?php endif; ?>

	<?php endforeach; ?>
</div><!-- .gallery-images -->

<p><input id="add-images" type="file" multiple="multiple" name="gallery[]" accept="image/*"></p>

<div id="preview"></div>