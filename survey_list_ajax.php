<?php

# '../' works for a sub-folder.  use './' for the root  
require '../inc_0700/config_inc.php'; #provides configuration, pathing, error handling, db credentials 
 
$sql = 
"
select CONCAT(a.FirstName, ' ', a.LastName) AdminName, s.SurveyID, s.Title, s.Description, 
date_format(s.DateAdded, '%W %D %M %Y %H:%i') 'DateAdded' from "
. PREFIX . "surveys s, " . PREFIX . "Admin a where s.AdminID=a.AdminID order by s.DateAdded desc
";

#Fills <title> tag. If left empty will default to $PageTitle in config_inc.php  
$config->titleTag = 'Survey List';

#Fills <meta> tags.  Currently we're adding to the existing meta tags in config_inc.php
$config->metaDescription = 'Seattle Central\'s ITC280 Class Muffins are made with pure PHP! ' . $config->metaDescription;
$config->metaKeywords = 'Muffins,PHP,Fun,Bran,Regular,Regular Expressions,'. $config->metaKeywords;
$config->loadhead .= 
'
<script src="http://code.jquery.com/jquery-latest.js" type="text/javascript"></script>
';

# END CONFIG AREA ---------------------------------------------------------- 

get_header(); #defaults to theme header or header_inc.php

?>
<script type="text/javascript">
$("document").ready(function(){
	$('.survey').hide();//hide all survey class divs
    $('.ajax').click(function(e){
    e.preventDefault(); //stops link from submitting
    var this_survey = $(this).attr("href");//get current survey #
    
    var this_div = "#d" + this_survey;
        
    //alert(this_div);

    $.get(
    "survey_view_ajax.php",
    {id:this_survey},
    function(data){
        
        //if survey is currently visible, slideUP
        if($(".survey").is(":visible"))
        {
        $(".survey").slideUp();
        
        }
        
        
        
        
        $(this_div).html(data).slideDown();
        
        //alert(data);
    },
    "html");
        
        
        
    });

});//end of .ajax click function

    //alert("jQuery is working!");

</script>
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
    echo '<div align="center">We have ' . $myPager->showTotal() . ' ' . $itemz . '!</div>';
	while($row = mysqli_fetch_assoc($result))
	{# process each row
         echo '<div align="center"><a href="'  . (int)$row['SurveyID'] . '" class="ajax">' . dbOut($row['Title']) . '</a>';
         echo '</div>';
        
        
        echo '<div class="survey" align="center" id="d'  . (int)$row['SurveyID'] . '">&nbsp</div>';
	
    
    }
	echo $myPager->showNAV(); # show paging nav, only if enough records	 
}else{#no records
    echo "<div align=center>What! No surveys?  There must be a mistake!!</div>";	
}
@mysqli_free_result($result);
get_footer(); #defaults to theme footer or footer_inc.php
?>
