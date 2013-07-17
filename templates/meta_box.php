<?php wp_nonce_field( 'se_save_post', 'se_save_meta_box'); ?>

<p>
	<label>
		<input type="checkbox" name="se_exclude_from_search" <?php echo ($excluded == 'on' ? 'checked="checked"' : ''); ?> /> Exclude from search results
	</label>
</p>
