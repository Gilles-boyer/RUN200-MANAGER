<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Liste des Engagés - {{ $race->name }}</title>
    <style>
        @page {
            margin: 14mm 12mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            margin: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2px solid #333;
            padding-bottom: 9px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0;
            font-size: 14pt;
            color: #666;
            font-weight: normal;
        }
        .race-info {
            margin-bottom: 12px;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }
        .race-info p {
            margin: 5px 0;
        }
        .stats {
            margin-bottom: 12px;
        }
        .total-engaged {
            font-size: 12pt;
            font-weight: bold;
            color: #333;
        }
        .category-summary {
            margin-top: 6px;
            table-layout: fixed;
        }
        .category-summary th,
        .category-summary td {
            padding: 4px 6px;
            font-size: 8pt;
        }
        .category-summary .count {
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .engaged-table {
            table-layout: fixed;
        }
        .engaged-table thead {
            display: table-header-group;
        }
        .engaged-table tr {
            page-break-inside: avoid;
        }
        th {
            background: #333;
            color: #fff;
            padding: 6px 4px;
            text-align: left;
            font-size: 8pt;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8pt;
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .paddock {
            font-weight: bold;
            text-align: center;
        }
        .race-number {
            font-weight: bold;
            text-align: center;
        }
        .category {
            font-size: 8pt;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RUN200 - LISTE DES ENGAGÉS</h1>
        <h2>{{ $race->name }}</h2>
    </div>

    <div class="race-info">
        <p><strong>Date :</strong> {{ $race->race_date->format('d/m/Y') }}</p>
        <p><strong>Lieu :</strong> {{ $race->location }}</p>
        <p><strong>Saison :</strong> {{ $race->season->name }}</p>
    </div>

    <div class="stats">
        <div class="total-engaged">{{ $totalEngaged }} engagés</div>
        @if(count($categoryCounts) > 0)
            <table class="category-summary">
                <colgroup>
                    <col style="width: 40%;"><col style="width: 10%;">
                    <col style="width: 40%;"><col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr><th>CATÉGORIE</th><th class="count">TOTAL</th><th>CATÉGORIE</th><th class="count">TOTAL</th></tr>
                </thead>
                <tbody>
                    @foreach(array_chunk($categoryCounts, 2, true) as $categories)
                        <tr>
                            @foreach($categories as $category => $count)
                                <td>{{ $category }}</td><td class="count">{{ $count }}</td>
                            @endforeach
                            @if(count($categories) === 1)
                                <td></td><td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <table class="engaged-table">
        <colgroup>
            <col style="width: 8%;"><col style="width: 9%;"><col style="width: 6%;">
            <col style="width: 18%;"><col style="width: 21%;"><col style="width: 21%;"><col style="width: 17%;">
        </colgroup>
        <thead>
            <tr>
                <th style="text-align: center;">QR</th>
                <th class="paddock">PADDOCK</th>
                <th class="race-number">N°</th>
                <th>CODE INSCRIPTION</th>
                <th>PILOTE</th>
                <th>VÉHICULE</th>
                <th>CATÉGORIE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $registration)
            <tr>
                <td style="text-align: center; padding: 4px;">
                    @if($registration->qr_code_data_uri)
                        <img src="{{ $registration->qr_code_data_uri }}" alt="QR" style="width: 38px; height: 38px;">
                    @else
                        -
                    @endif
                </td>
                <td class="paddock">{{ $registration->paddock ?? '-' }}</td>
                <td class="race-number">{{ $registration->car->race_number }}</td>
                <td style="font-weight: bold; font-size: 8pt;">{{ $registration->registration_code ?? '-' }}</td>
                <td>
                    {{ $registration->pilot->last_name }} {{ $registration->pilot->first_name }}
                    <br><span class="category">Licence: {{ $registration->pilot->license_number }}</span>
                </td>
                <td>
                    {{ $registration->car->make }} {{ $registration->car->model }}
                </td>
                <td>{{ $registration->car->category->name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Aucun engagé pour cette course</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Document généré le {{ $generatedAt->format('d/m/Y à H:i') }} - RUN200 Manager</p>
        <p>Ce document est à usage interne uniquement.</p>
    </div>
</body>
</html>
