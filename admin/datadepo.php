<?php
session_start();
require '../config.php';
require '../lib/session_login_admin.php';
if (isset($_POST['update'])) {
        $get_oid = $conn->real_escape_string($_GET['deposit_id']);
        $status = $conn->real_escape_string($_POST['status']);
        $tf = $conn->real_escape_string($_POST['jumlah_transfer']);
        $saldo = $conn->real_escape_string($_POST['get_saldo']);

        $deponya = $conn->query("SELECT * FROM deposit WHERE id = '$get_oid'");
        $datanya = $deponya->fetch_assoc();
    
        $username = $datanya['username'];
        $depositID = $datanya['kode_deposit'];
        
        if ($deponya->num_rows == 0) {
            $_SESSION['hasil'] = array('alert' => 'danger', 'judul' => 'Gagal', 'pesan' => 'Data Deposit Tidak Ditemukan');
        } else if ($datanya['status'] == "Success") { 
        $_SESSION['hasil'] = array('alert' => 'danger', 'judul' => 'Gagal', 'pesan' => 'Ups, Status Deposit Sudah Success Tidak Dapat Diubah');
        } else if ($datanya['status'] == "Error") { 
        $_SESSION['hasil'] = array('alert' => 'danger', 'judul' => 'Gagal', 'pesan' => 'Ups, Status Deposit Sudah Error Tidak Dapat Diubah');
        } else {
            if ($conn->query("UPDATE deposit SET jumlah_transfer = '$tf', get_saldo = '$saldo', status = '$status' WHERE id = '$get_oid'") == true){
            if ($status == "Success") {
                $conn->query("UPDATE users set saldo = saldo + $saldo WHERE username = '$username'");
                $conn->query("INSERT INTO history_saldo VALUES ('', '$username', 'Penambahan Saldo', '$saldo', 'Penambahan Saldo Dengan Deposit ID $depositID', '$date', '$time')");

                $_SESSION['hasil'] = array('alert' => 'success', 'judul' => 'Berhasil', 'pesan' => 'Data Deposit Berhasil Di Update 
                    <br /> Deposit ID : '.$depositID.'
                    <br /> Status : '.$status.'
                    <br /> Tujuan : '.$username.'
                    <br /> Nominal : '.$saldo.'
                    ');
            } else {
                $_SESSION['hasil'] = array('alert' => 'success', 'judul' => 'Berhasil', 'pesan' => 'Data Deposit Berhasil Di Update 
                    <br /> Deposit ID : '.$depositID.'
                    <br /> Status : '.$status.'
                    ');
            }
        } else {
            $_SESSION['hasil'] = array('alert' => 'danger', 'judul' => 'Gagal', 'pesan' => 'Gagal');
        }
        }
        header("Location: " . $_SERVER['REQUEST_URI'] . "");
    exit();
    } else if (isset($_POST['hapus'])) {
        $get_oid = $conn->real_escape_string($_GET['deposit_id']);
        
        $deponya = $conn->query("SELECT * FROM deposit WHERE id = '$get_oid'");
        $datanya = $deponya->fetch_assoc();
    
        $depositID = $datanya['kode_deposit'];
        
        if ($deponya->num_rows == 0) {
                $_SESSION['hasil'] = array('alert' => 'danger', 'judul' => 'Gagal', 'pesan' => 'Data Deposit Tidak Ditemukan');
        } else {
            if ($conn->query("DELETE FROM deposit WHERE id = '$get_oid'") == TRUE) {
                $_SESSION['hasil'] = array('alert' => 'success', 'judul' => 'Berhasil', 'pesan' => 'Data Deposit Berhasil Di Hapus.
                <br /> Deposit ID : '.$depositID.'');
            }
        }
         header("Location: " . $_SERVER['REQUEST_URI'] . "");
    exit();
    }
require '../lib/header_admin.php';
?>
<?php
$jumlah_hari_ini = mysqli_num_rows($conn->query("SELECT * FROM deposit WHERE date = '$date' AND status = 'Success'"));

$total_hari_ini = $conn->query("SELECT SUM(jumlah_transfer) AS total FROM deposit WHERE date = '$date' AND status = 'Success'");
$data_hari_ini = $total_hari_ini->fetch_assoc();
//Hari ini
?>
<div class="content-wrapper">
<?php
if (isset($_SESSION['hasil'])) {
?>
    <div class="alert alert-<?php echo $_SESSION['hasil']['alert'] ?>">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <b><?php echo $_SESSION['hasil']['judul'] ?></b> <?php echo $_SESSION['hasil']['pesan'] ?>
    </div>
<?php
    unset($_SESSION['hasil']);
}
?>
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5>Total Deposit</h5>
                    <div class="row">
                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                <h2 class="mb-0">Rp <?php echo number_format($data_deposit['total'], 0, ',', '.'); ?></h2>
                                <p class="text-success ml-2 mb-0 font-weight-medium">( <?php echo $count_deposit; ?> )</p>
                            </div>
                        </div>
                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                            <i class="icon-lg mdi mdi-credit-card-multiple text-success ml-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h5>Deposit Hari Ini</h5>
                    <div class="row">
                        <div class="col-8 col-sm-12 col-xl-8 my-auto">
                            <div class="d-flex d-sm-block d-md-flex align-items-center">
                                <h2 class="mb-0">Rp <?php echo number_format($data_hari_ini['total'],0,',','.'); ?></h2>
                                <p class="text-success ml-2 mb-0 font-weight-medium">( <?php echo $jumlah_hari_ini; ?> )</p>
                            </div>
                        </div>
                        <div class="col-4 col-sm-12 col-xl-4 text-center text-xl-right">
                            <i class="icon-lg mdi mdi-credit-card text-success ml-auto"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row ">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <form class="form-horizontal" method="GET">
                        <input type="hidden" name="csrf_token" value="<?php echo $config['csrf_token'] ?>">
                        <div class="row">
                            <div class="form-group col-lg-3">
                                <badge>Tampilkan</badge>
                                <select class="form-control" name="tampil">
                                    <option value="10">10</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="500">500</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-3">
                                <badge>Status</badge>
                                <select class="form-control" name="status">
                                    <option value="">Semua</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Success">Success</option>
                                    <option value="Error">Error</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-3">
                                <badge>Cari ID Deposit</badge>
                                <input type="text" class="form-control" name="cari" placeholder="Cari ID" value="">
                            </div>
                            <div class="form-group col-lg-3">
                                <badge>Sumbit Filter</badge>
                                <button type="submit" class="btn btn-dark btn-lg btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th> Deposit ID </th>
                                    <th> Username </th>
                                    <th> Pembayaran </th>
                                    <th> Metode </th>
                                    <th> Waktu </th>
                                    <th> Jumlah Transfer </th>
                                    <th> Jumlah Diterima </th>
                                    <th> Fee </th>
                                    <th> Status </th>
                                    <th> Aksi Pembayaran </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // start paging config
                                if (isset($_GET['cari'])) {
                                    $cari_oid = $conn->real_escape_string(filter($_GET['cari']));
                                    $cari_status = $conn->real_escape_string(filter($_GET['status']));

                                    $cek_deposit = "SELECT * FROM deposit WHERE kode_deposit LIKE '%$cari_oid%' AND status LIKE '%$cari_status%' ORDER BY id DESC"; // edit
                                } else {
                                    $cek_deposit = "SELECT * FROM deposit ORDER BY id DESC"; // edit
                                }
                                if (isset($_GET['cari'])) {
                                    $cari_urut = $conn->real_escape_string(filter($_GET['tampil']));
                                    $records_per_page = $cari_urut; // edit
                                } else {
                                    $records_per_page = 10; // edit
                                }

                                $starting_position = 0;
                                if (isset($_GET["halaman"])) {
                                    $starting_position = ($conn->real_escape_string(filter($_GET["halaman"])) - 1) * $records_per_page;
                                }
                                $new_query = $cek_deposit . " LIMIT $starting_position, $records_per_page";
                                $new_query = $conn->query($new_query);
                                // end paging config
                                while ($data_depo = $new_query->fetch_assoc()) {
                                    if ($data_depo['status'] == "Pending") {
                                        $badge = "warning";
                                    } else if ($data_depo['status'] == "Error") {
                                        $badge = "danger";
                                    } else if ($data_depo['status'] == "Success") {
                                        $badge = "success";
                                    }
                                ?>
                                    <tr>
                                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?deposit_id=<?php echo $data_depo['id']; ?>" class="form-inline" role="form" method="POST">
                                        <td><?php echo $data_depo['kode_deposit']; ?></td>
                                        <td><?php echo $data_depo['username']; ?></td>
                                        <td><?php echo $data_depo['payment']; ?></td>
                                        <td><?php echo $data_depo['place_from']; ?></td>
                                        <td>
                                            <?php echo tanggal_indo($data_depo['date']); ?>, <?php echo $data_depo['time']; ?> WIB
                                        </td>
                                        <td><input type="text" class="form-control" style="width: 100px;" name="jumlah_transfer" value="<?php echo $data_depo['jumlah_transfer']; ?>"></td>
                                        <td><input type="text" class="form-control" style="width: 100px;" name="get_saldo" value="<?php echo $data_depo['get_saldo']; ?>"></td>
                                        <td> Rp <?php echo $data_depo['fee']; ?> </td>
                                        <td>
                                            <select class="form-control" style="width: 100px;" name="status">
                                            <?php if ($data_depo['status'] == "Success") { ?>
                                                <option value="<?php echo $data_depo['status']; ?>"><?php echo $data_depo['status']; ?></option>
                                            <?php } else if ($data_depo['status'] == "Error") { ?>
                                            <option value="<?php echo $data_depo['status']; ?>"><?php echo $data_depo['status']; ?></option>
                                            <?php } else { ?>
                                                <option value="<?php echo $data_depo['status']; ?>"><?php echo $data_depo['status']; ?></option>
                                                <option value="Pending">Pending</option>
                                                <option value="Success">Success</option>
                                                <option value="Error">Error</option>
                                            <?php
                                            }
                                            ?>
                                            </select>
                                        </td>
                                        <td align="center">
                                        <a href="javascript:;" onclick="pembayaran('/admin/ajax/deposit/view.php?id_deposit=<?php echo $data_depo['id']; ?>')" class="btn btn-xs btn-info"><i class="mdi mdi-eye" title="View"></i> View </a>
                                        <button data-toggle="tooltip" title="Update" type="submit" name="update" class="btn btn-xs btn-bordred btn-warning"><i class="mdi mdi-border-color"></i> Update </button>
                                            <button data-toggle="tooltip" title="Hapus" type="submit" name="hapus" class="btn btn-xs btn-bordred btn-danger"><i class="mdi mdi-delete"></i> Hapus </button>
                                        </td>
                                        </form> 
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div><br>
                    <ul class="pagination pagination-sm m-0 float-right">
                        <?php
                        // start paging link
                        if (isset($_GET['cari'])) {
                            $cari_urut = $conn->real_escape_string(filter($_GET['tampil']));
                        } else {
                            $cari_urut =  10;
                        }
                        if (isset($_GET['cari'])) {
                            $cari_oid = $conn->real_escape_string(filter($_GET['cari']));
                            $cari_status = $conn->real_escape_string(filter($_GET['status']));
                            $cari_urut = $conn->real_escape_string(filter($_GET['tampil']));
                        } else {
                            $self = $_SERVER['PHP_SELF'];
                        }
                        $cek_deposit = $conn->query($cek_deposit);
                        $total_records = mysqli_num_rows($cek_deposit);
                        echo "<li class='disabled page-item'><a class='page-link' href='#'>Total : " . $total_records . "</a></li>";
                        if ($total_records > 0) {
                            $total_pages = ceil($total_records / $records_per_page);
                            $current_page = 1;
                            if (isset($_GET["halaman"])) {
                                $current_page = $conn->real_escape_string(filter($_GET["halaman"]));
                                if ($current_page < 1) {
                                    $current_page = 1;
                                }
                            }
                            if ($current_page > 1) {
                                $previous = $current_page - 1;
                                if (isset($_GET['cari'])) {
                                    $cari_oid = $conn->real_escape_string(filter($_GET['cari']));
                                    $cari_status = $conn->real_escape_string(filter($_GET['status']));
                                    $cari_urut = $conn->real_escape_string(filter($_GET['tampil']));
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=1&tampil=" . $cari_urut . "&status=" . $cari_status . "&cari=" . $cari_oid . "'><<</a></li>";
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $previous . "&tampil=" . $cari_urut . "&status=" . $cari_status . "&cari=" . $cari_oid . "'><</a></li>";
                                } else {
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=1'><<</a></li>";
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $previous . "'><</a></li>";
                                }
                            }
                            // limit page
                            $limit_page = $current_page + 3;
                            $limit_show_link = $total_pages - $limit_page;
                            if ($limit_show_link < 0) {
                                $limit_show_link2 = $limit_show_link * 2;
                                $limit_link = $limit_show_link - $limit_show_link2;
                                $limit_link = 3 - $limit_link;
                            } else {
                                $limit_link = 3;
                            }
                            $limit_page = $current_page + $limit_link;
                            // end limit page
                            // start page
                            if ($current_page == 1) {
                                $start_page = 1;
                            } else if ($current_page > 1) {
                                if ($current_page < 4) {
                                    $min_page  = $current_page - 1;
                                } else {
                                    $min_page  = 3;
                                }
                                $start_page = $current_page - $min_page;
                            } else {
                                $start_page = $current_page;
                            }
                            // end start page
                            for ($i = $start_page; $i <= $limit_page; $i++) {
                                if (isset($_GET['cari'])) {
                                    $cari_oid = $conn->real_escape_string(filter($_GET['cari']));
                                    $cari_status = $conn->real_escape_string(filter($_GET['status']));
                                    $cari_urut = $conn->real_escape_string(filter($_GET['tampil']));
                                    if ($i == $current_page) {
                                        echo "<li class='active page-item'><a class='page-link' href='#'>" . $i . "</a></li>";
                                    } else {
                                        echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $i . "&tampil=" . $cari_urut . "&status=" . $cari_status . "&cari=" . $cari_oid . "'>" . $i . "</a></li>";
                                    }
                                } else {
                                    if ($i == $current_page) {
                                        echo "<li class='active page-item'><a class='page-link' href='#'>" . $i . "</a></li>";
                                    } else {
                                        echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $i . "'>" . $i . "</a></li>";
                                    }
                                }
                            }
                            if ($current_page != $total_pages) {
                                $next = $current_page + 1;
                                if (isset($_GET['cari'])) {
                                    $cari_oid = $conn->real_escape_string(filter($_GET['cari']));
                                    $cari_status = $conn->real_escape_string(filter($_GET['status']));
                                    $cari_urut = $conn->real_escape_string(filter($_GET['tampil']));
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $next . "&tampil=" . $cari_urut . "&status=" . $cari_status . "&cari=" . $cari_oid . "'>></a></li>";
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $total_pages . "&tampil=" . $cari_urut . "&status=" . $cari_status . "&cari=" . $cari_oid . "'>>></a></li>";
                                } else {
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $next . "'>></i></a></li>";
                                    echo "<li class='page-item'><a class='page-link' href='" . $self . "?halaman=" . $total_pages . "'>>></a></li>";
                                }
                            }
                        }
                        // end paging link
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!--MODAL-->
<div class="modal fade" id="modal-detail-pembayaran">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-credit-card-multiple"></i> Detail Pembayaran Users</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detail-pembayaran">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php
require '../lib/footer_admin.php';
?>