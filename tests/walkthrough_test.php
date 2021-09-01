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
 * This file contains tests that walks an answersselect question through various interaction models.
 *
 * @package    qtype_answersselect
 * @copyright 2021 Joseph Rézeau <joseph@rezeau.org>
 * @copyright based on work by 2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/answersselect/questiontype.php');


/**
 * Unit tests ofr the answersselect question type.
 *
 * @copyright 2021 Joseph Rézeau <joseph@rezeau.org>
 * @copyright based on work by 2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_answersselect_walkthrough_test extends qbehaviour_walkthrough_test_base {

    public function test_shows_standard_instruction_yes() {

        // Create an answersselect question.
        $mc = test_question_maker::make_question('answersselect', 'two_of_four');
        $mc->shuffleanswers = false;
        $mc->showstandardinstruction = true;
        $mc->answersselectmode = 0;
        $mc->randomselectcorrect = 0;
        $mc->randomselectincorrect = 0;
        $mc->correctchoicesseparator = 0;
        $this->start_attempt_at_question($mc, 'interactive', 3);
        $this->render();

        // Check for 'Show standard instruction'.
        $standardinstruction = get_string('selectmulti', 'qtype_multichoice');
        $this->assertStringContainsString($standardinstruction, $this->currentoutput);
    }

    public function test_shows_standard_instruction_no() {

        // Create an answersselect question.
        $mc = test_question_maker::make_question('answersselect', 'two_of_four');
        $mc->shuffleanswers = false;
        $mc->showstandardinstruction = false;
        $mc->answersselectmode = 0;
        $mc->randomselectcorrect = 0;
        $mc->randomselectincorrect = 0;
        $mc->correctchoicesseparator = 0;
        $this->start_attempt_at_question($mc, 'interactive', 3);
        $this->render();

        // Check for 'Show standard instruction'.
        $standardinstruction = get_string('selectmulti', 'qtype_multichoice');
        $this->assertStringNotContainsString($standardinstruction, $this->currentoutput);
    }

    public function test_interactive_behaviour() {

        // Create an answersselect question.
        $mc = test_question_maker::make_question('answersselect', 'two_of_four');
        $mc->shuffleanswers = false;
        $mc->showstandardinstruction = true;
        $mc->answersselectmode = 0;
        $mc->randomselectcorrect = 0;
        $mc->randomselectincorrect = 0;
        $mc->correctchoicesseparator = 0;
        $this->start_attempt_at_question($mc, 'interactive', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, false),
                $this->get_contains_mc_checkbox_expectation('choice1', true, false),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, false),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(3),
                $this->get_no_hint_visible_expectation());

        // Save the wrong answer.
        $this->process_submission(array('choice1' => '1', 'choice3' => '1'));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, false),
                $this->get_contains_mc_checkbox_expectation('choice1', true, true),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, true),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(3),
                $this->get_no_hint_visible_expectation());

        // Submit the wrong answer.
        $this->process_submission(array('choice1' => '1', 'choice3' => '1', '-submit' => '1'));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_mc_checkbox_expectation('choice0', false, false),
                $this->get_contains_mc_checkbox_expectation('choice1', false, true),
                $this->get_contains_mc_checkbox_expectation('choice2', false, false),
                $this->get_contains_mc_checkbox_expectation('choice3', false, true),
                $this->get_contains_try_again_button_expectation(true),
                $this->get_does_not_contain_correctness_expectation(),
                $this->get_contains_hint_expectation('Hint 1'),
                $this->get_contains_num_parts_correct(0),
                $this->get_contains_standard_incorrect_combined_feedback_expectation(),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice0'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice1'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice2'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice3'));

        // Do try again.
        $this->process_submission(array('-tryagain' => 1));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, false),
                $this->get_contains_mc_checkbox_expectation('choice1', true, true),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, true),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(2),
                $this->get_no_hint_visible_expectation());

        // Submit a partially right answer.
        $this->process_submission(array('choice0' => '1', 'choice3' => '1', '-submit' => '1'));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_mc_checkbox_expectation('choice0', false, true),
                $this->get_contains_mc_checkbox_expectation('choice1', false, false),
                $this->get_contains_mc_checkbox_expectation('choice2', false, false),
                $this->get_contains_mc_checkbox_expectation('choice3', false, true),
                $this->get_contains_try_again_button_expectation(true),
                $this->get_does_not_contain_correctness_expectation(),
                $this->get_contains_hint_expectation('Hint 2'),
                $this->get_contains_num_parts_correct(1),
                $this->get_contains_standard_partiallycorrect_combined_feedback_expectation(),
                $this->get_contains_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice0', '1'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice1'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice2'),
                $this->get_contains_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice3', '0'));

        // Do try again.
        $this->process_submission(array('choice0' => '1', '-tryagain' => 1));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, true),
                $this->get_contains_mc_checkbox_expectation('choice1', true, false),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, false),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(1),
                $this->get_no_hint_visible_expectation());

        // Submit the right answer.
        $this->process_submission(array('choice0' => '1', 'choice2' => '1', '-submit' => '1'));

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(1.5);
        $this->check_current_output(
                $this->get_contains_mc_checkbox_expectation('choice0', false, true),
                $this->get_contains_mc_checkbox_expectation('choice1', false, false),
                $this->get_contains_mc_checkbox_expectation('choice2', false, true),
                $this->get_contains_mc_checkbox_expectation('choice3', false, false),
                $this->get_does_not_contain_try_again_button_expectation(),
                $this->get_contains_correct_expectation(),
                $this->get_contains_standard_correct_combined_feedback_expectation());
    }

    public function test_interactive_behaviour2() {

        // Create an answersselect question.
        $mc = test_question_maker::make_question('answersselect', 'two_of_four');
        $mc->showstandardinstruction = 1;
        $mc->hints = array(
            new qtype_answersselect_hint(1, 'Hint 1', FORMAT_HTML, true, true, true),
            new qtype_answersselect_hint(2, 'Hint 2', FORMAT_HTML, true, true, true),
        );
        $mc->shuffleanswers = false;
        $mc->showstandardinstruction = true;
        $mc->answersselectmode = 0;
        $mc->randomselectcorrect = 0;
        $mc->randomselectincorrect = 0;
        $mc->correctchoicesseparator = 0;
        $this->start_attempt_at_question($mc, 'interactive', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, false),
                $this->get_contains_mc_checkbox_expectation('choice1', true, false),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, false),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(3),
                $this->get_no_hint_visible_expectation(),
                new question_pattern_expectation('/' .
                        preg_quote(get_string('selectmulti', 'qtype_multichoice'), '/') . '/'));

        // Submit the wrong answer with too manu options selected.
        $this->process_submission(array(
                'choice1' => '1', 'choice2' => '1', 'choice3' => '1', '-submit' => '1'));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_mc_checkbox_expectation('choice0', false, false),
                $this->get_contains_mc_checkbox_expectation('choice1', false, true),
                $this->get_contains_mc_checkbox_expectation('choice2', false, true),
                $this->get_contains_mc_checkbox_expectation('choice3', false, true),
                $this->get_contains_try_again_button_expectation(true),
                $this->get_does_not_contain_correctness_expectation(),
                $this->get_contains_hint_expectation('Hint 1'),
                new question_pattern_expectation('/' .
                        preg_quote(get_string('toomanyselected', 'qtype_multichoice'), '/') . '/'),
                new question_no_pattern_expectation('/Three is odd/'),
                $this->get_contains_standard_partiallycorrect_combined_feedback_expectation(),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice0'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice1'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice2'),
                $this->get_does_not_contain_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice3'),
                new question_pattern_expectation('/' .
                        preg_quote(get_string('selectmulti', 'qtype_multichoice'), '/') . '/'));
    }

    public function test_interactive_clear_wrong() {

        // Create an answersselect question.
        $mc = test_question_maker::make_question('answersselect', 'two_of_four');
        $mc->hints = array(
            new qtype_answersselect_hint(1, 'Hint 1', FORMAT_HTML, true, true, true),
            new qtype_answersselect_hint(2, 'Hint 2', FORMAT_HTML, true, true, true),
        );
        $mc->shuffleanswers = false;
        $mc->showstandardinstruction = true;
        $mc->answersselectmode = 0;
        $mc->randomselectcorrect = 0;
        $mc->randomselectincorrect = 0;
        $mc->correctchoicesseparator = 0;
        $this->start_attempt_at_question($mc, 'interactive', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, false),
                $this->get_contains_mc_checkbox_expectation('choice1', true, false),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, false),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(3),
                $this->get_no_hint_visible_expectation());

        // Submit a wrong answer.
        $this->process_submission(array('choice1' => '1', 'choice3' => '1', '-submit' => '1'));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_contains_mc_checkbox_expectation('choice0', false, false),
                $this->get_contains_mc_checkbox_expectation('choice1', false, true),
                $this->get_contains_mc_checkbox_expectation('choice2', false, false),
                $this->get_contains_mc_checkbox_expectation('choice3', false, true),
                $this->get_contains_try_again_button_expectation(true),
                $this->get_does_not_contain_correctness_expectation(),
                $this->get_contains_num_parts_correct(0),
                $this->get_contains_hint_expectation('Hint 1'),
                $this->get_contains_standard_incorrect_combined_feedback_expectation(),
                $this->get_contains_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice1', '0'),
                $this->get_contains_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice3', '0'));

        // Try again.
        $this->process_submission(array('choice1' => '0', 'choice3' => '0', '-tryagain' => '1'));

        // Vreify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, false),
                $this->get_contains_mc_checkbox_expectation('choice1', true, false),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, false),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(2),
                $this->get_no_hint_visible_expectation());

        // Submit a partially right answer.
        $this->process_submission(array('choice0' => '1', 'choice3' => '1', '-submit' => '1'));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_contains_mc_checkbox_expectation('choice0', false, true),
                $this->get_contains_mc_checkbox_expectation('choice1', false, false),
                $this->get_contains_mc_checkbox_expectation('choice2', false, false),
                $this->get_contains_mc_checkbox_expectation('choice3', false, true),
                $this->get_contains_try_again_button_expectation(true),
                $this->get_does_not_contain_correctness_expectation(),
                $this->get_contains_num_parts_correct(1),
                $this->get_contains_hint_expectation('Hint 2'),
                $this->get_contains_standard_partiallycorrect_combined_feedback_expectation(),
                $this->get_contains_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice0', '1'),
                $this->get_contains_hidden_expectation(
                        $this->quba->get_field_prefix($this->slot) . 'choice3', '0'));

        // Try again.
        $this->process_submission(array('choice0' => '1', 'choice3' => '0', '-tryagain' => '1'));

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_contains_marked_out_of_summary(),
                $this->get_contains_question_text_expectation($mc),
                $this->get_contains_mc_checkbox_expectation('choice0', true, true),
                $this->get_contains_mc_checkbox_expectation('choice1', true, false),
                $this->get_contains_mc_checkbox_expectation('choice2', true, false),
                $this->get_contains_mc_checkbox_expectation('choice3', true, false),
                $this->get_contains_submit_button_expectation(true),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_num_parts_correct(),
                $this->get_tries_remaining_expectation(1),
                $this->get_no_hint_visible_expectation());
    }

    public function test_interactive_bug_11263() {

        // Create an answersselect question.
        $mc = test_question_maker::make_question('answersselect', 'two_of_five');
        $mc->penalty = 1;
        $mc->answersselectmode = 0;
        $mc->randomselectcorrect = 0;
        $mc->randomselectincorrect = 0;
        $mc->correctchoicesseparator = 0;
        $this->start_attempt_at_question($mc, 'interactive', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_tries_remaining_expectation(3));

        // Submit a wrong answer.
        $this->process_submission(array(
            'choice0' => '0',
            'choice1' => '0',
            'choice2' => '0',
            'choice3' => '1',
            'choice4' => '1',
            '-submit' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        // Try again.
        $this->process_submission(array(
            'choice0' => '0',
            'choice1' => '0',
            'choice2' => '0',
            'choice3' => '1',
            'choice4' => '1',
            '-tryagain' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_tries_remaining_expectation(2));

        // Submit a wrong answer again.
        $this->process_submission(array(
            'choice0' => '0',
            'choice1' => '0',
            'choice2' => '0',
            'choice3' => '1',
            'choice4' => '1',
            '-submit' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);

        // Try again - clears wrong.
        $this->process_submission(array(
            'choice0' => '0',
            'choice1' => '0',
            'choice2' => '0',
            'choice3' => '0',
            'choice4' => '0',
            '-tryagain' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_tries_remaining_expectation(1));

        // Submit one right choice.
        $this->process_submission(array(
            'choice0' => '1',
            'choice1' => '0',
            'choice2' => '0',
            'choice3' => '0',
            'choice4' => '0',
            '-submit' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(0);
    }

    public function test_interactive_regrade_changing_num_tries_leaving_open() {
        // Create a multichoice multiple question.
        $q = test_question_maker::make_question('answersselect', 'two_of_five');
        $q->answersselectmode = 0;
        $this->start_attempt_at_question($q, 'interactive', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_tries_remaining_expectation(3));

        // Submit the right answer.
        $this->process_submission(array(
            'choice0' => '1',
            'choice1' => '1',
            'choice2' => '0',
            'choice3' => '0',
            'choice4' => '0',
            '-submit' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(3);

        // Now change the quiestion so that answer is only partially right, and regrade.
        $q->answers[15]->fraction = 1;

        $this->quba->regrade_all_questions(false);

        // Verify.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
    }

    public function test_interactive_regrade_changing_num_tries_finished() {
        // Create a multichoice multiple question.
        $q = test_question_maker::make_question('answersselect', 'two_of_five');
        $q->answersselectmode = 0;
        $this->start_attempt_at_question($q, 'interactive', 3);

        // Check the initial state.
        $this->check_current_state(question_state::$todo);
        $this->check_current_mark(null);
        $this->check_current_output(
                $this->get_tries_remaining_expectation(3));

        // Submit the right answer.
        $this->process_submission(array(
            'choice0' => '1',
            'choice1' => '1',
            'choice2' => '0',
            'choice3' => '0',
            'choice4' => '0',
            '-submit' => '1'
        ));

        // Verify.
        $this->check_current_state(question_state::$gradedright);
        $this->check_current_mark(3);

        // Now change the quiestion so that answer is only partially right, and regrade.
        $q->answers[15]->fraction = 1;

        $this->quba->regrade_all_questions(true);

        // Verify.
        $this->check_current_state(question_state::$gradedpartial);
        $this->check_current_mark(2);
    }

    /**
     * Gets contains num parts correct.
     *
     * @param int $num
     */
    protected function get_contains_num_parts_correct($num) {
        $a = new stdClass();
        if ($num == 1) {
            return new question_pattern_expectation('/<div class="numpartscorrect">' .
                preg_quote(get_string('yougot1right', 'qtype_answersselect', $a), '/') . '/');
        } else {
            $f = new NumberFormatter(current_language(), NumberFormatter::SPELLOUT);
            $a->num = $f->format($num);
            return new question_pattern_expectation('/<div class="numpartscorrect">' .
                preg_quote(get_string('yougotnright', 'qtype_answersselect', $a), '/') . '/');
        }
    }
}
