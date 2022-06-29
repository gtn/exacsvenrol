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

global $ADMIN, $CFG, $DB, $PAGE;
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$PAGE->set_context(context_system::instance());

$PAGE->set_url(new moodle_url('/blocks/exacsvenrol/import.php'));
$PAGE->set_title("Import Title");
$PAGE->set_heading("Import Heading");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_FILES)) {
        try {
            move_uploaded_file($_FILES['file']['tmp_name'], $CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']);
        } finally {
            try{
                $myfile = fopen( $CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name'], "r");
                $value = fread($myfile, filesize($CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']));
                fclose($myfile);

                $users = explode("\n", $value);

                foreach (array_filter($users) as $u) {
                    $u = explode(",", $u);
                    if(($DB->get_record('user', ['username' => $u[0]])) !== null){
                        $user = create_user_record($u[0], $u[4]);
                        $user->firstname = $u[1];
                        $user->lastname = $u[2];
                        $user->email = $u[3];
                        $DB->update_record('user', $user);
                    }
                }
            } catch (Exception $e){
                echo "One of the users you wanted to upload is already existing!";
            }
        }
    } else {
        echo "<br/> Something went wrong!";
    }
}
?>
<h1>EXACSVENROL</h1>

<form method="post" enctype="multipart/form-data">
    <label>Please select your CSV-File (*.csv)!
    </label>
    <br/>
    <input name="file" type="file" accept="text/csv">
    <br/><br/>
    <input type="submit" value="Upload CSV!">
</form>
