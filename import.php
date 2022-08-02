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

global $ADMIN, $CFG, $DB, $PAGE, $OUTPUT;

use context_system;
use moodle_url;

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/exacsvenrol/import.php'));
$PAGE->set_title("Exacsvenrol");
$PAGE->set_heading("Exacsvenrol");
$PAGE->set_pagelayout('mydashboard');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    createUser();
}

echo $OUTPUT->header();

function createUser(){
    global $CFG, $DB;
    if (!empty($_FILES)) {
        try {
            move_uploaded_file($_FILES['file']['tmp_name'], $CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']);
        } finally {
            try {
                $myfile = fopen($CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name'], "r");
                $value = fread($myfile, filesize($CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']));
                unlink($CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']);
                fclose($myfile);

                $users = explode("\n", $value);

                foreach (array_filter($users) as $u) {
                    $u = explode(",", $u);
                    if (($DB->get_record('user', ['username' => $u[0]])) !== null) {
                        $user = create_user_record($u[0], $u[4]);
                        $user->firstname = $u[1];
                        $user->lastname = $u[2];
                        $user->email = $u[3];

                        $DB->update_record('user', $user);

                        $user = $DB->get_record('user', ['username' => $u[0]]);
                        $course = $DB->get_record('course', ['fullname' => $u[6]]);
                        enrolUser($user->id, strtolower($u[5]), $course->id);
                    }
                }
                echo "Users were created successfully!";
            } catch (Exception $e) {
                echo $e;
            }
        }
    } else {
        echo "<br/> Couldn't find the file!";
    }
}

function enrolUser($userid, $role, $courseid)
{
    $manualinstance = null;
    $enrol = enrol_get_plugin("manual");
    $instances = enrol_get_instances($courseid, true);

    foreach ($instances as $instance) {
        if ($instance->enrol == "manual") {
            $manualinstance = $instance;
            break;
        }
    }

    if ($role == "manager") {
        $roleid = 1;
    } elseif ($role == "coursecreator") {
        $roleid = 2;
    } elseif ($role == "editingteacher") {
        $roleid = 3;
    } elseif ($role == "teacher") {
        $roleid = 4;
    } elseif ($role == "student") {
        $roleid = 5;
    } elseif ($role == "guest") {
        $roleid = 6;
    } elseif ($role == "user") {
        $roleid = 7;
    } elseif ($role == "frontpage") {
        $roleid = 8;
    } else {
        throw new Exception("There isn't a role called: " . $role);
    }

    if ($manualinstance != null) {
        $enrol->enrol_user($manualinstance, $userid, $roleid);
    }
}

?>
<form method="post" enctype="multipart/form-data">
    <label>Please select your CSV-File (*.csv)!
    </label>
    <br/>
    <input name="file" type="file" accept="text/csv">
    <br/><br/>
    <input type="submit" value="Upload CSV!">
</form>

<?php
    echo $OUTPUT->footer();
?>
