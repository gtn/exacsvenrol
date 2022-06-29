<?php

class block_exacsvenrol extends block_base
{
    public function init()
    {
        $this->title = get_string('exacsvenrol', 'block_exacsvenrol');
    }

    public function get_content()
    {
        global $CFG;

        if ($this->content !== null) {
            return $this->content;
        }
        $content = '';

        $icon = '<img src="' . $CFG->wwwroot . '/blocks/exacsvenrol/icons/user-solid.svg' . '" class="icon" alt="" />';
        $content .= $icon . '<a href="/blocks/exacsvenrol/import.php">Upload Users</a>';

        $this->content = new stdClass();
        $this->content->text = $content;
        return $this->content;
    }

    public function readCSV()
    {
        global $CFG, $DB;

        $myfile = fopen( $CFG->fileroot . "\\blocks\\exacsvenrol\\user.csv", "r");
        $value = fread($myfile, filesize($CFG->fileroot . "\\blocks\\exacsvenrol\\user.csv"));
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
