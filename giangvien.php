<?php
session_start();
include 'connect.php';

/* =========================
   KIỂM TRA ĐĂNG NHẬP ĐỘC LẬP (DÀNH CHO ADMIN)
========================= */
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] != 'admin'){
    // Nếu không có session của admin, lập tức đuổi về trang login của admin
    header("Location: login_admin.php");
    exit();
}

/* THÊM GIẢNG VIÊN */
if(isset($_POST['add_teacher'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $fullname = mysqli_real_escape_string($conn, $_POST['full_name']);
    $password = $_POST['password'];
    
    // Băm mật khẩu
    $hashed_pw = password_hash($password, PASSWORD_DEFAULT);

    // Thêm vào bảng users với role='teacher'
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email'");
    if(mysqli_num_rows($check) > 0){
        $err = "Tên đăng nhập hoặc Email đã tồn tại!";
    } else {
        mysqli_query($conn, "INSERT INTO users (username, email, password, full_name, role) VALUES ('$username', '$email', '$hashed_pw', '$fullname', 'teacher')");
        $msg = "Thêm giảng viên thành công!";
    }
}

/* XÓA GIẢNG VIÊN */
if(isset($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    // Khi xóa giảng viên, nếu database có khóa ngoại ON DELETE SET NULL, 
    // các khóa học do GV này dạy sẽ tự động chuyển teacher_id thành NULL.
    mysqli_query($conn, "DELETE FROM users WHERE user_id='$del_id' AND role='teacher'");
    header("Location: giangvien.php");
    exit();
}

$teachers = mysqli_query($conn, "SELECT * FROM users WHERE role='teacher' ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Giảng viên</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
    /* Dùng chung CSS cấu trúc Admin */
    * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
    body { background:#f4f7fb; display:flex; min-height: 100vh; }
    /* SIDEBAR MÀU XANH GRADIENT ĐỒNG BỘ */
    .sidebar { 
        width:260px; 
        background: linear-gradient(180deg, #2563eb, #06b6d4); 
        padding:30px 20px; 
        position:fixed; 
        height:100%; 
        color:white; 
        box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        z-index: 100; /* Giữ sidebar nổi lên trên, không bị modal che mất */
    }
    .sidebar h2 { 
        color:white; 
        margin-bottom:35px; 
        font-size:26px; 
        text-align: center; 
        font-weight: 700; 
        text-shadow: 1px 1px 3px rgba(0,0,0,0.2); 
    }
    .sidebar a { 
        display:flex; 
        align-items: center; 
        gap: 12px; 
        text-decoration:none; 
        color:rgba(255,255,255,0.85); 
        padding:14px 18px; 
        border-radius:12px; 
        margin-bottom:10px; 
        transition:0.3s; 
        font-weight: 500; 
    }
    .sidebar a:hover, .sidebar a.active { 
        background:rgba(255,255,255,0.2); 
        color:white; 
    }
    /* Dành cho nút Đăng xuất nếu bạn có thêm vào */
    .sidebar a.logout { 
        margin-top: 50px; 
        background: rgba(239, 68, 68, 0.9); 
        color: white; 
        justify-content: center; 
    }
    .sidebar a.logout:hover { 
        background: #dc2626; 
    }
    .main { margin-left:260px; width:calc(100% - 260px); padding:40px; }
    .card { background:white; border-radius:20px; padding:25px; margin-bottom:35px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    table { width:100%; border-collapse:collapse; margin-top: 20px; }
    table th { background:#f8fafc; color:#475569; padding:15px; text-align:left; font-size: 14px; border-bottom: 2px solid #e2e8f0; }
    table td { padding:15px; border-bottom:1px solid #f1f5f9; color: #334155; }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; color: #475569; font-size: 14px; font-weight: 500; }
    .form-group input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; }
    .btn-submit { background: #10b981; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 15px; margin-top: 10px; }
    .btn-del { background: #ef4444; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px; font-size: 13px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TNU Admin</h2>
    <a href="admin.php">📊 Tổng quan (Dashboard)</a>
    <a href="danhsachkhoahoc.php">📚 Quản lý Khóa học</a>
    <a href="giangvien.php" class="active">👨‍🏫 Quản lý Giảng viên</a>
    <a href="hocvien.php">🎓 Quản lý Học viên</a>
    <a href="caidat.php">⚙️ Cài đặt Hệ thống</a>
    <a href="index.php">🏠 Về trang chủ User</a>
</div>

<div class="main">
    <h1 style="margin-bottom: 30px; color: #0f172a;">Quản lý Giảng viên</h1>
    
    <?php if(isset($msg)) echo "<div style='background:#dcfce7; color:#16a34a; padding:15px; border-radius:10px; margin-bottom:20px;'>$msg</div>"; ?>
    <?php if(isset($err)) echo "<div style='background:#fee2e2; color:#dc2626; padding:15px; border-radius:10px; margin-bottom:20px;'>$err</div>"; ?>

    <div class="card">
        <h3 style="margin-bottom: 20px; font-size: 20px;">Cấp tài khoản Giảng viên</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Họ và Tên</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Email liên hệ</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Tên đăng nhập (Username)</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Mật khẩu khởi tạo</label>
                    <input type="password" name="password" required>
                </div>
            </div>
            <button type="submit" name="add_teacher" class="btn-submit">+ Thêm Giảng Viên</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px; font-size: 20px;">Danh Sách Giảng Viên</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Họ và Tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Hành động</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($teachers)){ ?>
            <tr>
                <td>#<?php echo $row['user_id']; ?></td>
                <td><strong><?php echo $row['full_name']; ?></strong></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td>
                    <a href="?delete=<?php echo $row['user_id']; ?>" class="btn-del" onclick="return confirm('Bạn chắc chắn muốn xóa giảng viên này?');">Xóa</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>