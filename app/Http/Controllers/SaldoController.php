<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;

class SaldoController extends Controller
{
    public function index(Request $request)
    {
        // Hanya admin yang dapat mengelola saldo
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        // Fetch topups
        $topups = Saldo::orderBy('tanggal', 'desc')->get();

        // Fetch running balance
        $runningSaldo = Saldo::getRunningSaldo();

        // Fetch totals
        $totalTopup = Saldo::sum('nominal') ?? 0;
        $totalMasuk = BarangMasuk::join('barangs', 'barang_masuks.barang_id', '=', 'barangs.id')
            ->selectRaw('SUM(barang_masuks.jumlah * barangs.harga_satuan) as total')
            ->value('total') ?? 0;
        $totalKeluar = BarangKeluar::join('barangs', 'barang_keluars.barang_id', '=', 'barangs.id')
            ->selectRaw('SUM(barang_keluars.jumlah * barangs.harga_satuan) as total')
            ->value('total') ?? 0;

        // Combined ledger list
        $ledgerTopups = $topups->map(function($item) {
            return [
                'tanggal' => $item->tanggal,
                'tipe' => 'masuk_topup',
                'keterangan' => 'Top Up: ' . $item->keterangan,
                'nominal' => $item->nominal,
                'is_deletable' => true,
                'id' => $item->id,
            ];
        });

        $ledgerMasuk = BarangMasuk::with('barang')->get()->map(function($item) {
            return [
                'tanggal' => $item->tanggal,
                'tipe' => 'keluar_barang',
                'keterangan' => 'Pembelian: ' . ($item->barang->nama_barang ?? 'Barang') . ' (' . $item->jumlah . ' ' . ($item->barang->satuan ?? '') . ')',
                'nominal' => $item->jumlah * ($item->barang->harga_satuan ?? 0),
                'is_deletable' => false,
                'id' => $item->id,
            ];
        });

        $ledgerKeluar = BarangKeluar::with('barang')->get()->map(function($item) {
            return [
                'tanggal' => $item->tanggal,
                'tipe' => 'masuk_barang',
                'keterangan' => 'Penjualan: ' . ($item->barang->nama_barang ?? 'Barang') . ' (' . $item->jumlah . ' ' . ($item->barang->satuan ?? '') . ')',
                'nominal' => $item->jumlah * ($item->barang->harga_satuan ?? 0),
                'is_deletable' => false,
                'id' => $item->id,
            ];
        });

        $ledger = collect()
            ->merge($ledgerTopups)
            ->merge($ledgerMasuk)
            ->merge($ledgerKeluar)
            ->sortByDesc('tanggal')
            ->values();

        return view('saldo.index', compact('topups', 'runningSaldo', 'totalTopup', 'totalMasuk', 'totalKeluar', 'ledger'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string|max:255',
            'tanggal' => 'required|date',
        ]);

        Saldo::create($request->all());

        return redirect()->route('saldo.index')->with('success', 'Saldo berhasil didepositkan.');
    }

    public function destroy(Saldo $saldo)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $saldo->delete();

        return redirect()->route('saldo.index')->with('success', 'Transaksi top up saldo berhasil dihapus.');
    }
}
