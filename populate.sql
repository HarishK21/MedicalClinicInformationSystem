INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1001, 'John', 'Doe', '1985-04-12', 'Male', 180, 80, 'john.doe@email.com', '416-555-1000', '123 Main St');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1002, 'Sarah', 'Miller', '1992-09-05', 'Female', 165, 60, 'sarah.miller@email.com', '416-555-2000', '456 Oak St');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1003, 'Michael', 'Brown', '1978-01-20', 'Male', 175, 82, 'michael.brown@email.com', '416-555-3000', '789 Pine St');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1004, 'Emily', 'Johnson', '1995-07-11', 'Female', 160, 55, 'emily.johnson@email.com', '416-555-4000', '321 Maple Ave');

INSERT INTO Patient (OhipID, FirstName, LastName, DateOfBirth, Sex, Height, Weight, Email, Phone, Address)
VALUES (1005, 'David', 'Chen', '1988-12-03', 'Male', 170, 75, 'david.chen@email.com', '416-555-5000', '654 Elm St');


INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2001, 'Alice', 'Wong', 'Physician', 'alice.wong@clinic.com', '647-555-1111', '12 Clinic Rd', 'Active', 120000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2002, 'Robert', 'King', 'Nurse', 'robert.king@clinic.com', '647-555-2222', '14 Clinic Rd', 'Active', 70000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2003, 'Karen', 'Lopez', 'Receptionist', 'karen.lopez@clinic.com', '647-555-3333', '15 Clinic Rd', 'Active', 45000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2004, 'James', 'White', 'Physician', 'james.white@clinic.com', '647-555-4444', '16 Clinic Rd', 'Absence', 110000);

INSERT INTO Staff (StaffID, FirstName, LastName, Role, Email, Phone, Address, EmploymentStatus, Salary)
VALUES (2005, 'Linda', 'Patel', 'Nurse', 'linda.patel@clinic.com', '647-555-5555', '17 Clinic Rd', 'Retired', 65000);


INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, MedicationType)
VALUES (3001, 1001, 2001, 'Amoxicillin', '500mg', '3 times daily for 7 days', '2024-02-01', 'Antibiotic');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, MedicationType)
VALUES (3002, 1002, 2002, 'Cetirizine', '10mg', 'Once daily', '2024-02-03', 'Antihistamine');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, MedicationType)
VALUES (3003, 1003, 2001, 'Atorvastatin', '20mg', 'Once daily', '2024-02-10', 'Cholesterol');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, MedicationType)
VALUES (3004, 1004, 2004, 'Ibuprofen', '400mg', 'As needed', '2024-02-15', 'Pain Relief');

INSERT INTO Prescription (PrescriptionID, OhipID, StaffID, Medication, Dose, Timeframe, DateIssued, MedicationType)
VALUES (3005, 1005, 2001, 'Metformin', '500mg', 'Twice daily', '2024-02-21', 'Diabetes');


INSERT INTO MedicationInfo (Medication, Instructions, SideEffects)
VALUES ('Amoxicillin', 'Take with food', 'Nausea, diarrhea, rash');

INSERT INTO MedicationInfo (Medication, Instructions, SideEffects)
VALUES ('Cetirizine', 'Take in the morning', 'Drowsiness');

INSERT INTO MedicationInfo (Medication, Instructions, SideEffects)
VALUES ('Ibuprofen', 'Take after meals', 'Stomach irritation, dizziness');

INSERT INTO MedicationInfo (Medication, Instructions, SideEffects)
VALUES ('Atorvastatin', 'Take at bedtime', 'Muscle pain, headache');

INSERT INTO MedicationInfo (Medication, Instructions, SideEffects)
VALUES ('Metformin', 'Take with evening meal', 'Upset stomach, metallic taste');


INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4001, 1001, 'Y', 'General Checkup', 0, 'OHIP', '2024-02-01');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4002, 1002, 'N', 'Allergy Test', 120, 'Credit', '2024-02-03');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4003, 1003, 'Y', 'Blood Work', 0, 'OHIP', '2024-02-10');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4004, 1004, 'N', 'X-Ray', 200, 'Debit', '2024-02-15');

INSERT INTO Billing (BillingID, OhipID, OhipCoverage, Service, Cost, PaymentMethod, PaymentDate)
VALUES (4005, 1005, 'N', 'Diabetes Consultation', 150, 'Cash', '2024-02-21');


INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5001, 1001, 2001, '2024-02-01', 'Completed', 'Sore throat', 'Prescribed antibiotics');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5002, 1002, 2002, '2024-02-03', 'Completed', 'Allergies', 'Antihistamine given');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5003, 1003, 2001, '2024-02-10', 'Completed', 'High cholesterol', 'Medication adjusted');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5004, 1004, 2004, '2024-02-15', 'Completed', 'Back pain', 'Pain relief suggested');

INSERT INTO Appointment (AppointmentID, OhipID, StaffID, DateAndTime, Status, ReasonForVisit, Result)
VALUES (5005, 1005, 2001, '2024-02-21', 'Scheduled', 'Blood sugar issues', NULL);


INSERT INTO MedicalRecord (OhipID, Allergies, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (1001, 'Peanuts', 'Throat swab', 'Tetanus, Flu Shot', 'Amoxicillin', 'No major history');

INSERT INTO MedicalRecord (OhipID, Allergies, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (1002, 'Pollen', 'Allergy test', 'COVID vaccine', 'Cetirizine', 'Mother has asthma');

INSERT INTO MedicalRecord (OhipID, Allergies, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (1003, NULL, 'Blood test', 'Flu Shot', 'Atorvastatin', 'Family heart issues');

INSERT INTO MedicalRecord (OhipID, Allergies, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (1004, 'Dust', 'X-Ray', 'None', 'Ibuprofen', 'No major conditions');

INSERT INTO MedicalRecord (OhipID, Allergies, Procedures, Vaccinations, PastMedication, FamilyHistory)
VALUES (1005, NULL, 'Blood test', 'Flu Shot', 'Metformin', 'Family history of diabetes');


INSERT INTO Diagnoses (DiagnosisID, OhipID, Diagnosis)
VALUES (6001, 1001, 'Asthma');

INSERT INTO Diagnoses (DiagnosisID, OhipID, Diagnosis)
VALUES (6002, 1002, 'Seasonal Allergies');

INSERT INTO Diagnoses (DiagnosisID, OhipID, Diagnosis)
VALUES (6003, 1003, 'High Cholesterol');

INSERT INTO Diagnoses (DiagnosisID, OhipID, Diagnosis)
VALUES (6004, 1004, 'Muscle Strain');

INSERT INTO Diagnoses (DiagnosisID, OhipID, Diagnosis)
VALUES (6005, 1005, 'Type 2 Diabetes');

COMMIT;