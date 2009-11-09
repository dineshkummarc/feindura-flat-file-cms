<?php
/*
    feindura - Flat File Content Management System
    Copyright (C) 2009 Fabian Vogelsteller [frozeman.de]

    This program is free software;
    you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
    without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program;
    if not,see <http://www.gnu.org/licenses/>.
*/
// library/functions/general.functions.php version 0.31


//error_reporting(E_ALL);

// ** -- getmicrotime ------------------------------------------------------------------------------
// returns a unix timestamp as float
// -------------------------------------------------------------------------------------------------
function getMicroTime(){
  list($usec, $sec) = explode(" ",microtime());
  return ((float)$usec + (float)$sec);
}

// ** -- secToMin ----------------------------------------------------------------------------------
// changes seconds in minutes
// -------------------------------------------------------------------------------------------------
function secToTime($sec) {
  $hours = floor($sec / 3600);
  $mins = floor(($sec -= ($hours * 3600)) / 60);  
  $seconds = floor($sec - ($mins * 60));
  
  // adds leading zeros
  if($hours < 10)
    $hours = '0'.$hours;
  if($mins < 10)
    $mins = '0'.$mins;
  if($seconds < 10)
    $seconds = '0'.$seconds;
  
  return $hours.':'.$mins.':'.$seconds;
}

// ** -- formatHighNumber ----------------------------------------------------------------------------------
// format a high number to 1 000 000,00
// -------------------------------------------------------------------------------------------------
function formatHighNumber($number,$decimalsNumber = 0) {  
  return number_format($number, $decimalsNumber, ',', ' ');
}

// ** -- showVisitTime -----------------------------------------------------------------------------
// SHOWs the visitTime as text
// -------------------------------------------------------------------------------------------------
function showVisitTime($time) {
  global $langFile;

  $hour = substr($time,0,2);
  $minute = substr($time,3,2);
  $second = substr($time,6,2);
  
  // adds the text for the HOURs
  if($hour == 0)
      $hour = false;
  // adds the text for the MINUTEs
  if($minute == 0)
      $minute = false;  
  // adds the text for the SECONDs
  if($second == 0)
      $second = false;
  
  // 01:01:01 Stunden
  if($hour !== false && $minute !== false && $second !== false)
      $time = '<b>'.$hour.'</b>:'.$minute.':'.$second;
  // 01:01 Stunden
  elseif($hour !== false && $minute !== false && $second === false)
      $time = '<b>'.$hour.'</b>:'.$minute;
  // 01:01 Minuten
  elseif($hour === false && $minute !== false && $second !== false)
      $time = '<b>'.$minute.'</b>:'.$second; 
  
  // 01 Stunden
  elseif($hour !== false && $minute === false && $second === false)
      $time = '<b>'.$hour.'</b>';
  // 01 Minuten
  elseif($hour === false && $minute !== false && $second === false)
      $time = '<b>'.$minute.'</b>';
  // 01 Sekunden
  elseif($hour === false && $minute === false && $second !== false)
      $time = '<b>'.$second.'</b>';
  
  
  // get the time together
  if($hour) {
    if($hour == 1)
      $time = $time.' <b>'.$langFile['log_hour_single'].'</b>';
    else
      $time = $time.' <b>'.$langFile['log_hour_multiple'].'</b>';
  } elseif($minute) {
    if($hour == 1)
      $time = $time.' <b>'.$langFile['log_minute_single'].'</b>';
    else
      $time = $time.' <b>'.$langFile['log_minute_multiple'].'</b>';
  } elseif($second) {
    if($hour == 1)
      $time = $time.' <b>'.$langFile['log_second_single'].'</b>';
    else
      $time = $time.' <b>'.$langFile['log_second_multiple'].'</b>';
  }
  
  
  // RETURN formated time
  return $time;
}

// ** -- saveLog --------------------------------------------------------------------------------
// saves a log file with time and task which was done
// -----------------------------------------------------------------------------------------------------
function saveLog($task,               // (String) a description of the task which was performed
                 $object = false) {   // (String) the page name or the name of the object on which the task was performed
  global $documentRoot;
  global $adminConfig;
  global $langFile;
  
  $logFile = dirname(__FILE__).'/../../'.'statistic/log.txt';
  
  if(file_exists($logFile))
    $oldLog = file($logFile);
    
  if($logFile = @fopen($logFile,"w")) {
    
    // adds a break before the object
    if($object)
      $object = '::'.$object;
    
    // -> create the new log string
    $newLog = date('Y')."-".date('m')."-".date('d').' '.date("H:i:s",time()).' '.$task.$object;
    
    // -> write the new log file
    flock($logFile,2);    
    fwrite($logFile,$newLog."\n");    
    $count = 1;
    foreach($oldLog as $oldLogRow) {
      fwrite($logFile,$oldLogRow);
      // stops the log after 120 entries
      if($count == 119)
        break;      
      $count++;
    }    
    flock($logFile,3);
    fclose($logFile);
    
    return true;
  } else return false;
}

// ** -- createBrowserChart --------------------------------------------------------------------------------
// creates a chart to display the browsers, used by the users
// -----------------------------------------------------------------------------------------------------
// $searchWordString         [Der String der die suchworte enth�lt im Format: 'suchwort,1|suchwort,3|...'  (String)],
// $minFontSize              [Die minimal Schriftartgr��e (Number)],
// $maxFontSize              [Die maximale Schriftartgr��e (Number)]
function createBrowserChart() {
  global $websiteStatistic;
  global $langFile;
  
  foreach(explode('|',$websiteStatistic['userBrowsers']) as $browser) {   
    $browsers[] =  explode(',',$browser);
  }
  
  $highestNumber = 1;
  foreach($browsers as $browser) {
    $highestNumber += $browser[1];
  }
  
  echo '<table class="tableChart"><tr>';
  foreach($browsers as $browser) {
    
    $tablePercent = $browser[1] / $highestNumber;
    $tablePercent = round($tablePercent * 100);
    
    // change the Names and the Colors
    if($browser[0] == 'firefox') {
      $browserName = 'Firefox';
      $browserColor = 'browserBg_firefox.png';
      $browserLogo = 'browser_firefox.png';
    } elseif($browser[0] == 'netscape') {
      $browserName = 'Netscape Navigator';
      $browserColor = 'browserBg_netscape.png';
      $browserLogo = 'browser_netscape.png';
    } elseif($browser[0] == 'chrome') {
      $browserName = 'Google Chrome';
      $browserColor = 'browserBg_chrome.png';
      $browserLogo = 'browser_chrome.png';
    } elseif($browser[0] == 'ie') {
      $browserName = 'Internet Explorer';
      $browserColor = 'browserBg_ie.png';
      $browserLogo = 'browser_ie.png';
    } elseif($browser[0] == 'opera') {
      $browserName = 'Opera';
      $browserColor = 'browserBg_opera.png';
      $browserLogo = 'browser_opera.png';
    } elseif($browser[0] == 'konqueror') {
      $browserName = 'Konqueror';
      $browserColor = 'browserBg_konqueror.png';
      $browserLogo = 'browser_konqueror.png';
    } elseif($browser[0] == 'lynx') {
      $browserName = 'Lynx';
      $browserColor = 'browserBg_lynx.png';
      $browserLogo = 'browser_lynx.png';
    } elseif($browser[0] == 'safari') {
      $browserName = 'Safari';
      $browserColor = 'browserBg_safari.png';
      $browserLogo = 'browser_safari.png';
    } elseif($browser[0] == 'mozilla') {
      $browserName = 'Mozilla';
      $browserColor = 'browserBg_mozilla.png';
      $browserLogo = 'browser_mozilla.png';
    } elseif($browser[0] == 'others') {
      $browserName = $langFile['log_browser_others'];
      $browserColor = 'browserBg_others.png';
      $browserLogo = 'browser_others.png';
    }  

    // calculates the text width and the cell width
    $textWidth = round(((strlen($browserName) + 20) * 4) + 45); // +45 = logo width + padding; +20 = percent text on the end    
    $cellWidth = round(780 * ($tablePercent / 100)); // 780px = the width of the 100% table    
    //echo '<div style="border-bottom:1px solid red;width:'.$textWidth.'px;">'.$cellWidth.' -> '.$textWidth.'</div>';
    
    // show tex only if cell is big enough
    if($cellWidth < $textWidth) {
      $cellText = '';
      $cellWidth -= 10;
      
      //echo $browserName.': '.$cellWidth.'<br>';
      
      // makes the browser logo smaller
      if($cellWidth < 40) {// 40 = logo width
        
        // change logo size
        if($cellWidth <= 0)
          $logoSize = 'width:0px;';
        else
          $logoSize = 'width:'.$cellWidth.'px;';
        
        // change cellpadding
        $createPadding = round(($cellWidth / 40) * 10);
        if($bigLogo === false && $createPadding < 5 && $createPadding > 0)
          $cellpadding = 'padding: '.$createPadding.'px 5px;';
        else
          $cellpadding = 'padding: 0px 5px;';

      }
        
      $bigLogo = false;
    } else {      
      $cellText = '&nbsp;&nbsp;<span style="position:relative; top:12px;"><b>'.$browserName.'</b> ('.$browser[1].')';
      $logoSize = '';
      $bigLogo = true;
      $cellpadding = '';
    }
    
    // SHOW the table cell with the right browser and color
    echo '<td style="'.$cellpadding.';width:'.$tablePercent.'%;background:url(library/image/bg/'.$browserColor.') repeat-x;" class="toolTip" title="'.$browserName.' '.$tablePercent.'%::'.$browser[1].' '.$langFile['log_visitCount'].'"><img src="library/image/sign/'.$browserLogo.'" style="float:left;'.$logoSize.'" alt="browser logo" />'.$cellText.'</td>';
  
  }
  echo '</tr></table>';


}

// ** -- createTagCloud --------------------------------------------------------------------------------
// creates a tag cloud out of the searchwords
// -----------------------------------------------------------------------------------------------------
// $searchWordString         [Der String der die suchworte enth�lt im Format: 'suchwort,1|suchwort,3|...'  (String)],
// $minFontSize              [Die minimal Schriftartgr��e (Number)],
// $maxFontSize              [Die maximale Schriftartgr��e (Number)]
function createTagCloud($searchWordString,$minFontSize = 10,$maxFontSize = 20) {
  global $langFile;
  
  if(!empty($searchWordString)) {
    foreach(explode('|',$searchWordString) as $searchWord) {   
      $searchWords[] =  explode(',',$searchWord);
    }
    
    $highestNumber = $searchWords[0][1];
    //$lowestNumber = $searchWords[count($searchWords)-1][1];
    
    // sort alphabetical
    sort($searchWords);
    
    foreach($searchWords as $searchWord) {
      
      $fontSize = $searchWord[1] / $highestNumber;
      $fontSize = round($fontSize * $maxFontSize) + $minFontSize;
      
      echo '<span style="font-size:'.$fontSize.'px;color:#C37B43;" class="toolTip" title="[span]&quot;'.$searchWord[0].'&quot;[/span] '.$langFile['log_searchwordtothissite_part1'].' [span]'.$searchWord[1].'[/span] '.$langFile['log_searchwordtothissite_part2'].'::">'.$searchWord[0].'</span>&nbsp;&nbsp;'."\n"; //<span style="color:#888888;">('.$searchWord[1].')</span>
    
    }
  } else {
    echo '<span class="blue" style="font-size:15px;">'.$langFile['log_notags'].'</span>';
  }
}


// ** -- isSpider ----------------------------------------------------------------------------------
// checks if the user-agent is bot/spider
// actual botlist from http://spiderlist.codeforgers.com/
// require spiders.xml/spiders.txt
// ---------------------------------------------------------------------------------------------------
function isSpider() {

  if(isset($_SERVER['HTTP_USER_AGENT'])) {
    $userAgent = ($_SERVER['HTTP_USER_AGENT']);
    
    // hohlt die botliste aus der spiders.xml liste
    // wenn php version > 5
    if(substr(phpversion(),0,1) >= 5) {
      if($xml = simplexml_load_file(dirname(__FILE__)."/../thirdparty/spiders.xml", 'SimpleXMLElement', LIBXML_NOCDATA)) {
       
        foreach($xml as $xmlData) {
            $bots[] = strtolower($xmlData['ident']);            
        }
      }
      
      /*
      //listet die bots auf damit ich sie in einer datei speicher kann
      foreach($bots as $bot) {
        echo $bot.',';
      }
      */
      
    } else { // php version 4
      // hohlt die botliste aus der spiders.txt liste
      $filename = dirname(__FILE__)."/../thirdparty/spiders.txt";
      $fd = fopen ($filename, "r");
      $bots = fread ($fd, filesize($filename));
      fclose ($fd);
      $bots = explode(',',$bots);
    }
    
    //var_dump($bots);
    
    $userAgent = strtolower($userAgent);
    $i = 0;
    $summe = count($bots);
  
    while ($i < $summe) {
      if ( strstr($userAgent, $bots[$i]))
        return true; // User-Agent ist ein Bot
      $i++;
    }
    // User-Agent is no Bot
    return false;
  
  } else return false; // HTTP_USER_AGENT ist nicht vorhanden
}

// * -- sortSearchwordString ----------------------------------------------------------------------------------
// sorts the searchword string, with the most counted at the beginning
// -----------------------------------------------------------------------------------------------------
function sortSearchwordString($a, $b) {
  $aExp = explode(',',$a);
  $bExp = explode(',',$b);
  
  if ($aExp[1] == $bExp[1]) {
      return 0;
  }      
  //echo 'A:'.$aExp[1].'<br />';
  //echo 'B:'.$bExp[1].'<br />';
  return ($aExp[1] > $bExp[1]) ? -1 : 1;
}

// ** -- addToDataString ----------------------------------------------------------------------------------
// adds to a string like "wordula,1|wordlem,5|wordquer,3" a new word or count up an exisiting word
// -----------------------------------------------------------------------------------------------------
function addToDataString($dataArray,       // (Array) an array with Strings to look for in the dataString
                         $dataString) {    // (String) the data String in the FORMAT: "wordula,1|wordlem,5|wordquer,3"
                              
  $exisitingDatas = explode('|',$dataString);
          
  // -> COUNTS THE EXISTING SEARCHWORDS
  $countExistingData = 0;
  $newDataString = '';
  foreach($exisitingDatas as $exisitingData) {          
    $exisitingData = explode(',',$exisitingData);
    $countExistingData++; 
    
    $countNewData = -1;
    foreach($dataArray as $data) {            
      $data = cleanSpecialChars($data,''); // entfernt Sonderzeichen
      $data = htmlentities($data,ENT_QUOTES, 'UTF-8');
      $data = strtolower($data);
      $countNewData++;
      
      // wenn es das Stichwort schon gibt
      if($exisitingData[0] == $data) {
        // z�hlt ein die Anzahl des Stichworts h�her
        $exisitingData[1]++;
        $foundSw[] = $data;
      }
    }
    
    // adds the old Searchwords (maybe counted up) to the String with the new ones            
    if(!empty($exisitingData[0])) {
      $newDataString .= $exisitingData[0].','.$exisitingData[1];
      if($countExistingData < count($exisitingDatas))
        $newDataString .= '|';
    }
  }
  
  // -> ADDS NEW SEARCHWORDS
  $countNewData = 0;
  foreach($dataArray as $data) {
  
    $data = cleanSpecialChars($data,''); // entfernt Sonderzeichen
    $data = htmlentities($data,ENT_QUOTES, 'UTF-8');
    $data = strtolower($data);
    $countNewData++;
    
    if(isset($foundSw) && is_array($foundSw))
      $foundSwStr = implode('|',$foundSw);
 
    if(!isset($foundSw) || (!empty($data) && strstr($foundSwStr,$data) == false)) {
      if(!empty($data)) {// verhindert das leere Suchwort strings gespeichert werden
        if(substr($newDataString,-1) != '|')
          $newDataString .= '|';
        // f�gt ein neues Suchwort in den String mit den Suchw�rtern ein                
        $newDataString .= $data.',1';
        
        if($countNewData < count($dataArray))
          $newDataString .= '|';
      }
    }
  }          
  //echo $newDataString.'<br />';
  
  // removes the FIRST "|"
  while(substr($newDataString,0,1) == '|') {
    $newDataString = substr($newDataString, 1);
  }
  // removes the LAST "|"
  while(substr($newDataString,-1) == '|') {
    $newDataString = substr($newDataString, 0, -1);
  }
  
  // -> SORTS the NEW SEARCHWORD STRING with THE SEARCHWORD with MOST COUNT at the BEGINNING
  if($dataArray = explode('|',$newDataString)) {
  
    // sortiert den array, mithilfe der funktion sortArray
    natsort($dataArray);
    usort($dataArray, "sortSearchwordString");          

    // f�gt den neugeordneten Suchworte String wieder zu einem Array zusammen
    $newDataString = implode('|',$dataArray);
  }
  
  // RETURNs the new data String
  return $newDataString;
}

// ** -- saveLog --------------------------------------------------------------------------------
// saves the the website statistic
// - count user visits
// - count bot visits 
// - register user browser
// - logs the last referers
// -----------------------------------------------------------------------------------------------------
function saveWebsiteStats() {  
  global $phpTags;
  global $adminConfig;
  global $websiteStatistic;
  global $_SESSION; // needed for check if the user has already visited the page AND reduce memory, because only run once the isSpider() function
  global $HTTP_SESSION_VARS;
  
    //unset($_SESSION);
    
    // if its an older php version, set the session var
    if(phpversion() <= '4.1.0')
      $_SESSION = $HTTP_SESSION_VARS;
    
    // COUNT if the user/spider isn't already counted
    if(!isset($_SESSION['log_agentVisited']) || $_SESSION['log_agentVisited'] === false) {
      
      // -> CHECKS if the user is NOT a BOT/SPIDER
      if ((isset($_SESSION['log_userIsSpider']) && $_SESSION['log_userIsSpider'] === false) ||
          ($_SESSION['log_userIsSpider'] = isSpider()) === false) {
        
        // -> COUNT the userVisitCount UP
        if($websiteStatistic['userVisitCount'] == '')
          $websiteStatistic['userVisitCount'] = '0';
        else
          $websiteStatistic['userVisitCount']++;
        
        // -> adds the user BROWSER
        $userBrowser = getBrowser($_SERVER['HTTP_USER_AGENT']);
        $websiteStatistic["userBrowsers"] = addToDataString(array($userBrowser),$websiteStatistic["userBrowsers"]);
        
      // -> COUNT the spiderVisitCount UP
      } elseif($websiteStatistic['spiderVisitCount'] == '')
        $websiteStatistic['spiderVisitCount'] = '0';
      else
        $websiteStatistic['spiderVisitCount']++;
      
      // ->> OPEN websiteStatistic.php for writing
      if($statisticFile = @fopen(dirname(__FILE__)."/../../statistic/websiteStatistic.php","w")) {

        
        flock($statisticFile,2);        
        fwrite($statisticFile,$phpTags[0]);  
              
        fwrite($statisticFile,"\$websiteStatistic['userVisitCount'] =    '".$websiteStatistic["userVisitCount"]."';\n");
        fwrite($statisticFile,"\$websiteStatistic['spiderVisitCount'] =  '".$websiteStatistic["spiderVisitCount"]."';\n");
        fwrite($statisticFile,"\$websiteStatistic['userBrowsers'] =      '".$websiteStatistic["userBrowsers"]."';\n\n");
        
        fwrite($statisticFile,"return \$websiteStatistic;");
              
        fwrite($statisticFile,$phpTags[1]);        
        flock($statisticFile,3);
        fclose($statisticFile);
        
        // saves the user as visited
        //$_SESSION['log_agentVisited'] = true;
      }
    }  
}

// ** -- savePageStats ----------------------------------------------------------------------------------
// saves the statistics of a page
// needs to have a session startet with: session_start(); in the header of the HTML Page, to prevent multiple count of page visits
// -----------------------------------------------------------------------------------------------------
// $pageContent      [the array, given by the readPage($page,$category) function (Array)]
function savePageStats($pageContent) {
    global $adminConfig;
    global $_SESSION; // needed for check if the user has already visited the page AND reduce memory, because only run once the isSpider() function
    global $HTTP_SESSION_VARS;
    
    //unset($_SESSION);
    
    // if its an older php version, set the session var
    if(phpversion() <= '4.1.0')
      $_SESSION = $HTTP_SESSION_VARS;

    // --------------------------------------------------------------------------------
    // CHECKS if the user is NOT a BOT/SPIDER
    if ((isset($_SESSION['log_userIsSpider']) && $_SESSION['log_userIsSpider'] === false) ||
        ($_SESSION['log_userIsSpider'] = isSpider()) === false) {
      
      // -> saves the FIRST PAGE VISIT
      // -----------------------------
      if(empty($pageContent['log_firstVisit'])) {
        $pageContent['log_firstVisit'] = date('Y')."-".date('m')."-".date('d').' '.date("H:i:s",time());
        $pageContent['log_visitCount'] = 1;
      }
      
      // -> saves the LAST PAGE VISIT
      // ----------------------------
      $pageContent['log_lastVisit'] = date('Y')."-".date('m')."-".date('d').' '.date("H:i:s",time());
      
      // -> COUNT UP, if the user haven't already visited this page in this session
      // --------------------------------------------------------------------------
      if(!isset($_SESSION['log_visitedPages']))
        $_SESSION['log_visitedPages'] = array();
        
      if(in_array($pageContent['id'],$_SESSION['log_visitedPages']) === false) {
        //echo $pageContent['id'].' -> '.$pageContent['log_visitCount'];
        $pageContent['log_visitCount']++;
        // add to the array of already visited pages
        array_push($_SESSION['log_visitedPages'],$pageContent['id']);
      }
      
      // ->> SAVE THE SEARCHWORDs from GOOGLE, YAHOO, MSN (Bing)
      // -------------------------------------------------------
      if(!empty($_SERVER['HTTP_REFERER'])) {
        $searchWords = parse_url($_SERVER['HTTP_REFERER']);
        // test search url strings:
        //$searchWords = parse_url('http://www.google.de/search?q=mair%E4nd+%26+geld+syteme%3F&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:de:official&client=firefox-a');
        //$searchWords = parse_url('http://www.google.de/search?hl=de&safe=off&client=firefox-a&rls=org.mozilla%3Ade%3Aofficial&hs=pLl&q=umlaute+aus+url+umwandeln&btnG=Suche&meta=');
        //$searchWords = parse_url('http://www.bing.com/search?q=hll%C3%B6le+ich+such+ein+wort+f%C3%BCr+mich&go=&form=QBRE&filt=all');
        //$searchWords = parse_url('http://de.search.yahoo.com/search;_ylt=A03uv8f1RWxKvX8BGYMzCQx.?p=wurmi&y=Suche&fr=yfp-t-501&fr2=sb-top&rd=r1&sao=1');
        //$searchWords = parse_url('http://de.search.yahoo.com/search;_ylt=A03uv8f1RWxKvX8BGYMzCQx.?p=umlaute&y=Suche&fr=yfp-t-501&fr2=sb-top&rd=r1&sao=1');
        if(strstr($searchWords['host'],'google') || strstr($searchWords['host'],'bing') || strstr($searchWords['host'],'yahoo')) {
  
          //sucht das suchwort beginn aus dem url-query string heraus
          if(strstr($searchWords['host'],'yahoo'))
            $searchWords = strstr($searchWords['query'],'p=');
          else
            $searchWords = strstr($searchWords['query'],'q=');
          $searchWords = substr($searchWords,2,strpos($searchWords,'&')-2);
  
          $searchWords = rawurldecode($searchWords);
          //$searchWords = urldecode($searchWords);
          $searchWords = explode('+',$searchWords);    
          
          // adds the searchwords to the searchword data string
          $pageContent['log_searchwords'] = addToDataString($searchWords,$pageContent['log_searchwords']);
          
          /*
          $exisitingSearchWords = explode('|',$pageContent['log_searchwords']);
          
          // -> COUNTS THE EXISTING SEARCHWORDS
          $countExistingSw = 0;
          $newSearchString = '';
          foreach($exisitingSearchWords as $exisitingSearchWord) {          
            $exisitingSearchWord = explode(',',$exisitingSearchWord);
            $countExistingSw++; 
            
            $countNewSw = -1;
            foreach($searchWords as $searchWord) {            
              $searchWord = cleanSpecialChars($searchWord,''); // entfernt Sonderzeichen
              $searchWord = htmlentities($searchWord,ENT_QUOTES, 'UTF-8');
              $searchWord = strtolower($searchWord);
              $countNewSw++;
              
              // wenn es das Stichwort schon gibt
              if($exisitingSearchWord[0] == $searchWord) {
                // z�hlt ein die Anzahl des Stichworts h�her
                $exisitingSearchWord[1]++;
                $foundSw[] = $searchWord;
              }
            }
            
            // adds the old Searchwords (maybe counted up) to the String with the new ones            
            if(!empty($exisitingSearchWord[0])) {
              $newSearchString .= $exisitingSearchWord[0].','.$exisitingSearchWord[1];
              if($countExistingSw < count($exisitingSearchWords))
                $newSearchString .= '|';
            }
          }
          
          // -> ADDS NEW SEARCHWORDS
          $countNewSw = 0;
          foreach($searchWords as $searchWord) {
          
            $searchWord = cleanSpecialChars($searchWord,''); // entfernt Sonderzeichen
            $searchWord = htmlentities($searchWord,ENT_QUOTES, 'UTF-8');
            $searchWord = strtolower($searchWord);
            $countNewSw++;
            
            if(isset($foundSw) && is_array($foundSw))
              $foundSwStr = implode('|',$foundSw);
         
            if(!isset($foundSw) || (!empty($searchWord) && strstr($foundSwStr,$searchWord) == false)) {
              if(!empty($searchWord)) {// verhindert das leere Suchwort strings gespeichert werden
                if(substr($newSearchString,-1) != '|')
                  $newSearchString .= '|';
                // f�gt ein neues Suchwort in den String mit den Suchw�rtern ein                
                $newSearchString .= $searchWord.',1';
                
                if($countNewSw < count($searchWords))
                  $newSearchString .= '|';
              }
            }
          }          
          //echo $newSearchString.'<br />';
          
          // removes the FIRST "|"
          while(substr($newSearchString,0,1) == '|') {
            $newSearchString = substr($newSearchString, 1);
          }
          // removes the LAST "|"
          while(substr($newSearchString,-1) == '|') {
            $newSearchString = substr($newSearchString, 0, -1);
          }
          
          // -> SORTS the NEW SEARCHWORD STRING with THE SEARCHWORD with MOST COUNT at the BEGINNING
          if($searchWords = explode('|',$newSearchString)) {
          
            // sortiert den array, mithilfe der funktion sortArray
            natsort($searchWords);
            usort($searchWords, "sortSearchwordString");          
      
            // f�gt den neugeordneten Suchworte String wieder zu einem Array zusammen
            $newSearchString = implode('|',$searchWords);
          }          
          
          // replace the SEARCHWORDS var
          $pageContent['log_searchwords'] = $newSearchString;
          */       
        }
      }      
      
      // ->> VISIT TIME
      // --------------
      $newMinVisitTimes = '';
      $newMaxVisitTimes = '';
      $maxCount = 5;
      
      // -> count the time difference, between the last page and the current
      if(isset($_SESSION['log_lastPage'])) {
        $orgVisitTime = getMicroTime() - $_SESSION['log_lastPage_timestamp'];
        
        // makes a time out of seconds
        $orgVisitTime = secToTime($orgVisitTime);
        $visitTime = $orgVisitTime;
        
        // -> saves the MAX visitTime
        // ****
        if(!empty($_SESSION['log_lastPage']['log_visitTime_max']) && $visitTime !== false) {
        
          $maxVisitTimes = explode('|',$_SESSION['log_lastPage']['log_visitTime_max']);
          
          // adds the new time if it is bigger than the highest min time
          if($visitTime > $maxVisitTimes[count($maxVisitTimes) - 1]) {
            array_unshift($maxVisitTimes,$visitTime);
            $visitTime = false;
          }
          // adds the new time on the beginnig of the array          
          $newMaxVisitTimes = array_slice($maxVisitTimes,0,$maxCount);
          
          // sort array
          natsort($newMaxVisitTimes);
          $newMaxVisitTimes = array_reverse($newMaxVisitTimes);
          // make array to string
          $newMaxVisitTimes = implode('|',$newMaxVisitTimes);
          
        } elseif(!empty($_SESSION['log_lastPage']['log_visitTime_max']))
          $newMaxVisitTimes = $_SESSION['log_lastPage']['log_visitTime_max'];
        else
          $newMaxVisitTimes = $orgVisitTime;
        
        // -> saves the MIN visitTime
        // ****
        if(!empty($_SESSION['log_lastPage']['log_visitTime_min']) && $visitTime !== false) {
        
          $minVisitTimes = explode('|',$_SESSION['log_lastPage']['log_visitTime_min']);
          
          // adds the new time if it is bigger than the highest min time
          if($visitTime > $minVisitTimes[0]) {
            array_unshift($minVisitTimes,$visitTime);
          }
          // adds the new time on the beginnig of the array  
          $newMinVisitTimes = array_slice($minVisitTimes,0,$maxCount);

          // sort array
          natsort($newMinVisitTimes);
          $newMinVisitTimes = array_reverse($newMinVisitTimes);
          // make array to string
          $newMinVisitTimes = implode('|',$newMinVisitTimes);
          
        } elseif(!empty($_SESSION['log_lastPage']['log_visitTime_min']))
          $newMinVisitTimes = $_SESSION['log_lastPage']['log_visitTime_min'];
        else
          $newMinVisitTimes = '00:00:00';          
        
        // -> adds the new max times to the pageContent Array
        $_SESSION['log_lastPage']['log_visitTime_min'] = $newMinVisitTimes;
        $_SESSION['log_lastPage']['log_visitTime_max'] = $newMaxVisitTimes;
        
        // -> SAVE the LAST PAGE
        savePage($_SESSION['log_lastPage']['category'],$_SESSION['log_lastPage']['id'],$_SESSION['log_lastPage']);
      }
      // stores the time of the LAST PAGE in the session
      $_SESSION['log_lastPage'] = $pageContent;
      $_SESSION['log_lastPage_timestamp'] = getMicroTime();
      

      // -> SAVE the PAGE STATISTICS
      return savePage($pageContent['category'],$pageContent['id'],$pageContent);
    }
}

?>