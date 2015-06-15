<?php
/**
 * survey_view.php is a page to demonstrate the proof of concept of the 
 * initial SurveySez objects.
 *
 * Objects in this version are the Survey, Question & Answer objects
 * 
 * @package SurveySez
 * @author Bill Newman/ Mike Murphy <williamnewman@gmail.com><murph517@gmail.com>
 * @version 2.1 2011/10/25
 * @link http://www.billnsara.com/advdb/  
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License ("OSL") v. 3.0
 * @see config_inc.php  
 * @todo none
 */
 
require '../inc_0700/config_inc.php'; #provides configuration, pathing, error handling, db credentials
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

$mySurvey = new Survey($myID);
if($mySurvey->isValid)
{
	$config->titleTag = "'" . $mySurvey->Title . "' Survey!";
}else{
	$config->titleTag = smartTitle(); //use constant 
}
#END CONFIG AREA ---------------------------------------------------------- 

//get_header(); #defaults to theme header or header_inc.php
?>

<h3><?=THIS_PAGE;?></h3>
<?php
if($mySurvey->isValid)
{ #check to see if we have a valid SurveyID
	echo $mySurvey->SurveyID . "<br />";
	echo $mySurvey->Title . "<br />";
	echo $mySurvey->Description . "<br />";
	echo $mySurvey->showQuestions();
}else{
	echo "Sorry, no such survey!";	
}
//get_footer(); #defaults to theme footer or footer_inc.php
?>


<?php

/*
 * Survey Class retrieves data info for an individual Survey
 * 
 * The constructor an instance of the Survey class creates multiple instances of the 
 * Question class and the Answer class to store questions and answers data from the DB.
 *
 * Properties of the Survey class like Title, Description and TotalQuestions provide 
 * summary information upon demand.
 * 
 * A survey object (an instance of the Survey class) can be created in this manner:
 *
 *<code>
 *$mySurvey = new Survey($myID);
 *</code>
 *
 * In which one is the number of a valid Survey in the database. 
 *
 * The showQuestions() method of the Survey object created will access an array of question 
 * objects and internally access a method of the Question class named showAnswers() which will 
 * access an array of Answer objects to produce the visible data.
 *
 * @see Question
 * @see Answer 
 * @todo none
 */

class Survey
{
	 public $SurveyID = 0;
	 public $Title = "";
	 public $Description = "";
	 public $isValid = FALSE;
	 public $TotalQuestions = 0; #stores number of questions
	 protected $aQuestion = Array();#stores an array of question objects
	
	/**
	 * Constructor for Survey class. 
	 *
	 * @param integer $id The unique ID number of the Survey
	 * @return void 
	 * @todo none
	 */ 
    function __construct($id)
	{#constructor sets stage by adding data to an instance of the object
		$this->SurveyID = (int)$id;
		if($this->SurveyID == 0){return FALSE;}
		
		#get Survey data from DB
		$sql = 
"
select CONCAT(a.FirstName, ' ', a.LastName) AdminName, s.SurveyID, s.Title, s.Description, 
date_format(s.DateAdded, '%W %D %M %Y %H:%i') 'DateAdded' from "
. PREFIX . "surveys s, " . PREFIX . "Admin a where s.AdminID=a.AdminID order by s.DateAdded desc
";
		
		#in mysqli, connection and query are reversed!  connection comes first
		$result = mysqli_query(IDB::conn(),$sql) or die(trigger_error(mysqli_error(IDB::conn()), E_USER_ERROR));
		if (mysqli_num_rows($result) > 0)
		{#Must be a valid survey!
			$this->isValid = TRUE;
			while ($row = mysqli_fetch_assoc($result))
			{#dbOut() function is a 'wrapper' designed to strip slashes, etc. of data leaving db
			     $this->Title = dbOut($row['Title']);
			     $this->Description = dbOut($row['Description']);
			}
		}
		@mysqli_free_result($result); #free resources
		
		if(!$this->isValid){return;}  #exit, as Survey is not valid
		
		#attempt to create question objects
		$sql = sprintf("select QuestionID, Question, Description from " . PREFIX . "questions where SurveyID =%d",$this->SurveyID);
		$result = mysqli_query(IDB::conn(),$sql) or die(trigger_error(mysqli_error(IDB::conn()), E_USER_ERROR));
		if (mysqli_num_rows($result) > 0)
		{#show results
		   while ($row = mysqli_fetch_assoc($result))
		   {
				#create question, and push onto stack!
				$this->aQuestion[] = new Question(dbOut($row['QuestionID']),dbOut($row['Question']),dbOut($row['Description'])); 
		   }
		}
		$this->TotalQuestions = count($this->aQuestion); //the count of the aQuestion array is the total number of questions
		@mysqli_free_result($result); #free resources
		
		#attempt to load all Answer objects into cooresponding Question objects 
	    $sql = "select a.AnswerID, a.Answer, a.Description, a.QuestionID from  
		" . PREFIX . "surveys s inner join " . PREFIX . "questions q on q.SurveyID=s.SurveyID 
		inner join " . PREFIX . "answers a on a.QuestionID=q.QuestionID   
		where s.SurveyID = %d   
		order by a.AnswerID asc";
		$sql = sprintf($sql,$this->SurveyID); #process SQL
		$result = mysqli_query(IDB::conn(),$sql) or die(trigger_error(mysqli_error(IDB::conn()), E_USER_ERROR));
		if (mysqli_num_rows($result) > 0)
		{#at least one answer!
		   while ($row = mysqli_fetch_assoc($result))
		   {#match answers to questions
			    $QuestionID = (int)$row['QuestionID']; #process db var
				foreach($this->aQuestion as $question)
				{#Check db questionID against Question Object ID
					if($question->QuestionID == $QuestionID)
					{
						$question->TotalAnswers += 1;  #increment total number of answers
						#create answer, and push onto stack!
						$question->aAnswer[] = new Answer((int)$row['AnswerID'],dbOut($row['Answer']),dbOut($row['Description']));
						break; 
					}
				}	
		   }
		}
	}# end Survey() constructor
	
	/**
	 * Reveals questions in internal Array of Question Objects 
	 *
	 * @param none
	 * @return string prints data from Question Array 
	 * @todo none
	 */ 
	function showQuestions()
	{
		if($this->TotalQuestions > 0)
		{#be certain there are questions
			foreach($this->aQuestion as $question)
			{#print data for each 
				echo $question->QuestionID . " ";
				echo $question->Text . " ";
				echo $question->Description . "<br />";
				#call showAnswers() method to display array of Answer objects
				$question->showAnswers() . "<br />";
			}
		}else{
			echo "There are currently no questions for this survey.";	
		}
	}# end showQuestions() method
}# end Survey class

class Question
{
	 public $QuestionID = 0;
	 public $Text = "";
	 public $Description = "";
	 public $aAnswer = Array();#stores an array of answer objects
	 public $TotalAnswers = 0;
	/**
	 * Constructor for Question class. 
	 *
	 * @param integer $id ID number of question 
	 * @param string $question The text of the question
	 * @param string $description Additional description info
	 * @return void 
     * @todo none
	 */ 
    function __construct($id,$question,$description)
	{#constructor sets stage by adding data to an instance of the object
		$this->QuestionID = (int)$id;
		$this->Text = $question;
		$this->Description = $description;
	}# end Question() constructor
	
	/**
	 * Reveals answers in internal Array of Answer Objects 
	 * for each question 
	 *
	 * @param none
	 * @return string prints data from Answer Array 
	 * @todo none
	 */ 
	function showAnswers()
	{
		if($this->TotalAnswers != 1){$s = 's';}else{$s = '';} #add 's' only if NOT one!!
		echo "<em>[" . $this->TotalAnswers . " answer" . $s . "]</em> "; 
		foreach($this->aAnswer as $answer)
		{#print data for each
			echo "<em>(" . $answer->AnswerID . ")</em> ";
			echo $answer->Text . " ";
			if($answer->Description != "")
			{#only print description if not empty
				echo "<em>(" . $answer->Description . ")</em>";
			}
		}
		print "<br />";
	}#end showAnswers() method
}# end Question class

class Answer
{
	 public $AnswerID = 0;
	 public $Text = "";
	 public $Description = "";
	/**
	 * Constructor for Answer class. 
	 *
	 * @param integer $AnswerID ID number of answer 
	 * @param string $Text The text of the answer
	 * @param string $Description Additional description info
	 * @return void 
	 * @todo none
	 */ 
    function __construct($AnswerID,$answer,$description)
	{#constructor sets stage by adding data to an instance of the object
		$this->AnswerID = (int)$AnswerID;
		$this->Text = $answer;
		$this->Description = $description;
	}#end Answer() constructor
}#end Answer class
