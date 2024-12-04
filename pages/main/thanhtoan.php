<?php
include ".././admin/Sconfig/myconfig.php";  // Kết nối cơ sở dữ liệu
// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['dangnhap']) && $_SESSION['dangnhap'] === true) {
    // Lấy thông tin người dùng từ session
    $tenkhachhang = isset($_SESSION['tenkhachhang']) ? $_SESSION['tenkhachhang'] : '';
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    $diachi = isset($_SESSION['diachi']) ? $_SESSION['diachi'] : '';
    $dienthoai = isset($_SESSION['dienthoai']) ? $_SESSION['dienthoai'] : '';

    // Kiểm tra giỏ hàng có tồn tại và không trống
    if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
        $total_amount = 0;  // Tổng tiền giỏ hàng

        // Tính tổng tiền giỏ hàng
        foreach ($_SESSION['cart'] as $cart_item) {
            // Kiểm tra xem sản phẩm có đầy đủ thông tin không
            if (!isset($cart_item['id'], $cart_item['tensanpham'], $cart_item['giasp'], $cart_item['soluong'])) {
                echo "<p>Thông tin sản phẩm không hợp lệ.</p>";
                exit;
            }
            $total_price = $cart_item['giasp'] * $cart_item['soluong'];
            $total_amount += $total_price;
        }
    } else {
        // Nếu giỏ hàng trống, chuyển hướng về trang giỏ hàng
        echo "<script>alert('Giỏ hàng của bạn trống.'); window.location.href = 'index.php?quanly=giohang';</script>";
        exit();
    }
} else {
    // Nếu chưa đăng nhập, yêu cầu đăng nhập
    echo "<script>alert('Vui lòng đăng nhập để tiếp tục thanh toán.'); window.location.href = 'index.php?quanly=dangnhap';</script>";
    exit();
}
?>

<!-- Form thanh toán -->
<h2>Thông tin thanh toán</h2>
<p>Họ và Tên: <?= htmlspecialchars($tenkhachhang) ?>!</p>
<p>Email: <?= htmlspecialchars($email) ?></p>
<p>Địa chỉ giao hàng: <?= htmlspecialchars($diachi) ?></p>
<p>Số điện thoại: <?= htmlspecialchars($dienthoai) ?></p>

<h3>Giỏ hàng của bạn</h3>
<table>
    <tr>
        <th>Tên sản phẩm</th>
        <th>Số lượng</th>
        <th>Giá</th>
        <th>Thành tiền</th>
    </tr>
    <?php
    // Hiển thị giỏ hàng
    foreach ($_SESSION['cart'] as $cart_item) {
        $total_price = $cart_item['giasp'] * $cart_item['soluong'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($cart_item['tensanpham']) . "</td>";
        echo "<td>" . $cart_item['soluong'] . "</td>";
        echo "<td>" . number_format($cart_item['giasp'], 0, ',', '.') . " VND</td>";
        echo "<td>" . number_format($total_price, 0, ',', '.') . " VND</td>";
        echo "</tr>";
    }
    ?>
</table>

<h3>Tổng tiền giỏ hàng: <?= number_format($total_amount, 0, ',', '.') ?> VND</h3>

<!-- Chọn phương thức thanh toán -->
<h3>Phương thức thanh toán</h3>
<form action="index.php?quanly=xulythanhtoan" method="POST">
    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
    <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
    
    <label>
        <input type="radio" name="payment_method" value="COD" checked> Thanh toán khi nhận hàng
    </label><br>
    
    <label>
        <input type="radio" name="payment_method" value="Online"> Thanh toán qua chuyển khoản online
    </label><br>
    
    <button class="dathang" type="submit" name="place_order">Đặt hàng</button>
</form>


<style>
    /* Kiểu dáng chung cho trang */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

/* Tiêu đề trang thanh toán */
h2 {
    text-align: center;
    color: #333;
    margin-top: 20px;
    font-size: 28px;
    font-weight: bold;
}

/* Các thông tin khách hàng */
p {
    font-size: 18px;
    color: #555;
    margin: 10px 0;
}

/* Giỏ hàng */
h3 {
    text-align: center;
    color: #333;
    margin-top: 20px;
    font-size: 22px;
}

/* Bảng giỏ hàng */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

table th, table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
    font-size: 16px;
}

table th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Cột giá và thành tiền */
table td:last-child {
    font-weight: bold;
    color: #007bff;
}

/* Tổng tiền giỏ hàng */
h3 {
    text-align: center;
    font-size: 20px;
    color: #333;
    font-weight: bold;
}

/* Form đặt hàng */
/* Kiểu dáng cho nút Đặt hàng */
.dathang {
    margin: 0 483px;
    padding: 15px 30px;
    background-color: #28a745;  /* Màu xanh lá */
    color: white;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s, box-shadow 0.3s;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Hiệu ứng hover cho nút Đặt hàng */
.dathang:hover {
    background-color: #007bff;  /* Màu xanh đậm khi hover */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

/* Hiệu ứng focus cho nút Đặt hàng */
.dathang:focus {
    outline: none;
    box-shadow: 0 0 5px 2px rgba(40, 167, 69, 0.6);  /* Ánh sáng khi nút được chọn */
}

/* Responsive cho nút Đặt hàng (dành cho thiết bị nhỏ) */
@media (max-width: 768px) {
    .dathang {
        width: 100%;  /* Đặt nút chiếm toàn bộ chiều rộng màn hình */
        padding: 18px;
        font-size: 20px;
    }
}

form button {
    padding: 15px 30px;
    background-color: #28a745;
    color: white;
    font-size: 18px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

form button:hover {
    background-color: #218838;
}

/* Các thông báo lỗi, thành công */
.alert {
    padding: 15px;
    margin: 20px auto;
    width: 80%;
    border-radius: 5px;
    text-align: center;
}

.alert-success {
    background-color: #28a745;
    color: white;
}

.alert-error {
    background-color: #dc3545;
    color: white;
}

/* Responsive - Điều chỉnh cho màn hình nhỏ */
@media (max-width: 768px) {
    table, form {
        width: 100%;
    }

    form button {
        width: 100%;
        font-size: 20px;
        padding: 18px;
    }
}

</style>