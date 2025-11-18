<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="layout">

    <aside class="sidebar">
        <h2>Admin Actions</h2>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="drop_patient">
            <button type="submit" class="sidebar-btn danger"
                onclick="return confirm('Are you sure you want to DROP the Patient table? This cannot be undone.');">
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

        <form method="get" action="patient.php">
            <input type="hidden" name="view" value="patient_fullnames">
            <button type="submit" class="sidebar-btn query">
                Patient Full Names
            </button>
        </form>

        <form method="get" action="patient.php">
            <input type="hidden" name="view" value="staff_roles">
            <button type="submit" class="sidebar-btn query">
                Active Staff by Role
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

        if (!$conn) {
            $e = oci_error();
            echo "<p class='error'>Database Connection Failed: " . htmlentities($e['message']) . "</p>";
        } else {
            $adminMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (isset($_POST['table_action'])) {
                    $action = $_POST['table_action'];

                    if ($action === 'drop_patient') {
                        $dropPatient = "BEGIN EXECUTE IMMEDIATE 'DROP TABLE Patient CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;";
                        $dropStaff   = "BEGIN EXECUTE IMMEDIATE 'DROP TABLE Staff CASCADE CONSTRAINTS'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;";

                        $err1 = runStatement($conn, $dropPatient);
                        $err2 = runStatement($conn, $dropStaff);

                        if ($err1 || $err2)
                            $adminMessage = "<p class='error'>Error dropping tables.</p>";
                        else
                            $adminMessage = "<p class='success'>Patient and Staff tables dropped.</p>";

                    } elseif ($action === 'create_patient') {
                        $createPatient = "CREATE TABLE Patient (
                            OhipID INT PRIMARY KEY,
                            FirstName VARCHAR(100) NOT NULL,
                            LastName VARCHAR(100) NOT NULL,
                            DateOfBirth DATE NOT NULL,
                            Sex VARCHAR(10) CHECK (Sex IN ('Male', 'Female')) NOT NULL,
                            Height INT CHECK (Height > 0),
                            Weight INT CHECK (Weight > 0),
                            Email VARCHAR(100),
                            Phone VARCHAR(14),
                            Address VARCHAR(100)
                        )";

                        $createStaff = "CREATE TABLE Staff (
                            StaffID INT PRIMARY KEY,
                            FirstName VARCHAR(100) NOT NULL,
                            LastName VARCHAR(100) NOT NULL,
                            Role VARCHAR(100) NOT NULL,
                            Email VARCHAR(100),
                            Phone VARCHAR(14),
                            Address VARCHAR(100),
                            EmploymentStatus VARCHAR(7) DEFAULT 'Active' CHECK (EmploymentStatus IN ('Absence', 'Active', 'Retired')),
                            Salary INT CHECK (Salary >= 0)
                        )";

                        $err1 = runStatement($conn, $createPatient);
                        $err2 = runStatement($conn, $createStaff);

                        if ($err1 || $err2)
                            $adminMessage = "<p class='error'>Error creating tables.</p>";
                        else
                            $adminMessage = "<p class='success'>Patient and Staff tables created (empty).</p>";

                    } elseif ($action === 'populate_patient') {
                        $inserts = [
                            "INSERT INTO Patient VALUES (1001, 'Miladshan', 'Jeevakaran', DATE '2005-07-10', 'Male', 120, 35, 'mil.jev@gmail.com', '647-880-4910', '123 Main St')",
                            "INSERT INTO Patient VALUES (1002, 'Umair', 'Alam', DATE '2005-06-01', 'Male', 165, 60, 'umair.alam@gmail.com', '905-624-4591', '456 Niagara Ave')",
                            "INSERT INTO Patient VALUES (1003, 'Harish', 'Kiritharan', DATE '2005-08-21', 'Male', 175, 72, 'h.kiritha@gmail.com', '416-555-9999', '789 Pine Rd')",
                            "INSERT INTO Patient VALUES (1004, 'Bob', 'Singh', DATE '1992-03-12', 'Female', 170, 65, 'bobSingh89@gmail.com', '416-555-1212', '12 Elm St')",
                            "INSERT INTO Patient VALUES (1005, 'David', 'Wilson', DATE '1978-11-30', 'Male', 182, 90, 'david.wilson@gmail.com', '416-555-3434', '98 King St')",
                            "INSERT INTO Patient VALUES (1006, 'Bob', 'Singh', DATE '2001-07-08', 'Female', 160, 55, 'Bsingh11@gmail.com', '416-555-5656', '22 River Rd')",
                            "INSERT INTO Patient VALUES (1007, 'Akshar', 'Patel', DATE '1995-09-02', 'Male', 178, 77, 'akshar.patel@gmail.com', '416-555-7878', '45 Maple Ave')",
                            "INSERT INTO Patient VALUES (1008, 'David', 'Wilson', DATE '2003-12-18', 'Female', 168, 62, 'david.wilson@gmail.com', '416-555-9090', '67 Birch Ln')",

                            "INSERT INTO Staff VALUES (2001, 'Emily', 'Johnson', 'Doctor', 'emily.johnson@gmail.com', '416-555-2222', '12 Clinic Blvd', 'Active', 120000)",
                            "INSERT INTO Staff VALUES (2002, 'Michael', 'Brown', 'Nurse', 'michael.brown@gmail.com', '416-555-3333', '34 Wellness St', 'Active', 65000)",
                            "INSERT INTO Staff VALUES (2003, 'Sarah', 'Lee', 'Doctor', 'sarah.lee@gmail.com', '416-555-4444', '56 Health Dr', 'Retired', 150000)",
                            "INSERT INTO Staff VALUES (2004, 'James', 'Taylor', 'Doctor', 'james.taylor@gmail.com', '416-555-1111', '78 Clinic Rd', 'Active', 115000)",
                            "INSERT INTO Staff VALUES (2005, 'Anna', 'White', 'Nurse', 'anna.white@gmail.com', '416-555-2223', '90 Wellness Blvd', 'Active', 67000)",
                            "INSERT INTO Staff VALUES (2006, 'Robert', 'Green', 'Receptionist', 'robert.green@gmail.com', '416-555-3334', '33 Care St', 'Active', 55000)",
                            "INSERT INTO Staff VALUES (2007, 'Isabella', 'King', 'Medical Student', 'isabella.king@gmail.com', '416-555-4445', '44 Med Dr', 'Active', 145000)",
                        ];

                        $hadError = false;
                        foreach ($inserts as $sql) {
                            $err = runStatement($conn, $sql);
                            if ($err && strpos($err['message'], 'ORA-00001') === false) {
                                $adminMessage = "<p class='error'>Error populating tables: " . htmlentities($err['message']) . "</p>";
                                $hadError = true;
                                break;
                            }
                        }

                        if (!$hadError)
                            $adminMessage = "<p class='success'>Patient and Staff tables populated successfully (duplicates skipped).</p>";
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

                case 'patient_fullnames':
                    echo "<h2>Patients Grouped by Full Name</h2>";

                    $sql = "SELECT FirstName, LastName, COUNT(*) AS FullNames
                            FROM Patient
                            GROUP BY LastName, FirstName
                            ORDER BY FullNames DESC, LastName, FirstName";
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
                            echo "<table>";
                            echo "<tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Number of Patients</th>
                                  </tr>";

                            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['FULLNAMES']) . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                    }
                    break;

                case 'staff_roles':
                    echo "<h2>Active Staff Members by Role</h2>";

                    $sql = "SELECT Role, COUNT(*) AS StaffCount
                            FROM Staff
                            WHERE EmploymentStatus = 'Active'
                            GROUP BY Role
                            ORDER BY StaffCount DESC";
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
                            echo "<table>";
                            echo "<tr>
                                    <th>Role</th>
                                    <th>Active Staff Count</th>
                                  </tr>";

                            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['ROLE']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['STAFFCOUNT']) . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
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
