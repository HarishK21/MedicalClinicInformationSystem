SELECT FirstName, LastName, COUNT(*) AS FullNames
FROM Patient
GROUP BY LastName, FirstName
ORDER BY FullNames DESC, LastName, FirstName;

SELECT Role, COUNT(*) as StaffCount
FROM Staff
WHERE EmploymentStatus = 'Active'
GROUP BY Role
ORDER BY StaffCount DESC;

SELECT OhipID, SUM(Cost) AS AmountOwed
FROM Billing
GROUP BY OhipID
ORDER BY AmountOwed DESC;

SELECT DISTINCT ReasonForVisit, Result
FROM Appointment
WHERE Result IS NOT NULL
ORDER BY ReasonForVisit DESC;

SELECT EmploymentStatus, AVG(Salary) AS AvgSalary
FROM Staff
GROUP BY EmploymentStatus
ORDER BY AvgSalary DESC;

SELECT OhipID, COUNT(*) AS AppointmentCount
FROM Appointment
GROUP BY OhipID
ORDER BY AppointmentCount DESC;
