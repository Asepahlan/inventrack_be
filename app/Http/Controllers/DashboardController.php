<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBarang = Barang::count();
        $stokMenipis = Barang::where('stok', '<=', 5)->count();
        $totalMasuk = BarangMasuk::whereMonth('tanggal', date('m'))->sum('jumlah');
        $totalKeluar = BarangKeluar::whereMonth('tanggal', date('m'))->sum('jumlah');

        // Total Nominal Keuangan (Rp) Bulan Ini
        $totalMasukNominal = BarangMasuk::whereMonth('barang_masuks.tanggal', date('m'))
            ->join('barangs', 'barang_masuks.barang_id', '=', 'barangs.id')
            ->selectRaw('SUM(barang_masuks.jumlah * barangs.harga_satuan) as total')
            ->value('total') ?? 0;

        $totalKeluarNominal = BarangKeluar::whereMonth('barang_keluars.tanggal', date('m'))
            ->join('barangs', 'barang_keluars.barang_id', '=', 'barangs.id')
            ->selectRaw('SUM(barang_keluars.jumlah * barangs.harga_satuan) as total')
            ->value('total') ?? 0;

        $barangsMenipis = Barang::where('stok', '<=', 5)->get();

        // Data for Chart (Daily in current month) - 7 latest transaction days
        $chartMasuk = BarangMasuk::selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)->get()->reverse()->values();

        $chartKeluar = BarangKeluar::selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)->get()->reverse()->values();

        return view('dashboard', compact(
            'totalBarang', 'stokMenipis', 'totalMasuk', 'totalKeluar',
            'totalMasukNominal', 'totalKeluarNominal',
            'barangsMenipis', 'chartMasuk', 'chartKeluar'
        ));
    }
}
