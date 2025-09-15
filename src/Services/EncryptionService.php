<?php

namespace Esmat\MultiTenantPermission\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;

class EncryptionService
{
    protected $encrypter;
    
    public function __construct()
    {
        $key = config('multitenant-permission.security.encryption.key');
        $this->encrypter = new Encrypter($key, 'AES-256-CBC');
    }
    
    /**
     * Encrypt a value
     */
    public function encrypt($value): string
    {
        return $this->encrypter->encrypt($value);
    }
    
    /**
     * Decrypt a value
     */
    public function decrypt($payload)
    {
        try {
            return $this->encrypter->decrypt($payload);
        } catch (DecryptException $e) {
            report($e);
            return null;
        }
    }
    
    /**
     * Encrypt a tenant ID for use in URLs
     */
    public function encryptTenantId(int $tenantId): string
    {
        return $this->encrypt($tenantId);
    }
    
    /**
     * Decrypt a tenant ID from a URL
     */
    public function decryptTenantId(string $payload): ?int
    {
        $decrypted = $this->decrypt($payload);
        
        return is_numeric($decrypted) ? (int)$decrypted : null;
    }
}
