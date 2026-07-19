<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    /**
     * Seed gejala diagnosis untuk wizard.
     */
    public function run(): void
    {
        $motorcycleSymptoms = [
            ['label' => 'Mesin tidak menyala', 'description' => 'Mesin completely mati, starter tidak berfungsi', 'icon' => 'power-outline', 'category' => 'engine', 'sort_order' => 1],
            ['label' => 'Mesin mati mendadak', 'description' => 'Mesin tiba-tiba mati saat dikendarai', 'icon' => 'alert-outline', 'category' => 'engine', 'sort_order' => 2],
            ['label' => 'Suara mesin tidak normal', 'description' => 'Mesin berisik, bergetar berlebihan', 'icon' => 'volume-high-outline', 'category' => 'engine', 'sort_order' => 3],
            ['label' => 'Mesin pincang', 'description' => 'Mesin tidak stabil, langsam tidak teratur', 'icon' => 'speedometer-outline', 'category' => 'engine', 'sort_order' => 4],
            ['label' => 'Overheat / Panas berlebih', 'description' => 'Temperatur mesin naik, air radiator habis', 'icon' => 'thermometer-outline', 'category' => 'engine', 'sort_order' => 5],
            ['label' => 'Rem tidak berfungsi', 'description' => 'Rem blong, tidak bisa menghentikan kendaraan', 'icon' => 'ban-outline', 'category' => 'brake', 'sort_order' => 6],
            ['label' => 'Rem berbunyi', 'description' => 'Suara berdecit atau berderit saat rem ditekan', 'icon' => 'volume-medium-outline', 'category' => 'brake', 'sort_order' => 7],
            ['label' => 'Ban bocor', 'description' => 'Ban kempes atau bocor', 'icon' => 'ellipse-outline', 'category' => 'tire', 'sort_order' => 8],
            ['label' => 'Ban aus', 'description' => 'Tapak ban sudah tipis dan aus', 'icon' => 'ellipse-outline', 'category' => 'tire', 'sort_order' => 9],
            ['label' => 'Aki / Baterai soak', 'description' => 'Aki tidak bisa menyalakan lampu atau starter', 'icon' => 'flash-outline', 'category' => 'electrical', 'sort_order' => 10],
            ['label' => 'Lampu mati', 'description' => 'Lampu utama, sein, atau lampu belakang mati', 'icon' => 'bulb-outline', 'category' => 'electrical', 'sort_order' => 11],
            ['label' => 'Kelistrikan konslet', 'description' => 'Spul putus, fuse sering putus', 'icon' => 'flash-outline', 'category' => 'electrical', 'sort_order' => 12],
            ['label' => 'Oli bocor', 'description' => 'Kebocoran oli mesin atau gardan', 'icon' => 'water-outline', 'category' => 'engine', 'sort_order' => 13],
            ['label' => 'Rantai longgar', 'description' => 'Rantai berisik, loncat, atau putus', 'icon' => 'link-outline', 'category' => 'general', 'sort_order' => 14],
            ['label' => 'Kopling aus', 'description' => 'Kopling selip, tarikan berat', 'icon' => 'settings-outline', 'category' => 'general', 'sort_order' => 15],
            ['label' => 'CVT bermasalah', 'description' => 'Tarikan enteng tapi kecepatan tidak naik (matic)', 'icon' => 'settings-outline', 'category' => 'general', 'sort_order' => 16],
            ['label' => 'Sparepart perlu diganti', 'description' => 'Komponen tertentu sudah aus dan perlu diganti', 'icon' => 'construct-outline', 'category' => 'sparepart', 'sort_order' => 17],
            ['label' => 'Lainnya', 'description' => 'Masalah lain yang tidak tercantum di atas', 'icon' => 'ellipsis-horizontal-outline', 'category' => 'general', 'sort_order' => 18],
        ];

        $carSymptoms = [
            ['label' => 'Mesin tidak menyala', 'description' => 'Starter tidak berfungsi, mesin completely mati', 'icon' => 'power-outline', 'category' => 'engine', 'sort_order' => 1],
            ['label' => 'Mesin mati mendadak', 'description' => 'Mesin tiba-tiba mati saat dikendarai', 'icon' => 'alert-outline', 'category' => 'engine', 'sort_order' => 2],
            ['label' => 'Mesin pincang', 'description' => 'Mesin tidak stabil, langsam tidak teratur', 'icon' => 'speedometer-outline', 'category' => 'engine', 'sort_order' => 3],
            ['label' => 'Overheat / Panas berlebih', 'description' => 'Temperatur mesin naik drastis', 'icon' => 'thermometer-outline', 'category' => 'engine', 'sort_order' => 4],
            ['label' => 'Transmisi bermasalah', 'description' => 'Perpindahan gigi tersentak, tidak masuk gigi', 'icon' => 'settings-outline', 'category' => 'engine', 'sort_order' => 5],
            ['label' => 'Rem tidak berfungsi', 'description' => 'Rem blong, pedal rem lunak', 'icon' => 'ban-outline', 'category' => 'brake', 'sort_order' => 6],
            ['label' => 'Rem bergetar', 'description' => 'Getaran saat rem ditekan', 'icon' => 'phone-portrait-outline', 'category' => 'brake', 'sort_order' => 7],
            ['label' => 'Ban bocor', 'description' => 'Ban kempes atau bocor', 'icon' => 'ellipse-outline', 'category' => 'tire', 'sort_order' => 8],
            ['label' => 'Ban aus', 'description' => 'Tapak ban sudah tipis', 'icon' => 'ellipse-outline', 'category' => 'tire', 'sort_order' => 9],
            ['label' => 'Aki / Baterai soak', 'description' => 'Aki tidak bisa menyalakan mesin', 'icon' => 'flash-outline', 'category' => 'electrical', 'sort_order' => 10],
            ['label' => 'Lampu mati', 'description' => 'Lampu utama, rem, atau sein mati', 'icon' => 'bulb-outline', 'category' => 'electrical', 'sort_order' => 11],
            ['label' => 'Kelistrikan konslet', 'description' => 'Fuse sering putus, lampu berkedip', 'icon' => 'flash-outline', 'category' => 'electrical', 'sort_order' => 12],
            ['label' => 'AC tidak dingin', 'description' => 'AC tidak mengeluarkan udara dingin', 'icon' => 'snow-outline', 'category' => 'general', 'sort_order' => 13],
            ['label' => 'Oli bocor', 'description' => 'Kebocoran oli mesin', 'icon' => 'water-outline', 'category' => 'engine', 'sort_order' => 14],
            ['label' => 'Sparepart perlu diganti', 'description' => 'Komponen tertentu perlu diganti', 'icon' => 'construct-outline', 'category' => 'sparepart', 'sort_order' => 15],
            ['label' => 'Lainnya', 'description' => 'Masalah lain yang tidak tercantum di atas', 'icon' => 'ellipsis-horizontal-outline', 'category' => 'general', 'sort_order' => 16],
        ];

        foreach ($motorcycleSymptoms as $symptom) {
            Symptom::updateOrCreate(
                ['label' => $symptom['label'], 'vehicle_category' => 'motorcycle'],
                array_merge($symptom, ['is_active' => true]),
            );
        }

        foreach ($carSymptoms as $symptom) {
            Symptom::updateOrCreate(
                ['label' => $symptom['label'], 'vehicle_category' => 'car'],
                array_merge($symptom, ['is_active' => true]),
            );
        }
    }
}
