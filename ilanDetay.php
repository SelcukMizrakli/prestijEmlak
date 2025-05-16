<?php
session_start();
include("ayar.php");

$loggedIn = isset($_SESSION['giris']) && isset($_SESSION['uyeAd']) && isset($_SESSION['uyeMail']);
if ($loggedIn) {
    $kullaniciAdi   = $_SESSION['uyeAd'];
    $kullaniciEmail = $_SESSION['uyeMail'];
}

// İlan ID'sini al
if (isset($_GET['id'])) {
    $ilanID = intval($_GET['id']);
} else {
    // Eğer ID yoksa, ana sayfaya yönlendir
    header("Location: index.php");
    exit;
}

// İlan bilgilerini al
$sorgu = $baglan->prepare("SELECT 
    il.ilanID,
    uye.uyeAd,
    uye.uyeSoyad,
    uye.uyeTelNo,
    uye.uyeMail,
    id.ilanDAciklama,
    id.ilanDFiyat,
    id.ilanDmetreKareBrut,
    id.ilanDmetreKareNet,
    id.ilanDOdaSayisi,
    id.ilanDBinaYasi,
    id.ilanDSiteIcerisindeMi,
    id.ilanDMulkTuru,
    id.ilanDKonumBilgisi,
    id.ilanDIsitmaTipi,
    id.ilanDBulunduguKatSayisi,
    id.ilanDBinaKatSayisi,
    mt.mulkTipiBaslik,
    it.ilanTurAdi,
    a.adresBaslik,
    a.adresMahalle,
    a.adresIlce,
    a.adresSehir,
    a.adresUlke,
    a.adresPostaKodu,
    GROUP_CONCAT(r.resimUrl) as resimler
FROM t_ilanlar il
JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
JOIN t_mulktipi mt ON id.ilanDMulkTipiID = mt.mulkTipID
JOIN t_ilantur it ON id.ilanDIlanTurID = it.ilanTurID
JOIN t_adresler a ON il.ilanAdresID = a.adresID
JOIN t_uyeler uye ON il.ilanUyeID = uye.uyeID
LEFT JOIN t_resimler r ON il.ilanID = r.resimIlanID
WHERE il.ilanID = ?
GROUP BY il.ilanID");
$sorgu->bind_param("i", $ilanID);
$sorgu->execute();
$result = $sorgu->get_result();
$ilan = $result->fetch_object();

if (!$ilan) {
    echo "İlan bulunamadı.";
    exit;
}

// Varsayılan değerler
$fiyat = $ilan->ilanDFiyat ?? 0;
$metreKareBrut = $ilan->ilanDmetreKareBrut ?? 'Belirtilmemiş';
$metreKareNet = $ilan->ilanDmetreKareNet ?? 'Belirtilmemiş';
$odaSayisi = $ilan->ilanDOdaSayisi ?? 'Belirtilmemiş';
$binaYasi = $ilan->ilanDBinaYasi ?? 'Belirtilmemiş';
$siteIcerisindeMi = $ilan->ilanDSiteIcerisindeMi ? 'Evet' : 'Hayır';
$mulkTipi = $ilan->mulkTipiBaslik ?? 'Belirtilmemiş';
$ilanTuru = $ilan->ilanTurAdi ?? 'Belirtilmemiş';
$konum = $ilan->ilanDKonumBilgisi ?? 'Belirtilmemiş';
$isitmaTipi = $ilan->ilanDIsitmaTipi ?? 'Belirtilmemiş';
$bulunduguKat = $ilan->ilanDBulunduguKatSayisi ?? 'Belirtilmemiş';
$binaKatSayisi = $ilan->ilanDBinaKatSayisi ?? 'Belirtilmemiş';
$adresBaslik = $ilan->adresBaslik ?? 'Belirtilmemiş';
$adresMahalle = $ilan->adresMahalle ?? 'Belirtilmemiş';
$adresIlce = $ilan->adresIlce ?? 'Belirtilmemiş';
$adresSehir = $ilan->adresSehir ?? 'Belirtilmemiş';
$adresUlke = $ilan->adresUlke ?? 'Belirtilmemiş';
$adresPostaKodu = $ilan->adresPostaKodu ?? 'Belirtilmemiş';
$kullaniciSoyadi = $ilan->uyeSoyad ?? 'Belirtilmemiş';
$kullaniciTelefon = $ilan->uyeTelNo ?? 'Belirtilmemiş';
$ilanAciklama = $ilan->ilanDAciklama ?? 'Açıklama bulunamadı';
$ilanVerenAd = $ilan->uyeAd ?? 'Belirtilmemiş';
$ilanVerenSoyad = $ilan->uyeSoyad ?? 'Belirtilmemiş';
$ilanVerenTelefon = $ilan->uyeTelNo ?? 'Belirtilmemiş';
$ilanVerenEmail = $ilan->uyeMail ?? 'Belirtilmemiş';
// Resimleri ayır
$resimler = isset($ilan->resimler) && $ilan->resimler !== null ? explode(',', $ilan->resimler) : [];

// Favori durumu kontrolü
$favoriDurumu = false;
if ($loggedIn) {
    $favoriSorgu = $baglan->prepare("SELECT favoriID FROM t_favoriler 
        WHERE favoriUyeID = ? AND favoriIlanID = ? ");
    $favoriSorgu->bind_param("ii", $_SESSION['uyeID'], $ilanID);
    $favoriSorgu->execute();
    $favoriSonuc = $favoriSorgu->get_result();
    $favoriDurumu = $favoriSonuc->num_rows > 0;
}

?>

<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prestij Emlak - İlan Detay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <?php include("header.php"); ?>
    <div class="container mt-5">
        <div class="image-container">
            <button class="nav-button left" onclick="changeImage(-1)">&#10094;</button>
            <img src="<?php echo htmlspecialchars($resimler[0] ?? ''); ?>" alt="İlan Resmi" class="large-image" id="currentImage">
            <button class="nav-button right" onclick="changeImage(1)">&#10095;</button>
        </div>
        <div class="thumbnail-container">
            <?php foreach ($resimler as $index => $resim): ?>
                <img src="<?php echo htmlspecialchars($resim); ?>" alt="Küçük Resim" class="thumbnail" onclick="document.getElementById('currentImage').src='<?php echo htmlspecialchars($resim); ?>';">
            <?php endforeach; ?>
        </div>
        <div class="details-container mt-4">
            <div class="row">
                <div class="col-md-6 ">
                    <h3>İlan Bilgileri</h3>
                    <?php if ($loggedIn): ?>
                        <button id="favoriButton" class="btn <?php echo $favoriDurumu ? 'btn-danger' : 'btn-outline-danger'; ?> mb-3" onclick="favoriEkle(<?php echo $ilanID; ?>)">
                            <i class="fas fa-heart"></i> <?php echo $favoriDurumu ? 'Favorilerden Çıkar' : 'Favorilere Ekle'; ?>
                        </button>
                    <?php endif; ?>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Fiyat:</strong> <?php echo number_format($fiyat, 2); ?> TL</li>
                        <li class="list-group-item"><strong>Metrekare (Brüt):</strong> <?php echo $metreKareBrut; ?> m²</li>
                        <li class="list-group-item"><strong>Metrekare (Net):</strong> <?php echo $metreKareNet; ?> m²</li>
                        <li class="list-group-item"><strong>Oda Sayısı:</strong> <?php echo $odaSayisi; ?></li>
                        <li class="list-group-item"><strong>Bina Yaşı:</strong> <?php echo $binaYasi; ?></li>
                        <li class="list-group-item"><strong>Site İçerisinde Mi:</strong> <?php echo $siteIcerisindeMi; ?></li>
                        <li class="list-group-item"><strong>Mülk Türü:</strong> <?php echo $mulkTipi; ?></li>
                        <li class="list-group-item"><strong>İlan Türü:</strong> <?php echo $ilanTuru; ?></li>
                        <li class="list-group-item"><strong>Konum:</strong> <?php echo $konum; ?></li>
                        <li class="list-group-item"><strong>Isıtma Tipi:</strong> <?php echo $isitmaTipi; ?></li>
                        <li class="list-group-item"><strong>Bulunduğu Kat:</strong> <?php echo $bulunduguKat; ?></li>
                        <li class="list-group-item"><strong>Bina Kat Sayısı:</strong> <?php echo $binaKatSayisi; ?></li>
                        <li class="list-group-item"><strong>Açıklama:</strong> <?php echo $ilanAciklama; ?></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3>İlan Veren Bilgileri</h3>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>Ad Soyad:</strong> 
                            <?php echo htmlspecialchars($ilanVerenAd . ' ' . $ilanVerenSoyad); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Telefon:</strong> 
                            <?php echo htmlspecialchars($ilanVerenTelefon); ?>
                        </li>
                        <li class="list-group-item">
                            <strong>E-posta:</strong> 
                            <?php echo htmlspecialchars($ilanVerenEmail); ?>
                        </li>
                    </ul>
                    <br><br>
                    <h3>Adres Bilgileri</h3>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>Mahalle:</strong> <?php echo $adresMahalle; ?><br>
                            <strong>İlçe:</strong> <?php echo $adresIlce; ?><br>
                            <strong>Şehir:</strong> <?php echo $adresSehir; ?>
                        </li>
                        <li class="list-group-item"><strong>Ülke:</strong> <?php echo $adresUlke; ?></li>
                        <li class="list-group-item"><strong>Posta Kodu:</strong> <?php echo $adresPostaKodu; ?></li>
                    </ul>
                </div>
            </div>
            <div class="map-container ">
                <br><h2 style="margin-left: 33%;">Konum Bilgisi</h2>
                <?php
                $fullAddress = trim($adresMahalle . ', ' . $adresIlce . ', ' . $adresSehir);
                if (!empty($fullAddress)): ?>
                    <iframe
                        src="https://www.google.com/maps?q=<?php echo urlencode($fullAddress); ?>&output=embed"
                        width="50%"
                        height="300"
                        style="border:0; margin-left: 25%;"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                <?php else: ?>
                    <p>Adres bilgisi mevcut değil.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
        <p><a href="#" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="#" style="color: #ff6600; text-decoration: none;">Gizlilik Politikası</a></p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        let currentIndex = 0;
        const images = <?php echo json_encode($resimler); ?>;

        function changeImage(direction) {
            currentIndex = (currentIndex + direction + images.length) % images.length;
            document.getElementById('currentImage').src = images[currentIndex];
        }

        function favoriEkle(ilanID) {
            // Form verisi oluştur
            const formData = new FormData();
            formData.append('ilanID', ilanID);

            fetch('favoriEkle.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const favoriButton = document.getElementById('favoriButton');
                    if (data.action === 'added') {
                        favoriButton.classList.remove('btn-outline-danger');
                        favoriButton.classList.add('btn-danger');
                        favoriButton.innerHTML = '<i class="fas fa-heart"></i> Favorilerden Çıkar';
                    } else {
                        favoriButton.classList.remove('btn-danger');
                        favoriButton.classList.add('btn-outline-danger');
                        favoriButton.innerHTML = '<i class="fas fa-heart"></i> Favorilere Ekle';
                    }
                    alert(data.message);
                } else {
                    alert('Bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu!');
            });
        }
    </script>
</body>

</html>