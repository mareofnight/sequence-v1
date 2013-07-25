<?php

include_once(realpath(__dir__).'/business/sq_session.php');// keep the current session going
require_once(realpath(__dir__).'/business/sq_do.php');

$slugs = array($_GET['slug1'], $_GET['slug2'], $_GET['slug3']);

// get the slugs from the URL
$slugs = array();
$counter = 1;
while (array_key_exists(('slug'.$counter), $_GET)) {
    $slugs[$counter] = $_GET['slug'.$counter];
    $counter += 1;
}

// display the correct "page" for each request
if (!isset($slugs[1]) || strcmp($slugs[1], 'admin') != 0) {// public area
    require_once(realpath(__dir__).'/presentation/sq_public_writer.php');
    
    if (
        (sq_utils::exists($slugs[1]) && !sq_do::check_slug($slugs[1])) ||
        (sq_utils::exists($slugs[2]) && !ctype_digit($slugs[2]) && !sq_do::check_slug($slugs[2])) ||
        (sq_utils::exists($slugs[3]) && !ctype_digit($slugs[3]) && !sq_do::check_slug($slugs[3]))
       ){
        echo sq_public_writer::page_not_found();
    }
    
    if (!isset($slugs[1])) {
        echo sq_public_writer::index();
    }
    else {
        switch ($slugs[1]) {
            case 'login':
                header('Location: '.sq_utils::get_uri().'/admin/login');// redirect to the admin login page
                break;
            case 'logout':
                header('Location: '.sq_utils::get_uri().'/admin/logout');// redirect to the admin logout page
                break;
            case 'news':
                echo sq_public_writer::news();
                break;
            default:
                echo sq_public_writer::write($slugs);
                break;
        }
    }
}
else {                                                    // admin area
    require_once(realpath(__dir__).'/admin/sq_admin_writer.php');
    
    // check that user has permission to view
    if ((!isset($_SESSION['userid']) || !sq_do::check_permission('view_admin_area', $_SESSION['userid']))
        && strcmp($slugs[2], 'login') != 0 && strcmp($slugs[2], 'lost') != 0) {
        if (isset($slugs[2]) == false) {
            header('Location: '.sq_utils::get_uri().'/admin/login');// redirect to the admin login page
        }
        else {
            echo sq_admin_writer::no_permission();
        }
    }
    else {// if user has permission to view
         if (!isset($slugs[2])) {
            echo sq_admin_writer::index();
        }
        else {
            switch ($slugs[2]) {
                case 'basicpage':
                    if (!sq_utils::exists($slugs[3])) {
                        echo sq_admin_writer::list_basicpages();
                    }
                    else if (strcmp('new', $slugs[3]) == 0) {
                        echo sq_admin_writer::new_basicpage($slugs[3]);
                    }
                    else if (sq_do::basicpage_exists($slugs[3])) {
                        echo sq_admin_writer::edit_basicpage($slugs[3]);
                    }
                    else { echo sq_admin_writer::err404(); }
                    break;
                case 'storypage':
                    if (sq_do::storypage_exists($slugs[3])) {
                        echo sq_admin_writer::edit_storypage($slugs[3]);
                    }
                    else { echo sq_admin_writer::err404(); }
                    break;
                case 'story':
                    $allStorySlugs = array_keys(sq_do::get_stories());
                    if (in_array($slugs[3], $allStorySlugs) == true) {
                        if (!isset($slugs[4])) {
                            echo sq_admin_writer::story($slugs[3]);
                        }
                        else {
                            switch ($slugs[4]) {
                                case 'edit':
                                    if (isset($slugs[5])) {
                                        echo sq_admin_writer::edit_story($slugs[3], $slugs[5]);
                                    } else {
                                        echo sq_admin_writer::edit_story($slugs[3]);
                                    }
                                    break;
                                case 'new':
                                    $id = sq_do::new_storypage($slugs[3]);
                                    header('Location: '.sq_utils::get_uri().'/admin/storypage/'.$id.'/new');
                                    break;
                                case 'storypage':
                                    echo sq_admin_writer::list_storypages($slugs[3]);
                                    break;
                            }
                        }
                        break;
                    }
                    else if (strcmp($slugs[3], 'new') == 0) {
                        echo sq_admin_writer::new_story();
                    }
                    else {
                        echo sq_admin_writer::err404();;
                    }
                    
                    break;
                case 'settings':
                    echo sq_admin_writer::settings();
                    break;
                case 'users':
                    if (!isset($slugs['3'])) {
                        echo sq_admin_writer::list_users();
                    }
                    else if (strcmp($slugs['3'], 'new') == 0) {
                        echo sq_admin_writer::new_user();
                    }
                    else if (ctype_digit($slugs['3']) && sq_do::get_user($slugs['3']) != false) {
                        echo sq_admin_writer::user($slugs['3']);
                    }
                    else {
                        echo sq_admin_writer::err404();;
                    }
                    break;
                case 'login':
                    echo sq_admin_writer::login();
                    break;
                case 'logout':
                    echo sq_admin_writer::logout();
                    break;
                case 'lost':
                    echo sq_admin_writer::lost_account();
                    break;
                case 'myaccount':
                    echo sq_admin_writer::myaccount();
                    break;
                default:
                    echo sq_admin_writer::err404();
                    break;
            }
    }
    }
   
}



?>