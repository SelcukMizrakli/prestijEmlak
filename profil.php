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
$query = $baglan->prepare("SELECT uyeAd, uyeSoyad, uyeTelNo, uyeAdresID FROM t_uyeler WHERE uyeID = ?");
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
            <h2><?php echo htmlspecialchars($kullaniciAdi); ?></h2>
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
            $uyeAd = htmlspecialchars($_POST['uyeAd']);
            $uyeSoyad = htmlspecialchars($_POST['uyeSoyad']);
            $uyeTelNo = htmlspecialchars($_POST['uyeTelNo']);
            $uyeSifre = isset($_POST['uyeSifre']) && !empty($_POST['uyeSifre']) ? password_hash($_POST['uyeSifre'], PASSWORD_DEFAULT) : null;

            if (empty($uyeAd)) {
                echo json_encode(['success' => false, 'message' => 'Ad alanı boş olamaz.']);
                exit;
            }

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

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['uyeAd']) || empty($_POST['uyeAd'])) {
                echo json_encode(['success' => false, 'message' => 'Ad alanı boş olamaz.']);
                exit;
            }

            $uyeAd = htmlspecialchars($_POST['uyeAd']);
            echo json_encode(['success' => true, 'message' => 'Ad alındı: ' . $uyeAd]);
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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="yonetim-tab" data-bs-toggle="tab" data-bs-target="#yonetim" type="button" role="tab">Yönetim Paneli</button>
            </li>
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
                        SELECT id.ilanDFiyat, id.ilanDMulkTuru, id.ilanDKonumBilgisi, r.resimUrl
                        FROM t_favoriler f
                        JOIN t_ilandetay id ON f.favoriIlanID = id.ilanDilanID
                        LEFT JOIN t_resimler r ON f.favoriIlanID = r.resimIlanID AND r.resimDurum = 1
                        WHERE f.favoriUyeID = ?
                    ");
                    $query->bind_param("i", $kullaniciID);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $resim = $row['resimUrl'] ?: 'default.jpg'; // Resim yoksa varsayılan resim
                            echo '
                                <div class="col-md-4">
                                    <div class="card">
                                        <img src="' . htmlspecialchars($resim) . '" class="card-img-top" alt="İlan Resmi">
                                        <div class="card-body">
                                            <h5 class="card-title">' . htmlspecialchars($row['ilanDMulkTuru']) . '</h5>
                                            <p class="card-text"><strong>Fiyat:</strong> ' . number_format($row['ilanDFiyat'], 2) . ' TL</p>
                                            <p class="card-text"><strong>Konum:</strong> ' . htmlspecialchars($row['ilanDKonumBilgisi']) . '</p>
                                        </div>
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
                </div>
            </div>

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

                    // AJAX ile yetki düzenleme işlemi
                    fetch('profil.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=updateYetki&uyeId=${uyeId}&uyeYetki=${uyeYetki}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Üye yetkisi başarıyla güncellendi!');
                                // Sayfayı yenilerken yönetim paneli sekmesini açık tut
                                window.location.href = 'profil.php?tab=yonetim';
                            } else {
                                alert('Bir hata oluştu: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Hata:', error);
                            alert('Üye yetkisi başarıyla güncellendi!');
                            // Yönetim sekmesini açık tut
                            window.location.href = 'profil.php?tab=yonetim';
                        });
                });
            </script>

            <?php
            // Yetki düzenleme işlemi
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateYetki') {
                $uyeId = intval($_POST['uyeId']);
                $uyeYetki = intval($_POST['uyeYetki']);

                if ($uyeId > 0 && in_array($uyeYetki, [1, 2, 3])) {
                    $query = $baglan->prepare("UPDATE t_uyeler SET uyeYetkiID = ? WHERE uyeID = ?");
                    $query->bind_param("ii", $uyeYetki, $uyeId);

                    if ($query->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Geçersiz veri.']);
                }
                exit;
            }
            ?>

        </div>
    </div>

    <footer>
        <p>&copy; 2025 Prestij Emlak. Tüm hakları saklıdır.</p>
    </footer>
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
</body>

</html>