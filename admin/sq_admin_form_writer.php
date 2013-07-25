<?php
require_once(realpath(__dir__).'/../business/sq_utils.php');
require_once(realpath(__dir__).'/../business/sq_do.php');

class sq_admin_form_writer {

/**
 * Write HTML to display a form in the admin area
 * @param $elements two-dimensional array of form elements, with element names as keys, and input types, labels, default values, etc. in the inner arrays
 * @param $location URI that the form action should go to (submit button link location)
 * @param $errors array of error messages, with the names of the elements they relate to as keys
 * @param $confirm confirmation message if the form has just been saved correctly, otherwise null
 **/
static function writeForm($elements, $location, $errors = array(), $confirm = null) {
    $out = '';
    $out .= "\n".'<form enctype="multipart/form-data" action="'.$location.'" method="POST">'."\n";
    
    // print save confirmation mesage
    if (isset ($confirm) && $confirm != false) {
        $out .= "\n\t".'<div class="confirm">'.$confirm.'</div>';
    }
    // print save errors
    if (isset($errors['submit'])) {
        $out .= "\n\t\t".'<h3 class="error">'.$errors['submit'].'</h3>';
    }
    
    // print form elements
    foreach ($elements as $name=>$element) {
        if (!isset($element['display']) && $element['display'] != true) {
            $out .= "\n\t".'<div class="formelement '.$name.'">';
        }
        if (!isset($element['label'])) { $element['label'] = $name; }
        $element['label'] == sq_utils::sanitize_out($element['label']);
        switch ($element['tag']) {
            /*******      SUBMIT      *******/
            case 'submit':
                if (isset($element['delete'])) {
                    $out .= "\n\t\t".'<input name="submit" type="submit" value="delete" />';
                }
                if (isset($element['cancel'])) {
                    $out .= "\n\t\t".'<a href="'.sq_utils::get_uri().'/admin" class="button cancel">cancel</a>';
                }
                $out .= "\n\t\t".'<input name="submit" type="submit" value="'.$element['label'].'" />';
                break;
            /*******      CHECK      *******/
            case 'check':
                if ($element['display'] == true) {
                    $out .= "\n\t\t".'<div class="check">';
                    $out .= "\n\t\t\t".'<h3>'.$element['label'].'</h3>';
                    $out .= "\n\t\t\t".'<div class="options">';
                    foreach ($element['options'] as $option) {
                        $out .= "\n\t\t\t\t".'<input name="submit" type="submit" value="'.$option.'" />';
                    }
                    $out .= "\n\t\t\t".'</div>';
                    $out .= "\n\t\t".'</div>';
                }
                
                break;
            /*******      INPUT      *******/
            case 'input':
                if (!isset($element['type'])) { $element['type'] = 'text'; }
                $out .= "\n\t\t".'<label for="'.$name.'-element">'.$element['label'].'</label>';
                $out .= "\n\t\t".'<input name="'.$name.'" type="'.$element['type'].'" id="'.$name.'-element"';
                if (isset($element['value']) && strcmp($element['type'], 'password') != 0) {// default value (don't set if password)
                    $out .= ' value="'.$element['value'].'"';
                }
                if (isset($element['checked']) && $element['checked'] == true) {// checkbox - default checked or unchecked
                    $out .= ' checked';
                }
                $out .= ' />';
                break;
            /*******      SELECT      *******/
            case 'select':
                $out .= "\n\t\t".'<label for="'.$name.'=element">'.$element['label'].'</label>';
                $out .= "\n\t\t".'<select name="'.$name.'" id="'.$name.'-element"';
                if (sq_utils::exists($element['value'])) { $out .= ' selected="'.$element['value'].'"'; }
                $out .= '>';
                foreach ($element['options'] as $value=>$option) {
                    if (sq_utils::exists($value)) {
                        $value = $option;
                    }
                    $out .= "\n\t\t\t".'<option value="'.$value.'"';
                    if (sq_utils::exists($element['value']) && strcmp($element['value'], $value) == 0) {// set default option
                        $out .= ' selected="selected" ';
                    }
                    $out .= '>'.$option.'</option>';
                }
                $out .= "\n\t\t".'</select>';
                break;
            /*******      HIDDEN      *******/
            case 'hidden':
                $out .= "\n\t\t".'<input name="'.$name.'" type="hidden" value="'.$element['value'].'" />';
                break;
            /*******      WYSIWYG      *******/
            case 'wysiwyg':
                $out .= "\n\t\t".'<label for="'.$name.'-element">'.$element['label'].'</label>';
                $out .= "\n\t\t".'<textarea name="'.$name.'" id="'.$name.'-element">'.$element['value'].'</textarea>';
                $out .= "\n\t\t".'<script>'."\n\t\t\t".'CKEDITOR.replace( "'.$name.'", '.
                '{ customConfig: "'.sq_utils::get_uri().'/admin/ckeditor_config.js" }'.
                ');'."\n\t\t".'</script>';
                break;
            /*******      IMAGE      *******/
            case 'image':
                $out .= "\n\t\t".'<label for="'.$name.'-file-element">'.$element['label'].'</label>';
                
                if (sq_utils::exists($element['value'])) {
                    $imgurl = sq_utils::get_uri().$element['value'];
                }
                else {
                    $imgurl = sq_utils::get_uri().'/media/default/placeholder.png';
                }
                $out .= "\n\t\t".'<img src="'.$imgurl.'"';
                if ($element['thumbnail'] == true) {
                    $out .= ' style="max-width: 120px; max-height: 120px;"';
                }
                $out .= '/>';
                
                $out .= "\n\t\t".'<input type="file" accept="image/*" name="'.$name.'-file" id="'.$name.'-file-element" /><input type="hidden" name="'.$name.'" value="'.$element['value'].'">';
                if (strlen($element['value'])>0) {
                    $out .= "\n\t\t".'<div class="help">Current image: '.$element['value'].'</div>';
                }
                break;
            /*******      P      *******/
            case 'p':
                $out .= "\n\t\t".'<p>'.$element['value'].'</p>';
                break;
            /*******      DEFAULT      *******/
            default:
                if (!isset($element['value'])) { $element['value'] = $name; }
                $out .= "\n\t\t".'<'.$element['tag'].'>'.$element['value'].'</'.$element['tag'].'>';
                break;
        }
        if (isset($element['deleteable'])) {
            $out .= "\n\t\t".'<label for="'.$name.'-delete-element">'.$element['deleteable'].'</label>';
            $out .= "\n\t\t".'<input type="checkbox" name="'.$name.'-delete" id="'.$name.'-delete-element" value="1" />';
        }
        if (array_key_exists($name, $errors)) {
            $out .= "\n\t\t".'<div class="error">'.$errors[$name].'</div>';
        }
        if (array_key_exists('help', $element)) {
            $out .= "\n\t\t".'<div class="help">'.$element['help'].'</div>';
        }
        if (!isset($element['display']) && $element['display'] != true) { $out .= "\n\t".'</div>'; }
    }
    $out .= "\n".'</form>'."\n\n";
    return $out;
}

static function no_permission() {
    return "\n".'<h3 class="error">You do not have permission to use this page.</h3>';
}

/**
 * Retrieve and sanitize inputs from POST
 * (sanitizing is not customized to the data type)
 * @param $elements the elements of the form that the input comes from
 * @return an array of the inputs from POST
 **/
static function getInputs($elements) {
    $inputs = array();
    foreach ($elements as $key=>$element) {
        if (isset($element['deleteable'])) {
            $inputs[$key.'-delete'] = sq_utils::sanitize_basic($_POST[$key.'-delete']);
        }
        if (isset($_POST[$key])) {
            $inputs[$key] = sq_utils::sanitize_basic($_POST[$key]);
            $elements[$key]['value'] = $inputs[$key];
        }
    }
    // correct for checkboxes
    foreach ($elements as $key=>$element) {
        if (isset($element['type']) && strcmp($element['type'], 'checkbox') == 0
            && isset($inputs['submit']) && !isset($inputs[$key])) {
        $inputs[$key] = 0;
        }
        if (isset($element['deleteable']) && strcmp(strval($inputs[$key.'-delete']), '1') == 0) {
            $inputs[$key] = null;
        }
    }
    return $inputs;
}

/**
 * change the form elements to reflect the inputs (should be done after merging inputs with database contents)
 * @param $inputs array of inputs, already sanitized and merged with database contents
 * @param $elements the form elements
 * @return the form elements, transformed to reflect the inputs
 **/
static function processElements($inputs, $elements) {
    foreach ($inputs as $key=>$input) {
        if (isset($elements[$key])) {
            // checkboxes
            if (isset($elements[$key]['type']) && strcmp($elements[$key]['type'], 'checkbox') == 0) {
                if (isset($inputs[$key]) && strcmp($input, '1') == 0) {
                    $elements[$key]['checked'] = true;
                }
            }
            
            // if delete option is checked, clear the value of the associated field
            else if (isset($inputs[$key.'-delete']) && strlen($inputs[$key.'-delete']) > 0) {
                $elements[$key]['value'] = '';
            }
            else {
               $elements[$key]['value'] = $inputs[$key];
            }
            
            // deal with delete confirmation messages
            if (isset($elements['deletecheck']) && isset($inputs['submit']) && strcmp($inputs['submit'], 'delete') == 0) {
                $elements['deletecheck']['display'] = true;
            }
            else if (isset($elements['deletecheck']) && isset($inputs['submit']) && strcmp($inputs['submit'], 'cancel') == 0) {
                $elements['deletecheck']['display'] = false;
            }
        }
        else {
            unset($inputs[$key]);
        }
    }
    return $elements;
}


/**
 * accessor for the basic page form elements
 * @return array of the basic page form elements
 **/
static function getEditBasicpageElements() {
    $elements = array(
        'oldslug'=>array('tag'=>'hidden', 'type'=>'hidden'),
        'deletecheck'=>array('tag'=>'check', 'display'=>false, 'label'=>'Are you sure you want to delete this basic page?',
                             'options'=>array('delete basic page', 'cancel')),
        'title'=>array('tag'=>'input', 'type'=>'text'),
        'short_title'=>array('tag'=>'input', 'type'=>'text', 'label'=>'short title'),
        'slug'=>array('tag'=>'input', 'type'=>'text', 'help'=>'A story'."'".'s slug is used to refer to the story in URLs. '.
                        'The slug must contain only numbers, lower case letter, underscores and dashes.'),
        'body'=>array('tag'=>'wysiwyg', 'help'=>'Use the image upload button in the text editor to include illustrations or comics. '.
                      'Drag the lower right corner to see more of your page at once.'),
        'publish_now'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'publish now'),
        'publish'=>array('tag'=>'input', 'type'=>'text', 'value'=>date('Y-m-d H:i'), 'isdatetime'=>true,
                         'help'=>'format: year-month-day hours:minutes, with 24 hour clock hours (example: 2013-05-15 13:45)'.
                                    '<br >Leave this feild blank or all zeros (0000-00-00 00:00) to save this page as a draft.'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true, 'delete'=>true)
    );
    return $elements;
}

/**
 * Generate HTML for a form to edit a basic page
 * @param $id content id of the basic page to edit
 * @return HTML for the form
 **/
static function edit_basicpage($id) {
    if (!sq_do::check_permission('edit_basicpage')) { return self::no_permission(); }
    $elements = self::getEditBasicpageElements();
    $inputs = self::getInputs($elements);
    $oldData = sq_do::get_basicpage($id, false);
    $inputs = array_merge($oldData, $inputs);
    if (!sq_utils::exists($inputs['oldslug'])) { $inputs['oldslug'] = $inputs['slug'];}
    $elements = self::processElements($inputs, $elements);
    
    if (!sq_do::content_published($id)) {
        $draft = "\n\t".'<div class="draft">Draft</div>';
    }
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['slug'])) {
            $errors['slug'] = 'Please enter a slug';
        }
        else if (strlen($inputs['slug']) > 20) {
            $errors['slug'] = 'Slug must be 20 or fewer characters long';
        }
        else if (strcmp(sq_utils::sanitize_slug($inputs['slug']), trim($inputs['slug'])) != 0) {
            $errors['slug'] = 'Slug must contain only lowercase letters, numbers, underscores (_) and dashes (-)';
        }
        else if (strcmp(trim($inputs['slug']), trim($inputs['oldslug'])) != 0 &&
            sq_do::check_slug($inputs['slug']) != false) {
            $errors['slug'] = 'The slug "'.$inputs['slug'].'" is already taken - please choose a different slug';
        }
        if (!sq_utils::exists($inputs['title'])) {
            $errors['title'] = 'Please enter a title';
        }
        else if (strlen($inputs['title']) > 255) {
            $errors['title'] = 'Title must be 255 or fewer characters long';
        }
        if (!sq_utils::exists($inputs['short_title'])) {
            $errors['short_title'] = 'Please enter a short title';
        }
        else if (strlen($inputs['short_title']) > 20) {
            $errors['short_title'] = 'Short title must be 20 or fewer characters long';
        }
        if (strlen($inputs['body']) > 65535) {
            $errors['body'] = 'Source code of the body be 65535 or fewer characters long';
        }
        if (sq_utils::exists($inputs['publish']) && strlen($inputs['publish']) != 0 &&
        strcmp($inputs['publish'], '0') != 0 && strcmp($inputs['publish'], '0000-00-00 00:00') != 0) {
            if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9])$/", $inputs['publish'], $dateparts)) {
                if (checkdate($dateparts[2], $dateparts[3], $dateparts[1])) {}
                else {
                    $errors['publish'] = 'Publish date/time must be a valid date and time';
                }
            }
            else {
                $errors['publish'] = 'Publish date/time must be in this format: year-month-day hours:minutes (example: 2013-05-15 13:45)';
            }
        }
        
        // alter time for publish_now() option
        if ($inputs['publish_now']) {
            $inputs['publish'] = sq_utils::from_unixtime(time());
            $elements['publish']['value'] = $inputs['publish'];
        }
        
        // save changes
        if (count($errors) == 0) {
            
            // database
            sq_do::edit_basicpage($id, $inputs);
            $confirm = 'basic page saved';
            
            // update "oldslug" to reflect the changes
            $elements['oldslug']['value'] = $elements['slug']['value'];
            
        }
        
    }
    if (isset($inputs['submit']) && strcmp($inputs['submit'], 'delete basic page') == 0) {// delete if confirmed
        sq_do::delete_basicpage($id);
        $confirm = 'basic page deleted';
        $elements = array();
    }
    if (strcmp($_GET['slug4'], 'new') == 0) {// different message for newly created story pages
        $confirm = 'basic page created';
    }
    return $draft.self::writeForm($elements, sq_utils::get_uri().'/admin/basicpage/'.$id, $errors, $confirm);
}


/**
 * Accessor for the elements of the create basic page form
 * @return two dimensional array of the create basic page form elements
 **/
static function getNewBasicpageElements() {
    return array(
        'title'=>array('tag'=>'input', 'type'=>'text'),
        'submit'=>array('tag'=>'submit', 'label'=>'create basic page', 'cancel'=>true)
    );
}

/**
 * Write the HTML for, validate, and run the create new basic page form
 * @return all HTML for the create new basic page form, or null if a new basic page has been created
 **/
static function new_basicpage() {
    if (!sq_do::check_permission('edit_basicpage')) { return self::no_permission(); }
    $elements = self::getNewBasicpageElements();
    $inputs = self::getInputs($elements);
    $elements = self::processElements($inputs, $elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['title'])) {
            $errors['title'] = 'Please enter a title';
        }
        else if (strlen($inputs['title']) > 255) {
            $errors['title'] = 'Title must be 255 or fewer characters long';
        }
        /*else if (strlen($inputs['slug']) > 20) {
            $errors['slug'] = 'Slug must be 20 or fewer characters long';
        }
        else if (strcmp(sq_utils::sanitize_slug($inputs['slug']), trim($inputs['slug'])) != 0) {
            $errors['slug'] = 'Slug must contain only lowercase letters, numbers, underscores (_) and dashes (-)';
        }
        else if (sq_do::check_slug($inputs['slug']) != false) {
            $errors['slug'] = 'The slug "'.$inputs['slug'].'" is already taken - please choose a different slug';
        }*/
        else {
            $id = sq_do::new_basicpage($inputs['title']);
            header('Location: '.sq_utils::get_uri().'/admin/basicpage/'.$id);// redirect to the edit page of the new basic page
            return null;
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/basicpage/new', $errors);
}





/**
 * accessor for the story page form elements
 * @return array of the story page form elements
 **/
static function getEditStorypageElements() {
    $elements = array(
        'deletecheck'=>array('tag'=>'check', 'display'=>false, 'label'=>'Are you sure you want to delete this story page?',
                             'options'=>array('delete story page', 'cancel')),
        'title'=>array('tag'=>'input', 'type'=>'text'),
        'iscover'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'cover', 'help'=>'check if this story page is a cover page'),
        'thumbnail'=>array('tag'=>'image', 'thumbnail'=>true, 'deleteable'=>'remove'),
        'body'=>array('tag'=>'wysiwyg', 'help'=>'Use the image upload button in the text editor to include illustrations or comics. '.
                      'Drag the lower right corner to see more of your page at once.'),
        'author_comment'=>array('tag'=>'wysiwyg', 'label'=>'author comments'),
        'publish_now'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'publish now'),
        'publish'=>array('tag'=>'input', 'type'=>'text', 'value'=>date('Y-m-d H:i'), 'isdatetime'=>true,
                         'help'=>'format: year-month-day hours:minutes, with 24 hour clock hours (example: 2013-05-15 13:45)'.
                                    '<br >Leave this feild blank or all zeros (0000-00-00 00:00) to save this page as a draft.'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true, 'delete'=>true)
    );
    return $elements;
}

/**
 * Generate HTML for a form to edit a story page
 * @param $id content id of the story page to edit
 * @return HTML for the form
 **/
static function edit_storypage($id) {
    if (!sq_do::check_permission('edit_storypage')) { return self::no_permission(); }
    $elements = self::getEditStorypageElements();
    $inputs = self::getInputs($elements);
    $oldData = sq_do::get_storypage($id, false);
    $inputs = array_merge($oldData, $inputs);
    $elements = self::processElements($inputs, $elements);
    
    if (!sq_do::content_published($id)) {
        $draft = "\n\t".'<div class="draft">Draft</div>';
    }
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['title'])) {
            $errors['title'] = 'Please enter a title';
        }
        if (strlen($inputs['title']) > 255) {
            $errors['title'] = 'Title must be 255 or fewer characters long';
        }
        if (strlen($inputs['body']) > 65535) {
            $errors['body'] = 'Source code of the body be 65535 or fewer characters long';
        }
        if (strlen($inputs['author_comment']) > 65535) {
            $errors['author_comment'] = 'Source code of the body be 65535 or fewer characters long';
        }
        if (sq_utils::exists($inputs['publish']) && strlen($inputs['publish']) != 0 &&
        strcmp($inputs['publish'], '0') != 0 && strcmp($inputs['publish'], '0000-00-00 00:00') != 0) {
            if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9])$/", $inputs['publish'], $dateparts)) {
                if (checkdate($dateparts[2], $dateparts[3], $dateparts[1])) {}
                else {
                    $errors['publish'] = 'Publish date/time must be a valid date and time';
                }
            }
            else {
                $errors['publish'] = 'Publish date/time must be in this format: year-month-day hours:minutes (example: 2013-05-15 13:45)';
            }
        }
        
        // alter time for publish_now() option
        if ($inputs['publish_now']) {
            $inputs['publish'] = sq_utils::from_unixtime(time());
            $elements['publish']['value'] = $inputs['publish'];
        }
        
        // save changes
        if (count($errors) == 0) {
            
            // image upload
            if (($_FILES['thumbnail-file'] != "none") && (!empty($_FILES['thumbnail-file']['name'])) ) {
                $upload_result = sq_utils::upload_file('thumbnail-file', 'image');
                if (strcmp(substr($upload_result, 0, 1), ' ') != 0 ) {
                    $errors['thumbnail'] = $upload_result;
                }
                else {
                    $elements['thumbnail']['value'] = substr($upload_result, 1);
                    $inputs['thumbnail'] = substr($upload_result, 1);
                }
            }
            
            // database
            sq_do::edit_storypage($id, $inputs);
            $confirm = 'story page saved';
            
        }
        
    }
    if (isset($inputs['submit']) && strcmp($inputs['submit'], 'delete story page') == 0) {// delete if confirmed
        sq_do::delete_storypage($id);
        $confirm = 'story page deleted';
        $elements = array();
    }
    if (strcmp($_GET['slug4'], 'new') == 0) {// different message for newly created story pages
        $confirm = 'story page created';
    }
    return $draft.self::writeForm($elements, sq_utils::get_uri().'/admin/storypage/'.$id, $errors, $confirm);
}


/**
 * Accessor for the elements of the create story form
 * @return two dimensional array of the create story form elements
 **/
static function getEditStoryElements() {
    return array(
        'oldslug'=>array('tag'=>'hidden', 'type'=>'hidden'),
        'deletecheck'=>array('tag'=>'check', 'display'=>false, 'label'=>'Are you sure you want to delete this story?',
                             'options'=>array('delete story', 'cancel')),
        'title'=>array('tag'=>'input', 'type'=>'text'),
        'short_title'=>array('tag'=>'input', 'type'=>'text', 'label'=>'short title'),
        'slug'=>array('tag'=>'input', 'type'=>'text', 'help'=>'A story'."'".'s slug is used to refer to the story in URLs. '.
                        'The slug must contain only numbers, lower case letter, underscores and dashes.'),
        'synopsis'=>array('tag'=>'wysiwyg', 'help'=>'Drag the lower right corner to see more of your page at once.'),
        'Archive Display Settings'=>array('tag'=>'h3'),
        'show_synopsis'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show synopsis'),
        'show_cover_thumb'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show cover thumbnail'),
        'show_page_thumb'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show page thumbnail'),
        'show_page_title'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show page title'),
        'show_page_num'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show page number'),
        'Story Page Display Settings'=>array('tag'=>'h3'),
        'show_prevnext'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show previous/next page links'),
        'show_firstlast'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show first/last page links'),
        'Homepage Display Settings'=>array('tag'=>'h3'),
        'show_updates'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'announce updates to this story on the site homepage'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true, 'delete'=>true)
    );
}

/**
 * Write the HTML for, validate, and run the create new story form
 * @param $slug the slug of the story to edit
 * @param $success whether the story has been saved successfully (null if not saved, 'true' if success)
 * @return all HTML for the create new story form, or null if a new story has been created
 **/
static function edit_story($slug, $success = null) {
    if (!sq_do::check_permission('edit_story')) { return self::no_permission(); }
    $elements = self::getEditStoryElements();
    $inputs = self::getInputs($elements);
    if (!isset($_GET['success'])) { $inputs['oldslug'] = $slug; }
    $inputs = array_merge(sq_do::get_story($inputs['oldslug']), $inputs);
    $elements = self::processElements($inputs, $elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['slug'])) {
            $errors['slug'] = 'Please enter a slug';
        }
        else if (strlen($inputs['slug']) > 20) {
            $errors['slug'] = 'Slug must be 20 or fewer characters long';
        }
        else if (strcmp(sq_utils::sanitize_slug($inputs['slug']), trim($inputs['slug'])) != 0) {
            $errors['slug'] = 'Slug must contain only lowercase letters, numbers, underscores (_) and dashes (-)';
        }
        else if (strcmp(trim($inputs['slug']), trim($slug)) != 0 &&
            sq_do::check_slug($inputs['slug']) != false) {
            $errors['slug'] = 'The slug "'.$inputs['slug'].'" is already taken - please choose a different slug';
        }
        else if (!sq_utils::exists($inputs['title'])) {
            $errors['title'] = 'Please enter a title';
        }
        else if (strlen($inputs['title']) > 255) {
            $errors['title'] = 'Title must be 255 or fewer characters long';
        }
        else if (!sq_utils::exists($inputs['short_title'])) {
            $errors['short_title'] = 'Please enter a short title';
        }
        else if (strlen($inputs['short_title']) > 20) {
            $errors['short_title'] = 'Short title must be 20 or fewer characters long';
        }
        else {
            $errors['submit'] = sq_do::edit_story($inputs['oldslug'], $inputs);
            if (strlen($errors['submit']) == 0) { unset($errors['submit']); }
            $confirm = 'story saved';
        }
    }
    if (isset($inputs['submit']) && strcmp($inputs['submit'], 'delete story') == 0) {// delete if confirmed
        $success = sq_do::delete_story($slug);
        if ($success === true) {
            $confirm = 'Story deleted';
            $elements = array();
        }
        else if ($success === false) {
            $errors['submit'] = 'Delete failed';
        }
        else {
            $errors['submit'] = $success;
        }
    }
    if (strcmp($success, 'true') == 0) {// decide whether to display "story saved" message
        $confirm = 'story saved';
    }
    else if (strcmp($_GET['slug5'], 'new') == 0) {// different message for newly created stories
        $confirm = 'story created';
    }
    
    return self::writeForm($elements, sq_utils::get_uri().'/admin/story/'.$slug.'/edit', $errors, $confirm);
}




/**
 * Accessor for the elements of the create story form
 * @return two dimensional array of the create story form elements
 **/
static function getNewStoryElements() {
    return array(
        'title'=>array('tag'=>'input', 'type'=>'text'),
        'submit'=>array('tag'=>'submit', 'label'=>'create story', 'cancel'=>true)
    );
}

/**
 * Write the HTML for, validate, and run the create new story form
 * @return all HTML for the create new story form, or null if a new story has been created
 **/
static function new_story() {
    if (!sq_do::check_permission('edit_story')) { return self::no_permission(); }
    $elements = self::getNewStoryElements();
    $inputs = self::getInputs($elements);
    $elements = self::processElements($inputs, $elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['title'])) {
            $errors['title'] = 'Please enter a title';
        }
        else if (strlen($inputs['title']) > 255) {
            $errors['title'] = 'Title must be 255 or fewer characters long';
        }
        /*else if (strlen($inputs['slug']) > 20) {
            $errors['slug'] = 'Slug must be 20 or fewer characters long';
        }
        else if (strcmp(sq_utils::sanitize_slug($inputs['slug']), trim($inputs['slug'])) != 0) {
            $errors['slug'] = 'Slug must contain only lowercase letters, numbers, underscores (_) and dashes (-)';
        }
        else if (sq_do::check_slug($inputs['slug']) != false) {
            $errors['slug'] = 'The slug "'.$inputs['slug'].'" is already taken - please choose a different slug';
        }*/
        else {
            $slug = sq_do::new_story($inputs['title']);
            header('Location: '.sq_utils::get_uri().'/admin/story/'.$slug.'/edit/new');// redirect to the edit page of the new story
            return null;
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/story/new', $errors);
}

/**
 * accessor for the elements of the settings form
 * @return array of the data for the elements of the settings form
 **/
static function getSettingsElements() {
    return array(
        'site_title'=>array('tag'=>'input', 'type'=>'text', 'label'=>'site title'),
        'news_rss_url'=>array('tag'=>'input', 'type'=>'text', 'label'=>'news RSS feed'),
        'home_show_news'=>array('tag'=>'input', 'type'=>'checkbox', 'value'=>'1', 'label'=>'show news on homepage'),
        'home_format'=>array('tag'=>'select', 'label'=>'homepage format',
            'options'=>array('stories / text / news', 'stories / news / text',
                             'text / stories / news', 'text / news / stories',
                             'news / stories / text', 'news / text / stories'),
            'help'=>'the order in which story updates ("stories"), news, and homepage text ("text") are displayed on the homepage'),
        'home_update_format'=>array('tag'=>'select', 'label'=>'story update format',
            'options'=>array('title', 'thumbnail', 'body',
                             'title thumbnail'=>'title / thumbnail',
                             'title body'=>'title / body',
                             'title thumbnail body'=>'title / thumbnail / body'),
            'help'=>'the combination of new story pages'."'".' titles, thumbnails and bodies to display on the home page'),
        'home_text'=>array('tag'=>'wysiwyg', 'label'=>'homepage text', 'help'=>'a text blurb to display on the homepage'),
        'timezone'=>array('tag'=>'select',  'options'=>DateTimeZone::listIdentifiers(),
            'help'=>'This timezone is used when setting content publish times'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true)
    );
}

/**
 * write HTML for the edit settings form
 * @return HTML for the edit settings form
 **/
static function settings() {
    if (!sq_do::check_permission('edit_settings')) { return self::no_permission(); }
    $elements = self::getSettingsElements();
    $inputs = self::getInputs($elements);
    $oldData = sq_do::get_settings();
    $inputs = array_merge($oldData, $inputs);
    $elements = self::processElements($inputs, $elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['site_title'])) {
            $errors['site_title'] = 'Please enter a title';
        }
        if (strlen($inputs['site_title']) > 255) {
            $errors['site_title'] = 'Title must be 255 or fewer characters long';
        }
        if (strlen($inputs['news_rss_url']) > 255) {
            $errors['news_rss_url'] = 'News RSS feed URL must be 255 or fewer characters long';
        }
        if (strlen($inputs['home_text']) > 255) {
            $errors['home_text'] = 'Homepage text must be 255 or fewer characters long';
        }
        if (count($errors)==0) {
            sq_do::edit_settings($inputs);
            $confirm = 'settings saved';
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/settings', $errors, $confirm);
}


/**
 * Accessor for the elements of the modify user form, and their input types
 * @return two dimensional array of the elements of the modify user form, and their input types
 **/
static function getNewUserElements() {
    return array(
        'username'=>array('tag'=>'input', 'type'=>'text'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true)
    );
}

/**
 * Write HTML for the modify user form
 * @param $userid userid of the user to display and modify
 * @return HTML for the modify user form
 **/
static function new_user() {
    if (!sq_do::check_permission('manage_users')) { return self::no_permission(); }
    $elements = self::getNewUserElements();
    $inputs = self::getInputs($elements);
    $elements = self::processElements($inputs, $elements);
    
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['username'])) {
            $errors['username'] = 'Please enter a username';
        }
        else if (strlen($inputs['username']) > 20) {
            $errors['username'] = 'Username must be 20 or fewer characters long';
        }
        else if (sq_do::check_username($inputs['username'])) {
            $errors['username'] = 'This username is already taken';
        }
        if (count($errors) == 0) {
            $userid = sq_do::new_user($inputs['username']);
            if ($userid == false) {
                $errors['submit'] = 'User could not be saved';
            }
            else {
                header('Location: '.sq_utils::get_uri().'/admin/users/'.$userid);// redirect to modify the user
            }
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/users/new', $errors);
}


/**
 * Accessor for the elements of the modify user form, and their input types
 * @return two dimensional array of the elements of the modify user form, and their input types
 **/
static function getUserElements() {
    return array(
        'username'=>array('tag'=>'input', 'type'=>'text'),
        'email'=>array('tag'=>'input', 'type'=>'text'),
        'url'=>array('tag'=>'input', 'type'=>'text'),
        'role'=>array('tag'=>'select', 'options'=>array('admin', 'banned')),
        'new_password'=>array('tag'=>'input', 'type'=>'password', 'label'=>'new password', 'help'=>'optional'),
        'confirm_password'=>array('tag'=>'input', 'type'=>'password', 'label'=>'confirm new password', 'help'=>'optional'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true)
    );
}

/**
 * Write HTML for the modify user form
 * @param $userid userid of the user to display and modify
 * @return HTML for the modify user form
 **/
static function user($userid) {
    if (!sq_do::check_permission('manage_users')) { return self::no_permission(); }
    $elements = self::getUserElements();
    $inputs = self::getInputs($elements);
    $oldData = sq_do::get_user($userid);
    $inputs = array_merge($oldData, $inputs);
    $elements = self::processElements($inputs, $elements);
    
    if ($userid == $_SESSION['userid']) {
        unset($inputs['role']);
        $elements['role'] = array('tag'=>'p', 'value'=>'You cannot change your own role.');
    }
    
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['username'])) {
            $errors['username'] = 'Please enter a username';
        }
        else if (strlen($inputs['username']) > 20) {
            $errors['username'] = 'Username must be 20 or fewer characters long';
        }
        if (!sq_utils::exists($inputs['email'])) {
            $errors['email'] = 'Please enter your email address';
        }
        else if (strlen($inputs['email']) > 255) {
            $errors['email'] = 'Email must be 255 or fewer characters long';
        }
        else if (strcmp($inputs['email'], sq_utils::sanitize_email($inputs['email'])) != 0) {
            $errors['email'] = 'Email must include only letters, digits, "@", and/or these special characters" !#$%&*+-/=?^_`{|}~.[]'."'";
        }
        if (strlen($inputs['url']) > 255) {
            $errors['url'] = 'URL must be 255 or fewer characters long';
        }
        else if (sq_utils::exists($inputs['url']) && strcmp($inputs['url'], sq_utils::sanitize_url($inputs['url'])) != 0) {
            $errors['url'] = 'URL must include only letters, digits, and/or these special characters: -._~:/?#[]@!$&()*+,;='."'";
        }
        if (strlen($inputs['new_password']) > 20) {
            $errors['new_password'] = 'Password must be 20 or fewer characters long';
        }
        if (strcmp($inputs['new_password'], $inputs['confirm_password']) != 0) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        if (count($errors) == 0) {
            $success = sq_do::set_user($userid, $inputs);
            if ($success) {
                $confirm = 'account info ';
            }
            if (strlen($inputs['new_password']) != 0 &&
                    strcmp($inputs['new_password'], $inputs['confirm_password']) == 0) {
                $success = sq_do::set_password(sq_utils::hash($inputs['new_password']), $userid);
                if ($success) {
                    if (isset($confirm)) {
                        $confirm .= 'and password ';
                    }
                    else {
                        $confirm = 'password ';
                    }
                }
            }
        }
        if (strlen($confirm)>0) {
            $confirm .= 'saved';
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/users/'.$userid, $errors, $confirm);
}

/**
 * Accessor for the elements of the my account form, and their input types
 * @return two dimensional array of my account form elements
 **/
static function getMyaccountElements() {
    return array(
        'username'=>array('tag'=>'input', 'type'=>'text'),
        'email'=>array('tag'=>'input', 'type'=>'text'),
        'url'=>array('tag'=>'input', 'type'=>'text'),
        'current_password'=>array('tag'=>'input', 'type'=>'password', 'label'=>'current password', 'help'=>'required in order to save'),
        'new_password'=>array('tag'=>'input', 'type'=>'password', 'label'=>'new password', 'help'=>'optional'),
        'confirm_password'=>array('tag'=>'input', 'type'=>'password', 'label'=>'confirm new password', 'help'=>'optional'),
        'submit'=>array('tag'=>'submit', 'label'=>'save', 'cancel'=>true)
    );
}

/**
 * Write HTML for the my account form
 * @return HTML for the my account form
 **/
static function myaccount() {
    if (!sq_do::check_permission('edit_own_account')) { return self::no_permission(); }
    $elements = self::getMyaccountElements();
    $inputs = self::getInputs($elements);
    $oldData = sq_do::get_self();
    $inputs = array_merge($oldData, $inputs);
    unset($inputs['role']);
    $elements = self::processElements($inputs, $elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['username'])) {
            $errors['username'] = 'Please enter a username';
        }
        else if (strlen($inputs['username']) > 20) {
            $errors['username'] = 'Username must be 20 or fewer characters long';
        }
        if (!sq_utils::exists($inputs['email'])) {
            $errors['email'] = 'Please enter your email address';
        }
        else if (strlen($inputs['email']) > 255) {
            $errors['email'] = 'Email must be 255 or fewer characters long';
        }
        else if (strcmp($inputs['email'], sq_utils::sanitize_email($inputs['email'])) != 0) {
            $errors['email'] = 'Email must include only letters, digits, "@", and/or these special characters" !#$%&*+-/=?^_`{|}~.[]'."'";
        }
        if (strlen($inputs['url']) > 255) {
            $errors['url'] = 'URL must be 255 or fewer characters long';
        }
        else if (sq_utils::exists($inputs['url']) && strcmp($inputs['url'], sq_utils::sanitize_url($inputs['url'])) != 0) {
            $errors['url'] = 'URL must include only letters, digits, and/or these special characters: -._~:/?#[]@!$&()*+,;='."'";
        }
        if (!sq_utils::exists($inputs['current_password'])) {
            $errors['current_password'] = 'Please enter your password';
        }
        else if (!sq_do::check_password($inputs['current_password'])) {
            $errors['current_password'] = 'Incorrect password';
        }
        if (strlen($inputs['new_password']) > 20) {
            $errors['new_password'] = 'Password must be 20 or fewer characters long';
        }
        if (strcmp($inputs['new_password'], $inputs['confirm_password']) != 0) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        if (count($errors)==0) {
            $success = sq_do::set_self($inputs);
            if ($success) {
                $confirm = 'account info saved';
            }
        }
        if (count($errors)==0 && sq_utils::exists($inputs['new_password']) &&
                strcmp($inputs['current_password'], $inputs['new_password']) != 0 &&
                strcmp($inputs['new_password'], $inputs['confirm_password']) == 0) {
            $success = sq_do::set_password(sq_utils::hash($inputs['new_password']));
            if ($success) {
                if (!sq_utils::exists($confirm)) {
                    $confirm = 'password saved';
                }
                else {
                    $confirm = 'account info and password saved';
                }
            }
        }
        
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/myaccount', $errors, $confirm);
}

/**
 * Accessor for the elements of the lost account form, and their input types
 * @return two dimensonal array of lost account form elements
 **/
static function getLostAccountElements() {
    return array(
        'email'=>array('tag'=>'input', 'type'=>'text', 'label'=>'email addresss'),
        'submit'=>array('tag'=>'submit', 'label'=>'retrieve account')
    );
}

static function lost_account() {
    $elements = self::getLostAccountElements();
    $inputs = self::getInputs($elements);
    $elements = self::processElements($inputs, $elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['email'])) {
            $errors['email'] = 'Please enter your email address';
        }
        else if (strlen($inputs['email']) > 255) {
            $errors['email'] = 'Email must be 255 or fewer characters long';
        }
        else if (strcmp($inputs['email'], sq_utils::sanitize_email($inputs['email'])) != 0) {
            $errors['email'] = 'Email must include only letters, digits, "@", and/or these special characters" !#$%&*+-/=?^_`{|}~.[]'."'";
        }
        if (count($errors) == 0) {
            $sendErrors = sq_do::email_lost_account($inputs['email']);
            if (sq_utils::exists($sendErrors) && strlen($sendErrors) > 0) {
                $errors['email'] = $sendErrors;
            }
            if (count($errors) == 0) {
                $confirm = "Your account information has been sent. Please check your email, and use the information contained in the email to log in.";
            }
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/lost', $errors);
}

/**
 * Accessor for the elements of the login form, and their input types
 * @return two dimensonal array of login form elements
 **/
static function getLoginElements() {
    return array(
        'username'=>array('tag'=>'input', 'type'=>'text'),
        'password'=>array('tag'=>'input', 'type'=>'password'),
        'submit'=>array('tag'=>'submit', 'label'=>'login'),
        'retrieve'=>array('tag'=>'p', 'value'=>'<a href="'.sq_utils::get_uri().'/admin/lost">Forgot your username or password?</a>')
    );
}

/**
 * Write all HTML for the login form, and call a function to login the user
 * @return all HTML for the login form
 **/
static function login() {
    $elements = self::getLoginElements();
    $inputs = self::getInputs($elements);
    
    // get error messages
    $errors = array();
    if (isset($inputs['submit']) && strcmp($inputs['submit'], $elements['submit']['label']) == 0) {
        if (!sq_utils::exists($inputs['username'])) {
            $errors['username'] = 'Please enter your username';
        }
        if (!sq_utils::exists($inputs['password'])) {
            $errors['password'] = 'Please enter your password';
        }
        if (count($errors) == 0) {
            $errors2 = sq_do::login($inputs['username'], $inputs['password']);
            $errors = array_merge($errors, $errors2);
        }
    }
    return self::writeForm($elements, sq_utils::get_uri().'/admin/login', $errors);
}

}
?>