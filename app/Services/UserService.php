<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Get paginated users with filters.
     */
    public function getUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['roles', 'company'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('f_name', 'like', "%{$search}%")
                      ->orWhere('l_name', 'like', "%{$search}%")
                      ->orWhere(DB::raw("CONCAT(f_name, ' ', l_name)"), 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('roles.id', $role);
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['company_id'] ?? null, function ($query, $companyId) {
                $query->where('company_id', $companyId);
            })
            ->latest();

        return $query->paginate($perPage);
    }

    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        DB::beginTransaction();
        try {
            // Handle name splitting if f_name is not provided
            if (isset($data['name']) && !isset($data['f_name'])) {
                $parts = explode(' ', $data['name'], 2);
                $data['f_name'] = $parts[0];
                $data['l_name'] = $parts[1] ?? '';
            }

            // Hash password
            $data['password'] = Hash::make($data['password']);

            // Create user
            $user = User::create($data);

            // Sync roles
            if (isset($data['roles'])) {
                $user->roles()->sync($data['roles']);
            }

            // Log activity
            $user->logActivity('created', "User account created: {$user->name}");

            DB::commit();
            return $user->fresh(['roles']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        DB::beginTransaction();
        try {
            // Handle name splitting if f_name is not provided
            if (isset($data['name']) && !isset($data['f_name'])) {
                $parts = explode(' ', $data['name'], 2);
                $data['f_name'] = $parts[0];
                $data['l_name'] = $parts[1] ?? '';
            }

            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Update user
            $user->update($data);

            // Sync roles
            if (isset($data['roles'])) {
                $user->roles()->sync($data['roles']);
            }

            // Log activity
            $user->logActivity('updated', "User account updated: {$user->name}");

            DB::commit();
            return $user->fresh(['roles']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): bool
    {
        DB::beginTransaction();
        try {
            // Log activity before deletion
            $userName = $user->name;
            $user->logActivity('deleted', "User account deleted: {$userName}");

            // Delete user
            $user->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user by ID with relations.
     */
    public function getUserById(int $id): User
    {
        return User::with(['roles', 'company', 'activityLogs'])
            ->withCount(['activityLogs'])
            ->findOrFail($id);
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user): User
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        $user->logActivity('status_changed', "Status changed to: {$newStatus}");
        
        return $user;
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user, string $password): bool
    {
        $user->update(['password' => Hash::make($password)]);
        $user->logActivity('password_reset', 'Password was reset');
        
        return true;
    }
}
