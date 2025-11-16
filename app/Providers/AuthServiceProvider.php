<?php

namespace App\Providers;

use App\Models\User;
use App\Domains\Users\Policies\UserPolicy;
use App\Domains\Users\Policies\RolePolicy;
use App\Domains\Users\Policies\PermissionPolicy;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
// imports de SupportTechnical
use IncadevUns\CoreDomain\Models\Ticket;
use IncadevUns\CoreDomain\Models\TicketReply;
use IncadevUns\CoreDomain\Models\ReplyAttachment;
use App\Domains\SupportTechnical\Policies\TicketPolicy;
use App\Domains\SupportTechnical\Policies\TicketReplyPolicy;
use App\Domains\SupportTechnical\Policies\ReplyAttachmentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
        // Políticas de SupportTechnical
        Ticket::class => TicketPolicy::class,
        TicketReply::class => TicketReplyPolicy::class,
        ReplyAttachment::class => ReplyAttachmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}