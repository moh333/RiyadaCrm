<?php

namespace App\Modules\Tenant\Contacts\Infrastructure;

use App\Modules\Tenant\Contacts\Domain\CustomField;
use App\Modules\Tenant\Contacts\Domain\Repositories\CustomFieldRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * EloquentCustomFieldRepository
 * 
 * Manages custom field definitions in vtiger_field table
 */
class EloquentCustomFieldRepository implements CustomFieldRepositoryInterface
{
    public function findByModule(int $tabId): Collection
    {
        $fields = DB::connection('tenant')
            ->table('vtiger_field')
            ->where('tabid', $tabId)
            ->where('generatedtype', 2) // Only custom fields
            ->orderBy('block')
            ->orderBy('sequence')
            ->get();

        return $fields->map(fn($field) => CustomField::fromDatabase((array) $field));
    }

    public function findById(int $fieldId): ?CustomField
    {
        $field = DB::connection('tenant')
            ->table('vtiger_field')
            ->where('fieldid', $fieldId)
            ->first();

        return $field ? CustomField::fromDatabase((array) $field) : null;
    }

    public function findByFieldName(int $tabId, string $fieldName): ?CustomField
    {
        $field = DB::connection('tenant')
            ->table('vtiger_field')
            ->where('tabid', $tabId)
            ->where('fieldname', $fieldName)
            ->first();

        return $field ? CustomField::fromDatabase((array) $field) : null;
    }

    public function save(CustomField $field): void
    {
        $data = $field->toArray();

        if ($this->findById($field->getFieldId())) {
            // Update existing
            DB::connection('tenant')
                ->table('vtiger_field')
                ->where('fieldid', $field->getFieldId())
                ->update($data);
        } else {
            // Insert new
            DB::connection('tenant')
                ->table('vtiger_field')
                ->insert($data);
        }
    }

    public function delete(int $fieldId): void
    {
        // 1. Get field info first for cleanup
        $field = DB::connection('tenant')->table('vtiger_field')->where('fieldid', $fieldId)->first();
        if (!$field)
            return;

        // 2. Physical delete from vtiger_field
        DB::connection('tenant')->table('vtiger_field')->where('fieldid', $fieldId)->delete();

        // 3. Cleanup from vtiger_def_org_field
        DB::connection('tenant')->table('vtiger_def_org_field')->where('fieldid', $fieldId)->delete();
    }

    public function getNextSequence(int $blockId): int
    {
        $maxSequence = DB::connection('tenant')
            ->table('vtiger_field')
            ->where('block', $blockId)
            ->max('sequence');

        return ($maxSequence ?? 0) + 1;
    }

    public function nextFieldId(): int
    {
        return $this->getNextIdFromSeq('vtiger_field_seq', 'vtiger_field', 'fieldid');
    }

    private function getNextIdFromSeq(string $seqTable, string $dataTable, string $idColumn): int
    {
        return DB::connection('tenant')->transaction(function () use ($seqTable, $dataTable, $idColumn) {
            $query = DB::connection('tenant')->table($seqTable)->lockForUpdate();
            $result = $query->first();

            if (!$result) {
                // Initialize if empty
                $maxId = DB::connection('tenant')->table($dataTable)->max($idColumn) ?? 1000;
                $nextId = $maxId + 1;
                DB::connection('tenant')->table($seqTable)->insert(['id' => $nextId]);
                return $nextId;
            }

            $nextId = (int) $result->id + 1;
            DB::connection('tenant')->table($seqTable)->where('id', $result->id)->update(['id' => $nextId]);

            return $nextId;
        });
    }

    public function nextPicklistValueId(): int
    {
        return $this->getNextIdFromSeq('vtiger_picklistvalues_seq', 'vtiger_role2picklist', 'picklistvalueid');
    }

    public function nextPicklistId(): int
    {
        return $this->getNextIdFromSeq('vtiger_picklist_seq', 'vtiger_picklist', 'picklistid');
    }

    public function columnExists(string $columnName, string $tableName): bool
    {
        return Schema::connection('tenant')->hasColumn($tableName, $columnName);
    }

    public function createPicklist(string $fieldName, array $values): void
    {
        $tableName = "vtiger_{$fieldName}";
        $idColumn = "{$fieldName}id";

        // 1. Create the picklist table if it doesn't exist
        // DDL (Schema::create) causes an implicit commit in MySQL, so it MUST be done outside any transaction.
        if (!Schema::connection('tenant')->hasTable($tableName)) {
            Schema::connection('tenant')->create($tableName, function ($table) use ($fieldName, $idColumn) {
                $table->increments($idColumn);
                $table->string($fieldName, 200);
                $table->integer('presence')->default(1);
                $table->integer('picklist_valueid');
                $table->integer('sortid')->nullable();
                $table->index($fieldName);
            });
        }

        // 2. Perform DML operations in a transaction
        DB::connection('tenant')->transaction(function () use ($tableName, $fieldName, $values) {
            // Register in vtiger_picklist
            $picklistId = $this->nextPicklistId();
            DB::connection('tenant')->table('vtiger_picklist')->insert([
                'picklistid' => $picklistId,
                'name' => $fieldName
            ]);

            // Get all roles to associate values
            $roles = DB::connection('tenant')->table('vtiger_role')->pluck('roleid');

            // Insert values
            foreach ($values as $index => $value) {
                $picklistValueId = $this->nextPicklistValueId();

                DB::connection('tenant')->table($tableName)->insert([
                    $fieldName => $value,
                    'presence' => 1,
                    'picklist_valueid' => $picklistValueId,
                    'sortid' => $index
                ]);

                foreach ($roles as $roleId) {
                    DB::connection('tenant')->table('vtiger_role2picklist')->insert([
                        'roleid' => $roleId,
                        'picklistvalueid' => $picklistValueId,
                        'picklistid' => $picklistId,
                        'sortid' => $index
                    ]);
                }
            }
        });
    }

    public function deletePicklist(string $fieldName): void
    {
        $tableName = "vtiger_{$fieldName}";

        // 1. Get picklist ID
        $picklist = DB::connection('tenant')->table('vtiger_picklist')->where('name', $fieldName)->first();
        if ($picklist) {
            // 2. Cleanup role associations
            DB::connection('tenant')->table('vtiger_role2picklist')->where('picklistid', $picklist->picklistid)->delete();

            // 3. Delete from vtiger_picklist
            DB::connection('tenant')->table('vtiger_picklist')->where('picklistid', $picklist->picklistid)->delete();
        }

        // 4. Drop the picklist table
        Schema::connection('tenant')->dropIfExists($tableName);
    }

    public function ensureCustomTableExists(string $tableName): void
    {
        if (!Schema::connection('tenant')->hasTable($tableName)) {
            // Determine module name from table name (e.g., vtiger_contactscf -> contacts)
            $modulePart = str_replace(['vtiger_', 'cf'], '', $tableName);

            // Heuristic for primary key (vtiger standard)
            // Contacts -> contactid, Accounts -> accountid, etc.
            // Singularize if needed, but vtiger usually uses contactid for vtiger_contactdetails
            $primaryKey = rtrim($modulePart, 's') . 'id';

            // Special cases
            if ($modulePart === 'contacts')
                $primaryKey = 'contactid';
            if ($modulePart === 'accounts')
                $primaryKey = 'accountid';

            Schema::connection('tenant')->create($tableName, function ($table) use ($primaryKey) {
                $table->integer($primaryKey)->primary();
            });
        }
    }
}
