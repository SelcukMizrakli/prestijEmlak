<?php
session_start();
include("ayar.php"); // Veritabanı bağlantısını dahil et
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
      <form class="search-form" method="GET" action="">
        <select name="sehir" class="form-control">
          <option value="">İl Seçiniz</option>
          <?php
          // Aktif adresleri getiren sorgu
          $sehirQuery = "SELECT DISTINCT adresSehir 
                           FROM t_adresler a 
                           JOIN t_ilanlar i ON a.adresID = i.ilanAdresID 
                           WHERE i.ilanDurum = 1
                           ORDER BY adresSehir";

          $sehirResult = $baglan->query($sehirQuery);

          if ($sehirResult && $sehirResult->num_rows > 0) {
            while ($sehir = $sehirResult->fetch_assoc()) {
              $selected = (isset($_GET['sehir']) && $_GET['sehir'] == $sehir['adresSehir']) ? 'selected' : '';
              echo "<option value='" . htmlspecialchars($sehir['adresSehir']) . "' $selected>" .
                htmlspecialchars($sehir['adresSehir']) .
                "</option>";
            }
          }
          ?>
        </select>

        <select name="ilan_turu">
          <option value="">İlan Türü</option>
          <?php
          $turQuery = "SELECT ilanTurID, ilanTurAdi FROM t_ilantur ORDER BY ilanTurAdi";
          $turResult = $baglan->query($turQuery);
          while ($tur = $turResult->fetch_assoc()) {
            $selected = (isset($_GET['ilan_turu']) && $_GET['ilan_turu'] == $tur['ilanTurID']) ? 'selected' : '';
            echo "<option value='" . $tur['ilanTurID'] . "' $selected>" . htmlspecialchars($tur['ilanTurAdi']) . "</option>";
          }
          ?>
        </select>

        <select name="mulk_turu">
          <option value="">Mülk Türü</option>
          <option value="ev" <?php echo (isset($_GET['mulk_turu']) && $_GET['mulk_turu'] == 'ev') ? 'selected' : ''; ?>>Ev</option>
          <option value="daire" <?php echo (isset($_GET['mulk_turu']) && $_GET['mulk_turu'] == 'daire') ? 'selected' : ''; ?>>Daire</option>
          <option value="ofis" <?php echo (isset($_GET['mulk_turu']) && $_GET['mulk_turu'] == 'ofis') ? 'selected' : ''; ?>>Ofis</option>
        </select>

        <input type="number" name="min_fiyat" placeholder="Min Fiyat" value="<?php echo isset($_GET['min_fiyat']) ? htmlspecialchars($_GET['min_fiyat']) : ''; ?>">
        <input type="number" name="max_fiyat" placeholder="Max Fiyat" value="<?php echo isset($_GET['max_fiyat']) ? htmlspecialchars($_GET['max_fiyat']) : ''; ?>">
        <input type="text" name="oda_sayisi" placeholder="Oda Sayısı" value="<?php echo isset($_GET['oda_sayisi']) ? htmlspecialchars($_GET['oda_sayisi']) : ''; ?>">

        <button type="submit" class="btn btn-secondary">Ara</button>
        <button type="button" class="btn btn-secondary" onclick="clearForm()">Temizle</button>
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

  // Filtreleme sorgusu
  $sql = "SELECT DISTINCT
            il.ilanID,
            id.ilanDAciklama,
            id.ilanDFiyat,
            id.ilanDOdaSayisi,
            id.ilanDMulkTuru,
            a.adresSehir,
            a.adresIlce,
            (SELECT r.resimUrl FROM t_resimler r WHERE r.resimIlanID = il.ilanID AND r.resimDurum = 1 LIMIT 1) AS resimYolu
        FROM t_ilanlar il
        JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
        JOIN t_adresler a ON il.ilanAdresID = a.adresID
        WHERE il.ilanDurum = 1"; // Sadece aktif ilanları getir

  // Filtreleme koşulları
  $params = [];
  $types = "";

  if (!empty($_GET['sehir'])) {
    $sql .= " AND a.adresSehir = ?";
    $params[] = $_GET['sehir'];
    $types .= "s";
  }

  if (!empty($_GET['ilan_turu'])) {
    $sql .= " AND id.ilanDIlanTurID = ?";
    $params[] = $_GET['ilan_turu'];
    $types .= "i";
  }

  if (!empty($_GET['mulk_turu'])) {
    $sql .= " AND id.ilanDMulkTuru = ?";
    $params[] = $_GET['mulk_turu'];
    $types .= "s";
  }

  if (!empty($_GET['min_fiyat'])) {
    $sql .= " AND id.ilanDFiyat >= ?";
    $params[] = $_GET['min_fiyat'];
    $types .= "d";
  }

  if (!empty($_GET['max_fiyat'])) {
    $sql .= " AND id.ilanDFiyat <= ?";
    $params[] = $_GET['max_fiyat'];
    $types .= "d";
  }

  if (!empty($_GET['oda_sayisi'])) {
    $sql .= " AND id.ilanDOdaSayisi = ?";
    $params[] = $_GET['oda_sayisi'];
    $types .= "s";
  }

  $sql .= " ORDER BY il.ilanYayinTarihi DESC";

  // Sorguyu hazırla ve çalıştır
  if (!empty($params)) {
    $stmt = $baglan->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {
    $result = $baglan->query($sql);
  }

  // Sonuçları görüntüle
  if ($result && $result->num_rows > 0) {
    echo '<div class="listings">';
    echo '<div class="listing-grid">';
    while ($row = $result->fetch_assoc()) {
      $ilanID = $row['ilanID'];
      $resimYolu = $row['resimYolu'] ?? 'https://via.placeholder.com/300x200'; // Varsayılan resim
      $ilanBaslik = $row['ilanDAciklama'] ?? 'Başlık bulunamadı'; // Varsayılan başlık
      $ilanFiyat = $row['ilanDFiyat'] ?? 'Fiyat belirtilmemiş'; // Varsayılan fiyat

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
    echo '<div class="listings"><p>Arama kriterlerinize uygun ilan bulunamadı.</p></div>';
  }
  $baglan->close();
  ?>


  <!-- Footer -->
  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function clearForm() {
      // Tüm select elementlerini temizle
      document.querySelectorAll('.search-form select').forEach(select => {
        select.selectedIndex = 0;
      });

      // Tüm input elementlerini temizle
      document.querySelectorAll('.search-form input').forEach(input => {
        input.value = '';
      });

      // Sayfayı URL parametreleri olmadan yenile
      window.location.href = window.location.pathname;
    }
  </script>
</body>

</html>