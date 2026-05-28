<?php
session_start();
include 'connect.php';

$message = "";

if(isset($_POST['login'])){
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
                if($user['status'] != 'active'){
                    $message = "Tài khoản đã bị khóa!";
                } else {
                    // Thiết lập Session chung cho Frontend
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];

                    if($user['role'] == 'admin'){
                        // Nếu là Admin đăng nhập ở đây, cấp luôn quyền Backend
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_user_id'] = $user['user_id'];
                        $_SESSION['admin_role'] = $user['role'];
                        header("Location: admin.php");
                        exit();
                    } else {
                        header("Location: index.php");
                        exit();
                    }
                }
            } else {
                $message = "Sai mật khẩu!";
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
<title>Đăng Nhập</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
body { height:100vh; display:flex; justify-content:center; align-items:center; background:linear-gradient(135deg, #4facfe, #00c6fb); }
.container { width:400px; background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); }
.container h2 { text-align:center; margin-bottom:25px; }
.input-box { position:relative; margin-bottom:20px; }
.input-box input { width:100%; padding:14px; border:none; border-radius:10px; background:#f1f1f1; outline:none; }
.eye { position:absolute; right:15px; top:50%; transform:translateY(-50%); cursor:pointer; }
.btn { width:100%; padding:14px; border:none; border-radius:10px; background:linear-gradient(135deg, #4facfe, #00c6fb); color:white; font-size:16px; cursor:pointer; font-weight: 600; margin-bottom: 10px; }
.btn-admin { background: #1e293b; display: block; text-align: center; text-decoration: none; padding: 14px; border-radius: 10px; color: white; font-weight: 500; }
.message { color:red; text-align:center; margin-bottom:15px; }
.link { text-align:center; margin-top:15px; }
.link a { color:#2196f3; text-decoration:none; }
</style>
</head>
<body>

<div class="container">
    <h2>Đăng Nhập</h2>
    <div class="message"><?php echo $message; ?></div>

    <form method="POST">
        <div class="input-box">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="input-box">
            <input type="password" id="password" name="password" placeholder="Mật khẩu" required>
            <i class="fa-solid fa-eye eye" id="togglePassword"></i>
        </div>
        <button type="submit" name="login" class="btn">Đăng Nhập</button>
    </form>

    <a href="login_admin.php" class="btn-admin">👨‍💻 Dành cho Quản trị viên</a>

    <div class="link">
        Chưa có tài khoản? <a href="register.php">Đăng ký</a>
    </div>
</div>

<script>
const togglePassword = document.getElementById("togglePassword");
const password = document.getElementById("password");
togglePassword.onclick = () => {
    const type = password.type === "password" ? "text" : "password";
    password.type = type;
    togglePassword.classList.toggle("fa-eye-slash");
}
</script>
</body>
</html>