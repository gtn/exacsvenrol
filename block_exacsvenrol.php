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
		if (is_siteadmin()){
			$icon = '<img src="' . $CFG->wwwroot . '/blocks/exacsvenrol/icons/user-solid.svg' . '" class="icon" alt="" />';
			$content .= $icon . '<a href="' . $CFG->wwwroot . '/blocks/exacsvenrol/import.php">Upload Users</a><br/>';
			$content .= $icon . '<a href="' . $CFG->wwwroot . '/blocks/exacsvenrol/importNew.php">Upload Users (New)</a>';
		}
        $this->content = new stdClass();
        $this->content->text = $content;
        return $this->content;
    }
    // The PHP tag and the curly bracket for the class definition
    // will only be closed after there is another function added in the next section.
}

?>
