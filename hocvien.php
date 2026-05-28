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

/* XÓA HỌC VIÊN */
if(isset($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id='$del_id' AND role='student'");
    header("Location: hocvien.php");
    exit();
}

/* ĐỔI TÊN HỌC VIÊN */
if(isset($_POST['update_name'])){
    $u_id = (int)$_POST['user_id'];
    $new_name = mysqli_real_escape_string($conn, $_POST['new_name']);
    mysqli_query($conn, "UPDATE users SET full_name='$new_name' WHERE user_id='$u_id'");
    $msg = "Đã cập nhật tên học viên!";
}

/* ĐỔI MẬT KHẨU HỌC VIÊN */
if(isset($_POST['update_password'])){
    $u_id = (int)$_POST['user_id'];
    $new_pw = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE users SET password='$new_pw' WHERE user_id='$u_id'");
    $msg = "Đã đặt lại mật khẩu thành công!";
}

$students = mysqli_query($conn, "SELECT * FROM users WHERE role='student' ORDER BY user_id DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Học viên</title>
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
    .card { background:white; border-radius:20px; padding:25px; margin-bottom:35px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    table { width:100%; border-collapse:collapse; margin-top: 20px; }
    table th { background:#f8fafc; color:#475569; padding:15px; text-align:left; font-size: 14px; border-bottom: 2px solid #e2e8f0; }
    table td { padding:15px; border-bottom:1px solid #f1f5f9; color: #334155; }
    
    .btn-action { background: #3b82f6; color: white; padding: 6px 10px; border:none; border-radius: 6px; font-size: 13px; cursor: pointer; margin-right: 5px; }
    .btn-action.pw { background: #8b5cf6; }
    .btn-action.del { background: #ef4444; text-decoration: none; }

    /* MODAL */
    .modal { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:none; align-items:center; justify-content:center; z-index:9999; }
    .modal.active { display:flex; }
    .modal-box { width:400px; background:white; padding:30px; border-radius:20px; animation: fadeIn 0.2s; }
    @keyframes fadeIn { from{opacity:0; transform:scale(0.95);} to{opacity:1; transform:scale(1);} }
    .modal-box input { width:100%; padding:12px; border-radius:8px; border:1px solid #ccc; margin-bottom:15px; margin-top: 10px;}
    .modal-buttons { display:flex; gap:10px; }
    .btn-confirm { flex:1; background:#2563eb; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; }
    .btn-close { flex:1; background:#ef4444; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TNU Admin</h2>
    <a href="admin.php">📊 Tổng quan (Dashboard)</a>
    <a href="danhsachkhoahoc.php">📚 Quản lý Khóa học</a>
    <a href="giangvien.php">👨‍🏫 Quản lý Giảng viên</a>
    <a href="hocvien.php" class="active">🎓 Quản lý Học viên</a>
    <a href="caidat.php">⚙️ Cài đặt Hệ thống</a>
    <a href="index.php">🏠 Về trang chủ User</a>
</div>

<div class="main">
    <h1 style="margin-bottom: 30px; color: #0f172a;">Quản lý Học viên</h1>
    
    <?php if(isset($msg)) echo "<div style='background:#dcfce7; color:#16a34a; padding:15px; border-radius:10px; margin-bottom:20px;'>$msg</div>"; ?>

    <div class="card">
        <h3>Danh Sách Học Viên Hệ Thống</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Họ và Tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Ngày tạo</th>
                <th>Thao tác Admin</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($students)){ ?>
            <tr>
                <td>#<?php echo $row['user_id']; ?></td>
                <td><strong><?php echo $row['full_name']; ?></strong></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <button class="btn-action" onclick="openNameModal(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES); ?>')">Đổi tên</button>
                    <button class="btn-action pw" onclick="openPwModal(<?php echo $row['user_id']; ?>)">Cấp lại Pass</button>
                    <a href="?delete=<?php echo $row['user_id']; ?>" class="btn-action del" onclick="return confirm('Xóa học viên sẽ xóa mọi đăng ký khóa học của họ. Chắc chắn xóa?');">Xóa</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

<div class="modal" id="nameModal">
    <div class="modal-box">
        <form method="POST">
            <h3>Đổi tên Học viên</h3>
            <input type="hidden" name="user_id" id="edit_user_id">
            <input type="text" name="new_name" id="edit_name" required>
            <div class="modal-buttons">
                <button type="submit" name="update_name" class="btn-confirm">Lưu</button>
                <button type="button" class="btn-close" onclick="closeModals()">Hủy</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="pwModal">
    <div class="modal-box">
        <form method="POST">
            <h3>Cấp lại Mật khẩu</h3>
            <p style="font-size:13px; color:#64748b; margin-top:5px;">Nhập mật khẩu mới cho học viên này.</p>
            <input type="hidden" name="user_id" id="pw_user_id">
            <input type="text" name="new_password" placeholder="Mật khẩu mới..." required>
            <div class="modal-buttons">
                <button type="submit" name="update_password" class="btn-confirm">Đổi Pass</button>
                <button type="button" class="btn-close" onclick="closeModals()">Hủy</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNameModal(id, currentName) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_name').value = currentName;
        document.getElementById('nameModal').classList.add('active');
    }
    
    function openPwModal(id) {
        document.getElementById('pw_user_id').value = id;
        document.getElementById('pwModal').classList.add('active');
    }

    function closeModals() {
        document.getElementById('nameModal').classList.remove('active');
        document.getElementById('pwModal').classList.remove('active');
    }
</script>

</body>
</html>