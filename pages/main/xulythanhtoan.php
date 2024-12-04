<?php
include ".././admin/Sconfig/myconfig.php";  // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['dangnhap']) && $_SESSION['dangnhap'] === true && isset($_SESSION['id_khachhang'])) {
    // Lấy id_khachhang từ session
    $id_khachhang = $_SESSION['id_khachhang'];  // Đã có trong session sau khi đăng nhập

    // Các thông tin khác từ session
    $email = $_SESSION['email'];
    $tenkhachhang = $_SESSION['tenkhachhang'];
    $diachi = $_SESSION['diachi'];
    $dienthoai = $_SESSION['dienthoai'];
    $total_amount = $_POST['total_amount'];  // Tổng tiền từ form
    $payment_method = $_POST['payment_method'];  // Phương thức thanh toán (COD hoặc Online)

    // Tạo mã đơn hàng (code_cart)
    $code_cart = 'ORDER_' . strtoupper(uniqid());  // Tạo mã đơn hàng ngẫu nhiên

    // Chèn thông tin giỏ hàng vào bảng tbl_cart
    $sql_cart = "INSERT INTO tbl_cart(id_khachhang, code_cart, cart_status, created_at) 
                 VALUES ('$id_khachhang', '$code_cart', 'pending', NOW())";
    if (mysqli_query($mysqli, $sql_cart)) {
        // Sau khi chèn giỏ hàng thành công, lấy id_cart vừa chèn
        $id_cart = mysqli_insert_id($mysqli);

        // Chèn các sản phẩm vào tbl_cart_details
        foreach ($_SESSION['cart'] as $cart_item) {
            $id_sanpham = $cart_item['id'];
            $soluong = $cart_item['soluong'];
            
            // Chèn chi tiết giỏ hàng vào tbl_cart_details
            $sql_details = "INSERT INTO tbl_cart_details(code_cart, id_sanpham, soluong, created_at)
                            VALUES ('$code_cart', '$id_sanpham', '$soluong', NOW())";
            mysqli_query($mysqli, $sql_details);
        }

        // Sau khi chèn thành công, chuyển hướng đến trang thành công
        echo "<script>alert('Đặt hàng thành công.'); window.location.href = 'index.php?quanly=thanhcong';</script>";
    } else {
        // Nếu có lỗi, thông báo lỗi
        echo "Có lỗi xảy ra khi đặt hàng!";
    }
} else {
    // Nếu chưa đăng nhập, yêu cầu đăng nhập
    echo "<script>alert('Vui lòng đăng nhập để tiếp tục thanh toán.'); window.location.href = 'index.php?quanly=dangnhap';</script>";
    exit();
}
?>

