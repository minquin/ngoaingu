<?php
session_start();
include 'connect.php';

/* =========================
   KIỂM TRA ĐĂNG NHẬP ĐỘC LẬP
========================= */
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] != 'admin'){
    header("Location: login_admin.php");
    exit();
}

/* =========================
   XỬ LÝ DUYỆT / HỦY
========================= */
if(isset($_GET['approve'])){
    $id = (int) $_GET['approve'];
    mysqli_query($conn,"UPDATE registrations SET status='approved' WHERE registration_id='$id'");
    header("Location: admin.php"); exit();
}

if(isset($_GET['cancel'])){
    $id = (int) $_GET['cancel'];
    mysqli_query($conn,"UPDATE registrations SET status='cancelled' WHERE registration_id='$id'");
    header("Location: admin.php"); exit();
}

if(isset($_GET['approve_transfer'])){
    $id = (int) $_GET['approve_transfer'];
    $get = mysqli_query($conn,"SELECT * FROM registrations WHERE registration_id='$id'");
    $row = mysqli_fetch_assoc($get);
    if($row){
        $new_course = $row['transfer_course_id'];
        mysqli_query($conn,"UPDATE registrations SET course_id='$new_course', request_status='approved', transfer_course_id=NULL WHERE registration_id='$id'");
    }
    header("Location: admin.php"); exit();
}

if(isset($_GET['reject_transfer'])){
    $id = (int) $_GET['reject_transfer'];
    mysqli_query($conn,"UPDATE registrations SET request_status='rejected' WHERE registration_id='$id'");
    header("Location: admin.php"); exit();
}

/* =========================
   TRUY VẤN DỮ LIỆU CHÍNH
========================= */
$students = mysqli_query($conn,"
    SELECT r.registration_id, r.status, u.full_name, u.email, c.course_name
    FROM registrations r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN courses c ON r.course_id = c.course_id
    ORDER BY r.registration_id DESC
");

$requests = mysqli_query($conn,"
    SELECT r.registration_id, r.change_request, r.request_date, r.request_status, u.full_name, c1.course_name AS old_course, c2.course_name AS new_course
    FROM registrations r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN courses c1 ON r.course_id = c1.course_id
    INNER JOIN courses c2 ON r.transfer_course_id = c2.course_id
    WHERE r.transfer_course_id IS NOT NULL
    ORDER BY r.request_date DESC
");

// Lấy thống kê nhanh
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='student'"))['c'];
$total_courses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM courses"))['c'];
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='teacher'"))['c'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - TNU English</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { background:#f4f7fb; display:flex; min-height: 100vh; }
    
    .sidebar { 
        width:260px; 
        background: linear-gradient(180deg, #2563eb, #06b6d4); 
        padding:30px 20px; 
        position:fixed; 
        height:100%; 
        color:white; 
        box-shadow: 4px 0 15px rgba(0,0,0,0.05);
    }
    .sidebar h2 { color:white; margin-bottom:35px; font-size:26px; text-align: center; font-weight: 700; text-shadow: 1px 1px 3px rgba(0,0,0,0.2); }
    .sidebar a { display:flex; align-items: center; gap: 12px; text-decoration:none; color:rgba(255,255,255,0.85); padding:14px 18px; border-radius:12px; margin-bottom:10px; transition:0.3s; font-weight: 500; }
    .sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.2); color:white; }
    .sidebar a.logout { margin-top: 50px; background: rgba(239, 68, 68, 0.9); color: white; justify-content: center; }
    .sidebar a.logout:hover { background: #dc2626; }
    
    .main { margin-left:260px; width:calc(100% - 260px); padding:40px; }
    .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .title { font-size:30px; color:#0f172a; font-weight: 600; }
    
    .widgets { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 35px; }
    .widget-card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; }
    .widget-info h4 { color: #64748b; font-size: 14px; margin-bottom: 8px; font-weight: 500; }
    .widget-info h2 { color: #0f172a; font-size: 28px; }
    .widget-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .icon-blue { background: #e0f2fe; color: #0ea5e9; }
    .icon-green { background: #dcfce7; color: #22c55e; }
    .icon-purple { background: #f3e8ff; color: #a855f7; }

    .card { background:white; border-radius:20px; padding:25px; margin-bottom:35px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow-x: auto; }
    .card h3 { margin-bottom:20px; font-size:20px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; }
    table { width:100%; border-collapse:collapse; white-space: nowrap; }
    table th { background:#f8fafc; color:#475569; padding:15px; text-align:left; font-size: 14px; font-weight: 600; text-transform: uppercase; }
    table td { padding:15px; border-bottom:1px solid #f1f5f9; color: #334155; font-size: 15px; }
    
    .status { padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; text-transform: uppercase; }
    .pending { background:#fef3c7; color:#d97706; }
    .approved { background:#dcfce7; color:#16a34a; }
    .cancelled, .rejected { background:#fee2e2; color:#dc2626; }
    
    .btn { border:none; padding:8px 14px; border-radius:8px; cursor:pointer; color:white; font-weight:500; font-size: 13px; transition:0.2s; }
    .btn:hover { transform:translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-approve { background:#2563eb; }
    .btn-reject { background:#ef4444; }
    .action-wrap { display:flex; gap:8px; }
    .done-text { color:#16a34a; font-weight:600; font-size: 14px; }
    .cancel-text { color:#dc2626; font-weight:600; font-size: 14px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TNU Admin</h2>
    <a href="admin.php" class="active">📊 Tổng quan (Dashboard)</a>
    <a href="danhsachkhoahoc.php">📚 Quản lý Khóa học</a>
    <a href="giangvien.php">👨‍🏫 Quản lý Giảng viên</a>
    <a href="hocvien.php">🎓 Quản lý Học viên</a>
    <a href="caidat.php">⚙️ Cài đặt Hệ thống</a>
    <a href="index.php">🏠 Về trang chủ User</a>
    <a href="logout.php" class="logout">Đăng xuất</a>
</div>

<div class="main">
    <div class="header-top">
        <h1 class="title">Xin chào, Quản trị viên! 👋</h1>
    </div>

    <div class="widgets">
        <div class="widget-card">
            <div class="widget-info">
                <h4>Tổng Học Viên</h4>
                <h2><?php echo $total_users; ?></h2>
            </div>
            <div class="widget-icon icon-blue">🎓</div>
        </div>
        <div class="widget-card">
            <div class="widget-info">
                <h4>Tổng Khóa Học</h4>
                <h2><?php echo $total_courses; ?></h2>
            </div>
            <div class="widget-icon icon-purple">📚</div>
        </div>
        
        <div class="widget-card">
            <div class="widget-info">
                <h4>Tổng Giảng Viên</h4>
                <h2><?php echo $total_teachers; ?></h2>
            </div>
            <div class="widget-icon icon-green">👨‍🏫</div>
        </div>
    </div>

    <div class="card">
        <h3>Duyệt đăng ký khóa học</h3>
        <table>
            <tr>
                <th>ID</th><th>Học viên</th><th>Email</th><th>Khóa học</th><th>Trạng thái</th><th>Hành động</th>
            </tr>
            <?php while($s = mysqli_fetch_assoc($students)){ ?>
            <tr>
                <td>#<?php echo $s['registration_id']; ?></td>
                <td><strong><?php echo $s['full_name']; ?></strong></td>
                <td><?php echo $s['email']; ?></td>
                <td><?php echo $s['course_name']; ?></td>
                <td><span class="status <?php echo $s['status']; ?>"><?php echo $s['status']; ?></span></td>
                <td>
                    <div class="action-wrap">
                        <?php if($s['status'] == 'pending'){ ?>
                            <a href="?approve=<?php echo $s['registration_id']; ?>"><button class="btn btn-approve">Phê duyệt</button></a>
                            <a href="?cancel=<?php echo $s['registration_id']; ?>"><button class="btn btn-reject">Từ chối</button></a>
                        <?php } elseif($s['status'] == 'approved'){ ?>
                            <span class="done-text">Đã duyệt</span>
                            <a href="?cancel=<?php echo $s['registration_id']; ?>"><button class="btn btn-reject">Hủy bỏ</button></a>
                        <?php } elseif($s['status'] == 'cancelled' || $s['status'] == 'rejected'){ ?>
                            <span class="cancel-text">Đã hủy/Từ chối</span>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <div class="card">
        <h3>Yêu cầu chuyển khóa</h3>
        <table>
            <tr>
                <th>ID</th><th>Học viên</th><th>Khóa cũ</th><th>Khóa mới</th><th>Lý do</th><th>Hành động</th>
            </tr>
            <?php while($r = mysqli_fetch_assoc($requests)){ ?>
            <tr>
                <td>#<?php echo $r['registration_id']; ?></td>
                <td><strong><?php echo $r['full_name']; ?></strong></td>
                <td><span style="color:#ef4444; font-weight:500;"><?php echo $r['old_course']; ?></span></td>
                <td><span style="color:#22c55e; font-weight:500;"><?php echo $r['new_course']; ?></span></td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;"><?php echo $r['change_request']; ?></td>
                <td>
                    <div class="action-wrap">
                        <?php if($r['request_status'] == 'pending'){ ?>
                            <a href="?approve_transfer=<?php echo $r['registration_id']; ?>"><button class="btn btn-approve">Chấp nhận</button></a>
                            <a href="?reject_transfer=<?php echo $r['registration_id']; ?>"><button class="btn btn-reject">Từ chối</button></a>
                        <?php } elseif($r['request_status'] == 'approved'){ ?>
                            <span class="done-text">Đã chuyển</span>
                        <?php } elseif($r['request_status'] == 'rejected'){ ?>
                            <span class="cancel-text">Đã từ chối</span>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>