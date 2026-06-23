# Issue Tracker

A mini issue tracker built with Laravel 12 and SQLite. Organise work into projects, track issues with statuses and priorities, assign tags and team members, and discuss progress through comments — all in a clean, custom-styled interface with no external CSS framework.

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2) |
| Database | SQLite (via XAMPP) |
| Frontend | Blade templates, vanilla CSS & JS |
| Auth | Laravel session auth (manual, no Breeze) |

## Features

- **Projects** — create, edit, soft-delete and restore projects; each project has a name, description, start date, and deadline; only the owner can edit or delete
- **Issues** — nested under projects; status (`open` / `in_progress` / `closed`), priority (`low` / `medium` / `high`), optional due date, soft-delete with restore
- **Tags** — global tag library with custom colours; attach/detach tags on any issue via AJAX toggle
- **Assignees** — assign multiple team members to an issue via a many-to-many pivot (`issue_user`); attach/detach via AJAX toggle
- **Comments** — AJAX-powered comment thread on each issue; comments load at the top, form at the bottom; paginated with "Load more"
- **Search** — debounced AJAX text search on both the All Issues list and each project's issue table (no page reload); dropdown filters (status, priority, tag) use standard form submit
- **Authorization** — `ProjectPolicy` ensures only a project's owner sees Edit/Delete buttons and can perform those actions
- **Cascade soft-delete** — deleting a project automatically soft-deletes all its issues; restoring a project restores them

## Architecture patterns


- `HasUuid` trait — auto-generates a UUID on `creating`; all models expose `uuid` as the route key (internal integer IDs are never in URLs)
- `Filterable` trait + `QueryFilters` base class — scoped filter classes per model (`ProjectFilters`, `TagFilters`, `CommentFilters`)
- `Store` / `Update` Form Request naming convention
- `delete()` / `restore()` controller methods instead of `destroy()` — soft-delete first, force-delete on second call
- `loadRelationships()` on the base `Controller` — reads `?with=` query param and calls `$model->load()`
- API Resources (`JsonResource`) with `'id' => $this->uuid` for all JSON responses

## Getting started

**Requirements:** PHP 8.2, Composer, XAMPP (or any local server with SQLite support)

```bash
# 1. Install dependencies
composer install

# 2. Copy environment file and generate key
cp .env.example .env
php artisan key:generate

# 3. Run migrations and seed demo data
php artisan migrate:fresh --seed

# 4. Serve the app
php artisan serve
```

Then open `http://localhost:8000` and sign in with one of the demo accounts.

## Demo accounts

| Name | Email | Password | Role |
|---|---|---|---|
| Alice Martin | alice@example.com | Secret123! | Owns projects 1 |
| James Okafor | james@example.com | Secret123! | Owns 2 |
| Sophie Renard | sophie@example.com | Secret123! | Member only |

## Project structure

```
app/
  Filters/          QueryFilters base + ProjectFilters, TagFilters, CommentFilters
  Http/
    Controllers/
      Auth/         LoginController
      ProjectController, IssueController, TagController
      CommentController, IssueTagController, IssueMemberController
    Requests/       StoreProjectRequest, UpdateProjectRequest, StoreIssueRequest, …
    Resources/      ProjectResource, IssueResource, TagResource, CommentResource, UserResource
  Models/           Project, Issue, Tag, Comment, User
  Policies/         ProjectPolicy
  Traits/           HasUuid, Filterable
database/
  migrations/
  seeders/          DatabaseSeeder (3 users, 5 tags, 3 projects, 10 issues, 30 comments)
resources/views/
  layouts/          app.blade.php (design system, delete dialog, flash messages)
  auth/             login.blade.php
  projects/         index, show, create, edit
  issues/           index (all issues), show, create, edit
  tags/             index
  partials/         pagination
routes/
  web.php           All routes — auth-guarded, explicit (no resource macros)
```

## Key routes

| Method | URI | Description |
|---|---|---|
| GET | `/login` | Login page |
| GET | `/projects` | Project list |
| GET | `/projects/{uuid}` | Project detail + issue table |
| GET | `/projects/{uuid}/issues/{uuid}` | Issue detail |
| GET | `/issues` | All issues (flat list) |
| GET | `/tags` | Tag management |
| POST | `/projects/{uuid}/issues/{uuid}/tags/toggle` | AJAX tag attach/detach |
| POST | `/projects/{uuid}/issues/{uuid}/members/toggle` | AJAX assignee attach/detach |
| POST | `/projects/{uuid}/issues/{uuid}/comments` | AJAX post comment |
