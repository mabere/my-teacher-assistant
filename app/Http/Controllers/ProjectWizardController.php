<?php

namespace App\Http\Controllers;

use App\Enums\TeachingProjectStatus;
use App\Http\Requests\CompleteTeachingProjectWizardRequest;
use App\Http\Requests\ConfirmTeachingProjectWizardContextRequest;
use App\Http\Requests\StoreTeachingProjectWizardBasicsRequest;
use App\Http\Requests\StoreTeachingProjectWizardExecutionRequest;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectWizardController extends Controller
{
    private const SESSION_KEY = 'teaching_project_wizard';

    /** @var array<int, array{number: int, title: string, description: string}> */
    private const STEPS = [
        ['number' => 1, 'title' => 'Dasar proyek', 'description' => 'Tentukan profil kelas, topik, dan tujuan.'],
        ['number' => 2, 'title' => 'Pelaksanaan', 'description' => 'Atur durasi dan model pembelajaran.'],
        ['number' => 3, 'title' => 'Konteks siswa', 'description' => 'Periksa konteks kelas dan kebutuhan diferensiasi.'],
        ['number' => 4, 'title' => 'Sumber dan tinjau', 'description' => 'Periksa ringkasan sebelum menyimpan proyek.'],
    ];

    public function create(Request $request): View
    {
        $user = $this->user($request);
        $state = $this->state($request);
        $wizardData = $state['data'];
        $step = max(1, min(count(self::STEPS), (int) $request->query('step', $state['step'])));
        $selectedProfile = null;

        if (isset($wizardData['class_profile_id'])) {
            $selectedProfile = $user->classProfiles()->find($wizardData['class_profile_id']);
        }

        if ($step > 1 && $selectedProfile === null) {
            $step = 1;
        }

        return view('teaching-projects.wizard', [
            'steps' => self::STEPS,
            'step' => $step,
            'wizardData' => $wizardData,
            'classProfiles' => $user->classProfiles()->orderBy('name')->get(),
            'selectedProfile' => $selectedProfile,
        ]);
    }

    public function storeBasics(StoreTeachingProjectWizardBasicsRequest $request): RedirectResponse
    {
        $this->mergeState($request, 2, $request->validated());

        return redirect()->route('teaching-projects.create', ['step' => 2]);
    }

    public function storeExecution(StoreTeachingProjectWizardExecutionRequest $request): RedirectResponse
    {
        if (! $this->hasBasics($request)) {
            return $this->restart($request);
        }

        $this->mergeState($request, 3, $request->validated());

        return redirect()->route('teaching-projects.create', ['step' => 3]);
    }

    public function storeContext(ConfirmTeachingProjectWizardContextRequest $request): RedirectResponse
    {
        if (! $this->hasBasics($request)) {
            return $this->restart($request);
        }

        $this->mergeState($request, 4, $request->validated());

        return redirect()->route('teaching-projects.create', ['step' => 4]);
    }

    public function complete(CompleteTeachingProjectWizardRequest $request): RedirectResponse
    {
        if (! $this->hasBasics($request)) {
            return $this->restart($request);
        }

        $state = $this->state($request);
        $data = $state['data'];
        $user = $this->user($request);
        $validated = Validator::make($data, $this->projectRules($user))->validate();

        $project = $user->teachingProjects()->create([
            ...$validated,
            'status' => TeachingProjectStatus::Draft,
        ]);

        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('teaching-projects.show', $project)
            ->with('success', 'Proyek pembelajaran berhasil dibuat dari wizard. Tambahkan sumber lalu lanjutkan ke draft RPP.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('dashboard')->with('info', 'Pembuatan proyek dibatalkan.');
    }

    /** @return array{step: int, data: array<string, mixed>} */
    private function state(Request $request): array
    {
        $state = $request->session()->get(self::SESSION_KEY, []);
        $userId = $request->user()?->getAuthIdentifier();

        if (! is_array($state) || (string) ($state['user_id'] ?? '') !== (string) $userId) {
            return ['step' => 1, 'data' => []];
        }

        return [
            'step' => max(1, (int) ($state['step'] ?? 1)),
            'data' => is_array($state['data'] ?? null) ? $state['data'] : [],
        ];
    }

    /** @param array<string, mixed> $data */
    private function mergeState(Request $request, int $step, array $data): void
    {
        $state = $this->state($request);

        $request->session()->put(self::SESSION_KEY, [
            'user_id' => $request->user()?->getAuthIdentifier(),
            'step' => $step,
            'data' => [...$state['data'], ...$data],
        ]);
    }

    private function hasBasics(Request $request): bool
    {
        return filled($this->state($request)['data']['class_profile_id'] ?? null);
    }

    private function restart(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('teaching-projects.create')
            ->with('error', 'Sesi wizard belum dimulai. Mulai dari dasar proyek.');
    }

    /** @return array<string, array<int, mixed>> */
    private function projectRules(User $user): array
    {
        return [
            'class_profile_id' => [
                'required',
                'integer',
                Rule::exists('class_profiles', 'id')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $user->id)),
            ],
            'title' => ['required', 'string', 'max:160'],
            'topic' => ['required', 'string', 'max:255'],
            'learning_outcomes' => ['nullable', 'string', 'max:10000'],
            'learning_objectives' => ['required', 'string', 'max:10000'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:10000'],
            'teaching_model' => ['nullable', 'string', 'max:120'],
        ];
    }

    private function user(Request $request): User
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return $user;
    }
}
