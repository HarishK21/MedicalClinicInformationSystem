<?php include 'header.php'; ?>

<div class="page-container">
    <h1>Patient Database Records</h1>

    <?php
    require_once 'a9connect.php';
    $db_conn_str = '(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=oracle.scs.ryerson.ca)(Port=1521))(CONNECT_DATA=(SID=orcl)))';
    $conn = oci_connect($username, $password, $db_conn_str);

    if (!$conn) {
        $e = oci_error();
        echo "<p class='error'>Database Connection Failed: " . htmlentities($e['message']) . "</p>";

    } else {
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
                    <th>Actions</th>
                </tr>";

            while ($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['OHIPID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
                echo "<td>" . htmlspecialchars($row['SEX']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PHONE']) . "</td>";
                echo "<td>
                        <a href='patient_edit.php?id=" . urlencode($row['OHIPID']) . "' class='btn edit'>Modify</a>
                        <a href='patient_delete.php?id=" . urlencode($row['OHIPID']) . "' class='btn delete'>Delete</a>
                    </td>";

                echo "</tr>";
            }
            echo "</table>";
            }
        }
        oci_close($conn);
    }
    ?>
</div>

</body>
</html>
