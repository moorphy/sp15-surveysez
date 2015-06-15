<?php //view is the results page
/**
 * survey_view.php is a page to demonstrate the proof of concept of the
 * initial SurveySez objects.
 *
 * Objects in this version are the Survey, Question & Answer objects
 *
 * @package SurveySez
 * @author Bill Newman <williamnewman@gmail.com>
 * @version 2.1 2011/10/25
 * @link http://www.billnsara.com/advdb/
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License ("OSL") v. 3.0
 * @see config_inc.php
 * @todo none
 */

require '../inc_0700/config_inc.php'; #provides configuration, pathing, error handling, db credentials
spl_autoload_register('MyAutoLoader::NamespaceLoader');//required to load SuveryZSez namespace object

$config->metaRobots = 'no index, no follow';#never index survey pages
/*
$config->metaDescription = ''; #Fills <meta> tags.
$config->metaKeywords = '';
$config->metaRobots = '';
$config->loadhead = ''; #load page specific JS
$config->banner = ''; #goes inside header
$config->copyright = ''; #goes inside footer
$config->sidebar1 = ''; #goes inside left side of page
$config->sidebar2 = ''; #goes inside right side of page
$config->nav1["page.php"] = "New Page!"; #add a new page to end of nav1 (viewable this page only)!!
$config->nav1 = array("page.php"=>"New Page!") + $config->nav1; #add a new page to beginning of nav1 (viewable this page only)!!
*/

# check variable of item passed in - if invalid data, forcibly redirect back to demo_list.php page
if(isset($_GET['id']) && (int)$_GET['id'] > 0){#proper data must be on querystring
     $myID = (int)$_GET['id']; #Convert to integer, will equate to zero if fails
}else{
    myRedirect(VIRTUAL_PATH . "surveys/index.php");
}

$mySurvey = new SurveySez\MY_Survey($myID); //backslash '\'means we need autoloader
if($mySurvey->isValid)
{
    $config->titleTag = "'" . $mySurvey->Title . "' Survey!";
}else{
    $config->titleTag = smartTitle(); //use constant
}
#END CONFIG AREA ----------------------------------------------------------

get_header(); #defaults to theme header or header_inc.php
?>
<h3><?=THIS_PAGE;?></h3>

<p>First iteration of SurveySez objects/application.</p>

<?php

if($mySurvey->isValid)
{ #check to see if we have a valid SurveyID
    echo $mySurvey->SurveyID . "<br />";
    echo $mySurvey->Title . "<br />";
    echo $mySurvey->Description . "<br />";
    echo $mySurvey->showQuestions($myID);
    
    echo SurveySez\MY_Survey::responseList($myID);
    
    
}else{
    echo "Sorry, no such survey!";
}

get_footer(); #defaults to theme footer or footer_inc.php
