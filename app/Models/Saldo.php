<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;

class Saldo extends Model
{
    use HasFactory;

    protected $table = 'saldos';

    protected $fillable = [
        'nominal',
        'keterangan',
        'tanggal',
    ];

    public static function getRunningSaldo()
    {
        $totalTopup = self::sum('nominal') ?? 0;
        
        $totalMasuk = BarangMasuk::join('barangs', 'barang_masuks.barang_id', '=', 'barangs.id')
            ->selectRaw('SUM(barang_masuks.jumlah * barangs.harga_satuan) as total')
            ->value('total') ?? 0;
            
        $totalKeluar = BarangKeluar::join('barangs', 'barang_keluars.barang_id', '=', 'barangs.id')
            ->selectRaw('SUM(barang_keluars.jumlah * barangs.harga_satuan) as total')
            ->value('total') ?? 0;

        return $totalTopup + $totalKeluar - $totalMasuk;
    }
}
