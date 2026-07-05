<?php

namespace Database\Seeders;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Categories
        $kategoriBebas = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Obat Bebas'],
            ['deskripsi' => 'Obat bebas yang dapat dibeli tanpa resep dokter (lingkaran hijau).']
        );

        $kategoriBebasTerbatas = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Obat Bebas Terbatas'],
            ['deskripsi' => 'Obat bebas terbatas yang dapat dibeli tanpa resep dokter tetapi memiliki tanda peringatan (lingkaran biru).']
        );

        $kategoriKeras = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Obat Keras'],
            ['deskripsi' => 'Obat keras yang harus dibeli dengan resep dokter (lingkaran merah/huruf K).']
        );

        $kategoriPsikotropika = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Obat Psikotropika'],
            ['deskripsi' => 'Obat golongan psikotropika yang mempengaruhi aktivitas psikis.']
        );

        $kategoriNarkotika = KategoriObat::firstOrCreate(
            ['nama_kategori' => 'Obat Narkotika'],
            ['deskripsi' => 'Obat golongan narkotika yang penggunaannya sangat diawasi ketat.']
        );

        // 2. Seed Suppliers
        $supplier1 = Supplier::firstOrCreate(
            ['nama_supplier' => 'PT. Kimia Farma Trading'],
            [
                'alamat' => 'Jl. Budi Utomo No. 1, Jakarta Pusat',
                'no_telp' => '0213841234',
                'email' => 'info@kimiafarma.co.id'
            ]
        );

        $supplier2 = Supplier::firstOrCreate(
            ['nama_supplier' => 'PT. Bina San Prima'],
            [
                'alamat' => 'Jl. Pahlawan No. 45, Bandung',
                'no_telp' => '0227301122',
                'email' => 'sales@binasanprima.com'
            ]
        );

        $supplier3 = Supplier::firstOrCreate(
            ['nama_supplier' => 'PT. Anugrah Argon Medica'],
            [
                'alamat' => 'Kawasan Industri Jababeka, Bekasi',
                'no_telp' => '0218981234',
                'email' => 'contact@anugrahargon.com'
            ]
        );

        // 3. Seed Medicines (Obat)
        // Normal Safe Stock Medicine
        Obat::firstOrCreate(
            ['barcode' => '8999999000123'],
            [
                'kategori_obat_id' => $kategoriBebas->id,
                'nama_obat' => 'Paracetamol 500mg',
                'harga_beli' => 8000,
                'harga_jual' => 10000,
                'stok' => 120,
                'stok_minimum' => 20,
                'tanggal_kedaluwarsa' => Carbon::now()->addYear(),
            ]
        );

        // Normal Prescription Medicine
        Obat::firstOrCreate(
            ['barcode' => '8999999000456'],
            [
                'kategori_obat_id' => $kategoriKeras->id,
                'nama_obat' => 'Amoxicillin 500mg',
                'harga_beli' => 12000,
                'harga_jual' => 15000,
                'stok' => 80,
                'stok_minimum' => 15,
                'tanggal_kedaluwarsa' => Carbon::now()->addMonths(18),
            ]
        );

        // Almost Low Stock Medicine
        Obat::firstOrCreate(
            ['barcode' => '8999999000789'],
            [
                'kategori_obat_id' => $kategoriBebasTerbatas->id,
                'nama_obat' => 'OBH Tropica Syrup 100ml',
                'harga_beli' => 15000,
                'harga_jual' => 18500,
                'stok' => 12,
                'stok_minimum' => 15,
                'tanggal_kedaluwarsa' => Carbon::now()->addMonths(6),
            ]
        );

        // Low Stock AND Near Expiry Medicine (to trigger dashboard widgets)
        Obat::firstOrCreate(
            ['barcode' => '8999999001012'],
            [
                'kategori_obat_id' => $kategoriBebasTerbatas->id,
                'nama_obat' => 'Decolgen Tablet Box',
                'harga_beli' => 42000,
                'harga_jual' => 50000,
                'stok' => 4,
                'stok_minimum' => 10,
                'tanggal_kedaluwarsa' => Carbon::now()->addDays(15),
            ]
        );

        // Psychotropic drug
        Obat::firstOrCreate(
            ['barcode' => '8999999002022'],
            [
                'kategori_obat_id' => $kategoriPsikotropika->id,
                'nama_obat' => 'Alprazolam 0.5mg',
                'harga_beli' => 25000,
                'harga_jual' => 35000,
                'stok' => 45,
                'stok_minimum' => 5,
                'tanggal_kedaluwarsa' => Carbon::now()->addYears(2),
            ]
        );
    }
}
