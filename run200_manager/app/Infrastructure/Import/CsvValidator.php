<?php

declare(strict_types=1);

namespace App\Infrastructure\Import;

use App\Domain\Exceptions\ImportException;
use Illuminate\Http\UploadedFile;

/**
 * Validateur avancé pour les fichiers CSV avant import.
 * Gère l'encoding, les séparateurs, et fournit un aperçu.
 */
class CsvValidator
{
    public const MAX_FILE_SIZE_KB = 5120; // 5 MB

    public const SUPPORTED_ENCODINGS = ['UTF-8', 'ISO-8859-1', 'Windows-1252'];

    public const ERROR_THRESHOLD_PERCENT = 50;

    public const PREVIEW_ROWS = 10;

    private array $detectedInfo = [];

    /**
     * Valide un fichier CSV uploadé.
     *
     * @throws ImportException
     */
    public function validate(UploadedFile $file): array
    {
        $this->detectedInfo = [];

        // 1. Validation de base
        $this->validateFileType($file);
        $this->validateFileSize($file);

        // 2. Lecture du contenu
        $content = file_get_contents($file->getRealPath());
        if ($content === false || strlen($content) === 0) {
            throw ImportException::emptyFile();
        }

        // 3. Détection et conversion de l'encoding
        $encoding = $this->detectEncoding($content);
        $this->detectedInfo['encoding'] = $encoding;

        if ($encoding !== 'UTF-8') {
            $content = $this->convertToUtf8($content, $encoding);
        }

        // 4. Détection du séparateur
        $delimiter = $this->detectDelimiter($content);
        $this->detectedInfo['delimiter'] = $delimiter;

        // 5. Parsing du CSV
        $lines = $this->parseLines($content, $delimiter);
        $this->detectedInfo['total_rows'] = count($lines) - 1; // -1 pour le header

        if (count($lines) < 2) {
            throw ImportException::emptyFile();
        }

        // 6. Validation des colonnes
        $header = $lines[0];
        $this->validateColumns($header);
        $this->detectedInfo['columns'] = $header;

        // 7. Génération de l'aperçu
        $this->detectedInfo['preview'] = $this->generatePreview($lines);

        return $this->detectedInfo;
    }

    /**
     * Valide uniquement le type de fichier.
     *
     * @throws ImportException
     */
    private function validateFileType(UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        $validMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        $validExtensions = ['csv', 'txt'];

        if (! in_array($mimeType, $validMimes) && ! in_array($extension, $validExtensions)) {
            throw ImportException::invalidFormat('CSV (.csv)');
        }
    }

    /**
     * Valide la taille du fichier.
     *
     * @throws ImportException
     */
    private function validateFileSize(UploadedFile $file): void
    {
        $sizeKb = $file->getSize() / 1024;

        if ($sizeKb > self::MAX_FILE_SIZE_KB) {
            throw new ImportException(
                message: sprintf('Fichier trop volumineux (%.1f KB). Maximum: %d KB', $sizeKb, self::MAX_FILE_SIZE_KB),
                context: ['size_kb' => $sizeKb, 'max_kb' => self::MAX_FILE_SIZE_KB]
            );
        }
    }

    /**
     * Détecte l'encodage du contenu.
     */
    private function detectEncoding(string $content): string
    {
        // Check for BOM
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            return 'UTF-8';
        }

        // Use mb_detect_encoding
        $detected = mb_detect_encoding($content, self::SUPPORTED_ENCODINGS, true);

        return $detected ?: 'UTF-8';
    }

    /**
     * Convertit le contenu en UTF-8.
     *
     * @throws ImportException
     */
    private function convertToUtf8(string $content, string $fromEncoding): string
    {
        $converted = @mb_convert_encoding($content, 'UTF-8', $fromEncoding);

        if ($converted === false) {
            throw ImportException::encodingError($fromEncoding);
        }

        return $converted;
    }

    /**
     * Détecte le séparateur CSV (virgule ou point-virgule).
     */
    private function detectDelimiter(string $content): string
    {
        // Prendre les premières lignes pour l'analyse
        $sample = substr($content, 0, 4096);

        $commaCount = substr_count($sample, ',');
        $semicolonCount = substr_count($sample, ';');

        return $semicolonCount > $commaCount ? ';' : ',';
    }

    /**
     * Parse les lignes du CSV.
     */
    private function parseLines(string $content, string $delimiter): array
    {
        $lines = [];
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        foreach (explode("\n", $content) as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $columns = str_getcsv($line, $delimiter);
            $columns = array_map('trim', $columns);
            $lines[] = $columns;
        }

        return $lines;
    }

    /**
     * Valide les colonnes obligatoires.
     *
     * @throws ImportException
     */
    private function validateColumns(array $header): void
    {
        $requiredColumns = ['position', 'bib', 'temps'];
        $alternativeNames = [
            'position' => ['position', 'pos', 'place', 'rang'],
            'bib' => ['bib', 'dossard', 'numero', 'number', 'race_number'],
            'temps' => ['temps', 'time', 'chrono', 'duree', 'duration'],
        ];

        $normalizedHeader = array_map(
            fn ($col) => preg_replace('/[^a-z0-9]/', '', mb_strtolower($col)),
            $header
        );

        $missingColumns = [];

        foreach ($requiredColumns as $required) {
            $found = false;
            foreach ($alternativeNames[$required] as $alt) {
                if (in_array($alt, $normalizedHeader)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $missingColumns[] = $required;
            }
        }

        if (! empty($missingColumns)) {
            throw ImportException::missingColumns($missingColumns);
        }
    }

    /**
     * Génère un aperçu des premières lignes.
     */
    private function generatePreview(array $lines): array
    {
        $preview = [];
        $header = $lines[0];

        $dataRows = array_slice($lines, 1, self::PREVIEW_ROWS);

        foreach ($dataRows as $index => $row) {
            $previewRow = [];
            foreach ($header as $colIndex => $colName) {
                $previewRow[$colName] = $row[$colIndex] ?? '';
            }
            $preview[] = [
                'row_number' => $index + 2,
                'data' => $previewRow,
            ];
        }

        return $preview;
    }

    /**
     * Récupère les informations détectées.
     */
    public function getDetectedInfo(): array
    {
        return $this->detectedInfo;
    }

    /**
     * Valide un contenu CSV (string) sans fichier uploadé.
     * Utile pour les tests ou imports programmatiques.
     */
    public function validateContent(string $content): array
    {
        $this->detectedInfo = [];

        if (strlen($content) === 0) {
            throw ImportException::emptyFile();
        }

        // Détection encoding
        $encoding = $this->detectEncoding($content);
        $this->detectedInfo['encoding'] = $encoding;

        if ($encoding !== 'UTF-8') {
            $content = $this->convertToUtf8($content, $encoding);
        }

        // Détection délimiteur
        $delimiter = $this->detectDelimiter($content);
        $this->detectedInfo['delimiter'] = $delimiter;

        // Parsing
        $lines = $this->parseLines($content, $delimiter);
        $this->detectedInfo['total_rows'] = count($lines) - 1;

        if (count($lines) < 2) {
            throw ImportException::emptyFile();
        }

        // Validation colonnes
        $header = $lines[0];
        $this->validateColumns($header);
        $this->detectedInfo['columns'] = $header;

        // Aperçu
        $this->detectedInfo['preview'] = $this->generatePreview($lines);

        return $this->detectedInfo;
    }
}
