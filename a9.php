<!DOCTYPE html>
<html>
<head>
    <title>CPS510 Database - Patient List</title>
    <!-- Simple styling for the table -->
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
    // 1. Include the credentials
    require_once 'a9connect.php';
    
    // 2. Define connection string
    $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';

    // 3. Connect to the database
    $conn = oci_connect($username, $password, $db_conn_str);

    if (!$conn) {
        // Handle connection error
        $e = oci_error();
        echo "<p class='error'>Database Connection Failed: " . htmlentities($e['message']) . "</p>";

    } else {
        // 4. Connection was successful, run the query
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
                // 5. Fetch and display results
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
        // 6. Close the connection
        oci_close($conn);
    }
    ?>

</body>
</html>