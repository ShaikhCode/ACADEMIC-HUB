<?php
session_start();
include("../connect/config.php");

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$onboardingCompleted = 0;
$pageno = 9;
// page checker ONBOARDING START IF DONE
$sql = "SELECT * FROM admins WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
$check_b = isset($data['check_b']) ? trim($data['check_b']) : '';
$completed_pages = array_filter(array_map('trim', explode(',', $check_b))); // Clean and split values


$page_no = strval($pageno); // Ensure it's a string

// Debugging logs
echo "<script>console.log('Fetched check_b value: " . addslashes(json_encode($check_b)) . "');</script>";
echo "<script>console.log('Completed pages array: " . addslashes(json_encode($completed_pages)) . "');</script>";
echo "<script>console.log('Checking page_no: " . addslashes(json_encode($page_no)) . " (Type: " . gettype($page_no) . ")');</script>";



// Check if page_no exists in completed pages
if (in_array($page_no, $completed_pages, true)) {
    $onboardingCompleted = 1;
}
echo "<script>console.log('Onboarding Completed: " . addslashes(json_encode($onboardingCompleted)) . "');</script>";
error_log("Onboarding Completed: " . var_export($onboardingCompleted, true));


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/rr.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- Intro.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">

    <!-- Intro.js JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
</head>

<body>
    <div class="overlay"></div>
    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar" data-intro=" Here's your navigation menu" data-step="1">
            <a href="admin.php">Home</a>
            <a href="addstaff.php">Staff-Manage</a>
            <a href="addstud.php">Student-Manage</a>
            <a href="addclass.php">Class-Manage</a>
            <a href="addsub.php">Subjects/Exams</a>
            <a href="reports.php">Report</a>
            <a href="profile.php"><img src="../img/avt/<?php echo $_SESSION['avt']; ?>.png" alt="Profile" style="vertical-align: middle;  height: 30px;  width: 30px;  object-fit: cover;  border-radius: 50%;">
                <span class="profile-text">Profile</span>
            </a>
        </nav>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="admin.php">Dashboard</a></li>
                <li><a href="addstaff.php">Staff-Manage</a></li>
                <li><a href="addstud.php">Student-Manage</a></li>
                <li><a href="addclass.php">Class-Organization</a></li>
                <li><a href="addsub.php">Subjects/Exams ADD</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="feedback.php">Feedback-Review</a></li>
            </ul>
        </aside>

        <section id="reports">
            <h2>Generate Student Reports</h2>
            <div class="filters" data-intro="Here all filter available to shorted the students" data-step="3">
                <label for="typeFilter">Type:</label>
                <select id="typeFilter">
                    <option value="both" selected>Both</option>
                    <option value="marks">Marks Only</option>
                    <option value="attendance">Attendance Only</option>
                </select>

                <label for="classFilter">Class:</label>
                <select id="classFilter">
                    <option value="">All Classes</option>
                    <?php
                    $c_id = $_SESSION['college_id'];
                    $result = $conn->query("SELECT class_id, branch FROM classes WHERE college_id='$c_id'");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['class_id']}'>{$row['branch']}</option>";
                    }
                    ?>
                </select>

                <label for="minPercentage">Min %:</label>
                <input type="number" id="minPercentage" min="0" max="100" placeholder="0">

                <label for="maxPercentage">Max %:</label>
                <input type="number" id="maxPercentage" min="0" max="100" placeholder="100">

                <button onclick="fetchReports()">Filter</button>
            </div>

            <div class="tbl" style="overflow-x: auto;">
                <table id="reportTable" class="display" data-intro="Here the Report Table will Display" data-step="4">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Average</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="export-buttons" data-intro="Here are opption How U want to to export the Data displayed" data-step="5">
                <button onclick="printReport()">Print Report</button>
                <button id="exportPDF" onclick="exportToPDF()">Export as PDF</button>
                <button id="exportExcel" onclick="exportToExcel()">Export as Excel</button>
            </div>
        </section>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>

    <!-- Onboarding Modal -->
    <div id="onboarding-modal" class="modal">
        <div class="modal-content">
            <h2>Welcome to Academic Hub!</h2>
            <p>Let's take a guided tour of your Reports Generate Page.</p>
            <button id="closee" onclick="startOnboarding()" style="margin: 10px;">Start Tour</button>
        </div>
    </div>
    <!-- Place this where your JS is -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const onboardingCompleted = <?php echo json_encode($onboardingCompleted == 0); ?>;

            if (onboardingCompleted) {
                document.getElementById("onboarding-modal").style.display = 'block';
            }
            const currentPag = <?php echo $pageno; ?>;

            document.getElementById('closee').addEventListener('click', function() {
                document.getElementById("onboarding-modal").style.display = 'none';
            });

            function startOnboarding() {
                const intro = introJs();
                document.getElementById("onboarding-modal").style.display = 'none';

                intro.oncomplete(function() {
                    sendCompletionStatus(currentPag);
                });

                intro.onexit(function() {
                    sendCompletionStatus(currentPag);
                });

                intro.start();
            }

            function sendCompletionStatus(pageNumber) {
                fetch('api/update.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded' // Because you're using $_POST, not json
                        },
                        body: new URLSearchParams({
                            page_no: pageNumber
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Update successful:', data);
                    })
                    .catch(error => {
                        console.error('Error updating onboarding status:', error);
                    });
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
    <script src="admin.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("minPercentage").addEventListener("input", filterTable);
            document.getElementById("maxPercentage").addEventListener("input", filterTable);
        });

        function fetchReports() {
            let class_id = document.getElementById("classFilter").value;
            let minPercentage = document.getElementById("minPercentage").value || 0;
            let maxPercentage = document.getElementById("maxPercentage").value || 100;
            let reportType = document.getElementById("typeFilter").value;
            let tableBody = document.querySelector("#reportTable tbody");

            fetch("fetch_reports.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `class_id=${class_id}&min_percentage=${minPercentage}&max_percentage=${maxPercentage}&type=${reportType}`
                })
                .then(response => response.json())
                .then(data => {
                    console.log("API Response:", data);
                    tableBody.innerHTML = "";

                    if (data.error) {
                        tableBody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center;">⚠️ ${data.error}</td></tr>`;
                        return;
                    }

                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">🚫 No records found</td></tr>`;
                        return;
                    }

                    let tableHeader = document.querySelector("#reportTable thead tr");

                    if (reportType === "marks") {
                        tableHeader.innerHTML = `<th>Roll No</th><th>Student Name</th><th>Marks </th><th>Phone</th>`;
                        tableBody.innerHTML = data.map(student => `
                <tr>
                    <td>${student.roll_number || '-'}</td>
                    <td>${student.username || '-'}</td>
                    <td>${isNaN(parseFloat(student.marks_percentage)) ? 'N/A' : parseFloat(student.marks_percentage).toFixed(2) + '%'}</td>
                    <td>${student.phone || '-'}</td>
                </tr>
            `).join("");
                    } else if (reportType === "attendance") {
                        tableHeader.innerHTML = `<th>Roll No</th><th>Student Name</th><th>Attendance </th><th>Phone</th>`;
                        tableBody.innerHTML = data.map(student => `
                <tr>
                    <td>${student.roll_number || '-'}</td>
                    <td>${student.username || '-'}</td>
                    <td>${isNaN(parseFloat(student.attendance_percentage)) ? 'N/A' : parseFloat(student.attendance_percentage).toFixed(2) + '%'}</td>
                    <td>${student.phone || '-'}</td>
                </tr>
            `).join("");
                    } else {
                        tableHeader.innerHTML = `<th>Roll No</th><th>Student Name</th><th>Marks</th><th>Attendance </th><th>Average</th><th>Phone</th>`;
                        tableBody.innerHTML = data.map(student => {
                            let marks = parseFloat(student.marks_percentage) || 0;
                            let attendance = parseFloat(student.attendance_percentage) || 0;
                            let average = ((marks + attendance) / 2).toFixed(2);

                            return `
                    <tr>
                        <td>${student.roll_number || '-'}</td>
                        <td>${student.username || '-'}</td>
                        <td>${isNaN(marks) ? 'N/A' : marks.toFixed(2) + '%'}</td>
                        <td>${isNaN(attendance) ? 'N/A' : attendance.toFixed(2) + '%'}</td>
                        <td>${isNaN(average) ? 'N/A' : average + '%'}</td>
                        <td>${student.phone || '-'}</td>
                    </tr>
                `;
                        }).join("");
                    }
                    filterTable();
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        function filterTable() {
            let min = parseFloat(document.getElementById("minPercentage").value) || 0;
            let max = parseFloat(document.getElementById("maxPercentage").value) || 100;

            document.querySelectorAll("#reportTable tbody tr").forEach(row => {
                let avgCell = row.cells.length === 6 ? row.cells[4] : row.cells[2];
                let avgValue = parseFloat(avgCell.textContent.replace('%', '')) || 0;

                if (avgValue >= min && avgValue <= max) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>

    <script>
        //Printing Functions
        function printReport() {
            let reportTable = document.getElementById("reportTable").outerHTML;
            let newWindow = window.open("", "_blank");

            newWindow.document.write(`
        <html>
        <head>
            <title>Academic Report</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid black; padding: 8px; text-align: center; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h2>Student Report</h2>
            ${reportTable}
        </body>
        </html>
    `);

            newWindow.document.close();
            newWindow.focus();
            newWindow.print();
            newWindow.close();
        }

        function exportToPDF() {
            let table = document.getElementById("reportTable");
            let rows = table.querySelectorAll("tr");

            let data = [];
            rows.forEach(row => {
                let rowData = [];
                row.querySelectorAll("th, td").forEach(cell => rowData.push(cell.innerText));
                data.push(rowData);
            });

            let docDefinition = {
                content: [{
                        text: 'Student Report',
                        style: 'header'
                    },
                    {
                        table: {
                            headerRows: 1,
                            widths: Array(data[0].length).fill('*'),
                            body: data
                        }
                    }
                ],
                styles: {
                    header: {
                        fontSize: 18,
                        bold: true,
                        margin: [0, 0, 0, 10]
                    }
                },
                defaultStyle: {
                    font: 'Roboto' // ✅ Use 'Roboto' which is available in pdfMake
                }
            };

            pdfMake.createPdf(docDefinition).download("Student_Report.pdf");
        }



        function exportToExcel() {
            let table = document.getElementById("reportTable");
            let ws = XLSX.utils.table_to_sheet(table);
            let wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "Student_Report.xlsx");
        }
    </script>
    <script>
        const currentPage = <?php echo $pageno; ?>;

        function startOnboarding() {
            const intro = introJs();

            intro.oncomplete(function() {
                sendCompletionStatus(currentPage);
            });

            intro.onexit(function() {
                sendCompletionStatus(currentPage);
            });

            intro.start();
        }

        function sendCompletionStatus(pageNumber) {
            fetch('api/update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded' // Because you're using $_POST, not json
                    },
                    body: new URLSearchParams({
                        page_no: pageNumber
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Update successful:', data);
                })
                .catch(error => {
                    console.error('Error updating onboarding status:', error);
                });

        }
    </script>



</body>

</html>