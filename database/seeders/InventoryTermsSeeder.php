<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryTermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = DB::connection('tenant')
            ->table('vtiger_tab')
            ->join('vtiger_app2tab', 'vtiger_tab.tabid', '=', 'vtiger_app2tab.tabid')
            ->pluck('vtiger_tab.name')
            ->toArray();

        if (empty($modules)) {
            $modules = ['Quotes', 'SalesOrder', 'PurchaseOrder', 'Invoice'];
        }
        $terms_en = "- Unless otherwise agreed in writing by the supplier all invoices are payable within thirty (30) days of the date of invoice, in the currency of the invoice, drawn on a bank based in India or by such other method as is agreed in advance by the Supplier.\n\n - All prices are not inclusive of VAT which shall be payable in addition by the Customer at the applicable rate.";

        $terms_ar = " - ما لم يتم الاتفاق على خلاف ذلك خطياً من قبل المورد ، فإن جميع الفواتير تكون مستحقة الدفع خلال ثلاثين (30) يوماً من تاريخ الفاتورة ، بعملة الفاتورة ، مسحوبة على بنك مقره الهند أو بأي طريقة أخرى يتفق عليها المورد مسبقاً.\n\n - جميع الأسعار لا تشمل ضريبة القيمة المضافة والتي تكون مستحقة الدفع بالإضافة إلى ذلك من قبل العميل بالسعر المعمول به.";

        foreach ($modules as $module) {
            DB::connection('tenant')->table('vtiger_inventory_termsandconditions')->updateOrInsert(
                ['module_name' => $module],
                [
                    'terms_en' => $terms_en,
                    'terms_ar' => $terms_ar,
                    'is_default' => 1,
                    'status' => 1,
                    'deleted' => 0,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
