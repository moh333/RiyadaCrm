# Contacts Module - Modular Structure

The Contacts module has been moved to `app\Modules\Tenant\Contacts` following a modular architecture.

## Folder Structure

```
app/Modules/Tenant/Contacts/
├── Domain/                                  # Domain Layer (Pure PHP)
│   ├── Contact.php                          # Main Contact Entity
│   ├── ValueObjects/
│   │   ├── FullName.php
│   │   ├── EmailAddress.php
│   │   ├── PhoneNumber.php
│   │   ├── Address.php
│   │   └── PortalCredentials.php
│   ├── Events/
│   │   ├── ContactCreated.php
│   │   ├── ContactUpdated.php
│   │   ├── ContactDeleted.php
│   │   ├── ContactPortalEnabled.php
│   │   ├── ContactPortalDisabled.php
│   │   ├── ContactEmailChanged.php
│   │   ├── ContactAccountLinked.php
│   │   ├── ContactAccountUnlinked.php
│   │   └── ContactImageUploaded.php
│   └── Repositories/
│       └── ContactRepositoryInterface.php
│
├── Infrastructure/                          # Infrastructure Layer
│   └── EloquentContactRepository.php
│
├── Application/                             # Application Layer
│   └── UseCases/
│       ├── CreateContactUseCase.php
│       ├── UpdateContactUseCase.php
│       ├── DeleteContactUseCase.php
│       ├── EnablePortalAccessUseCase.php
│       ├── DisablePortalAccessUseCase.php
│       ├── LinkContactToAccountUseCase.php
│       ├── UnlinkContactFromAccountUseCase.php
│       ├── TransferContactOwnershipUseCase.php
│       ├── UploadContactImageUseCase.php
│       └── ConvertLeadToContactUseCase.php
│
├── Http/                                    # HTTP Layer
│   └── Controllers/
│       └── ContactsController.php
│
├── ContactsModuleServiceProvider.php        # Service Provider
└── routes.php                               # Module Routes
```

## Namespaces

All classes use the namespace pattern:
```
App\Modules\Tenant\Contacts\{Layer}\{SubFolder}\{ClassName}
```

Examples:
- `App\Modules\Tenant\Contacts\Domain\Contact`
- `App\Modules\Tenant\Contacts\Domain\ValueObjects\FullName`
- `App\Modules\Tenant\Contacts\Application\UseCases\CreateContactUseCase`
- `App\Modules\Tenant\Contacts\Http\Controllers\ContactsController`

## Registration

### 1. Register the Service Provider

Add to `config/app.php`:

```php
'providers' => [
    // ...
    App\Modules\Tenant\Contacts\ContactsModuleServiceProvider::class,
],
```

Or use auto-discovery in `composer.json`:

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "App\\Modules\\Tenant\\Contacts\\ContactsModuleServiceProvider"
            ]
        }
    }
}
```

### 2. Autoload Configuration

Ensure `composer.json` has PSR-4 autoloading for the module:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "App\\Modules\\Tenant\\Contacts\\": "app/Modules/Tenant/Contacts/"
        }
    }
}
```

Then run:
```bash
composer dump-autoload
```

## Routes

All routes are automatically loaded from `routes.php`:

- `GET /api/contacts` - List contacts
- `GET /api/contacts/{id}` - Show contact
- `POST /api/contacts` - Create contact
- `PUT /api/contacts/{id}` - Update contact
- `DELETE /api/contacts/{id}` - Delete contact
- `POST /api/contacts/{id}/portal/enable` - Enable portal
- `POST /api/contacts/{id}/portal/disable` - Disable portal
- `POST /api/contacts/{id}/account/{accountId}` - Link to account
- `DELETE /api/contacts/{id}/account` - Unlink from account
- `POST /api/contacts/{id}/image` - Upload image

## vtiger Database Tables

The module interacts with these vtiger tables (read-only schema):

- `vtiger_crmentity` - Base entity table
- `vtiger_contactdetails` - Main contact data
- `vtiger_contactsubdetails` - Extended contact data
- `vtiger_contactaddress` - Address information
- `vtiger_contactscf` - Custom fields (EAV)
- `vtiger_portalinfo` - Portal credentials
- `vtiger_customerdetails` - Support contract info

## Key Features

✅ Clean Architecture with DDD  
✅ Modular structure for multi-tenancy  
✅ vtiger EAV pattern handling  
✅ Transactional operations  
✅ Domain events  
✅ Portal access management  
✅ Image upload with cleanup  
✅ Lead conversion support  
✅ Relationship cleanup on delete  

## Next Steps

1. **Register Service Provider** in `config/app.php`
2. **Run** `composer dump-autoload`
3. **Create Event Listeners** for domain events (e.g., send portal email)
4. **Add Tests** for use cases and repository
5. **Create API Resources** for JSON transformation
6. **Add Middleware** for permissions/authorization

## Important Notes

- **DO NOT** modify vtiger tables via migrations
- **ALWAYS** use `vtiger_crmentity_seq` for ID generation
- **ALWAYS** use `createdtime`/`modifiedtime` (NOT Laravel timestamps)
- **ALWAYS** use `deleted` flag (NOT Laravel soft deletes)
- **ALWAYS** wrap multi-table operations in transactions
- Portal username **MUST** equal contact's email address
