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

        // ----------------- HELPERS -----------------

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

        // --------------- MAIN PAGE LOGIC ---------------

        if (!$conn) {
            $e = oci_error();
            echo "<p class='error'>Database Connection Failed: " . htmlentities($e['message']) . "</p>";
        } else {

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_action'])) {
                $action = $_POST['table_action'];

                if ($action === 'drop_patient') {
                    runSQL($conn, 'drop.sql');

                } elseif ($action === 'create_patient') {
                    runSQL($conn, 'create.sql');

                } elseif ($action === 'populate_patient') {
                    runSQLPopulate($conn, 'populate.sql');

                } elseif ($action === 'run_queries') {
                    runQueries($conn, 'queries.sql');
                    oci_close($conn);
                    echo "</div></div></body></html>";
                    exit;
                }
            }

            echo "<p class='success'>Successfully connected to the Oracle database!</p>";

            // ---------- PATIENT TABLE (READ ONLY) ----------
            echo '<div class="table-header">
                    <h2>Patient List</h2>
                    <a href="add.php?table=patient" class="sidebar-btn primary" style="width:auto;">
                        Add Record
                    </a>
                  </div>';

            $sql = "SELECT * FROM Patient ORDER BY LastName";
            $stid = oci_parse($conn, $sql);

            if (!$stid) {
                $e = oci_error($conn);
                echo "<p class='error'>SQL Parsing Error: " . htmlentities($e['message']) . "</p>";
            } else {
                $r = @oci_execute($stid);
                if (!$r) {
                    $e = oci_error($stid);
                    if ($e && isset($e['code']) && $e['code'] == 942) {
                        // table does not exist
                        echo "<p class='warning'>Patient table does not exist. Use 'Create Tables' in the sidebar.</p>";
                    } else {
                        echo "<p class='error'>SQL Execution Error: " . htmlentities($e['message']) . "</p>";
                    }
                } else {
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
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }

            // ---------- STAFF TABLE (READ ONLY) ----------
            echo '<div class="table-header" style="margin-top:40px;">
                    <h2>Staff List</h2>
                    <a href="add.php?table=staff" class="sidebar-btn primary" style="width:auto;">
                        Add Record
                    </a>
                  </div>';

            $sql_staff = "SELECT * FROM Staff ORDER BY LastName";
            $stid_staff = oci_parse($conn, $sql_staff);
            if ($stid_staff) {
                $r2 = @oci_execute($stid_staff);
                if ($r2) {
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
                } else {
                    $e = oci_error($stid_staff);
                    if ($e && isset($e['code']) && $e['code'] == 942) {
                        echo "<p class='warning'>Staff table does not exist. Use 'Create Tables' in the sidebar.</p>";
                    } else {
                        echo "<p class='error'>SQL Execution Error: " . htmlentities($e['message']) . "</p>";
                    }
                }
            } else {
                echo "<p class='error'>Unable to prepare Staff query.</p>";
            }

            oci_close($conn);
        }
        ?>
    </div>
</div>

</body>
</html>
