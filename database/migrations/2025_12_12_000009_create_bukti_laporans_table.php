use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuktiLaporansTable extends Migration
{
    public function up()
    {
        Schema::create('bukti_laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_tugas_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('kategori');
            $table->string('file_path');
            $table->string('file_type');
            $table->string('keterangan')->nullable();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bukti_laporans');
    }
}
