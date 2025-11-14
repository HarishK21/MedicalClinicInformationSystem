<!DOCTYPE html>
<html>
<head>
    <title>CPS510 Database - Patient List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #004c9b;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>Patient Database Records</h1>

    <?php
    // --- NEW DEBUGGING BLOCK ---

    // 1. Check if the Oracle PHP extension (OCI8) is even loaded
    if (!function_exists('oci_connect')) {
        
        echo "<p class='error'>FATAL ERROR: The OCI8 (Oracle) PHP extension is not installed or enabled on the server.</p>";
        echo "<p>The web server cannot talk to an Oracle database. Please contact your system administrator.</p>";
    
    } else {
        // If the function exists, we can proceed.
        echo "<p class='success'>Debug: The oci_connect() function exists. OCI8 extension is loaded.</p>";

        // 2. Include the credentials
        require_once 'a9connect.php';
        echo "<p>Debug: 'a9connect.php' was included.</p>";
        
        // 3. Define connection string
        $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
        echo "<p>Debug: Trying to connect...</p>";

        // 4. Try to connect (using '@' to suppress the default PHP warning so we can make our own)
        $conn = @oci_connect($username, $password, $db_conn_str);

        if (!$conn) {
            // 5. IF IT FAILS: Get the *specific* Oracle error
            $e = oci_error();
            echo "<p class='error'>Database Connection Failed.</p>";
            echo "<p class='error'>Oracle Error Message: " . htmlentities($e['message']) . "</p>";
            
            // This is the most common error.
            if (strpos($e['message'], 'ORA-01017') !== false) {
                echo "<p class='error'>Debug Hint: 'ORA-01017' means 'invalid username/password'. Please double-check a9connect.php on the server.</p>";
            }

        } else {
            // 6. IF IT SUCCEEDS: Run the query
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
    }
    ?>

</body>
</html>