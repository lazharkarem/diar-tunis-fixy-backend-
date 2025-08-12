<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a temporary table with the new structure
        Schema::create('model_has_roles_new', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->primary(
                ['role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary'
            );

            $table->index(['model_id', 'model_type']);

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        // Copy data from the old table to the new one
        if (Schema::hasTable('model_has_roles')) {
            $records = [];
            $roles = DB::table('model_has_roles')->get();

            foreach ($roles as $role) {
                $records[] = [
                    'role_id' => $role->role_id,
                    'model_id' => $role->model_id,
                    'model_type' => 'App\\Models\\User'
                ];
            }

            if (!empty($records)) {
                DB::table('model_has_roles_new')->insert($records);
            }

            // Drop the old table and rename the new one
            Schema::drop('model_has_roles');
        }

        Schema::rename('model_has_roles_new', 'model_has_roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Create a temporary table with the old structure
        Schema::create('model_has_roles_temp', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('model_id');

            $table->primary(['role_id', 'model_id']);

            $table->index(['model_id']);

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        // Copy data from the current table to the old structure
        DB::table('model_has_roles')->orderBy('role_id')->orderBy('model_id')->chunk(100, function($roles) {
            $records = [];
            foreach ($roles as $role) {
                $records[] = [
                    'role_id' => $role->role_id,
                    'model_id' => $role->model_id
                ];
            }
            DB::table('model_has_roles_temp')->insert($records);
        });

        // Drop the current table and rename the old one back
        Schema::drop('model_has_roles');
        Schema::rename('model_has_roles_temp', 'model_has_roles');
    }
};
