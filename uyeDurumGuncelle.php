<?php
session_start();
include("ayar.php");

// Admin kontrolü
if (!isset($_SESSION['giris']) || !$_SESSION['giris'] || $_SESSION['uyeYetkiID'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkiniz yok']);
    exit;
}

if (!isset($_POST['uyeID']) || !isset($_POST['durum'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz parametreler']);
    exit;
}

$uyeID = intval($_POST['uyeID']);
$durum = intval($_POST['durum']);

$query = $baglan->prepare("UPDATE t_uyeler SET uyeAktiflikDurumu = ? WHERE uyeID = ?");
$query->bind_param("ii", $durum, $uyeID);

if ($query->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => $durum ? 'Hesap başarıyla aktifleştirildi' : 'Hesap başarıyla pasifleştirildi'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Veritabanı hatası']);
}
?>