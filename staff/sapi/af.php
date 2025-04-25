<?php
session_start();

include('../../connect/config.php');

if (isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];

    // ✅ Fetch class and subject name
    $class_info = $conn->query("SELECT branch as class_name FROM classes 
                                WHERE class_id = '$class_id'")
        ->fetch_assoc();
    $sub_info = $conn->query("SELECT subject_name FROM subjects 
                                WHERE subject_id = '$subject_id'")
        ->fetch_assoc();

    $class_subject_title = $class_info['class_name'] . ' - ' . $sub_info['subject_name'];

    // ✅ Display the heading
    echo "<h3 style='text-align: center; margin-bottom: 20px;'>$class_subject_title</h3>";
    
 // ✅ Add Export Button
    echo '<button id="exportExcel" class="mbtn" style="margin-top: 20px;">Export as Excel</button>';
    // Fetch students
    $students = [];
    $student_query = $conn->query("SELECT s.user_id as id,s.roll_number as roll_no, u.username as name FROM students s 
                                    INNER JOIN users u ON s.user_id=u.user_id 
                                    WHERE s.class_id = '$class_id' 
                                    ORDER BY CONVERT(s.roll_number, UNSIGNED INTEGER) ASC");
    while ($row = $student_query->fetch_assoc()) {
        $students[] = $row;
    }

    // Fetch attendance dates
    $dates = [];
    $date_query = $conn->query("SELECT DISTINCT date FROM attendance 
                                WHERE student_id IN (SELECT user_id FROM students WHERE class_id = '$class_id') 
                                AND subject_id=$subject_id 
                                ORDER BY date ASC");
    while ($row = $date_query->fetch_assoc()) {
        $dates[] = $row['date'];
    }

    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered">';
    echo '<thead><tr><th>Roll No</th><th>Name</th>';
    foreach ($dates as $date) {
        echo "<th>" . date("d M", strtotime($date)) . "</th>";
    }
    echo '</tr></thead><tbody>';

    foreach ($students as $student) {
        echo "<tr>";
        echo "<td>{$student['roll_no']}</td>";
        echo "<td>{$student['name']}</td>";

        foreach ($dates as $date) {
            $status_query = $conn->query("SELECT status FROM attendance 
                                          WHERE student_id = '{$student['id']}' AND date = '$date'");
            $status = ($status_query->num_rows > 0) ? $status_query->fetch_assoc()['status'] : '-';
        
            $student_id = $student['id']; // Extract once for cleaner short tags
            ?>
            <td class="att-cell"
                data-student="<?= $student_id ?>"
                data-date="<?= $date ?>"
                data-class="<?= $_POST['class_id'] ?>"
                data-subject="<?= $_POST['subject_id'] ?>">
                <?= $status ?>
            </td>
            <?php
        }
        

        echo "</tr>";
    }

    echo '</tbody></table>';
    echo '</div>';

   

    // ✅ JavaScript to export table
    echo '
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
    document.getElementById("exportExcel").addEventListener("click", function () {
        const table = document.querySelector("table");
        const wb = XLSX.utils.table_to_book(table, { sheet: "Attendance" });
        XLSX.writeFile(wb, "attendance.xlsx");
    });
    </script>
    ';
}
