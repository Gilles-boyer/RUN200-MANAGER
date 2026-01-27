<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Exception levée lors d'erreurs d'import de fichiers (CSV, etc.).
 */
class ImportException extends DomainException
{
    protected array $errors = [];

    public function __construct(string $message, array $errors = [], array $context = [])
    {
        $this->errors = $errors;

        parent::__construct(
            message: $message,
            errorCode: 'IMPORT_FAILED',
            userMessageKey: 'exceptions.import.failed',
            context: array_merge($context, ['errors_count' => count($errors)])
        );
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function invalidFormat(string $expectedFormat): self
    {
        return new self(
            message: "Format de fichier invalide. Attendu: {$expectedFormat}",
            context: ['expected_format' => $expectedFormat]
        );
    }

    public static function missingColumns(array $missingColumns): self
    {
        return new self(
            message: 'Colonnes obligatoires manquantes: '.implode(', ', $missingColumns),
            errors: $missingColumns,
            context: ['missing_columns' => $missingColumns]
        );
    }

    public static function rowErrors(array $rowErrors): self
    {
        return new self(
            message: count($rowErrors).' erreurs détectées lors de l\'import',
            errors: $rowErrors
        );
    }

    public static function encodingError(string $detectedEncoding): self
    {
        return new self(
            message: "Encodage non supporté: {$detectedEncoding}. Utilisez UTF-8.",
            context: ['detected_encoding' => $detectedEncoding]
        );
    }

    public static function emptyFile(): self
    {
        return new self(message: 'Le fichier est vide ou ne contient aucune donnée valide');
    }

    public static function tooManyErrors(int $errorCount, int $totalRows, int $threshold): self
    {
        $percentage = round(($errorCount / $totalRows) * 100);

        return new self(
            message: "Trop d'erreurs ({$percentage}%). Import annulé. Seuil: {$threshold}%",
            context: [
                'error_count' => $errorCount,
                'total_rows' => $totalRows,
                'threshold' => $threshold,
            ]
        );
    }
}
