DROP TABLE Computers;
DROP TABLE Timetable;
DROP TABLE Rooms;
DROP TABLE Users;
DROP TABLE Teachers;
DROP TABLE Updates;

CREATE TABLE Computers (
    computer_id integer  NOT NULL,
    room_ref integer  NOT NULL,
    "index" char(2)  NOT NULL,
    state varchar2(8)  NOT NULL,
    user_ref varchar2(30)  NULL,
    CONSTRAINT state_check CHECK (state IN ('linux', 'windows', 'macintosh', 'off')),
    CONSTRAINT Computers_pk PRIMARY KEY (computer_id)
) ;

CREATE TABLE Rooms (
    room_id integer  NOT NULL,
    color varchar2(10)  NOT NULL,
    cpu varchar2(60)  NOT NULL,
    gpu varchar2(30)  NOT NULL,
    ram integer  NOT NULL,
    dvd_drive char(1)  NOT NULL,
    CONSTRAINT dvd_drive_check CHECK (dvd_drive IN ('Y', 'N')),
    CONSTRAINT Rooms_pk PRIMARY KEY (room_id)
) ;

CREATE TABLE Teachers (
    teacher_id integer  NOT NULL,
    name varchar2(30)  NOT NULL,
    surname varchar2(30)  NOT NULL,
    CONSTRAINT Teachers_pk PRIMARY KEY (teacher_id)
) ;

CREATE TABLE Timetable (
    course_id integer  NOT NULL,
    name varchar2(60)  NOT NULL,
    start_time date  NOT NULL,
    end_time date  NOT NULL,
    day varchar2(10)  NOT NULL,
    week_type varchar2(5)  NOT NULL,
    room_ref integer  NOT NULL,
    teacher_ref integer,
    CONSTRAINT week_type_check CHECK (week_type IN ('all', 'odd', 'even')),
    CONSTRAINT Timetable_pk PRIMARY KEY (course_id)
) ;

CREATE TABLE Users (
    login varchar2(30)  NULL,
    name varchar2(30)  NOT NULL,
    surname varchar2(30)  NOT NULL,
    CONSTRAINT Users_pk PRIMARY KEY (login)
) ;

CREATE TABLE Updates (
	update_time date NOT NULL
) ;

ALTER TABLE Computers ADD CONSTRAINT Computers_Users
    FOREIGN KEY (user_ref)
    REFERENCES Users (login);

ALTER TABLE Timetable ADD CONSTRAINT Timetable_Teachers
    FOREIGN KEY (teacher_ref)
    REFERENCES Teachers (teacher_id);

ALTER TABLE Computers ADD CONSTRAINT Rooms_Computers
    FOREIGN KEY (room_ref)
    REFERENCES Rooms (room_id);

ALTER TABLE Timetable ADD CONSTRAINT Rooms_Timetable
    FOREIGN KEY (room_ref)
    REFERENCES Rooms (room_id);

-- ROOMS DATA --
INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (2041, 'red', 'Intel Core i5-4570, 3,2GHz', 'Nvidia GeForce GT755M', 8, 'N');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (2042, 'pink', 'Intel Xeon E3-1240v6 3,70GHz', 'Nvidia Quadro P400', 16, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (2043, 'orange', 'Intel Xeon E3-1240v6 3,70GHz', 'Nvidia Quadro P400', 16, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (2044, 'brown', 'Intel Xeon E3-1240v6 3,70GHz', 'Nvidia Quadro P400', 16, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (2045, 'yellow', 'Intel Core i7 870 2,93GHz/Intel Xeon E3 1270 3,4GHz', 'Nvidia Quadro NVS 295/300', 4, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (3041, 'khaki', 'Intel Core Quad Q9550 2,8GHz', 'Nvidia GeForce 9500 GT', 4, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (3042, 'green', 'Intel Xeon E3-1220 3,1GHz', 'Nvidia Quadro NVS 315', 8, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (3043, 'cyan', 'Intel Core 2 Duo E8400 3GHz', 'Nvidia Quadro NVS 290', 2, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (3044, 'blue', 'Intel Core 2 Duo E8400 3GHz', 'Nvidia Quadro NVS 290', 4, 'Y');

INSERT INTO Rooms 
    (room_id, color, cpu, gpu, ram, dvd_drive)
VALUES 
    (3045, 'violet', 'Intel Core 2 Duo E8400 3GHz', 'Nvidia Quadro NVS 290', 4, 'Y');
