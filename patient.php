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
            <button type="submit" class="sidebar-btn danger"
                    onclick="return confirm('Are you sure?');">
                Drop Tables
            </button>
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
        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="exit">
            <button type="submit" class="sidebar-btn danger">Exit</button>
        </form>
    </aside>

    <div class="page-container">
        <h1>Patient Database Records</h1>

        <?php
        require_once 'a9connect.php';
        $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
        $conn = oci_connect($username, $password, $db_conn_str);

        /* ---------- HELPER FUNCTIONS ---------- */

        // Run .sql file with generic statements
        function runSQL($conn, $file) {
            $sql = file_get_contents($file);
            $queries = explode(";", $sql);
            foreach ($queries as $q) {
                $q = trim($q);
                if ($q === "") continue;
                $stid = oci_parse($conn, $q);
                oci_execute($stid);
            }
            oci_commit($conn);
        }

        // Run .sql file and convert 'YYYY-MM-DD' to TO_DATE(...)
        function runSQLPopulate($conn, $file) {
            $sql = file_get_contents($file);
            $queries = explode(";", $sql);
            foreach ($queries as $q) {
                $q = trim($q);
                if ($q === "") continue;
                $q = preg_replace_callback(
                    "/'(\d{4}-\d{2}-\d{2})'/",
                    function ($m) {
                        return "TO_DATE('" . $m[1] . "', 'YYYY-MM-DD')";
                    },
                    $q
                );
                $stid = oci_parse($conn, $q);
                oci_execute($stid);
            }
            oci_commit($conn);
        }

        // Run all SELECTs in queries.sql for assignment part
        function runQueries($conn, $file) {
            $sql = file_get_contents($file);
            $queries = explode(";", $sql);
            $n = 1;
            foreach ($queries as $q) {
                $q = trim($q);
                if ($q === "") continue;
                echo "<h2>Query $n Results</h2>";
                $stid = oci_parse($conn, $q);
                if (oci_execute($stid)) {
                    echo "<table><tr>";
                    $cols = oci_num_fields($stid);
                    for ($i = 1; $i <= $cols; $i++) {
                        echo "<th>" . htmlspecialchars(oci_field_name($stid, $i)) . "</th>";
                    }
                    echo "</tr>";
                    while ($row = oci_fetch_array($stid, OCI_NUM + OCI_RETURN_NULLS)) {
                        echo "<tr>";
                        foreach ($row as $v) {
                            echo "<td>" . htmlspecialchars($v) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                $n++;
            }
        }

        // Function to create table
        function createTable($conn, $sql) {
            $stid = oci_parse($conn, $sql);
            oci_execute($stid);

            echo "<table><tr>";
            $cols = oci_num_fields($stid);
            for ($i = 1; $i <= $cols; $i++) {
                echo "<th>" . htmlspecialchars(oci_field_name($stid, $i)) . "</th>";
            }
            echo "</tr>";

            while ($row = oci_fetch_array($stid, OCI_NUM + OCI_RETURN_NULLS)) {
                echo "<tr>";
                foreach ($row as $v) {
                    echo "<td>" . htmlspecialchars($v) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }

        // Create Edit & Delete Fields
        function createActionForm($table, $pkName) {
            return '
            <div class="manage-bar">
                <form class="manage-form" method="GET" action="manage_action.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Edit '.$pkName.':</label>
                    <input type="number" name="id">
                    <button type="submit" class="manage-btn btn-edit">Edit</button>
                </form>

                <div style="border-left:1px solid #ccc; height:30px; margin:0 10px;"></div>

                <form class="manage-form" method="POST" action="manage_action.php"
                      onsubmit="return confirm(\'Permanently delete this ID?\');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Delete '.$pkName.':</label>
                    <input type="number" name="id">
                    <button type="submit" class="manage-btn btn-del">Delete</button>
                </form>
            </div>';
        }

        // Medication Edit & Delete (No primary key)
        function createActionFormMed($table, $pkName) {
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

                <form class="manage-form" method="POST" action="manage_action.php"
                      onsubmit="return confirm(\'Delete this record?\');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="table" value="'.$table.'">
                    <label>Delete '.$pkName.':</label>
                    <input type="text" name="id" required placeholder="Name...">
                    <button type="submit" class="manage-btn btn-del">Delete</button>
                </form>
            </div>';
        }

        // Check is table exists
        function isTable($conn, $name) {
            $sql = "SELECT COUNT(*) AS NUM FROM user_tables WHERE table_name = '" . strtoupper($name) . "'";
            $stid = oci_parse($conn, $sql);
            oci_execute($stid);
            $row = oci_fetch_assoc($stid);
            return ($row['NUM'] > 0);
        }
        /* ---------- MAIN LOGIC ---------- */

            // Admin sidebar actions
            if (isset($_POST['table_action'])) {

                $act = $_POST['table_action'];
                if ($act === 'drop_patient') {
                    runSQL($conn, 'drop.sql');

                } elseif ($act === 'create_patient') {
                    runSQL($conn, 'create.sql');

                } elseif ($act === 'populate_patient') {
                    runSQLPopulate($conn, 'populate.sql');

                } elseif ($act === 'run_queries') {
                    runQueries($conn, 'queries.sql');
                    echo "</div></div></body></html>";
                    exit;
                } elseif ($act === 'exit') {
                    oci_close($conn);
                    echo "<p class='success'>Disconnected from Oracle.</p>";
                    exit();
                }
            }


            echo "<p class='success'>Connected to Oracle.</p>";

            // Patient Table
            if (isTable($conn, 'PATIENT')) {
                echo '<div class="table-header"><h2>Patient List</h2></div>';
                echo createActionForm('Patient', 'OhipID');
                createTable($conn, "SELECT * FROM Patient ORDER BY LastName");
            }

            // Staff Table
            if (isTable($conn, 'STAFF')) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Staff List</h2></div>';
                echo createActionForm('Staff', 'StaffID');
                createTable($conn, "SELECT * FROM Staff ORDER BY LastName");
            }

            // Prescription Table
            if (isTable($conn, "PRESCRIPTION")) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Prescriptions</h2></div>';
                echo createActionForm('Prescription', 'PrescriptionID');
                createTable($conn, "SELECT * FROM Prescription ORDER BY PrescriptionID");
            }

            // Medication Table
            if (isTable($conn, "MEDICATIONINFO")) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Medication Info</h2></div>';
                echo createActionFormMed('MedicationInfo', 'Medication Name');
                createTable($conn, "SELECT * FROM MedicationInfo ORDER BY Medication");
            }

            // Billing Table
            if (isTable($conn, "BILLING")) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Billing Records</h2></div>';
                echo createActionForm('Billing', 'BillingID');
                createTable($conn, "SELECT * FROM Billing ORDER BY BillingID");
            }

            // Appointment Table
            if (isTable($conn, "APPOINTMENT")) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Appointments</h2></div>';
                echo createActionForm('Appointment', 'AppointmentID');
                createTable($conn, "SELECT * FROM Appointment ORDER BY AppointmentID");
            }

            // Medical Records Table
            if (isTable($conn, "MEDICALRECORD")) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Medical Records</h2></div>';
                echo createActionForm('MedicalRecord', 'OHIPID');
                createTable($conn, "SELECT * FROM MedicalRecord ORDER BY OhipID");
            }

            // Diagnosis Table
            if (isTable($conn, "DIAGNOSIS")) {
                echo '<div class="table-header" style="margin-top:40px;"><h2>Diagnoses</h2></div>';
                echo createActionForm('Diagnoses', 'DiagnosisID');
                createTable($conn, "SELECT * FROM Diagnoses ORDER BY DiagnosisID");
            }
        
        ?>
    </div>
</div>
</body>
</html>
