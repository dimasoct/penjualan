<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validasi tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Validasi format tanggal dan pastikan tanggal valid
if (!strtotime($start_date) || !strtotime($end_date)) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Validasi tanggal akhir tidak lebih dari hari ini
if (strtotime($end_date) > strtotime(date('Y-m-d'))) {
    $end_date = date('Y-m-d');
}

// Validasi tanggal awal tidak lebih besar dari tanggal akhir
if (strtotime($start_date) > strtotime($end_date)) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Hitung uang modal dari stok masuk dengan filter tanggal_masuk
$sql_modal = "SELECT COALESCE(SUM(stok_masuk * harga_beli), 0) as total_modal 
              FROM stok_masuk 
              WHERE tanggal_masuk BETWEEN ? AND ?";
$stmt_modal = $conn->prepare($sql_modal);
$stmt_modal->bind_param("ss", $start_date, $end_date);
$stmt_modal->execute();
$result_modal = $stmt_modal->get_result();
$row_modal = $result_modal->fetch_assoc();
$total_modal = $row_modal['total_modal'];

// Hitung uang penjualan dari stok keluar dengan filter tanggal_keluar
$sql_penjualan = "SELECT COALESCE(SUM(stok_keluar * harga_jual), 0) as total_penjualan 
                  FROM stok_keluar 
                  WHERE tanggal_keluar BETWEEN ? AND ?";
$stmt_penjualan = $conn->prepare($sql_penjualan);
$stmt_penjualan->bind_param("ss", $start_date, $end_date);
$stmt_penjualan->execute();
$result_penjualan = $stmt_penjualan->get_result();
$row_penjualan = $result_penjualan->fetch_assoc();
$total_penjualan = $row_penjualan['total_penjualan'];

// Hitung total biaya operasional dengan filter tanggal_biaya
$sql_operasional = "SELECT COALESCE(SUM(jumlah_biaya), 0) as total_biaya 
                    FROM operasional 
                    WHERE tanggal_biaya BETWEEN ? AND ?";
$stmt_operasional = $conn->prepare($sql_operasional);
$stmt_operasional->bind_param("ss", $start_date, $end_date);
$stmt_operasional->execute();
$result_operasional = $stmt_operasional->get_result();
$row_operasional = $result_operasional->fetch_assoc();
$total_operasional = $row_operasional['total_biaya'];

// Hitung keuntungan kotor
$keuntungan_kotor = $total_penjualan - $total_modal;

// Hitung keuntungan bersih
$keuntungan_bersih = $keuntungan_kotor - $total_operasional;

?>
<?php include '../header/header.php';?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Analisis</strong> Keuangan</h1>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Form filter tanggal -->
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date">Tanggal Mulai:</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date">Tanggal Akhir:</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-t'); ?>" class="btn btn-secondary">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 10%; text-align: center">No</th>
                                        <th style="width: 45%; text-align: center">Keterangan</th>
                                        <th style="width: 45%; text-align: center">Jumlah (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width: 10%; text-align: center">1</td>
                                        <td style="width: 45%; text-align: center">Modal (Stok Masuk)</td>
                                        <td style="width: 45%; text-align: center">Rp <?php echo number_format($total_modal, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 10%; text-align: center">2</td>
                                        <td style="width: 45%; text-align: center">Penjualan (Stok Keluar)</td>
                                        <td style="width: 45%; text-align: center">Rp <?php echo number_format($total_penjualan, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 10%; text-align: center">3</td>
                                        <td style="width: 45%; text-align: center">Keuntungan Kotor</td>
                                        <td style="width: 45%; text-align: center">Rp <?php echo number_format($keuntungan_kotor, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 10%; text-align: center">4</td>
                                        <td style="width: 45%; text-align: center">Biaya Operasional</td>
                                        <td style="width: 45%; text-align: center">Rp <?php echo number_format($total_operasional, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="width: 10%; text-align: center">5</td>
                                        <td style="width: 45%; text-align: center"><strong>Keuntungan Bersih</strong></td>
                                        <td style="width: 45%; text-align: center"><strong>Rp <?php echo number_format($keuntungan_bersih, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../footer/footer.php';?>
