<?php
	//Returns 0 if incorrect, 1 if partially correct, 2 if completely correct
	//Partially correct and incorrect answers are treated as partially correct
	function isCorrect($answer, $correct) {
		$cor = true;
		$partial = false;
		
		foreach(explode(",", $correct) as $c) {
			$match = false;
			foreach(explode(",", $answer) as $a) {
				if (trim($c) == trim($a)) {
					$match = true;
					break;
				}
			}
			
			if ($match) {
				$partial = true;
			} else {
				$cor = false;
			}
		}
		
		$ret = 0;
		if ($cor)
			$ret = 2;
		else if ($partial)
			$ret = 1;
		
		return $ret;
	}
?>