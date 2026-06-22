<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alice  = User::create(['name' => 'Alice Martin',  'email' => 'alice@example.com',  'password' => Hash::make('Secret123!')]);
        $james  = User::create(['name' => 'James Okafor',  'email' => 'james@example.com',  'password' => Hash::make('Secret123!')]);
        $sophie = User::create(['name' => 'Sophie Renard', 'email' => 'sophie@example.com', 'password' => Hash::make('Secret123!')]);

        $tags = collect([
            ['name' => 'bug',         'color' => '#ef4444'],
            ['name' => 'feature',     'color' => '#3b82f6'],
            ['name' => 'enhancement', 'color' => '#8b5cf6'],
            ['name' => 'docs',        'color' => '#f59e0b'],
            ['name' => 'urgent',      'color' => '#ec4899'],
        ])->map(fn($data) => Tag::create($data));

        $tagMap = $tags->keyBy('name');

        $projects = [
            [
                'owner'       => $alice,
                'name'        => 'Customer Portal Redesign',
                'description' => 'Redesign the customer-facing portal to improve usability, modernise the visual style, and reduce support ticket volume by at least 20%.',
                'start_date'  => '2026-04-01',
                'deadline'    => '2026-08-31',
                'issues'      => [
                    ['title' => 'Replace legacy login page with new design', 'description' => 'The current login page dates back to 2019. Implement the new Figma design, add "remember me" support, and ensure accessibility compliance (WCAG 2.1 AA).', 'status' => 'closed',      'priority' => 'high',   'due_date' => '2026-05-10', 'tags' => ['bug', 'enhancement'], 'members' => [$james]],
                    ['title' => 'Add dark mode support to dashboard',        'description' => 'Detect the OS-level preference via `prefers-color-scheme` and persist the choice in local storage. Verify all chart colours meet contrast requirements.',     'status' => 'in_progress', 'priority' => 'medium', 'due_date' => '2026-07-01', 'tags' => ['feature'], 'members' => [$sophie]],
                    ['title' => 'Fix broken avatar upload on profile page',  'description' => 'Users report that uploading a profile picture larger than 1 MB silently fails with no error message. Investigate the upload handler and add proper validation feedback.', 'status' => 'open', 'priority' => 'high', 'due_date' => '2026-06-25', 'tags' => ['bug', 'urgent'], 'members' => [$james, $sophie]],
                ],
            ],
            [
                'owner'       => $alice,
                'name'        => 'Internal API v2',
                'description' => 'Rebuild the internal REST API to align with OpenAPI 3.1, introduce versioning, and migrate all consumers off the deprecated v1 endpoints before EOY.',
                'start_date'  => '2026-03-15',
                'deadline'    => '2026-09-30',
                'issues'      => [
                    ['title' => 'Document all v1 endpoints in OpenAPI spec',    'description' => 'Audit every existing v1 route and produce a complete OpenAPI 3.1 YAML spec. Highlight deprecated fields and flag any endpoints with undocumented side-effects.',                                                                                                                                              'status' => 'closed',      'priority' => 'medium', 'due_date' => '2026-04-30', 'tags' => ['docs'], 'members' => [$james]],
                    ['title' => 'Implement JWT refresh token rotation',         'description' => 'Replace the current long-lived tokens with a short-lived access token (15 min) + rotating refresh token (7 days). Invalidate old refresh tokens on each use to prevent reuse attacks.',                                                                                                                         'status' => 'in_progress', 'priority' => 'high',   'due_date' => '2026-07-15', 'tags' => ['feature', 'urgent'], 'members' => [$sophie]],
                    ['title' => 'Rate-limit /search endpoint to prevent abuse', 'description' => 'The /search endpoint has been scraped aggressively in the past month. Apply a per-IP limit of 60 req/min with a 429 response and a Retry-After header.',                                                                                                                                                       'status' => 'open',        'priority' => 'high',   'due_date' => '2026-06-28', 'tags' => ['bug', 'urgent'], 'members' => []],
                ],
            ],
            [
                'owner'       => $james,
                'name'        => 'Mobile App — iOS & Android',
                'description' => 'Launch the first version of the companion mobile app, targeting React Native for shared codebase between iOS and Android, with offline-first sync.',
                'start_date'  => '2026-05-01',
                'deadline'    => '2026-12-01',
                'issues'      => [
                    ['title' => 'Set up React Native monorepo with Expo',          'description' => 'Initialise the Expo-managed workflow, configure ESLint + Prettier, set up the CI pipeline (GitHub Actions), and document the local dev setup for all team members.',                                                                                                          'status' => 'closed',      'priority' => 'high',   'due_date' => '2026-05-15', 'tags' => ['feature'], 'members' => [$alice]],
                    ['title' => 'Design offline sync strategy with conflict resolution', 'description' => 'Evaluate CRDTs vs last-write-wins for the notes entity. Document the chosen approach, edge cases, and write unit tests covering merge conflicts.',                                                                                                                   'status' => 'in_progress', 'priority' => 'high',   'due_date' => '2026-08-01', 'tags' => ['feature', 'docs'], 'members' => [$alice, $sophie]],
                    ['title' => 'Push notifications not delivered on Android 14',  'description' => 'Several testers running Android 14 report they never receive push notifications. The iOS build works correctly. Likely related to the new notification permission model introduced in API level 33.',                                                                       'status' => 'open',        'priority' => 'high',   'due_date' => '2026-07-10', 'tags' => ['bug', 'urgent'], 'members' => []],
                    ['title' => 'Write onboarding screens copy and animations',    'description' => 'Implement the three onboarding slides agreed with the product team. Use Reanimated 3 for the entrance animations and keep total bundle impact under 50 KB.',                                                                                                              'status' => 'open',        'priority' => 'low',    'due_date' => null,         'tags' => ['enhancement'], 'members' => [$sophie]],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $issues  = $projectData['issues'];
            $owner   = $projectData['owner'];
            unset($projectData['issues'], $projectData['owner']);

            $project = Project::create(array_merge($projectData, ['user_id' => $owner->id]));

            foreach ($issues as $issueData) {
                $tagNames = $issueData['tags'] ?? [];
                $members  = $issueData['members'] ?? [];
                unset($issueData['tags'], $issueData['members']);

                $issue = $project->issues()->create($issueData);
                $issue->tags()->attach($tagMap->only($tagNames)->pluck('id'));
                if (!empty($members)) {
                    $issue->members()->attach(collect($members)->pluck('id'));
                }

                $comments = [
                    ['author_name' => 'Alice Martin',  'body' => 'Picked this up — will post an update once I have a working branch.'],
                    ['author_name' => 'James Okafor',  'body' => 'Confirmed the issue on my end as well. Happy to review the fix once it\'s ready.'],
                    ['author_name' => 'Sophie Renard', 'body' => 'Left some inline notes in the PR. Overall looks good, just one concern about the error handling path.'],
                ];

                foreach ($comments as $comment) {
                    $issue->comments()->create($comment);
                }
            }
        }
    }
}
