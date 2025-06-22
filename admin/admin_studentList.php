<?php
require_once '../includes/session_check.php';
require_once '../config/connect.php';
include '../includes/header.php';

// Build filter query
$where_conditions = [];
$params = [];
$types = "";

// Class filter
if (!empty($_GET['class_filter']) && $_GET['class_filter'] !== 'all') {
    $where_conditions[] = "student.student_class = ?";
    $params[] = $_GET['class_filter'];
    $types .= "i";
}

// Gender filter
if (!empty($_GET['gender_filter']) && $_GET['gender_filter'] !== 'all') {
    $where_conditions[] = "student.gender = ?";
    $params[] = $_GET['gender_filter'];
    $types .= "s";
}

// Year filter (based on IC - first 2 digits represent birth year)
if (!empty($_GET['year_filter']) && $_GET['year_filter'] !== 'all') {
    $year_prefix = $_GET['year_filter'];
    $where_conditions[] = "student.student_ic LIKE ?";
    $params[] = $year_prefix . "%";
    $types .= "s";
}

// Search filter
if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $where_conditions[] = "(student.student_fname LIKE ? OR student.student_ic LIKE ? OR student.matrix LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

// Build final query
$sql = "SELECT student.*, class.class_name FROM student INNER JOIN class ON class.class_id = student.student_class";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY student.student_fname ASC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = mysqli_query($conn, $sql);
}

// Get filter options
$classes_query = "SELECT class_id, class_name FROM class ORDER BY class_name";
$classes_result = mysqli_query($conn, $classes_query);

// Get available years from student ICs
$years_query = "SELECT DISTINCT SUBSTRING(student_ic, 1, 2) as year_prefix FROM student ORDER BY year_prefix DESC";
$years_result = mysqli_query($conn, $years_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Senarai Pelajar - SRIAAWP ActivHub</title>
    <link rel="stylesheet" href="../assets/css/teacherList.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico">
    <style>
        /* Filter Section Styles */
        .filter-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .filter-section h3 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 18px;
        }
        
        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn-gray {
            background-color: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-gray:hover {
            background-color: #5a6268;
            text-decoration: none;
            color: white;
        }
        
        /* Import Section Styles */
        .import-section {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .import-section h3 {
            margin: 0 0 15px 0;
            color: #0056b3;
            font-size: 18px;
        }
        
        .template-downloads {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #b3d7ff;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        /* Results Summary Styles */
        .results-summary {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .results-summary p {
            margin: 0;
            color: #155724;
            font-size: 14px;
        }
        
        .results-summary p:not(:last-child) {
            margin-bottom: 8px;
        }
        
        /* Enhanced Student Card Styles */
        .teacher-card {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s ease;
        }
        
        .teacher-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .student-info {
            flex: 1;
        }
        
        .student-info p {
            margin: 0 0 8px 0;
            line-height: 1.5;
        }
        
        .student-info p:last-child {
            margin-bottom: 0;
        }
        
        .student-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 80px;
        }
        
        .credentials {
            font-weight: bold;
            color: #666;
        }
        
        /* No Results Styling */
        .no-results {
            text-align: center;
            padding: 40px 20px;
            border: 2px dashed #ddd;
            background: #f8f9fa;
        }
        
        .no-results-content {
            max-width: 300px;
            margin: 0 auto;
        }
        
        .no-results-content p {
            margin: 8px 0;
            color: #666;
        }
        
        .no-results-content p:first-of-type {
            font-size: 18px;
            color: #333;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
            
            .filter-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-actions .btn-blue,
            .filter-actions .btn-gray {
                justify-content: center;
            }
            
            .teacher-card {
                flex-direction: column;
                gap: 15px;
            }
            
            .student-actions {
                flex-direction: row;
                justify-content: flex-start;
                min-width: auto;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="logo-section">
            <img src="../assets/img/logo.png" alt="Logo" />
            <div class="logo-text">
                <span>SRIAAWP ActivHub</span>
                <?php include '../includes/navlinks.php'; ?>
            </div>
        </div>
        <div class="icon-section">
            <div class="user-section">
                <?php
                if (isset($_SESSION['user_role'])) {
                    if ($_SESSION['user_role'] === 'admin') {
                        echo '<span class="admin-text">' . strtoupper($_SESSION['admin_name'] ?? 'ADMIN') . '</span><br>';
                    }
                }
                ?>
                <span class="welcome-text">Selamat Kembali!</span>
            </div>
            <span class="material-symbols-outlined icon">notifications</span>
        </div>
    </header>

    <div class="container">
        <div class="teacher-list-container">
            <div class="teacher-list-box">
                <div class="title-bar">
                    <h2>SENARAI MURID</h2>
                    <div class="button-group">
                        <button class="btn-yellow" onclick="window.location.href='admin_add_student.php'">Tambah Pelajar Baru</button>
                        <button class="btn-red" onclick="location.href='admin_dashboard.php'">Batal</button>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h3>Filter Pelajar</h3>
                    <form method="GET" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="search">Cari:</label>
                                <input type="text" id="search" name="search" placeholder="Nama, IC, atau Matrik" 
                                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label for="class_filter">Kelas:</label>
                                <select id="class_filter" name="class_filter">
                                    <option value="all">Semua Kelas</option>
                                    <?php while ($class = mysqli_fetch_assoc($classes_result)): ?>
                                        <option value="<?= $class['class_id'] ?>" 
                                                <?= (isset($_GET['class_filter']) && $_GET['class_filter'] == $class['class_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class['class_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="gender_filter">Jantina:</label>
                                <select id="gender_filter" name="gender_filter">
                                    <option value="all">Semua Jantina</option>
                                    <option value="L" <?= (isset($_GET['gender_filter']) && $_GET['gender_filter'] == 'L') ? 'selected' : '' ?>>Lelaki</option>
                                    <option value="P" <?= (isset($_GET['gender_filter']) && $_GET['gender_filter'] == 'P') ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="year_filter">Tahun Lahir:</label>
                                <select id="year_filter" name="year_filter">
                                    <option value="all">Semua Tahun</option>
                                    <?php while ($year = mysqli_fetch_assoc($years_result)): 
                                        $full_year = "20" . $year['year_prefix']; // Convert to full year
                                        if ($year['year_prefix'] >= 50) $full_year = "19" . $year['year_prefix']; // Handle years before 2000
                                    ?>
                                        <option value="<?= $year['year_prefix'] ?>" 
                                                <?= (isset($_GET['year_filter']) && $_GET['year_filter'] == $year['year_prefix']) ? 'selected' : '' ?>>
                                            <?= $full_year ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn-blue">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="admin_studentList.php" class="btn-gray">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Import Section -->
                <div class="import-section">
                    <h3>Import Data</h3>
                    <form action="function/import_excel.php" method="post" enctype="multipart/form-data">
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required>
                        <button type="submit" class="btn-yellow">Import Excel</button>
                    </form>
                    
                    <!-- Fallback CSV option if Excel doesn't work -->
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; color: #666;">Alternative: CSV Import (if Excel import fails)</summary>
                        <form action="function/import_csv.php" method="post" enctype="multipart/form-data" style="margin-top: 10px;">
                            <input type="file" name="csv_file" accept=".csv" required>
                            <button type="submit" class="btn-yellow">Import CSV</button>
                        </form>
                    </details>
                    
                    <div class="template-downloads">
                        <a href="function/generate_template.php" class="btn-blue">
                            <i class="fas fa-download"></i> Download Excel Template
                        </a>
                        <a href="function/generate_csv_template.php" class="btn-blue">
                            <i class="fas fa-download"></i> Download CSV Template
                        </a>
                    </div>
                </div>

                <!-- Results Summary -->
                <div class="results-summary">
                    <?php 
                    $total_students = mysqli_num_rows($result);
                    echo "<p><strong>Menunjukkan $total_students pelajar</strong></p>";
                    
                    // Show active filters
                    $active_filters = [];
                    if (!empty($_GET['search'])) $active_filters[] = "Carian: '" . htmlspecialchars($_GET['search']) . "'";
                    if (!empty($_GET['class_filter']) && $_GET['class_filter'] !== 'all') {
                        mysqli_data_seek($classes_result, 0);
                        while ($class = mysqli_fetch_assoc($classes_result)) {
                            if ($class['class_id'] == $_GET['class_filter']) {
                                $active_filters[] = "Kelas: " . $class['class_name'];
                                break;
                            }
                        }
                    }
                    if (!empty($_GET['gender_filter']) && $_GET['gender_filter'] !== 'all') {
                        $gender_map = ['L' => 'Lelaki', 'P' => 'Perempuan'];
                        $active_filters[] = "Jantina: " . ($gender_map[$_GET['gender_filter']] ?? $_GET['gender_filter']);
                    }
                    if (!empty($_GET['year_filter']) && $_GET['year_filter'] !== 'all') {
                        $year_display = $_GET['year_filter'] >= 50 ? "19" . $_GET['year_filter'] : "20" . $_GET['year_filter'];
                        $active_filters[] = "Tahun: " . $year_display;
                    }
                    
                    if (!empty($active_filters)) {
                        echo "<p><strong>Filter aktif:</strong> " . implode(", ", $active_filters) . "</p>";
                    }
                    ?>
                </div>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        // Format gender display
                        $gender_display = '';
                        switch(strtoupper($row['gender'])) {
                            case 'L': $gender_display = 'Lelaki'; break;
                            case 'P': $gender_display = 'Perempuan'; break;
                            default: $gender_display = $row['gender']; break;
                        }
                        
                        // Determine birth year from IC
                        $ic_year = substr($row['student_ic'], 0, 2);
                        $birth_year = $ic_year >= 50 ? "19" . $ic_year : "20" . $ic_year;
                    ?>
                        <div class="teacher-card" id="<?= $row['student_ic'] ?>">
                            <div class="student-info">
                                <p><strong><?= htmlspecialchars($row['student_fname']) ?></strong></p>
                                <p><strong>Kelas:</strong> <?= htmlspecialchars($row['class_name']) ?></p>
                                <p><strong>Matrik:</strong> <?= htmlspecialchars($row['matrix']) ?></p>
                                <p><strong>Jantina:</strong> <?= $gender_display ?></p>
                                <p><strong>Tahun Lahir:</strong> <?= $birth_year ?></p>
                                <p><span class="credentials">Nombor Kad Pengenalan:</span> <?= htmlspecialchars($row['student_ic']) ?></p>
                            </div>
                            <div class="student-actions">
                                <button class="edit-button" onclick="edit(<?= $row['student_ic'] ?>)">Edit</button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="teacher-card no-results">
                        <div class="no-results-content">
                            <i class="fas fa-search" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <p><strong>Tiada Rekod Dijumpai</strong></p>
                            <p>Tiada pelajar yang sepadan dengan kriteria carian anda.</p>
                            <p>Cuba ubah filter atau reset carian.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div id="importResultModal" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Import Results</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="result-grid">
                    <div class="result-item success">
                        <div class="result-icon">✅</div>
                        <div class="result-text">
                            <span id="success-count">0</span> students added
                        </div>
                    </div>
                    <div class="result-item warning">
                        <div class="result-icon">⚠️</div>
                        <div class="result-text">
                            <span id="invalid-count">0</span> invalid rows
                        </div>
                    </div>
                    <div class="result-item info">
                        <div class="result-icon">🔄</div>
                        <div class="result-text">
                            <span id="duplicate-count">0</span> duplicates skipped
                        </div>
                    </div>
                    <div class="result-item error">
                        <div class="result-icon">❌</div>
                        <div class="result-text">
                            <span id="fail-count">0</span> failed inserts
                        </div>
                    </div>
                </div>
                <div id="error-details" class="error-details"></div>
            </div>
            <div class="modal-footer">
                <button class="modal-close-btn">OK</button>
            </div>
        </div>
    </div>



    <script>
        function edit(id) {
            fetch('function/get_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById(id).innerHTML = result.message;
                });
        }

        function cancel(id) {
            fetch('function/student_list.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id
                    })
                })
                .then(response => response.json())
                .then(result => {
                    document.getElementById(id).innerHTML = result.message;
                });
        }

        function save(id) {
            var name = document.getElementsByName("edit_name_" + id)[0].value;
            var password = document.getElementsByName("edit_password_" + id)[0].value;
            var class1 = document.getElementsByName("class_" + id)[0].value;

            if (name == "" || class1 == "" || id == "") {
                alert("Please fill all fields!");
                return;
            }

            const data = {
                id,
                name,
                password,
                class1
            };

            fetch('function/student_update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    alert(result.status == 1 ? "Updated successfully!" : "Update failed!");
                    document.getElementById(id).innerHTML = result.message;
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['import_result'])) : ?>
                const result = <?php echo json_encode($_SESSION['import_result']); ?>;
                const modal = document.getElementById('importResultModal');

                // Ensure modal exists
                if (modal) {
                    // Populate data
                    if (result.success !== undefined) document.getElementById('success-count').textContent = result.success;
                    if (result.invalid !== undefined) document.getElementById('invalid-count').textContent = result.invalid;
                    if (result.duplicate !== undefined) document.getElementById('duplicate-count').textContent = result.duplicate;
                    if (result.fail !== undefined) document.getElementById('fail-count').textContent = result.fail;

                    // Show error details if any
                    if (result.errors && result.errors.length > 0) {
                        const errorContainer = document.getElementById('error-details');
                        if (errorContainer) {
                            errorContainer.innerHTML = '<h4>Error Details:</h4><ul>' +
                                result.errors.map(err => `<li>${err}</li>`).join('') + '</ul>';
                        }
                    }

                    // Show modal
                    modal.style.display = 'block';

                    // Close handlers
                    const closeModal = () => modal.style.display = 'none';

                    document.querySelector('.close-modal')?.addEventListener('click', closeModal);
                    document.querySelector('.modal-close-btn')?.addEventListener('click', closeModal);

                    window.addEventListener('click', function(event) {
                        if (event.target == modal) {
                            closeModal();
                        }
                    });

                    // Clear session data
                    fetch('function/clear_import_result.php', {
                            method: 'POST'
                        })
                        .catch(err => console.error('Error clearing session:', err));
                }
            <?php
                unset($_SESSION['import_result']);
            endif;
            ?>
        });
    </script>
</body>

</html>