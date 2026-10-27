<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bimbingan Konseling Kelompok</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #666; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h3 { margin: 0 0 5px 0; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>Laporan Bimbingan Konseling Kelompok</h3>
        <p>Periode: {{ $namaBulan ?? ($bulan ?? '-') }} {{ $tahun ?? '' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 15%;">Divisi</th>
                <th style="width: 15%;">Kelas</th>
                <th style="width: 15%;">Periode</th>
                <th style="width: 35%;">Kasus</th>
                <th style="width: 15%;">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($datas as $index => $data)
                <tr>
                    <td class="text-center">{{ $index + 1 }}.</td>
                    <td>{{ $data->getDivisi->nama ?? '-' }}</td>
                    <td>{{ $data->getKelas->nama ?? '-' }}</td>
                    <td class="text-center">{{ isset(\App\Models\MasterData\Tanggal::listBulan[$data->bulan]) ? \App\Models\MasterData\Tanggal::listBulan[$data->bulan] : $data->bulan }} {{ $data->tahun }}</td>
                    <td>{{ $data->kasus ?? '-' }}</td>
                    <td class="text-center">{{ !empty($data->tanggal) ? (is_numeric($data->tanggal) ? date('d-m-Y', $data->tanggal) : date('d-m-Y', strtotime($data->tanggal))) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Data laporan tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
