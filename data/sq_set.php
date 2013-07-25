<?php

require_once(realpath(__dir__).'/../data/db.php');

class sq_set {

/**
 * insert a new user
 * @param $username username of the new user
 * @return array with 1 inside if success, array with 0 inside if failed
 **/
static function insert_user($username) {
    $query = 'INSERT INTO User (username, roleid) VALUES (?, 0)';
    $params = array(array($username));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * set userid, username, email, URL and/or role of one user, given their userid
 * @param $userid userid of the user to modify
 * @param $values values to set for other attributes
 * @return array with 1 inside if success, array with 0 inside if failed
 **/
static function user($userid, $values) {
    $params = array(array($values['username'], $values['email'], $values['url']));
    //$query = 'UPDATE User SET username = ?, email = ?, url = ? ';
    $query = 'UPDATE User SET';
    $params = array(array());
    if (isset($values['username'])) {
        $query .= ' username = ?,';
        $params[0][] = $values['username'];
    }
    if (isset($values['email'])) {
        $query .= ' email = ?,';
        $params[0][] = $values['email'];
    }
    if (isset($values['url'])) {
        $query .= ' url = ?,';
        $params[0][] = $values['url'];
    }
    if (isset($values['role'])) {
        $query .= ' roleid = (SELECT roleid FROM Role WHERE role = ?),';
        $params[0][] = $values['role'];
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ' WHERE userid = ?';
    $params[0][] = $userid;
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * set th epassword of one user, given their userid
 * @param $userid userid of the user to modify
 * @param $password the user's new password
 * @return array with 1 inside if success, array with 0 inside if failed
 **/
static function password($userid, $password) {
    $query = 'UPDATE User SET password = ? WHERE userid = ?';
    $params = array(array($password, $userid));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}



/**
 * save multiple settings
 * @param $settings a two dimensional array setting names and values, with the value at index [0] and the name at index[1]
 *      (keys of the outer level of the array can be anything)
 * @return an array of the results of saving the settings, with 1 for success and 0 for failure, with the same array keys as the outer level of the $settings array
 **/
static function settings($settings) {
    $query = 'UPDATE Setting SET value = ? WHERE name = ?';
    $params = $settings;
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}
 

/**
 * add a slug
 * @param $slug the slug to add
 * @return an array with 1 inside if successful, 0 if not successful
 **/
static function insert_slug($slug) {
    $query = 'INSERT INTO Slug (slug) VALUES (?)';
    $params = array(array($slug));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}

/**
 * delete a slug (will only delete non-reserved slugs)
 * @param $slug the slug to delete
 * @return an array with 1 inside if successful, 0 if not successful
 **/
static function delete_slug($slug) {
    $query = 'DELETE FROM Slug WHERE slug = ?';
    $checkquery = 'SELECT reserved FROM Slug WHERE slug = ?';
    $params = array(array($slug));
    $db = new DB();
    $check = $db->run($checkquery, $params);
    // only run query if the slug being deleted is not a reserved slug
    if ($check[0][0]['reserved'] == 0 || $check[0][0]['reserved'] == false || strcmp($check[0][0]['reserved'], '0') == 0) {
        $result = $db->run($query, $params);
    }
    $db->close();
    return $result;
}

/**
 * update all story attributes, and the slug if necessary
 * @param $slug the story's slug before any editing
 * @param $values the new values of the story's attributes
 **/
static function story($slug, $values) {
    $query = 'UPDATE Story SET slug = ? , short_title = ?, title = ?, synopsis = ?, '.
        'show_synopsis = ?, show_cover_thumb = ?, show_page_thumb = ?, '.
        'show_page_title = ?, show_page_num = ?, show_prevnext = ?, '.
        'show_firstlast = ?, show_updates = ? WHERE slug = ?';
    $params = array(array($values['slug'], $values['short_title'], $values['title'], $values['synopsis'], 
                          $values['show_synopsis'], $values['show_cover_thumb'], $values['show_page_thumb'],
                          $values['show_page_title'], $values['show_page_num'], $values['show_prevnext'],
                          $values['show_firstlast'], $values['show_updates'], $slug));
    $db = new DB();
    $result = $db->run($query, $params);
    $db->close();
    return $result;
}


/**
 * add a new, blank story to the database
 * @param $slug the slug for the new story
 * @return a two dimensional array around '1' if successful, 0 if not
 **/
static function new_story($slug, $title, $short) {
    $query1 = 'INSERT INTO Slug (slug, reserved) VALUES (?, false)';
    $query2 = 'INSERT INTO Story (slug, title, short_title) VALUES (?, ?, ?)';
    $params1 = array(array($slug));
    $params2 = array(array($slug, $title, $short));
    $db = new DB();
    $result = $db->run($query1, $params1);
    $result = $db->run($query2, $params2);
    $db->close();
    return $result;
}



/**
 * update a story page's other attributes, given its storyid
 * @param $id the id of the story page
 * @param $values the new values of the story page's attributes
 **/
static function storypage($id, $values) {
    $query1 = 'UPDATE Content SET title = ?, body = ?, publish = FROM_UNIXTIME(?), edited = NOW() WHERE id = ?';
    $params1 = array(array($values['title'], $values['body'], $values['publish'], $id));
    $query2 = 'UPDATE Story_Page SET iscover = ?, thumbnail = ?, author_comment = ? WHERE id = ?';
    $params2 = array(array($values['iscover'], $values['thumbnail'], $values['author_comment'], $id));
    $db = new DB();
    $db->run('SET time_zone = "UTC"', array(array()));// set timzeone (for timestamp processing)
    $result = $db->run($query1, $params1);
    $result = $db->run($query2, $params2);
    $db->close();
    return $result;
}


/**
 * add a new, blank basic page to the database
 * @param $slug the slug for the new basic page
 * @param $userid the userid of the user creating the basic page
 * @return a two dimensional array around '1' if successful, 0 if not
 **/
// INSERT INTO Slug (slug, reserved) VALUES (?, false)
// INSERT INTO Story (slug) VALUES (?)
static function new_basicpage($slug, $title, $short, $userid) {
    $query1 = 'INSERT INTO Slug (slug, reserved) VALUES (?, false)';
    $slugparams = array(array($slug));
    $query2 = 'INSERT INTO Content (created, edited, title, userid) VALUES (NOW(), NOW(), ?, ?)';
    $params2 = array(array($title, $userid));
    $query3 = 'SELECT id FROM Content WHERE title = ? ORDER BY created';
    $query4 = 'INSERT INTO Basic_Page (slug, short_title, id) VALUES (?, ?, ?)';
    // $params4 is set later
    
    $db = new DB();
    $db->run('SET time_zone = "+0:00"', array(array()));// set timzeone (for timestamp processing)
    $result = $db->run($query1, $slugparams);
    $result = $db->run($query2, $params2);
    $result = $db->run($query3, array(array($title)));
    $id = $result[0][0]['id'];
    $params4 = array(array($slug, $short, $id));
    $result = $db->run($query4, $params4);
    $db->close();
    return $id;
}


/**
 * update a basaic page's other attributes, given its storyid
 * @param $id the id of the basic page
 * @param $values the new values of the basic page's attributes
 **/
static function basicpage($id, $values) {
    $query1 = 'UPDATE Content SET title = ?, body = ?, publish = FROM_UNIXTIME(?), edited = NOW() WHERE id = ?';
    $params1 = array(array($values['title'], $values['body'], $values['publish'], $id));
    $query2 = 'UPDATE Basic_Page SET short_title = ?, slug = ? WHERE id = ?';
    $params2 = array(array($values['short_title'], $values['slug'], $id));
    
    $db = new DB();
    $db->run('SET time_zone = "UTC"', array(array()));// set timzeone (for timestamp processing)
    $result = $db->run($query1, $params1);
    $result = $db->run($query2, $params2);
    $db->close();
    return $result;
}


/**
 * add a new, blank story page to the database
 * @param $slug the slug of the story the story page is being added to
 * @param $userid the userid of the user creating the story
 * @return a two dimensional array around the ID of the newly created story
 **/
static function insert_storypage($slug, $userid) {
    // setup queries
    $query_pagenum = 'SELECT IFNULL(MAX(pagenum), -1) AS "pagenum" FROM Story_Page WHERE story = (SELECT storyid FROM Story WHERE slug = ?)';
    $params_pagenum = array(array($slug));
    
    $query1 = 'INSERT INTO Content (created, edited, title, userid) VALUES (NOW(), NOW(), CONCAT("Page ", ?), ?)';
    // $params1 will be specified later (depends on result of $query_pagenum)
    
    $query2 = 'INSERT INTO Story_Page (id, story, pagenum) VALUES ('.
                '(SELECT MAX(id) FROM Content WHERE created = (SELECT MAX(created) FROM Content)), '.
                '(SELECT MAX(storyid) FROM Story WHERE slug = ?), ? )';
    // $params2 will be specified later (depends on result of $query_pagenum)
    
    $query3 = 'SELECT id FROM Content WHERE created = (SELECT MAX(created) FROM Content)';
    $params3 = array(array($timestamp));
    
    $db = new DB();
    
    // get the page number and set the parameter arrays that require it
    $pagenum = $db->run($query_pagenum, $params_pagenum);
    $pagenum = intval($pagenum[0][0]['pagenum']) + 1;
    $params1 = array(array($pagenum, $userid));
    $params2 = array(array($slug, $pagenum));
    
    // insert records
    $db->run('SET time_zone = "UTC"', array(array()));// set timzeone (for timestamp processing)
    $db->run($query1, $params1);
    $db->run($query2, $params2);
    
    // get the content ID
    $result = $db->run($query3, $params3);
    $db->close();
    return $result;
}



/**
 * Delete a basic page, given its id
 * @param $id id of the basic page to delete
 * @return two dimensional array with 1 inside if deleted, otherwise 0 inside
 **/
static function delete_basicpage($id) {
    $queryslug = 'SELECT slug FROM Basic_Page WHERE id = ?';
    $query1 = 'DELETE FROM Basic_Page WHERE id = ?';
    $query2 = 'DELETE FROM Content WHERE id = ?';
    $params = array(array($id));
    
    $db = new DB();
    $slug = $db->run($queryslug, $params);
    $slug = $slug[0][0]['slug'];
    $result = $db->run($query1, $params);
    $result = $db->run($query2, $params);
    $db->close();
    self::delete_slug($slug);// delete the slug too
    return $result;
}

/**
 * Delete a story page, given its id
 * @param $id id of the story page to delete
 * @return two dimensional array with 1 inside if deleted, otherwise 0 inside
 **/
static function delete_storypage($id) {
    $query1 = 'DELETE FROM Story_Page WHERE id = ?';
    $query2 = 'DELETE FROM Content WHERE id = ?';
    $params = array(array($id));
    
    $db = new DB();
    $result = $db->run($query1, $params);
    $result = $db->run($query2, $params);
    $db->close();
    return $result;
}

/**
 * Delete a story (only if it has no pages - this does not cascade!), given its slug
 * @param $slug slug of the story to delete
 * @return two dimensional array with 1 inside if deleted, otherwise 0 inside
 **/
static function delete_story($slug) {
    $query1 = 'DELETE FROM Story WHERE slug = ? AND 0 IN '.
                    '(SELECT COUNT(id) FROM Story_Page WHERE story IN '.
                            '(SELECT storyid FROM (SELECT storyid FROM Story WHERE slug = ?) subq));';
    $query2 = 'DELETE FROM Slug WHERE slug = ?';
    $params1 = array(array($slug, $slug));
    $params2 = array(array($slug));
    
    $db = new DB();
    $result = $db->run($query1, $params1);
    $result = $db->run($query2, $params2);
    $db->close();
    return $result;
}




}

?>