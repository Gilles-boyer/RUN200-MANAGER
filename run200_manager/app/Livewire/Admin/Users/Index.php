<?php

namespace App\Livewire\Admin\Users;

use App\Models\Pilot;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public string $statusFilter = 'active'; // active, deleted, all

    // View modal
    public bool $showViewModal = false;

    public ?int $viewingUserId = null;

    // Edit modal
    public bool $showEditModal = false;

    public ?int $editingUserId = null;

    public string $editName = '';

    public string $editEmail = '';

    public array $editRoles = [];

    // Edit Pilot modal
    public bool $showEditPilotModal = false;

    public ?int $editingPilotId = null;

    public string $pilotFirstName = '';

    public string $pilotLastName = '';

    public string $pilotPhone = '';

    public string $pilotLicenseNumber = '';

    public ?string $pilotBirthDate = null;

    public string $pilotBirthPlace = '';

    public string $pilotAddress = '';

    public string $pilotCity = '';

    public string $pilotPostalCode = '';

    public string $pilotEmergencyName = '';

    public string $pilotEmergencyPhone = '';

    // Delete confirmation
    public bool $showDeleteConfirmation = false;

    public ?int $deletingUserId = null;

    // Create user modal
    public bool $showCreateModal = false;

    public string $createName = '';

    public string $createEmail = '';

    public string $createPassword = '';

    public array $createRoles = [];

    protected $queryString = ['search', 'roleFilter', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function toggleRole(int $userId, string $roleName)
    {
        $user = User::withTrashed()->findOrFail($userId);

        if ($user->hasRole($roleName)) {
            $user->removeRole($roleName);
            session()->flash('success', "Rôle {$roleName} retiré de {$user->name}.");
        } else {
            $user->assignRole($roleName);
            session()->flash('success', "Rôle {$roleName} ajouté à {$user->name}.");
        }
    }

    // Create user
    public function openCreateModal()
    {
        $this->reset(['createName', 'createEmail', 'createPassword', 'createRoles']);
        $this->showCreateModal = true;
    }

    public function createUser()
    {
        $this->validate([
            'createName' => 'required|string|max:255',
            'createEmail' => 'required|email|unique:users,email',
            'createPassword' => 'required|string|min:8',
            'createRoles' => 'required|array|min:1',
            'createRoles.*' => 'exists:roles,name',
        ], [
            'createName.required' => 'Le nom est obligatoire.',
            'createEmail.required' => 'L\'email est obligatoire.',
            'createEmail.email' => 'L\'email doit être valide.',
            'createEmail.unique' => 'Cet email est déjà utilisé.',
            'createPassword.required' => 'Le mot de passe est obligatoire.',
            'createPassword.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'createRoles.required' => 'Veuillez sélectionner au moins un rôle.',
            'createRoles.min' => 'Veuillez sélectionner au moins un rôle.',
        ]);

        $user = User::create([
            'name' => $this->createName,
            'email' => $this->createEmail,
            'password' => $this->createPassword,
        ]);

        $user->syncRoles($this->createRoles);

        session()->flash('success', "Utilisateur {$this->createName} créé avec succès.");
        $this->closeCreateModal();
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->reset(['createName', 'createEmail', 'createPassword', 'createRoles']);
    }

    // View user details
    public function viewUser(int $userId)
    {
        $this->viewingUserId = $userId;
        $this->showViewModal = true;
    }

    public function getViewingUserProperty()
    {
        if (! $this->viewingUserId) {
            return null;
        }

        return User::withTrashed()->with(['pilot', 'roles'])->find($this->viewingUserId);
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingUserId = null;
    }

    // Edit user
    public function editUser(int $userId)
    {
        $user = User::withTrashed()->with('roles')->findOrFail($userId);
        $this->editingUserId = $userId;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editRoles = $user->roles->pluck('name')->toArray();
        $this->showEditModal = true;
    }

    public function updateUser()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|unique:users,email,'.$this->editingUserId,
            'editRoles' => 'required|array|min:1',
            'editRoles.*' => 'exists:roles,name',
        ]);

        $user = User::withTrashed()->findOrFail($this->editingUserId);
        $user->update([
            'name' => $this->editName,
            'email' => $this->editEmail,
        ]);

        $user->syncRoles($this->editRoles);

        session()->flash('success', "Utilisateur {$this->editName} mis à jour.");
        $this->closeEditModal();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingUserId = null;
        $this->reset(['editName', 'editEmail', 'editRoles']);
    }

    // Edit pilot
    public function editPilot(int $pilotId)
    {
        $pilot = Pilot::findOrFail($pilotId);
        $this->editingPilotId = $pilotId;
        $this->pilotFirstName = $pilot->first_name;
        $this->pilotLastName = $pilot->last_name;
        $this->pilotPhone = $pilot->phone ?? '';
        $this->pilotLicenseNumber = $pilot->license_number ?? '';
        $this->pilotBirthDate = $pilot->birth_date?->format('Y-m-d');
        $this->pilotBirthPlace = $pilot->birth_place ?? '';
        $this->pilotAddress = $pilot->address ?? '';
        $this->pilotCity = $pilot->city ?? '';
        $this->pilotPostalCode = $pilot->postal_code ?? '';
        $this->pilotEmergencyName = $pilot->emergency_contact_name ?? '';
        $this->pilotEmergencyPhone = $pilot->emergency_contact_phone ?? '';
        $this->showEditPilotModal = true;
    }

    public function updatePilot()
    {
        $this->validate([
            'pilotFirstName' => 'required|string|max:255',
            'pilotLastName' => 'required|string|max:255',
            'pilotPhone' => 'nullable|string|max:20',
            'pilotLicenseNumber' => 'nullable|string|max:50',
            'pilotBirthDate' => 'nullable|date',
            'pilotBirthPlace' => 'nullable|string|max:255',
            'pilotAddress' => 'nullable|string|max:255',
            'pilotCity' => 'nullable|string|max:255',
            'pilotPostalCode' => 'nullable|string|max:10',
            'pilotEmergencyName' => 'nullable|string|max:255',
            'pilotEmergencyPhone' => 'nullable|string|max:20',
        ]);

        $pilot = Pilot::findOrFail($this->editingPilotId);
        $pilot->update([
            'first_name' => $this->pilotFirstName,
            'last_name' => $this->pilotLastName,
            'phone' => $this->pilotPhone ?: null,
            'license_number' => $this->pilotLicenseNumber ?: null,
            'birth_date' => $this->pilotBirthDate ?: null,
            'birth_place' => $this->pilotBirthPlace ?: null,
            'address' => $this->pilotAddress ?: null,
            'city' => $this->pilotCity ?: null,
            'postal_code' => $this->pilotPostalCode ?: null,
            'emergency_contact_name' => $this->pilotEmergencyName ?: null,
            'emergency_contact_phone' => $this->pilotEmergencyPhone ?: null,
        ]);

        session()->flash('success', "Profil pilote de {$this->pilotFirstName} {$this->pilotLastName} mis à jour.");
        $this->closeEditPilotModal();
    }

    public function closeEditPilotModal()
    {
        $this->showEditPilotModal = false;
        $this->editingPilotId = null;
        $this->reset([
            'pilotFirstName', 'pilotLastName', 'pilotPhone', 'pilotLicenseNumber',
            'pilotBirthDate', 'pilotBirthPlace', 'pilotAddress', 'pilotCity',
            'pilotPostalCode', 'pilotEmergencyName', 'pilotEmergencyPhone',
        ]);
    }

    // Soft delete
    public function confirmDelete(int $userId)
    {
        $this->deletingUserId = $userId;
        $this->showDeleteConfirmation = true;
    }

    public function getDeletingUserProperty()
    {
        if (! $this->deletingUserId) {
            return null;
        }

        return User::find($this->deletingUserId);
    }

    public function deleteUser()
    {
        if ($this->deletingUserId) {
            $user = User::findOrFail($this->deletingUserId);
            $name = $user->name;
            $user->delete();
            session()->flash('success', "Utilisateur {$name} supprimé.");
        }
        $this->closeDeleteConfirmation();
    }

    public function restoreUser(int $userId)
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();
        session()->flash('success', "Utilisateur {$user->name} restauré.");
    }

    public function closeDeleteConfirmation()
    {
        $this->showDeleteConfirmation = false;
        $this->deletingUserId = null;
    }

    public function render()
    {
        $query = User::with(['roles', 'pilot']);

        // Status filter
        if ($this->statusFilter === 'deleted') {
            $query->onlyTrashed();
        } elseif ($this->statusFilter === 'all') {
            $query->withTrashed();
        }
        // 'active' is default (no trashed)

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhereHas('pilot', function ($pq) {
                        $pq->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%')
                            ->orWhere('license_number', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Role filter
        if ($this->roleFilter) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
