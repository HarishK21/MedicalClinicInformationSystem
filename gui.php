<?php
require_once 'config.php';
include 'header.php';
?>
<div class="card">
    <h2>Welcome</h2>
    <p>
        This web application uses the <strong>Medical Clinic Information System</strong> database
        (tables: Patient, Staff, Prescription, Billing, Appointment, MedicalRecord).
    </p>
    <p>
        Use the navigation bar above or the shortcuts below:
    </p>
    <p>
        <a class="btn" href="patients_list.php">View Patients</a>
        <a class="btn" href="patients_add.php">Add New Patient</a>
        <a class="btn" href="appointments_list.php">View Appointments</a>
    </p>
    <p style="font-size: 13px; color:#555;">
        This interface demonstrates:
        <ul>
            <li>Connecting PHP to Oracle 11g</li>
            <li>Running SELECT queries and showing results in tables</li>
            <li>Inserting new rows via HTML forms (Patients)</li>
        </ul>
    </p>
</div>
<?php include 'footer.php'; ?>
