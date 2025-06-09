<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TestimonialController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $testimonials = Testimonial::query()
            ->latest()
            ->get();

        return TestimonialResource::collection($testimonials);
    }
}
