namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuktiLaporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'surat_tugas_id',
        'user_id',
        'kategori',
        'file_path',
        'file_type',
        'keterangan',
        'nominal',
    ];

    public function laporan()
    {
        return $this->belongsTo(SuratTugas::class, 'surat_tugas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
