<?php

namespace App\Http\Controllers;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use App\Http\Requests\StoreClassProfileRequest;
use App\Http\Requests\UpdateClassProfileRequest;
use App\Models\ClassProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ClassProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->user($request);

        return view('class-profiles.index', [
            'classProfiles' => $user->classProfiles()
                ->withCount('teachingProjects')
                ->latest('updated_at')
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('class-profiles.create', $this->formOptions());
    }

    public function store(StoreClassProfileRequest $request): RedirectResponse
    {
        $classProfile = $this->user($request)
            ->classProfiles()
            ->create($request->validated());

        return redirect()
            ->route('class-profiles.edit', $classProfile)
            ->with('success', 'Profil kelas berhasil dibuat.');
    }

    public function edit(ClassProfile $classProfile): View
    {
        Gate::authorize('update', $classProfile);

        return view('class-profiles.edit', [
            ...$this->formOptions(),
            'classProfile' => $classProfile,
        ]);
    }

    public function update(UpdateClassProfileRequest $request, ClassProfile $classProfile): RedirectResponse
    {
        Gate::authorize('update', $classProfile);

        $classProfile->update($request->validated());

        return redirect()
            ->route('class-profiles.edit', $classProfile)
            ->with('success', 'Profil kelas berhasil diperbarui.');
    }

    public function destroy(ClassProfile $classProfile): RedirectResponse
    {
        Gate::authorize('delete', $classProfile);

        if ($classProfile->teachingProjects()->exists()) {
            return back()->with('error', 'Profil kelas masih digunakan oleh proyek pembelajaran dan tidak dapat dihapus.');
        }

        $classProfile->delete();

        return redirect()
            ->route('class-profiles.index')
            ->with('success', 'Profil kelas berhasil dihapus.');
    }

    /** @return array{educationLevels: array<int, EducationLevel>, abilityProfiles: array<int, AbilityProfile>} */
    private function formOptions(): array
    {
        return [
            'educationLevels' => EducationLevel::cases(),
            'abilityProfiles' => AbilityProfile::cases(),
        ];
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
