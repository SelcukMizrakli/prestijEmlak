<?php
session_start();
include("ayar.php");

header('Content-Type: application/json');

try {
    // Oturum kontrolü
    if (!isset($_SESSION['giris']) || $_SESSION['giris'] !== true) {
        throw new Exception("Giriş yapmanız gerekiyor");
    }

    // POST verilerini kontrol et
    if (!isset($_POST['konusmaID']) || !isset($_POST['mesajText'])) {
        throw new Exception("Gerekli veriler eksik");
    }

    $konusmaID = intval($_POST['konusmaID']);
    $mesajText = trim($_POST['mesajText']);
    $gonderenID = $_SESSION['uyeID'];

    // Konuşmanın diğer katılımcısını bul
    // Since t_konusmalar does not have participant columns, find the other participant from messages
    $konusmaSorgu = $baglan->prepare("
        SELECT 
            CASE 
                WHEN m.mesajIletenID = ? THEN m.mesajAlanID
                ELSE m.mesajIletenID
            END as aliciID
        FROM t_mesajlar m
        WHERE m.mesajKonusmaID = ?
        LIMIT 1
    ");
    
    $konusmaSorgu->bind_param("ii", $gonderenID, $konusmaID);
    $konusmaSorgu->execute();
    $result = $konusmaSorgu->get_result();
    $konusma = $result->fetch_assoc();

    if (!$konusma) {
        // If no messages yet, we cannot find other participant from messages
        // So we can assume the other participant is the ilan owner from t_konusmalar's konusmaIlanID
        $ilanSorgu = $baglan->prepare("
            SELECT il.ilanUyeID
            FROM t_konusmalar k
            JOIN t_ilanlar il ON k.konusmaIlanID = il.ilanID
            WHERE k.konusmaID = ?
        ");
        $ilanSorgu->bind_param("i", $konusmaID);
        $ilanSorgu->execute();
        $ilanResult = $ilanSorgu->get_result();
        $ilan = $ilanResult->fetch_assoc();

        if (!$ilan) {
            throw new Exception("Konuşma veya ilan sahibi bulunamadı");
        }

        $ilanSahibiID = $ilan['ilanUyeID'];

        if ($gonderenID == $ilanSahibiID) {
            throw new Exception("Konuşma katılımcısı bulunamadı");
        }

        $konusma = ['aliciID' => $ilanSahibiID];
    }

    // Mesajı veritabanına ekle
    $mesajEkle = $baglan->prepare("
        INSERT INTO t_mesajlar (
            mesajKonusmaID, 
            mesajIletenID, 
            mesajAlanID, 
            mesajText, 
            mesajGonderilmeTarihi,
            mesajOkunduDurumu
        ) VALUES (?, ?, ?, ?, NOW(), 0)
    ");

    $mesajEkle->bind_param("iiis", 
        $konusmaID, 
        $gonderenID, 
        $konusma['aliciID'], 
        $mesajText
    );

    if (!$mesajEkle->execute()) {
        throw new Exception("Mesaj gönderilemedi");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Mesaj başarıyla gönderildi'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>