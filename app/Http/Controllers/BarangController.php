<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::with('kategori');
        if ($request->has('search')) {
            $query->where('nama_barang', 'like', '%' . $request->search . '%')
                  ->orWhereHas('kategori', function($q) use ($request) {
                      $q->where('nama_kategori', 'like', '%' . $request->search . '%');
                  });
        }

        $barangs = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return response()->json([
            'success' => true,
            'data' => [
                'barangs' => $barangs,
                'kategoris' => $kategoris
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        Barang::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil ditambahkan.'
        ], 201);
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        $barang->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil diupdate.'
        ]);
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();
        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dihapus.'
        ]);
    }
}
