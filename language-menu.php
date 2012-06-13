<?php

/**
 * language-menu
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2008 - 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 *
 */

global $database;
$table = TABLE_PREFIX.'pages';
// get the actual page link
if (PAGE_ID == 0) {
    // PAGE_ID 0 indicates the LEPTON search!
    $check_link = '/search/index.php';
}
else {
    $SQL = "SELECT `link` FROM $table WHERE `page_id`='".PAGE_ID."'";
    if (!$active_page = $database->get_one($SQL, MYSQL_ASSOC)) {
        $msg = ($database->is_error()) ? $database->get_error() : '- page not found! -';
        trigger_error(sprintf('[TEMPLATE - %s] %s', __LINE__, $msg), E_USER_ERROR);
    }
    else {
        // get the link without language separator
        $check_link = substr($active_page.PAGE_EXTENSION, strlen('/en'));
    }
}
// attention: the menu number must fit !!!
$menu = 1;
// get the level 0 language pages
$SQL = "SELECT `page_id`, `link`, `page_title`, `menu_title`, `language` FROM $table WHERE `level`='0' AND `visibility`='public' AND `menu`='$menu' ORDER BY `position` ASC";
if (false === ($query = $database->query($SQL)))
    trigger_error(sprintf('[TEMPLATE - %s] %s', __LINE__, $database->get_error()), E_USER_ERROR);
$language_menu = '<div class="language-menu-container">';
$max = $query->numRows();
// create the language menu
for ($i=0; $i < $max; $i++) {
    if (!$lang = $query->fetchRow(MYSQL_ASSOC))
        trigger_error(sprintf('[TEMPLATE - %s] %s', __LINE__, $database->get_error()), E_USER_ERROR);
    $class = 'language-item';
    $alternate = sprintf('rel="alternate" hreflang="%s" ', strtolower($lang['language']));
    if (LANGUAGE == $lang['language']) {
        $class .= ' active'; // actual selected item
        $alternate = ''; // don't indicate the active language as alternate!
    }
    if ($i == 0) $class .= ' first'; // first item of the language menu
    if ($i == $max-1) $class .= ' last'; // last item of the language menu
    $language_menu .= '<span class="'.$class.'">'; // create span and class
    $link = WB_URL.PAGES_DIRECTORY.$lang['link'].PAGE_EXTENSION; // default link to the language root
    if (file_exists(WB_PATH.PAGES_DIRECTORY.'/'.strtolower($lang['language']).$check_link))
        // it exists a corresponding page to the actual language, use it!
        $link = WB_URL.PAGES_DIRECTORY.'/'.strtolower($lang['language']).$check_link;
    // complete the language link - setting "rel" and "hreflang" to specify the alternate languages
    // see also: http://support.google.com/webmasters/bin/answer.py?hl=en&answer=189077
    if (empty($alternate)) {
        $language_menu .= $lang['page_title'].'</span>';
    }
    else {
        $language_menu .= sprintf('<a %shref="%s">%s</a></span>', $alternate, $link, $lang['page_title']);
    }
}
$language_menu .= '</div>';
echo $language_menu;

?>