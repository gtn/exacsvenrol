<?php
require_once("myform.php");

class block_exacsvenrol extends block_base
{
    public function init()
    {
        $this->title = get_string('exacsvenrol', 'block_exacsvenrol');
    }

    public function get_content()
    {
        if ($this->content !== null) {
            return $this->content;
        }

        $mform = new simplehtml_form();


        if (($data = $mform->get_data())) {
            die();
        } else {
            $this->content->text = $mform->render();
        }
        return $this->content;
    }

    public function readCSV()
    {
        global $DB;

        $myfile = fopen("C:\Users\wolfg\Documents\moodle\server\moodle\blocks\\exacsvenrol\\user.csv", "r");
        $value = fread($myfile, filesize("C:\Users\wolfg\Documents\moodle\server\moodle\blocks\\exacsvenrol\\user.csv"));
        fclose($myfile);

        $users = explode("\n", $value);

        foreach ($users as $u) {
            $u = explode(",", $u);
            $user = create_user_record($u[0], $u[4]);
            $user->firstname = $u[1];
            $user->lastname = $u[2];
            $user->email = $u[3];
            $DB->update_record('user', $user);
        }
    }
    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.
}

?>
