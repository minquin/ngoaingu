<?php

session_start();
include 'connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

/* =========================
   KIỂM TRA QUYỀN ĐỂ HIỆN NÚT ADMIN Ở FOOOTER
========================= */
$role_query = mysqli_query($conn, "SELECT role FROM users WHERE user_id='$user_id'");
$user_data_db = mysqli_fetch_assoc($role_query);
$user_role = $user_data_db['role'];

/* =========================
   DARK MODE
========================= */

if(!isset($_SESSION['theme'])){
    $_SESSION['theme'] = 'light';
}

if(isset($_POST['toggle_theme'])){
    $_SESSION['theme'] = ($_SESSION['theme'] == 'light') ? 'dark' : 'light';
    header("Location: index.php");
    exit();
}

$isDark = $_SESSION['theme'] == 'dark';

/* =========================
   ĐỔI TÊN TÀI KHOẢN
========================= */
if(isset($_POST['change_name_submit'])){
    $new_name = mysqli_real_escape_string($conn, $_POST['new_name']);
    
    mysqli_query($conn, "UPDATE users SET full_name='$new_name' WHERE user_id='$user_id'");
    
    $_SESSION['full_name'] = $new_name;
    $full_name = $new_name; 
    $success = "Đổi tên tài khoản thành công!";
}

/* =========================
   ĐỔI MẬT KHẨU (Bcrypt)
========================= */
if(isset($_POST['change_password_submit'])){
    $current_pw = $_POST['current_password'];
    $new_pw = $_POST['new_password'];

    $query = mysqli_query($conn, "SELECT password FROM users WHERE user_id='$user_id'");
    
    if(mysqli_num_rows($query) > 0) {
        $user_data = mysqli_fetch_assoc($query);
        $hashed_password_db = $user_data['password'];

        if(password_verify($current_pw, $hashed_password_db)){
            $new_hashed_pw = password_hash($new_pw, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$new_hashed_pw' WHERE user_id='$user_id'");
            $success = "Đổi mật khẩu thành công!";
        } else {
            $error = "Mật khẩu hiện tại không đúng!";
        }
    }
}

/* =========================
   ĐĂNG KÝ KHÓA HỌC
========================= */

if(isset($_POST['register_course'])){
    $course_id = $_POST['course_id'];
    $check = mysqli_query($conn,"
    SELECT * FROM registrations WHERE user_id='$user_id' AND course_id='$course_id' AND status != 'cancelled'
    ");

    if(mysqli_num_rows($check) == 0){
        mysqli_query($conn,"INSERT INTO registrations(user_id, course_id, status, payment_status) VALUES('$user_id', '$course_id', 'pending', 'unpaid')");
        $success = "Đăng ký khóa học thành công! Đang chờ duyệt.";
    }else{
        $error = "Bạn đã đăng ký khóa học này rồi!";
    }
}

/* =========================
   YÊU CẦU CHUYỂN KHÓA
========================= */

if(isset($_POST['transfer_submit'])){
    $registration_id = $_POST['registration_id'];
    $new_course = $_POST['new_course'];
    $reason = $_POST['transfer_reason'];

    mysqli_query($conn,"UPDATE registrations SET transfer_course_id='$new_course', change_request='$reason', request_status='pending', request_date=NOW() WHERE registration_id='$registration_id'");
    $success = "Đã gửi yêu cầu chuyển khóa!";
}

/* =========================
   HỦY KHÓA HỌC
========================= */

if(isset($_POST['cancel_submit'])){
    $registration_id = $_POST['registration_id'];
    $reason = $_POST['cancel_reason'];

    mysqli_query($conn,"UPDATE registrations SET status='cancelled', cancel_reason='$reason' WHERE registration_id='$registration_id'");
    $success = "Đã hủy khóa học!";
}

/* =========================
   TRUY VẤN DỮ LIỆU
========================= */

$courses = mysqli_query($conn,"SELECT * FROM courses ORDER BY course_id DESC");

$my_courses = mysqli_query($conn,"
SELECT registrations.registration_id, registrations.status, courses.course_name
FROM registrations
INNER JOIN courses ON registrations.course_id = courses.course_id
WHERE registrations.user_id='$user_id' ORDER BY registrations.registration_id DESC
");

$notifications_query = mysqli_query($conn, "
SELECT r.status, c.course_name, r.updated_at
FROM registrations r
INNER JOIN courses c ON r.course_id = c.course_id
WHERE r.user_id = '$user_id' AND r.status IN ('approved', 'rejected')
ORDER BY r.updated_at DESC LIMIT 10
");

// Đếm số lượng thông báo để quyết định hiển thị chấm đỏ hay không
$noti_count = mysqli_num_rows($notifications_query);
$bell_class = ($noti_count > 0) ? 'has-noti' : '';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TNU English</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{ margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body{ background: <?php echo $isDark ? '#0f172a' : '#f4f7fb'; ?>; color: <?php echo $isDark ? '#ffffff' : '#111827'; ?>; transition:0.3s; display: flex; flex-direction: column; min-height: 100vh; }

/* NAVBAR */
header{ width:100%; padding:18px 40px; background: linear-gradient(90deg, #2563eb, #06b6d4); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:999; box-shadow:0 3px 15px rgba(0,0,0,0.12); }
.logo{ display:flex; align-items:center; text-decoration:none; }
.logo img{ height:60px; width:auto; object-fit:contain; display:block; }
.nav-right{ display:flex; align-items:center; gap:25px; }
.nav-right > a{ color:white; text-decoration:none; font-weight:600; }
.menu-btn{ color:white; font-size:28px; cursor:pointer; }

/* NOTIFICATIONS & SETTINGS */
.action-box { position: relative; }
.bell-btn { font-size: 24px; cursor: pointer; background: none; border: none; color: white; position: relative; }

/* CHỈ HIỆN CHẤM ĐỎ KHI CÓ CLASS has-noti */
.bell-btn.has-noti::after { content: ''; position: absolute; top: 2px; right: 2px; width: 10px; height: 10px; background: #ef4444; border-radius: 50%; border: 2px solid #2563eb; }

.dropdown-menu { position:absolute; right:0; top:50px; width:320px; background: <?php echo $isDark ? '#1e293b' : '#ffffff'; ?>; border-radius:18px; padding:15px; display:none; flex-direction:column; gap:12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); max-height: 400px; overflow-y: auto; }
.dropdown-menu.active{ display:flex; animation: modalFadeIn 0.2s ease forwards; }
.dropdown-menu.settings-width { width: 260px; }
.noti-item { padding: 12px; border-radius: 12px; background: <?php echo $isDark ? '#334155' : '#f1f5f9'; ?>; font-size: 14px; line-height: 1.5; }
.noti-item.approved { border-left: 4px solid #22c55e; }
.noti-item.rejected { border-left: 4px solid #ef4444; }
.noti-time { display: block; font-size: 11px; color: <?php echo $isDark ? '#94a3b8' : '#64748b'; ?>; margin-top: 5px; }
.settings-btn{ border:none; padding:10px 18px; border-radius:14px; background:white; color:#2563eb; font-weight:600; cursor:pointer; }
.dropdown-menu a, .dropdown-menu button{ width:100%; border:none; padding:14px; border-radius:12px; background: <?php echo $isDark ? '#334155' : '#f1f5f9'; ?>; color: <?php echo $isDark ? '#ffffff' : '#111827'; ?>; text-align:left; cursor:pointer; text-decoration:none; font-size:15px; }

/* SLIDER */
.slider{ width:100%; height:500px; position:relative; overflow:hidden; }
.slide{ position:absolute; width:100%; height:100%; opacity:0; transition:1s; }
.slide.active{ opacity:1; }
.slide img{ width:100%; height:100%; object-fit:cover; }
.overlay{ position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); color:white; text-align:center; width:80%; }
.overlay h1{ font-size:55px; margin-bottom:20px; text-shadow: 2px 2px 8px rgba(0,0,0,0.5); }
.overlay p{ font-size:22px; line-height:1.8; text-shadow: 1px 1px 5px rgba(0,0,0,0.5); }
.dots{ position:absolute; bottom:25px; width:100%; text-align:center; }
.dot{ width:14px; height:14px; background:#ddd; border-radius:50%; display:inline-block; margin:0 5px; }
.dot.active{ background:white; }

/* SIDEBAR */
.sidebar{ position:fixed; top:0; left:-350px; width:320px; height:100vh; background: <?php echo $isDark ? '#1e293b' : '#ffffff'; ?>; z-index:9999; padding:25px; transition:0.4s; overflow:auto; box-shadow: 5px 0 15px rgba(0,0,0,0.1); }
.sidebar.active{ left:0; }
.close-sidebar{ text-align:right; font-size:30px; cursor:pointer; margin-bottom:20px; }
.sidebar h2{ margin-bottom:20px; }
.course-item{ background: <?php echo $isDark ? '#334155' : '#f1f5f9'; ?>; padding:15px; border-radius:14px; margin-bottom:15px; }
.course-item h3{ margin-bottom:5px; font-size: 16px; }
.sidebar-btn{ width:100%; border:none; padding:14px; border-radius:14px; color:white; font-weight:600; cursor:pointer; margin-top:15px; }
.transfer-btn{ background:#2563eb; }
.cancel-btn{ background:#ef4444; }

/* CONTENT */
.container{ width:92%; margin:auto; padding:50px 0; flex: 1; }
.section-title{ font-size:28px; margin-bottom:25px; }
.course-grid{ display:grid; grid-template-columns: repeat(auto-fit,minmax(320px,1fr)); gap:25px; }
.card{ background: <?php echo $isDark ? '#1e293b' : '#ffffff'; ?>; border-radius:25px; overflow:hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.12); transition:0.3s; }
.card:hover{ transform:translateY(-8px); }
.card img{ width:100%; height:220px; object-fit:cover; }
.card-content{ padding:25px; }
.card h3{ font-size:24px; margin-bottom:15px; }
.card p{ line-height:1.8; margin-bottom:15px; font-size: 14px; color: <?php echo $isDark ? '#cbd5e1' : '#475569'; ?>; }
.tags{ display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
.tag{ background:#2563eb; color:white; padding:8px 12px; border-radius:10px; font-size:12px; }
.price{ font-size:30px; color:#2196f3; font-weight:700; margin-bottom:20px; }
.register-btn{ width:100%; border:none; padding:15px; border-radius:14px; background: linear-gradient(90deg, #2563eb, #06b6d4); color:white; font-weight:600; cursor:pointer; }
.success{ background:#22c55e; color:white; padding:18px; border-radius:14px; margin-bottom:25px; }
.error{ background:#ef4444; color:white; padding:18px; border-radius:14px; margin-bottom:25px; }

/* MODAL */
.modal{ position:fixed; inset:0; background:rgba(0,0,0,0.5); display:none; align-items:center; justify-content:center; z-index:99999; }
.modal.active{ display:flex; }
.modal-box{ width:450px; background: <?php echo $isDark ? '#1e293b' : '#ffffff'; ?>; padding:30px; border-radius:20px; animation: modalFadeIn 0.3s ease forwards; }
@keyframes modalFadeIn { from { opacity: 0; transform: scale(0.9) translateY(-20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
.modal-box h2{ margin-bottom:20px; }
.modal-box input, .modal-box select, .modal-box textarea{ width:100%; padding:14px; border-radius:12px; border:1px solid <?php echo $isDark ? '#475569' : '#ccc'; ?>; margin-bottom:15px; background: <?php echo $isDark ? '#334155' : '#ffffff'; ?>; color: <?php echo $isDark ? '#ffffff' : '#111827'; ?>; }
.modal-box textarea { resize: vertical; min-height: 100px; }
.modal-buttons{ display:flex; gap:10px; }
.confirm{ flex:1; border:none; padding:14px; border-radius:12px; background:#2563eb; color:white; cursor:pointer; }
.close{ flex:1; border:none; padding:14px; border-radius:12px; background:#ef4444; color:white; cursor:pointer; }

/* FOOTER */
footer { background: <?php echo $isDark ? '#1e293b' : '#ffffff'; ?>; color: <?php echo $isDark ? '#cbd5e1' : '#475569'; ?>; text-align: center; padding: 35px 20px; margin-top: auto; box-shadow: 0 -5px 15px rgba(0,0,0,0.05); }
footer h3 { color: <?php echo $isDark ? '#ffffff' : '#111827'; ?>; margin-bottom: 10px; font-size: 20px; }
footer p { margin-bottom: 5px; font-size: 15px; }
.footer-highlight { color: #2563eb; font-weight: 600; }
.btn-admin-access { display: inline-block; background: linear-gradient(90deg, #ef4444, #f97316); color: white; padding: 10px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 20px; transition: 0.3s; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
.btn-admin-access:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); color: white; }
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="close-sidebar" onclick="closeSidebar()">✕</div>
    <h2>Khóa học đã đăng ký</h2>
    <?php while($mc = mysqli_fetch_assoc($my_courses)){ ?>
    <div class="course-item">
        <h3><?php echo $mc['course_name']; ?></h3>
        <p style="color: #2196f3; font-weight: 500;">
            <?php 
                if($mc['status'] == 'pending') echo 'Đang chờ duyệt';
                elseif($mc['status'] == 'approved') echo 'Đã duyệt';
                elseif($mc['status'] == 'completed') echo 'Đã hoàn thành';
                elseif($mc['status'] == 'cancelled') echo 'Đã hủy';
                elseif($mc['status'] == 'rejected') echo 'Bị từ chối';
                else echo $mc['status'];
            ?>
        </p>
    </div>
    <?php } ?>
    <button class="sidebar-btn transfer-btn" onclick="openTransfer()">Yêu cầu chuyển đổi khóa học</button>
    <button class="sidebar-btn cancel-btn" onclick="openCancel()">Hủy đăng ký khóa học</button>
</div>

<header>
    <a href="index.php" class="logo">
        <img src="assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/150x60?text=TNU+English'">
    </a>
    
    <div class="nav-right">
        <a href="index.php">Trang chủ</a>
        <a href="about.php">Về chúng tôi</a>
        
        <div class="action-box">
            <button class="bell-btn <?php echo $bell_class; ?>" onclick="toggleNoti()">🔔</button>
            <div class="dropdown-menu" id="notiMenu">
                <h4 style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid <?php echo $isDark ? '#475569' : '#e2e8f0'; ?>;">Thông báo mới</h4>
                
                <?php 
                if($noti_count > 0){
                    while($noti = mysqli_fetch_assoc($notifications_query)){
                        $noti_class = ($noti['status'] == 'approved') ? 'approved' : 'rejected';
                        $noti_text = ($noti['status'] == 'approved') ? 'Quản trị viên đã <b>duyệt</b> khóa học' : 'Quản trị viên đã <b>hủy duyệt</b> khóa học';
                ?>
                    <div class="noti-item <?php echo $noti_class; ?>">
                        <p><?php echo $noti_text; ?>: <b><?php echo $noti['course_name']; ?></b></p>
                        <span class="noti-time"><?php echo date('d/m/Y H:i', strtotime($noti['updated_at'])); ?></span>
                    </div>
                <?php 
                    }
                } else {
                    echo "<p style='text-align:center; font-size: 14px; padding: 10px 0;'>Không có thông báo nào</p>";
                }
                ?>
            </div>
        </div>

        <div class="action-box">
            <button class="settings-btn" onclick="toggleSettings()">⚙ Cài đặt</button>
            <div class="dropdown-menu settings-width" id="settingsMenu">
                <form method="POST">
                    <button type="submit" name="toggle_theme">🌙 Chế độ tối / sáng</button>
                </form>
                <a href="#" onclick="openChangeName(); return false;">Đổi tên tài khoản</a>
                <a href="#" onclick="openChangePassword(); return false;">Đổi mật khẩu</a>
                <a href="logout.php" style="color: #ef4444; font-weight: 600;">Đăng xuất</a>
            </div>
        </div>
        
        <div class="menu-btn" onclick="openSidebar()">☰</div>
    </div>
</header>

<div class="slider">
    <div class="slide active">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f">
        <div class="overlay">
            <h1>Xin chào <?php echo htmlspecialchars($full_name); ?></h1>
            <p>Đây là trang đăng ký khóa học ngoại ngữ, bạn có thể xem và đăng ký khóa học tại đây.</p>
        </div>
    </div>
    <div class="slide">
        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644">
        <div class="overlay">
            <h1>TNU English</h1>
            <p>Nền tảng học ngoại ngữ hiện đại dành cho học sinh và sinh viên.</p>
        </div>
    </div>
    <div class="dots">
        <span class="dot active"></span>
        <span class="dot"></span>
    </div>
</div>

<div class="container">
    <h2 class="section-title">Danh sách khóa học</h2>

    <?php if(isset($success)){ ?>
        <div class="success"><?php echo $success; ?></div>
    <?php } ?>

    <?php if(isset($error)){ ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <div class="course-grid">
        <?php while($row = mysqli_fetch_assoc($courses)){ ?>
        <div class="card">
            <img src="<?php echo $row['image']; ?>">
            <div class="card-content">
                <h3><?php echo $row['course_name']; ?></h3>
                <p><?php echo $row['description']; ?></p>
                <div class="tags">
                    <div class="tag"><?php echo $row['language']; ?></div>
                    <div class="tag"><?php echo $row['level']; ?></div>
                    <div class="tag"><?php echo $row['duration_weeks']; ?> tuần</div>
                </div>
                <div class="price"><?php echo number_format($row['price']); ?> VNĐ</div>
                
                <form method="POST" onsubmit="return openRegisterConfirm(event, this);">
                    <input type="hidden" name="course_id" value="<?php echo $row['course_id']; ?>">
                    <input type="hidden" name="register_course" value="1">
                    <button type="submit" class="register-btn">🎓 Đăng ký ngay</button>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<footer>
    <h3>Trường đại học Tây Nguyên</h3>
    <p>Học phần: <span class="footer-highlight">Trí tuệ nhân tạo và Ứng dụng</span></p>
    <p>Công nghệ thông tin K25</p>

    <?php if($user_role == 'admin'): ?>
        <div style="margin-top: 10px;">
            <a href="admin.php" class="btn-admin-access">⚙️ Quản trị hệ thống</a>
        </div>
    <?php endif; ?>
</footer>

<div class="modal" id="registerModal">
    <div class="modal-box">
        <h2>Xác nhận đăng ký</h2>
        <p style="margin-bottom: 20px;">Bạn có chắc muốn đăng ký khóa học này không?</p>
        <div class="modal-buttons">
            <button class="confirm" onclick="submitRegisterForm()">Đồng ý</button>
            <button class="close" onclick="closeRegisterConfirm()">Hủy</button>
        </div>
    </div>
</div>

<div class="modal" id="changeNameModal">
    <div class="modal-box">
        <form method="POST">
            <h2>Đổi tên tài khoản</h2>
            <input type="text" name="new_name" placeholder="Nhập tên mới..." value="<?php echo htmlspecialchars($full_name); ?>" required>
            <div class="modal-buttons">
                <button type="submit" class="confirm" name="change_name_submit">Lưu thay đổi</button>
                <button type="button" class="close" onclick="closeChangeName()">Đóng</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="changePasswordModal">
    <div class="modal-box">
        <form method="POST">
            <h2>Đổi mật khẩu</h2>
            <input type="password" name="current_password" placeholder="Mật khẩu hiện tại..." required>
            <input type="password" name="new_password" placeholder="Mật khẩu mới..." required>
            <div class="modal-buttons">
                <button type="submit" class="confirm" name="change_password_submit">Lưu thay đổi</button>
                <button type="button" class="close" onclick="closeChangePassword()">Đóng</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="transferModal">
    <div class="modal-box">
        <form method="POST">
            <h2>Yêu cầu chuyển khóa</h2>
            <select name="registration_id">
                <?php
                $list = mysqli_query($conn,"
                SELECT registrations.registration_id, courses.course_name
                FROM registrations
                INNER JOIN courses ON registrations.course_id = courses.course_id
                WHERE registrations.user_id='$user_id' AND registrations.status != 'cancelled'
                ");
                while($l = mysqli_fetch_assoc($list)){
                ?>
                <option value="<?php echo $l['registration_id']; ?>"><?php echo $l['course_name']; ?></option>
                <?php } ?>
            </select>
            <select name="new_course">
                <?php
                $c2 = mysqli_query($conn,"SELECT * FROM courses");
                while($c = mysqli_fetch_assoc($c2)){
                ?>
                <option value="<?php echo $c['course_id']; ?>"><?php echo $c['course_name']; ?></option>
                <?php } ?>
            </select>
            <textarea name="transfer_reason" placeholder="Nhập lý do chuyển khóa..." required></textarea>
            <div class="modal-buttons">
                <button type="submit" class="confirm" name="transfer_submit">Đồng ý</button>
                <button type="button" class="close" onclick="closeTransfer()">Đóng</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="cancelModal">
    <div class="modal-box">
        <form method="POST">
            <h2>Hủy khóa học</h2>
            <select name="registration_id">
                <?php
                $list2 = mysqli_query($conn,"
                SELECT registrations.registration_id, courses.course_name
                FROM registrations
                INNER JOIN courses ON registrations.course_id = courses.course_id
                WHERE registrations.user_id='$user_id' AND registrations.status != 'cancelled'
                ");
                while($l2 = mysqli_fetch_assoc($list2)){
                ?>
                <option value="<?php echo $l2['registration_id']; ?>"><?php echo $l2['course_name']; ?></option>
                <?php } ?>
            </select>
            <textarea name="cancel_reason" placeholder="Nhập lý do hủy..." required></textarea>
            <div class="modal-buttons">
                <button type="submit" class="confirm" name="cancel_submit">Đồng ý</button>
                <button type="button" class="close" onclick="closeCancel()">Đóng</button>
            </div>
        </form>
    </div>
</div>

<script>
/* DROP DOWNS (THÔNG BÁO & CÀI ĐẶT) */
function toggleNoti(){
    document.getElementById("notiMenu").classList.toggle("active");
    document.getElementById("settingsMenu").classList.remove("active");
}

function toggleSettings(){
    document.getElementById("settingsMenu").classList.toggle("active");
    document.getElementById("notiMenu").classList.remove("active");
}

document.addEventListener("click", function(event) {
    let isClickInsideNoti = document.querySelector('.bell-btn').contains(event.target) || document.getElementById('notiMenu').contains(event.target);
    let isClickInsideSettings = document.querySelector('.settings-btn').contains(event.target) || document.getElementById('settingsMenu').contains(event.target);
    
    if (!isClickInsideNoti) document.getElementById('notiMenu').classList.remove("active");
    if (!isClickInsideSettings) document.getElementById('settingsMenu').classList.remove("active");
});

/* SIDEBAR */
function openSidebar(){
    document.getElementById("sidebar").classList.add("active");
}
function closeSidebar(){
    document.getElementById("sidebar").classList.remove("active");
}

/* CUSTOM MODAL XÁC NHẬN ĐĂNG KÝ */
let formToSubmit = null;
function openRegisterConfirm(event, formElement) {
    event.preventDefault(); 
    formToSubmit = formElement; 
    document.getElementById("registerModal").classList.add("active");
    return false;
}
function closeRegisterConfirm(){
    document.getElementById("registerModal").classList.remove("active");
    formToSubmit = null;
}
function submitRegisterForm(){
    if (formToSubmit) {
        formToSubmit.submit();
    }
}

/* MODAL ĐỔI TÊN & MẬT KHẨU */
function openChangeName(){
    document.getElementById("changeNameModal").classList.add("active");
    document.getElementById("settingsMenu").classList.remove("active");
}
function closeChangeName(){
    document.getElementById("changeNameModal").classList.remove("active");
}

function openChangePassword(){
    document.getElementById("changePasswordModal").classList.add("active");
    document.getElementById("settingsMenu").classList.remove("active");
}
function closeChangePassword(){
    document.getElementById("changePasswordModal").classList.remove("active");
}

/* MODAL CHUYỂN / HỦY */
function openTransfer(){
    document.getElementById("transferModal").classList.add("active");
    closeSidebar();
}
function closeTransfer(){
    document.getElementById("transferModal").classList.remove("active");
}

function openCancel(){
    document.getElementById("cancelModal").classList.add("active");
    closeSidebar();
}
function closeCancel(){
    document.getElementById("cancelModal").classList.remove("active");
}

/* SLIDER */
let slides = document.querySelectorAll(".slide");
let dots = document.querySelectorAll(".dot");
let current = 0;

function showSlide(index){
    slides.forEach(slide=>{ slide.classList.remove("active"); });
    dots.forEach(dot=>{ dot.classList.remove("active"); });
    slides[index].classList.add("active");
    dots[index].classList.add("active");
}

setInterval(()=>{
    current++;
    if(current >= slides.length){ current = 0; }
    showSlide(current);
}, 10000);

</script>
</body>
</html>