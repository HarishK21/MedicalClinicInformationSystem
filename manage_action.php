<?php
require_once 'a9connect.php';
$db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
$conn = oci_connect($username, $password, $db_conn_str);

if (!$conn) {
    die("Database Connection Failed.");
}

// ----- DELETE LOGIC (POST) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $table = $_POST['table'];
    $id = $_POST['id'];
    
    $pk = '';
    if ($table === 'Patient') $pk = 'OhipID';
    elseif ($table === 'Staff') $pk = 'StaffID';
    elseif ($table === 'Prescription') $pk = 'PrescriptionID';
    elseif ($table === 'MedicationInfo') $pk = 'Medication';
    elseif ($table === 'Billing') $pk = 'BillingID';
    elseif ($table === 'Appointment') $pk = 'AppointmentID';
    elseif ($table === 'MedicalRecord') $pk = 'OhipID';
    elseif ($table === 'Diagnoses') $pk = 'DiagnosisID';

    $sql = "DELETE FROM $table WHERE $pk = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    
    if (oci_execute($stid)) {
        oci_commit($conn);
        header("Location: patient.php");
    } else {
        $e = oci_error($stid);
        echo "Error deleting: " . htmlentities($e['message']);
        echo "<br><a href='patient.php'>Back to List</a>";
    }
    exit;
}

// ----- UPDATE LOGIC (POST) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $table = $_POST['table'];
    $id = $_POST['id']; // Original ID
    
    // Prepare SQL based on Table
    if ($table === 'Patient') {
        $sql = "UPDATE Patient SET FirstName=:fn, LastName=:ln, DateOfBirth=TO_DATE(:dob, 'YYYY-MM-DD'), Sex=:sex, Height=:h, Weight=:w, Email=:em, Phone=:ph, Address=:addr WHERE OhipID=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":fn", $_POST['firstname']);
        oci_bind_by_name($stid, ":ln", $_POST['lastname']);
        oci_bind_by_name($stid, ":dob", $_POST['dob']);
        oci_bind_by_name($stid, ":sex", $_POST['sex']);
        oci_bind_by_name($stid, ":h", $_POST['height']);
        oci_bind_by_name($stid, ":w", $_POST['weight']);
        oci_bind_by_name($stid, ":em", $_POST['email']);
        oci_bind_by_name($stid, ":ph", $_POST['phone']);
        oci_bind_by_name($stid, ":addr", $_POST['address']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'Staff') {
        $sql = "UPDATE Staff SET FirstName=:fn, LastName=:ln, Role=:rl, Email=:em, Phone=:ph, Address=:addr, EmploymentStatus=:es, Salary=:sal WHERE StaffID=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":fn", $_POST['firstname']);
        oci_bind_by_name($stid, ":ln", $_POST['lastname']);
        oci_bind_by_name($stid, ":rl", $_POST['role']);
        oci_bind_by_name($stid, ":em", $_POST['email']);
        oci_bind_by_name($stid, ":ph", $_POST['phone']);
        oci_bind_by_name($stid, ":addr", $_POST['address']);
        oci_bind_by_name($stid, ":es", $_POST['status']);
        oci_bind_by_name($stid, ":sal", $_POST['salary']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'Prescription') {
        $sql = "UPDATE Prescription SET OhipID=:oid, StaffID=:sid, Medication=:med, Dose=:ds, Timeframe=:tf, DateIssued=TO_DATE(:dt, 'YYYY-MM-DD'), MedicationType=:mt WHERE PrescriptionID=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":oid", $_POST['ohipid']);
        oci_bind_by_name($stid, ":sid", $_POST['staffid']);
        oci_bind_by_name($stid, ":med", $_POST['medication']);
        oci_bind_by_name($stid, ":ds", $_POST['dose']);
        oci_bind_by_name($stid, ":tf", $_POST['timeframe']);
        oci_bind_by_name($stid, ":dt", $_POST['dateissued']);
        oci_bind_by_name($stid, ":mt", $_POST['type']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'MedicationInfo') {
        $sql = "UPDATE MedicationInfo SET Instructions=:ins, SideEffects=:se WHERE Medication=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":ins", $_POST['instructions']);
        oci_bind_by_name($stid, ":se", $_POST['sideeffects']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'Billing') {
        $sql = "UPDATE Billing SET OhipID=:oid, OhipCoverage=:cov, Service=:svc, Cost=:cst, PaymentMethod=:pm, PaymentDate=TO_DATE(:pd, 'YYYY-MM-DD') WHERE BillingID=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":oid", $_POST['ohipid']);
        oci_bind_by_name($stid, ":cov", $_POST['coverage']);
        oci_bind_by_name($stid, ":svc", $_POST['service']);
        oci_bind_by_name($stid, ":cst", $_POST['cost']);
        oci_bind_by_name($stid, ":pm", $_POST['method']);
        oci_bind_by_name($stid, ":pd", $_POST['date']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'Appointment') {
        // Handles date+time
        $sql = "UPDATE Appointment SET OhipID=:oid, StaffID=:sid, DateAndTime=TO_DATE(:dt, 'YYYY-MM-DD HH24:MI'), Status=:st, ReasonForVisit=:rsn, Result=:res WHERE AppointmentID=:id";
        $stid = oci_parse($conn, $sql);
        $dateTimeStr = str_replace("T", " ", $_POST['datetime']); // fix HTML5 datetime-local
        oci_bind_by_name($stid, ":oid", $_POST['ohipid']);
        oci_bind_by_name($stid, ":sid", $_POST['staffid']);
        oci_bind_by_name($stid, ":dt", $dateTimeStr);
        oci_bind_by_name($stid, ":st", $_POST['status']);
        oci_bind_by_name($stid, ":rsn", $_POST['reason']);
        oci_bind_by_name($stid, ":res", $_POST['result']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'MedicalRecord') {
        $sql = "UPDATE MedicalRecord SET Allergies=:alg, Procedures=:proc, Vaccinations=:vac, PastMedication=:pm, FamilyHistory=:fh WHERE OhipID=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":alg", $_POST['allergies']);
        oci_bind_by_name($stid, ":proc", $_POST['procedures']);
        oci_bind_by_name($stid, ":vac", $_POST['vaccinations']);
        oci_bind_by_name($stid, ":pm", $_POST['pastmed']);
        oci_bind_by_name($stid, ":fh", $_POST['family']);
        oci_bind_by_name($stid, ":id", $id);

    } elseif ($table === 'Diagnoses') {
        $sql = "UPDATE Diagnoses SET OhipID=:oid, Diagnosis=:diag WHERE DiagnosisID=:id";
        $stid = oci_parse($conn, $sql);
        oci_bind_by_name($stid, ":oid", $_POST['ohipid']);
        oci_bind_by_name($stid, ":diag", $_POST['diagnosis']);
        oci_bind_by_name($stid, ":id", $id);
    }

    if (oci_execute($stid)) {
        oci_commit($conn);
        header("Location: patient.php");
    } else {
        $e = oci_error($stid);
        echo "Error updating: " . htmlentities($e['message']);
        echo "<br><a href='patient.php'>Back</a>";
    }
    exit;
}

// ----- DISPLAY EDIT FORM (GET) -----
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $table = $_GET['table'];
    $id = $_GET['id'];
    
    if (!$id) die("Invalid ID provided. <a href='patient.php'>Back</a>");

    $pk = '';
    if ($table === 'Patient') $pk = 'OhipID';
    elseif ($table === 'Staff') $pk = 'StaffID';
    elseif ($table === 'Prescription') $pk = 'PrescriptionID';
    elseif ($table === 'MedicationInfo') $pk = 'Medication';
    elseif ($table === 'Billing') $pk = 'BillingID';
    elseif ($table === 'Appointment') $pk = 'AppointmentID';
    elseif ($table === 'MedicalRecord') $pk = 'OhipID';
    elseif ($table === 'Diagnoses') $pk = 'DiagnosisID';

    $sql = "SELECT * FROM $table WHERE $pk = :id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":id", $id);
    oci_execute($stid);
    $row = oci_fetch_array($stid, OCI_ASSOC);

    if (!$row) die("Record with ID '$id' not found in $table. <a href='patient.php'>Back</a>");

    // RENDER FORM
    echo '<link rel="stylesheet" href="styles.css">';
    echo '<div class="page-container" style="max-width:600px; margin:50px auto;">';
    echo "<h1>Edit $table (ID: $id)</h1>";
    echo "<form method='POST' action='manage_action.php'>";
    echo "<input type='hidden' name='action' value='update'>";
    echo "<input type='hidden' name='table' value='$table'>";
    echo "<input type='hidden' name='id' value='$id'>";

    if ($table === 'Patient') {
        $dob = $row['DATEOFBIRTH'] ? date('Y-m-d', strtotime($row['DATEOFBIRTH'])) : '';
        echo "First Name: <input type='text' name='firstname' value='{$row['FIRSTNAME']}' required><br>";
        echo "Last Name: <input type='text' name='lastname' value='{$row['LASTNAME']}' required><br>";
        echo "DOB: <input type='date' name='dob' value='$dob' required><br>";
        echo "Sex: <input type='text' name='sex' value='{$row['SEX']}' required><br>";
        echo "Height: <input type='number' name='height' value='{$row['HEIGHT']}'><br>";
        echo "Weight: <input type='number' name='weight' value='{$row['WEIGHT']}'><br>";
        echo "Email: <input type='email' name='email' value='{$row['EMAIL']}'><br>";
        echo "Phone: <input type='text' name='phone' value='{$row['PHONE']}'><br>";
        echo "Address: <input type='text' name='address' value='{$row['ADDRESS']}'><br>";
    }
    elseif ($table === 'Staff') {
        echo "First Name: <input type='text' name='firstname' value='{$row['FIRSTNAME']}' required><br>";
        echo "Last Name: <input type='text' name='lastname' value='{$row['LASTNAME']}' required><br>";
        echo "Role: <input type='text' name='role' value='{$row['ROLE']}' required><br>";
        echo "Email: <input type='email' name='email' value='{$row['EMAIL']}'><br>";
        echo "Phone: <input type='text' name='phone' value='{$row['PHONE']}'><br>";
        echo "Address: <input type='text' name='address' value='{$row['ADDRESS']}'><br>";
        echo "Status: <input type='text' name='status' value='{$row['EMPLOYMENTSTATUS']}'><br>";
        echo "Salary: <input type='number' name='salary' value='{$row['SALARY']}'><br>";
    }
    elseif ($table === 'Prescription') {
        $dt = $row['DATEISSUED'] ? date('Y-m-d', strtotime($row['DATEISSUED'])) : '';
        echo "OHIP ID: <input type='number' name='ohipid' value='{$row['OHIPID']}' required><br>";
        echo "Staff ID: <input type='number' name='staffid' value='{$row['STAFFID']}' required><br>";
        echo "Medication: <input type='text' name='medication' value='{$row['MEDICATION']}' required><br>";
        echo "Dose: <input type='text' name='dose' value='{$row['DOSE']}'><br>";
        echo "Timeframe: <input type='text' name='timeframe' value='{$row['TIMEFRAME']}'><br>";
        echo "Date: <input type='date' name='dateissued' value='$dt' required><br>";
        echo "Type: <input type='text' name='type' value='{$row['MEDICATIONTYPE']}'><br>";
    }
    elseif ($table === 'MedicationInfo') {
        echo "Instructions: <input type='text' name='instructions' value='{$row['INSTRUCTIONS']}' required><br>";
        echo "Side Effects: <input type='text' name='sideeffects' value='{$row['SIDEEFFECTS']}'><br>";
    }
    elseif ($table === 'Billing') {
        $dt = $row['PAYMENTDATE'] ? date('Y-m-d', strtotime($row['PAYMENTDATE'])) : '';
        echo "OHIP ID: <input type='number' name='ohipid' value='{$row['OHIPID']}' required><br>";
        echo "Coverage (Y/N): <input type='text' name='coverage' value='{$row['OHIPCOVERAGE']}'><br>";
        echo "Service: <input type='text' name='service' value='{$row['SERVICE']}' required><br>";
        echo "Cost: <input type='number' name='cost' value='{$row['COST']}' required><br>";
        echo "Method: <input type='text' name='method' value='{$row['PAYMENTMETHOD']}'><br>";
        echo "Date: <input type='date' name='date' value='$dt'><br>";
    }
    elseif ($table === 'Appointment') {
        $dt = $row['DATEANDTIME'] ? date('Y-m-d\TH:i', strtotime($row['DATEANDTIME'])) : '';
        echo "OHIP ID: <input type='number' name='ohipid' value='{$row['OHIPID']}' required><br>";
        echo "Staff ID: <input type='number' name='staffid' value='{$row['STAFFID']}' required><br>";
        echo "Date/Time: <input type='datetime-local' name='datetime' value='$dt' required><br>";
        echo "Status: <input type='text' name='status' value='{$row['STATUS']}'><br>";
        echo "Reason: <input type='text' name='reason' value='{$row['REASONFORVISIT']}'><br>";
        echo "Result: <input type='text' name='result' value='{$row['RESULT']}'><br>";
    }
    elseif ($table === 'MedicalRecord') {
        echo "Allergies: <input type='text' name='allergies' value='{$row['ALLERGIES']}'><br>";
        echo "Procedures: <input type='text' name='procedures' value='{$row['PROCEDURES']}'><br>";
        echo "Vaccinations: <input type='text' name='vaccinations' value='{$row['VACCINATIONS']}'><br>";
        echo "Past Meds: <input type='text' name='pastmed' value='{$row['PASTMEDICATION']}'><br>";
        echo "Family Hx: <input type='text' name='family' value='{$row['FAMILYHISTORY']}'><br>";
    }
    elseif ($table === 'Diagnoses') {
        echo "OHIP ID: <input type='number' name='ohipid' value='{$row['OHIPID']}' required><br>";
        echo "Diagnosis: <input type='text' name='diagnosis' value='{$row['DIAGNOSIS']}'><br>";
    }

    echo "<br><button type='submit' class='sidebar-btn primary'>Save Changes</button>";
    echo " <a href='patient.php' class='sidebar-btn neutral' style='text-decoration:none; display:inline-block; width:auto;'>Cancel</a>";
    echo "</form></div>";
}
?>