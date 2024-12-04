<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
use GuzzleHttp\Psr7\Message;
// Kết nối với cơ sở dữ liệu
$mysqli = new mysqli("localhost", "root", "", "banphukien");

if ($mysqli->connect_error) {
    echo "Lỗi kết nối: " . $mysqli->connect_error;
    exit();
}

if (isset($_POST['dangky'])) {
    // Lấy dữ liệu từ form đăng ký
    $tenKhachHang = $_POST["tenkhachhang"];
    $email = $_POST["email"];
    $diaChi = $_POST["diachi"];
    $dienThoai = $_POST["dienthoai"];
    $matKhau = $_POST["matkhau"];
    $xacNhanMatKhau = $_POST["xacnhanmatkhau"];

    // Kiểm tra các trường dữ liệu có rỗng không
    if (empty($tenKhachHang) || empty($email) || empty($diaChi) || empty($dienThoai) || empty($matKhau) || empty($xacNhanMatKhau)) {
        $error_message = "Vui lòng điền đầy đủ thông tin!";
    } else {
        // Kiểm tra nếu mật khẩu và xác nhận mật khẩu giống nhau
        if ($matKhau != $xacNhanMatKhau) {
            $error_message = "Mật khẩu và xác nhận mật khẩu không khớp!";
        } else {
            // Kiểm tra tên khách hàng chỉ chứa chữ cái và khoảng trắng
            if (!preg_match("/^[A-Za-z\s]+$/", $tenKhachHang)) {
                $error_message = "Tên khách hàng chỉ được chứa chữ cái và khoảng trắng!";
            }

            // Kiểm tra số điện thoại có đúng 10 chữ số
            if (!preg_match("/^\d{10}$/", $dienThoai)) {
                $error_message = "Số điện thoại phải có đúng 10 chữ số!";
            }

            // Kiểm tra mật khẩu có đủ mạnh không (ít nhất 8 ký tự, bao gồm chữ cái viết hoa, chữ cái viết thường và số)
            if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/", $matKhau)) {
                $error_message = "Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái viết hoa, chữ cái viết thường và một số!";
            }

            // Kiểm tra email đã tồn tại hay chưa
            if (empty($error_message)) { // chỉ kiểm tra email nếu các kiểm tra khác không có lỗi
                $sql_check_email = "SELECT * FROM tbl_dangky WHERE email = ?";
                if ($stmt = $mysqli->prepare($sql_check_email)) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $error_message = "Email đã được sử dụng!";
                    }
                    $stmt->close();
                }
            }

            // Nếu không có lỗi, tiến hành lưu vào cơ sở dữ liệu
            if (empty($error_message)) {
                // Mã hóa mật khẩu
                $hashed_password = password_hash($matKhau, PASSWORD_DEFAULT);

                // Thực hiện chèn thông tin khách hàng vào cơ sở dữ liệu
                $sql_insert = "INSERT INTO tbl_dangky (tenkhachhang, email, diachi, matkhau, dienthoai) VALUES (?, ?, ?, ?, ?)";
                if ($stmt_insert = $mysqli->prepare($sql_insert)) {
                    $stmt_insert->bind_param("sssss", $tenKhachHang, $email, $diaChi, $hashed_password, $dienThoai);
                    $stmt_insert->execute();

                    // Sau khi đăng ký thành công, chèn dữ liệu vào bảng tbl_dangnhap
                    $sql_insert_login = "INSERT INTO tbl_dangnhap (email, matkhau) VALUES (?, ?)";
                    if ($stmt_insert_login = $mysqli->prepare($sql_insert_login)) {
                        $stmt_insert_login->bind_param("ss", $email, $hashed_password);
                        $stmt_insert_login->execute();
                    }

                    $id_khachhang = $stmt_insert->insert_id;    // Lấy id khách hàng vừa chèn
               // Sau khi đăng ký và chèn thông tin vào cả 2 bảng, đăng nhập tự động
             
               $_SESSION["email"] = $email;
               $_SESSION["dangnhap"] = true;

               // Lấy thông tin tên khách hàng từ bảng tbl_dangky
               $sql_khachhang = "SELECT tenkhachhang, diachi, dienthoai FROM tbl_dangky WHERE email = ?";
               if ($stmt_khachhang = $mysqli->prepare($sql_khachhang)) {
                   $stmt_khachhang->bind_param("s", $email);
                   $stmt_khachhang->execute();
                   $result_khachhang = $stmt_khachhang->get_result();

                   if ($result_khachhang->num_rows > 0) {
                       $row_khachhang = $result_khachhang->fetch_assoc();
                        // Lưu id_khachhang vào session
                       $_SESSION['id_khachhang'] = $row_khachhang['id_khachhang'];
                       $_SESSION['tenkhachhang'] = $row_khachhang['tenkhachhang'];
                       $_SESSION['diachi'] = $row_khachhang['diachi'];
                       $_SESSION['dienthoai'] = $row_khachhang['dienthoai'];
                   }
               }
                    // Thông báo đăng ký thành công và chuyển hướng
                    $success_message = "Đăng ký thành công! bạn sẽ được đưa tới trang chủ.";
                    echo "<script>
                            alert('$success_message');
                            window.location.href = 'index.php';
                          </script>";
                    exit();
                } else {
                    $error_message = "Lỗi khi đăng ký, vui lòng thử lại!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href=".././css/register.css"> 
    <script>
    function autoCompleteEmail() {
        var emailInput = document.getElementById("email");
        var emailValue = emailInput.value.trim();
        if (emailValue && !emailValue.includes("@")) {
            emailInput.value = emailValue + "@gmail.com";
        }
    }

     // Kiểm tra tên khách hàng chỉ chứa chữ cái và khoảng trắng
     function validateName() {
        var name = document.getElementById("tenkhachhang").value;
        var regex = /^[A-Za-z\s]+$/; // chỉ cho phép chữ cái và khoảng trắng
        if (!regex.test(name)) {
            alert("Tên khách hàng chỉ được chứa chữ cái và khoảng trắng.");
            return false;
        }
        return true;
    }
       // Kiểm tra số điện thoại không quá 10 chữ số
    function validatePhone() {
        var phone = document.getElementById("dienthoai").value;
        var regex = /^\d{10}$/; // kiểm tra chỉ có 10 chữ số
        if (!regex.test(phone)) {
            alert("Số điện thoại phải có đúng 10 chữ số.");
            return false;
        }
        return true;
    }
  // Kiểm tra mật khẩu mạnh
  function validatePassword() {
        var password = document.getElementById("matkhau").value;
        var confirmPassword = document.getElementById("xacnhanmatkhau").value;

        // Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái viết hoa, chữ cái viết thường và chữ số
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
        
        if (!passwordRegex.test(password)) {
            alert("Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái viết hoa, chữ cái viết thường và một số.");
            return false;
        }

        // Kiểm tra mật khẩu và xác nhận mật khẩu có giống nhau không
        if (password !== confirmPassword) {
            alert("Mật khẩu và xác nhận mật khẩu không khớp.");
            return false;
        }

        return true;
    }
 

      // Kiểm tra tất cả các trường khi submit
      function validateForm() {
        if (validateName() && validatePhone() && validatePassword()) {
            return true; // Gửi form nếu tất cả các kiểm tra thành công
        }
        return false; // Ngừng gửi form nếu có kiểm tra không thành công
    }
</script>
</head>
<body>
    <div class="register-container">
        <div class="register-form">
            <h2>Đăng ký tài khoản</h2>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?quanly=dangky" onsubmit="return validateForm()">
                <div class="input-group">
                    <label for="tenkhachhang">Tên khách hàng</label>
                    <input type="text" id="tenkhachhang" name="tenkhachhang" required placeholder="Nhập tên khách hàng">
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Nhập email"  onblur="autoCompleteEmail()">
                </div>

                <div class="input-group">
                    <label for="diachi">Địa chỉ</label>
                    <input type="text" id="diachi" name="diachi" required placeholder="Nhập địa chỉ">
                </div>

                <div class="input-group">
                    <label for="dienthoai">Điện thoại</label>
                    <input type="text" id="dienthoai" name="dienthoai" required placeholder="Nhập số điện thoại">
                </div>

                <div class="input-group">
                    <label for="matkhau">Mật khẩu</label>
                    <input type="password" id="matkhau" name="matkhau" required placeholder="Nhập mật khẩu">
                </div>

                <div class="input-group">
                    <label for="xacnhanmatkhau">Xác nhận mật khẩu</label>
                    <input type="password" id="xacnhanmatkhau" name="xacnhanmatkhau" required placeholder="Xác nhận mật khẩu">
                </div>

                <button type="submit" name="dangky" class="register-btn">Đăng ký</button>

                <div class="login-link">
                    <p>Đã có tài khoản? <a href="index.php?quanly=dangnhap">Đăng nhập ngay</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
