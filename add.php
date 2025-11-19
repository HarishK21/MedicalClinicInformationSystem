<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Record</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="topbar">
        <div class="brand">
            <span class="brand-title">Medical Clinic Information System</span>
        </div>

        <nav class="navbar">
            <a href="patient.php" class="active">Patients</a>
        </nav>
    </div>
</header>

<div class="layout">
    <div class="page-container">
        <h1>Add Record</h1>

        <?php
        require_once 'a9connect.php';
        $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
        $conn = oci_connect($username, $password, $db_conn_str);

        function runStatement($conn, $sql) {
            $stid = oci_parse($conn, $sql);
            if (!$stid) {
                return oci_error($conn);
            }
            $r = oci_execute($stid, OCI_COMMIT_ON_SUCCESS);
            if (!$r) {
                return oci_error($stid);
            }
            return null;
        }

        // basic escaping for single quotes in text fields
        function esc($s) {
            return str_replace("'", "''", $s);
        }

        if (!$conn) {
            $e = oci_error();
            echo "<p class='error'>Database Connection Failed: " . htmlentities($e['message']) . "</p>";
        } else {

            // Determine table name from GET or POST
            $table = null;
            if (isset($_GET['table'])) {
                $table = $_GET['table'];
            } elseif (isset($_POST['table_name'])) {
                $table = $_POST['table_name'];
            }

            if ($table === null) {
                echo "<p class='error'>No table specified.</p>";
                echo '<p><a href="patient.php" class="sidebar-btn neutral">Back to Records</a></p>';
                oci_close($conn);
                echo "</div></div></body></html>";
                exit;
            }

            $adminMessage = '';

            // Handle POST insert
            if ($_SERVER['REQUEST_METHOD'] === 'POST'
                && isset($_POST['record_action'])
                && $_POST['record_action'] === 'add_record') {

                if ($table === 'patient') {

                    $sql = "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address) VALUES (
                        " . $_POST['ohip_id'] . ",
                        '" . esc($_POST['first_name']) . "',
                        '" . esc($_POST['last_name']) . "',
                        DATE '" . $_POST['dob'] . "',
                        '" . esc($_POST['sex']) . "',
                        " . ($_POST['height'] === '' ? 'NULL' : $_POST['height']) . ",
                        " . ($_POST['weight'] === '' ? 'NULL' : $_POST['weight']) . ",
                        '" . esc($_POST['email']) . "',
                        '" . esc($_POST['phone']) . "',
                        '" . esc($_POST['address']) . "'
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Patient record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Patient record added successfully.</p>";

                } elseif ($table === 'staff') {

                    $sql = "INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary) VALUES (
                        " . $_POST['staff_id'] . ",
                        '" . esc($_POST['first_name']) . "',
                        '" . esc($_POST['last_name']) . "',
                        '" . esc($_POST['role']) . "',
                        '" . esc($_POST['email']) . "',
                        '" . esc($_POST['phone']) . "',
                        '" . esc($_POST['address']) . "',
                        '" . esc($_POST['employment_status']) . "',
                        " . ($_POST['salary'] === '' ? 'NULL' : $_POST['salary']) . "
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Staff record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Staff record added successfully.</p>";

                } elseif ($table === 'prescription') {

                    $dateIssued = $_POST['date_issued'];
                    $dateSql = $dateIssued === '' ? 'NULL' : "DATE '" . $dateIssued . "'";

                    $sql = "INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, MedicationType) VALUES (
                        " . $_POST['prescription_id'] . ",
                        " . $_POST['ohip_id'] . ",
                        " . $_POST['staff_id'] . ",
                        '" . esc($_POST['medication']) . "',
                        '" . esc($_POST['dose']) . "',
                        '" . esc($_POST['timeframe']) . "',
                        " . $dateSql . ",
                        '" . esc($_POST['medication_type']) . "'
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Prescription record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Prescription record added successfully.</p>";

                } elseif ($table === 'medicationinfo') {

                    $sql = "INSERT INTO MedicationInfo (Medication, Instructions, SideEffects) VALUES (
                        '" . esc($_POST['medication']) . "',
                        '" . esc($_POST['instructions']) . "',
                        '" . esc($_POST['side_effects']) . "'
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Medication Info record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Medication Info record added successfully.</p>";

                } elseif ($table === 'billing') {

                    $paymentDate = $_POST['payment_date'];
                    $payDateSql = $paymentDate === '' ? 'NULL' : "DATE '" . $paymentDate . "'";

                    $sql = "INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate) VALUES (
                        " . $_POST['billing_id'] . ",
                        " . $_POST['ohip_id'] . ",
                        '" . esc($_POST['ohip_coverage']) . "',
                        '" . esc($_POST['service']) . "',
                        " . ($_POST['cost'] === '' ? 'NULL' : $_POST['cost']) . ",
                        '" . esc($_POST['payment_method']) . "',
                        " . $payDateSql . "
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Billing record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Billing record added successfully.</p>";

                } elseif ($table === 'appointment') {

                    // datetime-local returns "YYYY-MM-DDTHH:MM"
                    $dt = $_POST['date_time'];
                    $dt_sql = $dt === '' ? null : str_replace('T', ' ', $dt);
                    $dtExpr = $dt_sql === null
                        ? 'NULL'
                        : "TO_DATE('" . $dt_sql . "', 'YYYY-MM-DD HH24:MI')";

                    $sql = "INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result) VALUES (
                        " . $_POST['appointment_id'] . ",
                        " . $_POST['ohip_id'] . ",
                        " . $_POST['staff_id'] . ",
                        " . $dtExpr . ",
                        '" . esc($_POST['status']) . "',
                        '" . esc($_POST['reason_for_visit']) . "',
                        '" . esc($_POST['result']) . "'
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Appointment record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Appointment record added successfully.</p>";

                } elseif ($table === 'medicalrecord') {

                    $sql = "INSERT INTO MedicalRecord (OhipID, Allergies, Procedures, Vaccinations, PastMedication, FamilyHistory) VALUES (
                        " . $_POST['ohip_id'] . ",
                        '" . esc($_POST['allergies']) . "',
                        '" . esc($_POST['procedures']) . "',
                        '" . esc($_POST['vaccinations']) . "',
                        '" . esc($_POST['past_medication']) . "',
                        '" . esc($_POST['family_history']) . "'
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Medical Record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Medical Record added successfully.</p>";

                } elseif ($table === 'diagnoses') {

                    $sql = "INSERT INTO Diagnoses (DiagnosisID, OhipID, Diagnosis) VALUES (
                        " . $_POST['diagnosis_id'] . ",
                        " . $_POST['ohip_id'] . ",
                        '" . esc($_POST['diagnosis']) . "'
                    )";

                    $err = runStatement($conn, $sql);
                    $adminMessage = $err
                        ? "<p class='error'>Error adding Diagnosis record: " . htmlentities($err['message']) . "</p>"
                        : "<p class='success'>Diagnosis record added successfully.</p>";

                }
            }

            echo $adminMessage;


            /* ---------- FORMS FOR EACH TABLE ---------- */

            if ($table === 'patient') {
                ?>
                <h2>Add Patient Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="patient">

                    <div><label>OHIP ID:</label><input type="number" name="ohip_id" required></div>
                    <div><label>First Name:</label><input type="text" name="first_name" required></div>
                    <div><label>Last Name:</label><input type="text" name="last_name" required></div>
                    <div><label>Date of Birth:</label><input type="date" name="dob" required></div>
                    <div><label>Sex:</label>
                        <select name="sex" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div><label>Height (cm):</label><input type="number" name="height"></div>
                    <div><label>Weight (kg):</label><input type="number" name="weight"></div>
                    <div><label>Email:</label><input type="email" name="email"></div>
                    <div><label>Phone:</label><input type="text" name="phone"></div>
                    <div><label>Address:</label><input type="text" name="address"></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'staff') {
                ?>
                <h2>Add Staff Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="staff">

                    <div><label>Staff ID:</label><input type="number" name="staff_id" required></div>
                    <div><label>First Name:</label><input type="text" name="first_name" required></div>
                    <div><label>Last Name:</label><input type="text" name="last_name" required></div>
                    <div><label>Role:</label><input type="text" name="role" required></div>
                    <div><label>Email:</label><input type="email" name="email"></div>
                    <div><label>Phone:</label><input type="text" name="phone"></div>
                    <div><label>Address:</label><input type="text" name="address"></div>
                    <div>
                        <label>Employment Status:</label>
                        <select name="employment_status" required>
                            <option value="Active">Active</option>
                            <option value="Absence">Absence</option>
                            <option value="Retired">Retired</option>
                        </select>
                    </div>
                    <div><label>Salary:</label><input type="number" name="salary"></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'prescription') {
                ?>
                <h2>Add Prescription Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="prescription">

                    <div><label>Prescription ID:</label><input type="number" name="prescription_id" required></div>
                    <div><label>OHIP ID:</label><input type="number" name="ohip_id" required></div>
                    <div><label>Staff ID:</label><input type="number" name="staff_id" required></div>
                    <div><label>Medication:</label><input type="text" name="medication" required></div>
                    <div><label>Dose:</label><input type="text" name="dose"></div>
                    <div><label>Timeframe:</label><input type="text" name="timeframe"></div>
                    <div><label>Date Issued:</label><input type="date" name="date_issued"></div>
                    <div><label>Medication Type:</label><input type="text" name="medication_type"></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'medicationinfo') {
                ?>
                <h2>Add Medication Info Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="medicationinfo">

                    <div><label>Medication:</label><input type="text" name="medication" required></div>
                    <div><label>Instructions:</label><textarea name="instructions"></textarea></div>
                    <div><label>Side Effects:</label><textarea name="side_effects"></textarea></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'billing') {
                ?>
                <h2>Add Billing Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="billing">

                    <div><label>Billing ID:</label><input type="number" name="billing_id" required></div>
                    <div><label>OHIP ID:</label><input type="number" name="ohip_id" required></div>
                    <div><label>OHIP Coverage:</label><input type="text" name="ohip_coverage"></div>
                    <div><label>Service:</label><input type="text" name="service"></div>
                    <div><label>Cost:</label><input type="number" step="0.01" name="cost"></div>
                    <div><label>Payment Method:</label><input type="text" name="payment_method"></div>
                    <div><label>Payment Date:</label><input type="date" name="payment_date"></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'appointment') {
                ?>
                <h2>Add Appointment Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="appointment">

                    <div><label>Appointment ID:</label><input type="number" name="appointment_id" required></div>
                    <div><label>OHIP ID:</label><input type="number" name="ohip_id" required></div>
                    <div><label>Staff ID:</label><input type="number" name="staff_id" required></div>
                    <div><label>Date & Time:</label><input type="datetime-local" name="date_time"></div>
                    <div><label>Status:</label><input type="text" name="status"></div>
                    <div><label>Reason for Visit:</label><textarea name="reason_for_visit"></textarea></div>
                    <div><label>Result:</label><textarea name="result"></textarea></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'medicalrecord') {
                ?>
                <h2>Add Medical Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="medicalrecord">

                    <div><label>OHIP ID:</label><input type="number" name="ohip_id" required></div>
                    <div><label>Allergies:</label><textarea name="allergies"></textarea></div>
                    <div><label>Procedures:</label><textarea name="procedures"></textarea></div>
                    <div><label>Vaccinations:</label><textarea name="vaccinations"></textarea></div>
                    <div><label>Past Medication:</label><textarea name="past_medication"></textarea></div>
                    <div><label>Family History:</label><textarea name="family_history"></textarea></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } elseif ($table === 'diagnoses') {
                ?>
                <h2>Add Diagnosis Record</h2>
                <form method="post" action="add.php" class="crud-form">
                    <input type="hidden" name="record_action" value="add_record">
                    <input type="hidden" name="table_name" value="diagnoses">

                    <div><label>Diagnosis ID:</label><input type="number" name="diagnosis_id" required></div>
                    <div><label>OHIP ID:</label><input type="number" name="ohip_id" required></div>
                    <div><label>Diagnosis:</label><textarea name="diagnosis"></textarea></div>

                    <div class="form-actions">
                        <button type="submit" class="sidebar-btn primary">Add Record</button>
                        <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                    </div>
                </form>
                <?php

            } else {
                echo "<p class='error'>Unsupported table: " . htmlspecialchars($table) . "</p>";
                echo '<p><a href="patient.php" class="sidebar-btn neutral">Back to Records</a></p>';
            }

            oci_close($conn);
        }
        ?>
    </div>
</div>

</body>
</html>
