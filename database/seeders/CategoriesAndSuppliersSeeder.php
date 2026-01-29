<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class CategoriesAndSuppliersSeeder extends Seeder
{
	public function run(): void
	{
		// Категории на азербайджанском языке
		$categories = [
			['name' => 'Elektronika', 'description' => 'Elektronik avadanlıqlar və cihazlar'],
			['name' => 'Məişət texnikası', 'description' => 'Məişət üçün lazım olan texniki avadanlıqlar'],
			['name' => 'Mebel', 'description' => 'Ev və ofis üçün mebel'],
			['name' => 'Geyim', 'description' => 'Kişi, qadın və uşaq geyimləri'],
			['name' => 'Ayaqqabı', 'description' => 'Müxtəlif növ ayaqqabılar'],
			['name' => 'Kosmetika', 'description' => 'Gözəllik və sağlamlıq məhsulları'],
			['name' => 'Qida məhsulları', 'description' => 'Müxtəlif qida məhsulları'],
			['name' => 'İdman məhsulları', 'description' => 'İdman avadanlıqları və aksesuarlar'],
			['name' => 'Kitablar', 'description' => 'Müxtəlif janrlarda kitablar'],
			['name' => 'Oyuncaqlar', 'description' => 'Uşaqlar üçün oyuncaqlar'],
		];

		foreach ($categories as $category) {
			ProductCategory::query()->firstOrCreate(
				['name' => $category['name']],
				['description' => $category['description']]
			);
		}

		// Поставщики на азербайджанском языке
		$suppliers = [
			['name' => 'Bakı Ticarət Mərkəzi', 'contact_name' => 'Əli Vəliyev', 'phone' => '+994-50-123-45-67', 'email' => 'ali.veliyev@baki.az', 'website' => 'www.bakiticaret.az', 'comment' => 'Ən böyük yerli təchizatçı'],
			['name' => 'Tech Electronics', 'contact_name' => 'Gülnar Həsənova', 'phone' => '+994-70-234-56-78', 'email' => 'gulnar.hasanova@techelec.com', 'website' => 'www.techelec.az', 'comment' => 'Elektronika üzrə ixtisaslaşmış'],
			['name' => 'Moda Evi', 'contact_name' => 'Fərid Əhmədov', 'phone' => '+994-55-345-67-89', 'email' => 'ferid.ahmadov@modaevi.az', 'website' => 'www.modaevi.az', 'comment' => 'Geyim və aksesuarlar'],
			['name' => 'Kitab Dünyası', 'contact_name' => 'Leyla Kərimova', 'phone' => '+994-12-456-78-90', 'email' => 'leyla.kerimova@kitab.az', 'website' => 'www.kitabdunyasi.az', 'comment' => 'Kitab təchizatçısı'],
			['name' => 'Qida Səbəti', 'contact_name' => 'Murad İsmayılov', 'phone' => '+994-51-567-89-01', 'email' => 'murad.ismayilov@qidas.az', 'website' => 'www.qidasebeti.az', 'comment' => 'Təzə qida məhsulları'],
			['name' => 'AzSport Market', 'contact_name' => 'Elvin Məmmədov', 'phone' => '+994-99-678-90-12', 'email' => 'elvin.mammadov@azsport.az', 'website' => 'www.azsport.az', 'comment' => 'İdman ləvazimatları'],
			['name' => 'Ev Mebeli', 'contact_name' => 'Zəhra Nuriyeva', 'phone' => '+994-77-789-01-23', 'email' => 'zahra.nuriyeva@evmebeli.az', 'website' => 'www.evmebeli.az', 'comment' => 'Keyfiyyətli mebel təchizatı'],
			['name' => 'Uşaq Dünyası', 'contact_name' => 'Nigar Quliyeva', 'phone' => '+994-50-890-12-34', 'email' => 'nigar.quliyeva@ushaq.az', 'website' => 'www.ushaqdunyasi.az', 'comment' => 'Uşaq oyuncaqları və geyimləri'],
		];

		foreach ($suppliers as $supplier) {
			Supplier::query()->firstOrCreate(
				['name' => $supplier['name']],
				$supplier
			);
		}
	}
}
