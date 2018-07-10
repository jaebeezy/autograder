<?php


// autograder.php 
// by jae young choi
// created on march 26th 2018 
// cs 490 


// array to curl to getAllClasses
$arrAllClasses = ["queryType" => "getClasses", "username" => "teacher"];
$arrAllClasses = json_encode($arrAllClasses);

// initiate the curl 
$curlAllClasses = curl_init();
// curl credentials
curl_setopt($curlAllClasses, CURLOPT_URL, 'https://web.njit.edu/~tc95/CS490/beta/model.php');
curl_setopt($curlAllClasses, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlAllClasses, CURLOPT_POST, 1);
curl_setopt($curlAllClasses, CURLOPT_POSTFIELDS, $arrAllClasses);
// executing the curl 
$testAllClasses = curl_exec($curlAllClasses);
$respAllClasses = json_decode($testAllClasses, true);

// echo the results
echo "This is the getClasses query output: <br>";
echo $testAllClasses . "<br><br>";


// all the classes keys
$allClasses = $respAllClasses['classesKeys'];

// echo and all the classes within the system
echo "These are just the classesKeys:<br>";
$allClasses1 = json_encode($allClasses);
$allClasses1Dec = json_decode($allClasses1);
print_r($allClasses1Dec);
echo "<br><br>";

curl_close($curlAllClasses);

$questionIDArray = [];
$feedbackArray = [];	
$pointsArray = [];


// for each classes inside all the classes... iterating through all classes
foreach(array_values($allClasses1Dec) as $classID){

	// current class
	echo "This is the current class we are working with: <br>";
	echo $classID . "<br><br>";

	// query for getExam
	// I need getExam to extract the gradeKey in order for the correct grade scaling
	$arrGetExam = [
		"queryType"		=> 	"getExam",
		"classIDKey"	=>	$classID
	];
	$arrGetExam = json_encode($arrGetExam);

	// initiate the curl 
	$curlGetExam = curl_init();

	curl_setopt($curlGetExam, CURLOPT_URL, 'https://web.njit.edu/~tc95/CS490/beta/model.php');
	curl_setopt($curlGetExam, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curlGetExam, CURLOPT_POST, 1);
	curl_setopt($curlGetExam, CURLOPT_POSTFIELDS, $arrGetExam);

	$testExam = curl_exec($curlGetExam);
	$respExam = json_decode($testExam, true);

	echo "This is the getExam query: <br>";
	echo $testExam . "<br><br>";

	// just getting the gradeKey which tells us how much the exam is out of 
	$gradeScale = $respExam['gradeKey'];


	// for each student inside the class... iterating through all studnets within class
	foreach(array_values($respAllClasses[$classID]) as $students){
		// current student
		echo "This is the current student we are working with: <br>";
		echo $students . "<br><br>";

		// getAnswers array to curl 
		$arrGetAnswers = ["queryType" => "getAnswers", "classIDKey" => $classID, "studentIDKey" => $students];
		$arrGetAnswers = json_encode($arrGetAnswers);
		
		// curl initialization
		$curlAnswers = curl_init();
		// info needed to completel curl
		curl_setopt($curlAnswers, CURLOPT_URL, 'https://web.njit.edu/~tc95/CS490/beta/model.php');
		curl_setopt($curlAnswers, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlAnswers, CURLOPT_POST, 1);
		curl_setopt($curlAnswers, CURLOPT_POSTFIELDS, $arrGetAnswers);
		// executing the curl
		$testAnswer = curl_exec($curlAnswers);
		$respAnswer = json_decode($testAnswer, true);
		// echoing the curl request
		echo "This is the getAnswer query: <br>";
		echo $testAnswer . "<br><br>";
		// closing the curl
		curl_close($curlAnswers);

		// just to keep track of the iterations
		// i echoed out some arrays 
		$answerKeyQuestions = $respAnswer['answerKeys'];
		echo "This is the answerKeys array: <br>"; 
		print_r($answerKeyQuestions);
		echo "<br><br>";
		echo "This is just the question IDs: <br>";
		$justQuestions = array_keys($answerKeyQuestions);
		print_r($justQuestions);
		echo "<br><br>";
		$justAnswers = array_values($answerKeyQuestions);
		echo "This is just the answers: <br>";
		print_r($justAnswers);
		echo "<br><br>";

		// pointsKeys array
		$answerPointKey = $respAnswer['pointsKeys'];
		echo "This is the pointKeys array: <br>";
		print_r($answerPointKey);
		echo "<br><br>";

		// for each question id 
		foreach($justQuestions as $question){
			// current questionIDkey
			echo "This is the question ID I am working with now: <br>";
			echo $question . "<br><br>";

			// current answer 
			echo "This is the answer I am working with now: <br>";
			echo $answerKeyQuestions[$question] . "<br><br>"; 

			// getQuestion query 
			$arrGetQues = ["queryType" => "getQuestion", "questionIDKey" => $question];
			$arrGetQues = json_encode($arrGetQues);

			$curlQuestions = curl_init();

			curl_setopt($curlQuestions, CURLOPT_URL, 'https://web.njit.edu/~tc95/CS490/beta/model.php');
			curl_setopt($curlQuestions, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlQuestions, CURLOPT_POST, 1);
			curl_setopt($curlQuestions, CURLOPT_POSTFIELDS, $arrGetQues);

			$testQuestion = curl_exec($curlQuestions);
			$respQuestion = json_decode($testQuestion, true);

			echo "This is the getQuestion query: <br>";
			echo $testQuestion . "<br><br>";


			// function name 
			// i need this to compare if one of the use cases of the
			// function being properly named
			// if it does not match within the answer
			// i have to take points off and replace the answer
			// with the correct one to still see if the testcases still 
			// work or not 
			$funcName = $respQuestion['funcNameKey'];

			// getting the testcases 
			// obviously i need these to check that the code is properly
			// working and answers the question
			$testcase1 = $respQuestion['testcase1Key'];

			// getting all of the individual testcases input and output 
			$testcase1Arr = explode(",", $testcase1);
			$testcase1Arr = explode(",", $testcase1);
			$testcase1ArrEncoded = json_encode($testcase1Arr);
			$testcase1ArrDecoded = json_decode($testcase1ArrEncoded, true);
			$testcase1VarTrim = explode("=", $testcase1ArrDecoded[0]);
			$testcase1OutTrim = explode("=", $testcase1ArrDecoded[1]);
			$testcaseVariable1 = $testcase1VarTrim[1];
			$testcaseOutput1 = $testcase1OutTrim[1];

			$testcase2 = $respQuestion['testcase2Key'];

			$testcase2Arr = explode(",", $testcase2);
			$testcase2Arr = explode(",", $testcase2);
			$testcase2ArrEncoded = json_encode($testcase2Arr);
			$testcase2ArrDecoded = json_decode($testcase2ArrEncoded, true);
			$testcase2VarTrim = explode("=", $testcase2ArrDecoded[0]);
			$testcase2OutTrim = explode("=", $testcase2ArrDecoded[1]);
			$testcaseVariable2 = $testcase2VarTrim[1];
			$testcaseOutput2 = $testcase2OutTrim[1];

			$testcase3 = $respQuestion['testcase3Key'];

			$testcase3Arr = explode(",", $testcase3);
			$testcase3Arr = explode(",", $testcase3);
			$testcase3ArrEncoded = json_encode($testcase3Arr);
			$testcase3ArrDecoded = json_decode($testcase3ArrEncoded, true);
			$testcase3VarTrim = explode("=", $testcase3ArrDecoded[0]);
			$testcase3OutTrim = explode("=", $testcase3ArrDecoded[1]);
			$testcaseVariable3 = $testcase3VarTrim[1];
			$testcaseOutput3 = $testcase3OutTrim[1];

			$testcase4 = $respQuestion['testcase4Key'];

			$testcase4Arr = explode(",", $testcase4);
			$testcase4Arr = explode(",", $testcase4);
			$testcase4ArrEncoded = json_encode($testcase4Arr);
			$testcase4ArrDecoded = json_decode($testcase4ArrEncoded, true);
			$testcase4VarTrim = explode("=", $testcase4ArrDecoded[0]);
			$testcase4OutTrim = explode("=", $testcase4ArrDecoded[1]);
			$testcaseVariable4 = $testcase4VarTrim[1];
			$testcaseOutput4 = $testcase4OutTrim[1];

			$testcase5 = $respQuestion['testcase5Key'];

			$testcase5Arr = explode(",", $testcase5);
			$testcase5Arr = explode(",", $testcase5);
			$testcase5ArrEncoded = json_encode($testcase5Arr);
			$testcase5ArrDecoded = json_decode($testcase5ArrEncoded, true);
			$testcase5VarTrim = explode("=", $testcase5ArrDecoded[0]);
			$testcase5OutTrim = explode("=", $testcase5ArrDecoded[1]);
			$testcaseVariable5 = $testcase5VarTrim[1];
			$testcaseOutput5 = $testcase5OutTrim[1];

			$testcaseVariableArray = [];
			$testcaseOutputArray = []; 

			// testcases need to have both input and output available for it to be considered during the grading

			if ($testcaseVariable1 != NULL && $testcaseOutput1 != NULL){
				array_push($testcaseVariableArray, $testcaseVariable1);
				array_push($testcaseOutputArray, $testcaseOutput1);
			}
			if ($testcaseVariable2 != NULL && $testcaseOutput2 != NULL){
				array_push($testcaseVariableArray, $testcaseVariable2);
				array_push($testcaseOutputArray, $testcaseOutput2);
			}
			if ($testcaseVariable3 != NULL && $testcaseOutput3 != NULL){
				array_push($testcaseVariableArray, $testcaseVariable3);
				array_push($testcaseOutputArray, $testcaseOutput3);
			}
			if ($testcaseVariable4 != NULL && $testcaseOutput4 != NULL){
				array_push($testcaseVariableArray, $testcaseVariable4);
				array_push($testcaseOutputArray, $testcaseOutput4);
			}
			if ($testcaseVariable5 != NULL && $testcaseOutput5 != NULL){
				array_push($testcaseVariableArray, $testcaseVariable5);
				array_push($testcaseOutputArray, $testcaseOutput5);
			}

			/*
			echo "<br>THESE ARE THE ARRAYS FOR TESTCASES!!! <br>";
			print_r($testcaseVariableArray);
			echo "<br>";
			print_r($testcaseOutputArray);
			echo "<br><br>";


			echo "<br>EACH OUTPUT TEST<br><br>"; 
			*/

			$feed = "[AUTOMATIC PYTHON GRADER RESULTS]\n\n";

			// iterating through potential testcases 
			for ($i = 0; $i < count($testcaseVariableArray); $i++){

				// python script to write onto
				$filename = "exec.py";
				$myfile = fopen($filename, "w") or die("Unable to open file!");
				fwrite($myfile, $answerKeyQuestions[$question]);
				fwrite($myfile, "\n");
				fwrite($myfile, "print(");
				fwrite($myfile, $funcName);
				fwrite($myfile, "(");
				fwrite($myfile, $testcaseVariableArray[$i]);
				fwrite($myfile, "))");
				fclose($myfile);

				// shell executing the python script and saving the result 
				$result = shell_exec("python /afs/cad/u/j/c/jc889/public_html/cs490/exec.py 2>&1");

				// echo the result from the python file
				echo "<br>The result of the python file is: <br> ";
				echo $result . "<br><br>";				

				$answerCode =  file_get_contents( "exec.py" );
				// trimming the result to ensure correct comparisons
				$d = trim($result);
				$answer = str_replace('"', "", $testcaseOutputArray[$i]);	

				// the function name by the student
				echo "This is the answer header: <br>";
				$head = strtok($answerCode, ':');
				echo $head . "<br><br>";

				// the function name that should be correct
				echo "This is the CORRECT header: <br>";
				$correctHead = "def " . $funcName . "(X)";
				echo $correctHead . "<br><br>";



				// if the function name by student does not match with the correct function name...
				// replace the function name and still run a new script to see if results match with
				// testcases
				
				if ($head != $correctHead && strlen($answerCode) > 20){
					echo "This is the wrong answer with wrong funcname <br>";
					echo $answerCode ."<br><br>";
					// replacing the wrong function name with the correct one
					$correctAnswerCode = str_replace($head, $correctHead, $answerCode);
					echo "This is the correct answer to test cases now. <br>";
					echo $correctAnswerCode ."<br><br>";

					$filename = "exec.py";

					$myfile = fopen($filename, "w") or die("Unable to open file!");
					fwrite($myfile, $correctAnswerCode);
					fclose($myfile);

					$result = shell_exec("python /afs/cad/u/j/c/jc889/public_html/cs490/exec.py 2>&1");

					echo "The result of the correct funcname for the answer is now: <br>";
					echo $result . "<br><br>";
					
					$d = trim($result);
					
				}

				

				$index = $i + 1; 

				if ($d == $answer){
				$points += (($answerPointKey[$question] / count($testcaseVariableArray)) * 0.8);
				echo $feed .= "Testcase " . $index ." matches. \nThese are your points: " . $points . "\n";
				// have to zero out points otherwise they keep stacking 
		
				}

				// if testcase does not match with results, no points. 
				if ($d != $answer){
					$points += 0;
					echo $feed .= "Testcase " . $index ." does not match. \nThese are your points: " . $points . "\n";
				}



			}

			// if correct function header
			if ($head == $correctHead){
				echo $feed .= "Function name was correct. \n"; 
				$points += ($answerPointKey[$question]/5);
				echo $feed .= "These are your points: " . $points . "\n";
			}

			// if incorrect function header 
			if ($head != $correctHead){
				echo $feed .= "Function name was incorrect. \n";
				echo $feed .= "These are your points: " . $points . "\n";
			}


			// need to push all the feedback, points, question IDs in one array for the putGrade curl 
			array_push($feedbackArray, $feed);		
			echo "<br><br> This is the feedback array: ";
			print_r($feedbackArray);
			echo "<br><br>"; 

			array_push($pointsArray, $points);
			echo "<br><br> This is the point array: ";
			print_r($pointsArray);
			echo "<br><br>";

			array_push($questionIDArray, $question);
			echo "<br><br> This is the question id array: ";
			print_r($questionIDArray);
			echo "<br><br>";

			// resetting the points and feedback otherwise it will stack on previous student's responses
			$feed = "";
			$points = 0;
			

		}

		// just echoing some important info to make sure that the putGrade curl will work 100%
		echo "<br><br><br> THESE ARE THE ITEMS THAT I WILL CURL TO BACKEND FOR PUTGRADE ARRAY <br>";
		echo "class: " . $classID . "<br>";
		echo "student: " . $students . "<br>";
		echo "questionIdKeys: ";
		print_r($questionIDArray);
		echo "<br>feedback array: ";
		print_r($feedbackArray);
		echo "<br>points array: ";
		print_r($pointsArray);
		echo "<br>";


		echo "TOTAL POSSIBLE POINTS ARRAY: <br>";
		print_r($answerPointKey);
		echo "<br>" . array_sum($answerPointKey); 

		echo "<br><br>This is the scaling factor: <br>";
		print_r($gradeScale);
		echo "<br><br>";

		// if there is no answer set the gradeKey to -1.0 so the student can take the exam 
		if(empty($answerKeyQuestions[$question])){
			echo "<br>Set gradeKey to -1.0<br><br>"; 
			$gradeKey = -1.0; 
		}

		// otherwise, calculate the new grade
		else{
			echo "<br>";
			// ( points earned / total points possible ) * exam scale 
			echo "grade: " . $gradeKey = ((array_sum($pointsArray) / array_sum($answerPointKey)) * $gradeScale); 
			echo "<br><br><br>";
		}

		// if the question ID array is empty, then there is nothing to curl 
		if ($questionIDArray != NULL){
			// putGrades array credentials
			$arrPutGrades = ["queryType" => "putGrade", "classIDKey" => $classID, "studentIDKey" => $students, "feedBack" => $feedbackArray, "questionIDKeys" => $questionIDArray, "scoresKeys" => $pointsArray, "gradeKey" => $gradeKey];
			$arrPutGrades = json_encode($arrPutGrades);
			
			// initialize the curl 
			$curlPutGrades = curl_init();
			// info needed to completel curl
			curl_setopt($curlPutGrades, CURLOPT_URL, 'https://web.njit.edu/~tc95/CS490/beta/model.php');
			curl_setopt($curlPutGrades, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlPutGrades, CURLOPT_POST, 1);
			curl_setopt($curlPutGrades, CURLOPT_POSTFIELDS, $arrPutGrades);
			// executing the curl
			$testGrades = curl_exec($curlPutGrades);

			
			// echoing the curl request
			echo "RESPONSE: <br>";
			echo $testGrades . "<br><br>";
			// closing the curl
			curl_close($curlAnswers);
		}


		// resestting the arrays used for the curl for the next student 
		$questionIDArray = [];
		$feedbackArray = [];
		$pointsArray = [];

	}
}


?>