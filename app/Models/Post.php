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
             ->useDisk('public')  // or s3 if configured
             ->singleFile(false); // allow multiple uploads
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
