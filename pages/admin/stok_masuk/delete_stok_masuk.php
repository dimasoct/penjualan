<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil id_stok_masuk dari permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_stok_masuk = isset($_POST['id_stok_masuk']) ? $_POST['id_stok_masuk'] : '';

    if (!empty($id_stok_masuk)) {
        // Mulai transaksi
        $conn->begin_transaction();

        try {
            // Ambil data stok_masuk yang akan dihapus
            $select_sql = "SELECT id_barang, stok_masuk FROM stok_masuk WHERE id_stok_masuk = ?";
            $select_stmt = $conn->prepare($select_sql);
            $select_stmt->bind_param("s", $id_stok_masuk);
            $select_stmt->execute();
            $result = $select_stmt->get_result();

            if ($result->num_rows > 0) {
                $stok_masuk_data = $result->fetch_assoc();
                $id_barang = $stok_masuk_data['id_barang'];
                $stok_masuk = $stok_masuk_data['stok_masuk'];

                // Kurangi stok di tabel stok jika id_barang cocok
                $update_sql = "UPDATE stok SET stok = stok - ? WHERE id_barang = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("is", $stok_masuk, $id_barang);
                $update_stmt->execute();

                // Pastikan stok tidak negatif
                if ($update_stmt->affected_rows > 0) {
                    // Hapus data dari tabel stok_masuk
                    $delete_sql = "DELETE FROM stok_masuk WHERE id_stok_masuk = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("s", $id_stok_masuk);
                    $delete_stmt->execute();

                    // Commit transaksi
                    $conn->commit();
                    echo "Data stok masuk berhasil dihapus.";
                } else {
                    throw new Exception("Stok tidak ditemukan atau gagal diperbarui.");
                }
            } else {
                throw new Exception("Data stok masuk tidak ditemukan.");
            }
        } catch (Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            $conn->rollback();
            echo "Gagal menghapus data stok masuk: " . $e->getMessage();
        }
    } else {
        echo "ID stok masuk tidak valid.";
    }
} else {
    echo "Metode permintaan tidak valid.";
}

// Tutup koneksi
$conn->close();
?>
