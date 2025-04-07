<?php
session_start();
if (!isset($_SESSION['giris'])) {
    header("Location: girisYap.php");
    exit();
}

include("ayar.php");

// Formdan gelen bilgileri al
$ilanFiyat = $_POST['ilanFiyat'];
$ilanMetrekareBrut = $_POST['ilanMetrekareBrut'];
$ilanMetrekareNet = $_POST['ilanMetrekareNet'];
$ilanOdaSayisi = $_POST['ilanOdaSayisi'];
$ilanBinaYasi = $_POST['ilanBinaYasi'];
$ilanSiteIcerisindeMi = $_POST['ilanSiteIcerisindeMi'];
$ilanMulkTuru = $_POST['ilanMulkTuru'];
$ilanKonum = $_POST['ilanKonum'];
$ilanIsitmaTipi = $_POST['ilanIsitmaTipi'];
$ilanBulunduguKat = $_POST['ilanBulunduguKat'];
$ilanBinaKatSayisi = $_POST['ilanBinaKatSayisi'];

// İlan bilgilerini veritabanına kaydet
$sorgu = $baglan->prepare("INSERT INTO t_ilandetay (ilanFiyat, ilanMetrekareBrut, ilanMetrekareNet, ilanOdaSayisi, ilanBinaYasi, ilanSiteIcerisindeMi, ilanMulkTuru, ilanKonum, ilanIsitmaTipi, ilanBulunduguKat, ilanBinaKatSayisi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$sorgu->bind_param("iiiiissssii", $ilanFiyat, $ilanMetrekareBrut, $ilanMetrekareNet, $ilanOdaSayisi, $ilanBinaYasi, $ilanSiteIcerisindeMi, $ilanMulkTuru, $ilanKonum, $ilanIsitmaTipi, $ilanBulunduguKat, $ilanBinaKatSayisi);

if ($sorgu->execute()) {
    $ilanID = $baglan->insert_id; // Eklenen ilan ID'sini al

    // Resimleri yükle ve veritabanına kaydet
    if (isset($_FILES['ilanResimler']) && count($_FILES['ilanResimler']['name']) > 0) {
        $resimSayisi = count($_FILES['ilanResimler']['name']);
        if ($resimSayisi > 25) {
            echo "En fazla 25 resim yükleyebilirsiniz.";
            exit();
        }

        for ($i = 0; $i < $resimSayisi; $i++) {
            $resimAdi = $_FILES['ilanResimler']['name'][$i];
            $resimTmp = $_FILES['ilanResimler']['tmp_name'][$i];
            $hedefKlasor = "uploads/";
            $hedefDosya = $hedefKlasor . uniqid() . "_" . basename($resimAdi);

            if (move_uploaded_file($resimTmp, $hedefDosya)) {
                // Resim yolunu veritabanına kaydet
                $resimSorgu = $baglan->prepare("INSERT INTO t_resimler (resimIlanID, resimUrl) VALUES (?, ?)");
                $resimSorgu->bind_param("is", $ilanID, $hedefDosya);
                $resimSorgu->execute();
            }
        }
    }

    echo "İlan başarıyla eklendi.";
    header("Location: ilanDetay.php?id=" . $ilanID);
    exit();
} else {
    echo "İlan eklenirken bir hata oluştu.";
}
?>

<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prestij Emlak - İlan Ekle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>İlan Ekle</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="ilanFiyat" class="form-label">Fiyat (TL)</label>
                <input type="number" class="form-control" id="ilanFiyat" name="ilanFiyat" required>
            </div>
            <div class="mb-3">
                <label for="ilanMetrekareBrut" class="form-label">Metrekare (Brüt)</label>
                <input type="number" class="form-control" id="ilanMetrekareBrut" name="ilanMetrekareBrut" required>
            </div>
            <div class="mb-3">
                <label for="ilanMetrekareNet" class="form-label">Metrekare (Net)</label>
                <input type="number" class="form-control" id="ilanMetrekareNet" name="ilanMetrekareNet" required>
            </div>
            <div class="mb-3">
                <label for="ilanOdaSayisi" class="form-label">Oda Sayısı</label>
                <input type="text" class="form-control" id="ilanOdaSayisi" name="ilanOdaSayisi" required>
            </div>
            <div class="mb-3">
                <label for="ilanBinaYasi" class="form-label">Bina Yaşı</label>
                <input type="number" class="form-control" id="ilanBinaYasi" name="ilanBinaYasi" required>
            </div>
            <div class="mb-3">
                <label for="ilanSiteIcerisindeMi" class="form-label">Site İçerisinde Mi?</label>
                <select class="form-control" id="ilanSiteIcerisindeMi" name="ilanSiteIcerisindeMi" required>
                    <option value="1">Evet</option>
                    <option value="0">Hayır</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="ilanMulkTuru" class="form-label">Mülk Türü</label>
                <input type="text" class="form-control" id="ilanMulkTuru" name="ilanMulkTuru" required>
            </div>
            <div class="mb-3">
                <label for="ilanKonum" class="form-label">Konum</label>
                <input type="text" class="form-control" id="ilanKonum" name="ilanKonum" required>
            </div>
            <div class="mb-3">
                <label for="ilanIsitmaTipi" class="form-label">Isıtma Tipi</label>
                <input type="text" class="form-control" id="ilanIsitmaTipi" name="ilanIsitmaTipi" required>
            </div>
            <div class="mb-3">
                <label for="ilanBulunduguKat" class="form-label">Bulunduğu Kat</label>
                <input type="number" class="form-control" id="ilanBulunduguKat" name="ilanBulunduguKat" required>
            </div>
            <div class="mb-3">
                <label for="ilanBinaKatSayisi" class="form-label">Bina Kat Sayısı</label>
                <input type="number" class="form-control" id="ilanBinaKatSayisi" name="ilanBinaKatSayisi" required>
            </div>
            <div class="mb-3">
                <label for="ilanResimler" class="form-label">İlan Resimleri (En fazla 25 adet)</label>
                <input type="file" class="form-control" id="ilanResimler" name="ilanResimler[]" multiple accept="image/*" required>
                <small class="form-text text-muted">Birden fazla resim seçmek için Ctrl veya Shift tuşunu kullanabilirsiniz.</small>
            </div>
            <button type="submit" class="btn btn-primary">İlanı Ekle</button>
        </form>
    </div>
</body>
</html>
