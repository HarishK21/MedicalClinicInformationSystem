<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link rel="stylesheet" href="styles.css">
   
</head>
<body>

<header>
    <div class="topbar">
        <div class="brand"><span class="brand-title">Medical Clinic Information System</span></div>
        <nav class="navbar"><a href="patient.php" class="active">Patients</a></nav>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <h2>Admin Actions</h2>
        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="drop_patient">
            <button type="submit" class="sidebar-btn danger" onclick="return confirm('Are you sure? This cannot be undone.');">Drop Tables</button>
        </form>
        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="create_patient">
            <button type="submit" class="sidebar-btn primary">Create Tables</button>
        </form>
        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="populate_patient">
            <button type="submit" class="sidebar-btn neutral">Populate Tables</button>
        </form>
        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="run_queries">
            <button type="submit" class="sidebar-btn query">Run Queries</button>
        </form>
    </aside>

    <div class="page-container">
        <h1>Patient Database Records</h1>

        <?php
        require_once 'a9connect.php';
        $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
        $conn = oci_connect($username, $password, $db_conn_str);

        // --- HELPERS ---
        function runSQL($conn, $file) {
            $queries = explode(";", file_get_contents($file));
            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue;
                $stid = oci_parse($conn, $query);
                if (!oci_execute($stid)) {
                    $e = oci_error($stid);
                    echo "<p class='error'>" . htmlentities($e['message']) . "</p>";
                }
            }
            oci_commit($conn);
        }

        function runSQLPopulate($conn, $file) {
            $queries = explode(";", file_get_contents($file));
            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue;
                $query = preg_replace_callback("/'(\d{4}-\d{2}-\d{2})'/", function ($m) { return "TO_DATE('" . $m[1] . "', 'YYYY-MM-DD')"; }, $query);
                $stid = oci_parse($conn, $query);
                if (!oci_execute($stid)) {
                    $e = oci_error($stid);
                    echo "<p class='error'>" . htmlentities($e['message']) . "</p>";
                }
            }
            oci_commit($conn);
        }

        function runQueries($conn, $file) {
            $queries = explode(";", file_get_contents($file));
            $n = 1;
            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue;
                echo "<h2>Query $n Results</h2>";
                $stid = oci_parse($conn, $query);
                if (@oci_execute($stid)) {
                    echo "<table><tr>";
                    $ncols = oci_num_fields($stid);
                    for ($i = 1; $i <= $ncols; $i++) echo "<th>" . htmlspecialchars(oci_field_name($stid, $i)) . "</th>";
                    echo "</tr>";
                    while ($row = oci_fetch_array($stid, OCI_NUM + OCI_RETURN_NULLS)) {
                        echo "<tr>";
                        foreach ($row as $v) echo "<td>" . htmlspecialchars($v) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                $n++;
            }
        }

        // --- HELPER TO RENDER MANAGEMENT FORMS ---
        function renderManageBar($table, $pkName) {
            return '
            <div class="manage-bar">
                <form class="manage-form" method="GET" action="manage_action.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Edit '.$pkName.':</label>
                    <input type="number" name="id" required placeholder="ID...">
                    <button type="submit" class="manage-btn btn-edit">Edit</button>
                </form>
                
                <div style="border-left:1px solid #ccc; height:30px; margin:0 10px;"></div>

                <form class="manage-form" method="POST" action="manage_action.php" onsubmit="return confirm(\'Permanently delete this ID?\');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Delete '.$pkName.':</label>
                    <input type="number" name="id" required placeholder="ID...">
                    <button type="submit" class="manage-btn btn-del">Delete</button>
                </form>
            </div>';
        }
        
        // SPECIAL RENDER FOR MEDICATION INFO (Text PK)
        function renderManageBarText($table, $pkName) {
            return '
            <div class="manage-bar">
                <form class="manage-form" method="GET" action="manage_action.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Edit '.$pkName.':</label>
                    <input type="text" name="id" required placeholder="Name...">
                    <button type="submit" class="manage-btn btn-edit">Edit</button>
                </form>
                <div style="border-left:1px solid #ccc; height:30px; margin:0 10px;"></div>
                <form class="manage-form" method="POST" action="manage_action.php" onsubmit="return confirm(\'Delete this record?\');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Delete '.$pkName.':</label>
                    <input type="text" name="id" required placeholder="Name...">
                    <button type="submit" class="manage-btn btn-del">Delete</button>
                </form>
            </div>';
        }

        // --- MAIN LOGIC ---
        if (!$conn) {
            $e = oci_error();
            echo "<p class='error'>Connection Failed: " . htmlentities($e['message']) . "</p>";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_action'])) {
                $act = $_POST['table_action'];
                if ($act === 'drop_patient') runSQL($conn, 'drop.sql');
                elseif ($act === 'create_patient') runSQL($conn, 'create.sql');
                elseif ($act === 'populate_patient') runSQLPopulate($conn, 'populate.sql');
                elseif ($act === 'run_queries') { runQueries($conn, 'queries.sql'); oci_close($conn); echo "</div></div></body></html>"; exit; }
            }

            echo "<p class='success'>Connected to Oracle.</p>";

            // 1. PATIENT
            echo '<div class="table-header"><h2>Patient List</h2></div>';
            echo renderManageBar('Patient', 'OhipID');
            
            $stid = oci_parse($conn, "SELECT * FROM Patient ORDER BY LastName");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>OHIP ID</th><th>First Name</th><th>Last Name</th><th>DOB</th><th>Sex</th><th>Height</th><th>Weight</th><th>Email</th><th>Phone</th><th>Address</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $dob = $row['DATEOFBIRTH'] ? date('Y-m-d', strtotime($row['DATEOFBIRTH'])) : '';
                    echo "<tr>
                        <td>".htmlspecialchars($row['OHIPID'])."</td>
                        <td>".htmlspecialchars($row['FIRSTNAME'])."</td>
                        <td>".htmlspecialchars($row['LASTNAME'])."</td>
                        <td>".$dob."</td>
                        <td>".htmlspecialchars($row['SEX'])."</td>
                        <td>".htmlspecialchars($row['HEIGHT'])."</td>
                        <td>".htmlspecialchars($row['WEIGHT'])."</td>
                        <td>".htmlspecialchars($row['EMAIL'])."</td>
                        <td>".htmlspecialchars($row['PHONE'])."</td>
                        <td>".htmlspecialchars($row['ADDRESS'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 2. STAFF
            echo '<div class="table-header" style="margin-top:40px;"><h2>Staff List</h2></div>';
            echo renderManageBar('Staff', 'StaffID');

            $stid = oci_parse($conn, "SELECT * FROM Staff ORDER BY LastName");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>Staff ID</th><th>First Name</th><th>Last Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Address</th><th>Status</th><th>Salary</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    echo "<tr>
                        <td>".htmlspecialchars($row['STAFFID'])."</td>
                        <td>".htmlspecialchars($row['FIRSTNAME'])."</td>
                        <td>".htmlspecialchars($row['LASTNAME'])."</td>
                        <td>".htmlspecialchars($row['ROLE'])."</td>
                        <td>".htmlspecialchars($row['EMAIL'])."</td>
                        <td>".htmlspecialchars($row['PHONE'])."</td>
                        <td>".htmlspecialchars($row['ADDRESS'])."</td>
                        <td>".htmlspecialchars($row['EMPLOYMENTSTATUS'])."</td>
                        <td>".htmlspecialchars($row['SALARY'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 3. PRESCRIPTION
            echo '<div class="table-header" style="margin-top:40px;"><h2>Prescriptions</h2></div>';
            echo renderManageBar('Prescription', 'PrescriptionID');

            $stid = oci_parse($conn, "SELECT * FROM Prescription ORDER BY PrescriptionID");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>ID</th><th>OHIP</th><th>Staff</th><th>Medication</th><th>Dose</th><th>Time</th><th>Date</th><th>Type</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $date = $row['DATEISSUED'] ? date('Y-m-d', strtotime($row['DATEISSUED'])) : '';
                    echo "<tr>
                        <td>".htmlspecialchars($row['PRESCRIPTIONID'])."</td>
                        <td>".htmlspecialchars($row['OHIPID'])."</td>
                        <td>".htmlspecialchars($row['STAFFID'])."</td>
                        <td>".htmlspecialchars($row['MEDICATION'])."</td>
                        <td>".htmlspecialchars($row['DOSE'])."</td>
                        <td>".htmlspecialchars($row['TIMEFRAME'])."</td>
                        <td>".$date."</td>
                        <td>".htmlspecialchars($row['MEDICATIONTYPE'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 4. MEDICATION INFO (PK IS TEXT)
            echo '<div class="table-header" style="margin-top:40px;"><h2>Medication Info</h2></div>';
            echo renderManageBarText('MedicationInfo', 'Medication Name');

            $stid = oci_parse($conn, "SELECT * FROM MedicationInfo ORDER BY Medication");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>Medication</th><th>Instructions</th><th>Side Effects</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    echo "<tr>
                        <td>".htmlspecialchars($row['MEDICATION'])."</td>
                        <td>".htmlspecialchars($row['INSTRUCTIONS'])."</td>
                        <td>".htmlspecialchars($row['SIDEEFFECTS'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 5. BILLING
            echo '<div class="table-header" style="margin-top:40px;"><h2>Billing Records</h2></div>';
            echo renderManageBar('Billing', 'BillingID');

            $stid = oci_parse($conn, "SELECT * FROM Billing ORDER BY BillingID");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>ID</th><th>OHIP</th><th>Cov</th><th>Service</th><th>Cost</th><th>Method</th><th>Date</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $date = $row['PAYMENTDATE'] ? date('Y-m-d', strtotime($row['PAYMENTDATE'])) : '';
                    echo "<tr>
                        <td>".htmlspecialchars($row['BILLINGID'])."</td>
                        <td>".htmlspecialchars($row['OHIPID'])."</td>
                        <td>".htmlspecialchars($row['OHIPCOVERAGE'])."</td>
                        <td>".htmlspecialchars($row['SERVICE'])."</td>
                        <td>".htmlspecialchars($row['COST'])."</td>
                        <td>".htmlspecialchars($row['PAYMENTMETHOD'])."</td>
                        <td>".$date."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 6. APPOINTMENT
            echo '<div class="table-header" style="margin-top:40px;"><h2>Appointments</h2></div>';
            echo renderManageBar('Appointment', 'ApptID');

            $stid = oci_parse($conn, "SELECT * FROM Appointment ORDER BY AppointmentID");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>ID</th><th>OHIP</th><th>Staff</th><th>Time</th><th>Status</th><th>Reason</th><th>Result</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    $date = $row['DATEANDTIME'] ? date('Y-m-d H:i', strtotime($row['DATEANDTIME'])) : '';
                    echo "<tr>
                        <td>".htmlspecialchars($row['APPOINTMENTID'])."</td>
                        <td>".htmlspecialchars($row['OHIPID'])."</td>
                        <td>".htmlspecialchars($row['STAFFID'])."</td>
                        <td>".$date."</td>
                        <td>".htmlspecialchars($row['STATUS'])."</td>
                        <td>".htmlspecialchars($row['REASONFORVISIT'])."</td>
                        <td>".htmlspecialchars($row['RESULT'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 7. MEDICAL RECORD
            echo '<div class="table-header" style="margin-top:40px;"><h2>Medical Records</h2></div>';
            echo renderManageBar('MedicalRecord', 'OHIP ID');

            $stid = oci_parse($conn, "SELECT * FROM MedicalRecord ORDER BY OhipID");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>OHIP ID</th><th>Allergies</th><th>Procedures</th><th>Vaccines</th><th>Past Meds</th><th>Family Hx</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    echo "<tr>
                        <td>".htmlspecialchars($row['OHIPID'])."</td>
                        <td>".htmlspecialchars($row['ALLERGIES'])."</td>
                        <td>".htmlspecialchars($row['PROCEDURES'])."</td>
                        <td>".htmlspecialchars($row['VACCINATIONS'])."</td>
                        <td>".htmlspecialchars($row['PASTMEDICATION'])."</td>
                        <td>".htmlspecialchars($row['FAMILYHISTORY'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            // 8. DIAGNOSES
            echo '<div class="table-header" style="margin-top:40px;"><h2>Diagnoses</h2></div>';
            echo renderManageBar('Diagnoses', 'DiagnosisID');

            $stid = oci_parse($conn, "SELECT * FROM Diagnoses ORDER BY DiagnosisID");
            if (@oci_execute($stid)) {
                echo "<table><tr><th>ID</th><th>OHIP</th><th>Diagnosis</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    echo "<tr>
                        <td>".htmlspecialchars($row['DIAGNOSISID'])."</td>
                        <td>".htmlspecialchars($row['OHIPID'])."</td>
                        <td>".htmlspecialchars($row['DIAGNOSIS'])."</td>
                    </tr>";
                }
                echo "</table>";
            }

            oci_close($conn);
        }
        ?>
    </div>
</div>
</body>
</html>