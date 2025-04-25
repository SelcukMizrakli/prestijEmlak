<?php
require_once 'ayar.php'; // Veritabanı bağlantınızı içeren dosya

// CORS ayarları (gerekirse)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Yanıtın JSON formatında olduğunu belirt
header('Content-Type: application/json');

// Yetki düzenleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateYetki') {
    $uyeId = isset($_POST['uyeId']) ? intval($_POST['uyeId']) : 0;
    $uyeYetki = isset($_POST['uyeYetki']) ? intval($_POST['uyeYetki']) : 0;
    
    // Gelen verileri kontrol et
    if ($uyeId > 0 && in_array($uyeYetki, [1, 2, 3])) {
        $query = $baglan->prepare("UPDATE t_uyeler SET uyeYetkiID = ? WHERE uyeID = ?");
        $query->bind_param("ii", $uyeYetki, $uyeId);

        if ($query->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $baglan->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
}
?>