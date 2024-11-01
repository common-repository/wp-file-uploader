<?php
/*
Plugin Name: File Uploader
Plugin URI: http://samrat131.wordpress.com/
Description: Using file uploader user can upload any file with description, blog admin/site owner will get a email notification of uploaded file with attachment.
Version: 1.1
Author: samrat131
Author URI: http://samrat131.wordpress.com/
License: GPL2
*/

function file_upload() {

if($_REQUEST['_wpnonce'] != '' and isset ($_REQUEST['_wpnonce']) ) {
	$nonce=$_REQUEST['_wpnonce'];
	if (! wp_verify_nonce($nonce, 'file-uploader') ) die('Security check');
	else {
		return '<table width="400" border="0" cellpadding="1" cellspacing="0">
		<tr>
			<td><h2>File uploaded and email sent to admin. Thanks!</h2></td>
		</tr>
	</table>';
	}

}
else
{
ob_start();
?>
	
<form action="<?php bloginfo('siteurl'); ?>/wp-content/plugins/wp-file-uploader/process-form.php" method="post" enctype="multipart/form-data">
	<?php wp_nonce_field('update-options'); ?>
	
	<table width="400" border="0" cellpadding="5" cellspacing="0">
		<tr>
			<td><h2>Upload a file</h2></td>
		</tr>
		<tr>
			<td>Description about file</td>
		</tr>
		<tr>
			<td colspan="2">
			<textarea id="postcontent" name="postcontent" rows="5" cols="50"></textarea>
			</td>
		</tr>
		<?php if ( !is_user_logged_in() ) { ?>
		<tr>
			<td colspan="2">
			<?php
			include_once ('captcha.php');
			$captcha_instance = new ReallySimpleCaptcha();
			// Change the background color of CAPTCHA image to black
			$captcha_instance->bg = array(255, 255, 255);
			$word = $captcha_instance->generate_random_word();
			$prefix = mt_rand();
			//$correct = $captcha_instance->check($prefix, $the_answer_from_respondent);
			//$captcha_instance->remove($prefix);
			?><div style="height:20px; float:left"><img src="<?php echo get_option('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__)).'/tmp/'.$captcha_instance->generate_image($prefix, $word);?>" /></div><div style="height:24px;"><input id="captcha" name="captcha" type="text" value=""></div><input type="hidden" id="prefix" name="prefix" value="<?php echo $prefix;?>"></td>
		</tr>
		<?php } ?>
		<tr>
			<td><input type="file" id="postimage" name="postimage" /></td>
			<td align="right"><input value="Upload" type="submit" id="postsubmit" name="postsubmit" />
			</td>
		</tr>
	</table>
</form>
<?php
$out= ob_get_contents();
ob_end_clean();
return $out;
}
}

add_shortcode ('file-upload', 'file_upload');
?>