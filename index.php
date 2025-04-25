<?php
session_start();
// Eğer kullanıcı giriş yapmışsa, session'dan bilgileri çekiyoruz.
$loggedIn = isset($_SESSION['giris']) && isset($_SESSION['uyeAd']) && isset($_SESSION['uyeMail']);
if ($loggedIn) {
  $kullaniciAdi   = $_SESSION['uyeAd'];
  $kullaniciEmail = $_SESSION['uyeMail'];
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prestij Emlak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <style>

  </style>
</head>

<body>
  <?php include("header.php"); ?>

  <!-- Arama Bölümü -->
  <section class="search-section">
    <div class="search-container">
      <h1>Hayalinizdeki Evi Bulun</h1>
      <form class="search-form">
        <select name="il">
          <option value="">İl Seçiniz</option>
          <option value="istanbul">İstanbul</option>
          <option value="ankara">Ankara</option>
          <option value="izmir">İzmir</option>
        </select>
        <select name="ilan-turu">
          <option value="">İlan Türü</option>
          <option value="satilik">Satılık</option>
          <option value="kiralik">Kiralık</option>
        </select>
        <input type="text" name="fiyat" placeholder="Fiyat Aralığı">
        <input type="text" name="oda" placeholder="Oda Sayısı">
        <button type="submit" class="btn btn-secondary">Ara</button>
        <button type="reset" class="btn btn-secondary">Temizle</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='ilanEkle.php'">İlan Ekle</button>
      </form>
    </div>
  </section>

  <?php
  // Veritabanı bağlantı ayarları
  include("ayar.php");
  if ($baglan->connect_error) {
    die("Bağlantı hatası: " . $baglan->connect_error);
  }

  // En çok görüntülenen 5 ilanı çekiyoruz
  $sql = "SELECT 
            i.istatistikGoruntulenmeSayisi,
            il.ilanID,
            id.ilanDAciklama AS ilanBaslik, -- Başlık olarak ilanDAciklama kullanılıyor
            id.ilanDFiyat,
            (SELECT r.resimUrl FROM t_resimler r WHERE r.resimIlanID = il.ilanID LIMIT 1) AS resimYolu
          FROM t_istatistik i
          JOIN t_ilanlar il ON i.istatistikIlanID = il.ilanID
          JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
          ORDER BY i.istatistikGoruntulenmeSayisi DESC 
          LIMIT 5";

  $result = $baglan->query($sql);

  if ($result->num_rows > 0) {
    echo '<div class="listings">';
    echo '<div class="listing-grid">';
    while ($row = $result->fetch_assoc()) {
      $ilanID = $row['ilanID'];
      $resimYolu = $row['resimYolu'] ?? 'https://via.placeholder.com/300x200'; // Varsayılan resim
      $ilanBaslik = $row['ilanBaslik'] ?? 'Başlık bulunamadı'; // Varsayılan başlık
      $ilanFiyat = $row['ilanDFiyat'] ?? 'Fiyat belirtilmemiş'; // Varsayılan fiyat

      // Additional details from ilanlar.php query for display
      // We need to fetch ilan details similar to ilanlar.php for consistent display
      // So we will do a separate query to get ilan details for each ilanID or join in main query

      // To keep it simple, let's modify the main query to include the additional fields from ilanlar.php:
      // ilanDmetreKareBrut, ilanDOdaSayisi, ilanDKonumBilgisi, ilanDMulkTuru, adresSehir, adresIlce

      // But since we cannot modify the query here, let's do a separate query for each ilanID to get these details

      $detailsSql = "SELECT 
                      id.ilanDAciklama,
                      id.ilanDFiyat,
                      id.ilanDmetreKareBrut,
                      id.ilanDOdaSayisi,
                      id.ilanDKonumBilgisi,
                      id.ilanDMulkTuru,
                      a.adresSehir,
                      a.adresIlce
                    FROM t_ilandetay id
                    JOIN t_ilanlar il ON il.ilanID = id.ilanDilanID
                    JOIN t_adresler a ON il.ilanAdresID = a.adresID
                    WHERE il.ilanID = " . intval($ilanID);

      $detailsResult = $baglan->query($detailsSql);
      $details = $detailsResult->fetch_assoc();

      $ilanAciklama = htmlspecialchars($details['ilanDAciklama'] ?? 'Açıklama bulunamadı');
      $ilanMetrekare = htmlspecialchars($details['ilanDmetreKareBrut'] ?? '');
      $ilanOdaSayisi = htmlspecialchars($details['ilanDOdaSayisi'] ?? '');
      $ilanKonum = htmlspecialchars($details['ilanDKonumBilgisi'] ?? '');
      $ilanMulkTuru = htmlspecialchars($details['ilanDMulkTuru'] ?? '');
      $ilanSehir = htmlspecialchars($details['adresSehir'] ?? '');
      $ilanIlce = htmlspecialchars($details['adresIlce'] ?? '');
      $ilanFiyatFormatted = number_format($ilanFiyat, 2);

      echo '<a href="ilanDetay.php?id=' . htmlspecialchars($ilanID) . '" style="text-decoration: none; color: inherit;">';
      echo '<div class="listing-card">';
      echo '<img src="' . htmlspecialchars($resimYolu) . '" alt="İlan Resmi">';
      echo '<div class="card-content">';
      echo '<h3>' . htmlspecialchars($ilanMulkTuru) . '</h3>';
      echo '<p>' . $ilanOdaSayisi . ' Oda, ' . $ilanMetrekare . ' m²</p>';
      echo '<p>' . $ilanSehir . ' / ' . $ilanIlce . '</p>';
      echo '<p><strong>Fiyat:</strong> ' . $ilanFiyatFormatted . ' TL</p>';
      echo '</div>';
      echo '</div>';
      echo '</a>';
    }
    echo '</div>';
    echo '</div>';
  } else {
    echo '<div class="listings"><p>Öne çıkan ilan bulunamadı.</p></div>';
  }
  $baglan->close();
  ?>


  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>