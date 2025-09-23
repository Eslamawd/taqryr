<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         $countries = [
            ['code' => 'EG', 'name' => 'Egypt', 'name_ar' => 'مصر'],
            ['code' => 'SA', 'name' => 'Saudi Arabia', 'name_ar' => 'السعودية'],
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'name_ar' => 'الإمارات'],
            ['code' => 'QA', 'name' => 'Qatar', 'name_ar' => 'قطر'],
            ['code' => 'KW', 'name' => 'Kuwait', 'name_ar' => 'الكويت'],
            ['code' => 'BH', 'name' => 'Bahrain', 'name_ar' => 'البحرين'],
            ['code' => 'OM', 'name' => 'Oman', 'name_ar' => 'عُمان'],
            ['code' => 'YE', 'name' => 'Yemen', 'name_ar' => 'اليمن'],
            ['code' => 'JO', 'name' => 'Jordan', 'name_ar' => 'الأردن'],
            ['code' => 'LB', 'name' => 'Lebanon', 'name_ar' => 'لبنان'],
            ['code' => 'SY', 'name' => 'Syria', 'name_ar' => 'سوريا'],
            ['code' => 'IQ', 'name' => 'Iraq', 'name_ar' => 'العراق'],
            ['code' => 'MA', 'name' => 'Morocco', 'name_ar' => 'المغرب'],
            ['code' => 'DZ', 'name' => 'Algeria', 'name_ar' => 'الجزائر'],
            ['code' => 'TN', 'name' => 'Tunisia', 'name_ar' => 'تونس'],
            ['code' => 'LY', 'name' => 'Libya', 'name_ar' => 'ليبيا'],
            ['code' => 'SD', 'name' => 'Sudan', 'name_ar' => 'السودان'],
            ['code' => 'PS', 'name' => 'Palestine', 'name_ar' => 'فلسطين'],

            ['code' => 'US', 'name' => 'United States', 'name_ar' => 'الولايات المتحدة'],
            ['code' => 'CA', 'name' => 'Canada', 'name_ar' => 'كندا'],
            ['code' => 'UK', 'name' => 'United Kingdom', 'name_ar' => 'المملكة المتحدة'],
            ['code' => 'FR', 'name' => 'France', 'name_ar' => 'فرنسا'],
            ['code' => 'DE', 'name' => 'Germany', 'name_ar' => 'ألمانيا'],
            ['code' => 'IT', 'name' => 'Italy', 'name_ar' => 'إيطاليا'],
            ['code' => 'ES', 'name' => 'Spain', 'name_ar' => 'إسبانيا'],
            ['code' => 'RU', 'name' => 'Russia', 'name_ar' => 'روسيا'],
            ['code' => 'TR', 'name' => 'Turkey', 'name_ar' => 'تركيا'],
            ['code' => 'IN', 'name' => 'India', 'name_ar' => 'الهند'],
            ['code' => 'CN', 'name' => 'China', 'name_ar' => 'الصين'],
            ['code' => 'JP', 'name' => 'Japan', 'name_ar' => 'اليابان'],
            ['code' => 'KR', 'name' => 'South Korea', 'name_ar' => 'كوريا الجنوبية'],
            ['code' => 'BR', 'name' => 'Brazil', 'name_ar' => 'البرازيل'],
            ['code' => 'AR', 'name' => 'Argentina', 'name_ar' => 'الأرجنتين'],
            ['code' => 'MX', 'name' => 'Mexico', 'name_ar' => 'المكسيك'],
            ['code' => 'ZA', 'name' => 'South Africa', 'name_ar' => 'جنوب أفريقيا'],
            ['code' => 'NG', 'name' => 'Nigeria', 'name_ar' => 'نيجيريا'],
            ['code' => 'AU', 'name' => 'Australia', 'name_ar' => 'أستراليا'],
            ['code' => 'NZ', 'name' => 'New Zealand', 'name_ar' => 'نيوزيلندا'],
            ['code' => 'SE', 'name' => 'Sweden', 'name_ar' => 'السويد'],
        ];

        DB::table('countries')->insert($countries);
    }
}
