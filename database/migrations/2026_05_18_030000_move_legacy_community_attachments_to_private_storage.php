<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('community_messages')
            ->whereNotNull('attachment')
            ->orderBy('id')
            ->each(function (object $message): void {
                $attachment = json_decode((string) $message->attachment, true);

                if (! is_array($attachment) || ($attachment['disk'] ?? 'local') !== 'public') {
                    return;
                }

                $path = $this->safeCommunityAttachmentPath($attachment['path'] ?? null);
                if ($path === null) {
                    return;
                }

                $this->moveBetweenStorageRoots($path, 'public', 'private');
                $attachment['disk'] = 'local';

                DB::table('community_messages')
                    ->where('id', $message->id)
                    ->update(['attachment' => json_encode($attachment)]);
            });
    }

    public function down(): void
    {
        DB::table('community_messages')
            ->whereNotNull('attachment')
            ->orderBy('id')
            ->each(function (object $message): void {
                $attachment = json_decode((string) $message->attachment, true);

                if (! is_array($attachment) || ($attachment['disk'] ?? 'local') !== 'local') {
                    return;
                }

                $path = $this->safeCommunityAttachmentPath($attachment['path'] ?? null);
                if ($path === null) {
                    return;
                }

                $this->moveBetweenStorageRoots($path, 'private', 'public');
                $attachment['disk'] = 'public';

                DB::table('community_messages')
                    ->where('id', $message->id)
                    ->update(['attachment' => json_encode($attachment)]);
            });
    }

    private function moveBetweenStorageRoots(string $path, string $fromRoot, string $toRoot): void
    {
        $from = storage_path('app/'.$fromRoot.'/'.$path);
        $to = storage_path('app/'.$toRoot.'/'.$path);

        if (! File::exists($from)) {
            return;
        }

        File::ensureDirectoryExists(dirname($to));

        if (! File::exists($to)) {
            File::copy($from, $to);
        }

        File::delete($from);
    }

    private function safeCommunityAttachmentPath(mixed $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path), '/');

        if ($path === '' || str_contains($path, "\0") || str_contains($path, '..')) {
            return null;
        }

        return str_starts_with($path, 'community-attachments/') ? $path : null;
    }
};
