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
        
        return response()->json([
            'success' => true,
            'data' => compact('barangKeluars', 'barangs')
        ]);
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
            return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi! Stok saat ini: ' . $barang->stok], 400);
        }

        // Create transaction
        BarangKeluar::create($request->all());

        // Update Stok
        $barang->stok -= $request->jumlah;
        $barang->save();

        return response()->json(['success' => true, 'message' => 'Transaksi barang keluar berhasil dicatat.'], 201);
    }

    public function destroy(BarangKeluar $barang_keluar)
    {
        // Revert stok
        $barang = $barang_keluar->barang;
        $barang->stok += $barang_keluar->jumlah;
        $barang->save();

        $barang_keluar->delete();
        
        return response()->json(['success' => true, 'message' => 'Transaksi barang keluar berhasil dibatalkan.']);
    }
}
