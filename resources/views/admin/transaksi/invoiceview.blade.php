<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice_no }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            color: #fff;
            margin: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: #000;
        }

        .container {
            width: 100%;
            min-height: 100vh;
            /* pastikan menutupi layar penuh */
            padding: 40px;
            box-sizing: border-box;

            /* 🔧 Perbaiki background agar penuh */
            background-image: url('{{ url('bg-invoice.jpeg') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }


        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #ffffff22;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo img {
            width: 150px;
        }

        .invoice-info {
            text-align: right;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-section {
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.8;
        }

        .info-section strong {
            width: 120px;
            display: inline-block;
        }

        .section-title {
            background-color: #8fd3f4;
            color: #000;
            font-weight: bold;
            padding: 8px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            color: #fff;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 10px;
        }

        .desc {
            background-color: #1b1b1b;
        }

        .desc td {
            border-bottom: 1px solid #2a2a2a;
        }

        .total-row {
            background-color: #8fd3f4;
            color: #000;
            font-weight: bold;
        }

        .footer {
            font-size: 12px;
            text-align: center;
            margin-top: 40px;
            color: #ccc;
        }

        .toolbar {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
        }

        .toolbar button {
            background-color: #8fd3f4;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 6px;
            margin: 0 5px;
        }

        @media print {

            .toolbar {
                display: none;
            }

            @page {
                size: A4;
                margin: 0;
                /* ❗ hapus semua margin print */
            }

            html,
            body {
                margin: 0;
                padding: 0;
                height: 100%;
                width: 100%;
                background: #000;
            }

            .container {
                width: 100%;
                height: 100vh;
                padding: 40px;
                box-sizing: border-box;

                background-image: url('{{ url('bg-invoice.jpeg') }}');
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center center;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>

    <div id="invoice" class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('logowhite.PNG') }}" alt="Logo">
            </div>
            <div class="invoice-info">
                <p>No. Invoice : {{ $invoice_no }}</p>
                <p>Tanggal : {{ $tanggal }}</p>
            </div>
        </div>

        <div class="info-section">
            <p><strong>Nama Member :</strong> {{ $nama_member }}</p>
            <p><strong>No. Member :</strong> {{ $no_member }}</p>
            <p><strong>Telepon :</strong> {{ $telepon }}</p>
        </div>

        <table>
            <tr class="section-title">
                <th>Deskripsi Layanan</th>
                <th>Jumlah</th>
            </tr>
            <tr class="desc">
                <td>
                    <strong>{{ $paket }}</strong><br>
                    Periode {{ $periode_mulai }} - {{ $periode_selesai }}<br>
                </td>
                <td>Rp {{ $total }}</td>
            </tr>
            <tr class="desc">
                <td>Subtotal</td>
                <td>Rp {{ $total }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Pembayaran :</td>
                <td>Rp {{ $total }}</td>
            </tr>
        </table>

        <div class="info-section">
            <p><strong>Metode Pembayaran :</strong> {{ $metode }} (BRI a.n G-Rind Up Fitness 1)</p>
            <p><strong>Tanggal Bayar :</strong> {{ $tanggal_bayar }}</p>
        </div>

        <div class="footer">
            <p>Thank you for grinding with us.<br>
                Let’s build strength, discipline, and legacy together.</p>
        </div>
    </div>
</body>

</html>
