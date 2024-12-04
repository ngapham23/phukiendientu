<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mysqli = new mysqli("localhost", "root", "", "banphukien");

if ($mysqli->connect_error) {
    echo "Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.";
    exit();
}

if (isset($_POST['dangnhap'])) {
    // Lấy dữ liệu từ form đăng nhập
    $email = trim($_POST["email"]);
    $matKhau = trim($_POST["matkhau"]);
    // Kiểm tra các trường dữ liệu có rỗng không
    if (empty($email) || empty($matKhau)) {
        $error_message = "Vui lòng điền đầy đủ thông tin!";
    } else {
        // Truy vấn để kiểm tra email trong bảng tbl_dangnhap
        $sql_check_email = "SELECT * FROM tbl_dangnhap WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql_check_email)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Kiểm tra mật khẩu có đúng không
                if (password_verify($matKhau, $row['matkhau'])) {
                    // Mật khẩu chính xác, lưu thông tin vào session và chuyển hướng
                    $_SESSION["email"] = $row['email'];
                    $_SESSION["dangnhap"] = true;

                    $sql_khachhang = "SELECT tenkhachhang, diachi, dienthoai FROM tbl_dangky WHERE email = ?";
                    if ($stmt_khachhang = $mysqli->prepare($sql_khachhang)) {
                        $stmt_khachhang->bind_param("s", $email);
                        $stmt_khachhang->execute();
                        $result_khachhang = $stmt_khachhang->get_result();

                    if ($result_khachhang->num_rows > 0) {
                         $row_khachhang = $result_khachhang->fetch_assoc();
                           $_SESSION['id_khachhang'] = $row_khachhang['id_khachhang'];
                            $_SESSION['tenkhachhang'] = $row_khachhang['tenkhachhang'];
                             $_SESSION['diachi'] = $row_khachhang['diachi'];
                            $_SESSION['dienthoai'] = $row_khachhang['dienthoai'];
                                     }
                                }
                    
                   
                    $success_message = "Đăng nhập thành công!.";
                     // Điều hướng
                if (isset($_SESSION['from_cart']) && $_SESSION['from_cart'] === true) {
                    unset($_SESSION['from_cart']);
                    echo "<script> alert('$success_message');window.location.href = 'index.php?quanly=giohang';</script>";
                    exit();
                } else {
                    echo "<script>alert('$success_message');window.location.href = 'index.php';</script>";
                    exit();
                }
                } else {
                    $error_message = "Mật khẩu không chính xác!";
                }
            } else {
                $error_message = "Email không tồn tại!";
            }

            $stmt->close();
        }
    }
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập tài khoản</title>
    <link rel="stylesheet" href="../css/login.css"> 
    <script>
    function autoCompleteEmail() {
        var emailInput = document.getElementById("email");
        var emailValue = emailInput.value.trim();
        if (emailValue && !emailValue.includes("@")) {
            emailInput.value = emailValue + "@gmail.com";
        }
    }
</script>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Đăng nhập tài khoản</h2>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?quanly=dangnhap">
                <div class="input-group">
                    <label for="email">Email</label>
                    <!-- Khi người dùng nhập, gọi hàm autoCompleteEmail() -->
                    <input type="email" id="email" name="email" required placeholder="Nhập email" onblur="autoCompleteEmail()">
                </div>

                <div class="input-group">
                    <label for="matkhau">Mật khẩu</label>
                    <input type="password" id="matkhau" name="matkhau" required placeholder="Nhập mật khẩu">
                </div>

                <button type="submit" name="dangnhap" class="login-btn">Đăng nhập</button>

                <div class="register-link">
                    <p>Chưa có tài khoản? <a href="index.php?quanly=dangky">Đăng ký ngay</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
