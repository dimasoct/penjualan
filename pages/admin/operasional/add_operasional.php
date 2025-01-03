<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi pesan sukses/gagal
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $keterangan = $_POST['keterangan'];
    $jumlah_biaya = $_POST['jumlah_biaya'];
    $tanggal_biaya = $_POST['tanggal_biaya'];

    // Validasi input
    if (!empty($keterangan) && !empty($jumlah_biaya) && !empty($tanggal_biaya)) {
        // Query untuk memasukkan data
        $query = "INSERT INTO operasional (keterangan, jumlah_biaya, tanggal_biaya) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sis", $keterangan, $jumlah_biaya, $tanggal_biaya);

        if ($stmt->execute()) {
            header("Location: operasional.php?success=true");
            exit;
        } else {
            $message = "Terjadi kesalahan: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Semua kolom harus diisi.";
    }
}

$conn->close();
?>

<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Tambah Biaya Operasional</strong></h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info"> <?php echo $message; ?> </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_biaya" class="form-label">Jumlah Biaya</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="jumlah_biaya" name="jumlah_biaya" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_biaya" class="form-label">Tanggal Biaya</label>
                                <input type="date" class="form-control" id="tanggal_biaya" name="tanggal_biaya" required>
                            </div><br>
                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col-6">
                                        <a href="operasional.php" class="btn btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary w-100">Add Biaya Operasional</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../footer/footer.php'; ?>
