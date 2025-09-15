<?php

namespace Esmat\MultiTenantPermission\Events;

use Esmat\MultiTenantPermission\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUpdated
{
    use Dispatchable, SerializesModels;
    
    public $user;
    public $original;
    public $changes;
    
    public function __construct(User $user, array $original, array $changes)
    {
        $this->user = $user;
        $this->original = $original;
        $this->changes = $changes;
    }
}
