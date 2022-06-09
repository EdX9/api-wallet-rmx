<?php

use App\Models\Wallet\Transaction;
use App\Models\Wallet\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table(), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('holder');
            $table->string('name');
            $table->string('slug')
                ->index()
            ;
            $table->uuid('uuid')
                ->unique()
            ;
            $table->string('description')
                ->nullable()
            ;
            $table->json('meta')
                ->nullable()
            ;
            $table->decimal('balance', 64, 0)
                ->default(0)
            ;
            $table->unsignedSmallInteger('decimal_places')
                ->default(2)
            ;
            $table->timestamps();

            $table->unique(['holder_type', 'holder_id', 'slug']);
        });

        Schema::table($this->transactionTable(), function (Blueprint $table) {
            $table->foreign('wallet_id')
                ->references('id')
                ->on($this->table())
                ->onDelete('cascade')
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::drop($this->table());
    }

    protected function table(): string
    {
        return (new Wallet())->getTable();
    }

    private function transactionTable(): string
    {
        return (new Transaction())->getTable();
    }
};