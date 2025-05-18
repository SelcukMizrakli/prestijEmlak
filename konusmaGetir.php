<?php
session_start();
include("ayar.php");

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
        throw new Exception("Giriş yapmanız gerekiyor");
    }

    if (!isset($_POST['ilanID']) || !isset($_POST['aliciID'])) {
        throw new Exception("Gerekli veriler eksik");
    }

    $gonderenID = $_SESSION['uyeID'];
    $ilanID = intval($_POST['ilanID']);
    $aliciID = intval($_POST['aliciID']);

    if ($gonderenID === $aliciID) {
        throw new Exception("Kendinize mesaj gönderemezsiniz");
    }

    // Konuşma var mı kontrol et
    $kontrolSorgu = $baglan->prepare("SELECT konusmaID FROM t_konusmalar WHERE konusmaIlanID = ? LIMIT 1");
    $kontrolSorgu->bind_param("i", $ilanID);
    $kontrolSorgu->execute();
    $result = $kontrolSorgu->get_result();

    if ($result->num_rows > 0) {
        $konusma = $result->fetch_assoc();
        $konusmaID = $konusma['konusmaID'];
    } else {
        // Yeni konuşma oluştur
        $ekleSorgu = $baglan->prepare("INSERT INTO t_konusmalar (konusmaIlanID, konusmaBaslangicTarihi, konusmaGuncellenmeTarihi) VALUES (?, NOW(), NOW())");
        $ekleSorgu->bind_param("i", $ilanID);
        if (!$ekleSorgu->execute()) {
            throw new Exception("Konuşma oluşturulamadı");
        }
        $konusmaID = $baglan->insert_id;
    }

    echo json_encode([
        'success' => true,
        'konusmaID' => $konusmaID
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
