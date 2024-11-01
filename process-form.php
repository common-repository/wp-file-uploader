<?php
	/**
	 * Scan folder for new images
	 * @return array $files list of image filenames 
	 */
	function scandirectory( $dirname = '.' ) { 
		$ext = array('jpeg', 'jpg', 'png', 'gif'); 

		$files = array(); 
		if( $handle = opendir( $dirname ) ) { 
			while( false !== ( $file = readdir( $handle ) ) ) {
				$info = pathinfo( $file );
				// just look for images with the correct extension
                if ( isset($info['extension']) )
				    //if ( in_array( strtolower($info['extension']), $ext) )
					   $files[] = utf8_encode( $file );
			}		
			closedir( $handle ); 
		} 
		sort( $files );
		return ( $files ); 
	} 
	 

	/**
	 * Slightly modfifed version of pathinfo(), clean up filename & rename jpeg to jpg
	 * 
	 * @param string $name The name being checked. 
	 * @return array containing information about file
	 */
	function fileinformation( $name ) {
		
		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_file_name($name);
		
		//get the parts of the name
		$filepart = pathinfo ( strtolower($name) );
		
		if ( empty($filepart) )
			return false;
		
		// required until PHP 5.2.0
		if ( empty($filepart['filename']) ) 
			$filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );
		
		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );
		
		//extension jpeg will not be recognized by the slideshow, so we rename it
		$filepart['extension'] = ($filepart['extension'] == 'jpeg') ? 'jpg' : $filepart['extension'];
		
		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];
		
		return $filepart;
	}
	
//$ext = substr($fileName, strrpos($fileName, '.') + 1);
//$ext = substr(strrchr($fileName, '.'), 1);


if ( isset($_POST['postsubmit']) and $_POST['postsubmit'] != '' )
{

include_once("../../../wp-config.php");

include_once ('captcha.php');
$captcha_instance = new ReallySimpleCaptcha();

$the_answer_from_respondent = $_POST['captcha'];
$prefix = $_POST['prefix'];
$correct = $captcha_instance->check($prefix, $the_answer_from_respondent);
$captcha_instance->remove($prefix);


if ($correct or is_user_logged_in() ) {

$err = "";
$msg = "";

	if ($_FILES["postimage"]["name"] != ''){
	
		$dir = '../../uploads/';
		if (!is_dir($dir)){
			mkdir($dir, 0777);
		}
	
		$file_path = $dir;
		
		$imageslist = scandirectory( $file_path );
	
		  if ($_FILES["postimage"]["error"] > 0)
			{
			$err .= "Return Code: " . $_FILES["postimage"]["error"] . "<br />";
			}
		  else
			{
				$filepart = fileinformation( $_FILES['postimage']['name'] );
				$filename = $filepart['basename'];
				// check if this filename already exist in the folder
				$i = 2;
				while ( in_array( $filename, $imageslist ) ) {
					$filename = $filepart['filename'] . '_' . $i++ . '.' .$filepart['extension'];
				}
				  move_uploaded_file($_FILES["postimage"]["tmp_name"], $file_path.$filename);
			}
		  $file_url = get_bloginfo('siteurl')."/wp-content/uploads/";
	}
	else {
		wp_die( __('<strong>Sorry</strong>, you did not select file to upload.') );
	}
	


$to = get_bloginfo('admin_email');
$subject = get_bloginfo('name').' user upload a file.';
$message = $_POST['postcontent'];
$attachments = array(WP_CONTENT_DIR . '/uploads/'.$filename);

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'To: Admin <'.get_bloginfo('admin_email').'>' . "\r\n";
//$headers .= 'From: '.get_bloginfo('name').''. "\r\n";
//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

$res = @wp_mail($to, $subject, $message, $headers, $attachments);

if($res) {
$nonce= wp_create_nonce  ('file-uploader');

if( strpos($_POST['_wp_http_referer'],'?') > 0 )
	$location = $_POST['_wp_http_referer'].'&_wpnonce='.$nonce;
else
	$location = $_POST['_wp_http_referer'].'?_wpnonce='.$nonce;

wp_redirect($location);
}
else
{
	wp_die( __('<strong>Sorry</strong>, email cannot sent.') );
}

}
else
{
	wp_die( __('<strong>Sorry</strong>, Wrong captcha inputed') );
}
  
}
?>