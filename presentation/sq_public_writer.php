<?php

require_once(realpath(__dir__).'/../business/sq_utils.php');
//require_once(realpath(__dir__).'/../business/sq_do.php');
require_once(realpath(__dir__).'/sq_public_layout_writer.php');

class sq_public_writer {


/**
 * tead the slugs, and use them to call the correct writer function
 * @param $slug array of all the web page's slugs
 * @return the web page's HTML
 **/
static function write($slugs) {
    $story = sq_do::story_title($slugs[1]);
    
    if ($story) {// if the first slug is a story's slug
        if (!sq_utils::exists($slugs[2])) {
            return self::story($slugs[1]);
        }
        else if (ctype_digit($slugs[2])) {
            return self::story_page($slugs[1], $slugs[2]);
        }
        else {
            return self::page_not_found($slugs[1]);
        }
    }
    
    else if (sq_do::basicpage_exists_slug($slugs[1])) {// if the first slug is a basic page's slug
        return self::basic_page($slugs[1]);
    }
    else {
        return self::page_not_found();
    }
}

/**
 * write HTML for an error page
 * @return HTML for an error page
 **/
static function page_not_found() {
    $out = '';
    $out .= sq_public_layout_writer::above('404 Page Not Found');
    $out .= '<h2>404 Error</h2>'."\n\n";
    $out .= "\n\t".'<p>';
    $out .= "\n\t\t".'Page not found.';
    $out .= "\n\t".'</p>';
    $out .= sq_public_layout_writer::below();
    return $out;
}

/**
 * Write HTML for the index/homepage
 * @return HTM for the index page
 **/
static function index() {
    $uri = sq_utils::get_uri();
    $order = explode(' / ', sq_do::get_setting('home_format'));
    $out = '';
    $out .= sq_public_layout_writer::above('Home');
    
    foreach ($order as $section) {
        switch($section) {
            case 'stories':
                $out .= '<h2>Story Updates:</h2>'."\n\n";
                $out .= "\n".'<div class="updates">';
                $out .= self::updates();
                $out .= "\n".'</div>';
                break;
            case 'text':
                $out .= "\n\t".'<div class="hometext">';
                $out .= "\n\t\t".sq_utils::sanitize_out(sq_do::get_setting('home_text'), true);
                $out .= "\n\t".'</div>';
                break;
            case 'news':
                if (strlen(sq_do::get_setting('news_rss_url')) > 0 && strcmp(sq_do::get_setting('home_show_news'), 0) != 0) {
                    $out .= "\n".'<h2>News:</h2>';
                    $out .= "\n\t".'<div class="homenews news">';
                    $out .= self::news_items();
                    $out .= "\n\t".'</div>';
                }
                break;
        }
    }    
    $out .= sq_public_layout_writer::below();
    return $out;
}

/**
 * Write HTML for the homepage story updates
 * @return HTML for the homepage story updates
 **/
static function updates() {
    $out = '';
    $updates = sq_do::get_update();
    $format = explode(' / ', sq_do::get_setting('home_update_format'));
    $uri = sq_utils::get_uri();
    if (count($updates) > 0) {
        foreach ($updates as $update) {
            $out .= "\n\t".'<div class="update">';
            $out .= "\n\t\t".'<h3>'.sq_utils::sanitize_out($update['storytitle']).'</h3>';
            foreach ($format as $element) {
                switch($element) {
                    case 'title':
                        $out .= "\n\t\t".'<h4>'
                                .'<a href="'.$uri.'/'.$update['slug'].'/'.$update['pagenum'].'">'
                                .sq_utils::sanitize_out($update['title']).'</a></h4>';
                        break;
                    case 'thumbnail':
                        if (sq_utils::exists($update['thumbnail'])) {
                            $out .= "\n\t\t"
                                .'<a href="'.$uri.'/'.$update['slug'].'/'.$update['pagenum'].'">'
                                .'<img src="'.$uri.$update['thumbnail'].'" class="thumbnail" /></a>';
                        }
                        break;
                    case 'body':
                        $out .= "\n\t\t".'<div class="pagebody">';
                        $out .= "\n\t\t\t".sq_utils::sanitize_out($update['body'], true);
                        $out .= "\n\t\t".'</div>';
                        break;
                }
            }
            /*$out .= "\n\t\t".'<h3>'.sq_utils::sanitize_out($update['storytitle']).': '.
                        '<a href="'.$uri.'/'.$update['slug'].'/'.$update['pagenum'].'">'.
                        sq_utils::sanitize_out($update['title']).'</a></h3>';*/
            $out .= "\n\t".'</div>';
        }
    }
    else {
        $out .= "\n\t".'<div class="update"><p>There are no story updates.</p></div>';
    }
    return $out;
}

/**
 * write HTML for the news page
 * @return HTML for the news page
 **/
static function news() {
    $out = '';
    $out .= sq_public_layout_writer::above('News');
    $out .= "\n".'<h2>News</h2>';
    $out .= "\n\t".'<div class="newspage news">';
    $out .= self::news_items();
    $out .= "\n\t".'</div>';
    $out .= sq_public_layout_writer::below();
    return $out;
}


/**
 * write the HTML for the list of news items (not including wrappers and headers)
 * @return HTML for the list of news items
 **/
static function news_items() {
    $out = '';
    $newsItems = sq_do::get_news();
    if (is_array($newsItems)) {
        foreach ($newsItems as $news) {
            $out .= "\n\t\t".'<div class="newsitem">';
            if (sq_utils::exists($news['title'])) {
                $out .= "\n\t\t\t".'<h3 class="news-title">';
                if (sq_utils::exists($news['guid'])) {
                    $out .= "\n\t\t\t\t".'<a href="'.$news['guid'].'">'.$news['title'].'</a>';
                }
                else {
                    $out .= "\n\t\t\t\t".$news['title'];
                }
                $out .= "\n\t\t\t".'</h3>';
            }
            if (sq_utils::exists($news['date'])) {
                $out .= "\n\t\t\t".'<div class="news-date">';
                $out .= "\n\t\t\t\t".$news['date'];
                $out .= "\n\t\t\t".'</div>';
            }
            if (sq_utils::exists($news['desc'])) {
                $out .= "\n\t\t\t".'<div class="news-body">';
                $out .= "\n\t\t\t\t".$news['desc'];
                $out .= "\n\t\t".'</div>';
            }
            $out .= "\n\t\t".'</div>';
        }
    }
    else {
        $out .= "\n\t\t".'<div class="newsitem">';
        $out .= "\n\t\t\t".'<h3 class="news-title error">';
        $out .= "\n\t\t\t\t".'News feed could not be displayed.';
        $out .= "\n\t\t\t".'</h3>';
        $out .= "\n\t\t".'</div>';
    }
    
    $source = sq_do::get_news_source();
    if ($source != false) {
        $out .= "\n\t\t".'<div class="news-source">Read more news at <a href="'.
                $source['url'].'">'.$source['title'].'</a></div>';
    }
    return $out;
}

/**
 * write HTML for a story archive page
 * @return HTML for a story archive page
 **/
static function story($slug) {
    $story = sq_do::get_story($slug, true);
    if (!isset($story) || count($story) == 0) {
        return self::page_not_found();
    }
    $pages = sq_do::get_storypages($slug);
    $uri = sq_utils::get_uri();
    $out = '';
    $out .= sq_public_layout_writer::above($story['title']);
    $out .= "\n\t".'<h2>'.$story['title'].'</h2>';
    
    if ($story['show_synopsis']) {
        $out .= "\n\t".'<div class="synopsis">';
        $out .= "\n\t\t".sq_utils::sanitize_out($story['synopsis'], true);
        $out .= "\n\t".'</div>';
    }
    
    $out .= "\n\t".'<div class="archive';
    if ($story['show_cover_thumb']) { $out .= ' withcoverthumb';}
    if ($story['show_cover_thumb']) { $out .= ' withpagethumb';}
    $out .= '">';
    
    // convert to relative page numbers
    $cover = 0;
    foreach ($pages as $id=>$page) {
        if ($page['iscover']) {
            $cover = intval($page['pagenum']);
            $pages[$id]['display_pagenum'] = 0;
        }
        else {
            $pages[$id]['display_pagenum'] = intval($page['pagenum']) - $cover;
        }
    }
    
    // write all the story pages
    $story['show_title'] = true;
    $counter = 0;
    foreach ($pages as $page) {
        $counter = $counter + 1;
        
        // start first chapter section
        if ($counter == 1 && !$page['iscover']) {
            $out .= "\n\t\t".'<div class="chapter">';
        }
        
        // open div
        if ($page['iscover']) {
            if ($counter != 1) {// end chapter section
                $out .= "\n\t\t".'</div>';
            }
            $out .= "\n\t\t".'<div class="coverlink';
            if ($story['show_cover_thumb']) { $out .= ' withcoverthumb';}
            $out .= '">';
        }
        else {
            $out .= "\n\t\t\t".'<div class="pagelink">';
        }
        
        // link
        $out .= "\n\t\t\t\t".'<a href="'.$uri.'/'.$slug.'/'.$page['pagenum'].'">';
        
        // thumbnail
        if (sq_utils::exists($page['thumbnail']) &&
            ((!$page['iscover'] && $story['show_page_thumb']) ||
            ($page['iscover'] && $story['show_cover_thumb']))) {
                $out .= "\n\t\t\t\t".'<img src="'.$uri.$page['thumbnail'].'" class="thumbnail" />';
                
        }
        
        // pagenum
        if (!$page['iscover'] && $story['show_page_num']) {
            $out .= "\n\t\t\t\t\t".'<span class="pagenum">'.$page['display_pagenum'].'</span>';
        }
        
        // title
        if ($story['show_page_title'] || $page['iscover']) {
            $out .= "\n\t\t\t\t\t".'<span class="title">'.$page['title'].'</span>';
        }
        
        // end last chapter section
        if ($counter == count($pages)) {
            $out .= "\n\t\t".'</div>';
        }
        
        // close link and close div
        $out .= "\n\t\t\t".'</a>'."\n\t\t".'</div>';
        
        // end chapter section
        if ($page['iscover']) {
            $out .= "\n\t\t".'<div class="chapter">';
        }
    }
    
    $out .= "\n\t".'</div>';
    $out .= sq_public_layout_writer::below();
    return $out;
}
 
/**
 * write HTML for a story page
 * @param $slug the slug of the story
 * @param $pagenum the page number of the story page
 * @return HTML for a story page
 **/
static function story_page($slug, $pagenum) {
    $page = sq_do::get_storypage_by_pagenum($slug, $pagenum, false);
    if (!isset($page) || count($page) == 0) {
        return self::page_not_found();
    }
    $pagenav = self::pagenav($slug, $pagenum);
    $out = '';
    $out .= sq_public_layout_writer::above($page['title']);
    
    if ($page['iscover']) {
         $out .= "\n\t".'<span class="cover">';
    }
    
    $out .= "\n\t".'<h2>'.sq_utils::sanitize_out($page['title']).'</h2>';
    
    $out .= $pagenav;
    
    // body
    $out .= "\n\t".'<div class="pagebody">';
    $out .= "\n\t\t".sq_utils::sanitize_out($page['body'], true);
    $out .= "\n\t".'</div>';
    
    $out .= $pagenav;
    
    // author comment
    if (sq_utils::exists($page['author_comment'])) {
        $out .= "\n\t".'<div class="authorcomment">';
        $out .= "\n\t\t".'<h3>Creator'."'".'s Comments</h3>';
        $out .= "\n\t\t".sq_utils::sanitize_out($page['author_comment'], true);
        $out .= "\n\t".'</div>';
    }
    
    if ($page['iscover']) {
         $out .= "\n\t".'</span>';
    }
    
    $out .= sq_public_layout_writer::below();
    return $out;
}

/**
 * write HTML for the previous/next/first/last storypage navigation
 * @param $slug the slug of the story
 * @param $thisPagenum the page number of the story page
 * @return HTML for the previous/next/first/last storypage navigation
 **/
static function pagenav($slug, $thisPagenum) {
    $pagelinks = sq_do::get_storypage_links($slug, $thisPagenum);
    $uri = sq_utils::get_uri();
    $out = '';
    $out .= "\n\t".'<nav class="nav pagenav">';
    
    if (isset($pagelinks) && is_array($pagelinks)) {
        foreach ($pagelinks as $type=>$pagenum) {
            if (strcmp($type, 'first') == 0) {
                $out .= "\n\t\t".'<div class="forwardlinks">';
            }
            else if (strcmp($type, 'next') == 0) {
                $out .= "\n\t\t".'<div class="backlinks">';
            }
            
            if (isset($pagenum) && strlen($pagenum) > 0 && intval($pagenum) != $thisPagenum) {
                $out .= "\n\t\t\t".'<div class="'.$type.'">';
                $out .= '<a href="'.$uri.'/'.$slug.'/'.$pagenum.'"><span>'.$type.'</span></a>';
                $out .= '</div>';
            }
            
            if (strcmp($type, 'previous') == 0) {
                $out .= "\n\t\t".'</div>';
            }
            else if (strcmp($type, 'last') == 0) {
                $out .= "\n\t\t".'</div>';
            }
        }
    }
    
    $out .= "\n\t".'</nav>';
    return $out;
}



/**
 * write HTML for a basic page
 * @param $slug the slug of the basic page
 * @return HTML for a story page
 **/
static function basic_page($slug) {
    $page = sq_do::get_basicpage_by_slug($slug, false);
    if (!isset($page) || count($page) == 0) {
        return self::page_not_found();
    }
    $out = '';
    $out .= sq_public_layout_writer::above($page['title']);
    
    $out .= "\n\t".'<h2>'.sq_utils::sanitize_out($page['title']).'</h2>';
    
    $out .= $pagenav;
    
    // body
    $out .= "\n\t".'<div class="pagebody">';
    $out .= "\n\t\t".sq_utils::sanitize_out($page['body'], true);
    $out .= "\n\t".'</div>';
    
    $out .= sq_public_layout_writer::below();
    return $out;
}




}

?>