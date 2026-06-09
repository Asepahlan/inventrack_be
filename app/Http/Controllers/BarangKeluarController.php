<?php

namespace App\Http\Controllers;

use App\Models\BarangKeluar;
use App\Models\Barang;
use Illuminate\Http\Request;

class BarangKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangKeluar::with('barang');
        
        if ($request->has('search')) {
            $query->whereHas('barang', function($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%');
            });
        }
        
        // Hanya tampilkan barang yang punya stok
        $barangs = Barang::where('stok', '>', 0)->get();
        $barangKeluars = $query->latest()->paginate(10)->withQueryString();
        
        return view('barang_keluar.index', compact('barangKeluars', 'barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
        ]);

        $barang = Barang::find($request->barang_id);

        if ($request->jumlah > $barang->stok) {
            return back()->withErrors(['jumlah' => 'Stok tidak mencukupi! Stok saat ini: ' . $barang->stok])->withInput();
        }

        // Create transaction
        BarangKeluar::create($request->all());

        // Update Stok
        $barang->stok -= $request->jumlah;
        $barang->save();

        return redirect()->route('barang-keluar.index')->with('success', 'Transaksi barang keluar berhasil dicatat.');
    }

    public function destroy(BarangKeluar $barang_keluar)
    {
        // Revert stok
        $barang = $barang_keluar->barang;
        $barang->stok += $barang_keluar->jumlah;
        $barang->save();

        $barang_keluar->delete();
        
        return redirect()->route('barang-keluar.index')->with('success', 'Transaksi barang keluar berhasil dibatalkan.');
    }
}
