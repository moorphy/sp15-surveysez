<?php
/**
 * demo_list_pager.php along with demo_view_pager.php provides a sample web application
 *
 * The difference between demo_list.php and demo_list_pager.php is the reference to the 
 * Pager class which processes a mysqli SQL statement and spans records across multiple  
 * pages. 
 *
 * The associated view page, demo_view_pager.php is virtually identical to demo_view.php. 
 * The only difference is the pager version links to the list pager version to create a 
 * separate application from the original list/view. 
 * 
 * @package nmPager
 * @author Bill Newman <williamnewman@gmail.com>
 * @version 3.02 2011/05/18
 * @link http://www.newmanix.com/
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License ("OSL") v. 3.0
 * @see demo_view_pager.php
 * @see Pager.php 
 * @todo none
 */

# '../' works for a sub-folder.  use './' for the root  
require '../inc_0700/config_inc.php'; #provides configuration, pathing, error handling, db credentials 
 
# SQL statement
$sql = 
"
select CONCAT(a.FirstName, ' ', a.LastName) AdminName, s.SurveyID, s.Title, s.Description, 
date_format(s.DateAdded, '%W %D %M %Y %H:%i') 'DateAdded' from "
. PREFIX . "surveys s, " . PREFIX . "Admin a where s.AdminID=a.AdminID order by s.DateAdded desc
";


#Fills <title> tag. If left empty will default to $PageTitle in config_inc.php  
$config->titleTag = 'A6: Survey List/View | Mike Murphy';

#Fills <meta> tags.  Currently we're adding to the existing meta tags in config_inc.php
$config->metaDescription = 'Seattle Central\'s ITC280 Class Surveys are made with pure PHP! ' . $config->metaDescription;
$config->metaKeywords = 'Surveys,PHP,Fun,Questions,Regular,Regular Expressions,'. $config->metaKeywords;

/*
$config->loadhead = ''; #load page specific JS
$config->banner = ''; #goes inside header
$config->copyright = ''; #goes inside footer
$config->sidebar1 = ''; #goes inside left side of page
$config->sidebar2 = ''; #goes inside right side of page
$config->nav1["page.php"] = "New Page!"; #add a new page to end of nav1 (viewable this page only)!!
$config->nav1 = array("page.php"=>"New Page!") + $config->nav1; #add a new page to beginning of nav1 (viewable this page only)!!
*/

# END CONFIG AREA ---------------------------------------------------------- 

get_header(); #defaults to theme header or header_inc.php
?>
<h3 align="center"><?=smartTitle();?></h3>
 
<?php
#reference images for pager
$prev = '<img src="' . VIRTUAL_PATH . 'images/arrow_prev.gif" border="0" />';
$next = '<img src="' . VIRTUAL_PATH . 'images/arrow_next.gif" border="0" />';

# Create instance of new 'pager' class
$myPager = new Pager(10,'',$prev,$next,'');
$sql = $myPager->loadSQL($sql);  #load SQL, add offset

# connection comes first in mysqli (improved) function
$result = mysqli_query(IDB::conn(),$sql) or die(trigger_error(mysqli_error(IDB::conn()), E_USER_ERROR));

if(mysqli_num_rows($result) > 0)
{#records exist - process
	if($myPager->showTotal()==1){$itemz = "survey";}else{$itemz = "surveys";}  //deal with plural
    echo '<div align="center"><em>We have ' . $myPager->showTotal() . ' ' . $itemz . '! Click on the Survey ID</em></div>';
            echo '<table align="center" border="1" style="border-collapse:collapse" cellpadding="3" cellspacing="3">';
		echo '<tr>
				<th>Survey ID</th>
				<th>Title</th>
				<th>Description</th>
				<th>Admin Name</th>
                <th>Date Added</th>
			</tr>
			';
	while($row = mysqli_fetch_assoc($result))
	{# process each row
echo '<tr>' .
'<td><a href="' . VIRTUAL_PATH . 'surveys/survey_view.php?id=' . (int)$row . '">'. (int)$row['SurveyID'] . '</td>
				    <td><a href="' . VIRTUAL_PATH . 'surveys/survey_view.php?id=' . (int)$row . '">' . dbOut($row['Title']) . '</a></td>
                    <td><a href="' . VIRTUAL_PATH . 'surveys/survey_view.php?id=' . (int)$row . '">' .  dbOut($row['Description']) . '</a></td>
                    <td><a href="' . VIRTUAL_PATH . 'surveys/survey_view.php?id=' . (int)$row . '">' . dbOut($row['AdminName']) . '</a></td>
                    <td><a href="' . VIRTUAL_PATH . 'surveys/survey_view.php?id=' . (int)$row . '">' . dbOut($row['DateAdded']) . '</a></td>'
				.'</tr>';
		}
		echo '</table>';
    
	echo $myPager->showNAV(); # show paging nav, only if enough records	 
}else{#no records
    echo "<div align=center>There are currently no surveys</div>";	
}
@mysqli_free_result($result);

get_footer(); #defaults to theme footer or footer_inc.php
?>
