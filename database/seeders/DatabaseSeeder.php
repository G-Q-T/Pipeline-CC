<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Content;
use App\Models\ContentAssignment;
use App\Models\ContentChecklist;
use App\Models\ContentMetric;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users (Admin, Creator, Editor)
        $admin = User::firstOrCreate(
            ['email' => 'admin@pipeline.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $creator = User::firstOrCreate(
            ['email' => 'creator@pipeline.test'],
            [
                'name' => 'Creator Utama',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CREATOR,
            ]
        );

        $editor = User::firstOrCreate(
            ['email' => 'editor@pipeline.test'],
            [
                'name' => 'Editor Alex',
                'password' => Hash::make('password'),
                'role' => User::ROLE_EDITOR,
            ]
        );

        // 2. Channels for Creator
        $ytChannel = Channel::firstOrCreate(
            ['user_id' => $creator->id, 'platform' => Channel::PLATFORM_YOUTUBE],
            ['handle_name' => '@CreatorPipelineYT']
        );

        $ttChannel = Channel::firstOrCreate(
            ['user_id' => $creator->id, 'platform' => Channel::PLATFORM_TIKTOK],
            ['handle_name' => '@pipeline_creator']
        );

        $igChannel = Channel::firstOrCreate(
            ['user_id' => $creator->id, 'platform' => Channel::PLATFORM_INSTAGRAM],
            ['handle_name' => '@pipeline.creator']
        );

        // 3. Sample Contents across statuses
        // Content 1: Idea
        Content::firstOrCreate(
            ['user_id' => $creator->id, 'title' => 'Top 5 AI Tools untuk Konten Kreator 2026'],
            [
                'channel_id' => $ytChannel->id,
                'description' => 'Membahas rekomendasi AI generator video, scriptwriting, dan thumbnail.',
                'script_draft' => null,
                'format_type' => Content::FORMAT_LONG_VIDEO,
                'status' => Content::STATUS_IDEA,
                'priority' => Content::PRIORITY_HIGH,
                'scheduled_date' => now()->addDays(7)->toDateString(),
            ]
        );

        // Content 2: Scripting
        Content::firstOrCreate(
            ['user_id' => $creator->id, 'title' => '3 Tips Meningkatkan Retention Rate TikTok'],
            [
                'channel_id' => $ttChannel->id,
                'description' => 'Hook di 3 detik pertama sangat menentukan keberhasilan video.',
                'script_draft' => "Hook: Jangan buat video TikTok sebelum tahu rahasia ini!\nProblem: Banyak orang upload tapi drop di detik ke-2.\nSolution: Pakai visual hook dan pertanyaan memancing.",
                'format_type' => Content::FORMAT_SHORT_VIDEO,
                'status' => Content::STATUS_SCRIPTING,
                'priority' => Content::PRIORITY_MEDIUM,
                'scheduled_date' => now()->addDays(3)->toDateString(),
            ]
        );

        // Content 3: Editing (Assigned to Editor Alex with Checklists)
        $editingContent = Content::firstOrCreate(
            ['user_id' => $creator->id, 'title' => 'Setup Studio Minimalis Budget 2 Juta'],
            [
                'channel_id' => $ytChannel->id,
                'description' => 'Review ring light, mic condenser, dan backdrop akustik.',
                'script_draft' => "Intro studio tour, breakdown harga barang, tips lighting 3-point.",
                'format_type' => Content::FORMAT_LONG_VIDEO,
                'status' => Content::STATUS_EDITING,
                'priority' => Content::PRIORITY_HIGH,
                'scheduled_date' => now()->addDays(2)->toDateString(),
            ]
        );

        ContentAssignment::firstOrCreate(
            ['content_id' => $editingContent->id, 'editor_id' => $editor->id],
            ['assigned_by' => $creator->id, 'assigned_at' => now()]
        );

        ContentChecklist::firstOrCreate(
            ['content_id' => $editingContent->id, 'task_name' => 'Rough cut dan sinkronisasi audio'],
            ['is_completed' => true]
        );
        ContentChecklist::firstOrCreate(
            ['content_id' => $editingContent->id, 'task_name' => 'B-roll dan color grading'],
            ['is_completed' => true]
        );
        ContentChecklist::firstOrCreate(
            ['content_id' => $editingContent->id, 'task_name' => 'Sound design dan SFX'],
            ['is_completed' => false]
        );

        // Content 4: Ready
        Content::firstOrCreate(
            ['user_id' => $creator->id, 'title' => 'Cheat Sheet Desain Carousel Canva'],
            [
                'channel_id' => $igChannel->id,
                'description' => 'Slide 1-7 berisi panduan visual typography dan grid.',
                'script_draft' => 'Slide 1: Cover menarik. Slide 2-6: Tips praktis. Slide 7: CTA Save & Share.',
                'format_type' => Content::FORMAT_CAROUSEL,
                'status' => Content::STATUS_READY,
                'priority' => Content::PRIORITY_MEDIUM,
                'scheduled_date' => now()->addDay()->toDateString(),
            ]
        );

        // Content 5: Published with Metrics
        $publishedContent = Content::firstOrCreate(
            ['user_id' => $creator->id, 'title' => 'Cara Monetisasi Konten Dari Nol'],
            [
                'channel_id' => $ytChannel->id,
                'description' => 'Studi kasus menghasilkan pendapatan pertama dari sponsorship dan adsense.',
                'script_draft' => 'Draft naskah lengkap.',
                'format_type' => Content::FORMAT_LONG_VIDEO,
                'status' => Content::STATUS_PUBLISHED,
                'priority' => Content::PRIORITY_HIGH,
                'scheduled_date' => now()->subDays(5)->toDateString(),
                'published_date' => now()->subDays(4)->toDateString(),
                'published_url' => 'https://youtube.com/watch?v=sample12345',
            ]
        );

        ContentMetric::firstOrCreate(
            ['content_id' => $publishedContent->id, 'recorded_at' => now()->subDays(3)->toDateString()],
            [
                'views_count' => 1250,
                'likes_count' => 95,
                'comments_count' => 24,
                'shares_count' => 12,
            ]
        );

        ContentMetric::firstOrCreate(
            ['content_id' => $publishedContent->id, 'recorded_at' => now()->toDateString()],
            [
                'views_count' => 4580,
                'likes_count' => 380,
                'comments_count' => 76,
                'shares_count' => 45,
            ]
        );
    }
}
