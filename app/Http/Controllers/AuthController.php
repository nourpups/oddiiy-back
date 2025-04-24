<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(LoginRequest $request): array|JsonResponse
    {
        $data = $request->validated();
        $phone = $data['phone'];

        $user = User::query()->where('phone', $phone)->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'errors' => [
                    'password' => __('auth.password')
                ],
            ], 400);
        }

        $token = $user->createToken($user->name);

        Auth::login($user);

        return [
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
        ];
    }

    public function register(RegisterRequest $request): array|UserResource
    {
        $data = $request->validated();

        $user = User::query()->create($data);

        $token = $user->createToken($user->name);

        Auth::login($user);

        return [
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
        ];
    }

    public function logout(Request $request): Response
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    public function resetPassword(ResetPasswordRequest $request): array
    {
        $user = User::query()->where('phone', $request->validated('phone'))->first();

        $user->update(['password' => Hash::make($request->validated('newPassword'))]);

        $token = $user->createToken($user->name);

        Auth::login($user);

        return [
            'token' => $token->plainTextToken
        ];
    }

}
