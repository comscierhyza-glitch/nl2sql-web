<?php

$joinRelationships = [

    "students:courses" =>
        "students.course_id = courses.id",

    "students:grades" =>
        "students.id = grades.student_id",

    "employees:departments" =>
        "employees.department_id = departments.id",

    "orders:customers" =>
        "orders.customer_id = customers.id",

];