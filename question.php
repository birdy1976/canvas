<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Short answer question definition class.
 *
 * @package    qtype
 * @subpackage canvas
 * @copyright  2012 Martin Vögeli (Voma) {@link http://moodle.ch/}, based on 2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Represents a short answer question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_canvas_question extends question_graded_by_strategy
        implements question_response_answer_comparer {
    /** @var boolean whether answers should be graded case-sensitively. */
    public $usecase;
    /** @var array of question_answer. */
    public $answers = array();

    public function __construct() {
        parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    public function get_expected_data() {
        return array('answer' => PARAM_RAW_TRIMMED);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            return $response['answer'];
        } else {
            return null;
        }
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
                ($response['answer'] || $response['answer'] === '0');
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_canvas');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'answer');
    }

    public function get_answers() {
        return $this->answers;
    }

    public function compare_response_with_answer(array $response, question_answer $answer) {
		/* Voma start */
		$temp1 = $this->answers;
		$temp2 = reset($temp1); $strSolution = $temp2->answer;
		$temp2 = next($temp1); $strURL = $temp2->answer;
		$temp2 = next($temp1); $intRadius = $temp2->answer;

		$arrT = self::getArray($strSolution); // Teacher
		$arrS = self::getArray($response['answer']); // Student

		$intRadius2 = 4*$intRadius*$intRadius;

		$fltThreshold = (5+0.5*$this->usecase)/10;

		$intTRight = 0; 
		$intSRight = 0;

		foreach ($arrT as $i => $valueT) {
			foreach ($arrS as $j => $valueS) {
				$fltDistance2 = pow($arrT[$i][0]-$arrS[$j][0],2) + pow($arrT[$i][1]-$arrS[$j][1],2);
				if($intRadius2-$fltDistance2 >= 0){
					if(!$arrT[$i][2]){$arrT[$i][2] = true; $intTRight++;}
					if(!$arrS[$j][2]){$arrS[$j][2] = true; $intSRight++;}
				}
			}			
		}
		/* Voma end */

		/* Voma edit start */
        /* return self::compare_string_with_wildcard(
                $response['answer'], $answer->answer, !$this->usecase); */
		if(count($arrS) != 0){
			return ($intTRight/count($arrT)*$intSRight/count($arrS) >= $fltThreshold ? true : false);
		}else{
			return false;
		}
		/* Voma end */
    }

	/* Voma start */
    public static function getArray($strArray){
		if($strArray != ""){
			$arr1 = split(",", $strArray); $arr2;
			foreach ($arr1 as $key => $value) {
				if($key%2 == 0){
					$arr2[$key/2][0] = $arr1[$key]; // x
					$arr2[$key/2][1] = $arr1[$key+1]; // y
					$arr2[$key/2][2] = false;
				}
			}
			return $arr2;
		}else{
			return array();
		}
	}
	/* Voma end */

    public static function compare_string_with_wildcard($string, $pattern, $ignorecase) {
        // Break the string on non-escaped asterisks.
        $bits = preg_split('/(?<!\\\\)\*/', $pattern);
        // Escape regexp special characters in the bits.
        $excapedbits = array();
        foreach ($bits as $bit) {
            $excapedbits[] = preg_quote(str_replace('\*', '*', $bit));
        }
        // Put it back together to make the regexp.
        $regexp = '|^' . implode('.*', $excapedbits) . '$|u';

        // Make the match insensitive if requested to.
        if ($ignorecase) {
            $regexp .= 'i';
        }

        return preg_match($regexp, trim($string));
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $currentanswer = $qa->get_last_qt_var('answer');
            $answer = $qa->get_question()->get_matching_answer(array('answer' => $currentanswer));
            $answerid = reset($args); // itemid is answer id.
            return $options->feedback && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }
}
