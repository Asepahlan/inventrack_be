<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        \Carbon\Carbon::setLocale('id');
        $bulan = (int)$request->input('bulan', date('m'));
        $tahun = (int)$request->input('tahun', date('Y'));
        
        $barangMasuks = BarangMasuk::with('barang')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->orderBy('tanggal', 'asc')
                            ->get();

        $barangKeluars = BarangKeluar::with('barang')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->orderBy('tanggal', 'asc')
                            ->get();

        return view('laporan.index', compact('barangMasuks', 'barangKeluars', 'bulan', 'tahun'));
    }

    public function print(Request $request)
    {
        \Carbon\Carbon::setLocale('id');
        $bulan = (int)$request->input('bulan', date('m'));
        $tahun = (int)$request->input('tahun', date('Y'));
        
        $barangMasuks = BarangMasuk::with('barang')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->orderBy('tanggal', 'asc')
                            ->get();

        $barangKeluars = BarangKeluar::with('barang')
                            ->whereMonth('tanggal', $bulan)
                            ->whereYear('tanggal', $tahun)
                            ->orderBy('tanggal', 'asc')
                            ->get();

        return view('laporan.print', compact('barangMasuks', 'barangKeluars', 'bulan', 'tahun'));
    }
}
