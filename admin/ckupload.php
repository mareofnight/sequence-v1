<?

/**
 * This file is an add-on to ckeditor to allow the upload file function to work.
 * 
 * Based on code from the following sources:
 *    http://www.caeus.com/articles/how-to-add-and-upload-an-image-using-ckeditor/
 *    http://forums.hotarucms.org/showthread.php?1882-alternative-to-image_upload-plugin-gt-upload-images-with-ckeditor
 *
 * Written by Kim Desorcie, 2013. (I can't really say "copyright" here because most of this code is borrowed.)
 **/

 include_once(realpath(__dir__).'/../business/sq_session.php');// keep the current session going
require_once(realpath(__dir__).'/../business/sq_utils.php');
require_once(realpath(__dir__).'/../business/sq_do.php');

$relative_path = '/media/images/'.date('Y-m-d_h-i')."_".$_FILES['upload']['name'];
$url = sq_utils::get_uri().$relative_path;
$location = __dir__.'/..'.$relative_path;

$allowedImageTypes = array("image/pjpeg","image/jpeg","image/jpg","image/png","image/x-png","image/gif"); 

//extensive suitability check before doing anything with the file...
   if (!sq_do::check_permission('upload_media')) {
      $message = "You do not have permission to upload media.";
   }
   else if (($_FILES['upload'] == "none") OR (empty($_FILES['upload']['name'])) ) {
      $message = "No file uploaded.";
   }
   else if ($_FILES['upload']["size"] == 0) {
      $message = "The file has no content.";
   }
   else if (!in_array($_FILES['upload']["type"], $allowedImageTypes)) {
      $message = "The image must be in JPG, PNG or GIF format.";
   }
   else if (!is_uploaded_file($_FILES['upload']["tmp_name"])) {
      $message = "Error - please try again.";
   }
   else {// try to actually upload the file
      $message = "";
      $move = @ move_uploaded_file($_FILES['upload']['tmp_name'], $location);
      if($move == false) {
         $message = "Error uploading the file.";
      }
      else {// double-check the file type (the other check can be spoofed, this one should be harder to fake)
         list($width, $height, $type, $attr) = getimagesize($location); 
         $fileType = image_type_to_mime_type($type);
         if (!in_array($fileType, $allowedImageTypes)) {  
           $warning= "The image must be in JPG, PNG or GIF format."; 
           unlink($location); // Delete unsupported file 
         }
      }
   }
 
$funcNum = $_GET['CKEditorFuncNum'] ;
echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";

/*

Not Found

The requested URL /sequenceexample/ckupload.php was not found on this server.

Additionally, a 404 Not Found error was encountered while trying to use an ErrorDocument to handle the request.


 */


?>