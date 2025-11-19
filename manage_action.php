<?php
require_once 'a9connect.php';
$db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
$conn = oci_connect($username, $password, $db_conn_str);

if (!$conn) {
    die("Database Connection Failed.");
}

// ===============================================================
// 1. DELETE LOGIC
// ===============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $table = $_POST['table'];
    $id = $_POST['id'];

    // Set Primary Key based on Table Name (No arrays used)
    $pk = "";
    if ($table == 'Patient') { $pk = 'OhipID'; }
    elseif ($table == 'Staff') { $pk = 'StaffID'; }
    elseif ($table == 'Prescription') { $pk = 'PrescriptionID'; }
    elseif ($table == 'MedicationInfo') { $pk = 'Medication'; }
    elseif ($table == 'Billing') { $pk = 'BillingID'; }
    elseif ($table == 'Appointment') { $pk = 'AppointmentID'; }
    elseif ($table == 'MedicalRecord') { $pk = 'OhipID'; }
    elseif ($table == 'Diagnoses') { $pk = 'DiagnosisID'; }

    $sql = "DELETE FROM $table WHERE $pk = '$id'";
    
    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    oci_commit($conn);
    header("Location: patient.php");
    exit;
    
}

// ===============================================================
// 2. UPDATE LOGIC
// ===============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $table = $_POST['table'];
    $id = $_POST['id'];
    $p = $_POST; 

    if ($table === 'Patient') {
        $sql = "UPDATE Patient SET 
                FirstName='{$p['firstname']}', LastName='{$p['lastname']}', 
                DateOfBirth=TO_DATE('{$p['dob']}', 'YYYY-MM-DD'), Sex='{$p['sex']}', 
                Height='{$p['height']}', Weight='{$p['weight']}', 
                Email='{$p['email']}', Phone='{$p['phone']}', Address='{$p['address']}' 
                WHERE OhipID='$id'";

    } elseif ($table === 'Staff') {
        $sql = "UPDATE Staff SET 
                FirstName='{$p['firstname']}', LastName='{$p['lastname']}', Role='{$p['role']}', 
                Email='{$p['email']}', Phone='{$p['phone']}', Address='{$p['address']}', 
                EmploymentStatus='{$p['status']}', Salary='{$p['salary']}' 
                WHERE StaffID='$id'";

    } elseif ($table === 'Prescription') {
        $sql = "UPDATE Prescription SET 
                OhipID='{$p['ohipid']}', StaffID='{$p['staffid']}', Medication='{$p['medication']}', 
                Dose='{$p['dose']}', Timeframe='{$p['timeframe']}', 
                DateIssued=TO_DATE('{$p['dateissued']}', 'YYYY-MM-DD'), MedicationType='{$p['type']}' 
                WHERE PrescriptionID='$id'";

    } elseif ($table === 'MedicationInfo') {
        $sql = "UPDATE MedicationInfo SET 
                Instructions='{$p['instructions']}', SideEffects='{$p['sideeffects']}' 
                WHERE Medication='$id'";

    } elseif ($table === 'Billing') {
        $sql = "UPDATE Billing SET 
                OhipID='{$p['ohipid']}', OhipCoverage='{$p['coverage']}', Service='{$p['service']}', 
                Cost='{$p['cost']}', PaymentMethod='{$p['method']}', 
                PaymentDate=TO_DATE('{$p['date']}', 'YYYY-MM-DD') 
                WHERE BillingID='$id'";

    } elseif ($table === 'Appointment') {
        $dt = str_replace("T", " ", $p['datetime']); 
        $sql = "UPDATE Appointment SET 
                OhipID='{$p['ohipid']}', StaffID='{$p['staffid']}', 
                DateAndTime=TO_DATE('$dt', 'YYYY-MM-DD HH24:MI'), 
                Status='{$p['status']}', ReasonForVisit='{$p['reason']}', Result='{$p['result']}' 
                WHERE AppointmentID='$id'";

    } elseif ($table === 'MedicalRecord') {
        $sql = "UPDATE MedicalRecord SET 
                Allergies='{$p['allergies']}', Procedures='{$p['procedures']}', 
                Vaccinations='{$p['vaccinations']}', PastMedication='{$p['pastmed']}', 
                FamilyHistory='{$p['family']}' 
                WHERE OhipID='$id'";

    } elseif ($table === 'Diagnoses') {
        $sql = "UPDATE Diagnoses SET 
                OhipID='{$p['ohipid']}', Diagnosis='{$p['diagnosis']}' 
                WHERE DiagnosisID='$id'";
    }

    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    oci_commit($conn);
    header("Location: patient.php");
    exit;
}

// ===============================================================
// 3. EDIT FORM LOGIC (FETCH DATA)
// ===============================================================
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $table = $_GET['table'];
    $id = $_GET['id'];
    
    // Set Primary Key based on Table Name (No arrays used)
    $pk = "";
    if ($table == 'Patient') { $pk = 'OhipID'; }
    elseif ($table == 'Staff') { $pk = 'StaffID'; }
    elseif ($table == 'Prescription') { $pk = 'PrescriptionID'; }
    elseif ($table == 'MedicationInfo') { $pk = 'Medication'; }
    elseif ($table == 'Billing') { $pk = 'BillingID'; }
    elseif ($table == 'Appointment') { $pk = 'AppointmentID'; }
    elseif ($table == 'MedicalRecord') { $pk = 'OhipID'; }
    elseif ($table == 'Diagnoses') { $pk = 'DiagnosisID'; }

    $sql = "SELECT * FROM $table WHERE $pk = '$id'";
    $stid = oci_parse($conn, $sql);
    oci_execute($stid);
    $row = oci_fetch_array($stid, OCI_ASSOC);

    if (!$row) die("Record not found. <a href='patient.php'>Back</a>");

    echo '<link rel="stylesheet" href="styles.css">';
    echo '<div class="page-container" style="max-width:600px; margin:50px auto;">';
    echo "<h1>Edit $table (ID: $id)</h1>";
    echo "<form method='POST' action='manage_action.php'>";
    echo "<input type='hidden' name='action' value='update'>";
    echo "<input type='hidden' name='table' value='$table'>";
    echo "<input type='hidden' name='id' value='$id'>";

    if ($table === 'Patient') {
        $dob = "";
        if ($row['DATEOFBIRTH'] != null) {
            $dob = date('Y-m-d', strtotime($row['DATEOFBIRTH']));
        }

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
        $dt = "";
        if ($row['DATEISSUED'] != null) {
            $dt = date('Y-m-d', strtotime($row['DATEISSUED']));
        }

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
        $dt = "";
        if ($row['PAYMENTDATE'] != null) {
            $dt = date('Y-m-d', strtotime($row['PAYMENTDATE']));
        }

        echo "OHIP ID: <input type='number' name='ohipid' value='{$row['OHIPID']}' required><br>";
        echo "Coverage (Y/N): <input type='text' name='coverage' value='{$row['OHIPCOVERAGE']}'><br>";
        echo "Service: <input type='text' name='service' value='{$row['SERVICE']}' required><br>";
        echo "Cost: <input type='number' name='cost' value='{$row['COST']}' required><br>";
        echo "Method: <input type='text' name='method' value='{$row['PAYMENTMETHOD']}'><br>";
        echo "Date: <input type='date' name='date' value='$dt'><br>";
    }
    elseif ($table === 'Appointment') {
        $dt = "";
        if ($row['DATEANDTIME'] != null) {
            $dt = date('Y-m-d\TH:i', strtotime($row['DATEANDTIME']));
        }

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