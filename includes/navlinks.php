
<?php
if (!isset($user_role)) {
    if (isset($_SESSION['user_role'])) {
        $user_role = $_SESSION['user_role'];
    } else {
        $user_role = null;
    }
}
?>

<!--This file decides what appears in the header based on user role-->
    <div class="nav-links">
        <?php if ($user_role === 'admin'): ?>
            <a href="../admin/admin_dashboard.php">Papan Pemuka</a>
            <a href="../admin/admin_list.php">Senarai Admin</a>
            <a href="../teacher/teacherList.php">Senarai Guru-Guru</a>
            <a href="../admin/admin_studentList.php">Senarai Murid-Murid</a>
        <?php elseif ($user_role === 'teacher'): ?>
            <a href="../teacher/teacher_dashboard.php">Papan Pemuka</a>
            <a href="../forms/audit_history.php">Sejarah Borang</a>
            <a href="../teacher/teacher_profile.php">Profil Guru</a>
            <a href="../student/studentList.php">Senarai Pelajar</a>
            <a href="../forms/approve_form.php">Senarai Borang</a>
            <a href="../student/student_cocuactivityform.php">Tambah Aktiviti Murid</a>
            <a href="../events/add_events.php">Tambah Acara</a>
            <a href="../cocurricular/cocurricular_board.php">Papan Kokurikulum</a>
        <?php elseif ($user_role === 'student'): ?>
            <a href="../student/student_dashboard.php">Papan Pemuka</a>
            <a href="student_formhistory.php">Sejarah Borang</a>
            <a href="student_profile.php">Profil Murid</a>
            <a href="student_cocurricular.php">Profil & Aktiviti Kokurikulum</a>
            <a href="../cocurricular/cocurricular_board.php">Papan Kokurikulum</a>
        <?php else: ?>
            <a href="index.php">Laman Utama</a>
        <?php endif; ?>
    </div>
