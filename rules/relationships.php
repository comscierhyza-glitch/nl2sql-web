<?php

return [

    "students" => [

        "courses" => [

            "type" => "INNER JOIN",
            "left" => "course_id",
            "right" => "id"

        ],

        "grades" => [

            "type" => "INNER JOIN",
            "left" => "id",
            "right" => "student_id"

        ]

    ],

    "employees" => [
        "departments" => [
            "type" => "INNER JOIN",
            "left" => "department_id",
            "right" => "id"
        ],
        
        "employee_projects" => [
            "type" => "INNER JOIN",
            "left" => "id",
            "right" => "employee_id"
        ]
    ],
    "employee_projects" => [
        "projects" => [
            "type" => "INNER JOIN",
            "left" => "project_id",
            "right" => "id"
        ]
    ]

];
