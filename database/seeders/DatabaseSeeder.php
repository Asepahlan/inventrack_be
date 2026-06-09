<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Staff Gudang',
            'username' => 'staff',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        // 2. Data Kategori
        $kategoriMap = [];
        $kategoriNames = ['Elektronik', 'ATK', 'Furnitur'];
        foreach ($kategoriNames as $name) {
            $kategori = Kategori::create(['nama_kategori' => $name]);
            $kategoriMap[$name] = $kategori->id;
        }

        // 3. Data Barang (Stok disesuaikan dengan rekap Masuk - Keluar)
        $barangs = [
            ['nama_barang' => 'Laptop Asus ROG', 'kategori' => 'Elektronik', 'stok' => 15, 'satuan' => 'Unit', 'harga_satuan' => 15000000],
            ['nama_barang' => 'Mouse Logitech Wireless', 'kategori' => 'Elektronik', 'stok' => 40, 'satuan' => 'Pcs', 'harga_satuan' => 250000],
            ['nama_barang' => 'Kertas HVS A4 80gr', 'kategori' => 'ATK', 'stok' => 90, 'satuan' => 'Rim', 'harga_satuan' => 50000],
            ['nama_barang' => 'Pulpen Faster', 'kategori' => 'ATK', 'stok' => 180, 'satuan' => 'Pack', 'harga_satuan' => 30000],
            ['nama_barang' => 'Proyektor Epson', 'kategori' => 'Elektronik', 'stok' => 5, 'satuan' => 'Unit', 'harga_satuan' => 8000000],
            ['nama_barang' => 'Kursi Kerja Ergostabil', 'kategori' => 'Furnitur', 'stok' => 20, 'satuan' => 'Unit', 'harga_satuan' => 1200000],
        ];

        foreach ($barangs as $barang) {
            Barang::create([
                'nama_barang' => $barang['nama_barang'],
                'kategori_id' => $kategoriMap[$barang['kategori']],
                'stok' => $barang['stok'],
                'satuan' => $barang['satuan'],
                'harga_satuan' => $barang['harga_satuan'],
            ]);
        }

        // 4. Riwayat Barang Masuk
        $barangMasuks = [
            ['barang_id' => 1, 'tanggal' => '2026-03-01', 'jumlah' => 15],
            ['barang_id' => 2, 'tanggal' => '2026-03-02', 'jumlah' => 50],
            ['barang_id' => 3, 'tanggal' => '2026-03-03', 'jumlah' => 100],
            ['barang_id' => 4, 'tanggal' => '2026-03-04', 'jumlah' => 200],
            ['barang_id' => 5, 'tanggal' => '2026-03-10', 'jumlah' => 5],
            ['barang_id' => 6, 'tanggal' => '2026-03-15', 'jumlah' => 20],
        ];

        foreach ($barangMasuks as $bm) {
            BarangMasuk::create($bm);
        }

        // 5. Riwayat Barang Keluar
        $barangKeluars = [
            ['barang_id' => 2, 'tanggal' => '2026-04-01', 'jumlah' => 10], // Stok sisa: 50 - 10 = 40
            ['barang_id' => 3, 'tanggal' => '2026-04-02', 'jumlah' => 10], // Stok sisa: 100 - 10 = 90
            ['barang_id' => 4, 'tanggal' => '2026-04-05', 'jumlah' => 20], // Stok sisa: 200 - 20 = 180
        ];

        foreach ($barangKeluars as $bk) {
            BarangKeluar::create($bk);
        }
    }
}
