<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Periksa apakah id_user dikirim
if (isset($_POST['id_user'])) {
    $id_user = $_POST['id_user'];

    // Query untuk menghapus data user
    $query = "DELETE FROM users WHERE id_user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus user.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID user tidak ditemukan.']);
}

$conn->close();
?>
