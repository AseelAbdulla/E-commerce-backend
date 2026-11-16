<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    /**
     * تحويل مصفوفة رسائل ثنائية اللغة إلى رسائل بلغة واحدة
     * حسب اللغة الحالية للتطبيق (يحددها middleware SetLocaleFromHeader)
     */
    private function bilingualMessages(array $messages): array
    {
        $locale = App::getLocale();

        return array_map(
            fn ($translations) => $translations[$locale] ?? $translations['ar'],
            $messages
        );
    }

    /**
     * Register
     *
     * إنشاء حساب مستخدم جديد مع عنوانه الأساسي
     */
    public function register(Request $request): JsonResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'role' => [
                'nullable',
                'string',
                'in:local-client,import-client',
            ],

            'country' => [
                'required',
                'string',
                'max:100',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'region' => [
                'required',
                'string',
                'max:100',
            ],

            'street' => [
                'required',
                'string',
                'max:255',
            ],

            'building' => [
                'required',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ], $this->bilingualMessages([
            'name.required' => ['ar' => 'الاسم مطلوب.', 'en' => 'Name is required.'],
            'name.max' => ['ar' => 'الاسم طويل جداً.', 'en' => 'Name is too long.'],

            'email.required' => ['ar' => 'البريد الإلكتروني مطلوب.', 'en' => 'Email is required.'],
            'email.email' => ['ar' => 'صيغة البريد الإلكتروني غير صحيحة.', 'en' => 'Email format is invalid.'],
            'email.unique' => ['ar' => 'البريد الإلكتروني مستخدم بالفعل.', 'en' => 'Email is already in use.'],

            'phone.required' => ['ar' => 'رقم الجوال مطلوب.', 'en' => 'Phone number is required.'],

            'password.required' => ['ar' => 'كلمة المرور مطلوبة.', 'en' => 'Password is required.'],
            'password.min' => ['ar' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.', 'en' => 'Password must be at least 8 characters.'],
            'password.confirmed' => ['ar' => 'كلمتا المرور غير متطابقتين.', 'en' => 'Passwords do not match.'],

            'role.in' => ['ar' => 'نوع الحساب غير صحيح.', 'en' => 'Account type is invalid.'],

            'country.required' => ['ar' => 'حقل الدولة مطلوب.', 'en' => 'Country is required.'],
            'city.required' => ['ar' => 'حقل المدينة مطلوب.', 'en' => 'City is required.'],
            'region.required' => ['ar' => 'حقل المنطقة مطلوب.', 'en' => 'Region is required.'],
            'street.required' => ['ar' => 'حقل الشارع مطلوب.', 'en' => 'Street is required.'],
            'building.required' => ['ar' => 'حقل المبنى مطلوب.', 'en' => 'Building is required.'],
        ]));

        /*
        |--------------------------------------------------------------------------
        | Create User + Address (Transaction)
        |--------------------------------------------------------------------------
        */

        $result = DB::transaction(function () use ($validated, $request) {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'status' => 'active',
            ]);

            $requestedRole = $request->input('role', 'local-client');

            if (Role::where('name', $requestedRole)->where('guard_name', 'sanctum')->exists()) {
                $user->assignRole($requestedRole);
            } elseif (Role::where('name', $requestedRole)->exists()) {
                $user->assignRole($requestedRole);
            } else {
                $user->assignRole('local-client');
            }

            $address = Address::create([
                'user_id' => $user->id,
                'country' => $validated['country'],
                'city' => $validated['city'],
                'region' => $validated['region'],
                'street' => $validated['street'],
                'building' => $validated['building'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            $role = $user->getRoleNames()->first();

            return [
                'user' => $user,
                'address' => $address,
                'role' => $role,
                'token' => $token,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Return Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => App::getLocale() === 'ar'
                ? 'تم إنشاء الحساب بنجاح'
                : 'Account created successfully',

            'data' => [
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'phone' => $result['user']->phone,

                    'role' => $result['role'],

                    'permissions' => $result['user']
                        ->getAllPermissions()
                        ->pluck('name')
                        ->values(),
                ],

                'address' => [
                    'id' => $result['address']->id,
                    'country' => $result['address']->country,
                    'city' => $result['address']->city,
                    'region' => $result['address']->region,
                    'street' => $result['address']->street,
                    'building' => $result['address']->building,
                    'notes' => $result['address']->notes,
                ],

                'token' => $result['token'],

                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Login
     *
     * تسجيل الدخول
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ], $this->bilingualMessages([
            'email.required' => ['ar' => 'البريد الإلكتروني مطلوب.', 'en' => 'Email is required.'],
            'email.email' => ['ar' => 'صيغة البريد الإلكتروني غير صحيحة.', 'en' => 'Email format is invalid.'],
            'password.required' => ['ar' => 'كلمة المرور مطلوبة.', 'en' => 'Password is required.'],
        ]));

        if (
            !Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ])
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    App::getLocale() === 'ar'
                        ? 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
                        : 'Invalid email or password',
                ],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (
            isset($user->status) &&
            $user->status !== 'active'
        ) {
            Auth::logout();

            return response()->json([
                'success' => false,

                'message' => App::getLocale() === 'ar'
                    ? 'هذا الحساب غير مفعل'
                    : 'This account is inactive',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        $role = $user->getRoleNames()->first();

        $permissions = $user
            ->getAllPermissions()
            ->pluck('name')
            ->values();

        return response()->json([
            'success' => true,

            'message' => App::getLocale() === 'ar'
                ? 'تم تسجيل الدخول بنجاح'
                : 'Logged in successfully',

            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $role,
                    'permissions' => $permissions,
                ],

                'token' => $token,

                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Current User
     *
     * إرجاع بيانات المستخدم الحالي
     */
    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $role = $user->getRoleNames()->first();

        $permissions = $user
            ->getAllPermissions()
            ->pluck('name')
            ->values();

        return response()->json([
            'success' => true,

            'message' => App::getLocale() === 'ar'
                ? 'تم جلب بيانات المستخدم'
                : 'User data retrieved',

            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                ],

                'role' => $role,

                'permissions' => $permissions,
            ],
        ], 200);
    }

    /**
     * Logout
     *
     * تسجيل الخروج
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request
            ->user()
            ->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,

            'message' => App::getLocale() === 'ar'
                ? 'تم تسجيل الخروج بنجاح'
                : 'Logged out successfully',
        ], 200);
    }
}
