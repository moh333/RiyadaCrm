<?php

use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;


require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$draft = [
    'models' => []
];

// الفولدرات اللي فيها الميجريشنز
$folders = [
    database_path('migrations'),
    database_path('migrations/tenants'),
];

foreach ($folders as $folder) {
    $files = glob($folder . '/*.php');

    foreach ($files as $file) {
        $content = file_get_contents($file);

        if (preg_match('/Schema::create\(\'([^\']+)\'/', $content, $matches)) {
            $table = $matches[1];

            // اسم الموديل
            $modelName = Str::studly(Str::singular($table));

            // لو الميجريشن جوه tenants folder نضيف namespace Tenant
            if (Str::contains($folder, 'tenants')) {
                $modelName = "Tenant\\$modelName";
            }

            $draft['models'][$modelName] = [
                'table' => $table,
                'fillable' => ['*'], // ممكن تعدل بعدين
            ];
        }
    }
}

// نحفظ draft.yaml
file_put_contents(__DIR__ . '/draft.yaml', Yaml::dump($draft, 4, 2, Yaml::DUMP_OBJECT_AS_MAP));

echo "✅ draft.yaml generated successfully!\n";
