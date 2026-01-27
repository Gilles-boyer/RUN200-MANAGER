<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Engagement - {{ $form->pilot_name }} - #{{ $form->car_race_number }}</title>
    <style>
        @page {
            margin: 12mm 10mm 12mm 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            color: #1e3a5f;
            line-height: 1.3;
        }

        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .logo {
            font-size: 22pt;
            font-weight: bold;
        }
        .logo-run {
            color: #c41e3a;
        }
        .logo-200 {
            color: #1e3a5f;
        }
        .logo-sub {
            font-size: 7pt;
            color: #1e3a5f;
            margin-top: -5px;
        }
        .main-title {
            font-size: 13pt;
            font-weight: bold;
            color: #c41e3a;
            text-align: center;
        }
        .race-date {
            font-size: 11pt;
            font-weight: bold;
            color: #c41e3a;
            text-align: center;
        }

        /* Race Number Box */
        .race-number-box {
            background: #1e3a5f;
            color: white;
            padding: 5px 12px;
            text-align: center;
            border-radius: 5px;
        }
        .race-number-label {
            font-size: 7pt;
            margin: 0;
        }
        .race-number {
            font-size: 32pt;
            font-weight: bold;
            margin: 0;
            line-height: 1;
        }

        /* Control Boxes */
        .controls-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .control-box {
            border: 1px solid #1e3a5f;
            padding: 6px;
            vertical-align: top;
        }
        .control-title {
            background: #1e3a5f;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            padding: 2px;
            margin: -6px -6px 6px -6px;
        }
        .control-row {
            margin-bottom: 4px;
            font-size: 8pt;
        }
        .control-label {
            font-weight: bold;
        }

        /* Section */
        .section {
            border: 1px solid #1e3a5f;
            margin-bottom: 8px;
            border-radius: 3px;
            overflow: hidden;
        }
        .section-title {
            background: #c41e3a;
            color: white;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            padding: 3px;
        }
        .section-content {
            padding: 8px;
        }

        /* Two columns table */
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col td {
            width: 50%;
            vertical-align: top;
            padding: 2px 5px 2px 0;
        }

        /* Fields */
        .field-label {
            font-weight: bold;
            font-size: 8pt;
        }
        .field-value {
            font-size: 9pt;
        }
        .highlight {
            font-weight: bold;
        }

        /* Checkboxes */
        .cb {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #1e3a5f;
            text-align: center;
            font-size: 7pt;
            line-height: 8px;
            vertical-align: middle;
            margin-right: 2px;
        }
        .cb-checked {
            background: #1e3a5f;
            color: white;
        }
        .cb-label {
            font-size: 8pt;
            margin-right: 10px;
            font-weight: bold;
        }

        /* Engagement text */
        .engagement-text {
            font-size: 8pt;
            text-align: justify;
            line-height: 1.4;
        }
        .engagement-text p {
            margin: 4px 0;
        }

        /* Signature */
        .signature-area {
            margin-top: 12px;
            text-align: center;
        }
        .signature-info {
            font-size: 9pt;
            margin-bottom: 8px;
        }
        .signature-mention {
            font-size: 8pt;
            font-style: italic;
        }
        .signature-img {
            max-height: 50px;
            max-width: 180px;
        }
        .signature-table {
            width: 100%;
            margin-top: 8px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: bottom;
        }
        .signature-label {
            font-size: 7pt;
            margin-top: 3px;
        }

        /* Minor alert */
        .minor-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 5px;
            margin-top: 8px;
            font-size: 8pt;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding: 8px;
            border-top: 1px solid #ddd;
        }
        .footer-logos {
            font-size: 8pt;
            color: #1e3a5f;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 100px; vertical-align: middle;">
                <div class="logo"><span class="logo-run">RUN</span><span class="logo-200">200</span></div>
                <div class="logo-sub">CIRCUIT FELIX GUICHARD</div>
            </td>
            <td style="vertical-align: middle; text-align: center;">
                <div class="main-title">ENGAGEMENT EPREUVE D'ACCÉLÉRATION DU</div>
                <div class="race-date">{{ mb_strtoupper($form->race_date->locale('fr')->isoFormat('dddd D MMM YYYY')) }}</div>
            </td>
            <td style="width: 90px; text-align: right; vertical-align: middle;">
                <div class="race-number-box">
                    <div class="race-number-label">N° DE COURSE</div>
                    <div class="race-number">{{ $form->car_race_number }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Control boxes --}}
    <table class="controls-table">
        <tr>
            <td style="width: 50%; padding-right: 4px;">
                <div class="control-box">
                    <div class="control-title">Vérification technique</div>
                    <div class="control-row"><span class="control-label">Date :</span> {{ $form->tech_checked_at?->format('d/m/Y H:i') ?? '________________' }}</div>
                    <div class="control-row"><span class="control-label">Note :</span> {{ $form->tech_notes ?? '________________' }}</div>
                    <div class="control-row"><span class="control-label">Contrôleur :</span> {{ $form->tech_controller_name ?? '________________' }}</div>
                    <div class="control-row"><span class="control-label">Signature :</span> @if($form->tech_checked_at) <span style="font-style: italic; color: #059669;">✓ Validé électroniquement</span> @endif</div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 4px;">
                <div class="control-box">
                    <div class="control-title">Contrôle administratif</div>
                    <div class="control-row"><span class="control-label">Date :</span> {{ $form->admin_validated_at?->format('d/m/Y H:i') ?? '________________' }}</div>
                    <div class="control-row"><span class="control-label">Note :</span> {{ $form->admin_notes ?? '________________' }}</div>
                    <div class="control-row"><span class="control-label">Agent :</span> {{ $form->adminValidator?->name ?? $form->witness->name ?? '________________' }}</div>
                    <div class="control-row"><span class="control-label">Signature :</span> @if($form->admin_validated_at) <span style="font-style: italic; color: #059669;">✓ Validé électroniquement</span> @endif</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Pilote --}}
    <div class="section">
        <div class="section-title">Pilote</div>
        <div class="section-content">
            <table class="two-col">
                <tr>
                    <td><span class="field-label">Nom :</span> <span class="field-value highlight">{{ strtoupper(explode(' ', $form->pilot_name)[1] ?? $form->pilot_name) }}</span></td>
                    <td><span class="field-label">Prénom :</span> <span class="field-value highlight">{{ strtoupper(explode(' ', $form->pilot_name)[0] ?? '') }}</span></td>
                </tr>
                <tr>
                    <td><span class="field-label">N°Licence :</span> <span class="field-value highlight">{{ $form->pilot_license_number ?? '' }}</span></td>
                    <td><span class="field-label">Date de naissance :</span> <span class="field-value highlight">{{ $form->pilot_birth_date?->format('d/m/Y') ?? '' }}</span></td>
                </tr>
                <tr>
                    <td><span class="field-label">N°Permis :</span> <span class="field-value">{{ $form->pilot_permit_number ?? '' }}</span></td>
                    <td><span class="field-label">Délivré le :</span> <span class="field-value">{{ $form->pilot_permit_date?->format('d/m/Y') ?? '' }}</span></td>
                </tr>
                <tr>
                    <td><span class="field-label">Téléphone :</span> <span class="field-value highlight">{{ $form->pilot_phone ?? '' }}</span></td>
                    <td><span class="field-label">Email :</span> <span class="field-value">{{ $form->pilot_email ?? '' }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Voiture --}}
    <div class="section">
        <div class="section-title">Voiture</div>
        <div class="section-content">
            <table class="two-col">
                <tr>
                    <td>
                        <span class="field-label">Marque / Modèle :</span>
                        <span class="field-value highlight">{{ $form->car_make }} {{ $form->car_model }}</span>
                    </td>
                    <td>
                        <span class="field-label">Catégorie :</span>
                        <span class="field-value highlight">{{ $form->car_category }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Engagement Pilote --}}
    <div class="section">
        <div class="section-title">Engagement Pilote</div>
        <div class="section-content">
            <div class="engagement-text">
                <p>- Je déclare sur l'honneur ne pas être sous le coup d'une suspension de licence</p>
                <p>- Je soussigné <strong>{{ strtoupper($form->pilot_name) }}</strong></p>
                <p>déclare avoir pris connaissance du règlement particulier de la compétition ainsi que de la réglementation générale des prescriptions générales des courses de côte telles qu'elles ont été établies par la F.F.S.A.</p>
            </div>

            @if($form->is_minor)
            <div class="minor-box">
                <strong>⚠️ PILOTE MINEUR</strong><br>
                Tuteur légal : {{ $form->guardian_name }}<br>
                N° Licence tuteur : {{ $form->guardian_license_number ?? 'Non renseigné' }}
            </div>
            @endif

            <div class="signature-area">
                <div class="signature-info">
                    Fait à Sainte Anne, le {{ $form->signed_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                </div>
                <div class="signature-mention">
                    Signature et Mention : "Je certifie conforme"
                </div>

                <table class="signature-table">
                    <tr>
                        <td style="width: {{ $form->is_minor ? '50%' : '100%' }};">
                            @if($form->signature_data)
                                <img src="{{ $form->signature_data }}" alt="Signature" class="signature-img">
                            @else
                                <div style="height: 50px; border-bottom: 1px solid #1e3a5f; width: 150px; margin: 0 auto;"></div>
                            @endif
                            <div class="signature-label">Signature du pilote</div>
                        </td>
                        @if($form->is_minor)
                        <td style="width: 50%;">
                            @if($form->guardian_signature_data)
                                <img src="{{ $form->guardian_signature_data }}" alt="Signature tuteur" class="signature-img">
                            @else
                                <div style="height: 50px; border-bottom: 1px solid #1e3a5f; width: 150px; margin: 0 auto;"></div>
                            @endif
                            <div class="signature-label">Signature du tuteur légal</div>
                        </td>
                        @endif
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-logos">
            <strong>CFG</strong> | <strong>LSAR</strong> - Ligue du Sport Automobile de la Réunion | <strong>FFSA</strong> | <strong>ASA</strong> - Circuit Felix Guichard
        </div>
        <div>Document généré le {{ $generatedAt->format('d/m/Y à H:i') }} - Engagement numérique RUN200</div>
    </div>
</body>
</html>
