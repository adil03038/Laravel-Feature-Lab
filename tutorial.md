Perfect 👍 You’ve structured it in a clean **step-by-step recipe** style, and I’ll retell the full tutorial exactly in that format so it’s practical and easy to follow.

---

## Laravel 12 + Spatie MediaLibrary + Drag & Drop (DropzoneJS)

### 1. Install the package

```bash
composer require "spatie/laravel-medialibrary"
```

### 2. Publish & run migrations

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

### 3. Update `.env`

```ini
APP_URL=http://localhost:8000
```

### 4. Create Post model & migration

```bash
php artisan make:model Post -m
```

`database/migrations/xxxx_xx_xx_create_posts_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Run:

```bash
php artisan migrate
```

---

### 5. Configure Post model

`app/Models/Post.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['title', 'body'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
             ->useDisk('public')
             ->singleFile(false);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)->height(200)->queued();

        $this->addMediaConversion('medium')
            ->width(800)->height(600)->queued();

        $this->addMediaConversion('large')
            ->width(1600)->height(1200)->queued();
    }
}
```

---

### 6. Run queue worker

```bash
php artisan queue:work
```

---

### 7. Create Controller

```bash
php artisan make:controller PostController
```

`app/Http/Controllers/PostController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string',
            'body'     => 'nullable|string',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $post = Post::create($request->only('title', 'body'));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $post->addMedia($file)
                     ->preservingOriginal()
                     ->toMediaCollection('images');
            }
        }

        return response()->json([
            'redirect' => route('posts.show', $post)
        ]);
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}
```

---

### 8. Routes

`routes/web.php`

```php
use App\Http\Controllers\PostController;

Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
```

---

### 9. Storage link

```bash
php artisan storage:link
```

---

### 10. Views

#### `resources/views/posts/create.blade.php`

```blade
<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Create Post</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css"/>
</head>
<body>
  <h1>Create Post</h1>

  <form id="post-form" action="{{ route('posts.store') }}" method="POST">
    @csrf
    <input type="text" name="title" placeholder="Title"><br>
    <textarea name="body" placeholder="Body"></textarea><br>

    <div id="my-dropzone" class="dropzone"></div>

    <button type="submit">Save</button>
  </form>

  <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
  <script>
    Dropzone.autoDiscover = false;

    const dz = new Dropzone("#my-dropzone", {
      url: "#",
      autoProcessQueue: false,
      uploadMultiple: true,
      parallelUploads: 10,
      maxFilesize: 10,
      acceptedFiles: "image/*",
      addRemoveLinks: true
    });

    document.querySelector('#post-form').addEventListener('submit', function(e){
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);

      dz.getAcceptedFiles().forEach(file => {
        formData.append('images[]', file);
      });

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        alert("Post created! Images queued for conversion.");
        window.location.href = data.redirect;
      })
      .catch(err => {
        console.error(err);
        alert("Something went wrong");
      });
    });
  </script>
</body>
</html>
```

#### `resources/views/posts/show.blade.php`

```blade
<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{{ $post->title }}</title>
</head>
<body>
  <h1>{{ $post->title }}</h1>
  <p>{{ $post->body }}</p>

  <h3>Images:</h3>
  @foreach($post->getMedia('images') as $media)
    <p>
      Original: <img src="{{ $media->getUrl() }}"><br>
      Thumb: <img src="{{ $media->getUrl('thumb') }}"><br>
      Medium: <img src="{{ $media->getUrl('medium') }}"><br>
      Large: <img src="{{ $media->getUrl('large') }}"><br>
    </p>
  @endforeach
</body>
</html>
```

---

✅ Now you have:

* Posts table & model
* File upload (drag & drop) with **DropzoneJS**
* Images stored with **Spatie MediaLibrary**
* Automatic queued conversions (thumb, medium, large)
* Show page that displays all versions

---

Do you want me to also add **progress bars** to the Dropzone upload (so you see real-time progress while uploading), or keep it minimal as in this version?
