<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::query();
        if ($request->has('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategoris = $query->withCount('barangs')->latest()->paginate(10)->withQueryString();
        return response()->json([
            'success' => true,
            'data' => [
                'kategoris' => $kategoris
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        Kategori::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.'
        ], 201);
    }

    public function update(Request $request, Kategori $kategori)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diupdate.'
        ]);
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->barangs()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh data barang.'
            ], 400);
        }

        $kategori->delete();
        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.'
        ]);
    }
}
