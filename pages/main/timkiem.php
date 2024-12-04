<?php 

if (isset($_POST['timkiem'])) {
    if (empty($_POST['tukhoa'])) {
        echo "<p style='color: red;'>Vui lòng nhập từ khóa tìm kiếm.</p>";
    } else {
        $tukhoa = $_POST['tukhoa'];

        // Kiểm tra nếu từ khóa có ít nhất 3 ký tự
        if (strlen($tukhoa) < 3) {
            echo "<p style='color: red;'>Vui lòng nhập ít nhất 3 ký tự và có dấu đầy đủ để tìm kiếm.</p>";
        } else {
            // Tiến hành truy vấn tìm kiếm
            $sql_pro = "SELECT * FROM tbl_sanpham, tbl_danhmuc WHERE tbl_sanpham.id_danhmuc = tbl_danhmuc.id_danhmuc AND tbl_sanpham.tensanpham LIKE '%$tukhoa%' ORDER BY tbl_sanpham.id_sanpham DESC";
            $query_pro = mysqli_query($mysqli, $sql_pro);

            // Kiểm tra nếu không có kết quả
            if (mysqli_num_rows($query_pro) == 0) {
                echo "<p style='color: red;'>Không tìm thấy sản phẩm nào với từ khóa: <strong>$tukhoa</strong>.</p>";
            }
        }
    }
}
?>

<?php if (isset($_POST['timkiem']) && !empty($_POST['tukhoa']) && strlen($_POST['tukhoa']) >= 3): ?>
    <h3>Tên đã được tìm kiếm: <?php echo htmlspecialchars($_POST['tukhoa']); ?></h3>
<?php endif; ?>

<ul class="list_product">
    <?php
    if (isset($query_pro)) {
        while ($row = mysqli_fetch_array($query_pro)) {
    ?>
    <li>
        <a href="index.php?quanly=sanpham&id=<?php echo $row['id_sanpham'] ?>">
            <img src="/BanPhuKien/admin/Modules/quanlysp/uploads/<?php echo htmlspecialchars($row['hinhanh']); ?>" alt="<?php echo htmlspecialchars($row['tensanpham']); ?>">
            <p class="title_product"><?php echo htmlspecialchars($row['tensanpham']); ?></p>
            <p class="price_product"><?php echo number_format($row['giasp'], 0, ',', '.') . ' đ'; ?></p>
        </a>
    </li>
    <?php
        }
    }
    ?>
</ul>
