<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request, string $locale, User $user): UserResource
    {
        $address = $request->validated('address');
        $address["house"] = !empty($address["house"]) ? $address["house"] : "pusto";

        if (isset($address)) {
            $user->address()->updateOrCreate([
               'addressable_id' => $user->id,
            ], array_filter($address));
        }

        $user->update($request->validated());

        return new UserResource($user->fresh(['address']));
    }

}
