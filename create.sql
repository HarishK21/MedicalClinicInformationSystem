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

CREATE TABLE MedicationInfo (
    Medication VARCHAR(100) PRIMARY KEY,
    Instructions VARCHAR(100) NOT NULL,
    SideEffects VARCHAR(255)
);

CREATE TABLE Prescription (
    PrescriptionID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    StaffID INT NOT NULL,
    Medication VARCHAR(100) NOT NULL,
    Dose VARCHAR(100),
    Timeframe VARCHAR(100),
    DateIssued DATE NOT NULL,
    MedicationType VARCHAR(100),
    FOREIGN KEY (OhipID) REFERENCES Patient (OhipID) ON DELETE CASCADE,
    FOREIGN KEY (StaffID) REFERENCES Staff (StaffID) ON DELETE CASCADE
);

CREATE TABLE Billing (
    BillingID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    OhipCoverage Char(1) DEFAULT 'Y' CHECK (OhipCoverage IN ('Y', 'N')),
    Service VARCHAR(100) NOT NULL,
    Cost INT CHECK (Cost >= 0) NOT NULL,
    PaymentMethod VARCHAR(100) CHECK (PaymentMethod IN ('Credit', 'Debit', 'Cash', 'OHIP')),
    PaymentDate DATE,
    FOREIGN KEY (OhipID) REFERENCES Patient (OhipID) ON DELETE CASCADE
);

CREATE TABLE Appointment (
    AppointmentID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    StaffID INT NOT NULL,
    DateAndTime DATE NOT NULL,
    Status VARCHAR(100) DEFAULT 'Scheduled' CHECK (Status IN ('Cancelled', 'Scheduled', 'Completed', 'No-Show')) NOT NULL,
    ReasonForVisit VARCHAR(100),
    Result VARCHAR(100),
    FOREIGN KEY (OhipID) REFERENCES Patient (OhipID) ON DELETE CASCADE,
    FOREIGN KEY (StaffID) REFERENCES Staff (StaffID) ON DELETE CASCADE
); 

CREATE TABLE MedicalRecord (
    OhipID INT NOT NULL,
    Allergies VARCHAR(100),
    Procedures VARCHAR(200),
    Vaccinations VARCHAR(100),
    PastMedication VARCHAR(200),
    FamilyHistory VARCHAR(200),
    FOREIGN KEY (OhipID) REFERENCES Patient(OhipID) ON DELETE CASCADE
);

CREATE TABLE Diagnoses (
    DiagnosisID INT PRIMARY KEY,
    OhipID INT NOT NULL,
    Diagnosis VARCHAR(100),
    FOREIGN KEY (OhipID) REFERENCES Patient(OhipID) ON DELETE CASCADE
);

COMMIT;