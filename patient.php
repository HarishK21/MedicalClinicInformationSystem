<?php include 'header.php'; ?>

<div class="layout">

    <aside class="sidebar">
        <h2>Admin Actions</h2>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="drop_patient">
            <button type="submit" class="sidebar-btn danger"
                onclick="return confirm('Are you sure you want to DROP the Patient table? This cannot be undone.');">
                Drop Patient Table
            </button>
        </form>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="create_patient">
            <button type="submit" class="sidebar-btn primary">
                Create Patient Table
            </button>
        </form>

        <form method="post" action="patient.php">
            <input type="hidden" name="table_action" value="populate_patient">
            <button type="submit" class="sidebar-btn neutral">
                Populate Patient Table
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

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_action'])) {

                $action = $_POST['table_action'];

                if ($action === 'drop_patient') {
                    $sql = "
                        BEGIN
                          EXECUTE IMMEDIATE 'DROP TABLE Patient CASCADE CONSTRAINTS';
                        EXCEPTION
                          WHEN OTHERS THEN
                            IF SQLCODE != -942 THEN
                              RAISE;
                            END IF;
                        END;
                    ";
                    $err = runStatement($conn, $sql);
                    if ($err) {
                        $adminMessage = "<p class='error'>Error dropping Patient table: " . htmlentities($err['message']) . "</p>";
                    } else {
                        $adminMessage = "<p class='success'>Patient table dropped successfully.</p>";
                    }

                } elseif ($action === 'create_patient') {

                    $sql = "
                        CREATE TABLE Patient (
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
                        )
                    ";
                    $err = runStatement($conn, $sql);
                    if ($err) {
                        $adminMessage = "<p class='error'>Error creating Patient table: " . htmlentities($err['message']) . "</p>";
                    } else {
                        $adminMessage = "<p class='success'>Patient table created successfully.</p>";
                    }

                } elseif ($action === 'populate_patient') {

                    $inserts = [
                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1001, 'Miladshan', 'Jeevakaran', DATE '2005-07-10', 'Male', 120, 35,
                 'mil.jev@gmail.com', '647-880-4910', '123 Main St')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1002, 'Umair', 'Alam', DATE '2005-06-01', 'Male', 165, 60,
                 'umair.alam@gmail.com', '905-624-4591', '456 Niagara Ave')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1003, 'Harish', 'Kiritharan', DATE '2005-08-21', 'Male', 175, 72,
                 'h.kiritha@gmail.com', '416-555-9999', '789 Pine Rd')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1004, 'Bob', 'Singh', DATE '1992-03-12', 'Female', 170, 65,
                 'bobSingh89@gmail.com', '416-555-1212', '12 Elm St')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1005, 'David', 'Wilson', DATE '1978-11-30', 'Male', 182, 90,
                 'david.wilson@gmail.com', '416-555-3434', '98 King St')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1006, 'Bob', 'Singh', DATE '2001-07-08', 'Female', 160, 55,
                 'Bsingh11@gmail.com', '416-555-5656', '22 River Rd')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1007, 'Akshar', 'Patel', DATE '1995-09-02', 'Male', 178, 77,
                 'akshar.patel@gmail.com', '416-555-7878', '45 Maple Ave')",

                        "INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
         VALUES (1008, 'David', 'Wilson', DATE '2003-12-18', 'Female', 168, 62,
                 'david.wilson@gmail.com', '416-555-9090', '67 Birch Ln')",

                    ];

                    $hadError = false;
                    foreach ($inserts as $sql) {
                        $err = runStatement($conn, $sql);
                        if ($err && strpos($err['message'], 'ORA-00001') === false) {
                            $adminMessage = "<p class='error'>Error populating Patient table: " . htmlentities($err['message']) . "</p>";
                            $hadError = true;
                            break;
                        }
                    }
                    if (!$hadError) {
                        $adminMessage = "<p class='success'>Patient table populated (duplicate rows were skipped if they already existed).</p>";
                    }
                }

            }

            echo $adminMessage;

            echo "<p class='success'>Successfully connected to the Oracle database!</p>";

            $sql = "SELECT OhipID, FirstName, LastName, Sex, Email, Phone FROM Patient ORDER BY LastName";
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
                            <th>Sex</th>
                            <th>Email</th>
                            <th>Phone</th>
                          </tr>";

                    while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['OHIPID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['SEX']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['PHONE']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }

            oci_close($conn);
        }
        ?>
    </div>
</div>

</body>

</html>