<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('songs', static function (Blueprint $table): void {
            $table->unsignedInteger('price_cents')->nullable();
        });

        Schema::table('albums', static function (Blueprint $table): void {
            $table->unsignedInteger('price_cents')->nullable();
        });

        Schema::table('artists', static function (Blueprint $table): void {
            $table->unsignedInteger('seller_id')->nullable();
            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('orders', static function (Blueprint $table): void {
            $table->string('id', 26)->primary();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('status', 16)->index();
            $table->string('provider', 16);
            $table->string('provider_reference')->nullable()->index();
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('order_items', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('order_id', 26);
            $table->string('purchasable_type');
            $table->string('purchasable_id', 36);
            $table->unsignedInteger('seller_id')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->unsignedInteger('commission_cents');
            $table->unsignedInteger('seller_earning_cents');
            $table->timestamps();

            $table->index(['purchasable_type', 'purchasable_id']);
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('purchases', static function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('purchasable_type');
            $table->string('purchasable_id', 36);
            $table->string('order_id', 26)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'purchasable_type', 'purchasable_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('payouts', static function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('seller_id')->nullable();
            $table->unsignedInteger('amount_cents');
            $table->string('note')->nullable();
            $table->timestamp('paid_at');
            $table->unsignedInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });
    }
};
