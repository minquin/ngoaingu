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

/* THÊM KHÓA HỌC MỚI */
if(isset($_POST['add_course'])){
    $code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $name = mysqli_real_escape_string($conn, $_POST['course_name']);
    $lang = mysqli_real_escape_string($conn, $_POST['language']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    $weeks = (int)$_POST['duration_weeks'];
    $price = (float)$_POST['price'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $image = mysqli_real_escape_string($conn, $_POST['image_url']);
    // Phân công giảng viên
    $teacher_id = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : 'NULL';

    $query = "INSERT INTO courses (course_code, course_name, language, level, duration_weeks, price, description, image, teacher_id) 
              VALUES ('$code', '$name', '$lang', '$level', '$weeks', '$price', '$desc', '$image', $teacher_id)";
    
    if(mysqli_query($conn, $query)){
        $msg = "Thêm khóa học thành công!";
    } else {
        $err = "Lỗi: Mã khóa học có thể bị trùng!";
    }
}

/* XÓA KHÓA HỌC */
if(isset($_GET['delete'])){
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM courses WHERE course_id='$del_id'");
    header("Location: danhsachkhoahoc.php");
    exit();
}

// Lấy danh sách khóa học
$courses = mysqli_query($conn, "
    SELECT c.*, u.full_name as teacher_name 
    FROM courses c 
    LEFT JOIN users u ON c.teacher_id = u.user_id 
    ORDER BY c.course_id DESC
");

// Lấy danh sách giảng viên để đưa vào form chọn
$teachers = mysqli_query($conn, "SELECT user_id, full_name FROM users WHERE role='teacher'");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý Khóa học</title>
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
    
    /* STYLE CHO FORM */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; color: #475569; font-size: 14px; font-weight: 500; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; }
    .btn-submit { background: #2563eb; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 15px; margin-top: 10px; }
    .btn-del { background: #ef4444; color: white; padding: 6px 12px; text-decoration: none; border-radius: 6px; font-size: 13px; }
</style>
</head>
<body>

<div class="sidebar">
    <h2>TNU Admin</h2>
    <a href="admin.php">📊 Tổng quan (Dashboard)</a>
    <a href="danhsachkhoahoc.php" class="active">📚 Quản lý Khóa học</a>
    <a href="giangvien.php">👨‍🏫 Quản lý Giảng viên</a>
    <a href="hocvien.php">🎓 Quản lý Học viên</a>
    <a href="caidat.php">⚙️ Cài đặt Hệ thống</a>
    <a href="index.php">🏠 Về trang chủ User</a>
</div>

<div class="main">
    <h1 style="margin-bottom: 30px; color: #0f172a;">Quản lý Khóa học</h1>
    
    <?php if(isset($msg)) echo "<div style='background:#dcfce7; color:#16a34a; padding:15px; border-radius:10px; margin-bottom:20px;'>$msg</div>"; ?>
    <?php if(isset($err)) echo "<div style='background:#fee2e2; color:#dc2626; padding:15px; border-radius:10px; margin-bottom:20px;'>$err</div>"; ?>

    <div class="card">
        <h3 style="margin-bottom: 20px; font-size: 20px;">Thêm Khóa Học Mới</h3>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Mã Khóa (Ví dụ: IT01)</label>
                    <input type="text" name="course_code" required>
                </div>
                <div class="form-group">
                    <label>Tên Khóa Học</label>
                    <input type="text" name="course_name" required>
                </div>
                <div class="form-group">
                    <label>Ngôn Ngữ (hoặc Ngành)</label>
                    <input type="text" name="language" required>
                </div>
                <div class="form-group">
                    <label>Cấp độ (B1, HSK4, Cơ bản...)</label>
                    <input type="text" name="level" required>
                </div>
                <div class="form-group">
                    <label>Thời lượng (Số tuần)</label>
                    <input type="number" name="duration_weeks" required>
                </div>
                <div class="form-group">
                    <label>Giá tiền (VNĐ)</label>
                    <input type="number" name="price" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Phân công Giảng viên phụ trách</label>
                <select name="teacher_id">
                    <option value="">-- Chưa phân công --</option>
                    <?php while($t = mysqli_fetch_assoc($teachers)){ ?>
                        <option value="<?php echo $t['user_id']; ?>"><?php echo $t['full_name']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Mô tả chi tiết</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Link Ảnh Banner (Nhập URL hình ảnh)</label>
                <input type="text" name="image_url" placeholder="https://images.unsplash.com/...">
            </div>
            
            <button type="submit" name="add_course" class="btn-submit">+ Lưu Khóa Học</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-bottom: 20px; font-size: 20px;">Danh Sách Khóa Học Đang Mở</h3>
        <table>
            <tr>
                <th>Mã</th>
                <th>Tên Khóa Học</th>
                <th>Giảng viên</th>
                <th>Giá (VNĐ)</th>
                <th>Hành động</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($courses)){ ?>
            <tr>
                <td><strong><?php echo $row['course_code']; ?></strong></td>
                <td><?php echo $row['course_name']; ?></td>
                <td><?php echo $row['teacher_name'] ? $row['teacher_name'] : '<span style="color:#94a3b8;font-style:italic;">Chưa phân công</span>'; ?></td>
                <td style="color:#2563eb; font-weight:600;"><?php echo number_format($row['price']); ?>đ</td>
                <td>
                    <a href="?delete=<?php echo $row['course_id']; ?>" class="btn-del" onclick="return confirm('Bạn chắc chắn muốn xóa khóa học này?');">Xóa</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>