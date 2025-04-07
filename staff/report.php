<?php
session_start();
include("../connect/config.php");

// Ensure Only Admin Can Access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>

    <link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/rr.css">
    <link rel="stylesheet" href="staff.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>

    <div class="overlay"></div>

    <!-- Header -->
    <header class="header">
        <div class="logo">Academic Hub</div>
        <nav class="navbar">
            <a href="staff.php">Home</a>
            <a href="atten.php">Attendance</a>
            <a href="mark.php">Marks</a>
            <a href="stud_manage.php">Student-Managent</a>
            <a href="feedback.php">Feedback</a>
            <a href="report.php">Reports</a>
            <a href="profile.php"><img src="../img/avt/<?php echo $_SESSION['avt']; ?>.png" alt="Profile" style="vertical-align: middle;  height: 30px;  width: 30px;  object-fit: cover;  border-radius: 50%;">
                <span class="profile-text">Profile</span>
            </a>
        </nav>
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </header>


    <!-- Main Content -->
    <div class="container">

        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="staff.php">Dashboard</a></li>
                <li><a href="atten.php">Attendance</a></li>
                <li><a href="mark.php">Marks</a></li>
                <li><a href="stud_manage.php">Student-Management</a></li>
                <li><a href="feedback.php">Feedback</a></li>
                <li><a href="report.php">Reports</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </aside>

        <section id="reports">
            <h2>Generate Student Reports</h2>
            <div class="filters">
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
                <table id="reportTable" class="display">
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

            <div class="export-buttons">
                <button onclick="printReport()">Print Report</button>
                <button id="exportPDF" onclick="exportToPDF()">Export as PDF</button>
                <button id="exportExcel" onclick="exportToExcel()">Export as Excel</button>
            </div>
        </section>

    </div>

    <footer class="footer">
        <p>&copy; 2025 Academic Hub. All rights reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>

    <script src="staff.js"></script>

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

            fetch("fetch.php", {
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
                        tableHeader.innerHTML = `<th>Roll No</th><th>Student Name</th><th>Marks (%)</th><th>Phone</th>`;
                        tableBody.innerHTML = data.map(student => `
                <tr>
                    <td>${student.roll_number || '-'}</td>
                    <td>${student.username || '-'}</td>
                    <td>${isNaN(parseFloat(student.marks_percentage)) ? 'N/A' : parseFloat(student.marks_percentage).toFixed(2) + '%'}</td>
                    <td>${student.phone || '-'}</td>
                </tr>
            `).join("");
                    } else if (reportType === "attendance") {
                        tableHeader.innerHTML = `<th>Roll No</th><th>Student Name</th><th>Attendance (%)</th><th>Phone</th>`;
                        tableBody.innerHTML = data.map(student => `
                <tr>
                    <td>${student.roll_number || '-'}</td>
                    <td>${student.username || '-'}</td>
                    <td>${isNaN(parseFloat(student.attendance_percentage)) ? 'N/A' : parseFloat(student.attendance_percentage).toFixed(2) + '%'}</td>
                    <td>${student.phone || '-'}</td>
                </tr>
            `).join("");
                    } else {
                        tableHeader.innerHTML = `<th>Roll No</th><th>Student Name</th><th>Marks (%)</th><th>Attendance (%)</th><th>Average (%)</th><th>Phone</th>`;
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

</body>

</html>