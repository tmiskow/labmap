CREATE OR REPLACE VIEW current_week AS
    SELECT 
        CASE mod(to_number(to_char(SYSDATE, 'ww')), 2) 
            WHEN 0 THEN 'odd' 
            WHEN 1 THEN 'even' 
        END
    AS week_type
    FROM dual;

CREATE OR REPLACE VIEW current_timetable AS
    SELECT *
    FROM timetable
    WHERE end_time >= TO_DATE(TO_CHAR(SYSDATE, 'HH24:MI'), 'HH24:MI')
        AND day = trim(lower(to_char(SYSDATE, 'DAY')))
        AND (week_type = 'all' OR week_type = (SELECT week_type FROM current_week));

CREATE OR REPLACE VIEW next_courses AS
    SELECT room_ref AS room, MIN(start_time) AS min_time
    FROM current_timetable
    GROUP BY room_ref;

CREATE OR REPLACE VIEW current_course AS
    SELECT *
    FROM next_courses
    JOIN current_timetable
    ON next_courses.min_time = current_timetable.start_time
    AND next_courses.room = current_timetable.room_ref
    ORDER BY next_courses.room;

CREATE OR REPLACE VIEW full_current_timetable AS
    SELECT
        current_course.room_ref AS room_id,
        current_course.name AS course_name,
        current_course.start_time,
        current_course.end_time,
        current_course.week_type,
        teachers.name as teacher_name,
        teachers.surname AS teacher_surname
    FROM current_course
    JOIN teachers
    ON teachers.teacher_id = current_course.teacher_ref;

