<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangMasuk::with('barang');
        
        if ($request->has('search')) {
            $query->whereHas('barang', function($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%');
            });
        }
        
        $barangs = Barang::all();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $barangMasuks = $query->latest()->paginate(10)->withQueryString();
        
        return view('barang_masuk.index', compact('barangMasuks', 'barangs', 'kategoris'));
    }

    public function store(Request $request)
    {
        // Jika sedang menginput barang baru (bukan memilih dari daftar)
        if ($request->is_new_barang) {
            $request->validate([
                'nama_barang' => 'required|string|max:255|unique:barangs,nama_barang',
                'kategori_id' => 'required|exists:kategoris,id',
                'satuan' => 'required|string|max:50',
                'harga_satuan' => 'required|numeric|min:0',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer|min:1',
            ]);

            $barang = Barang::create([
                'nama_barang' => $request->nama_barang,
                'kategori_id' => $request->kategori_id,
                'satuan' => $request->satuan,
                'harga_satuan' => $request->harga_satuan,
                'stok' => 0, // Akan ditambah di bawah
            ]);

            $barang_id = $barang->id;
        } else {
            $request->validate([
                'barang_id' => 'required|exists:barangs,id',
                'tanggal' => 'required|date',
                'jumlah' => 'required|integer|min:1',
            ]);

            $barang_id = $request->barang_id;
            $barang = Barang::find($barang_id);
        }

        // Create transaction
        BarangMasuk::create([
            'barang_id' => $barang_id,
            'tanggal' => $request->tanggal,
            'jumlah' => $request->jumlah
        ]);

        // Update Stok
        $barang->stok += $request->jumlah;
        $barang->save();

        return redirect()->route('barang-masuk.index')->with('success', 'Transaksi barang masuk berhasil ditambahkan.');
    }

    public function destroy(BarangMasuk $barang_masuk)
    {
        // Revert stok
        $barang = $barang_masuk->barang;
        $barang->stok -= $barang_masuk->jumlah;
        $barang->save();

        $barang_masuk->delete();
        
        return redirect()->route('barang-masuk.index')->with('success', 'Transaksi barang masuk berhasil dibatalkan.');
    }
}
