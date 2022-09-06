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
$PAGE->set_title(get_string("pluginname", "block_exacsvenrol"));
$PAGE->set_heading(get_string("pluginname", "block_exacsvenrol"));
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
                $userList = array();
                $counter = 0;
                $errorCounter = 0;
                $enrollCounter = 0;

                foreach (array_filter($users) as $u) {
                    $u = explode(",", $u);
                    if (($DB->get_record('user', ['username' => $u[0]])) == null) {
                        $user = create_user_record($u[0], "sicheresPasswort123!");
                        $user->firstname = $u[1];
                        $user->lastname = $u[2];
                        $user->email = $u[3];

                        $DB->update_record('user', $user);

                        $counter++;
                    } else {
                        $userList[] = $u[0];
                        $errorCounter++;
                    }
                    $user = $DB->get_record('user', ['username' => $u[0]]);
                    $course = $DB->get_record('course', ['shortname' => $u[5]]);
                    $context = context_course::instance($course->id);

                    if(!is_enrolled($context, $user)){
                        enrolUser($user->id, strtolower($u[4]), $course->id);
                        $enrollCounter++;
                    }
                }
            } catch (Exception $e) {
                $msg = $e->getMessage();
                echo "<script>alert('$msg')</script>";
            } finally {
                if($counter > 0){
                    if($counter > 1){
                        $msg = get_string('usersCreated', 'block_exacsvenrol', $counter);
                    } else {
                        $msg = get_string('userCreated', 'block_exacsvenrol', $counter);
                    }
                    echo "<script>alert('$msg')</script>";
                }
                if($enrollCounter > 0){
                    if($enrollCounter > 1){
                        $msg = get_string('enrolledUsers', 'block_exacsvenrol', $enrollCounter);
                    } else {
                        $msg = get_string('enrolledUser', 'block_exacsvenrol', $enrollCounter);
                    }
                    echo "<script>alert('$msg')</script>";
                }
                if($errorCounter > 0){
                    $msg = getErrorUserList($userList);
                    echo "<script>alert('$msg')</script>";
                }
            }
        }
    } else {
        echo get_string('filenotreadable', 'block_exacsvenrol');
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
        throw new Exception(get_string("roleException","block_exacsvenrol", $role));
    }

    if ($manualinstance != null) {
        $enrol->enrol_user($manualinstance, $userid, $roleid);
    }
}

function getErrorUserList($users){
    $msg = "";
    $counter = 0;
    $userList = "";
    foreach ($users as $u){
        if(next($users)){
            $userList .= $u . ", ";
        } else {
            $userList .= "and " . $u;
        }
        $counter++;
    }

    if($counter > 1){
        $msg = get_string('moreUsersExists', 'block_exacsvenrol', $userList);
    } else {
        $msg = get_string('userExists', 'block_exacsvenrol', $userList);
    }

    return $msg;
}

    echo "<form method='post' enctype='multipart/form-data'>
    ". get_string('csvhint', 'block_exacsvenrol') ."
    </label>
    <br/>
    <input name='file' type='file' accept='text/csv'>
    <br/><br/>
    <input type='submit' value='". get_string('uploadButton', 'block_exacsvenrol')."'>
</form>"
?>


<?php
    echo $OUTPUT->footer();
?>
