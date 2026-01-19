<?php

namespace Database\Seeders\Catalogue;

use App\Models\Catalogue\Brand;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');

            return;
        }

        $creator = $users->first();

        $brands = [
            [
                'name' => 'Apple',
                'description' => 'Apple Inc. is an American multinational technology company that designs, develops, and sells consumer electronics, computer software, and online services.',
                'website' => 'https://www.apple.com',
                'sort_order' => 1,
            ],
            [
                'name' => 'Samsung',
                'description' => 'Samsung Electronics is a South Korean multinational electronics corporation and the flagship subsidiary of Samsung Group.',
                'website' => 'https://www.samsung.com',
                'sort_order' => 2,
            ],
            [
                'name' => 'Sony',
                'description' => 'Sony Corporation is a Japanese multinational conglomerate corporation that designs, develops, and sells consumer and professional electronics.',
                'website' => 'https://www.sony.com',
                'sort_order' => 3,
            ],
            [
                'name' => 'Dell',
                'description' => 'Dell Technologies Inc. is an American multinational technology company that develops, sells, repairs, and supports computers and related products and services.',
                'website' => 'https://www.dell.com',
                'sort_order' => 4,
            ],
            [
                'name' => 'HP',
                'description' => 'HP Inc. is an American multinational information technology company that develops personal computers, printers, and related supplies.',
                'website' => 'https://www.hp.com',
                'sort_order' => 5,
            ],
            [
                'name' => 'Lenovo',
                'description' => 'Lenovo Group Limited is a Chinese multinational technology company that designs, develops, manufactures, and sells personal computers, tablets, smartphones, and more.',
                'website' => 'https://www.lenovo.com',
                'sort_order' => 6,
            ],
            [
                'name' => 'Microsoft',
                'description' => 'Microsoft Corporation is an American multinational technology corporation that produces computer software, consumer electronics, and personal computers.',
                'website' => 'https://www.microsoft.com',
                'sort_order' => 7,
            ],
            [
                'name' => 'Google',
                'description' => 'Google LLC is an American multinational technology company that specializes in Internet-related services and products.',
                'website' => 'https://www.google.com',
                'sort_order' => 8,
            ],
            [
                'name' => 'OnePlus',
                'description' => 'OnePlus Technology is a Chinese smartphone manufacturer founded in 2013, known for producing high-quality Android smartphones.',
                'website' => 'https://www.oneplus.com',
                'sort_order' => 9,
            ],
            [
                'name' => 'Xiaomi',
                'description' => 'Xiaomi Corporation is a Chinese electronics company that designs, develops, and sells smartphones, mobile apps, laptops, and related consumer electronics.',
                'website' => 'https://www.mi.com',
                'sort_order' => 10,
            ],
            [
                'name' => 'LG',
                'description' => 'LG Electronics is a South Korean multinational electronics company that manufactures home appliances and consumer electronics.',
                'website' => 'https://www.lg.com',
                'sort_order' => 11,
            ],
            [
                'name' => 'ASUS',
                'description' => 'ASUS is a Taiwanese multinational computer and phone hardware and electronics company that specializes in designing and manufacturing laptops, desktops, and mobile devices.',
                'website' => 'https://www.asus.com',
                'sort_order' => 12,
            ],
            [
                'name' => 'Acer',
                'description' => 'Acer Inc. is a Taiwanese multinational hardware and electronics corporation that specializes in advanced electronics technology.',
                'website' => 'https://www.acer.com',
                'sort_order' => 13,
            ],
            [
                'name' => 'Bose',
                'description' => 'Bose Corporation is an American manufacturing company that predominantly sells audio equipment.',
                'website' => 'https://www.bose.com',
                'sort_order' => 14,
            ],
            [
                'name' => 'JBL',
                'description' => 'JBL is an American audio equipment manufacturer that produces loudspeakers and associated electronics.',
                'website' => 'https://www.jbl.com',
                'sort_order' => 15,
            ],
            [
                'name' => 'Sennheiser',
                'description' => 'Sennheiser electronic GmbH & Co. KG is a German audio company that designs and produces professional audio equipment.',
                'website' => 'https://www.sennheiser.com',
                'sort_order' => 16,
            ],
            [
                'name' => 'Nintendo',
                'description' => 'Nintendo Co., Ltd. is a Japanese multinational video game company that develops and publishes video games and video game consoles.',
                'website' => 'https://www.nintendo.com',
                'sort_order' => 17,
            ],
            [
                'name' => 'PlayStation',
                'description' => 'PlayStation is a video game brand that consists of five home video game consoles, as well as a media center, an online service, and more.',
                'website' => 'https://www.playstation.com',
                'sort_order' => 18,
            ],
            [
                'name' => 'Xbox',
                'description' => 'Xbox is a video gaming brand created and owned by Microsoft, representing gaming consoles, games, and services.',
                'website' => 'https://www.xbox.com',
                'sort_order' => 19,
            ],
            [
                'name' => 'Razer',
                'description' => 'Razer Inc. is a Singaporean-American multinational technology company that designs, develops, and sells consumer electronics, financial services, and gaming hardware.',
                'website' => 'https://www.razer.com',
                'sort_order' => 20,
            ],
        ];

        foreach ($brands as $brandData) {
            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($brandData['name'])],
                [
                    'name' => $brandData['name'],
                    'description' => $brandData['description'],
                    'website' => $brandData['website'],
                    'creator_id' => $creator->id,
                    'sort_order' => $brandData['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
