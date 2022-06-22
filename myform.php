<?php

// moodleform is defined in formslib.php
global $CFG;
require_once("$CFG->libdir/formslib.php");

class simplehtml_form extends moodleform {
    // Add elements to form.
    public function definition() {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // Add elements to your form.

        $this->add_action_buttons();
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}