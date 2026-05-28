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

// RESET LỊCH SỬ ĐĂNG KÝ
if(isset($_POST['reset_registrations'])){
    // Xóa tất cả dữ liệu trong bảng registrations, đưa ID auto_increment về lại từ đầu
    mysqli_query($conn, "TRUNCATE TABLE registrations");
    $msg = "Đã dọn dẹp toàn bộ dữ liệu Đăng ký khóa học!";
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Cài đặt Hệ thống</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
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
    
    .danger-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #fee2e2; border-left: 5px solid #ef4444; }
    .danger-card h3 { color: #ef4444; font-size: 20px; margin-bottom: 10px; }
    .danger-card p { color: #475569; font-size: 15px; margin-bottom: 20px; line-height: 1.6; }
    
    .btn-danger { background: #ef4444; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 15px; transition: 0.2s; }
    .btn-danger:hover { background: #dc2626; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TNU Admin</h2>
    <a href="admin.php">📊 Tổng quan (Dashboard)</a>
    <a href="danhsachkhoahoc.php">📚 Quản lý Khóa học</a>
    <a href="giangvien.php">👨‍🏫 Quản lý Giảng viên</a>
    <a href="hocvien.php">🎓 Quản lý Học viên</a>
    <a href="caidat.php" class="active">⚙️ Cài đặt Hệ thống</a>
    <a href="index.php">🏠 Về trang chủ User</a>
</div>

<div class="main">
    <h1 style="margin-bottom: 30px; color: #0f172a;">Cài đặt & Dọn dẹp Dữ liệu</h1>

    <?php if(isset($msg)) echo "<div style='background:#dcfce7; color:#16a34a; padding:15px; border-radius:10px; margin-bottom:20px;'>$msg</div>"; ?>

    <div class="danger-card">
        <h3>Khôi phục học kỳ mới (Xóa lịch sử đăng ký)</h3>
        <p>Hành động này sẽ xóa <b>TOÀN BỘ</b> dữ liệu trong bảng <code>registrations</code> (Bao gồm các yêu cầu đăng ký, chuyển khóa, hủy khóa...). <br>Danh sách Học viên, Khóa học và Giảng viên vẫn được giữ nguyên. Chỉ nên thực hiện khi bắt đầu một học kỳ mới.</p>
        
        <form method="POST" onsubmit="return confirm('CẢNH BÁO: Dữ liệu bị xóa sẽ KHÔNG THỂ khôi phục. Bạn có thực sự chắc chắn muốn dọn dẹp hệ thống đăng ký?');">
            <button type="submit" name="reset_registrations" class="btn-danger">⚠️ Thực hiện Dọn dẹp Dữ liệu</button>
        </form>
    </div>
</div>

</body>
</html>