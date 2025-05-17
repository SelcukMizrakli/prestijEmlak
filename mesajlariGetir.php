<?php
session_start();
include("ayar.php");

if (!isset($_SESSION['giris']) || !isset($_GET['konusmaID'])) {
    echo json_encode(['success' => false]);
    exit;
}

$konusmaID = intval($_GET['konusmaID']);
$kullaniciID = $_SESSION['uyeID'];

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
        u.uyeAd,
        u.uyeSoyad
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
        'mesajIletenID' => $mesaj['mesajIletenID'],
        'mesajText' => htmlspecialchars($mesaj['mesajText']),
        'mesajGonderilmeTarihi' => date('d.m.Y H:i', strtotime($mesaj['mesajGonderilmeTarihi'])),
        'gonderenAd' => htmlspecialchars($mesaj['uyeAd'] . ' ' . $mesaj['uyeSoyad']),
        'mesajOkunduDurumu' => $mesaj['mesajOkunduDurumu']
    ];
}

echo json_encode([
    'success' => true,
    'mesajlar' => $mesajlar
]);

// Auto-refresh için son mesaj ID'sini kaydet
if (!empty($mesajlar)) {
    $_SESSION['son_mesaj_id'] = end($mesajlar)['mesajID'];
}
?>