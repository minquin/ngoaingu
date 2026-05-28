<?php
session_start();
include 'connect.php';

$message = "";

if(isset($_POST['login_admin'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)){
        $message = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $sql = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $sql->bind_param("s", $email);
        $sql->execute();
        $result = $sql->get_result();

        if($result->num_rows > 0){
            $user = $result->fetch_assoc();

            if(password_verify($password, $user['password'])){
                if($user['role'] != 'admin'){
                    $message = "Bạn không có quyền truy cập khu vực này!";
                } elseif($user['status'] != 'active') {
                    $message = "Tài khoản quản trị đã bị khóa!";
                } else {
                    // CHỈ set Session cho Backend (Độc lập hoàn toàn với Student)
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user_id'] = $user['user_id'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    header("Location: admin.php");
                    exit();
                }
            } else {
                $message = "Sai mật khẩu quản trị!";
            }
        } else {
            $message = "Email không tồn tại!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - TNU</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { height:100vh; display:flex; justify-content:center; align-items:center; background: #0f172a; }
.container { width:400px; background:#1e293b; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.5); border-top: 5px solid #ef4444; }
.container h2 { text-align:center; margin-bottom:25px; color: white; }
.input-box { position:relative; margin-bottom:20px; }
.input-box input { width:100%; padding:14px; border:none; border-radius:10px; background:#334155; color: white; outline:none; }
.eye { position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; color: #94a3b8; }
.btn { width:100%; padding:14px; border:none; border-radius:10px; background:#ef4444; color:white; font-size:16px; cursor:pointer; font-weight: 600; }
.btn:hover { background: #dc2626; }
.message { color:#fca5a5; text-align:center; margin-bottom:15px; }
.link { text-align:center; margin-top:20px; }
.link a { color:#94a3b8; text-decoration:none; font-size: 14px; }
</style>
</head>
<body>
<div class="container">
    <h2>Quản Trị Hệ Thống</h2>
    <div class="message"><?php echo $message; ?></div>
    <form method="POST">
        <div class="input-box">
            <input type="email" name="email" placeholder="Email Admin" required>
        </div>
        <div class="input-box">
            <input type="password" id="password" name="password" placeholder="Mật khẩu Admin" required>
            <i class="fa-solid fa-eye eye" id="togglePassword"></i>
        </div>
        <button type="submit" name="login_admin" class="btn">Đăng Nhập Backend</button>
    </form>
    <div class="link">
        <a href="login.php">← Quay lại trang học viên</a>
    </div>
</div>
<script>
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");
togglePassword.onclick = () => {
    password.type = password.type === "password" ? "text" : "password";
    togglePassword.classList.toggle("fa-eye-slash");
}
</script>
</body>
</html>