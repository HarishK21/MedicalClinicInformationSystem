<?php // header.php ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Medical Clinic Information System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f4f5f7;
        }
        header {
            background: #283593;
            color: #fff;
            padding: 15px 25px;
        }
        header h1 {
            margin: 0;
            font-size: 22px;
        }
        nav {
            background: #3949ab;
            padding: 8px 25px;
        }
        nav a {
            color: #e3f2fd;
            margin-right: 15px;
            text-decoration: none;
            font-size: 14px;
        }
        nav a:hover {
            text-decoration: underline;
        }
        main {
            padding: 20px 25px;
        }
        .card {
            background: #fff;
            border-radius: 6px;
            padding: 15px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }
        th {
            background: #e8eaf6;
        }
        .btn {
            display: inline-block;
            padding: 6px 10px;
            background: #3949ab;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }
        .btn:hover {
            background: #1a237e;
        }
        .btn-secondary {
            background: #757575;
        }
        .btn-secondary:hover {
            background: #424242;
        }
        .form-row {
            margin-bottom: 10px;
        }
        label {
            display: inline-block;
            width: 140px;
            font-size: 14px;
        }
        input[type="text"], input[type="date"], select {
            padding: 4px 6px;
            font-size: 14px;
            width: 220px;
        }
        .msg-success {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        .msg-error {
            color: #c62828;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<header>
    <h1>Medical Clinic Information System</h1>
</header>
<nav>
    <a href="index.php">Home</a>
    <a href="patients_list.php">Patients</a>
    <a href="patients_add.php">Add Patient</a>
    <a href="appointments_list.php">Appointments</a>
</nav>
<main>
