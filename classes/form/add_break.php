<?php
class add_break extends moodleform
{

    public function definition()
    {
        $mform = $this->_form;
        $text = get_string('fromdate_text', 'local_good_habits');
        $mform->addElement('date_selector', 'fromdate', $text);
        $text = get_string('todate_text', 'local_good_habits');
        $mform->addElement('date_selector', 'todate', $text);
        $text = get_string('addbreak_submit_text', 'local_good_habits');
        $this->add_action_buttons(null, $text);
    }

}