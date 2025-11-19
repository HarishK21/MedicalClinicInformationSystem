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
        <div class="brand">
            <span class="brand-title">Medical Clinic Information System</span>
        </div>

        <nav class="navbar">
            <a href="patient.php" class="active">Patients</a>
        </nav>
    </div>
</header>

<div class="layout">

    <aside class="sidebar">
        <h2>Admin Actions</h2>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="drop_patient">
            <button type="submit" class="sidebar-btn danger"
                onclick="return confirm('Are you sure you want to DROP the tables? This cannot be undone.');">
                Drop Tables
            </button>
        </form>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="create_patient">
            <button type="submit" class="sidebar-btn primary">
                Create Tables
            </button>
        </form>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="populate_patient">
            <button type="submit" class="sidebar-btn neutral">
                Populate Tables
            </button>
        </form>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="run_queries">
            <button type="submit" class="sidebar-btn query">
                Run Queries
            </button>
        </form>
    </aside>

    <div class="page-container">
        <h1>Patient Database Records</h1>

        <?php
        require_once 'a9connect.php';
        $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
        $conn = oci_connect($username, $password, $db_conn_str);

        function runStatement($conn, $sql)
        {
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

        function runSQL($conn, $file) {
            $sqlCode = file_get_contents($file);
            $queries = explode(";", $sqlCode);

            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue;

                $stid = oci_parse($conn, $query);
                if (!oci_execute($stid)) {
                    $error = oci_error($stid);
                    echo "<p class='error'>Execution error: " . htmlentities($error['message']) . "</p>";
                    continue;
                }
            }
            oci_commit($conn);
        }

        function runSQLPopulate($conn, $file) {
            $sqlCode = file_get_contents($file);
            $queries = explode(";", $sqlCode);

            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue;

                // convert 'YYYY-MM-DD' to TO_DATE(...) for Oracle
                $query = preg_replace_callback(
                    "/'(\d{4}-\d{2}-\d{2})'/",
                    function ($matches) {
                        return "TO_DATE('" . $matches[1] . "', 'YYYY-MM-DD')";
                    },
                    $query
                );

                $stid = oci_parse($conn, $query);
                if (!oci_execute($stid)) {
                    $error = oci_error($stid);
                    echo "<p class='error'>Execution error: " . htmlentities($error['message']) . "</p>";
                    continue;
                }
            }
            oci_commit($conn);
        }

        // Run all SELECT queries from queries.sql and display each as a table
        function runQueries($conn, $file) {
            $sqlCode = file_get_contents($file);
            $queries = explode(";", $sqlCode);
            $queryNumber = 1;

            foreach ($queries as $query) {
                $query = trim($query);
                if (empty($query)) continue;

                echo "<h2>Query {$queryNumber} Results</h2>";

                $stid = oci_parse($conn, $query);
                if (!$stid) {
                    $e = oci_error($conn);
                    echo "<p class='error'>SQL Parsing Error: " . htmlentities($e['message']) . "</p>";
                    $queryNumber++;
                    continue;
                }

                $r = oci_execute($stid);
                if (!$r) {
                    $e = oci_error($stid);
                    echo "<p class='error'>SQL Execution Error: " . htmlentities($e['message']) . "</p>";
                    $queryNumber++;
                    continue;
                }

                echo "<table><tr>";
                $ncols = oci_num_fields($stid);
                for ($i = 1; $i <= $ncols; $i++) {
                    $colname = oci_field_name($stid, $i);
                    echo "<th>" . htmlspecialchars($colname) . "</th>";
                }
                echo "</tr>";

                while ($row = oci_fetch_array($stid, OCI_NUM + OCI_RETURN_NULLS)) {
                    echo "<tr>";
                    foreach ($row as $val) {
                        echo "<td>" . htmlspecialchars($val) . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";
                $queryNumber++;
            }
        }

        if (!$conn) {
            $e = oci_error();
            echo "<p class='error'>Database Connection Failed: " . htmlentities($e['message']) . "</p>";
        } else {
            $adminMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (isset($_POST['table_action'])) {
                    $action = $_POST['table_action'];

                    if ($action === 'drop_patient') {

                        runSQL($conn, 'drop.sql');

                    } elseif ($action === 'create_patient') {

                        runSQL($conn, 'create.sql');

                    } elseif ($action === 'populate_patient') {

                        runSQLPopulate($conn, 'populate.sql');

                    } elseif ($action === 'run_queries') {

                        runQueries($conn, 'queries.sql');
                    }
                }

                if (isset($_POST['record_action'])) {
                    $action = $_POST['record_action'];

                    if ($action === 'delete_patient') {
                        $ohip_id = $_POST['ohip_id'] ?? null;
                        if ($ohip_id !== null) {
                            $sql = "DELETE FROM Patient WHERE OhipID = $ohip_id";
                            $err = runStatement($conn, $sql);
                            if ($err) $adminMessage = "<p class='error'>Error deleting patient: " . htmlentities($err['message']) . "</p>";
                            else $adminMessage = "<p class='success'>Patient (OHIP ID: $ohip_id) deleted successfully.</p>";
                        }

                    } elseif ($action === 'add_patient') {
                        $sql = "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address) VALUES (
                            " . $_POST['ohip_id'] . ",
                            '" . $_POST['first_name'] . "',
                            '" . $_POST['last_name'] . "',
                            DATE '" . $_POST['dob'] . "',
                            '" . $_POST['sex'] . "',
                            " . $_POST['height'] . ",
                            " . $_POST['weight'] . ",
                            '" . $_POST['email'] . "',
                            '" . $_POST['phone'] . "',
                            '" . $_POST['address'] . "'
                        )";
                        $err = runStatement($conn, $sql);
                        if ($err) $adminMessage = "<p class='error'>Error adding patient: " . htmlentities($err['message']) . "</p>";
                        else $adminMessage = "<p class='success'>Patient added successfully.</p>";

                    } elseif ($action === 'edit_patient') {
                        $sql = "UPDATE Patient SET
                            FirstName = '" . $_POST['first_name'] . "',
                            LastName = '" . $_POST['last_name'] . "',
                            DateOfBirth = DATE '" . $_POST['dob'] . "',
                            Sex = '" . $_POST['sex'] . "',
                            Height = " . $_POST['height'] . ",
                            Weight = " . $_POST['weight'] . ",
                            Email = '" . $_POST['email'] . "',
                            Phone = '" . $_POST['phone'] . "',
                            Address = '" . $_POST['address'] . "'
                        WHERE OhipID = " . $_POST['ohip_id'];

                        $err = runStatement($conn, $sql);
                        if ($err) $adminMessage = "<p class='error'>Error updating patient: " . htmlentities($err['message']) . "</p>";
                        else $adminMessage = "<p class='success'>Patient (OHIP ID: " . $_POST['ohip_id'] . ") updated successfully.</p>";
                    }
                }
            }

            echo $adminMessage;

            $view = $_GET['view'] ?? 'list';

            switch ($view) {
                case 'add':
                    ?>
                    <h2>Add New Patient</h2>
                    <form method="post" action="patient.php" class="crud-form">
                        <input type="hidden" name="record_action" value="add_patient">
                        
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
                            <button type="submit" class="sidebar-btn primary">Add Patient</button>
                            <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                        </div>
                    </form>
                    <?php
                    break;

                case 'edit':
                    $patient_id = $_GET['id'];
                    $sql = "SELECT * FROM Patient WHERE OhipID = $patient_id";
                    $stid = oci_parse($conn, $sql);
                    oci_execute($stid);
                    $patient = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);

                    if (!$patient) {
                        echo "<p class='error'>Patient not found.</p>";
                    } else {
                        $dob_formatted = date('Y-m-d', strtotime($patient['DATEOFBIRTH']));
                        ?>
                        <h2>Edit Patient: <?php echo htmlspecialchars($patient['FIRSTNAME'] . ' ' . $patient['LASTNAME']); ?></h2>
                        <form method="post" action="patient.php" class="crud-form">
                            <input type="hidden" name="record_action" value="edit_patient">
                            <input type="hidden" name="ohip_id" value="<?php echo $patient['OHIPID']; ?>">
                            
                            <div><label>OHIP ID:</label><input type="number" value="<?php echo $patient['OHIPID']; ?>" disabled></div>
                            <div><label>First Name:</label><input type="text" name="first_name" value="<?php echo htmlspecialchars($patient['FIRSTNAME']); ?>" required></div>
                            <div><label>Last Name:</label><input type="text" name="last_name" value="<?php echo htmlspecialchars($patient['LASTNAME']); ?>" required></div>
                            <div><label>Date of Birth:</label><input type="date" name="dob" value="<?php echo $dob_formatted; ?>" required></div>
                            <div><label>Sex:</label>
                                <select name="sex" required>
                                    <option value="Male" <?php if ($patient['SEX'] == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if ($patient['SEX'] == 'Female') echo 'selected'; ?>>Female</option>
                                </select>
                            </div>
                            <div><label>Height (cm):</label><input type="number" name="height" value="<?php echo $patient['HEIGHT']; ?>"></div>
                            <div><label>Weight (kg):</label><input type="number" name="weight" value="<?php echo $patient['WEIGHT']; ?>"></div>
                            <div><label>Email:</label><input type="email" name="email" value="<?php echo htmlspecialchars($patient['EMAIL']); ?>"></div>
                            <div><label>Phone:</label><input type="text" name="phone" value="<?php echo htmlspecialchars($patient['PHONE']); ?>"></div>
                            <div><label>Address:</label><input type="text" name="address" value="<?php echo htmlspecialchars($patient['ADDRESS']); ?>"></div>

                            <div class="form-actions">
                                <button type="submit" class="sidebar-btn primary">Save Changes</button>
                                <a href="patient.php" class="sidebar-btn danger">Cancel</a>
                            </div>
                        </form>
                        <?php
                    }
                    break;

                case 'list':
                default:
                    echo "<p class='success'>Successfully connected to the Oracle database!</p>";
                    
                    $sql = "SELECT * FROM Patient ORDER BY LastName";
                    $stid = oci_parse($conn, $sql);

                    if (!$stid) {
                        $e = oci_error($conn);
                        echo "<p class='error'>SQL Parsing Error: " . htmlentities($e['message']) . "</p>";
                    } else {
                        $r = oci_execute($stid);
                        if (!$r) {
                            $e = oci_error($stid);
                            echo "<p class='error'>SQL Execution Error: " . htmlentities($e['message']) . "</p>";
                        } else {
                            echo "<h2>Patient List</h2>";
                            echo "<table>";
                            echo "<tr>
                                    <th>OHIP ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>DOB</th>
                                    <th>Sex</th>
                                    <th>Height</th>
                                    <th>Weight</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                  </tr>";
                            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                                $dob_formatted = date('Y-m-d', strtotime($row['DATEOFBIRTH']));
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['OHIPID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
                                echo "<td>" . $dob_formatted . "</td>";
                                echo "<td>" . htmlspecialchars($row['SEX']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['HEIGHT']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['WEIGHT']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['PHONE']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ADDRESS']) . "</td>";
                                
                                echo "<td>";
                                echo '<a href="patient.php?view=edit&id=' . $row['OHIPID'] . '" class="action-link">Edit</a> ';
                                
                                echo '<form method="post" action="patient.php" onsubmit="return confirm(\'Are you sure you want to delete this patient?\');" style="display:inline;">
                                        <input type="hidden" name="record_action" value="delete_patient">
                                        <input type="hidden" name="ohip_id" value="' . $row['OHIPID'] . '">
                                        <button type="submit" class="action-link-danger">Delete</button>
                                      </form>';
                                echo "</td>";

                                echo "</tr>";
                            }
                            echo "</table>";

                            echo "<h2>Staff List</h2>";
                            $sql_staff = "SELECT * FROM Staff ORDER BY LastName"; 
                            $stid_staff = oci_parse($conn, $sql_staff);
                            oci_execute($stid_staff);
                            echo "<table>";
                            echo "<tr>
                                    <th>Staff ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Employment Status</th>
                                    <th>Salary</th>
                                  </tr>";

                            while ($row = oci_fetch_array($stid_staff, OCI_ASSOC + OCI_RETURN_NULLS)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['STAFFID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ROLE']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['PHONE']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['ADDRESS']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['EMPLOYMENTSTATUS']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['SALARY']) . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";

                            echo '<div style="margin-top: 40px;"><a href="patient.php?view=add" class="sidebar-btn primary" style="width: auto;">Add New Patient</a></div>';
                        }
                    }
                    break;
            }

            oci_close($conn);
        }
        ?>
    </div>
</div>

</body>
</html>
