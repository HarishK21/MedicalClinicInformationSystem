DROP TABLE MedicalRecord;
DROP TABLE Billing;
DROP TABLE Prescription;
DROP TABLE Appointment;
DROP TABLE Staff;
DROP TABLE Patient;

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
);

CREATE TABLE Staff (
    StaffID INT PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Role VARCHAR(100) NOT NULL,
    Email VARCHAR(100),
    Phone VARCHAR(14),
    Address VARCHAR(100),
    EmploymentStatus VARCHAR(7) DEFAULT 'Active' CHECK (EmploymentStatus IN ('Absence', 'Active', 'Retired')),
    Salary INT CHECK (Salary >= 0)
);

CREATE TABLE Prescription (
    PrescriptionID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    StaffID INT NOT NULL,
    Medication VARCHAR(100) NOT NULL,
    Dose VARCHAR(100),
    Timeframe VARCHAR(100),
    DateIssued DATE NOT NULL,
    Instructions VARCHAR(100),
    FOREIGN KEY (OhipID) REFERENCES Patient (OhipID),
    FOREIGN KEY (StaffID) REFERENCES Staff (StaffID)
);

CREATE TABLE Billing (
    BillingID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    OhipCoverage Char(1) DEFAULT 'Y' CHECK (OhipCoverage IN ('Y', 'N')),
    Service VARCHAR(100) NOT NULL,
    Cost INT CHECK (Cost >= 0) NOT NULL,
    PaymentMethod VARCHAR(100) CHECK (PaymentMethod IN ('Credit', 'Debit', 'Cash', 'OHIP')),
    PaymentDate DATE,
    FOREIGN KEY (OhipID) REFERENCES Patient (OhipID)
);

CREATE TABLE Appointment (
    AppointmentID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    StaffID INT NOT NULL,
    DateAndTime DATE NOT NULL,
    Status VARCHAR(100) DEFAULT 'Scheduled' CHECK (Status IN ('Cancelled', 'Scheduled', 'Completed', 'No-Show')) NOT NULL,
    ReasonForVisit VARCHAR(100),
    Result VARCHAR(100),
    FOREIGN KEY (OhipID) REFERENCES Patient (OhipID),
    FOREIGN KEY (StaffID) REFERENCES Staff (StaffID)
); 

CREATE TABLE MedicalRecord (
    recordID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    Allergies VARCHAR(100),
    Diagnosis VARCHAR(200),
    Procedures VARCHAR(200),
    Vaccinations VARCHAR(100),
    PastMedication VARCHAR(200),
    FamilyHistory VARCHAR(200),
    FOREIGN KEY (OhipID) REFERENCES Patient(OhipID)
);

-- Insert Patients
INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1001, 'Miladshan', 'Jeevakaran', DATE '2005-07-10', 'Male', 120, 35, 'mil.jev@gmail.com', '647-880-4910', '123 Main St');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1002, 'Umair', 'Alam', DATE '2005-06-01', 'Male', 165, 60, 'umair.alam@gmail.com', '905-624-4591', '456 Niagara Ave');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1003, 'Harish', 'Kiritharan', DATE '2005-08-21', 'Male', 175, 72, 'h.kiritha@gmail.com', '416-555-9999', '789 Pine Rd');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1004, 'Bob', 'Singh', DATE '1992-03-12', 'Female', 170, 65, 'bobSingh89@gmail.com', '416-555-1212', '12 Elm St');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1005, 'David', 'Wilson', DATE '1978-11-30', 'Male', 182, 90, 'david.wilson@gmail.com', '416-555-3434', '98 King St');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address) 
VALUES (1006, 'Bob', 'Singh', DATE '2001-07-08', 'Female', 160, 55, 'Bsingh11@gmail.com', '416-555-5656', '22 River Rd');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address) 
VALUES (1007, 'Akshar', 'Patel', DATE '1995-09-02', 'Male', 178, 77, 'akshar.patel@gmail.com', '416-555-7878', '45 Maple Ave'); 





INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1008, 'David’', 'Wilson', DATE '2003-12-18', 'Female', 168, 62, 'david.wilson@gmail.com', '416-555-9090', '67 Birch Ln');

-- Insert Staff
INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2001, 'Emily', 'Johnson', 'Doctor', 'emily.johnson@gmail.com', '416-555-2222', '12 Clinic Blvd', 'Active', 120000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2002, 'Michael', 'Brown', 'Nurse', 'michael.brown@gmail.com', '416-555-3333', '34 Wellness St', 'Active', 65000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2003, 'Sarah', 'Lee', 'Doctor', 'sarah.lee@gmail.com', '416-555-4444', '56 Health Dr', 'Retired', 150000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2004, 'James', 'Taylor', 'Doctor', 'james.taylor@gmail.com', '416-555-1111', '78 Clinic Rd', 'Active', 115000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2005, 'Anna', 'White', 'Nurse', 'anna.white@gmail.com', '416-555-2223', '90 Wellness Blvd', 'Active', 67000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2006, 'Robert', 'Green', 'Receptionist', 'robert.green@gmail.com', '416-555-3334', '33 Care St', 'Active', 55000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary) 
VALUES (2007, 'Isabella', 'King', 'Medical Student', 'isabella.king@gmail.com', '416-555-4445', '44 Med Dr', 'Active', 145000);




INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2008, 'William', 'Lopez', 'Admin', 'william.lopez@gmail.com', '416-555-5556', '55 Health Way', 'Retired', 50000);

-- Insert Prescriptions
INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3001, 1001, 2001, 'Amoxicillin', '500mg', '7 days', DATE '2025-09-20', 'Take twice daily with food');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3002, 1002, 2002, 'Ibuprofen', '200mg', '5 days', DATE '2025-09-18', 'Take every 6 hours as needed for pain');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3003, 1004, 2004, 'Paracetamol', '500mg', '3 days', DATE '2025-09-23', 'Take every 8 hours');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3004, 1005, 2001, 'Amoxicillin', '250mg', '10 days', DATE '2025-09-24', 'Take three times daily');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3005, 1006, 2002, 'Ibuprofen', '400mg', '5 days', DATE '2025-09-25', 'Take every 6 hours with food');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3006, 1007, 2007, 'Metformin', '500mg', '30 days', DATE '2025-09-26', 'Take twice daily');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3007, 1008, 2004, 'Lisinopril', '10mg', '60 days', DATE '2025-09-26', 'Take once daily');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3008, 1001, 2005, 'Ibuprofen', '200mg', '3 days', DATE '2025-09-27', 'As needed for headache');
INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, Instructions)
VALUES (3009, 1002, 2004, 'Amoxicillin', '500mg', '7 days', DATE '2025-09-28', 'Take twice daily');

-- Insert Billing
INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4001, 1001, 'Y', 'General Checkup', 0, 'OHIP', DATE '2025-09-20');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4002, 1002, 'N', 'X-Ray', 150, 'Credit', DATE '2025-09-21');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4003, 1003, 'N', 'Blood Test', 75, 'Cash', DATE '2025-09-22');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4004, 1004, 'N', 'MRI', 300, 'Credit', DATE '2025-09-23');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4005, 1005, 'Y', 'Surgery Consultation', 0, 'OHIP', DATE '2025-09-24');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4006, 1006, 'N', 'Physiotherapy', 100, 'Debit', DATE '2025-09-25');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4007, 1007, 'N', 'Vaccination', 50, 'Cash', DATE '2025-09-26');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4008, 1008, 'Y', 'Annual Exam', 0, 'OHIP', DATE '2025-09-26');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4009, 1001, 'N', 'Blood Work', 80, 'Credit', DATE '2025-09-27');



-- Insert Appointments
INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5001, 1001, 2001, DATE '2025-09-25', 'Scheduled', 'Routine Checkup', NULL);

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5002, 1002, 2002, DATE '2025-09-26', 'Completed', 'Back Pain', 'Prescribed Ibuprofen');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5003, 1003, 2001, DATE '2025-09-27', 'Cancelled', 'Flu Symptoms', NULL);

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5004, 1004, 2004, DATE '2025-09-28', 'Scheduled', 'MRI Follow-up', NULL);

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5005, 1005, 2007, DATE '2025-09-29', 'Completed', 'Surgery Consultation', 'Cleared for operation');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5006, 1006, 2002, DATE '2025-09-30', 'Completed', 'Physiotherapy', 'Improved mobility');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5007, 1007, 2005, DATE '2025-10-01', 'Scheduled', 'Vaccination', NULL);

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5008, 1008, 2004, DATE '2025-10-02', 'Cancelled', 'Routine Exam', NULL);

-- Insert Medical Records
INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6001, 1001, 'Peanuts', 'Hypertension', 'Appendectomy (2015)', 'Tetanus, COVID-19', 'Lisinopril', 'Heart disease (father)');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6002, 1002, 'Eggs, Dust', 'Asthma', 'None', 'Flu Shot, COVID-19', 'Albuterol', 'Asthma (mother)');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6003, 1003, 'None', 'Diabetes Type 2', 'None', 'MMR, COVID-19', 'Metformin', 'Diabetes (grandmother)');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6004, 1004, 'Shellfish', 'Migraine', 'None', 'Flu Shot', 'Paracetamol', 'Stroke (mother)');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6005, 1005, 'Penicillin', 'Arthritis', 'Knee Surgery (2018)', 'Flu Shot', 'Ibuprofen', 
'Arthritis (father)');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6006, 1006, 'Dust', 'Back Pain', 'None', 'COVID-19', 'Ibuprofen', 'None');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6007, 1007, 'None', 'Hypertension', 'None', 'Flu Shot', 'Lisinopril', 'Hypertension (mother)');

INSERT INTO MedicalRecord (recordID, OhipID, Allergies, Diagnosis, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (6008, 1008, 'Gluten', 'Anemia', 'None', 'Tetanus', 'Iron supplements', 'Anemia (sister)');
