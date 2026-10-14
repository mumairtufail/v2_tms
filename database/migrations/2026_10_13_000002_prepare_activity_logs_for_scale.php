<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reshapes activity_logs for high write volume and for evidence that outlives
 * the records it refers to.
 *
 * Three changes, each with a reason:
 *
 * 1. Foreign keys are dropped. On an audit table they are actively harmful:
 *    every insert pays for constraint checks on five columns, and nullOnDelete
 *    means deleting a user quietly erases who performed every past action. The
 *    id columns and their indexes stay; only the constraints go.
 *
 * 2. The actor's name and email are denormalised onto the row, so the trail
 *    still names the person after their account is gone.
 *
 * 3. url and user_agent are widened. They were varchar(255) while the writer
 *    truncates at 2000 and 500, so a long URL raised an error in strict mode
 *    and the entry was lost - the opposite of what an audit log should do.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeys();

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('actor_name')->nullable()->after('impersonator_id');
            $table->string('actor_email')->nullable()->after('actor_name');
            $table->string('impersonator_name')->nullable()->after('actor_email');

            // Ties every entry written during one request together, including
            // the request row, its model changes and any error it produced.
            $table->uuid('request_id')->nullable()->after('session_id');

            // Engineer-facing payload: stack traces, exception chains, SQL
            // errors, headers. Super admin only, and pruned on its own clock
            // because it is by far the largest thing on the row.
            $table->json('technical')->nullable()->after('data');
        });

        DB::statement('ALTER TABLE activity_logs MODIFY url VARCHAR(2048) NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY user_agent VARCHAR(512) NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY ip_address VARCHAR(45) NULL');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('request_id');
        });

        $this->backfillActorColumns();
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['request_id']);
            $table->dropColumn(['actor_name', 'actor_email', 'impersonator_name', 'request_id', 'technical']);
        });

        DB::statement('ALTER TABLE activity_logs MODIFY url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY user_agent VARCHAR(255) NULL');
        DB::statement('ALTER TABLE activity_logs MODIFY ip_address VARCHAR(255) NULL');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('impersonator_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });
    }

    /**
     * Drop by discovered name - the constraints were added across several
     * migrations and may not follow one convention.
     */
    protected function dropForeignKeys(): void
    {
        $database = DB::connection()->getDatabaseName();

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$database, 'activity_logs', 'FOREIGN KEY']
        );

        foreach ($constraints as $constraint) {
            DB::statement(sprintf(
                'ALTER TABLE activity_logs DROP FOREIGN KEY `%s`',
                $constraint->CONSTRAINT_NAME
            ));
        }
    }

    /**
     * Lift the actor details that existing rows kept inside the data blob up
     * into the new columns, so history reads the same way as new entries.
     */
    protected function backfillActorColumns(): void
    {
        DB::table('activity_logs')
            ->whereNull('actor_name')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $data = json_decode($row->data ?? '[]', true) ?: [];

                    DB::table('activity_logs')->where('id', $row->id)->update([
                        'actor_name' => $data['actor_name'] ?? null,
                        'actor_email' => $data['actor_email'] ?? null,
                        'impersonator_name' => $data['impersonator_name'] ?? null,
                    ]);
                }
            });
    }
};
