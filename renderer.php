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
 * Short answer question renderer class.
 *
 * @package	qtype
 * @subpackage canvas
 * @copyright  2012 Martin Vögeli (Voma) {@link http://moodle.ch/}, based on 2009 The Open University
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for short answer questions.
 *
 * @copyright  2009 The Open University
 * @license	http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_canvas_renderer extends qtype_renderer {
	public function formulation_and_controls(question_attempt $qa,
			question_display_options $options) {

		$question = $qa->get_question();
		$currentanswer = $qa->get_last_qt_var('answer');

		$inputname = $qa->get_qt_field_name('answer');
		$inputattributes = array(
			'type' => 'text',
			'name' => $inputname,
			'value' => $currentanswer,
			'id' => $inputname,
			'size' => 80,
		);

		/* Voma Start */
		// Include module JavaScript
		$this->page->requires->js_init_call('M.qtype_canvas.init');
		// http://docs.moodle.org/dev/Using_jQuery_with_Moodle_2.0
		$this->page->requires->js('/question/type/canvas/jquery-1.7.2.js');
		// require_js(array('yui_yahoo', 'node'));
		// Prepare some variables
		$temp1; $temp2; // temporary variables
		$strID = str_replace ("answer", "", $inputattributes['id']);
		$temp1 = $question->answers;
		$temp2 = reset($temp1); $strSolution = $temp2->answer;
		$temp2 = next($temp1); $strURL = $temp2->answer;
		$temp2 = next($temp1); $intRadius = $temp2->answer;
		/* Voma End */

		if ($options->readonly) {
			$inputattributes['readonly'] = 'readonly';
		}

		$feedbackimg = '';
		if ($options->correctness) {
			$answer = $question->get_matching_answer(array('answer' => $currentanswer));
			if ($answer) {
				$fraction = $answer->fraction;
			} else {
				$fraction = 0;
			}
			$inputattributes['class'] = $this->feedback_class($fraction);
			$feedbackimg = $this->feedback_image($fraction);
		}

		$questiontext = $question->format_questiontext($qa);
		$placeholder = false;
		if (preg_match('/_____+/', $questiontext, $matches)) {
			$placeholder = $matches[0];
			$inputattributes['size'] = round(strlen($placeholder) * 1.1);
		}

		$input = html_writer::empty_tag('input', $inputattributes) . $feedbackimg;

		if ($placeholder) {
			$questiontext = substr_replace($questiontext, $input,
					strpos($questiontext, $placeholder), strlen($placeholder));
		}

		$result = html_writer::tag('div', $questiontext, array('class' => 'qtext'));

		if (!$placeholder) {
			$result .= html_writer::start_tag('div', array('class' => 'ablock'));
			$result .= get_string('answer', 'qtype_canvas',
					html_writer::tag('div', $input, array('class' => 'answer')));
			/* Voma Start */
			// Write hidden field Solution
			// if(strpos($_SERVER["PHP_SELF"], "review.php")){
				$temp1 = array('id' => $strID.'solution', 'name' => $strID.'solution', 'type' => 'hidden', 'value' => $strSolution);
				$result .= html_writer::empty_tag('input', $temp1);
			// }
			// Write hidden field URL
			$temp1 = array('id' => $strID.'url', 'name' => $strID.'url', 'type' => 'hidden', 'value' => $strURL);
			$result .= html_writer::empty_tag('input', $temp1);
			// Write hidden field Radius
			$temp1 = array('id' => $strID.'radius', 'name' => $strID.'radius', 'type' => 'hidden', 'value' => $intRadius);
			$result .= html_writer::empty_tag('input', $temp1);
			// Write DIV for canvas
			$result .= html_writer::start_tag('div', array('class' => 'qtype_canvas', 'id' => $strID));
			$result .= html_writer::end_tag('div');
			/* Voma End */
			$result .= html_writer::end_tag('div');
		}

		if ($qa->get_state() == question_state::$invalid) {
			$result .= html_writer::nonempty_tag('div',
					$question->get_validation_error(array('answer' => $currentanswer)),
					array('class' => 'validationerror'));
		}
		return $result;
	}

	public function specific_feedback(question_attempt $qa) {
		$question = $qa->get_question();

		$answer = $question->get_matching_answer(array('answer' => $qa->get_last_qt_var('answer')));
		if (!$answer || !$answer->feedback) {
			return '';
		}

		return $question->format_text($answer->feedback, $answer->feedbackformat,
				$qa, 'question', 'answerfeedback', $answer->id);
	}

	public function correct_response(question_attempt $qa) {
		$question = $qa->get_question();

		$answer = $question->get_matching_answer($question->get_correct_response());
		if (!$answer) {
			return '';
		}

		return get_string('correctansweris', 'qtype_canvas', s($answer->answer));
	}
}
