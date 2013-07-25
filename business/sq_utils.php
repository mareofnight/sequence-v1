<?php

require_once(realpath(__dir__).'/sq_do.php');

class sq_utils {

/**
 * Get the URI of the directory that Sequence is installed in (for use in links within the site)
 * @param $protocol whether or not to include http or https
 * @return URI of Sequence
 **/
static function get_uri($protocol = true) {
    if ($protocol) {
        if (isset($_SERVER['HTTPS'])) {$start = 'https://'; }
        else { $start = 'http://'; }
    }
    else { $start = ''; }
    $uri = str_replace(filter_var($_SERVER['DOCUMENT_ROOT'], FILTER_SANITIZE_URL),
                       $start.filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL),
                       realpath(__dir__));
    $uri = str_replace('/business', '', $uri);
    return $uri;
}


/**
 * Send an email from the website
 * @param $email address to send the message to
 * @param $subject the subject line
 * @param $message the email message body
 * @return true if sent, otherwise false
 **/
static function send_mail($email, $subject, $message) {
    if (isset($_SERVER['HTTP_HOST'])) {
        $domain = self::sanitize_basic($_SERVER['HTTP_HOST']);
    }
    else {
        $domain = self::sanitize_basic($_SERVER['SERVER_NAME']);
    }
    
    $success = mail($email, $subject, $message, 'From: Sequence@'.$domain);
    return $success;
}

static function gen_password() {
    $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($chars), 0, 9).substr(str_shuffle($chars), 0, 9);
}


/**
 * Apply hashing algorithms to a string.
 * @param $string the string to hash
 * @return the hashed string
 **/
static function hash($string) {
    $string = crypt($string, CRYPT_BLOWFISH);
    return $string;
}

/**
 * Convert a time string in the format Y-m-d h:i (0000-00-00 00:00) to a unix timestamp,
 *      using the timezone stored in site settings
 * @param $string time string
 * @param $useTimezone boolean - true to use timezone (if converting a user input time), false not to (if converting a time from the database)
 * @return unix timestamp (without the '@'), or false on failure
 **/
static function to_unixtime($string, $useTimezone = true) {
    try {
        if ($useTimezone) {
            $timezone = sq_do::get_setting('timezone');
            $datetime = new DateTime($string, new DateTimeZone($timezone));
        }
        else {
            $datetime = new DateTime($string);
        }
        $datetime->setTimezone(new DateTimeZone('UTC'));
    }
    catch (Exception $x) {
        return false;
    }
    
    return $datetime->getTimestamp();
}

/**
 * Convert a unix timestamp into a string in the format Y-m-d h:i (0000-00-00 00:00),
 *      using the timezone stored in site settings
 * @param $unix unix timestamps
 * @param $format custom time format (optional)
 * @return the time as a string in the format Y-m-d h:i (0000-00-00 00:00), or false on failure
 **/
static function from_unixtime($unix, $format = null) {
    try {
        if (strcmp(substr($unix, 0, 1), '@')!=0) { $unixTimestamp = '@'.$unix; }
        $timezone = new DateTimeZone(sq_do::get_setting('timezone'));
        $datetime = new DateTime($unixTimestamp);
        if ($unix > 0) {
            $datetime->setTimezone($timezone);
        }
    }
    catch (Exception $x) {
        return false;
    }
    
    if (self::exists($format) == false) {
        $format = 'Y-m-d h:i';
    }
    if ($unix == 0) {
        return 0;
    }
    else {
        return $datetime->format($format);
    }
}


/**
 * Upload an image file to the image file directory
 * @param $name the name of the file in the $_FILES array
 * @param $type the file type (options: image, ...)
 * @return error message if there is an error, otherwise the file name
 **/
static function upload_file($name, $type = 'image') {echo 'test';
    $relative_path = '/media/images/'.date('Y-m-d_h-i')."_".$_FILES[$name]['name'];
    $location = __dir__.'/..'.$relative_path;
    $valid_types = array('image');
    $allowedImageTypes = array("image/pjpeg","image/jpeg","image/jpg","image/png","image/x-png","image/gif");
    if (!in_array($type, $valid_types)) {
        return "Invalid file type";
    }
    if (!sq_do::check_permission('upload_media')) {
        $message = "You do not have permission to upload media";
    }
    else if (($_FILES[$name] == "none") OR (empty($_FILES[$name]['name'])) ) {
        $message = "No file uploaded";
    }
    else if ($_FILES[$name]["size"] == 0) {
        $message = "The file has no content";
    }
    else if (!in_array($_FILES[$name]["type"], $allowedImageTypes) && strcmp($type, 'image') == 0) {
        $message = "The image must be in JPG, PNG or GIF format";
    }
    else if (!is_uploaded_file($_FILES[$name]["tmp_name"])) {
        $message = "Error - please try again";
    }
    else {// try to actually upload the file
        $message = "";
        $move = @ move_uploaded_file($_FILES[$name]['tmp_name'], $location);
        if($move == false) {
            $message = $location."Error uploading the file";
        }
        else {// double-check the file type (the other check can be spoofed, this one should be harder to fake)
            list($width, $height, $type, $attr) = getimagesize($location); 
            $fileType = image_type_to_mime_type($type);
            if (!in_array($fileType, $allowedImageTypes)) {  
                $message = "The image must be in JPG, PNG or GIF format"; 
                unlink($location); // Delete unsupported file 
            }
        }
    }
    if (strlen($message)>0) {
        return $message;
    }
    else {
        return ' '.$relative_path;
    }
}



/********************************************************************
 ********************          VALIDATE          ********************
 ********************************************************************/

static function exists($variable) {
    if (!isset($variable) || strlen(trim($variable)) == 0) {
        return false;
    }
    else {
        return true;
    }
}

/********************************************************************
 ********************          SANITIZE          ********************
 ********************************************************************/

/**
 * Prepare data to be displayed in the browser - HTML character entities, etc.
 * @param $string the string to sanitize
 * @param $html whether or not the string contains HTML tags that should be kept (default false)
 * @return the sanitized string
 **/
static function sanitize_out($string, $html=false) {
    if ($html) {
        $entities = get_html_translation_table(HTML_ENTITIES);
        unset($entities['"']);
        unset($entities['<']);
        unset($entities['>']);
        unset($entities['&']);
        $string = strtr($string, $entities);
    }
    else {
        $string = htmlentities($string);
    }
    return $string;
}

/**
 * Sanitize a string (this function is used by the other sanitization functions)
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_basic($string) {
    $string = trim($string);
    return $string;
}

/**
 * Sanitize an HTML string
 * Only allows the tags: 
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_html($string) {
    $string = self::sanitize_basic($string);
    return $string;
}

/**
 * Sanitize a text string (UTF-8) that may not include HTML tags
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_text($string) {
    $string = self::sanitize_basic($string);
    $string = strip_tags($string);
    return $string;
}

/**
 * Sanitize a username
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_username($string) {
    $string = self::sanitize_basic($string);
    $string = preg_replace("/[^A-Za-z0-9_\-]+/", "", $string);
    return $string;
}

/**
 * Sanitize a URL string
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_url($string) {
    $string = self::sanitize_basic($string);
    $string = filter_var($string, FILTER_SANITIZE_URL);
    return $string;
}

/**
 * Sanitize a string that is a slug
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_slug($string) {
    $string = substr($string, 0, 20);
    $string = strtolower($string);
    $string = str_replace(' ', '-', $string);
    $string = preg_replace("/[^a-z0-9_\-]+/", "", $string);
    $string = self::sanitize_basic($string);
    return $string;
}

/**
 * Sanitize an email address string
 * @param $string the string to sanitize
 * @return the sanitized string
 **/
static function sanitize_email($string) {
    $string = self::sanitize_basic($string);
    $string = filter_var($string, FILTER_SANITIZE_EMAIL);
    return $string;
}


}

?>