<?php
session_start();
require("../config.php");
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	if (!isset($_POST['kategori'])) {
		exit("No direct script access allowed!");
	}
if (isset($_POST['kategori'])) {
	$post_kategori = $conn->real_escape_string($_POST['kategori']);
	$cek_layanan = $conn->query("SELECT * FROM layanan_sosmed WHERE id_kategori = '$post_kategori' AND status = 'Aktif' ORDER BY harga ASC");
	?>
	<option value="0">Pilih Salah Satu...</option>
	<?php
	while ($data_layanan = mysqli_fetch_assoc($cek_layanan)) {
	?>
	<option value="<?php echo $data_layanan['service_id'];?>">(<?php echo $data_layanan['service_id'];?>) <?php echo $data_layanan['layanan'];?> -Rp <?php echo number_format($data_layanan['harga'], 0, ',', '.');?></option>
	<?php
	}
} else {
?>
<option value="0">Error.</option>
<?php
}
} else {
	exit("No direct script access allowed!");
}