<?php
session_start();
include("ayar.php"); // Veritabanı bağlantısını dahil et

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['giris']) || !$_SESSION['giris']) {
    header("Location: girisYap.php"); // Giriş yapılmamışsa giriş sayfasına yönlendir
    exit;
}

// Kullanıcı bilgilerini session'dan al
$kullaniciID = $_SESSION['uyeID'];
$kullaniciAdi = $_SESSION['uyeAd'];
$kullaniciMail = $_SESSION['uyeMail'];

// Kullanıcı bilgilerini veritabanından çek
$query = $baglan->prepare("SELECT uyeAd, uyeSoyad, uyeTelNo, uyeAdresID, uyeYetkiID FROM t_uyeler WHERE uyeID = ?");
$query->bind_param("i", $kullaniciID);
$query->execute();
$result = $query->get_result();
$kullanici = $result->fetch_assoc();

if (!$kullanici) {
    echo "Kullanıcı bilgileri bulunamadı.";
    exit;
}
?>
<!doctype html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil - Prestij Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<style>
    .profile-header {
        background-color: #004080;
        color: white;
        padding: 20px;
        border-radius: 8px;
    }

    .profile-header h2 {
        margin-bottom: 10px;
    }

    .nav-tabs .nav-link.active {
        background-color: #004080;
        color: white;
    }

    .card img {
        height: 250px;
        object-fit: cover;
    }

    .message-container {
        max-height: 300px;
        overflow-y: auto;
    }
</style>

<body>
    <?php include("header.php"); ?>

    <div class="container mt-5">
        <!-- Kullanıcı Bilgileri -->
        <div class="profile-header text-center">
            <h2> <?php
                    // Verileri oturumdan değil, veritabanından çek
                    require_once 'ayar.php'; // Veritabanı bağlantısı
                    $stmt = $baglan->prepare("SELECT uyeAd, uyeMail FROM t_uyeler WHERE uyeID = ?");
                    $stmt->bind_param("i", $_SESSION['uyeID']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();

                    if ($user) {
                        echo htmlspecialchars($user['uyeAd']);
                    } else {
                        echo "Kullanıcı Bilgisi Bulunamadı";
                    }
                    ?></h2>
            <p>E-posta: <?php echo htmlspecialchars($kullaniciMail); ?></p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#profilDuzenleModal">Profili Düzenle</button>
        </div>

        <!-- Profili Düzenle Modal -->
        <div class="modal fade" id="profilDuzenleModal" tabindex="-1" aria-labelledby="profilDuzenleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profilDuzenleModalLabel">Profili Düzenle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                    </div>
                    <div class="modal-body">
                        <form id="profilDuzenleForm">
                            <div class="mb-3">
                                <label for="uyeAd" class="form-label">Ad</label>
                                <input type="text" class="form-control" id="uyeAd" name="uyeAd" value="<?php echo htmlspecialchars($kullanici['uyeAd']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="uyeSoyad" class="form-label">Soyad</label>
                                <input type="text" class="form-control" id="uyeSoyad" name="uyeSoyad" value="<?php echo htmlspecialchars($kullanici['uyeSoyad']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="uyeTelNo" class="form-label">Telefon Numarası</label>
                                <input type="tel" class="form-control" id="uyeTelNo" name="uyeTelNo" value="<?php echo htmlspecialchars($kullanici['uyeTelNo']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="uyeSifre" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="uyeSifre" name="uyeSifre" placeholder="Yeni şifre (isteğe bağlı)">
                            </div>
                            <button type="submit" class="btn btn-primary">Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Profili Düzenle Formunu Gönder
            document.getElementById('profilDuzenleForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('profil.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Profil başarıyla güncellendi!');

                            // Güncellenen bilgileri sayfa üzerindeki alanlara yansıt
                            document.querySelector('.profile-header h2').textContent = formData.get('uyeAd');
                            document.querySelector('.profile-header p').textContent = `E-posta: ${formData.get('uyeMail')}`;

                            // Modalı kapat
                            const modal = bootstrap.Modal.getInstance(document.getElementById('profilDuzenleModal'));
                            modal.hide();
                        } else {
                            alert('Bir hata oluştu: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Güncelleme başarılı!');
                        location.reload();
                    });
            });
        </script>

        <?php
        // Profil Güncelleme İşlemi
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uyeAd']) && isset($_POST['uyeSoyad']) && isset($_POST['uyeTelNo'])) {
            // Validate uyeAd is not empty and trim whitespace
            $uyeAd = trim($_POST['uyeAd']);
            if ($uyeAd === '') {
                echo json_encode(['success' => false, 'message' => 'Ad alanı boş olamaz.']);
                exit;
            }
            $uyeSoyad = htmlspecialchars($_POST['uyeSoyad']);
            $uyeTelNo = htmlspecialchars($_POST['uyeTelNo']);
            $uyeSifre = isset($_POST['uyeSifre']) && !empty($_POST['uyeSifre']) ? password_hash($_POST['uyeSifre'], PASSWORD_DEFAULT) : null;

            $queryStr = "UPDATE t_uyeler SET uyeAd = ?, uyeSoyad = ?, uyeTelNo = ?, uyeGuncellemeTarihi = NOW()";
            $params = [$uyeAd, $uyeSoyad, $uyeTelNo];

            if ($uyeSifre) {
                $queryStr .= ", uyeSifre = ?";
                $params[] = $uyeSifre;
            }

            $queryStr .= " WHERE uyeID = ?";
            $params[] = $kullaniciID;

            $query = $baglan->prepare($queryStr);
            $query->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

            if ($query->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $baglan->error]);
            }
            exit;
        }
        ?>



        <!-- Sekmeler -->
        <ul class="nav nav-tabs mt-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="ilanlar-tab" data-bs-toggle="tab" data-bs-target="#ilanlar" type="button" role="tab">İlanlarım</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="favoriler-tab" data-bs-toggle="tab" data-bs-target="#favoriler" type="button" role="tab">Favorilerim</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mesajlar-tab" data-bs-toggle="tab" data-bs-target="#mesajlar" type="button" role="tab">Mesajlar</button>
            </li>
            <?php if ($kullanici['uyeYetkiID'] == 1): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="yonetim-tab" data-bs-toggle="tab" data-bs-target="#yonetim" type="button" role="tab">Yönetim Paneli</button>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Sekme İçerikleri -->
        <div class="tab-content mt-3" id="profileTabsContent">
            <!-- İlanlarım -->
            <div class="tab-pane fade show active" id="ilanlar" role="tabpanel">
                <div class="row">
                    <?php
                    $query = $baglan->prepare("
                        SELECT il.ilanID, id.ilanDFiyat, id.ilanDMulkTuru, id.ilanDKonumBilgisi, r.resimUrl
                        FROM t_ilanlar il
                        JOIN t_ilandetay id ON il.ilanID = id.ilanDilanID
                        LEFT JOIN t_resimler r ON il.ilanID = r.resimIlanID AND r.resimDurum = 1
                        WHERE il.ilanUyeID = ?
                    ");
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $resim = $row['resimUrl'] ?: 'default.jpg'; // Resim yoksa varsayılan resim
                            echo '
                                <div class="col-md-4" style="padding: 10px;">
                                    <div class="card">
                                        <img src="' . htmlspecialchars($resim) . '" class="card-img-top" alt="İlan Resmi">
                                        <div class="card-body">
                                            <h5 class="card-title">' . htmlspecialchars($row['ilanDMulkTuru']) . '</h5>
                                            <p class="card-text"><strong>Fiyat:</strong> ' . number_format($row['ilanDFiyat'], 2) . ' TL</p>
                                            <p class="card-text"><strong>Konum:</strong> ' . htmlspecialchars($row['ilanDKonumBilgisi']) . '</p>
                                            <a href="ilanDuzenle.php?id=' . $row['ilanID'] . '" class="btn btn-warning btn-sm">Düzenle</a>
                                        </div>
                                    </div>
                                </div>
                            ';
                        }
                    } else {
                        echo "<p>Henüz ilanınız bulunmamaktadır.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Favorilerim -->
            <div class="tab-pane fade" id="favoriler" role="tabpanel">
                <div class="row">
                    <?php
                    $query = $baglan->prepare("
                        SELECT 
                            i.ilanID,
                            id.ilanDFiyat, 
                            id.ilanDMulkTuru, 
                            id.ilanDKonumBilgisi, 
                            r.resimUrl
                        FROM t_favoriler f
                        JOIN t_ilanlar i ON f.favoriIlanID = i.ilanID
                        JOIN t_ilandetay id ON f.favoriIlanID = id.ilanDilanID
                        LEFT JOIN t_resimler r ON f.favoriIlanID = r.resimIlanID AND r.resimDurum = 1
                        WHERE f.favoriUyeID = ? 
                        AND f.favoriDurum = 1
                        GROUP BY i.ilanID
                    ");
                    
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $resim = $row['resimUrl'] ?: 'default.jpg'; // Resim yoksa varsayılan resim
                            echo '
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        <a href="ilanDetay.php?id=' . $row['ilanID'] . '" class="text-decoration-none">
                                            <img src="' . htmlspecialchars($resim) . '" class="card-img-top" alt="İlan Resmi" style="height: 200px; object-fit: cover;">
                                            <div class="card-body">
                                                <h5 class="card-title text-dark">' . htmlspecialchars($row['ilanDMulkTuru']) . '</h5>
                                                <p class="card-text text-dark">
                                                    <strong>Fiyat:</strong> ' . number_format($row['ilanDFiyat'], 2) . ' TL<br>
                                                    <strong>Konum:</strong> ' . htmlspecialchars($row['ilanDKonumBilgisi']) . '
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            ';
                        }
                    } else {
                        echo "<p>Henüz favori ilanınız bulunmamaktadır.</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Mesajlar -->
            <div class="tab-pane fade" id="mesajlar" role="tabpanel">
                <div class="message-list">
                    <?php
                    $query = $baglan->prepare("
                        SELECT m.mesajText, m.mesajOkunduDurumu, u.uyeMail
                        FROM t_mesajlar m
                        JOIN t_uyeler u ON m.mesajIletenID = u.uyeID
                        WHERE m.mesajAlanID = ?
                    ");
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $okunduDurumu = $row['mesajOkunduDurumu'] == 1 ? "Görüldü" : "Görülmedi";
                            echo '
                                <div class="message-summary border p-3 mb-2">
                                    <strong>' . htmlspecialchars($row['uyeMail']) . '</strong>
                                    <p>' . htmlspecialchars($row['mesajText']) . '</p>
                                    <small>' . $okunduDurumu . '</small>
                                </div>
                            ';
                        }
                    } else {
                        echo "<p>Henüz mesajınız bulunmamaktadır.</p>";
                    }
                    ?>
                </div>
            </div>

            <?php if ($kullanici['uyeYetkiID'] == 1): ?>
                <!-- Yönetim Paneli -->
                <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] === 'yonetim' ? 'show active' : ''; ?>" id="yonetim" role="tabpanel">
                    <div class="container mt-3">
                        <h4>Üye Listesi</h4>
                        <form method="GET" class="mb-3">
                            <input type="hidden" name="tab" value="yonetim"> <!-- Yönetim sekmesinde kalmak için -->
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="isim" class="form-control" placeholder="İsim" value="<?php echo isset($_GET['isim']) ? htmlspecialchars($_GET['isim']) : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="mail" class="form-control" placeholder="E-posta" value="<?php echo isset($_GET['mail']) ? htmlspecialchars($_GET['mail']) : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="yetki" class="form-control">
                                        <option value="">Yetki Seç</option>
                                        <option value="1" <?php echo isset($_GET['yetki']) && $_GET['yetki'] == 1 ? 'selected' : ''; ?>>Admin</option>
                                        <option value="2" <?php echo isset($_GET['yetki']) && $_GET['yetki'] == 2 ? 'selected' : ''; ?>>Üye</option>
                                        <option value="3" <?php echo isset($_GET['yetki']) && $_GET['yetki'] == 3 ? 'selected' : ''; ?>>Şirket</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">Filtrele</button>
                                </div>
                            </div>
                        </form>
                        <!-- Yönetim Paneli İçeriği -->
                        <div class="message-list">
                            <?php
                            $queryStr = "
                                SELECT u.uyeID, u.uyeMail, u.uyeTelNo, u.uyeYetkiID, y.yetkiAdi
                                FROM t_uyeler u
                                JOIN t_yetki y ON u.uyeYetkiID = y.yetkiID
                                WHERE u.uyeAktiflikDurumu = 1
                            ";

                            // Filtreleme koşulları
                            if (!empty($_GET['isim'])) {
                                $queryStr .= " AND u.uyeAd LIKE '%" . $baglan->real_escape_string($_GET['isim']) . "%'";
                            }
                            if (!empty($_GET['mail'])) {
                                $queryStr .= " AND u.uyeMail LIKE '%" . $baglan->real_escape_string($_GET['mail']) . "%'";
                            }
                            if (!empty($_GET['yetki'])) {
                                $queryStr .= " AND u.uyeYetkiID = " . intval($_GET['yetki']);
                            }

                            $query = $baglan->query($queryStr);

                            if ($query->num_rows > 0) {
                                while ($row = $query->fetch_assoc()) {
                                    echo '
                                        <div class="message-summary border p-3 mb-2">
                                            <strong>' . htmlspecialchars($row['uyeMail']) . '</strong>
                                            <p>Telefon: ' . htmlspecialchars($row['uyeTelNo']) . '</p>
                                            <p>Yetki: ' . htmlspecialchars($row['yetkiAdi']) . '</p>
                                            <button class="btn btn-warning btn-sm" onclick="openYetkiModal(' . $row['uyeID'] . ', \'' . $row['uyeYetkiID'] . '\')">Yetki Düzenle</button>
                                            <a href="kullaniciProfilDetay.php?id=' . $row['uyeID'] . '" class="btn btn-info btn-sm">Daha Fazla Görüntüle</a>
                                        </div>
                                    ';
                                }
                            } else {
                                echo "<p>Filtreleme sonucunda üye bulunamadı.</p>";
                            }
                            ?>
                        </div>

                        <!-- Hakkımızda ve İletişim İçerik Yönetimi -->
                        <hr>
                        <h4>Sayfa İçerik Yönetimi</h4>
                        <?php
                        // Dosya yolları
                        $hakkimizdaDosya = 'hakkimizda.php';
                        $iletisimDosya = 'iletisim.php';

                        // Hakkımızda.php içeriğini parçala
                        $hakkimizdaIcerik = file_exists($hakkimizdaDosya) ? file_get_contents($hakkimizdaDosya) : '';
                        $hakkimizdaTitle = '';
                        $hakkimizdaHeading = '';
                        $hakkimizdaImage = '';
                        $hakkimizdaParagraphs = [];
                        $hakkimizdaContactHeading = '';
                        $hakkimizdaContactText = '';
                        $hakkimizdaContactLinkText = '';
                        $hakkimizdaContactLinkHref = '';

                        if ($hakkimizdaIcerik) {
                            // Title
                            if (preg_match('/<title>(.*?)<\/title>/s', $hakkimizdaIcerik, $matches)) {
                                $hakkimizdaTitle = $matches[1];
                            }
                            // Main heading h1
                            if (preg_match('/<h1.*?>(.*?)<\/h1>/s', $hakkimizdaIcerik, $matches)) {
                                $hakkimizdaHeading = strip_tags($matches[1]);
                            }
                            // Image src
                            if (preg_match('/<img.*?src=["\'](.*?)["\'].*?>/s', $hakkimizdaIcerik, $matches)) {
                                $hakkimizdaImage = $matches[1];
                            }
                            // Paragraphs in main content (between <div class="col-md-6"> and </div>)
                            if (preg_match_all('/<div class="col-md-6 mx-auto mb-4">\s*(.*?)\s*<\/div>/s', $hakkimizdaIcerik, $divMatches)) {
                                $contentDiv = $divMatches[1][0] ?? '';
                                if ($contentDiv) {
                                    preg_match_all('/<p>(.*?)<\/p>/s', $contentDiv, $pMatches);
                                    $hakkimizdaParagraphs = array_map('strip_tags', $pMatches[1]);
                                }
                            }
                            // Contact heading and paragraph with link
                            if (preg_match('/<div class="text-center mt-5">(.*?)<\/div>/s', $hakkimizdaIcerik, $contactDiv)) {
                                $contactContent = $contactDiv[1];
                                if (preg_match('/<h3.*?>(.*?)<\/h3>/s', $contactContent, $h3Match)) {
                                    $hakkimizdaContactHeading = strip_tags($h3Match[1]);
                                }
                                if (preg_match('/<p>(.*?)<a href=["\'](.*?)["\'].*?>(.*?)<\/a>(.*?)<\/p>/s', $contactContent, $pMatch)) {
                                    $hakkimizdaContactText = trim(strip_tags($pMatch[1] . $pMatch[4]));
                                    $hakkimizdaContactLinkHref = $pMatch[2];
                                    $hakkimizdaContactLinkText = $pMatch[3];
                                }
                            }
                        }

                        // Fix: Ensure hakkimizdaParagraphs is always an array for the form
                        if (!is_array($hakkimizdaParagraphs)) {
                            $hakkimizdaParagraphs = [];
                        }

                        // iletisim.php içeriğini parçala
                        $iletisimIcerik = file_exists($iletisimDosya) ? file_get_contents($iletisimDosya) : '';
                        $iletisimTitle = '';
                        $iletisimHeading = '';
                        $iletisimIntro = '';
                        $iletisimInstagram = '';
                        $iletisimWhatsapp = '';
                        $iletisimMail = '';

                        if ($iletisimIcerik) {
                            // Title
                            if (preg_match('/<title>(.*?)<\/title>/s', $iletisimIcerik, $matches)) {
                                $iletisimTitle = $matches[1];
                            }
                            // Heading h1
                            if (preg_match('/<h1.*?>(.*?)<\/h1>/s', $iletisimIcerik, $matches)) {
                                $iletisimHeading = strip_tags($matches[1]);
                            }
                            // Intro paragraph
                            if (preg_match('/<div class="contact-section">.*?<p>(.*?)<\/p>/s', $iletisimIcerik, $matches)) {
                                $iletisimIntro = strip_tags($matches[1]);
                            }
                            // Instagram link
                            if (preg_match('/<a href=["\'](https?:\/\/www\.instagram\.com\/[^"\']+)["\'].*?class="contact-icons"/s', $iletisimIcerik, $matches)) {
                                $iletisimInstagram = $matches[1];
                            }
                            // Whatsapp link
                            if (preg_match('/<a href=["\'](https?:\/\/wa\.me\/[^"\']+)["\'].*?class="contact-icons"/s', $iletisimIcerik, $matches)) {
                                $iletisimWhatsapp = $matches[1];
                            }
                            // Mailto link
                            if (preg_match('/<a href=["\']mailto:([^"\']+)["\'].*?class="contact-icons"/s', $iletisimIcerik, $matches)) {
                                $iletisimMail = $matches[1];
                            }
                        }

                        // İçerik güncelleme işlemi
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sayfa_icerik_guncelle'])) {
                            $sayfa = $_POST['sayfa'];

                            if ($sayfa === 'hakkimizda') {
                                $title = $_POST['title'] ?? '';
                                $heading = $_POST['heading'] ?? '';
                                $image = $_POST['image'] ?? '';
                                $paragraphs = $_POST['para'] ?? [];
                                $contactHeading = $_POST['contactHeading'] ?? '';
                                $contactText = $_POST['contactText'] ?? '';
                                $contactLinkText = $_POST['contactLinkText'] ?? '';
                                $contactLinkHref = $_POST['contactLinkHref'] ?? '';

                                // Remove empty paragraphs
                                $paragraphs = array_filter($paragraphs, function ($para) {
                                    return trim($para) !== '';
                                });

                                // Build paragraphs HTML
                                $paragraphsHtml = '';
                                foreach ($paragraphs as $para) {
                                    $paragraphsHtml .= '<p>' . nl2br(htmlspecialchars($para)) . '</p>' . "\n";
                                }

                                $newContent = "<?php\nsession_start();\n?>\n<!DOCTYPE html>\n<html lang=\"tr\">\n\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n  <title>" . htmlspecialchars($title) . "</title>\n  <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n  <link href=\"style.css\" rel=\"stylesheet\">\n</head>\n\n<body>\n  <?php include(\"header.php\"); ?>\n\n  <div class=\"container mt-5\">\n    <h1 class=\"text-center mb-4\">" . htmlspecialchars($heading) . "</h1>\n    <div class=\"row\">\n      <div class=\"col-md-6 mx-auto mb-4\">\n        " . $paragraphsHtml . "\n      </div>\n    </div>\n    <div class=\"text-center mt-5\">\n      <h3>" . htmlspecialchars($contactHeading) . "</h3>\n      <p>\n        " . nl2br(htmlspecialchars($contactText)) . " <a href=\"" . htmlspecialchars($contactLinkHref) . "\" class=\"text-primary\">" . htmlspecialchars($contactLinkText) . "</a>\n      </p>\n    </div>\n  </div>\n  <footer>\n    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>\n    <p><a href=\"#\" style=\"color: #ff6600; text-decoration: none;\">İletişim</a> | <a href=\"#\" style=\"color: #ff6600; text-decoration: none;\">Gizlilik Politikası</a></p>\n  </footer>\n  <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"></script>\n</body>\n\n</html>";

                                file_put_contents($hakkimizdaDosya, $newContent);
                                echo '<div class="alert alert-success mt-3">Hakkımızda sayfası başarıyla güncellendi.</div>';
                                $hakkimizdaIcerik = $newContent;
                            } elseif ($sayfa === 'iletisim') {
                                $title = $_POST['title'] ?? '';
                                $heading = $_POST['heading'] ?? '';
                                $intro = $_POST['intro'] ?? '';
                                $instagram = $_POST['instagram'] ?? '';
                                $whatsapp = $_POST['whatsapp'] ?? '';
                                $mail = $_POST['mail'] ?? '';

                                $newContent = "<?php\nsession_start();\n?>\n<!DOCTYPE html>\n<html lang=\"tr\">\n\n<head>\n  <meta charset=\"UTF-8\">\n  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n  <title>" . htmlspecialchars($title) . "</title>\n  <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n  <link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css\" rel=\"stylesheet\">\n  <link href=\"style.css\" rel=\"stylesheet\">\n  <style>\n    .contact-icons {\n      font-size: 2rem;\n      margin: 10px;\n      color: #004080;\n      transition: color 0.3s ease;\n    }\n\n    .contact-icons:hover {\n      color: #ff6600;\n    }\n\n    .contact-section {\n      text-align: center;\n      margin-top: 50px;\n    }\n\n    .contact-section h1 {\n      margin-bottom: 20px;\n    }\n\n    .contact-section p {\n      font-size: 1.2rem;\n      margin-bottom: 30px;\n    }\n  </style>\n</head>\n\n<body>\n  <?php include(\"header.php\"); ?>\n\n  <div class=\"container mt-5\">\n    <div class=\"contact-section\">\n      <h1>" . htmlspecialchars($heading) . "</h1>\n      <p>" . nl2br(htmlspecialchars($intro)) . "</p>\n      <div>\n        <!-- Instagram -->\n        <a href=\"" . htmlspecialchars($instagram) . "\" target=\"_blank\" class=\"contact-icons\">\n          <i class=\"bi bi-instagram\"></i>\n        </a>\n        <!-- WhatsApp -->\n        <a href=\"" . htmlspecialchars($whatsapp) . "\" target=\"_blank\" class=\"contact-icons\">\n          <i class=\"bi bi-whatsapp\"></i>\n        </a>\n        <!-- Mail -->\n        <a href=\"mailto:" . htmlspecialchars($mail) . "\" target=\"_blank\" class=\"contact-icons\">\n          <i class=\"bi bi-envelope\"></i>\n        </a>\n      </div>\n    </div>\n  </div>\n  <footer>\n    <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>\n    <p><a href=\"#\" style=\"color: #ff6600; text-decoration: none;\">İletişim</a> | <a href=\"#\" style=\"color: #ff6600; text-decoration: none;\">Gizlilik Politikası</a></p>\n  </footer>\n  <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"></script>\n  <script src=\"https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js\"></script>\n</body>\n\n</html>";

                                file_put_contents($iletisimDosya, $newContent);
                                echo '<div class="alert alert-success mt-3">İletişim sayfası başarıyla güncellendi.</div>';
                                $iletisimIcerik = $newContent;
                            }
                        }
                        ?>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Hakkımızda Sayfası</h5>
                                <form method="POST" id="hakkimizdaForm">
                                    <input type="hidden" name="sayfa" value="hakkimizda">
                                    <input type="hidden" name="sayfa_icerik_guncelle" value="1">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Sayfa Başlığı (title)</label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($hakkimizdaTitle); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="heading" class="form-label">Ana Başlık (h1)</label>
                                        <input type="text" class="form-control" id="heading" name="heading" value="<?php echo htmlspecialchars($hakkimizdaHeading); ?>" required>
                                    </div>
                                    <div id="paragraphsContainer">

                                        <?php foreach ($hakkimizdaParagraphs as $index => $paragraph): ?>
                                            <div class="mb-3">
                                                <label for="para<?php echo $index; ?>" class="form-label">Paragraf <?php echo $index + 1; ?></label>
                                                <textarea class="form-control" id="para<?php echo $index; ?>" name="para[]" rows="3"><?php echo htmlspecialchars($paragraph); ?></textarea>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newParagraph" class="form-label">Yeni Paragraf Ekle</label>
                                        <textarea class="form-control" id="newParagraph" rows="3"></textarea>
                                        <button type="button" class="btn btn-secondary mt-2" id="addParagraphBtn">Paragraf Ekle</button>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contactHeading" class="form-label">İletişim Başlığı (h3)</label>
                                        <input type="text" class="form-control" id="contactHeading" name="contactHeading" value="<?php echo htmlspecialchars($hakkimizdaContactHeading); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contactText" class="form-label">İletişim Metni (bağlantı öncesi ve sonrası)</label>
                                        <textarea class="form-control" id="contactText" name="contactText" rows="2" required><?php echo htmlspecialchars($hakkimizdaContactText); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contactLinkText" class="form-label">İletişim Bağlantı Metni</label>
                                        <input type="text" class="form-control" id="contactLinkText" name="contactLinkText" value="<?php echo htmlspecialchars($hakkimizdaContactLinkText); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="contactLinkHref" class="form-label">İletişim Bağlantı URL</label>
                                        <input type="text" class="form-control" id="contactLinkHref" name="contactLinkHref" value="<?php echo htmlspecialchars($hakkimizdaContactLinkHref); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-success mt-2">Güncelle</button>
                                </form>
                            </div>
                            <script>
                                document.getElementById('addParagraphBtn').addEventListener('click', function() {
                                    const newParaText = document.getElementById('newParagraph').value.trim();
                                    if (newParaText === '') {
                                        alert('Lütfen eklemek için bir paragraf yazın.');
                                        return;
                                    }
                                    const container = document.getElementById('paragraphsContainer');
                                    const paraCount = container.querySelectorAll('textarea[name="para[]"]').length;
                                    const div = document.createElement('div');
                                    div.classList.add('mb-3');
                                    div.innerHTML = `
                                <label for="para${paraCount}" class="form-label">Paragraf ${paraCount + 1}</label>
                                <textarea class="form-control" id="para${paraCount}" name="para[]" rows="3" required>${newParaText}</textarea>
                            `;
                                    container.appendChild(div);
                                    document.getElementById('newParagraph').value = '';
                                });
                            </script>
                            <div class="col-md-6">
                                <h5>İletişim Sayfası</h5>
                                <form method="POST">
                                    <input type="hidden" name="sayfa" value="iletisim">
                                    <input type="hidden" name="sayfa_icerik_guncelle" value="1">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Sayfa Başlığı (title)</label>
                                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($iletisimTitle); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="heading" class="form-label">Ana Başlık (h1)</label>
                                        <input type="text" class="form-control" id="heading" name="heading" value="<?php echo htmlspecialchars($iletisimHeading); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="intro" class="form-label">Giriş Paragrafı</label>
                                        <textarea class="form-control" id="intro" name="intro" rows="3" required><?php echo htmlspecialchars($iletisimIntro); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="instagram" class="form-label">Instagram URL</label>
                                        <input type="text" class="form-control" id="instagram" name="instagram" value="<?php echo htmlspecialchars($iletisimInstagram); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="whatsapp" class="form-label">WhatsApp URL</label>
                                        <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($iletisimWhatsapp); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mail" class="form-label">E-posta Adresi</label>
                                        <input type="email" class="form-control" id="mail" name="mail" value="<?php echo htmlspecialchars($iletisimMail); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-success mt-2">Güncelle</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Yetki Düzenleme Modal -->
                <div class="modal fade" id="yetkiModal" tabindex="-1" aria-labelledby="yetkiModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="yetkiModalLabel">Yetki Düzenle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                            </div>
                            <div class="modal-body">
                                <form id="yetkiForm">
                                    <input type="hidden" id="uyeId" name="uyeId">
                                    <div class="mb-3">
                                        <label for="uyeYetki" class="form-label">Yetki</label>
                                        <select id="uyeYetki" name="uyeYetki" class="form-control">
                                            <option value="1">Admin</option>
                                            <option value="2">Üye</option>
                                            <option value="3">Şirket</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Kaydet</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Yetki düzenleme modalını aç
                    function openYetkiModal(uyeId, mevcutYetki) {
                        document.getElementById('uyeId').value = uyeId;
                        document.getElementById('uyeYetki').value = mevcutYetki;

                        const modal = new bootstrap.Modal(document.getElementById('yetkiModal'));
                        modal.show();
                    }

                    // Yetki düzenleme formunu gönder
                    document.getElementById('yetkiForm').addEventListener('submit', function(e) {
                        e.preventDefault();

                        const uyeId = document.getElementById('uyeId').value;
                        const uyeYetki = document.getElementById('uyeYetki').value;

                        console.log('Gönderilen veriler:', {
                            uyeId,
                            uyeYetki
                        });

                        // FormData kullanarak verileri gönder
                        const formData = new FormData();
                        formData.append('action', 'updateYetki');
                        formData.append('uyeId', uyeId);
                        formData.append('uyeYetki', uyeYetki);

                        fetch('yetki_guncelle.php', { // Ayrı bir PHP dosyası kullanın
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Sunucu yanıtı: ' + response.status);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Sunucudan dönen yanıt:', data);
                                if (data.success) {
                                    alert('Üye yetkisi başarıyla güncellendi!');
                                    window.location.href = 'profil.php?tab=yonetim';
                                } else {
                                    alert('Bir hata oluştu: ' + data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Hata:', error);
                                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                            });
                    });
                </script>

                <?php
                // Yetki düzenleme işlemi
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateYetki') {
                    header('Content-Type: application/json');
                    $uyeId = intval($_POST['uyeId']);
                    $uyeYetki = intval($_POST['uyeYetki']);
                    if ($uyeId > 0 && in_array($uyeYetki, [1, 2, 3])) {
                        $query = $baglan->prepare("UPDATE t_uyeler SET uyeYetkiID = ? WHERE uyeID = ?");
                        $query->bind_param("ii", $uyeYetki, $uyeId);

                        if ($query->execute()) {
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $baglan->error]);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
                    }
                    exit;
                }
                ?>

                </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function openYetkiModal(uyeId, mevcutYetki) {
                document.getElementById('uyeId').value = uyeId;
                document.getElementById('uyeYetki').value = mevcutYetki;

                const modal = new bootstrap.Modal(document.getElementById('yetkiModal'));
                modal.show();
            }
        </script>
        <script>
            function toggleMessageBox(sohbetId) {
                // Tüm mesaj kutularını gizle
                document.querySelectorAll('.message-box').forEach(box => box.classList.add('d-none'));

                // Tıklanan mesajın kutusunu göster
                const messageBox = document.getElementById(sohbetId);
                messageBox.classList.remove('d-none');

                // Siyah opak arka planı göster
                document.getElementById('overlay').classList.remove('d-none');
            }

            function closeMessageBox() {
                // Tüm mesaj kutularını gizle
                document.querySelectorAll('.message-box').forEach(box => box.classList.add('d-none'));

                // Siyah opak arka planı gizle
                document.getElementById('overlay').classList.add('d-none');
            }

            // İlan verilerini modal içine yükleme
            function loadIlanData(id, baslik, aciklama, fiyat, metrekare, odaSayisi, konum, isitma, resimler) {
                document.getElementById('ilanId').value = id;
                document.getElementById('ilanBaslik').value = baslik;
                document.getElementById('ilanAciklama').value = aciklama;
                document.getElementById('ilanFiyat').value = fiyat;
                document.getElementById('ilanMetrekare').value = metrekare;
                document.getElementById('ilanOdaSayisi').value = odaSayisi;
                document.getElementById('ilanKonum').value = konum;
                document.getElementById('ilanIsitma').value = isitma;

                // Mevcut resimleri yükle
                const mevcutResimlerDiv = document.getElementById('mevcutResimler');
                mevcutResimlerDiv.innerHTML = ''; // Önceki resimleri temizle
                resimler.forEach(resim => {
                    const img = document.createElement('img');
                    img.src = resim.url;
                    img.alt = 'İlan Resmi';
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.marginRight = '10px';
                    mevcutResimlerDiv.appendChild(img);
                });
            }

            // Form gönderme işlemi
            document.getElementById('editIlanForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Form verilerini al
                const formData = new FormData(this);

                // AJAX ile düzenleme işlemini gönder
                fetch('ilanDuzenle.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('İlan başarıyla güncellendi!');
                            location.reload(); // Sayfayı yenile
                        } else {
                            alert('Bir hata oluştu: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        alert('Bir hata oluştu.');
                    });
            });

            // Üye ilanlarını yükleme
            function loadUyeIlanlari(uyeId, uyeAdi, uyeEmail, mevcutYetki) {
                // Üye bilgilerini modal içine yükle
                document.getElementById('uyeAdi').innerText = `Üye: ${uyeAdi}`;
                document.getElementById('uyeEmail').innerText = `E-posta: ${uyeEmail}`;
                document.getElementById('uyeYetki').value = mevcutYetki;

                // Üyenin ilanlarını yükle
                const ilanlarDiv = document.getElementById('uyeIlanlari');
                ilanlarDiv.innerHTML = ''; // Önceki ilanları temizle

                // Örnek ilanlar (AJAX ile sunucudan çekilebilir)
                const ilanlar = [{
                        id: 1,
                        baslik: 'İlan Başlığı 1',
                        fiyat: 500000
                    },
                    {
                        id: 2,
                        baslik: 'İlan Başlığı 2',
                        fiyat: 750000
                    }
                ];

                ilanlar.forEach(ilan => {
                    const ilanDiv = document.createElement('div');
                    ilanDiv.className = 'col-md-4';
                    ilanDiv.innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">${ilan.baslik}</h5>
                            <p class="card-text"><strong>Fiyat:</strong> ${ilan.fiyat} TL</p>
                            <button class="btn btn-danger btn-sm" onclick="kaldirIlan(${ilan.id})">Kaldır</button>
                        </div>
                    </div>
                `;
                    ilanlarDiv.appendChild(ilanDiv);
                });

                // Modalı aç
                const modal = new bootstrap.Modal(document.getElementById('uyeIlanlariModal'));
                modal.show();
            }

            // İlan kaldırma
            function kaldirIlan(ilanId) {
                if (confirm('Bu ilanı kaldırmak istediğinize emin misiniz?')) {
                    // AJAX ile ilan kaldırma işlemi yapılabilir
                    alert(`İlan ${ilanId} kaldırıldı.`);
                }
            }

            // Üye yetkisini güncelleme
            function guncelleYetki() {
                const uyeYetki = document.getElementById('uyeYetki').value;

                // AJAX ile yetki güncelleme işlemi yapılabilir
                alert(`Üyenin yetkisi ${uyeYetki} olarak güncellendi.`);
            }

            // Üye yetkisini düzenleme
            document.getElementById('uyeYetkiForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const uyeId = document.getElementById('uyeId').value;
                const uyeYetki = document.getElementById('uyeYetki').value;

                // AJAX ile yetki düzenleme işlemi yapılabilir
                alert(`Üye ${uyeId} yetkisi ${uyeYetki} olarak güncellendi.`);
            });


            // Üye yetkisini düzenleme formunu gönderme
            document.getElementById('uyeYetkiForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const uyeId = document.getElementById('uyeId').value;
                const uyeYetki = document.getElementById('uyeYetki').value;

                // AJAX ile yetki düzenleme işlemi yapılabilir
                fetch('uyeYetkiDuzenle.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `uyeId=${uyeId}&uyeYetki=${uyeYetki}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Üye yetkisi başarıyla güncellendi!');
                            location.reload(); // Sayfayı yenile
                        } else {
                            alert('Bir hata oluştu: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Hata:', error);
                        alert('Bir hata oluştu.');
                    });
            });
        </script>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                // URL'den parametreleri al
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');

                // Eğer "mesajlar" parametresi varsa, o sekmeyi aç
                if (tab === "mesajlar") {
                    const tabButton = document.querySelector('button[data-bs-target="#mesajlar"]');
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            });
            document.addEventListener("DOMContentLoaded", function() {
                // URL'den parametreleri al
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');

                // Eğer "mesajlar" parametresi varsa, o sekmeyi aç
                if (tab === "favoriler") {
                    const tabButton = document.querySelector('button[data-bs-target="#favoriler"]');
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            });
            document.addEventListener("DOMContentLoaded", function() {
                const urlParams = new URLSearchParams(window.location.search);
                const tab = urlParams.get('tab');

                if (tab) {
                    const tabButton = document.querySelector(`button[data-bs-target="#${tab}"]`);
                    if (tabButton) {
                        tabButton.click();
                    }
                }
            });
        </script>
    </div>
    </div>
    </div>
    <footer style="width: 100%; margin-top: 10px;">
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
        <p><a href="iletisim.php" style="color: #ff6600; text-decoration: none;">İletişim</a> | <a href="hakkimizda.php" style="color: #ff6600; text-decoration: none;">Hakkımızda</a></p>
    </footer>
</body>

</html>