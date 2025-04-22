<?php
session_start();
include("ayar.php"); // Veritabanı bağlantısını dahil et
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>İlanlar - Prestij Emlak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body>
  <?php include("header.php"); ?>

  <!-- Arama Bölümü -->
  <section class="search-section">
    <div class="search-container">
      <h1>Tüm İlanlar</h1>
      <form class="search-form" method="GET" action="ilanlar.php">
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

  <div class="container mt-5">
    <!-- İlanlar Bölümü -->
    <div class="listings mt-5">
      <div class="listing-grid">
        <?php
        // Veritabanından ilanları çek
        $query = "
          SELECT 
            il.ilanID,
            il.ilanDurum,
            id.ilanDAciklama,
            id.ilanDFiyat,
            id.ilanDmetreKareBrut,
            id.ilanDOdaSayisi,
            id.ilanDKonumBilgisi,
            id.ilanDMulkTuru,
            a.adresSehir,
            a.adresIlce,
            r.resimUrl
          FROM t_ilanlar il
          JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
          JOIN t_adresler a ON il.ilanAdresID = a.adresID
          LEFT JOIN t_resimler r ON il.ilanID = r.resimIlanID AND r.resimDurum = 1
          WHERE il.ilanDurum = 1
          GROUP BY il.ilanID
        ";
        $result = $baglan->query($query);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            // İlan bilgilerini değişkenlere ata
            $ilanID = $row['ilanID'];
            $ilanAciklama = htmlspecialchars($row['ilanDAciklama']);
            $ilanFiyat = number_format($row['ilanDFiyat'], 2);
            $ilanMetrekare = htmlspecialchars($row['ilanDmetreKareBrut']);
            $ilanOdaSayisi = htmlspecialchars($row['ilanDOdaSayisi']);
            $ilanKonum = htmlspecialchars($row['ilanDKonumBilgisi']);
            $ilanMulkTuru = htmlspecialchars($row['ilanDMulkTuru']);
            $ilanSehir = htmlspecialchars($row['adresSehir']);
            $ilanIlce = htmlspecialchars($row['adresIlce']);
            $ilanResim = $row['resimUrl'] ?: 'default.jpg'; // Resim yoksa varsayılan resim
        ?>
            <!-- Dinamik İlan Kartı -->
            <a href="ilanDetay.php?id=<?php echo $ilanID; ?>" style="text-decoration: none; color: inherit;">
              <div class="listing-card">
                <img src="<?php echo $ilanResim; ?>" alt="İlan Resmi">
                <div class="card-content">
                  <h3><?php echo $ilanMulkTuru; ?></h3>
                  <p><?php echo $ilanOdaSayisi; ?> Oda, <?php echo $ilanMetrekare; ?> m²</p>
                  <p><?php echo $ilanSehir; ?> / <?php echo $ilanIlce; ?></p>
                  <p><strong>Fiyat:</strong> <?php echo $ilanFiyat; ?> TL</p>
                </div>
              </div>
            </a>
        <?php
          }
        } else {
          echo "<p>Henüz ilan bulunmamaktadır.</p>";
        }
        ?>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>