<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['global_role'] ?? '') === 'super_admin') {
    header('Location: ../admin/dashboard.php');
    exit;
}

if (!isset($_SESSION['structure_id'])) {
    header('Location: login.php');
    exit;
}

switch ($_SESSION['role']) {
    case 'structure_admin':
        header('Location: ../admin/dashboard.php');
        break;

    case 'teacher':
        header('Location: ../teacher/dashboard.php');
        break;

    case 'student':
        header('Location: ../student/dashboard.php');
        break;

    case 'parent':
        header('Location: ../parent/dashboard.php');
        break;

    default:
        header('Location: logout.php');
        break;
}

exit;
