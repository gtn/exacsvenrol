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

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/blocks/exacsvenrol/importNew.php'));
$PAGE->set_title(get_string("pluginname", "block_exacsvenrol"));
$PAGE->set_heading(get_string("pluginname", "block_exacsvenrol"));
$PAGE->set_pagelayout('mydashboard');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    readCSV();
}
require_login();
if (!is_siteadmin()){
	print_error('Sie besitzen nicht die Rechte um dieses Feature zu verwenden.', 'block_exacsvenrol');
}
echo $OUTPUT->header();

function readCSV()
{
    global $CFG;
    try {
        move_uploaded_file($_FILES['file']['tmp_name'], $CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']);
    } finally {
        $csv = array_map("str_getcsv", file($CFG->fileroot . "\\blocks\\exacsvenrol\\files\\" . $_FILES['file']['name']));
        $keys  = array_shift($csv);

        foreach ($csv as $i=>$row) {
            $csv[$i] = array_combine($keys, $row);
        }

        foreach ($keys as $key) {
            if (strtolower($key) == "email" || strtolower($key) == "e-mail") {
                createUser($csv);
            }

            if (strtolower($key) == "courseid") {
                enrolType($csv, "id");
                break;

            } elseif (strtolower($key) == "courseshort") {
                enrolType($csv, "shortname");
                break;
            }
        }
    }
}

function createUser($value)
{
    global $DB;
    try {
        $counter = 0;
        $errorCounter = 0;
        $userList = array();

        foreach (array_filter($value) as $u) {
            if (($DB->get_record('user', ['username' => $u["username"]])) == null) {
                $user = create_user_record($u["username"], "sicheresPasswort123!");
                $user->firstname = $u["firstname"];
                $user->lastname = $u["lastname"];
                $user->email = $u["email"];

                $DB->update_record('user', $user);

                $counter++;
            } else {
                $userList[] = $u["username"];
                $errorCounter++;
            }
        }
    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo "<script>alert('$msg')</script>";
    } finally {
        if ($counter > 0) {
            if ($counter > 1) {
                $msg = get_string('usersCreated', 'block_exacsvenrol', $counter);
            } else {
                $msg = get_string('userCreated', 'block_exacsvenrol', $counter);
            }
            echo "<script>alert('$msg')</script>";
        }
        if ($errorCounter > 0) {
            $msg = getErrorUserList($userList);
            echo "<script>alert('$msg')</script>";
        }
    }
}

function enrolType($value, $type)
{
    global $DB;
    $enrolCounter = 0;
    foreach (array_filter($value) as $elem) {
        if ($type == "id") {
            $course = $DB->get_record('course', ['id' => intval($elem["courseid"])]);
        } else {
            $course = $DB->get_record('course', ['shortname' => $elem["courseshort"]]);
        }

        $context = context_course::instance($course->id);
        $user = $DB->get_record('user', ['username' => $elem["username"]]);

        if (!is_enrolled($context, $user)) {
            enrolUser($user->id, strtolower($elem["role"]), $course->id);
            $enrolCounter++;
        }
    }

    if ($enrolCounter > 0) {
        if ($enrolCounter > 1) {
            $msg = get_string('enrolledUsers', 'block_exacsvenrol', $enrolCounter);
        } else {
            $msg = get_string('enrolledUser', 'block_exacsvenrol', $enrolCounter);
        }
        echo "<script>alert('$msg')</script>";
    }
}

function enrolUser($userid, $role, $courseid)
{
    global $DB;

    $manualinstance = null;
    $enrol = enrol_get_plugin("manual");
    $instances = enrol_get_instances($courseid, true);

    foreach ($instances as $instance) {
        if ($instance->enrol == "manual") {
            $manualinstance = $instance;
            break;
        }
    }

    try{
        $t = $DB->get_record('role', ['shortname' => strtolower($role)]);
    } catch (Exception $e) {
        throw new Exception(get_string("roleException", "block_exacsvenrol", $role));
    }

    if ($manualinstance != null) {
        $enrol->enrol_user($manualinstance, $userid, $t->id);
    }
}

function getErrorUserList($users)
{
    $msg = "";
    $counter = 0;
    $userList = "";
    foreach ($users as $u) {
        if (next($users)) {
            $userList .= $u . ", ";
        } elseif ($counter > 1) {
            $userList .= "and " . $u;
        } else {
            $userList .= $u;
        }
        $counter++;
    }

    if ($counter > 1) {
        $msg = get_string('moreUsersExists', 'block_exacsvenrol', $userList);
    } else {
        $msg = get_string('userExists', 'block_exacsvenrol', $userList);
    }

    return $msg;
}
?>

<?php
echo "<form method='post' enctype='multipart/form-data'>
    " . get_string('csvhint', 'block_exacsvenrol') . "
    </label>
    <br/>
    <input name='file' type='file' accept='text/csv'>
    <br/><br/>
    <input type='submit' value='" . get_string('uploadButton', 'block_exacsvenrol') . "'>
</form><br>";

echo '<p>
<b>Hinweise:</b><br>
<i>Laden sie eine csv Datei hoch, um Benutzer in Kurse einzuschreiben und bei Bedarf neu im System anzulegen.<br>
Beispiele für gültige Formate der csv Datei:</i><br><br>
<b>Benutzer anlegen und in Kurs einschreiben:</b><br>
"username","firstname","lastname","email","role","courseid"<br>
"maxmuster","Max","Mustermann","mm@example.com","Student","1"<br>
"maximuster","Maxi","Mustermanni","mmi@example.com","Student","1"<br>
<br>
<b>Benutzer anlegen und in Kurs einschreiben:</b><br>
username,firstname,lastname,email,role,courseshort<br>
maxmuster,Max,Mustermann,mm@example.com,Student,1<br>
maximuster,Maxi,Mustermanni,mmi@example.com,Student,1<br>
<br>
<b>Benutzer ain Kurs einschreiben:</b><br>
username,courseid<br>
maxmuster,1<br>
maximuster,1<br>
<br>
<b>Benutzer ain Kurs einschreiben:</b><br>
"username","courseshort"<br>
"maxmuster","Kurs 12"<br>
"maximuster","Kurs 12"<br>
</p>';
?>


<?php
echo $OUTPUT->footer();
?>

