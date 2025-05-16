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
      <form class="search-form" method="GET" action="">
        <select name="sehir" class="form-control">
          <option value="">İl Seçiniz</option>
          <?php
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

  <div class="container mt-5">
    <!-- İlanlar Bölümü -->
    <div class="listings mt-5">
      <div class="listing-grid">
        <?php
        // Veritabanından ilanları çek
        $query = "SELECT 
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
          WHERE il.ilanDurum = 1";

        // Filtreleme koşulları
        $params = [];
        $types = "";

        if (!empty($_GET['sehir'])) {
          $query .= " AND a.adresSehir = ?";
          $params[] = $_GET['sehir'];
          $types .= "s";
        }

        if (!empty($_GET['ilan_turu'])) {
          $query .= " AND id.ilanDIlanTurID = ?";
          $params[] = $_GET['ilan_turu'];
          $types .= "i";
        }

        if (!empty($_GET['mulk_turu'])) {
          $query .= " AND id.ilanDMulkTuru = ?";
          $params[] = $_GET['mulk_turu'];
          $types .= "s";
        }

        if (!empty($_GET['min_fiyat'])) {
          $query .= " AND id.ilanDFiyat >= ?";
          $params[] = $_GET['min_fiyat'];
          $types .= "d";
        }

        if (!empty($_GET['max_fiyat'])) {
          $query .= " AND id.ilanDFiyat <= ?";
          $params[] = $_GET['max_fiyat'];
          $types .= "d";
        }

        if (!empty($_GET['oda_sayisi'])) {
          $query .= " AND id.ilanDOdaSayisi = ?";
          $params[] = $_GET['oda_sayisi'];
          $types .= "s";
        }

        $query .= " GROUP BY il.ilanID ORDER BY il.ilanYayinTarihi DESC";

        // Sorguyu hazırla ve çalıştır
        if (!empty($params)) {
          $stmt = $baglan->prepare($query);
          $stmt->bind_param($types, ...$params);
          $stmt->execute();
          $result = $stmt->get_result();
        } else {
          $result = $baglan->query($query);
        }

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