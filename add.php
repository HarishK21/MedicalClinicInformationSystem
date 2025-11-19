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
            return null; // success
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
                echo '<p><a href="patient.php" class="sidebar-btn neutral">Back to Patient Records</a></p>';
                oci_close($conn);
                echo "</div></div></body></html>";
                exit;
            }

            $adminMessage = '';

            // Handle POST insert
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_action']) && $_POST['record_action'] === 'add_record') {

                if ($table === 'patient') {
                    $sql = "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address) VALUES (
                        " . $_POST['ohip_id'] . ",
                        '" . $_POST['first_name'] . "',
                        '" . $_POST['last_name'] . "',
                        DATE '" . $_POST['dob'] . "',
                        '" . $_POST['sex'] . "',
                        " . ($_POST['height'] === '' ? 'NULL' : $_POST['height']) . ",
                        " . ($_POST['weight'] === '' ? 'NULL' : $_POST['weight']) . ",
                        '" . $_POST['email'] . "',
                        '" . $_POST['phone'] . "',
                        '" . $_POST['address'] . "'
                    )";

                    $err = runStatement($conn, $sql);
                    if ($err) {
                        $adminMessage = "<p class='error'>Error adding Patient record: " . htmlentities($err['message']) . "</p>";
                    } else {
                        $adminMessage = "<p class='success'>Patient record added successfully.</p>";
                    }

                } elseif ($table === 'staff') {
                    $sql = "INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary) VALUES (
                        " . $_POST['staff_id'] . ",
                        '" . $_POST['first_name'] . "',
                        '" . $_POST['last_name'] . "',
                        '" . $_POST['role'] . "',
                        '" . $_POST['email'] . "',
                        '" . $_POST['phone'] . "',
                        '" . $_POST['address'] . "',
                        '" . $_POST['employment_status'] . "',
                        " . ($_POST['salary'] === '' ? 'NULL' : $_POST['salary']) . "
                    )";

                    $err = runStatement($conn, $sql);
                    if ($err) {
                        $adminMessage = "<p class='error'>Error adding Staff record: " . htmlentities($err['message']) . "</p>";
                    } else {
                        $adminMessage = "<p class='success'>Staff record added successfully.</p>";
                    }
                }
            }

            echo $adminMessage;


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

            } else {
                echo "<p class='error'>Unsupported table: " . htmlspecialchars($table) . "</p>";
                echo '<p><a href="patient.php" class="sidebar-btn neutral">Back to Patient Records</a></p>';
            }

            oci_close($conn);
        }
        ?>
    </div>
</div>

</body>
</html>
