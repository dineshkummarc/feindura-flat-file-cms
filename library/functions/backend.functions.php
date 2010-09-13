<?php
/*
 * feindura - Flat File Content Management System
 * Copyright (C) Fabian Vogelsteller [frozeman.de]
 *
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not,see <http://www.gnu.org/licenses/>.
 */
/**
 * This file contains the main functions used by the backend of the feindura-CMS.
 * 
 * @package [backend]
 * 
 * @version 1.33
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.33 isAdmin(): add immediately return true if no remote_user exists 
 *    - 1.32 fixed editFiles()
 *    - 1.31 add checkStyleFiles()
 * 
 */


// ** -- redirect ----------------------------------------------------------------------------------
// leitet automatisch weiter auf die angegeben seite
// -----------------------------------------------------------------------------------------------------
// $goToPage      [seite auf die weitergeleitet werden soll (String)],
// $goToCategory  [the category in which to redirect (String)],
// $time          [the time in seconds after which it will redirect (Number)]
//function redirect($goToCategory, $goToPage, $time = 2) {
  //global $adminConfig;
  
  //echo '<meta http-equiv="refresh" content="'.$time.'; URL='.$adminConfig['basePath'].'?category='.$goToCategory.'&amp;page='.$goToPage.'">';
  //echo '<script type="text/javascript">
    /* <![CDATA[ */
      //document.location.href = "'.$adminConfig['basePath'].'?category='.$goToCategory.'&page='.$goToPage.'"
    /* ]]> */
    //</script>';
  //echo 'You should be automatically redirected, if not click <a href="'.$adminConfig['basePath'].'?category='.$goToCategory.'&amp;page='.$goToPage.'">here</a>.';
//}

/**
 * <b>Name</b> showErrorsInWindow()<br />
 * 
 * gets the PHP errors, to show them in the errorWindow
 * 
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$errorWindow</var> the errorWindow text which will extended with the given errors from PHP
 * 
 * @param int     $errorCode the PHP errorcode
 * @param string  $errorText the PHP error message
 * @param string  $errorFile the filename of the file where the erro occurred
 * @param int     $errorLine the line number where the error occurred
 * 
 * @return bool TRUE if PHP should not handle th errors, FALSE if PHP should show the errors
 * 
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function showErrorsInWindow($errorCode, $errorText, $errorFile, $errorLine) {
    
    // var
    $error = '<span class="rawError">'.$errorText."<br /><br />".$errorFile.' on line '.$errorLine."</span>\n";
    
    switch ($errorCode) {
    case E_USER_ERROR:
        return false;
        break;

    //case E_USER_WARNING: case E_USER_NOTICE:
        //$GLOBALS['errorWindow'] .= $error;
        //break;

    default:
        $GLOBALS['errorWindow'] .= $error;
        break;
    }
    
    /* to prevent the internal PHP error reporting */
    return true;
}

/**
 * <b>Name</b> isAdmin()<br />
 * 
 * Open the .htpasswd file and check if one of the usernames is:
 * "admin", "adminstrator", "superuser", "root" or "frozeman".
 * If one of the above usernames exist and the current user has one of this usernames it returns TRUE,
 * otherwise FALSE.<br />
 * If no user with the above usernames exists it assume that there is no admin and returns TRUE.
 * 
 * <b>Used Constants</b><br />
 *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
 *    
 * <b>Used Global Variables</b><br />
 *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php})
 *     
 * @return bool TRUE if the current user is an admin, or no admins exist, otherwise FALSE
 * 
 * 
 * @version 1.01
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.01 add immediately return true if no remote_user exists
 *    - 1.0 initial release
 * 
 */
function isAdmin() {
  
  if(!isset($_SERVER["REMOTE_USER"]))
    return true;
  
  $currentUser = strtolower($_SERVER["REMOTE_USER"]);
  
  // checks if the current user has a username like:
  if($currentUser == 'admin' || $currentUser == 'administrator' || $currentUser == 'root' || $currentUser == 'superuser') {
    return true;
  } else { // otherwise it checks if in the htpasswd is one of the above usernames, if not return true

    // checks for userfile
    if($getHtaccess = @file(DOCUMENTROOT.$GLOBALS['adminConfig']['basePath'].'.htaccess')) {

      // try to find the .htpasswd path
      foreach($getHtaccess as $htaccessLine) {
        if(strstr(strtolower($htaccessLine),'authuserfile')) {
          $passwdFilePath = substr($htaccessLine,strpos(strtolower($htaccessLine),'authuserfile')+13);
          $passwdFilePath = str_replace("\n", '', $passwdFilePath);
          $passwdFilePath = str_replace("\r", '', $passwdFilePath);          
          $passwdFilePath = str_replace(" ", '', $passwdFilePath);
        }    
      }
      
      // go trough users in .htpasswd, if there is any user with the above names
      // and current user have not such a username return false
      if($getHtpasswd = @file($passwdFilePath)) {        
        
        $adminExists = false;        
        foreach($getHtpasswd as $htpasswdLine) {
          $user = explode(':',strtolower($htpasswdLine));          
          
          if($user[0] == 'admin' || $user[0] == 'administrator' || $user[0] == 'root' || $user[0] == 'superuser')
            $adminExists = true;
        }
        
        // checks if the currentuser has such a name
        if($adminExists) {          
          return false; // ONLY WHEN AN ADMIN EXITS AND THE CURRENT USER ISNT THE ADMIN return false
        } else
          return true;
      
      } else
        return true;
      
    } else { // there is no user file      
      return true;
    }    
  }  
  return true;  
}

/**
 * <b>Name</b> getNewCatgoryId()<br />
 * 
 * Returns a new category ID, which is the highest category ID + 1.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$categoryConfig</var> the categories-settings config (included in the {@link general.include.php})
 *     
 * @return int a new category ID
 * 
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function getNewCatgoryId() {
  
  // gets the highest id
  $highestId = 0;
  if(is_array($GLOBALS['categoryConfig'])) {
    foreach($GLOBALS['categoryConfig'] as $category) {          
      if($category['id'] > $highestId)
        $highestId = $category['id'];
    }
    return ++$highestId;
  } else
    return 1;
}

/**
 * <b>Name</b> saveCategories()<br />
 * 
 * Saves the category-settings config array to the "config/category.config.php" file.
 * 
 * <b>Used Constants</b><br />
 *    - <var>PHPSTARTTAG</var> the php start tag
 *    - <var>PHPENDTAG</var> the php end tag
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$generalFunctions</var> to reset the {@link getStoredPagesIds} (included in the {@link general.include.php})
 * 
 * @param array $newCategories a $categoryConfig array to save
 * 
 * @return bool TRUE if the file was succesfull saved, otherwise FALSE
 * 
 * @example backend/categoryConfig.array.example.php of the $categoryConfig array
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function saveCategories($newCategories) {
  
  // �ffnet die category.config.php zum schreiben
  if($file = fopen(dirname(__FILE__)."/../../config/category.config.php","w")) {
 
      // *** write CATEGORIES
      flock($file,2); //LOCK_EX
      fwrite($file,PHPSTARTTAG); //< ?php
      
      // ->> GO through EVERY catgory and write it
      foreach($newCategories as $category) {

        // CHECK BOOL VALUES and change to FALSE
        $category['public'] = (isset($category['public']) && $category['public']) ? 'true' : 'false';
        $category['createdelete'] = (isset($category['createdelete']) && $category['createdelete']) ? 'true' : 'false';
        $category['thumbnail'] = (isset($category['thumbnail']) && $category['thumbnail']) ? 'true' : 'false';
        $category['plugins'] = (isset($category['plugins']) && $category['plugins']) ? 'true' : 'false';
        $category['showtags'] = (isset($category['showtags']) && $category['showtags']) ? 'true' : 'false';
        $category['showpagedate'] = (isset($category['showpagedate']) && $category['showpagedate']) ? 'true' : 'false';
        $category['sortbypagedate'] = (isset($category['sortbypagedate']) && $category['sortbypagedate']) ? 'true' : 'false';
        $category['sortascending'] = (isset($category['sortascending']) && $category['sortascending']) ? 'true' : 'false';
        
        // -> CHECK depency of PAGEDATE
        if($category['showpagedate'] == 'false')
          $category['sortbypagedate'] = 'false';
        
        if($category['sortbypagedate'] == 'true')
          $category['showpagedate'] = 'true';
        
        // -> CHECK if the THUMBNAIL HEIGHT/WIDTH is empty, and add the previous ones
        if(!isset($category['thumbWidth']))
          $category['thumbWidth'] = $GLOBALS['categoryConfig']['id_'.$category['id']]['thumbWidth'];
        if(!isset($category['thumbHeight']))
          $category['thumbHeight'] = $GLOBALS['categoryConfig']['id_'.$category['id']]['thumbHeight'];
        
        // adds absolute path slash on the beginning and implode the stylefiles
        $category['styleFile'] = prepareStyleFilePaths($category['styleFile']);
      
        // bubbles through the page, category and adminConfig to see if it should save the styleheet-file path, id or class-attribute
        $category['styleFile'] = setStylesByPriority($category['styleFile'],'styleFile',true);
        $category['styleId'] = setStylesByPriority($category['styleId'],'styleId',true);
        $category['styleClass'] = setStylesByPriority($category['styleClass'],'styleClass',true);
        
        // -> CLEAN all " out of the strings
        foreach($category as $postKey => $post) {    
          $category[$postKey] = str_replace(array('\"',"\'"),'',$post);
        }
        
        // WRITE
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['id'] =              ".$category['id'].";\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['name'] =            '".$category['name']."';\n");
        
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['public'] =          ".$category['public'].";\n");        
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['createdelete'] =    ".$category['createdelete'].";\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['thumbnail'] =       ".$category['thumbnail'].";\n");        
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['plugins'] =         ".$category['plugins'].";\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['showtags'] =        ".$category['showtags'].";\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['showpagedate'] =    ".$category['showpagedate'].";\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['sortbypagedate'] =  ".$category['sortbypagedate'].";\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['sortascending'] =   ".$category['sortascending'].";\n\n");
        
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['styleFile'] =       '".$category['styleFile']."';\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['styleId'] =         '".$category['styleId']."';\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['styleClass'] =      '".$category['styleClass']."';\n\n");
        
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['thumbWidth'] =      '".$category['thumbWidth']."';\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['thumbHeight'] =     '".$category['thumbHeight']."';\n");
        fwrite($file,"\$categoryConfig['id_".$category['id']."']['thumbRatio'] =      '".$category['thumbRatio']."';\n\n\n");
        
      }    
      fwrite($file,'return $categoryConfig;');
      
      fwrite($file,PHPENDTAG); //? >
    flock($file,3); //LOCK_UN
    fclose($file);
    
    // reset the stored page ids
    $GLOBALS['generalFunctions']->storedPagesIds = null;
    
    return true;
  } else
    return false;
}

/**
 * <b>Name</b> moveCategories()<br />
 * 
 * Change the order of the <var>$categoryConfig</var> array.
 * 
 * @param array       &$categoryConfig the $categoryConfig array (will also be changed global)
 * @param int         $category        the ID of the category to move
 * @param string      $direction       the direction to move, can be "up" or "down"
 * @param false|int   $position        (optional) the exact position where the category should be moved ("1" is top), if is number the $direction paramter doesn't count
 * 
 * @return bool TRUE if the category was succesfull moved, otherwise FALSE
 * 
 * @example backend/categoryConfig.array.example.php of the $categoryConfig array
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function moveCategories(&$categoryConfig, $category, $direction, $position = false) {
  
  $direction = strtolower($direction);
  
  // ->> CHECKS
  // if they fail it returns the unchanged $categoryConfig array
  if(is_array($categoryConfig) &&                         // is categories is array
    is_numeric($category) &&                          // have the given category id is a number
    $category == $categoryConfig['id_'.$category]['id'] &&     // dows the category exists in the $categoryConfig array
    (!$direction || $direction == 'up' || $direction == 'down') &&
    (!$position || is_numeric($position))) {   // is the right direction is given
    
    // vars
    $count = 1;
    $currentPosition = false;
    $dropedCategories = array();
    
    // -> finds out the position in the $categoryConfig array
    // and extract this category from it
    foreach($categoryConfig as $sortCategory) {
      //echo '>'.$sortCategory['id'].' -> '.$count.'<br />';
      
      if($sortCategory['id'] == $category) {
        $currentPosition = $count;
        $extractCategory = $sortCategory;
      } else  
        $dropedCategories[$sortCategory['id']] = $sortCategory;
      
      $count++;
    }    
    //echo 'currentPos: '.$currentPosition;
    
    // -> creates a new array with the category at the new position
    $count = 1;
    $sortetCategories = array();
    foreach($dropedCategories as $sortCategory) {
      
      // MOVE BY POSITION
      if($position !== false && is_numeric($position)) {
        
         //echo 'exactPos: '.$position;
        
        // if the position is lower than 1
        if($position < 1) {
          if($count == 1)
            $sortetCategories[] = $extractCategory;
          // put it at the first position
         $sortetCategories[] = $dropedCategories[$sortCategory['id']];
        }
        
        // if the position is higher than the count() of the array
        if($position > count($dropedCategories)) {
          $sortetCategories[] = $dropedCategories[$sortCategory['id']];
          // put it at the last position
          if($count == count($dropedCategories))
            $sortetCategories[] = $extractCategory;
        }
        
        // if it is in the array put it at the exact position
        if($position >= 1 && $position <= count($dropedCategories)) {
          if($position == $count)
            $sortetCategories[] = $extractCategory;
          // put it at the first position
          $sortetCategories[] = $dropedCategories[$sortCategory['id']];
        }
      
      // MOVE BY DIRECTION
      } else {
        // move the category UP
        // -------------
        if($direction == 'up') {
          
          // if the currentPosition is outside of the foreach
          if(($currentPosition - 1) <= 1) {
            // add the extract at the beginging of the array
            if($count == 1)
              $sortetCategories[] = $extractCategory;
          
          // add the extract at the new position
          } elseif(($currentPosition - 1) == $count)
              $sortetCategories[] = $extractCategory;
        }
        
        // adds the unmoved categories to the array
        // -------------
        $sortetCategories[] = $dropedCategories[$sortCategory['id']];
        
        // move the category DOWN
        // -------------
        if($direction == 'down') {
          
          // if the currentPosition is outside of the foreach
          if(($currentPosition + 1) > count($dropedCategories)) {
            // add the extract at the end of the array
            if($count == count($dropedCategories))
              $sortetCategories[] = $extractCategory;
          
          // add the extract at the new position
          } elseif($currentPosition == $count)
              $sortetCategories[] = $extractCategory; 
        }
      }
     
      $count++;
    }
    
    // -> set back the id as index
    $categoryConfig = array();
    foreach($sortetCategories as $sortetCategory) {
      echo '';
      $categoryConfig['id_'.$sortetCategory['id']] = $sortetCategory;
    }
    
    return true;
  
  } else
    return false;
}

/**
 * <b>Name</b> saveAdminConfig()<br />
 * 
 * Saves the administrator-settings config array to the "config/admin.config.php" file.
 * 
 * <b>Used Constants</b><br />
 *    - <var>PHPSTARTTAG</var> the php start tag
 *    - <var>PHPENDTAG</var> the php end tag
 * 
 * @param array $adminConfig a $adminConfig array to save
 * 
 * @return bool TRUE if the file was succesfull saved, otherwise FALSE
 * 
 * @example backend/adminConfig.array.example.php of the $adminConfig array
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function saveAdminConfig($adminConfig) {

  // **** opens admin.config.php for writing
  if($file = fopen(dirname(__FILE__)."/../../config/admin.config.php","w")) {
    
    // CHECK BOOL VALUES and change to FALSE
    $adminConfig['speakingUrl'] = (isset($adminConfig['speakingUrl']) && $adminConfig['speakingUrl']) ? 'true' : 'false';
    $adminConfig['user']['fileManager'] = (isset($adminConfig['user']['fileManager']) && $adminConfig['user']['fileManager']) ? 'true' : 'false';
    $adminConfig['user']['editWebsiteFiles'] = (isset($adminConfig['user']['editWebsiteFiles']) && $adminConfig['user']['editWebsiteFiles']) ? 'true' : 'false';
    $adminConfig['user']['editStylesheets'] = (isset($adminConfig['user']['editStylesheets']) && $adminConfig['user']['editStylesheets']) ? 'true' : 'false';
    $adminConfig['setStartPage'] = (isset($adminConfig['setStartPage']) && $adminConfig['setStartPage']) ? 'true' : 'false';
    $adminConfig['page']['createdelete'] = (isset($adminConfig['page']['createdelete']) && $adminConfig['page']['createdelete']) ? 'true' : 'false';
    $adminConfig['page']['thumbnails'] = (isset($adminConfig['page']['thumbnails']) && $adminConfig['page']['thumbnails']) ? 'true' : 'false';
    $adminConfig['page']['plugins'] = (isset($adminConfig['page']['plugins']) && $adminConfig['page']['plugins']) ? 'true' : 'false';
    $adminConfig['page']['showtags'] = (isset($adminConfig['page']['showtags']) && $adminConfig['page']['showtags']) ? 'true' : 'false';
    
    flock($file,2); // LOCK_EX
    fwrite($file,PHPSTARTTAG); //< ?php
    
    fwrite($file,"\$adminConfig['url'] =              '".$adminConfig['url']."';\n");
    fwrite($file,"\$adminConfig['basePath'] =         '".$adminConfig['basePath']."';\n");
    fwrite($file,"\$adminConfig['savePath'] =         '".$adminConfig['savePath']."';\n");
    fwrite($file,"\$adminConfig['uploadPath'] =       '".$adminConfig['uploadPath']."';\n");  
    fwrite($file,"\$adminConfig['websitefilesPath'] = '".$adminConfig['websitefilesPath']."';\n");
    fwrite($file,"\$adminConfig['stylesheetPath'] =   '".$adminConfig['stylesheetPath']."';\n");    
    fwrite($file,"\$adminConfig['dateFormat'] =       '".$adminConfig['dateFormat']."';\n");
    fwrite($file,"\$adminConfig['speakingUrl'] =      ".$adminConfig['speakingUrl'].";\n\n");
    
    fwrite($file,"\$adminConfig['varName']['page'] =     '".$adminConfig['varName']['page']."';\n");  
    fwrite($file,"\$adminConfig['varName']['category'] = '".$adminConfig['varName']['category']."';\n");  
    fwrite($file,"\$adminConfig['varName']['modul'] =    '".$adminConfig['varName']['modul']."';\n\n");
    
    fwrite($file,"\$adminConfig['user']['fileManager'] =      ".$adminConfig['user']['fileManager'].";\n");
    fwrite($file,"\$adminConfig['user']['editWebsiteFiles'] = ".$adminConfig['user']['editWebsiteFiles'].";\n");
    fwrite($file,"\$adminConfig['user']['editStylesheets'] =  ".$adminConfig['user']['editStylesheets'].";\n");  
    fwrite($file,"\$adminConfig['user']['info'] =             '".$adminConfig['user']['info']."';\n\n");
    
    fwrite($file,"\$adminConfig['setStartPage'] =            ".$adminConfig['setStartPage'].";\n");
    fwrite($file,"\$adminConfig['page']['createdelete'] =    ".$adminConfig['page']['createdelete'].";\n");
    fwrite($file,"\$adminConfig['page']['thumbnails'] =      ".$adminConfig['page']['thumbnails'].";\n");    
    fwrite($file,"\$adminConfig['page']['plugins'] =         ".$adminConfig['page']['plugins'].";\n");
    fwrite($file,"\$adminConfig['page']['showtags'] =        ".$adminConfig['page']['showtags'].";\n\n");
    
    fwrite($file,"\$adminConfig['editor']['enterMode'] =  '".$adminConfig['editor']['enterMode']."';\n");
    fwrite($file,"\$adminConfig['editor']['styleFile'] =  '".$adminConfig['editor']['styleFile']."';\n");
    fwrite($file,"\$adminConfig['editor']['styleId'] =    '".$adminConfig['editor']['styleId']."';\n");  
    fwrite($file,"\$adminConfig['editor']['styleClass'] = '".$adminConfig['editor']['styleClass']."';\n\n");  
  
    fwrite($file,"\$adminConfig['pageThumbnail']['width'] =  '".$adminConfig['pageThumbnail']['width']."';\n");
    fwrite($file,"\$adminConfig['pageThumbnail']['height'] = '".$adminConfig['pageThumbnail']['height']."';\n");
    fwrite($file,"\$adminConfig['pageThumbnail']['ratio'] =  '".$adminConfig['pageThumbnail']['ratio']."';\n");
    fwrite($file,"\$adminConfig['pageThumbnail']['path'] =   '".$adminConfig['pageThumbnail']['path']."';\n\n");
    
    fwrite($file,"return \$adminConfig;");
       
    fwrite($file,PHPENDTAG); //? >
    flock($file,3); //LOCK_UN
    fclose($file);   
    
    return true;
  } else
    return false;
}

/**
 * <b>Name</b> saveWebsiteConfig()<br />
 * 
 * Saves the website-settings config array to the "config/website.config.php" file.
 * 
 * <b>Used Constants</b><br />
 *    - <var>PHPSTARTTAG</var> the php start tag
 *    - <var>PHPENDTAG</var> the php end tag
 * 
 * @param array $websiteConfig a $websiteConfig array to save
 * 
 * @return bool TRUE if the file was succesfull saved, otherwise FALSE
 * 
 * @example backend/websiteConfig.array.example.php of the $websiteConfig array
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function saveWebsiteConfig($websiteConfig) {
   
  // opens the file for writing
  if($file = fopen(dirname(__FILE__)."/../../config/website.config.php","w")) {
    
    // CHECK BOOL VALUES and change to FALSE
    //$websiteConfig['noname'] = (isset($websiteConfig['noname']) && $websiteConfig['noname']) ? 'true' : 'false';
        
    // format keywords
    $keywords = preg_replace("/ +/", ' ', $websiteConfig['keywords']);
    $keywords = preg_replace("/,+/", ',', $keywords);
    $keywords = str_replace(', ',',', $keywords);
    $keywords = str_replace(' ,',',', $keywords);
    $keywords = str_replace(' ',',', $keywords);
    $websiteConfig['keywords'] = $keywords;
    
    // format all other strings
    $websiteConfig['title'] = $GLOBALS['generalFunctions']->prepareStringInput($websiteConfig['title']);
    $websiteConfig['publisher'] = $GLOBALS['generalFunctions']->prepareStringInput($websiteConfig['publisher']);
    $websiteConfig['copyright'] = $GLOBALS['generalFunctions']->prepareStringInput($websiteConfig['copyright']);
    $websiteConfig['keywords'] = $GLOBALS['generalFunctions']->prepareStringInput($websiteConfig['keywords']);
    $websiteConfig['description'] = $GLOBALS['generalFunctions']->prepareStringInput($websiteConfig['description']);
    
    // *** write
    flock($file,2); //LOCK_EX
      fwrite($file,PHPSTARTTAG); //< ?php
  
      fwrite($file,"\$websiteConfig['title']          = '".$websiteConfig['title']."';\n");
      fwrite($file,"\$websiteConfig['publisher']      = '".$websiteConfig['publisher']."';\n");
      fwrite($file,"\$websiteConfig['copyright']      = '".$websiteConfig['copyright']."';\n");
      fwrite($file,"\$websiteConfig['keywords']       = '".$websiteConfig['keywords']."';\n");
      fwrite($file,"\$websiteConfig['description']    = '".$websiteConfig['description']."';\n");
      fwrite($file,"\$websiteConfig['email']          = '".$websiteConfig['email']."';\n\n");
      
      fwrite($file,"\$websiteConfig['startPage']      = '".$websiteConfig['startPage']."';\n\n");
      
      fwrite($file,"return \$websiteConfig;");
    
      fwrite($file,PHPENDTAG); //? >
    flock($file,3); //LOCK_UN
    fclose($file);
  
    return true;
  } else
    return false;
}

/**
 * <b>Name</b> saveStatisticConfig()<br />
 * 
 * Saves the statiostic-settings config array to the "config/statistic.config.php" file.
 * 
 * <b>Used Constants</b><br />
 *    - <var>PHPSTARTTAG</var> the php start tag
 *    - <var>PHPENDTAG</var> the php end tag
 * 
 * @param array $statisticConfig a $statisticConfig array to save
 * 
 * @return bool TRUE if the file was succesfull saved, otherwise FALSE
 * 
 * @example backend/statisticConfig.array.example.php of the $statisticConfig array
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function saveStatisticConfig($statisticConfig) {
   
  // opens the file for writing
  if($file = fopen("config/statistic.config.php","w")) {
    
    // CHECK BOOL VALUES and change to FALSE
    //$statisticConfig['noname'] = (isset($statisticConfig['noname']) && $statisticConfig['noname']) ? 'true' : 'false';
    
    // WRITE
    flock($file,2); //LOCK_EX
      fwrite($file,PHPSTARTTAG); //< ?php
  
      fwrite($file,"\$statisticConfig['number']['mostVisitedPages']        = '".$statisticConfig['number']['mostVisitedPages']."';\n");
      fwrite($file,"\$statisticConfig['number']['longestVisitedPages']     = '".$statisticConfig['number']['longestVisitedPages']."';\n");
      fwrite($file,"\$statisticConfig['number']['lastEditedPages']         = '".$statisticConfig['number']['lastEditedPages']."';\n\n");
      
      fwrite($file,"\$statisticConfig['number']['refererLog']    = '".$statisticConfig['number']['refererLog']."';\n");
      fwrite($file,"\$statisticConfig['number']['taskLog']       = '".$statisticConfig['number']['taskLog']."';\n\n");
      
      
      fwrite($file,"return \$statisticConfig;");
    
      fwrite($file,PHPENDTAG); //? >
    flock($file,3); //LOCK_UN
    fclose($file);
  
    return true;
  } else
    return false;
}

/**
 * <b>Name</b> savePluginsConfig()<br />
 * 
 * Saves the plugins-settings config array to the "config/plugins.config.php" file.
 * 
 * <b>Used Constants</b><br />
 *    - <var>PHPSTARTTAG</var> the php start tag
 *    - <var>PHPENDTAG</var> the php end tag
 * 
 * @param array $pluginsConfig a $pluginsConfig array to save
 * 
 * @return bool TRUE if the file was succesfull saved, otherwise FALSE
 * 
 * @example backend/pluginsConfig.array.example.php of the $adminConfig array
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function savePluginsConfig($pluginsConfig) {

  // **** opens plugin.config.php for writing
  if($file = fopen(dirname(__FILE__)."/../../config/plugins.config.php","w")) {
    
    // CHECK BOOL VALUES and change to FALSE   
    flock($file,2); // LOCK_EX
    fwrite($file,PHPSTARTTAG); //< ?php
    
    if(is_array($pluginsConfig)) {
      foreach($pluginsConfig as $key => $value) {
        $pluginsConfig[$key]['active'] = (isset($pluginsConfig[$key]['active']) && $pluginsConfig[$key]['active']) ? 'true' : 'false';    
        fwrite($file,"\$pluginsConfig['$key']['active'] = ".$pluginsConfig[$key]['active'].";\n");
      }
    }
    
    fwrite($file,"\nreturn \$pluginsConfig;");
       
    fwrite($file,PHPENDTAG); //? >
    flock($file,3); //LOCK_UN
    fclose($file);   
    
    return true;
  } else
    return false;
}

/**
 * <b>Name</b> movePage()<br />
 * 
 * Moves a file into a new category directory.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php})
 *    - <var>$generalFunctions</var> to reset the {@link getStoredPagesIds} (included in the {@link general.include.php})
 * 
 * @param int $page         the page ID
 * @param int $fromCategory the ID of the category where the page is situated
 * @param int $toCategory   the ID of the category where the file will be moved to 
 * 
 * @return bool TRUE if the page was succesfull moved, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function movePage($page, $fromCategory, $toCategory) {
  
  // if there are pages not in a category set the category to empty
  if($fromCategory === false || $fromCategory == 0)
    $fromCategory = '';
  if($toCategory === false || $toCategory == 0)
    $toCategory = '';
    
  // MOVE categories
  if(copy(DOCUMENTROOT.$GLOBALS['adminConfig']['savePath'].$fromCategory.'/'.$page.'.php',
    DOCUMENTROOT.$GLOBALS['adminConfig']['savePath'].$toCategory.'/'.$page.'.php') &&
    unlink(DOCUMENTROOT.$GLOBALS['adminConfig']['savePath'].$fromCategory.'/'.$page.'.php')) {
    // reset the stored page ids
    $GLOBALS['generalFunctions']->storedPagess = null;
    $GLOBALS['generalFunctions']->storedPagesIds = null;
    
    return true;
  } else
    return false;
}

/**
 * <b>Name</b> getNewPageId()<br />
 * 
 * Returns a new page ID, which is the highest page ID + 1.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$generalFunctions</var> for the {@link getStoredPagesIds} (included in the {@link general.include.php})
 * 
 * @return int a new page ID
 * 
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function getNewPageId() {
  
  // loads the file list in an array
  $pages = $GLOBALS['generalFunctions']->getStoredPageIds();
  
  $highestId = 0;
  
  // go trough the file list and look for the highest number
  if(is_array($pages)) {
    foreach($pages as $page) {
      $pageId = $page['page'];
          
      if($pageId > $highestId)
        $highestId = $pageId;
    }
  }
  $highestId++;
  
  return $highestId;
}

/**
 * <b>Name</b> prepareStyleFilePaths()<br />
 * 
 * Check the array with stylesheet files if they have a slash on the beginnging and if there are not empty.
 * Then implodes the array to a string like:
 * 
 * <samp>
 * /style/header.css|/style/content.css|/style/footer.css
 * </samp>
 * 
 * If the $givenStyleFiles parameter is already a string it passes it trough.
 * 
 * @param array $givenStyleFiles the array with stylesheetfile paths
 * 
 * @return array the cleaned stylesheet files array
 * 
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function prepareStyleFilePaths($givenStyleFiles) {
  
  //vars
  $styleFiles = array();
  
  if(is_string($givenStyleFiles))
    return $givenStyleFiles;
    
  if(is_array($givenStyleFiles)) {
    foreach($givenStyleFiles as $styleFile) {
      // ** adds a "/" on the beginning of all absolute paths
      if(!empty($styleFile) && !strstr($styleFile,'://') && substr($styleFile,0,1) !== '/')
          $styleFile = '/'.$styleFile;
      
      // adds back to the string only if its not empty
      if(!empty($styleFile))
        $styleFiles[] = $styleFile;
    }
  }
  return implode('|#|',$styleFiles);
}

/**
 * <b>Name</b> getStylesByPriority()<br />
 * 
 * Returns the right stylesheet-file path, ID or class-attribute.
 * If the <var>$givenStyle</var> parameter is empty,
 * it check if the category has a styleheet-file path, ID or class-attribute set return the value if not return the value from the {@link $adminConfig administartor-settings config}.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php}) 
 *    - <var>$categoryConfig</var> the categories-settings config (included in the {@link general.include.php})
 * 
 * @param string $givenStyle the string with the stylesheet-file path, id or class
 * @param string $styleType  the key for the $pageContent, {@link $categoryConfig} or {@link $adminConfig} array can be "styleFile", "styleId" or "styleClass" 
 * @param int    $category   the ID of the category to bubble through
 * 
 * @return string the right stylesheet-file, ID or class
 * 
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function getStylesByPriority($givenStyle,$styleType,$category) {
  
  // check if the $givenStyle is empty
  if(empty($givenStyle)) {
  
    return (!empty($GLOBALS['categoryConfig']['id_'.$category][$styleType]))
    ? $GLOBALS['categoryConfig']['id_'.$category][$styleType]
    : $GLOBALS['adminConfig']['editor'][$styleType];
  
  // otherwise it passes through the $givenStyle parameter
  } else
    return $givenStyle;
  
}

/**
 * <b>Name</b> setStylesByPriority()<br />
 * 
 * Bubbles through the stylesheet-file path, ID or class-attribute
 * of the page, category and adminSetup and check if the stylesheet-file path, ID or class-attribute already exist.
 * Ff the <var>$givenStyle</var> parameter is empty,
 * it check if the category has a styleheet-file path, ID or class-attribute set return the value if not return the value from the {@link $adminConfig administartor-settings config}.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php}) 
 *    - <var>$categoryConfig</var> the categories-settings config (included in the {@link general.include.php})
 * 
 * @param string   $givenStyle the string with the stylesheet-file path, id or class
 * @param string   $styleType  the key for the $pageContent, {@link $categoryConfig} or {@link $adminConfig} array can be "styleFile", "styleId" or "styleClass" 
 * @param int|true $category   the ID of the category to bubble through or TRUE when the stylesheet-file path, id or class is from a category
 * 
 * @return string an empty string or the $givenStyle parameter if it was not found through while bubbleing up
 * 
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function setStylesByPriority($givenStyle,$styleType,$category) {
  
  // prepare string
  if($styleType != 'styleFile')
    $givenStyle = str_replace(array('#','.'),'',$givenStyle);
  elseif($styleType == 'styleFile' && !empty($givenStyle) && substr($givenStyle,0,1) !== '/')
    $givenStyle = '/'.$givenStyle;
    
  // compare string with category
  if($category !== true && !empty($GLOBALS['categoryConfig']['id_'.$category][$styleType])) {      
    if($givenStyle == $GLOBALS['categoryConfig']['id_'.$category][$styleType]) 
      $givenStyle = '';
      
  //  or adminConfig
  } elseif($givenStyle == $GLOBALS['adminConfig']['editor'][$styleType]) {
    $givenStyle = '';
  }  
  
  /*
  $givenStyle = ((!empty($GLOBALS['categoryConfig']['id_'.$category][$styleType]) && $givenStyle == $GLOBALS['categoryConfig']['id_'.$category][$styleType]) ||
                 (empty($GLOBALS['categoryConfig']['id_'.$category][$styleType]) && $givenStyle == $GLOBALS['adminConfig']['editor'][$styleType])) 
  ? $givenStyle
  : '';
  
  */
  
  return $givenStyle;
}

/**
 * <b>Name</b> editFiles()<br />
 * 
 * Generates a editable textfield with a file selection and a input for creating new files.
 * 
 * <b>Used Constants</b><br />
 *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$_POST</var> to get which form is open
 *    - <var>$langFile</var> the backend language-file (included in the {@link general.include.php})
 *    - <var>$savedForm</var> the variable to tell which form was saved (set in the {@link saveEditedFiles})
 * 
 * @param string		$filesPath	         the path where all files (also files in subfolders) will be shown for editing
 * @param string		$siteName	           a site name which will be set to the $_GET['site'] variable in the formular action attribute
 * @param string		$status		           a status name which will be set to the $_GET['status'] variable in the formular action attribute
 * @param string		$titleText	         a title text which will be displayed as the title of the edit files textfield
 * @param string		$anchorName	         the name of the anchor which will be added to the formular action attribute
 * @param string|false		$fileType      (optional) a filetype which will be added to each ne created file
 * @param string|array|false	$excluded	 (optional) a string (seperated with ",") or array with files or folder names which should be excluded from the file selection, if FALSE no file will be excluded
 * 
 * @uses generalFunctions::readFolderRecursive()	reads the $filesPath folder recursive and loads all file paths in an array
 * 
 * @return void displayes the file edit textfield
 * 
 * @version 1.01
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.01 put fileType to the classe instead of the id of the textarea
 *    - 1.0 initial release
 * 
 */
function editFiles($filesPath, $siteName, $status, $titleText, $anchorName, $fileType = false, $excluded = false) {
  
  // var
  $fileTypeText = null;
  $isFiles = false;
  
  // shows the block below if it is the ones which is saved before
  $hidden = ($_GET['status'] == $status || $GLOBALS['savedForm'] === $status) ? '' : ' hidden';

  echo '<form action="?site='.$siteName.'#'.$anchorName.'" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
        <div>
        <input type="hidden" name="send" value="saveEditedFiles" />
        <input type="hidden" name="status" value="'.$status.'" />
        <input type="hidden" name="filesPath" value="'.$filesPath.'" />';
  if($fileType)
    echo '<input type="hidden" name="fileType" value=".'.$fileType.'" />';
  echo '</div>';
  
  echo '<div class="block'.$hidden.'">
          <h1><a href="#" name="'.$anchorName.'" id="'.$anchorName.'">'.$titleText.'</a></h1>
          <div class="content"><br />';
      
  //echo $filesPath.'<br />';      
  // gets the files out of the directory --------------
  // adds the DOCUMENTROOT  
  $filesPath = str_replace(DOCUMENTROOT,'',$filesPath);  
  $dir = DOCUMENTROOT.$filesPath;
  if(!empty($filesPath) && is_dir($dir)) {
    $files = $GLOBALS['generalFunctions']->readFolderRecursive($filesPath);
    $files = $files['files'];

  	// ->> EXLUDES files or folders
  	if($excluded !== false) {
  	  
  	  // -> is string convert to array
  	  if(is_string($excluded)) {
  	    $excluded = explode(',',$excluded);
  	  }
  	  
  	  if(is_array($excluded)) {
  	    
  	    foreach($files as $file) {
  	      
  	      $foundToExclud = false;
  	      
  	      // looks if any of a excluded file is found
  	      foreach($excluded as $excl) {
  	        if(strstr($file,$excl))
  		        $foundToExclud = true;
  	      }
  
  	      // then exclud them
  	      if($foundToExclud === false)
  	        $newFiles[] = $file;	      
  	    }
  	    // set new files array to the old one
  	    $files = $newFiles;
  	  }
  	  
  	}
  	$isDir = true;	
  	
  	// only if still are files left
  	if(is_array($files) && !empty($files)) {
  	  $isFiles = true;
  	  // sort the files in a natural way (alphabetical)
  	  natsort($files);
  	}
  // dont show files but show directory error       
  } else {
    echo '<code>"'.$filesPath.'"</code> <b>'.$GLOBALS['langFile']['editFilesSettings_noDir'].'</b>';
    $isDir = false;
  }
  
  
  // GETS ACTUAL FILE ----------------------------------
  if($_GET['status'] == $status)
    $editFile = $_GET['file'];
  
  // wenn noch nicht per Dateiauswahl $editfile kreiert wurde
  if(empty($editFile) && isset($files)) {
    $editFile = $files[0];
  }
  
  if($isDir) {

    // FILE SELECTION ------------------------------------
    if($isFiles && isset($files)) {
      echo '<div class="editFiles left">
            <h2>'.$GLOBALS['langFile']['editFilesSettings_chooseFile'].'</h2>
            <input type="text" value="'.$filesPath.'" readonly="readonly" style="width:auto;" size="'.(strlen($filesPath)-2).'" />'."\n";
      echo '<select onchange="changeEditFile(\''.$siteName.'\',this.value,\''.$status.'\',\''.$anchorName.'\');">'."\n";
 
            // listet die Dateien aus dem Ordner als Mehrfachauswahl auf
            foreach($files as $cFile) {
              $onlyFile = str_replace($filesPath,'',$cFile);
              if($editFile == $cFile)
                echo '<option value="'.$cFile.'" selected="selected">'.$onlyFile.'</option>'."\n";
              else
                echo '<option value="'.$cFile.'">'.$onlyFile.'</option>'."\n";    
            }
      echo '</select></div>'."\n\n";
    } // -------------------------------------------------
    
    // create a NEW FILE ---------------------------------
    if($fileType)
      $fileTypeText = '<b>.'.$fileType.'</b>';
    echo '<div class="editFiles right">
          <h2>'.$GLOBALS['langFile']['editFilesSettings_createFile'].'</h2>
          <input name="newFile" style="width:200px;" class="thumbnailToolTip" title="'.$GLOBALS['langFile']['editFilesSettings_createFile'].'::'.$GLOBALS['langFile']['editFilesSettings_createFile_inputTip'].'" /> '.$fileTypeText.'
          </div>';
  }
  
  // OPEN THE FILE -------------------------------------
  if(@is_file(DOCUMENTROOT.$editFile)) {
    $editFileOpen = fopen(DOCUMENTROOT.$editFile,"r");  
    $file = @fread($editFileOpen,filesize(DOCUMENTROOT.$editFile));
    fclose($editFileOpen);
    
    echo '<input type="hidden" name="file" value="'.$editFile.'" />'."\n";

    $file = str_replace(array('<','>'),array('&lt;','&gt;'),$file);
    
    $fileType = (strtolower($fileType) == 'css') ? ' css' : ' mixed';
    
    echo '<textarea name="fileContent" cols="90" rows="30" class="editFiles'.$fileType.'" id="editFiles'.rand(1,9999).'">'.$file.'</textarea>';
  }  
  
  
  if($isDir) {
    if($isFiles)
      echo '<a href="?site='.$siteName.'&amp;status=deleteEditFiles&amp;editFilesStatus='.$status.'&amp;file='.$editFile.'#'.$anchorName.'" onclick="openWindowBox(\'library/sites/windowBox/deleteEditFiles.php?site='.$siteName.'&amp;status=deleteEditFiles&amp;editFilesStatus='.$status.'&amp;file='.$editFile.'&amp;anchorName='.$anchorName.'\',\''.$GLOBALS['langFile']['editFilesSettings_deleteFile'].'\');return false;" class="cancel left toolTip" title="'.$GLOBALS['langFile']['editFilesSettings_deleteFile'].'::" style="float:left;"></a>';
    echo '<br /><br /><input type="submit" value="" name="saveEditedFiles" class="button submit right" title="'.$GLOBALS['langFile']['form_submit'].'" />';
  }
  echo '</div>
      <div class="bottom"></div>
    </div>
    </form>';
}

/**
 * <b>Name</b> saveEditedFiles()<br />
 * 
 * Save the files edited in {@link editFiles()}.
 * 
 * <b>Used Constants</b><br />
 *    - <var>$_POST</var> for the file data
 *    - <var>DOCUMENTROOT</var> the absolut path of the webserver 
 * 
 * @param string &$savedForm	to set which form was is saved
 * 
 * @return bool TRUE if the file was succesfull saved, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function saveEditedFiles(&$savedForm) {
    
  // add DOCUMENTROOT
  $file = str_replace(DOCUMENTROOT,'',$_POST['file']);  
  $file = DOCUMENTROOT.$file;    
  $_POST['filesPath'] = str_replace(DOCUMENTROOT,'',$_POST['filesPath']);  
  $_POST['filesPath'] = DOCUMENTROOT.$_POST['filesPath'];
  
  
  // ->> SAVE FILE
  if(@is_file($file) && empty($_POST['newFile'])) {
    
    //$_POST['fileContent'] = preg_replace("#[\r\n]+#","\n",$_POST['fileContent']);
    
    $_POST['fileContent'] = str_replace('\"', '"', $_POST['fileContent']);
    $_POST['fileContent'] = str_replace("\'", "'", $_POST['fileContent']);
    //$_POST['fileContent'] 	= str_replace("<br />", "", $_POST['fileContent']);
    $_POST['fileContent'] = stripslashes($_POST['fileContent']);
    
    // wandelt umlaut in HTML zeichen um
    $_POST['fileContent'] = htmlentities($_POST['fileContent'],ENT_NOQUOTES,'UTF-8');      
    // changes & back, because of the $auml;
    $_POST['fileContent'] = str_replace("&amp;", "&", $_POST['fileContent']);
    // wandelt die php einleitungstags wieder in zeichen um
    $_POST['fileContent'] = str_replace(array('&lt;','&gt;'),array('<','>'),$_POST['fileContent']);
    
    if($file = fopen($file,"w")) {
    flock($file,2);
    fwrite($file,$_POST['fileContent']);
    flock($file,3);
    fclose($file);      
    
    $_GET['file'] = $_POST['file'];
    $_GET['status'] = $_POST['status'];
    $savedForm = $_POST['status'];
    
      return true;      
    } else
      return false;
    
  // ->> NEW FILE
  } else { // creates a new file if a filename was input in the field
        
    //$_POST['newFile'] = str_replace( array(" ","%","+","&","#","!","?","$","�",'"',"'","(",")"), '_', $_POST['newFile']);
    $_POST['newFile'] = str_replace( array("�","�","�","�","�","�","�"), array("ae","ue","oe","ss","Ae","Ue","Oe"), $_POST['newFile']);
    $_POST['newFile'] = $GLOBALS['generalFunctions']->cleanSpecialChars($_POST['newFile'],'_');
    
    echo $_POST['newFile'];
    
    $_POST['newFile'] = str_replace($_POST['fileType'],'',$_POST['newFile']);
    
    $fullFilePath = $_POST['filesPath'].'/'.$_POST['newFile'].$_POST['fileType'];
    
    //clean vars
    $fullFilePath = preg_replace("/\/+/", '/', $fullFilePath);
    
    if($file = fopen($fullFilePath,"w")) {
      
      $_GET['file'] = str_replace(DOCUMENTROOT,'',$fullFilePath);       
      $_GET['status'] = $_POST['status'];
      $savedForm = $_POST['status'];
      
      return true;
    } else
      return false;
  }
}

/**
 * <b>Name</b> delDir()<br />
 * 
 * Deletes a directory and all files in it.
 * 
 * @param string $dir the absolute path to the directory which will be deleted, must end with a "/"  
 * 
 * @return bool TRUE if the directory was succesfull deleted, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function delDir($dir) {
    $files = glob($dir.'*', GLOB_MARK );
    if(is_array($files)) {
      foreach( $files as $file ){
          if( substr( $file, -1 ) == '/' )
              delTree( $file );
          else
              unlink( $file );
      }
    }
    
    if(rmdir( $dir ))
      return true;
    else
      return false;
}

/**
 * <b>Name</b> isFolderWarning()<br />
 * 
 * Check if the <var>$folder</var> parameter is a directory, otherwise it return a warning text.
 * 
 * <b>Used Constants</b><br />
 *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$langFile</var> the backend language-file (included in the {@link general.include.php})    
 * 
 * @param string $folder the absolut path of the folder to check
 * 
 * @return string|false a warning text if it's not a directory, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function isFolderWarning($folder) {
  
  if(substr($folder,0,1) != '/')
    $folder = '/'.$folder;

  if(is_dir(DOCUMENTROOT.$folder) === false) {
      return '<span class="warning"><b>&quot;'.$folder.'&quot;</b> -> '.$GLOBALS['langFile']['adminSetup_error_isFolder'].'</span><br />';
  } else
    return false;
}

/**
 * <b>Name</b> isWritableWarning()<br />
 * 
 * Check if a file/folder is writeable, otherwise it return a warning text.
 * 
 * <b>Used Constants</b><br />
 *    - <var>DOCUMENTROOT</var> the absolut path of the webserver
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$langFile</var> the backend language-file (included in the {@link general.include.php})
 * 
 * @param string $fileFolder the absolut path of a file/folder to check
 * 
 * @return string|false a warning text if it's not writable, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function isWritableWarning($fileFolder) {
  
  if(substr($fileFolder,0,1) != '/')
    $fileFolder = '/'.$fileFolder;
  
  if(file_exists(DOCUMENTROOT.$fileFolder) && is_writable(DOCUMENTROOT.$fileFolder) === false) {
    return '<span class="warning toolTip" title="'.$fileFolder.'::'.$GLOBALS['langFile']['adminSetup_error_writeAccess_tip'].'"><b>&quot;'.$fileFolder.'&quot;</b> -> '.$GLOBALS['langFile']['adminSetup_error_writeAccess'].'</span><br />';
  } else
    return false;
}

/**
 * <b>Name</b> isWritableWarningRecursive()<br />
 * 
 * Check if folders and it's containing files are writeable, otherwise it return a warning text.
 * 
 * @param array $folders an array with absolut paths of folders to check
 * 
 * @uses isFolderWarning()                        to check if the folder is a valid directory, if not return a warning
 * @uses isWritableWarning()                      to check every file/folder if it's writable, if not return a warning
 * @uses generalFunctions::readFolderRecursive()  to read all subfolders and files in a directory
 * 
 * @return string|false warning texts if they are not writable, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function isWritableWarningRecursive($folders) {
  
  //var
  $return = false;
  
  foreach($folders as $folder) {
    if(!empty($folder)) {
      if($isFolder = isFolderWarning($folder)) {
        $return .= $isFolder;
      } else {
        $return .= isWritableWarning($folder);
        if($readFolder = $GLOBALS['generalFunctions']->readFolderRecursive($folder)) {
          if(is_array($readFolder['folders'])) {
            foreach($readFolder['folders'] as $folder) {
              $return .= isWritableWarning($folder);
            }
          }
          if(is_array($readFolder['files'])) {
            foreach($readFolder['files'] as $files) {
              $return .= isWritableWarning($files);
            }
          }
        }
      }
    }
  }
  
  return $return;
}

/**
 * <b>Name</b> checkBasePath()<br />
 * 
 * Check if the current path of the CMS is matching the <var>$adminConfig['basePath']</var>
 * And if the current URL is matching the <var>$adminConfig['url']</var>.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php})
 * 
 * @return bool TRUE if there are matching, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function checkBasePath() {
  
  $hostProtocol = (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == 'off') ? 'http://' : 'https://';
  $baseUrl = str_replace('www.','',$GLOBALS['adminConfig']['url']);
  $checkUrl = str_replace('www.','',$hostProtocol.$_SERVER["HTTP_HOST"]);
  
  $checkPath = preg_replace('#/+#','/',dirname($_SERVER['PHP_SELF']).'/');
  
  if($GLOBALS['adminConfig']['basePath'] ==  $checkPath &&
     $baseUrl == $checkUrl)
    return true;
  else
    return false;
}

/**
 * <b>Name</b> checkBasePath()<br />
 * 
 * Retruns a warning if the current path of the CMS and the current URL is not matching with the ones set in the <var>$adminConfig</var>.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$langFile</var> the backend language-file (included in the {@link general.include.php})
 * 
 * @uses checkBasePath() to check if the current pathand URL are matching
 * 
 * @return string|false a warining if there are not matching, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function basePathWarning() {
  
  if(checkBasePath() === false) {
    return '<div class="block warning">
            <h1>'.$GLOBALS['langFile']['warning_fmsConfWarning_h1'].'</h1>
            <div class="content">
              <p>'.$GLOBALS['langFile']['warning_fmsConfWarning'].'</p><!-- needs <p> tags for margin-left:..--> 
            </div> 
            <div class="bottom"></div> 
          </div>';
  } else
    return false;
}

/**
 * <b>Name</b> checkBasePath()<br />
 * 
 * Retruns a warning if the current set start page is existing.
 * 
 * <b>Used Global Variables</b><br />
 *    - <var>$adminConfig</var> the administrator-settings config (included in the {@link general.include.php})
 *    - <var>$websiteConfig</var> the website-settings config (included in the {@link general.include.php})
 *    - <var>$generalFunctions</var> for the {@link generalFunctions::getPageCategory()} method (included in the {@link general.include.php})
 *    - <var>$langFile</var> the backend language-file (included in the {@link general.include.php})
 * 
 * @uses generalFunctions::getPageCategory() to get the category of the start page
 * 
 * @return string|false a warning if the start page doesn't exist, otherwise FALSE
 * 
 * @version 1.0
 * <br />
 * <b>ChangeLog</b><br />
 *    - 1.0 initial release
 * 
 */
function startPageWarning() {
  
  if($GLOBALS['adminConfig']['setStartPage'] && $GLOBALS['websiteConfig']['startPage'] && ($startPageCategory = $GLOBALS['generalFunctions']->getPageCategory($GLOBALS['websiteConfig']['startPage'])) != 0)
    $startPageCategory .= '/';
  else
    $startPageCategory = '';
  
  if($GLOBALS['adminConfig']['setStartPage'] && (!$GLOBALS['websiteConfig']['startPage'] || !file_exists(DOCUMENTROOT.$GLOBALS['adminConfig']['savePath'].$startPageCategory.$GLOBALS['websiteConfig']['startPage'].'.php'))) {
    return '<div class="block info">
            <h1>'.$GLOBALS['langFile']['warning_startPageWarning_h1'].'</h1>
            <div class="content">
              <p>'.$GLOBALS['langFile']['warning_startPageWarning'].'</p><!-- needs <p> tags for margin-left:..--> 
            </div> 
            <div class="bottom"></div> 
          </div>';
  } else
    return false;
}

?>