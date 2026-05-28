<?php
include 'connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$isDark = isset($_SESSION['theme']) && $_SESSION['theme'] == 'dark';
?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Về chúng tôi - TNU English</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{

    background:
    <?php echo $isDark ? '#0f172a' : '#f4f7fb'; ?>;

    color:
    <?php echo $isDark ? '#ffffff' : '#111827'; ?>;
}

header{

    background:linear-gradient(90deg,#1d4ed8,#0ea5e9);

    padding:20px 40px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    color:white;
}

.logo{

    font-size:30px;

    font-weight:700;
}

nav{

    display:flex;

    gap:25px;
}

nav a{

    text-decoration:none;

    color:white;

    font-weight:600;
}

.container{

    width:90%;

    max-width:1200px;

    margin:50px auto;
}

.hero{

    background:
    <?php echo $isDark ? '#111827' : '#ffffff'; ?>;

    padding:50px;

    border-radius:30px;

    box-shadow:0 10px 30px rgba(0,0,0,0.12);

    margin-bottom:40px;
}

.hero h1{

    font-size:50px;

    margin-bottom:20px;

    color:#2196f3;
}

.hero p{

    line-height:1.8;

    font-size:18px;
}

.team{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(250px,1fr));

    gap:25px;
}

.member{

    background:
    <?php echo $isDark ? '#111827' : '#ffffff'; ?>;

    padding:30px;

    border-radius:24px;

    text-align:center;

    box-shadow:0 10px 25px rgba(0,0,0,0.1);

    transition:0.3s;
}

.member:hover{

    transform:translateY(-8px);
}

.avatar{

    width:100px;

    height:100px;

    border-radius:50%;

    background:linear-gradient(135deg,#1d4ed8,#0ea5e9);

    margin:auto;

    margin-bottom:20px;
}

.member h3{

    margin-bottom:10px;

    color:#2196f3;
}

.back{

    display:inline-block;

    margin-top:40px;

    padding:14px 22px;

    border-radius:14px;

    background:#2196f3;

    color:white;

    text-decoration:none;

    font-weight:600;
}

</style>

</head>

<body>

<header>

<div class="logo">
TNU English
</div>

<nav>

<a href="index.php">
Trang chủ
</a>

<a href="about.php">
Về chúng tôi
</a>

</nav>

</header>

<div class="container">

<div class="hero">

<h1>
Về chúng tôi
</h1>

<p>

TNU English là nền tảng đăng ký khóa học ngoại ngữ hiện đại, 
hỗ trợ học sinh và sinh viên tiếp cận các chương trình học chất lượng cao như IELTS, TOEIC, VSTEP, HSK và nhiều khóa học khác.

<br><br>

Website được phát triển với mục tiêu mang lại trải nghiệm đăng ký khóa học trực tuyến nhanh chóng, hiện đại và dễ sử dụng.

</p>

</div>

<div class="team">

<div class="member">

<div class="avatar"></div>

<h3>
CEO
</h3>

<p>
Đinh Thị Minh Quyên
</p>

</div>

<div class="member">

<div class="avatar"></div>

<h3>
Thiết kế web
</h3>

<p>
Đinh Văn Quốc
</p>

</div>

<div class="member">

<div class="avatar"></div>

<h3>
Tổng hợp nội dung
</h3>

<p>
Nguyễn Tiến Quyền
</p>

</div>

<div class="member">

<div class="avatar"></div>

<h3>
Thiết kế ảnh và Kiểm thử
</h3>

<p>
Phạm Như Quỳnh
</p>

</div>

<div class="member">

<div class="avatar"></div>

<h3>
Prompt và API
</h3>

<p>
Trần Nguyễn Như Ngọc
</p>

</div>

</div>

<a href="index.php" class="back">
← Quay về trang chủ
</a>

</div>

</body>
</html>