<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Jadwal Nonaktif - Presensi Kegiatan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container text-center p-4">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 450px; border-radius: 16px;">
            <div class="card-body p-4">
                <i class="fas fa-calendar-times text-warning fa-4x mb-3"></i>
                <h4 class="font-weight-bold text-dark">Sesi Presensi Belum Dibuka</h4>
                <p class="text-muted small">
                    Jadwal kegiatan <strong>{{ $jadwal->getKegiatan ? $jadwal->getKegiatan->nama : 'ini' }}</strong> saat ini sedang nonaktif atau telah ditutup oleh pengurus.
                </p>
                <div class="alert alert-light border small text-muted">
                    Tanggal: {{ $jadwal->tanggal ? $jadwal->tanggal->format('d M Y') : '-' }}<br>
                    Waktu: {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
                </div>
            </div>
        </div>
    </div>
</body>
</html>

