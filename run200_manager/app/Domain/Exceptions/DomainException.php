<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Exception de base pour toutes les exceptions métier du domaine.
 * Fournit des fonctionnalités communes : codes d'erreur, contexte, traduction.
 */
abstract class DomainException extends Exception implements Arrayable
{
    protected array $context = [];

    protected string $errorCode;

    protected string $userMessageKey;

    public function __construct(
        string $message,
        string $errorCode,
        string $userMessageKey,
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->errorCode = $errorCode;
        $this->userMessageKey = $userMessageKey;
        $this->context = $context;
    }

    /**
     * Code d'erreur unique pour l'identification.
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Clé de traduction pour le message utilisateur.
     */
    public function getUserMessageKey(): string
    {
        return $this->userMessageKey;
    }

    /**
     * Message traduit pour l'utilisateur.
     */
    public function getUserMessage(): string
    {
        return __($this->userMessageKey, $this->context);
    }

    /**
     * Contexte de l'exception pour les logs et debug.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Conversion en tableau pour les réponses API.
     */
    public function toArray(): array
    {
        // Use try/catch for unit tests where translator may not be bound
        try {
            $message = $this->getUserMessage();
        } catch (\Throwable) {
            $message = $this->getMessage();
        }

        $showContext = false;
        try {
            $showContext = app()->environment('local', 'testing');
        } catch (\Throwable) {
            // Ignore in unit tests
        }

        return [
            'error_code' => $this->errorCode,
            'message' => $message,
            'context' => $showContext ? $this->context : [],
        ];
    }

    /**
     * Log structuré de l'exception.
     */
    public function toLogContext(): array
    {
        return [
            'exception' => static::class,
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}
