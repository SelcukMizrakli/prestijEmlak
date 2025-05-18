<?php
session_start();
include("ayar.php");

// Oturum ve konuşma ID kontrolü
if (!isset($_SESSION['giris']) || !isset($_GET['konusmaID'])) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

$konusmaID = intval($_GET['konusmaID']);
$kullaniciID = $_SESSION['uyeID'];

try {
    // Okunmamış mesajları okundu olarak işaretle
    $baglan->query("
        UPDATE t_mesajlar 
        SET mesajOkunduDurumu = 1 
        WHERE mesajKonusmaID = $konusmaID 
        AND mesajAlanID = $kullaniciID 
        AND mesajOkunduDurumu = 0
    ");

    // Mesajları getir
    $mesajlarSorgu = $baglan->prepare("
        SELECT 
            m.*,
            CONCAT(u.uyeAd, ' ', u.uyeSoyad) as gonderenAdSoyad,
            DATE_FORMAT(m.mesajGonderilmeTarihi, '%d.%m.%Y %H:%i') as mesajGonderilmeTarihi
        FROM t_mesajlar m
        JOIN t_uyeler u ON m.mesajIletenID = u.uyeID
        WHERE m.mesajKonusmaID = ?
        ORDER BY m.mesajGonderilmeTarihi ASC
    ");

    $mesajlarSorgu->bind_param("i", $konusmaID);
    $mesajlarSorgu->execute();
    $result = $mesajlarSorgu->get_result();

    $mesajlar = [];
    while ($mesaj = $result->fetch_assoc()) {
        $mesajlar[] = [
            'mesajID' => $mesaj['mesajID'],
            'mesajText' => $mesaj['mesajText'],
            'mesajIletenID' => $mesaj['mesajIletenID'],
            'gonderenAdSoyad' => $mesaj['gonderenAdSoyad'],
            'mesajGonderilmeTarihi' => $mesaj['mesajGonderilmeTarihi'],
            'mesajOkunduDurumu' => $mesaj['mesajOkunduDurumu']
        ];
    }

    echo json_encode([
        'success' => true,
        'mesajlar' => $mesajlar
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu: ' . $e->getMessage()
    ]);
}
?>