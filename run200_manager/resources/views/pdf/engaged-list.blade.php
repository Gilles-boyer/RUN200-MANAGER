<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Liste des Engagés - {{ $race->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
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
            margin-bottom: 20px;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }
        .race-info p {
            margin: 5px 0;
        }
        .stats {
            margin-bottom: 20px;
        }
        .stats-grid {
            display: table;
            width: 100%;
        }
        .stats-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            background: #e9e9e9;
            border-right: 1px solid #fff;
        }
        .stats-item:last-child {
            border-right: none;
        }
        .stats-item .number {
            font-size: 24pt;
            font-weight: bold;
            color: #333;
        }
        .stats-item .label {
            font-size: 8pt;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background: #333;
            color: #fff;
            padding: 8px 5px;
            text-align: left;
            font-size: 9pt;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .paddock {
            font-weight: bold;
            text-align: center;
            width: 60px;
        }
        .race-number {
            font-weight: bold;
            text-align: center;
            width: 50px;
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
        .categories-summary {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .categories-summary h3 {
            font-size: 11pt;
            margin-bottom: 10px;
        }
        .category-badge {
            display: inline-block;
            background: #e0e0e0;
            padding: 3px 8px;
            margin: 2px;
            border-radius: 3px;
            font-size: 8pt;
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
        <div class="stats-grid">
            <div class="stats-item">
                <div class="number">{{ $totalEngaged }}</div>
                <div class="label">ENGAGÉS</div>
            </div>
            @foreach($categoryCounts as $category => $count)
            <div class="stats-item">
                <div class="number">{{ $count }}</div>
                <div class="label">{{ strtoupper($category) }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="paddock">PADDOCK</th>
                <th class="race-number">N°</th>
                <th>PILOTE</th>
                <th>VÉHICULE</th>
                <th>CATÉGORIE</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $registration)
            <tr>
                <td class="paddock">{{ $registration->paddock ?? '-' }}</td>
                <td class="race-number">{{ $registration->car->race_number }}</td>
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
                <td colspan="5" style="text-align: center; padding: 20px;">Aucun engagé pour cette course</td>
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
