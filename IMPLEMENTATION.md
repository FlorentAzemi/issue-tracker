# Issue Tracker — Implementation Reference

**Stack:** Laravel 12 · SQLite · Blade templates · Vanilla JS  
**Run:** `php artisan serve` → `http://localhost:8000`  
**Reseed:** `php artisan migrate:fresh --seed --force`

---

## Directory Structure

```
app/
  Filters/          Query filter classes (one per model)
  Http/
    Controllers/    One controller per entity
    Requests/       Store/Update form request classes
    Resources/      JSON API resources (id always = uuid)
  Models/           Eloquent models
  Traits/           HasUuid — auto-generates UUID on creating
database/
  factories/        Fake data factories
  migrations/       Schema definitions
  seeders/          DatabaseSeeder with realistic demo data
resources/views/
  layouts/app.blade.php   Single shared layout
  partials/               Reusable form fragments + pagination
  projects/               index · create · edit · show
  issues/                 index · create · edit · show
  tags/                   index
routes/web.php
```

---

## Database Schema

### Migrations (in order)

| File | Table | Key columns |
|---|---|---|
| `000010_create_projects_table` | `projects` | `uuid`, `name`, `description`, `start_date`, `deadline`, `user_id`, `deleted_at` |
| `000011_create_tags_table` | `tags` | `uuid`, `name` (unique), `color`, `deleted_at` |
| `000012_create_issues_table` | `issues` | `uuid`, `project_id`, `title`, `description`, `status` (open/in_progress/closed), `priority` (low/medium/high), `due_date`, `deleted_at` |
| `000013_create_issue_tag_table` | `issue_tag` | `issue_id`, `tag_id` — composite PK |
| `000014_create_comments_table` | `comments` | `uuid`, `issue_id`, `author_name`, `body`, `deleted_at` |

> `start_date` and `deadline` were added directly into the projects migration (not a separate migration) after the initial scaffold.

### Relationships

```
User        ──< Project      (one-to-many)
Project     ──< Issue        (one-to-many, cascade soft-delete)
Issue       >─< Tag          (many-to-many via issue_tag)
Issue       ──< Comment      (one-to-many)
```

---

## Models

All models share the same trait/behaviour pattern, matching `borm-api` conventions:

```php
use HasUuid, HasFactory, Filterable, SoftDeletes;

protected $fillable = ['uuid', ...];
protected $casts    = ['created_at' => 'datetime', 'updated_at' => 'datetime', 'deleted_at' => 'datetime', ...];

public function getRouteKeyName(): string { return 'uuid'; }
```

### `Project`
- Relationships: `user()`, `issues()`
- `boot()` — on soft-delete, soft-deletes all child issues; on force-delete, force-deletes them; on restore, restores them

### `Issue`
- Relationships: `project()`, `tags()` (M2M), `comments()` (ordered latest first)
- Default attributes: `status = open`, `priority = medium`

### `Tag`
- Relationship: `issues()` (M2M inverse)

### `Comment`
- Relationship: `issue()`

---

## Traits & Filters

### `app/Traits/HasUuid.php`
`bootHasUuid()` hooks into `creating` and assigns `Str::uuid()` automatically. No manual UUID passing needed.

### `app/Filters/QueryFilters.php`
Base filter class. Reads `filter[]` query params and calls a method per key if it exists on the subclass.

```
GET /projects?filter[search]=portal&filter[trashed]=false
```

### Filter subclasses

| Class | Supported filters |
|---|---|
| `ProjectFilters` | `search`, `trashed` |
| `TagFilters` | `search` |
| `CommentFilters` | `search` |

> `IssueFilters` was removed — `IssueController@index` uses inline filtering instead.

### `app/Filters/Filterable.php`
Trait added to every model. Adds `scopeFilter()` so models support `Model::filter($filters)`.

---

## Controllers

### Base `Controller`
Extends Laravel's `BaseController` with `AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`.  
Adds `loadRelationships($model, $request)` — reads `?with=relation1,relation2` from the request and eager-loads them onto the model.

### `ProjectController`
| Method | Route | Notes |
|---|---|---|
| `index` | `GET /projects` | `ProjectFilters` injected; default sort by `created_at desc` |
| `create` | `GET /projects/create` | |
| `store` | `POST /projects` | `StoreProjectRequest`; `loadRelationships` before redirect |
| `show` | `GET /projects/{project}` | Inline filters: status, priority, tag (by uuid), search |
| `edit` | `GET /projects/{project}/edit` | |
| `update` | `PUT /projects/{project}` | `UpdateProjectRequest`; `loadRelationships` before redirect |
| `delete` | `DELETE /projects/{project}` | Soft-delete; force-delete if already trashed |
| `restore` | `POST /projects/{project}/restore` | |

### `IssueController`
| Method | Route | Notes |
|---|---|---|
| `index` | `GET /issues` | Global list — inline filters, eager-loads `project` + `tags` |
| `showGlobal` | `GET /issues/{issue}` | Redirects to `projects.issues.show` (resolves project from issue) |
| `create` | `GET /projects/{project}/issues/create` | |
| `store` | `POST /projects/{project}/issues` | `StoreIssueRequest` |
| `show` | `GET /projects/{project}/issues/{issue}` | Loads `tags` + `project`; passes all tags for modal |
| `edit` | `GET /projects/{project}/issues/{issue}/edit` | |
| `update` | `PUT /projects/{project}/issues/{issue}` | `UpdateIssueRequest` |
| `delete` | `DELETE /projects/{project}/issues/{issue}` | Soft-delete or force-delete |
| `restore` | `POST /projects/{project}/issues/{issue}/restore` | |

### `TagController`
| Method | Route | Notes |
|---|---|---|
| `index` | `GET /tags` | `TagFilters` injected; `withCount('issues')` |
| `store` | `POST /tags` | `StoreTagRequest`; name must be unique |
| `delete` | `DELETE /tags/{tag}` | Soft-delete or force-delete |
| `restore` | `POST /tags/{tag}/restore` | |

### `CommentController` — JSON only
| Method | Route | Notes |
|---|---|---|
| `index` | `GET /projects/{project}/issues/{issue}/comments` | Paginated JSON; `CommentFilters` injected |
| `store` | `POST /projects/{project}/issues/{issue}/comments` | `StoreCommentRequest`; returns `CommentResource` 201 |
| `delete` | `DELETE /projects/{project}/issues/{issue}/comments/{comment}` | Returns 204 |
| `restore` | `POST /.../{comment}/restore` | Returns `CommentResource` 200 |

### `IssueTagController`
| Method | Route | Notes |
|---|---|---|
| `toggle` | `POST /projects/{project}/issues/{issue}/tags/toggle` | Accepts `tag_id` (int); toggles pivot; returns `{ attached, tag }` JSON |

---

## Form Requests

All request classes return `authorize(): true` (no auth in this project).

| Class | Used by | Rules |
|---|---|---|
| `StoreProjectRequest` | `ProjectController@store` | `name` required, `start_date` / `deadline` nullable dates, deadline after_or_equal start |
| `UpdateProjectRequest` | `ProjectController@update` | Same as Store |
| `StoreIssueRequest` | `IssueController@store` | `title` required, `status` enum, `priority` enum, `due_date` nullable |
| `UpdateIssueRequest` | `IssueController@update` | Same as Store |
| `StoreTagRequest` | `TagController@store` | `name` unique, `color` must match `#rrggbb` |
| `StoreCommentRequest` | `CommentController@store` | `author_name` required, `body` required max 5000 |

---

## API Resources

All resources expose `uuid` as `id` — internal integer IDs are never returned.

| Resource | Key fields |
|---|---|
| `ProjectResource` | `id` (uuid), `name`, `description`, `start_date`, `deadline`, nested `issues` when loaded |
| `IssueResource` | `id`, `title`, `status`, `priority`, `due_date`, nested `project` / `tags` / `comments` when loaded |
| `TagResource` | `id`, `name`, `color` |
| `CommentResource` | `id`, `author_name`, `body`, nested `issue` when loaded |

---

## Views

### Layout — `layouts/app.blade.php`
- Sticky navbar with active-link detection
- CSS custom properties design system (no external UI framework)
- `@stack('styles')` / `@stack('scripts')` for per-page additions
- Flash messages (`.js-flash`) auto-dismiss after **2 seconds** with CSS fade transition
- **Global delete dialog** — single modal rendered once; any `<form data-confirm-delete>` triggers it. Per-button text via `data-dialog-title` / `data-dialog-message`

### Partials
| File | Purpose |
|---|---|
| `partials/project-form.blade.php` | Shared create/edit form for projects |
| `partials/issue-form.blade.php` | Shared create/edit form for issues |
| `partials/pagination.blade.php` | Custom pagination using project CSS classes |

### Issue detail — `issues/show.blade.php`
Two-column layout (main + sidebar).

**Tags (AJAX):** "Manage" button opens a modal listing all tags. Clicking a tag calls `POST /…/tags/toggle` and updates the sidebar chips in-place — no page reload.

**Comments (AJAX):**
- On load, `GET /…/comments?page=1` fetches the first page; "Load more" fetches subsequent pages.
- Submitting the comment form posts JSON to `POST /…/comments`, prepends the new comment to the list, and clears the form.
- Inline validation errors shown below each field (no `alert()` boxes).

---

## Seeder — `DatabaseSeeder`

Creates deterministic demo data (no factories for names/content):

- 1 user (`demo@example.com`)
- 5 tags: `bug` · `feature` · `enhancement` · `docs` · `urgent`
- 3 projects with real names and descriptions
- 10 issues across the 3 projects (mixed statuses and priorities)
- 3 comments per issue from named authors (Alice Martin, James Okafor, Sophie Renard)

---

## Key Decisions

| Decision | Reason |
|---|---|
| UUID as route key (`getRouteKeyName`) | Matches borm-api convention; internal IDs never exposed in URLs |
| `SoftDeletes` on all entities | Enables restore; cascade handled in `Project::boot()` not at DB level |
| `delete()` method instead of `destroy()` | Matches borm-api naming; handles both soft and force delete in one method |
| Inline filtering in `IssueController@index` | `IssueFilters` was removed after the global issues list was rewritten; filter class would have been unused |
| `loadRelationships()` on base Controller | Allows callers to pass `?with=tags,project` to eager-load relations on demand, matching borm-api pattern |
| Separate `showGlobal` for `/issues/{issue}` | Global list uses flat URL; method resolves project and redirects to canonical nested URL |
