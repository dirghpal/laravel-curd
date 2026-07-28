<div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" class="form-control" required>
</div>
<div class="mb-3">
    <label class="form-label">Body</label>
    <textarea name="body" class="form-control" rows="5" required>{{ old('body', $post->body ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Published At</label>
    <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}" class="form-control">
</div>
