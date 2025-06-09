<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Http\Resources\Admin\TestimonialResource;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TestimonialController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $testimonials = Testimonial::query()
            ->latest()
            ->get();

        return TestimonialResource::collection($testimonials);
    }

    public function store(StoreTestimonialRequest $request): TestimonialResource
    {
        $validated = $request->validated();
        $translations = $validated['translations'];

        $testimonial = Testimonial::query()->create([
            ...$translations,
            'author_name' => $validated['author_name'],
        ]);

        return new TestimonialResource($testimonial);
    }

    public function show(string $locale, Testimonial $testimonial): TestimonialResource
    {
        return new TestimonialResource($testimonial);
    }

    public function update(UpdateTestimonialRequest $request, string $locale, Testimonial $testimonial): TestimonialResource
    {
        $validated = $request->validated();
        $translations = $validated['translations'];

        $testimonial = Testimonial::query()->create([
            ...$translations,
            'author_name' => $validated['author_name'],
        ]);

        return new TestimonialResource($testimonial);
    }

    public function destroy(string $locale, Testimonial $testimonial): Response
    {
        $testimonial->deleteTranslations();
        $testimonial->delete();

        return response()->noContent();
    }
}
