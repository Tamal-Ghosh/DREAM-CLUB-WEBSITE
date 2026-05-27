CREATE TABLE dbo.users (
    id INT IDENTITY(1, 1) PRIMARY KEY,
    full_name NVARCHAR(150) NOT NULL,
    email NVARCHAR(150) NOT NULL UNIQUE,
    role NVARCHAR(20) NOT NULL,
    password_hash NVARCHAR(255) NOT NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);

CREATE TABLE dbo.requests (
    id INT IDENTITY(1, 1) PRIMARY KEY,
    request_code NVARCHAR(20) NULL,
    patient_name NVARCHAR(150) NOT NULL,
    blood_group NVARCHAR(10) NOT NULL,
    hospital NVARCHAR(150) NOT NULL,
    location NVARCHAR(150) NULL,
    contact_number NVARCHAR(30) NOT NULL,
    units_needed INT NOT NULL,
    urgency_level NVARCHAR(20) NOT NULL,
    details NVARCHAR(500) NULL,
    status NVARCHAR(20) NOT NULL DEFAULT 'Pending',
    created_by_user_id INT NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    CONSTRAINT FK_requests_users FOREIGN KEY (created_by_user_id) REFERENCES dbo.users(id)
);
