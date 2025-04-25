<?php
$conn = new mysqli("localhost", "root", "", "hub");
$classes = $conn->query("SELECT * FROM classes");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Attendance Register View</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body class="p-4">

    <div class="container">
        <h3>Attendance Register</h3>
        <div class="form-group">
            <label>Select Class</label>
            <select id="classSelect" class="form-control">
                <option value="">-- Select --</option>
                <?php while ($row = $classes->fetch_assoc()): ?>
                    <option value="<?= $row['class_id'] ?>"><?= $row['branch'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div id="registerTable">
            <!-- Attendance table will load here -->
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // When the user selects a class
        $('#classSelect').on('change', function() {
            const classId = $(this).val();
            if (classId) {
                // Send the selected class_id to the backend to fetch attendance data
                $.post("sapi/af.php", {
                    class_id: classId
                }, function(data) {
                    $("#registerTable").html(data);

                    // After loading data, bind the click event to .att-cell elements
                    bindAttendanceClick();
                });
            } else {
                $("#registerTable").html('');
            }
        });

        // Function to bind click event on attendance cells
        function bindAttendanceClick() {
            document.querySelectorAll('.att-cell').forEach(cell => {
                cell.addEventListener('click', function() {
                    let current = this.textContent.trim();

                    // Determine next status based on current status
                    let next = '';
                    if (current === 'Present') {
                        next = 'Absent';
                    } else if (current === 'Absent') {
                        next = '-';
                    } else {
                        next = 'Present';
                    }

                    // Update cell text to reflect the new status
                    this.textContent = next;

                    // Send the updated attendance status to the backend
                    fetch('sapi/au.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `student_id=${this.dataset.student}&date=${this.dataset.date}&status=${next}`
                    });
                });
            });
        }
    </script>

</body>

</html>