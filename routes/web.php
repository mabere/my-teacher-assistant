<?php

use App\Http\Controllers\Admin\AiProviderSettingController;
use App\Http\Controllers\AlignmentController;
use App\Http\Controllers\ArtifactController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ClassProfileController;
use App\Http\Controllers\ContentApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneratedContentController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\ProjectWizardController;
use App\Http\Controllers\RppExportController;
use App\Http\Controllers\TeachingProjectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::view('/help', 'help.index')->name('help.index');

    Route::resource('class-profiles', ClassProfileController::class)->except('show');
    Route::get('teaching-projects/create', [ProjectWizardController::class, 'create'])->name('teaching-projects.create');
    Route::post('teaching-projects/wizard/step/1', [ProjectWizardController::class, 'storeBasics'])->name('teaching-projects.wizard.basics');
    Route::post('teaching-projects/wizard/step/2', [ProjectWizardController::class, 'storeExecution'])->name('teaching-projects.wizard.execution');
    Route::post('teaching-projects/wizard/step/3', [ProjectWizardController::class, 'storeContext'])->name('teaching-projects.wizard.context');
    Route::post('teaching-projects/wizard/step/4', [ProjectWizardController::class, 'complete'])->name('teaching-projects.wizard.complete');
    Route::post('teaching-projects/wizard/cancel', [ProjectWizardController::class, 'cancel'])->name('teaching-projects.wizard.cancel');
    Route::resource('teaching-projects', TeachingProjectController::class)->except('create');
    Route::post('teaching-projects/{teachingProject}/generate-rpp', [TeachingProjectController::class, 'generateRpp'])->name('teaching-projects.generate-rpp');
    Route::get('teaching-projects/{teachingProject}/rpp-drafts/{generatedContent}/edit', [GeneratedContentController::class, 'edit'])->name('teaching-projects.rpp-drafts.edit');
    Route::put('teaching-projects/{teachingProject}/rpp-drafts/{generatedContent}', [GeneratedContentController::class, 'update'])->name('teaching-projects.rpp-drafts.update');
    Route::post('teaching-projects/{teachingProject}/rpp-drafts/{generatedContent}/retry', [GeneratedContentController::class, 'retry'])->name('teaching-projects.rpp-drafts.retry');
    Route::post('teaching-projects/{teachingProject}/rpp-drafts/{generatedContent}/recover', [GeneratedContentController::class, 'recover'])->name('teaching-projects.rpp-drafts.recover');
    Route::post('teaching-projects/{teachingProject}/alignment/run', [AlignmentController::class, 'store'])->name('teaching-projects.alignment.run');
    Route::post('teaching-projects/{teachingProject}/artifacts', [ArtifactController::class, 'store'])->name('teaching-projects.artifacts.store');
    Route::post('teaching-projects/{teachingProject}/artifacts/{generatedContent}/retry', [ArtifactController::class, 'retry'])->name('teaching-projects.artifacts.retry');
    Route::post('teaching-projects/{teachingProject}/artifacts/{generatedContent}/recover', [ArtifactController::class, 'recover'])->name('teaching-projects.artifacts.recover');
    Route::post('teaching-projects/{teachingProject}/rpp-drafts/{generatedContent}/versions/{generatedContentVersion}/approve', [ContentApprovalController::class, 'store'])->name('teaching-projects.approvals.store');
    Route::get('teaching-projects/{teachingProject}/rpp-drafts/{generatedContent}/versions/{generatedContentVersion}/export/{format}', [RppExportController::class, 'download'])->where('format', 'docx|pdf')->name('teaching-projects.exports.download');
    Route::post('teaching-projects/{teachingProject}/documents', [ProjectDocumentController::class, 'store'])->name('teaching-projects.documents.store');
    Route::post('teaching-projects/{teachingProject}/documents/{projectDocument}/retry', [ProjectDocumentController::class, 'retry'])->name('teaching-projects.documents.retry');
    Route::post('teaching-projects/{teachingProject}/documents/{projectDocument}/recover', [ProjectDocumentController::class, 'recover'])->name('teaching-projects.documents.recover');
    Route::delete('teaching-projects/{teachingProject}/documents/{projectDocument}', [ProjectDocumentController::class, 'destroy'])->name('teaching-projects.documents.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('ai-provider', [AiProviderSettingController::class, 'edit'])->name('ai-provider.edit');
        Route::put('ai-provider', [AiProviderSettingController::class, 'update'])->name('ai-provider.update');
        Route::post('ai-provider/test', [AiProviderSettingController::class, 'test'])->name('ai-provider.test');
        Route::delete('ai-provider/key', [AiProviderSettingController::class, 'destroy'])->name('ai-provider.key.destroy');
    });
});
