<?php
include 'config.php';

// Fetch Total Count of Staff, Students, and Classes
function getTotalCount($table, $id, $column = "*")
{
    global $conn;
    $query = "SELECT COUNT($column) as total FROM $table WHERE college_id='$id'";
    $result = $conn->query($query);
    return $result ? $result->fetch_assoc()['total'] : 0;
}

// Fetch Staff Data (Limit 3 for Dashboard)
function getStaffList($id, $limit = 3)
{
    global $conn;
    $query = "SELECT users.username, staff.department, staff.phone 
          FROM users 
          INNER JOIN staff ON users.user_id = staff.user_id 
          WHERE users.college_id='$id'
          LIMIT $limit";
    return $conn->query($query);
}

// Fetch Student Data (Limit 3 for Dashboard)
function getStudentList($id, $limit = 3)
{
    global $conn;
    $query = "SELECT users.username, students.roll_number, students.phone 
          FROM users 
          INNER JOIN students ON users.user_id = students.user_id 
          WHERE users.college_id='$id'
          LIMIT $limit";

    return $conn->query($query);
}

// Fetch Class Data (Limit 3 for Dashboard)
function getClassList($id, $limit = 3)
{
    global $conn;
    $query = "SELECT * FROM classes WHERE college_id='$id' LIMIT $limit";
    return $conn->query($query);
    

}
