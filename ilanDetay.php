<?php
session_start();
include("ayar.php");

$loggedIn = isset($_SESSION['giris']) && isset($_SESSION['uyeAd']) && isset($_SESSION['uyeMail']);
if ($loggedIn) {
    $kullaniciAdi   = $_SESSION['uyeAd'];
    $kullaniciEmail = $_SESSION['uyeMail'];
}

// İlan ID'sini al ve kontrol et
$ilanID = isset($_GET['ilanID']) ? intval($_GET['ilanID']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($ilanID <= 0) {
    // Debug için hata mesajı
    error_log("Geçersiz ilan ID: " . print_r($_GET, true));
    
    // Kullanıcıyı bilgilendir ve yönlendir
    $_SESSION['hata'] = "Geçersiz ilan ID";
    header("Location: index.php");
    exit;
}

// İlanın varlığını kontrol et
$kontrolSorgu = $baglan->prepare("SELECT ilanID FROM t_ilanlar WHERE ilanID = ?");
$kontrolSorgu->bind_param("i", $ilanID);
$kontrolSorgu->execute();

if ($kontrolSorgu->get_result()->num_rows === 0) {
    // Debug için hata mesajı
    error_log("İlan bulunamadı: ID = " . $ilanID);
    
    // Kullanıcıyı bilgilendir ve yönlendir
    $_SESSION['hata'] = "İlan bulunamadı";
    header("Location: index.php");
    exit;
}

// İlan bilgilerini al
$sorgu = $baglan->prepare("SELECT 
    il.ilanID,
    il.ilanUyeID,
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
$ilanSahibiID = $ilan->ilanUyeID ?? 0;
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
    $favoriSorgu = $baglan->prepare("SELECT favoriID, favoriDurum FROM t_favoriler 
        WHERE favoriUyeID = ? AND favoriIlanID = ?");
    $favoriSorgu->bind_param("ii", $_SESSION['uyeID'], $ilanID);
    $favoriSorgu->execute();
    $favoriSonuc = $favoriSorgu->get_result();
    if ($favoriSonuc->num_rows > 0) {
        $favori = $favoriSonuc->fetch_assoc();
        $favoriDurumu = $favori['favoriDurum'] == 1;
    }
}

// İlan bilgilerini çekmeden önce görüntülenme sayısını artır
$istatistikSorgu = $baglan->prepare("
    INSERT INTO t_istatistik 
    (istatistikIlanID, istatistikGoruntulenmeSayisi, istatistikSonGuncellenmeTarihi)
    VALUES (?, 1, NOW())
    ON DUPLICATE KEY UPDATE 
    istatistikGoruntulenmeSayisi = istatistikGoruntulenmeSayisi + 1,
    istatistikSonGuncellenmeTarihi = NOW()
");
$istatistikSorgu->bind_param("i", $ilanID);
$istatistikSorgu->execute();
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
                        <button id="favoriButton"
                            class="btn <?php echo $favoriDurumu ? 'btn-danger' : 'btn-outline-danger'; ?> mb-3"
                            onclick="favoriEkle(<?php echo $ilanID; ?>)">
                            <i class="fas fa-heart"></i>
                            <?php echo $favoriDurumu ? 'Favorilerden Çıkar' : 'Favorilere Ekle'; ?>
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
                    <?php if ($loggedIn): ?>
                        <button class="btn btn-primary mb-3"
                            onclick="mesajGonder(<?php echo $ilanID; ?>, <?php echo $ilanSahibiID; ?>)">
                            <i class="fas fa-envelope"></i> Mesaj Gönder
                        </button>
                    <?php else: ?>
                        <p class="text-muted mb-3">Mesaj göndermek için <a href="girisYap.php">giriş yapın</a></p>
                    <?php endif; ?>
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
                <br>
                <h2 style="margin-left: 33%;">Konum Bilgisi</h2>
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
                    } else if (data.action === 'removed') {
                        favoriButton.classList.remove('btn-danger');
                        favoriButton.classList.add('btn-outline-danger');
                        favoriButton.innerHTML = '<i class="fas fa-heart"></i> Favorilere Ekle';
                    }

                    // İstatistik sayısını güncelle
                    const favoriSayisiElement = document.querySelector('.stats-container .fa-heart + p');
                    if (favoriSayisiElement) {
                        favoriSayisiElement.textContent = data.yeniFavoriSayisi;
                    }

                    alert(data.message);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu: ' + error.message);
            });
        }

        function mesajGonder(ilanID, aliciID) {
            if(!ilanID || !aliciID) {
                alert('Geçersiz ilan veya alıcı bilgisi!');
                return;
            }
            document.getElementById('ilanID').value = ilanID;
            document.getElementById('aliciID').value = aliciID;
            const modal = new bootstrap.Modal(document.getElementById('mesajModal'));
            modal.show();
        }

        document.getElementById('mesajForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Debug için form verilerini kontrol et
            console.log('İlan ID:', formData.get('ilanID'));
            console.log('Alıcı ID:', formData.get('aliciID'));
            console.log('Mesaj:', formData.get('mesajText'));

            fetch('mesajGonder.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Mesajınız başarıyla gönderildi!');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('mesajModal'));
                    modal.hide();
                    this.reset();
                } else {
                    throw new Error(data.message || 'Mesaj gönderilemedi');
                }
            })
            .catch(error => {
                console.error('Hata detayı:', error);
                alert('Mesaj gönderilirken bir hata oluştu: ' + error.message);
            });
        });
    </script>

    <!-- Mesaj Modal -->
    <div class="modal fade" id="mesajModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mesaj Gönder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="mesajForm">
                        <input type="hidden" id="ilanID" name="ilanID">
                        <input type="hidden" id="aliciID" name="aliciID">
                        <div class="mb-3">
                            <label for="mesajText" class="form-label">Mesajınız</label>
                            <textarea class="form-control" id="mesajText" name="mesajText" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Gönder</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>