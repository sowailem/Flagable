# Flagable

A flexible and robust Laravel package that provides comprehensive flagging functionality for Eloquent models. This package allows any model to flag any other model with different flag types such as like, follow, favorite, bookmark, upvote, downvote, and custom types through a sophisticated multi-table architecture.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Database Architecture](#database-architecture)
- [Usage Guide](#usage-guide)
- [API Reference](#api-reference)
- [Advanced Usage](#advanced-usage)
- [Default Flag Types](#default-flag-types)
- [Performance Considerations](#performance-considerations)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Features

- **Flexible Flagging System**: Any model can flag any other model with polymorphic relationships
- **Multiple Flag Types**: Support for like, follow, favorite, bookmark, upvote, downvote, and unlimited custom types
- **Sophisticated Architecture**: Four-table design for optimal performance and flexibility
- **Easy Integration**: Simple traits to add flagging capabilities to your models
- **Facade Support**: Clean API through Laravel facades
- **Database Migrations**: Automatic database structure setup with proper indexes and constraints
- **Default Seeders**: Pre-configured flag types ready to use
- **Laravel Auto-Discovery**: Automatic service provider and facade registration
- **Polymorphic Relationships**: Full support for different model types as flaggers and targets
- **Unique Constraints**: Prevents duplicate flags with database-level constraints
- **Performance Optimized**: Proper indexing and efficient queries

## Requirements

- PHP 8.0 or higher
- Laravel 9.0, 10.0, 11.0, or 12.0

## Installation

### Step 1: Install via Composer

```bash
composer require sowailem/flagable
```

### Step 2: Run Migrations

The package will automatically register its service provider and facade through Laravel's auto-discovery feature.

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

### Step 3: Seed Default Flag Types (Optional)

To populate the database with default flag types:

```bash
php artisan db:seed --class="Sowailem\Flagable\Database\Seeders\FlagTypeSeeder"
```

This will create the following flag types: `like`, `follow`, `favorite`, `bookmark`, `upvote`, `downvote`.

## Quick Start

### 1. Setup Your Models

Add the appropriate traits to your models:

```php
<?php

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Sowailem\Flagable\Traits\CanFlag;
use Sowailem\Flagable\Traits\Flagable;

// User model (can flag other models)
class User extends Authenticatable
{
    use CanFlag;
    
    // Your existing model code...
}

// Post model (can be flagged by other models)
class Post extends Model
{
    use Flagable;
    
    // Your existing model code...
}

// Comment model (can both flag and be flagged)
class Comment extends Model
{
    use CanFlag, Flagable;
    
    // Your existing model code...
}
```

### 2. Basic Usage

```php
$user = User::find(1);
$post = Post::find(1);

// Flag a post
$flag = $user->flag($post, 'like');

// Check if user has flagged the post
if ($user->hasFlagged($post, 'like')) {
    echo "User likes this post!";
}

// Unflag a post
$user->unflag($post, 'like');

// Get flag count for a post
$likeCount = $post->flagCount('like');
$totalFlags = $post->flagCount(); // All flag types

// Get all users who liked a post
$likers = $post->flaggers('like', User::class);

// Check if post is flagged by a specific user
$isLiked = $post->isFlaggedBy($user, 'like');
```

## Database Architecture

The package uses a sophisticated four-table architecture for maximum flexibility and performance:

### Tables Structure

#### 1. `flag_types` Table
Stores available flag types (like, follow, etc.)
```sql
- id (Primary Key)
- name (Unique String) - Flag type name
```

#### 2. `flag_targets` Table  
Stores model class names that can be flagged
```sql
- id (Primary Key)
- name (String) - Fully qualified class name
```

#### 3. `flag_links` Table
Links flag types to target model types (pivot table)
```sql
- id (Primary Key)
- flag_type_id (Foreign Key to flag_types)
- flag_target_id (Foreign Key to flag_targets)
- UNIQUE(flag_type_id, flag_target_id)
```

#### 4. `flags` Table
Stores individual flag records
```sql
- id (Primary Key)
- flag_link_id (Foreign Key to flag_links)
- flagger_type (String) - Polymorphic type
- flagger_id (Big Integer) - Polymorphic ID
- created_at, updated_at (Timestamps)
- UNIQUE(flag_link_id, flagger_type, flagger_id)
- INDEX(flagger_type, flagger_id)
```

### Relationships Diagram

```
FlagType ──┐
           ├── FlagLink ──── Flag ──── Flagger (Polymorphic)
FlagTarget ──┘
```

## Usage Guide

### Using Traits

#### CanFlag Trait

Add this trait to models that can flag other models:

```php
use Sowailem\Flagable\Traits\CanFlag;

class User extends Model
{
    use CanFlag;
}
```

**Available Methods:**
- `flag(Model $target, string $type): Flag`
- `unflag(Model $target, string $type): bool`
- `hasFlagged(Model $target, ?string $type = null): bool`
- `flags(): MorphMany` - Get all flags created by this model

#### Flagable Trait

Add this trait to models that can be flagged by other models:

```php
use Sowailem\Flagable\Traits\Flagable;

class Post extends Model
{
    use Flagable;
}
```

**Available Methods:**
- `isFlaggedBy(Model $flagger, ?string $type = null): bool`
- `flagCount(?string $type = null): int`
- `flaggers(string $type, string $flaggerModel): Collection`
- `flags(): HasManyThrough` - Get all flags for this model

### Using the Facade

```php
use Sowailem\Flagable\Facades\Flag;

// Manage flag types
$flagType = Flag::addFlagType('custom_type');
$removed = Flag::removeFlagType('old_type');

// Direct flagging operations
$flag = Flag::flag($user, $post, 'like');
$unflagged = Flag::unflag($user, $post, 'like');

// Query operations
$isFlagged = Flag::isFlaggedBy($post, $user, 'like');
$count = Flag::getFlagCount($post, 'like');
$flaggers = Flag::getFlaggers($post, 'like', User::class);
```

## API Reference

### CanFlag Trait Methods

#### `flag(Model $target, string $type): Flag`
Creates a flag record for the target model.

**Parameters:**
- `$target` - The model to be flagged
- `$type` - The flag type (e.g., 'like', 'follow')

**Returns:** `Flag` model instance

**Example:**
```php
$flag = $user->flag($post, 'like');
```

#### `unflag(Model $target, string $type): bool`
Removes a flag record for the target model.

**Parameters:**
- `$target` - The model to unflag
- `$type` - The flag type to remove

**Returns:** `bool` - True if flag was removed, false otherwise

**Example:**
```php
$removed = $user->unflag($post, 'like');
```

#### `hasFlagged(Model $target, ?string $type = null): bool`
Checks if this model has flagged the target model.

**Parameters:**
- `$target` - The model to check
- `$type` - Optional flag type filter

**Returns:** `bool`

**Example:**
```php
$hasLiked = $user->hasFlagged($post, 'like');
$hasAnyFlag = $user->hasFlagged($post); // Any flag type
```

#### `flags(): MorphMany`
Gets all flags created by this model.

**Returns:** `MorphMany` relationship

**Example:**
```php
$userFlags = $user->flags()->get();
$recentFlags = $user->flags()->where('created_at', '>', now()->subDays(7))->get();
```

### Flagable Trait Methods

#### `isFlaggedBy(Model $flagger, ?string $type = null): bool`
Checks if this model is flagged by the specified flagger.

**Parameters:**
- `$flagger` - The model that might have flagged this model
- `$type` - Optional flag type filter

**Returns:** `bool`

**Example:**
```php
$isLiked = $post->isFlaggedBy($user, 'like');
$hasAnyFlag = $post->isFlaggedBy($user);
```

#### `flagCount(?string $type = null): int`
Gets the count of flags for this model.

**Parameters:**
- `$type` - Optional flag type filter

**Returns:** `int`

**Example:**
```php
$likeCount = $post->flagCount('like');
$totalFlags = $post->flagCount();
```

#### `flaggers(string $type, string $flaggerModel): Collection`
Gets all models that have flagged this model with the specified type.

**Parameters:**
- `$type` - The flag type
- `$flaggerModel` - The class name of the flagger model

**Returns:** `Collection`

**Example:**
```php
$likers = $post->flaggers('like', User::class);
$followers = $user->flaggers('follow', User::class);
```

#### `flags(): HasManyThrough`
Gets all flag records for this model.

**Returns:** `HasManyThrough` relationship

**Example:**
```php
$postFlags = $post->flags()->get();
$recentFlags = $post->flags()->where('created_at', '>', now()->subDays(7))->get();
```

### Facade Methods

#### `Flag::addFlagType(string $name): FlagType`
Creates a new flag type.

**Parameters:**
- `$name` - The flag type name

**Returns:** `FlagType` model instance

#### `Flag::removeFlagType(string $name): bool`
Removes a flag type.

**Parameters:**
- `$name` - The flag type name to remove

**Returns:** `bool`

#### `Flag::flag(Model $flagger, Model $target, string $type): Flag`
Creates a flag record.

**Parameters:**
- `$flagger` - The model creating the flag
- `$target` - The model being flagged
- `$type` - The flag type

**Returns:** `Flag` model instance

#### `Flag::unflag(Model $flagger, Model $target, string $type): bool`
Removes a flag record.

**Parameters:**
- `$flagger` - The model removing the flag
- `$target` - The model being unflagged
- `$type` - The flag type

**Returns:** `bool`

#### `Flag::isFlaggedBy(Model $target, Model $flagger, ?string $type = null): bool`
Checks if target is flagged by flagger.

#### `Flag::getFlagCount(Model $target, ?string $type = null): int`
Gets flag count for target.

#### `Flag::getFlaggers(Model $target, string $type, string $flaggerModel): Collection`
Gets all flaggers for target.

## Advanced Usage

### Custom Flag Types

You can create custom flag types dynamically:

```php
use Sowailem\Flagable\Facades\Flag;

// Add custom flag types
Flag::addFlagType('report');
Flag::addFlagType('spam');
Flag::addFlagType('inappropriate');

// Use them immediately
$user->flag($post, 'report');
```

### Bulk Operations

```php
// Flag multiple posts
$posts = Post::whereIn('id', [1, 2, 3])->get();
foreach ($posts as $post) {
    $user->flag($post, 'like');
}

// Get flag counts for multiple posts
$posts = Post::with(['flags' => function ($query) {
    $query->whereHas('link.type', function ($q) {
        $q->where('name', 'like');
    });
}])->get();

foreach ($posts as $post) {
    $likeCount = $post->flagCount('like');
    echo "Post {$post->id} has {$likeCount} likes\n";
}
```

### Complex Queries

```php
// Get posts with more than 10 likes
$popularPosts = Post::whereHas('flags', function ($query) {
    $query->whereHas('link.type', function ($q) {
        $q->where('name', 'like');
    });
}, '>', 10)->get();

// Get users who liked specific posts
$postIds = [1, 2, 3];
$likers = User::whereHas('flags', function ($query) use ($postIds) {
    $query->whereHas('link', function ($q) use ($postIds) {
        $q->whereHas('target', function ($target) {
            $target->where('name', Post::class);
        })->whereHas('type', function ($type) {
            $type->where('name', 'like');
        });
    })->whereIn('flagger_id', function ($subQuery) use ($postIds) {
        $subQuery->select('id')->from('posts')->whereIn('id', $postIds);
    });
})->get();
```

### Model Relationships

```php
// In your User model
public function likedPosts()
{
    return $this->belongsToMany(Post::class, 'flags', 'flagger_id', 'flagger_id')
        ->whereHas('flags.link.type', function ($query) {
            $query->where('name', 'like');
        })
        ->where('flagger_type', User::class);
}

// In your Post model
public function likers()
{
    return $this->belongsToMany(User::class, 'flags', 'flagger_id', 'flagger_id')
        ->whereHas('flags.link.type', function ($query) {
            $query->where('name', 'like');
        })
        ->where('flagger_type', User::class);
}
```

## Default Flag Types

The package comes with six predefined flag types that are created when you run the seeder:

- **`like`** - General approval or appreciation
- **`follow`** - Subscribe to updates or content
- **`favorite`** - Mark as preferred or special
- **`bookmark`** - Save for later reference
- **`upvote`** - Positive voting (Reddit-style)
- **`downvote`** - Negative voting (Reddit-style)

### Using Default Types

```php
// All default types are immediately available
$user->flag($post, 'like');
$user->flag($anotherUser, 'follow');
$user->flag($post, 'bookmark');
$user->flag($comment, 'upvote');
```

## Performance Considerations

### Database Indexes

The package automatically creates the following indexes for optimal performance:

- Unique constraint on `flag_types.name`
- Unique constraint on `flag_links(flag_type_id, flag_target_id)`
- Unique constraint on `flags(flag_link_id, flagger_type, flagger_id)`
- Index on `flags(flagger_type, flagger_id)`

### Query Optimization

```php
// Efficient: Use specific flag type
$likeCount = $post->flagCount('like');

// Less efficient: Count all flags then filter
$allFlags = $post->flags()->get();
$likeCount = $allFlags->where('link.type.name', 'like')->count();

// Efficient: Eager load relationships
$posts = Post::with(['flags.link.type'])->get();

// Efficient: Use database aggregation
$popularPosts = Post::withCount(['flags' => function ($query) {
    $query->whereHas('link.type', function ($q) {
        $q->where('name', 'like');
    });
}])->having('flags_count', '>', 10)->get();
```

### Caching Strategies

```php
// Cache flag counts
$cacheKey = "post_{$post->id}_like_count";
$likeCount = Cache::remember($cacheKey, 3600, function () use ($post) {
    return $post->flagCount('like');
});

// Cache popular posts
$popularPosts = Cache::remember('popular_posts', 1800, function () {
    return Post::withCount(['flags' => function ($query) {
        $query->whereHas('link.type', function ($q) {
            $q->where('name', 'like');
        });
    }])->orderBy('flags_count', 'desc')->take(10)->get();
});
```

## Troubleshooting

### Common Issues

#### 1. "Class not found" errors
Make sure you've run `composer dump-autoload` after installation.

#### 2. Migration errors
Ensure you're running the migrations in the correct order. The package migrations are numbered to run in sequence.

#### 3. Duplicate flag errors
The package prevents duplicate flags at the database level. If you're getting constraint violations, check if the flag already exists before creating it:

```php
if (!$user->hasFlagged($post, 'like')) {
    $user->flag($post, 'like');
}
```

#### 4. Performance issues with large datasets
Use eager loading and database-level aggregations:

```php
// Instead of this:
foreach ($posts as $post) {
    $post->flagCount('like'); // N+1 query problem
}

// Do this:
$posts = Post::withCount(['flags' => function ($query) {
    $query->whereHas('link.type', function ($q) {
        $q->where('name', 'like');
    });
}])->get();

foreach ($posts as $post) {
    echo $post->flags_count; // No additional queries
}
```

#### 5. Memory issues with large collections
Use chunking for bulk operations:

```php
Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Process each post
        $likeCount = $post->flagCount('like');
    }
});
```

### Debug Mode

Enable query logging to debug performance issues:

```php
DB::enableQueryLog();

// Your flagging operations here
$user->flag($post, 'like');

// Check executed queries
$queries = DB::getQueryLog();
dd($queries);
```

### Validation

Always validate flag types before using them:

```php
use Sowailem\Flagable\Models\FlagType;

$validTypes = FlagType::pluck('name')->toArray();

if (in_array($requestedType, $validTypes)) {
    $user->flag($post, $requestedType);
} else {
    throw new InvalidArgumentException("Invalid flag type: {$requestedType}");
}
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork the repository** and create your feature branch
2. **Write tests** for any new functionality
3. **Follow PSR-12** coding standards
4. **Update documentation** for any API changes
5. **Submit a pull request** with a clear description

### Development Setup

```bash
# Clone your fork
git clone https://github.com/yourusername/flagable.git
cd flagable

# Install dependencies
composer install

# Run tests (when available)
composer test

# Check code style
composer cs-check
```

### Reporting Issues

When reporting issues, please include:

- Laravel version
- PHP version
- Package version
- Steps to reproduce
- Expected vs actual behavior
- Any error messages

## Security

If you discover any security-related issues, please email [abdullah.sowailem@gmail.com](mailto:abdullah.sowailem@gmail.com) instead of using the issue tracker.

### Security Considerations

- Always validate user input before creating flags
- Consider rate limiting flag creation to prevent abuse
- Implement proper authorization checks in your controllers
- Be cautious with mass assignment when using flag data

```php
// Example authorization check
public function flagPost(Request $request, Post $post)
{
    $this->authorize('flag', $post);
    
    $request->validate([
        'type' => 'required|string|in:like,follow,favorite,bookmark'
    ]);
    
    auth()->user()->flag($post, $request->type);
}
```

## Credits

- **[Abdullah Sowailem](https://github.com/sowailem)** - Creator and maintainer
- All contributors who have helped improve this package

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

**Flagable** provides a flexible and robust way to implement flagging functionality in Laravel applications. The sophisticated four-table architecture allows for multiple flag types (like, follow, favorite, etc.) and supports any model flagging any other model with optimal performance and data integrity.