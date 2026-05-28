<?php
include 'connect.php';

$message = "";

if(isset($_POST['register'])){

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password_raw = trim($_POST['password']);

    if(
        empty($full_name) ||
        empty($email) ||
        empty($password_raw)
    ){
        $message = "Vui lòng nhập đầy đủ thông tin!";
    }

    else{

        $username = explode('@', $email)[0];

        $password = password_hash(
            $password_raw,
            PASSWORD_DEFAULT
        );

        $check = $conn->prepare("
            SELECT email
            FROM users
            WHERE email=?
        ");

        $check->bind_param("s", $email);

        $check->execute();

        $result = $check->get_result();

        if($result->num_rows > 0){

            $message = "Email đã tồn tại!";

        } else {

            $sql = $conn->prepare("
                INSERT INTO users
                (
                    username,
                    email,
                    password,
                    full_name,
                    role,
                    status
                )

                VALUES

                (
                    ?,
                    ?,
                    ?,
                    ?,
                    'student',
                    'active'
                )
            ");

            $sql->bind_param(
                "ssss",
                $username,
                $email,
                $password,
                $full_name
            );

            if($sql->execute()){

                header("Location: login.php");
                exit();

            } else {

                $message = "Đăng ký thất bại!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Đăng Ký</title>

<link rel="preconnect"
href="https://fonts.googleapis.com">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(
    135deg,
    #4facfe,
    #00c6fb
    );
}

.container{
    width:400px;
    background:white;
    padding:40px;
    border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
}

.container h2{
    text-align:center;
    margin-bottom:25px;
    color:#333;
}

.input-box{
    margin-bottom:20px;
    position:relative;
}

.input-box input{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#f1f1f1;
    outline:none;
}

.eye{
    position:absolute;
    top:50%;
    right:15px;
    transform:translateY(-50%);
    cursor:pointer;
    color:#666;
}

.btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:linear-gradient(
    135deg,
    #4facfe,
    #00c6fb
    );
    color:white;
    font-size:16px;
    cursor:pointer;
}

.message{
    text-align:center;
    color:red;
    margin-bottom:15px;
}

.link{
    text-align:center;
    margin-top:15px;
}

.link a{
    text-decoration:none;
    color:#2196f3;
}

</style>

</head>
<body>

<div class="container">

<h2>Đăng Ký</h2>

<div class="message">
    <?php echo $message; ?>
</div>

<form method="POST">

<div class="input-box">

<input type="text"
name="full_name"
placeholder="Họ và tên"
required>

</div>

<div class="input-box">

<input type="email"
name="email"
placeholder="Email"
required>

</div>

<div class="input-box">

<input type="password"
id="password"
name="password"
placeholder="Mật khẩu"
required>

<i class="fa-solid fa-eye eye"
id="togglePassword"></i>

</div>

<button type="submit"
name="register"
class="btn">

Đăng Ký

</button>

</form>

<div class="link">
Đã có tài khoản?
<a href="login.php">
Đăng nhập
</a>
</div>

</div>

<script>

const togglePassword =
document.getElementById(
"togglePassword"
);

const password =
document.getElementById(
"password"
);

togglePassword.onclick = () => {

    const type =
    password.type === "password"
    ? "text"
    : "password";

    password.type = type;

    togglePassword.classList.toggle(
    "fa-eye-slash"
    );
}

</script>

</body>
</html>