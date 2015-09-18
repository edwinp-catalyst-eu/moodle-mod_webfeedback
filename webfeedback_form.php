<?php
require_once("$CFG->libdir/formslib.php");

class webfeedback_form extends moodleform {
    function definition() {
        global $CFG;

        $mform =& $this->_form;
        
        // add hidden form elements
        $mform->addElement('hidden','courseid');
        $mform->addElement('hidden','userid');
        $mform->addElement('hidden','pageurl');
        $mform->addElement('hidden','pagetitle');
        $mform->addElement('hidden','uaos');
        $mform->addElement('hidden','uabrowser');
        $mform->addElement('hidden','uabrowserversion');
        $mform->addElement('hidden','uaflashversion'); // gets populated in javascript
        $mform->addElement('hidden','uascreensize'); // gets populated in javascript
        
        // add header and intro text
        $mform->addElement('header', 'displayinfo', get_string('formheading', 'webfeedback'));
        $mform->addElement('html', '<div class="form-intro-text">' . get_string('formintro', 'webfeedback') .'</div>');

        // add the radio buttons
        $images = webfeedback_radio_images();
        $radioarray=array();
        for($i = 0; $i < count($images); $i++){
            $radioarray[] =&$mform->createElement('radio', 'rating', '',$images[$i],$i);
        }
        $mform->addGroup($radioarray, 'radioar', get_string('ratingselect', 'webfeedback'), array(' '), false);
        $mform->setDefault('rating', 2);

        // add description field
        $attributes=array('rows'=>'10', 'cols'=>'35');
        $mform->addElement('textarea', 'description', get_string('feedbacktext', 'webfeedback'), $attributes); $mform->setType('description', PARAM_TEXT);
        $mform->setType('description', PARAM_TEXT);

        // submit/cancel buttons
        $this->add_action_buttons();
    }
}

function webfeedback_radio_images() {
    global $CFG;
    return array('<img src="' . $CFG->wwwroot . '/mod/webfeedback/img/face-crying.png" alt="Sad">',
                '<img src="' . $CFG->wwwroot . '/mod/webfeedback/img/face-sad.png" alt="Not satisfied">',
                '<img src="' . $CFG->wwwroot . '/mod/webfeedback/img/face-plain.png" alt="Indifferent">',
                '<img src="' . $CFG->wwwroot . '/mod/webfeedback/img/face-smile.png" alt="Satisfied">',
                '<img src="' . $CFG->wwwroot . '/mod/webfeedback/img/face-grin.png" alt="Happy">');
}

?>